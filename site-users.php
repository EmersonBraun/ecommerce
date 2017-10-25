<?php 

use \Hcode\Page;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Cart;

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

//esqueci a senha
$app->get("/forgot", function(){

	$page = new Page();

	$page->setTpl("forgot");
});

$app->post("/forgot", function(){

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;
});
//envio de email para recuperação
$app->get("/forgot/sent", function(){

	$page = new Page();

	$page->setTpl("forgot-sent");

});
//resetar senha
$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($user["idrecover"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"],PASSWORD_DEFAULT,[
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-sucess");

});

?>