<?php 

namespace Braun;

class Model{

	private $values = [];
	const ERROR = "Error";
	const SUCCESS = "Success";
	//geters e seters
	public function __call($name, $args){

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));
		
		switch ($method) {
			case 'get':
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
				break;

			case 'set':
				$this->values[$fieldName] = $args[0];
				break;
		}
	}

	public function setData($data = array()){

		foreach ($data as $key => $value) {
			$this->{"set".$key}($value);
		}

	}

	public function getValues(){

		return $this->values;
	}

	//erros de login
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