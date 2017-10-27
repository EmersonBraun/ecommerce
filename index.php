<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Braun\Page;
use \Braun\PageAdmin;
use \Braun\Model\User;
use \Braun\Model\Category;
use \Braun\Model\Product;

$app = new Slim();

require_once("functions.php");
require_once("routes/site.php");
require_once("routes/site-cart.php");
require_once("routes/site-users.php");
require_once("routes/site-payment.php");
require_once("routes/admin.php");
require_once("routes/admin-users.php");
require_once("routes/admin-categories.php");
require_once("routes/admin-products.php");
require_once("routes/admin-orders.php");

$app->run();

 ?>