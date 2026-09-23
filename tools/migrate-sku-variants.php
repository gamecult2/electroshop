<?php
// CLI only; take a recoverable snapshot before any DDL or data reconciliation.
if (PHP_SAPI !== 'cli') exit('CLI only');
chdir(__DIR__ . '/../src');
require 'db_connect.php';
$tables = ['products','product_variants','variant_attributes','shopping_cart','orders','order_items','sku_counters','category_abbreviations','product_images','order_status_history','wishlists'];
$backupDir = sys_get_temp_dir() . '/electroshop-catalog-backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0700, true);
$snapshot = [];
foreach ($tables as $table) {
    $snapshot[$table] = ['ddl' => $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1], 'rows' => $pdo->query("SELECT * FROM `$table`")->fetchAll()];
}
$backup = $backupDir . '/sku-variants-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.json';
if (file_put_contents($backup, json_encode($snapshot, JSON_THROW_ON_ERROR)) === false) throw new RuntimeException('Backup failed.');
echo 'Backup saved: ', $backup, PHP_EOL;
foreach ($tables as $table) $pdo->exec("ALTER TABLE `$table` ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS catalog_repair_archive (id BIGINT AUTO_INCREMENT PRIMARY KEY, source_table VARCHAR(64) NOT NULL, source_id BIGINT NOT NULL, reason VARCHAR(255) NOT NULL, row_data JSON NOT NULL, archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY source_reason(source_table,source_id,reason)) ENGINE=InnoDB");
$archive = function($table, $row, $reason) use ($pdo) {
    $pdo->prepare('INSERT IGNORE INTO catalog_repair_archive(source_table,source_id,reason,row_data) VALUES(?,?,?,?)')->execute([$table,$row['id'],$reason,json_encode($row, JSON_THROW_ON_ERROR)]);
};
$addColumn = function($table,$column,$definition) use ($pdo) {
    $s=$pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $s->execute([$table,$column]);
    if (!$s->fetchColumn()) $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
};
$addColumn('shopping_cart','unavailable_reason','VARCHAR(255) NULL');
$addColumn('orders','inventory_policy',"VARCHAR(16) NOT NULL DEFAULT 'legacy'");
$addColumn('orders','inventory_released','TINYINT NOT NULL DEFAULT 0');
$addColumn('orders','checkout_key','CHAR(64) NULL');
$pdo->beginTransaction();
try {
    foreach ($pdo->query('SELECT * FROM products WHERE price < 0 OR stock_quantity < 0 OR discount_percentage < 0 OR discount_percentage > 100')->fetchAll() as $row) {
        $archive('products',$row,'Invalid price or inventory; review before reactivation');
        $pdo->prepare('UPDATE products SET is_active=0, price=GREATEST(price,0), stock_quantity=GREATEST(stock_quantity,0), discount_percentage=LEAST(100,GREATEST(0,discount_percentage)) WHERE id=?')->execute([$row['id']]);
    }
    foreach ($pdo->query('SELECT a.* FROM variant_attributes a LEFT JOIN product_variants v ON v.id=a.product_variant_id WHERE v.id IS NULL')->fetchAll() as $row) {
        $archive('variant_attributes',$row,'Missing variant');
        $pdo->prepare('DELETE FROM variant_attributes WHERE id=?')->execute([$row['id']]);
    }
    foreach (['shopping_cart','order_items'] as $table) {
        foreach ($pdo->query("SELECT i.* FROM `$table` i LEFT JOIN product_variants v ON v.id=i.variant_id WHERE i.variant_id IS NOT NULL AND v.id IS NULL")->fetchAll() as $row) {
            $archive($table,$row,'Missing variant');
            $extra=$table === 'shopping_cart' ? ", unavailable_reason='This option was removed. Remove this item and choose an available option.'" : '';
            $pdo->prepare("UPDATE `$table` SET variant_id=NULL $extra WHERE id=?")->execute([$row['id']]);
        }
    }
    $pdo->exec("UPDATE orders SET inventory_released=1 WHERE status='cancelled'");
    $pdo->exec('UPDATE products p JOIN (SELECT product_id,SUM(IF(is_active=1,stock_quantity,0)) stock FROM product_variants GROUP BY product_id) v ON v.product_id=p.id SET p.stock_quantity=v.stock');
    $pdo->commit();
} catch (Throwable $e) { $pdo->rollBack(); throw $e; }
$pdo->exec("CREATE TABLE IF NOT EXISTS sku_registry (sku VARCHAR(100) COLLATE utf8mb4_0900_ai_ci PRIMARY KEY, owner_type VARCHAR(16) NOT NULL, owner_id INT NOT NULL, KEY owner(owner_type,owner_id)) ENGINE=InnoDB");
foreach (['products'=>'product','product_variants'=>'variant'] as $table=>$type) {
    foreach ($pdo->query("SELECT id,sku FROM `$table` WHERE sku IS NOT NULL AND sku<>''")->fetchAll() as $row) {
        $s=$pdo->prepare('SELECT owner_type,owner_id FROM sku_registry WHERE sku=?'); $s->execute([$row['sku']]); $owner=$s->fetch();
        if ($owner && ($owner['owner_type']!==$type || (int)$owner['owner_id']!==(int)$row['id'])) throw new RuntimeException('Conflicting SKU requires manual reconciliation: '.$row['sku']);
        $pdo->prepare('INSERT IGNORE INTO sku_registry VALUES(?,?,?)')->execute([$row['sku'],$type,$row['id']]);
    }
}
$pdo->exec('CREATE TABLE IF NOT EXISTS sku_sequences (namespace VARCHAR(100) PRIMARY KEY, next_number BIGINT NOT NULL DEFAULT 1) ENGINE=InnoDB');
// Database enforcement also covers imports and older direct-SQL writers.
foreach (['products'=>'product','product_variants'=>'variant'] as $table=>$type) {
    foreach (['INSERT','UPDATE'] as $event) {
        $name='catalog_sku_'.$type.'_'.strtolower($event);
        $s=$pdo->prepare('SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME=?'); $s->execute([$name]);
        if (!$s->fetchColumn()) $pdo->exec("CREATE TRIGGER `$name` AFTER $event ON `$table` FOR EACH ROW BEGIN
            IF NEW.sku IS NOT NULL AND NEW.sku <> '' THEN
                IF EXISTS(SELECT 1 FROM sku_registry WHERE sku=NEW.sku AND (owner_type<>'$type' OR owner_id<>NEW.id)) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SKU is already reserved by another catalog item';
                ELSEIF NOT EXISTS(SELECT 1 FROM sku_registry WHERE sku=NEW.sku) THEN
                    INSERT INTO sku_registry(sku,owner_type,owner_id) VALUES(NEW.sku,'$type',NEW.id);
                END IF;
            END IF;
        END");
    }
}
$constraints = [
    ['product_variants','fk_catalog_variant_product','FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT'],
    ['variant_attributes','fk_catalog_attribute_variant','FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE'],
    ['shopping_cart','fk_catalog_cart_variant','FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT'],
    ['order_items','fk_catalog_order_variant','FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT'],
    ['variant_attributes','uq_catalog_attribute','UNIQUE (product_variant_id,attribute_name)'],
    ['product_variants','ck_catalog_variant_values','CHECK (price >= 0 AND stock_quantity >= 0)'],
    ['products','ck_catalog_product_values','CHECK (price >= 0 AND stock_quantity >= 0 AND discount_percentage BETWEEN 0 AND 100)'],
    ['shopping_cart','ck_catalog_cart_quantity','CHECK (quantity > 0)'],
    ['orders','uq_catalog_checkout','UNIQUE (checkout_key)']
];
foreach ($constraints as [$table,$name,$definition]) {
    $s=$pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_NAME=?'); $s->execute([$table,$name]);
    if (!$s->fetchColumn()) $pdo->exec("ALTER TABLE `$table` ADD CONSTRAINT `$name` $definition");
}
// Remove only exact, full-column SKU duplicates; keep the original unique index.
foreach (['products','product_variants'] as $table) {
    $indexes=$pdo->query("SHOW INDEX FROM `$table`")->fetchAll();
    $groups=[]; foreach($indexes as $index) $groups[$index['Key_name']][]=$index;
    foreach($groups as $name=>$parts) {
        if ($name !== 'sku' && count($parts)===1 && $parts[0]['Column_name']==='sku' && $parts[0]['Sub_part']===null) $pdo->exec("ALTER TABLE `$table` DROP INDEX `$name`");
    }
}
echo "Catalog migration complete; archived records and original order snapshots retained.", PHP_EOL;
