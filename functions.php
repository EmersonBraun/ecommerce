<?php 

use \Braun\Model\User;
use \Braun\Model\Cart;

function formatPrice($vlprcice){

	if(!$vlprcice > 0) $vlprcice = 0;
	
	return number_format((float)$vlprcice,2,",",".");;
}

function formatDate($date){

	return date('d/m/Y', strtotime($date));
	
}

function toUppercase($name){

	return strtoupper($name);
}

function checkLogin($inadimin = true){

	return User::checkLogin($inadimin);
}

function getUserName(){

	$user = User::getFromSession();

	return $user->getdesperson();
}

function getCartNrQtd(){

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];
}

function getCartVlSubTotal(){

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatPrice($totals['vlprice']);
}

?>