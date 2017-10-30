<?php 

namespace Braun\Model;

use \Braun\DB\Sql;
use \Braun\Model;

class Msg extends Model{

	const ERROR = "Error";
	const SUCCESS = "Success";
	
	//erros 
	public static function setError($msg){

		$_SESSION[self::ERROR] = (string)$msg;

	}

	public static function getError(){

		$msg = (isset($_SESSION[self::ERROR])) ? $_SESSION[self::ERROR] : "";

		self::clearError();

		return $msg;
		
	}

	public static function clearError(){

		$_SESSION[self::ERROR] = NULL;
	}

	//mensagem de sucesso
	public static function setSuccess($msg){

		$_SESSION[self::SUCCESS] = (string)$msg;

	}

	public static function getSuccess(){

		$msg = (isset($_SESSION[self::SUCCESS])) ? $_SESSION[self::SUCCESS] : "";

		self::clearSuccess();

		return $msg;
		
	}

	public static function clearSuccess(){

		$_SESSION[self::SUCCESS] = NULL;
	}
}

?>