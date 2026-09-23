<?php
// Real concurrent connections, confined to a uniquely named disposable database.
if (PHP_SAPI!=='cli') exit('CLI only');
chdir(__DIR__.'/../src');
require 'db_connect.php';
function get_current_user_id(){return null;}
function t($key){return $key;}
require 'models/Product.php';
require 'models/Order.php';
if (($argv[1] ?? '')==='--worker') {
    $database=$argv[2];
    if (!preg_match('/^catalog_regression_[a-f0-9]{16}$/D',$database)) throw new RuntimeException('Invalid test database');
    $pdo->exec("USE `$database`");
    $mode=$argv[3]; $id=(int)($argv[4] ?? 0);
    try {
        if ($mode==='sku') {
            $id=(new Product())->create(['name_en'=>'Concurrent SKU','category_id'=>1,'price'=>1,'stock_quantity'=>1]);
            echo json_encode(['sku'=>(new Product())->getById($id)['sku']]);
        } elseif ($mode==='cancel') {
            echo json_encode(['cancel'=>(new Order())->cancel($id)]);
        } else {
            $variant=(new Product())->getProductVariants($id)[0];
            $item=CatalogRules::sellable($pdo,$id,$variant['id']);
            $item['id']=random_int(1,100000000); $item['quantity']=1;
            $order=['order_number'=>'R-'.bin2hex(random_bytes(8)),'customer_id'=>null,'subtotal'=>10,'shipping_cost'=>0,'total_amount'=>10,'billing_address'=>[],'shipping_address'=>[],'delivery_option'=>'standard','wilaya'=>'Test','daira'=>'Test','commune'=>'Test','payment_method'=>'cod','items'=>[$item]];
            echo json_encode(['order'=>(new Order())->create($order)]);
        }
    } catch (DomainException $e) { echo json_encode(['blocked'=>$e->getMessage()]); }
    exit;
}
$database='catalog_regression_'.bin2hex(random_bytes(8));
$created=false;
$run = function($mode,$id=0) use ($database) {
    $jobs=[];
    for($i=0;$i<2;$i++) {
        $process=proc_open([PHP_BINARY,__FILE__,'--worker',$database,$mode,(string)$id],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
        if(!is_resource($process)) throw new RuntimeException('Cannot launch test worker');
        fclose($pipes[0]); $jobs[]=[$process,$pipes];
    }
    $results=[];
    foreach($jobs as [$process,$pipes]) {
        $out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        if ($err!=='') echo $err;
        if(proc_close($process)!==0) throw new RuntimeException('Worker failed: '.$err);
        $results[]=json_decode($out,true,512,JSON_THROW_ON_ERROR);
    }
    return $results;
};
try {
    $pdo->exec("CREATE DATABASE `$database`"); $created=true;
    foreach(['products','product_variants','variant_attributes','orders','order_items','order_status_history','product_images','sku_registry','sku_sequences','categories','category_abbreviations','brands'] as $table) $pdo->exec("CREATE TABLE `$database`.`$table` LIKE `".DB_NAME."`.`$table`");
    $pdo->exec("USE `$database`");
    $pdo->exec("INSERT INTO categories(id,name_en,name_fr) VALUES(1,'Concurrent category','Concurrent category')");
    $id=(new Product())->create(['name_en'=>'Last unit','category_id'=>1,'price'=>10,'stock_quantity'=>0,'variants'=>[['variant_name'=>'Red','price'=>10,'stock_quantity'=>1,'attributes'=>['color'=>'Red']]]]);
    $skuResults=$run('sku');
    if(empty($skuResults[0]['sku']) || empty($skuResults[1]['sku']) || $skuResults[0]['sku']===$skuResults[1]['sku']) throw new RuntimeException('Concurrent SKU allocation failed');
    echo 'PASS concurrent SKU allocation produces distinct SKUs',PHP_EOL;
    $purchases=$run('purchase',$id); $success=array_values(array_filter($purchases,fn($r)=>!empty($r['order'])));
    if(count($success)!==1 || count(array_filter($purchases,fn($r)=>isset($r['blocked'])))!==1) throw new RuntimeException('Last-unit concurrency failed: '.json_encode($purchases));
    if((int)$pdo->query('SELECT stock_quantity FROM products WHERE id='.$id)->fetchColumn()!==0) throw new RuntimeException('Incorrect stock after concurrent purchase');
    echo 'PASS exactly one concurrent last-unit purchase succeeds',PHP_EOL;
    $results=$run('cancel',$success[0]['order']);
    if(count(array_filter($results,fn($r)=>!empty($r['cancel'])))!==2 || (int)$pdo->query('SELECT stock_quantity FROM products WHERE id='.$id)->fetchColumn()!==1) throw new RuntimeException('Concurrent cancellation failed');
    echo 'PASS concurrent cancellation restores exactly one unit',PHP_EOL;
} finally {
    if($created && preg_match('/^catalog_regression_[a-f0-9]{16}$/D',$database)) {
        $pdo->exec('USE `'.DB_NAME.'`');
        $pdo->exec("DROP DATABASE `$database`");
        echo 'Removed disposable test database; live catalog was not modified by tests.',PHP_EOL;
    }
}
