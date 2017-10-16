<?php 

use \Hcode\Page;
use \Hcode\Model\Product;

//página inicial
$app->config('debug', true);

$app->get('/', function() {
    
    $products = Product::listAll();
    $page = new Page();

    $page->setTpl("index", [
    	'product'=>Product::checklist($products)
    ]);

});


?>