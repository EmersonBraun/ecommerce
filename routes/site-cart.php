<?php 

use \Braun\Page;
use \Braun\Model\Product;
use \Braun\Model\Category;
use \Braun\Model\Cart;
use \Braun\Model\Address;
use \Braun\Model\User;
use \Braun\Model\Msg;

//carrinho
$app->get("/cart", function(){

   $cart = Cart::getFromSession();

   $cart->checkZipCode();

   $page = new Page();

   $page->setTpl("cart", [
     'cart'=>$cart->getValues(),
     'products'=>$cart->getProducts(),
     'error'=>Cart::getError()
   ]);

});

$app->get("/cart/:idproduct/add", function($idproduct){

    $product = new Product();

    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

    for ($i = 0; $i < $qtd; $i++){

        $cart->addProduct($product);
    }

    header("Location: /cart");
    exit;

});

$app->get("/cart/:idproduct/minus", function($idproduct){

    $product = new Product();

    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $cart->removeProduct($product);

    header("Location: /cart");
    exit;

});

$app->get("/cart/:idproduct/remove", function($idproduct){

    $product = new Product();

    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $cart->removeProduct($product, true);
    
    header("Location: /cart");
    exit;

});

$app->post("/cart/freight",function(){

    $cart = Cart::getFromSession();

    $cart->setFreight($_POST['zipcode']);

    header("Location: /cart");
    exit;

});

?>