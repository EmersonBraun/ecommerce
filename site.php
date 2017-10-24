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
        'error'=>User::getError(),
        'errorRegister'=>User::getErrorRegister(),
        'registerValues'=>(isset($_SESSION['registerValues']) ? $_SESSION['registerValues'] : [
            'name'=>'',
            'email'=>'',
            'phone'=>''
        ])
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

$app->post("/register", function(){

    //para não zerar os valores já preenchidos
    $_SESSION['registerValues'] = $_POST;

    if(!isset($_POST['name']) || $_POST['name'] == ''){

        User::setErrorRegister("Preencha o nome");
        header("Location: /login");
        exit;
    }

    if(!isset($_POST['email']) || $_POST['email'] == ''){

        User::setErrorRegister("Preencha o e-mail");
        header("Location: /login");
        exit;
    }

    if(!isset($_POST['password']) || $_POST['password'] == ''){

        User::setErrorRegister("Preencha a senha");
        header("Location: /login");
        exit;
    }

    if(User::checkLoginExist($_POST['email']) === true){

        User::setErrorRegister("Este endereço de e-mai já está sendo usado por outro usuário");
        header("Location: /login");
        exit;

    }

    $user = new User();

    $user->setData([
        'inadmin'=>0,
        'deslogin'=>$_POST['email'],
        'desperson'=>$_POST['name'],
        'desemail'=>$_POST['email'],
        'despassword'=>$_POST['password'],
        'nrphone'=>$_POST['phone']
    ]);

    $user->save();

    User::login($_POST['email'], $_POST['password']);

    header("Location: /checkout");
    exit;
});
?>