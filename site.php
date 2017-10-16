<?php 

use \Hcode\Page;

//página inicial
$app->config('debug', true);

$app->get('/', function() {
    
    $page = new Page();

    $page->setTpl("index");

});


?>