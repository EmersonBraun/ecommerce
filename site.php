<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

//login usuario
$app->get("/checkout",function(){

    User::verifyLogin(false);

    $cart = Cart::getFromSession();

    $address= new Address;

    $page = new Page();

    $page->setTpl("checkout",[
        'cart'=>$cart->getValues(),
        'address'=>$address->getValues()
    ]);

});

$app->get("/login",function(){

    $page = new Page();

    $page->setTpl("login",[
        'error'=>User::getError()
    ]);

});

$app->post("/login",function(){

    try{

        User::login($_POST['login'], $_POST['password']);

    } catch(Exception $e){

        User::setError($e->getMessage());

    }

    header("Location: /checkout");
    exit;
});

$app->get("/logout", function(){

   User::logout();

   Cart::removeToSession();

   session_regenerate_id();

   header("Location: /login");
   exit;
});
?>