<?php
// Integration tests use connection-local temporary tables, never live catalog rows.
if (PHP_SAPI !== 'cli') exit('CLI only');
chdir(__DIR__.'/../src');
require 'db_connect.php';
function get_current_user_id() { return $GLOBALS['testCustomer'] ?? null; }
function t($key) { return $key; }
session_id('catalog-regression-'.bin2hex(random_bytes(6)));
require 'models/Product.php';
require 'models/Cart.php';
require 'models/Order.php';
$tables=['products','product_variants','variant_attributes','shopping_cart','orders','order_items','order_status_history','product_images','sku_registry','sku_sequences','category_abbreviations','categories'];
foreach($tables as $table) {
    $ddl=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
    $ddl=str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$ddl);
    $ddl=preg_replace('/^.*(?:FOREIGN KEY|FULLTEXT KEY).*\n/m','',$ddl);
    $ddl=preg_replace('/,\s*\) ENGINE/',"\n) ENGINE",$ddl);
    $pdo->exec($ddl);
}
$pdo->exec("INSERT INTO categories(id,name_en,name_fr) VALUES(1,'Regression category','Regression category')");
$count=0;
function check($condition,$message) { global $count; if(!$condition) throw new RuntimeException($message); $count++; echo 'PASS ', $message,PHP_EOL; }
function rejects($callback,$message) { try {$callback();} catch (Throwable $e) {check(true,$message); return;} throw new RuntimeException('Expected rejection: '.$message); }
$products=new Product(); $cart=new Cart(); $orders=new Order();
$data=['name_en'=>'Variant regression','category_id'=>1,'price'=>100,'discount_percentage'=>10,'stock_quantity'=>99,'sku'=>'REG-CATALOG-1','variants'=>[
    ['variant_name'=>'Red','price'=>120,'stock_quantity'=>3,'attributes'=>['Color'=>'Red']],
    ['variant_name'=>'Blue','price'=>150,'stock_quantity'=>2,'attributes'=>['Color'=>'Blue']]
]];
$id=$products->create($data); $variants=$products->getProductVariants($id);
check(count($variants)===2,'atomic product and variant creation');
check($variants[0]['sku']==='REG-CATALOG-1-V01' && $variants[1]['sku']==='REG-CATALOG-1-V02','sequential variant SKUs');
check((int)$products->getById($id)['stock_quantity']===5,'parent inventory derived from variants');
rejects(fn()=>$products->create(array_replace($data,['sku'=>'REG-CATALOG-BAD','variants'=>[['price'=>1,'stock_quantity'=>1,'attributes'=>[]]]])),'invalid variant rolls back creation');
check((int)$pdo->query("SELECT COUNT(*) FROM products WHERE sku='REG-CATALOG-BAD'")->fetchColumn()===0,'no partial product left after failure');
check(!$cart->add($id,1)['success'],'cannot buy parent of variant product');
check(!$cart->add($id,-1,$variants[0]['id'])['success'],'negative quantities rejected');
check(!$cart->add($id,'1.5',$variants[0]['id'])['success'],'fractional quantities rejected');
check($cart->add($id,2,$variants[0]['id'])['success'],'valid variant added');
$items=$cart->getItems();
check((float)$items[0]['price_at_time']===108.0 && (float)$cart->getSubtotal()===216.0,'discounted variant price shared by cart and totals');
$cartId=$items[0]['id'];
$GLOBALS['testCustomer']=999; $otherCart=new Cart();
check(!$otherCart->update($cartId,1)['success'],'cannot update another cart');
$GLOBALS['testCustomer']=null;
$products->update($id,['variants'=>array_reverse($variants)]);
$after=$products->getProductVariants($id);
check(array_column($after,'id')===array_column($variants,'id') && array_column($after,'sku')===array_column($variants,'sku'),'editing and reordering preserve variant identities');
$order=['order_number'=>'REG-'.bin2hex(random_bytes(4)),'customer_id'=>null,'subtotal'=>216,'shipping_cost'=>0,'total_amount'=>216,'billing_address'=>[],'shipping_address'=>[],'delivery_option'=>'standard','wilaya'=>'Test','daira'=>'Test','commune'=>'Test','payment_method'=>'cod','items'=>$cart->getItems()];
$orderId=$orders->create($order);
check((bool)$orderId,'variant order created');
check((int)$products->getVariantById($variants[0]['id'])['stock_quantity']===1,'order deducts selected variant stock');
check((int)$products->getById($id)['stock_quantity']===3,'order synchronizes parent stock');
check((int)$orders->create($order)===(int)$orderId,'repeat checkout is idempotent');
check($orders->cancel($orderId) && $orders->cancel($orderId),'repeat cancellation succeeds safely');
check((int)$products->getVariantById($variants[0]['id'])['stock_quantity']===3,'cancellation restocks exactly once');
$chargilyOrder=$order;
$chargilyOrder['order_number']='REG-'.bin2hex(random_bytes(4));
$chargilyOrder['payment_method']='chargily';
$chargilyOrder['items'][0]['id']=99999; // Model a fresh cart row and therefore a distinct checkout.
$chargilyOrderId=$orders->create($chargilyOrder);
check((bool)$chargilyOrderId && $orders->getById($chargilyOrderId)['payment_method']==='chargily','Chargily order persists before gateway handoff');
check($orders->updatePaymentDetails($chargilyOrderId,'pending','test-checkout-id') && $orders->getById($chargilyOrderId)['transaction_id']==='test-checkout-id','Chargily checkout ID is saved on the order');
check($orders->cancel($chargilyOrderId),'unpaid Chargily order can be cancelled and restocked');
$oversell=$order; $oversell['order_number']='REG-'.bin2hex(random_bytes(4)); $oversell['items'][0]['quantity']=4; $oversell['subtotal']=432;
rejects(fn()=>$orders->create($oversell),'checkout rejects insufficient stock');
check((int)$products->getVariantById($variants[0]['id'])['stock_quantity']===3,'failed checkout preserves stock');
rejects(fn()=>$products->create(array_replace($data,['sku'=>$variants[0]['sku'],'variants'=>[]])),'cross-table SKU collisions rejected');
$products->update($id,['variants'=>[$variants[1]]]);
check(!$cart->add($id,1,$variants[0]['id'])['success'],'retired variant cannot be purchased');
check((int)$pdo->query('SELECT COUNT(*) FROM product_variants')->fetchColumn()===2,'removed variant retained for order history');
check((int)$orders->getItems($orderId)[0]['variant_id']===(int)$variants[0]['id'],'order retains variant reference');
rejects(fn()=>CatalogRules::variantsFromForm([['attributes'=>['name'=>[' Color ','color'],'value'=>['Red','Blue']]]],10),'duplicate normalized attributes rejected');
$same=$data; $same['sku']='REG-DUP'; $same['variants'][1]['attributes']=['color'=>'RED'];
rejects(fn()=>$products->create($same),'duplicate option combinations rejected');
check(!$products->updateStock($id,50),'parent stock cannot override variant inventory');
$zero = CatalogRules::variantsFromForm([['price'=>'0','stock_quantity'=>'1','attributes'=>['name'=>['color'],'value'=>['Blue']]]],99);
check((float)$zero[0]['price']===0.0,'explicit zero variant price is not replaced by base price');
$generator = new SKUGenerator();
$pdo->beginTransaction();
$pdo->prepare('INSERT INTO sku_registry(sku,owner_type,owner_id) VALUES(?,?,?)')->execute(['IMPORT-V99','variant',99999]);
check($generator->generateVariantSKU('IMPORT')==='IMPORT-V100','variant sequence advances beyond imported and retired SKUs');
$pdo->rollBack();
echo "Completed $count assertions. Temporary tables disappear when this connection closes.",PHP_EOL;
