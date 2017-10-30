<?php 

use \Braun\PageAdmin;
use \Braun\Model\User;
use \Braun\Model\Pagination;

//listar usuários
$app->get('/admin/users',function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search !== '') {

		$pagination = Pagination::getPageSearch("user",$search, $page);

	} else {

		$pagination = Pagination::getPage("user",$page);

	}
	

	$pages = [];

	for ($i=0; $i < $pagination['pages']; $i++) { 
		
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$i+1,
				'search'=>$search
			]),
			'text'=>$i+1
		]);

	}

	$page = new PageAdmin();

    $page->setTpl("users", array(
    	"users"=>$pagination['data'],
    	"search"=>$search,
    	"pages"=>$pages
    ));

});
//criar usuários
$app->get('/admin/users/create',function(){

	User::verifyLogin();

	$page = new PageAdmin();

    $page->setTpl("users-create");

});

$app->post('/admin/users/create', function(){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
	"cost"=>12
	]);

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});
//apagar usuários
$app->get('/admin/users/:iduser/delete', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();
	header("Location: /admin/users");
	exit;
	
});
//update de usuário
$app->get('/admin/users/:iduser',function($iduser){

	User::verifyLogin();

	$user = new User();

  	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
   "user"=>$user->getValues()
  ));
	
});

$app->post('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();
	header("Location: /admin/users");
	exit;
});
//esqueci a senha
$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});
//envio de email para recuperação
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});
//resetar senha
$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($user["idrecover"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"],PASSWORD_DEFAULT,[
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-sucess");

});

?>