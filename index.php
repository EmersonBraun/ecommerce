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
require_once("routes".DIRECTORY_SEPARATOR."site.php");
require_once("routes".DIRECTORY_SEPARATOR."site-cart.php");
require_once("routes".DIRECTORY_SEPARATOR."site-users.php");
require_once("routes".DIRECTORY_SEPARATOR."site-payment.php");
require_once("routes".DIRECTORY_SEPARATOR."admin.php");
require_once("routes".DIRECTORY_SEPARATOR."admin-users.php");
require_once("routes".DIRECTORY_SEPARATOR."admin-categories.php");
require_once("routes".DIRECTORY_SEPARATOR."admin-products.php");
require_once("routes".DIRECTORY_SEPARATOR."admin-orders.php");

$app->run();

 ?>