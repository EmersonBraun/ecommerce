<?php 

use \Hcode\Page;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Cart;

//login usuario
$app->get("/checkout",function(){

    User::verifyLogin(false);

    $address = new Address;

    $cart = Cart::getFromSession();

    if(isset($_GET['zipcode'])){

    	$_GET['zipcode'] = $cart->getdeszipcode();
    }

    if(isset($_GET['zipcode'])){

    	$address->loadFromCEP($_GET['zipcode']);

    	$cart->setdeszipcode($_GET['zipcode']);

    	$cart->save();

    	$cart->getCalculateTotal();
    }

    if(!$address->getdesaddress()) $address->setdesaddress('');
    if(!$address->getdescomplement()) $address->setdescomplement('');
    if(!$address->getdesdistrict()) $address->setdesdistrict('');
    if(!$address->getdescity()) $address->setdescity('');
    if(!$address->getdesstate()) $address->setdesstate('');
    if(!$address->getdescountry()) $address->setdescountry('');
    if(!$address->getdeszipcode()) $address->setdeszipcode('');

    $page = new Page();

    $page->setTpl("checkout",[
        'cart'=>$cart->getValues(),
        'address'=>$address->getValues(),
        'products'=>$cart->getProducts(),
        'error'=>Address::getError(),
    ]);

});

$app->post("/checkout",function(){

	User::verifyLogin(false);

	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		
		Address::setError("Informe o CEP");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		
		Address::setError("Informe o endereço");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		
		Address::setError("Informe o bairro");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		
		Address::setError("Informe a cidade");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		
		Address::setError("Informe o estado.");
		header("Location: /checkout");
		exit;
	}
	

	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	header("Location: /order");
	exit;
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
//meu perfil
$app->get("/profile",function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile",[
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});

$app->post("/profile",function(){

	User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){

		User::setError("Preencha o seu nome.");
		header("Location: /profile");
		exit;

	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){

		User::setError("Preencha o seu e-mail.");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] !== $user->getdesemail()) {

		if (User::checkLoginExist($_POST['desemail']) === true) {
			
			User::setError("Esse email já está sendo utilizado por outro usuário.");
			header("Location: /profile");
			exit;
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->save();

	User::setSuccess("Dados alterados com sucesso!");

	header("Location: /profile");
	exit;

});
?>