<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

//página inicial
$app->config('debug', true);

$app->get('/', function() {
    
    $products = Product::listAll();
    $page = new Page();

    $page->setTpl("index", [
    	'product'=>Product::checklist($products)
    ]);

});

$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

    $page->setTpl("category", [
    	'category'=>$category->getValues(),
    	'product'=>Product::checklist($category->getProducts())
    ]);
});

?>