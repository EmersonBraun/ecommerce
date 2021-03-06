<?php 
use \Braun\Page;
use \Braun\PageAdmin;
use \Braun\Model\User;
use \Braun\Model\Category;
use \Braun\Model\Product;
use \Braun\Model\Pagination;

//listar categorias
$app->get("/admin/categories",function(){

	User::verifyLogin();
	
	//paginação e busca
	$search = (isset($_GET['search']))  ? $_GET['search']: "";

	$page = (isset($_GET['page']))? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = Pagination::getPageSearch("category",$search, $page);
		
	}else{

		$pagination = Pagination::getPage("category",$page);
	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++){

		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);

});
//criar categorias
$app->get("/admin/categories/create",function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

$app->post('/admin/categories/create', function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});
//apagar categorias
$app->get('/admin/categories/:idcategory/delete', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();
	header("Location: /admin/categories");
	exit;
	
});
//update de categoria
$app->get('/admin/categories/:idcategory',function($idcategory){

	User::verifyLogin();

	$category = new Category();

  	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", array(
   "category"=>$category->getValues()
  ));
	
});

$app->post('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();
	header("Location: /admin/categories");
	exit;
});
//relação categoria-produto
$app->get("/admin/categories/:idcategory/products",function($idcategory){

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

    $page->setTpl("categories-products", [
    	'category'=>$category->getValues(),
    	'productsRelated'=>$category->getProducts(true),
    	'productsNotRelated'=>$category->getProducts(false)
    ]);

});
//adicionar relação categoria-produto
$app->get("/admin/categories/:idcategory/products/:idproduct/add",function($idcategory, $idproduct){

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});
//remover relação categoria-produto
$app->get("/admin/categories/:idcategory/products/:idproduct/remove",function($idcategory, $idproduct){

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

?>