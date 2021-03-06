<?php 

use \Braun\Page;
use \Braun\Model\Product;
use \Braun\Model\Category;
use \Braun\Model\Cart;
use \Braun\Model\Address;
use \Braun\Model\User;

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

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = [];

    for ($i=1; $i <= $pagination['pages']; $i++){

        array_push($pages, [
            'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
            'page'=>$i
        ]);
    }

	$page = new Page();

    $page->setTpl("category", [
    	'category'=>$category->getValues(),
        'product'=>$pagination["data"],
    	'pages'=>$pages
    ]);
});

$app->get("/products/:desurl", function($desurl){

    $product = new Product();

    $product->getFromURL($desurl);

    $page = new Page();

    $page->setTpl("product-detail",[
        'product'=>$product->getValues(),
        'categories'=>$product->getCategories()
    ]);
});


?>