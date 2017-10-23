<?php 

function formatPrice($vlprcice){

	return number_format((float)$vlprcice,2,",",".");;
}

function toUppercase($name){

	return strtoupper($name);
}

?>