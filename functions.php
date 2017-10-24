<?php 

use \Hcode\Model\User;

function formatPrice($vlprcice){

	return number_format((float)$vlprcice,2,",",".");;
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

?>