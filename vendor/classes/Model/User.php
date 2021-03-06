<?php 

namespace Braun\Model;

use \Braun\DB\Sql;
use \Braun\Model;
use \Braun\Mailer;

class User extends Model{

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SESSION_ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSuccess";

	public static function getFromSession(){

		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0) {

			$user->setData($_SESSION[User::SESSION]);
			
		}

		return $user;

	}

	public static function checkLogin($inadmin = true){
		
		if(
			!isset($_SESSION[User::SESSION]) 
			||
			!$_SESSION[User::SESSION] 
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 
		){
			//não está logado
			return false;

		}else{

			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){

				return true;

			}elseif ($inadmin === false) {

				return true;

			}else{

				return false;

			}
		}

	}

	public static function verifyLogin($inadmin = true){

		if(!User::checkLogin($inadmin)){

			if ($inadmin) {
				header("Location: /admin/login");
			}else{
			header("Location: /login");
			}
			exit;
		}
	}

	public function logout(){

		$_SESSION[User::SESSION] = NULL;
		
	} 
	public static function login($login, $password){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0){
			throw new \Exception("Usuário inexiste ou senha inválida.");
			
		}

		$data = $results[0];

		if (password_verify($password,$data["despassword"]) === true){

			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] =  $user->getValues();

			return $user;

		} else {

			throw new \Exception("Usuário inexiste ou senha inválida.");

		}
	}

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson");
	} 
	
	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	public function get($iduser){

  	$sql = new Sql();

  	$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
    ":iduser"=>$iduser
  	));

  	$data = $results[0];

  	$data['desperson'] = utf8_encode($data['desperson']);
  	
  	$this->setData($data);
	}

	public function update(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	}

	public static function getForgot($email, $inadmin = true){

		$sql = new Sql();

		$results = $sql->select("
			SELECT * FROM tb_persons AS p
			INNER JOIN tb_users AS u USING(idperson)
			WHERE p.desemail = :email;", array(
				":email"=>$email
		));

		if(count($results) === 0){

			throw new \Exception("Não foi possível recuperar a senha.");
			
		}else{

			$data = $results[0];

			$resultRecovery = $sql->select("CALL sp_userspasswordsrecoveries_create (:iduser, :desip)",array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($resultRecovery) === 0) {
				
				throw new \Exception("Não foi possível recuperar a senha.");

			}else{

				$dataRecovery = $resultRecovery[0];

				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				if ($inadmin === true) {
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				}else{
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}
				

				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();

				return $data;
			}
		}
	}

	public static function validForgotDecrypt($code){


		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

		$sql = new Sql();

		$results = $sql->select("SELECT *
		FROM tb_userpasswordsrecoveries AS up
		INNER JOIN tb_users AS u USING(iduser)
		INNER JOIN tb_persons AS p USING(id_person)
		WHERE
			up.idrecovery = :idrecovery
		    AND
		    up.dtrecovery IS NULL
		    AND
		    DATE_ADD(up.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
		    	":idrecovery"=>$idrecovery
		    ));

		if (count($results) === 0) {

			throw new \Exception("Não foi possível recuperar a senha");
			
		}else{

			return $results[0];
		}
	}

	public static function setForgotUsed($idrecovery){

		$sql = new Sql();

		$sql->query("UPDATE tb_userpasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));
	}

	public function setPassword($password){

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET desperson = :password WHERE iduser = :iduser", array(
			":password"=>$password ,
			":iduser"=>$this->getiduser() 
		));

	}
	//erros de login
	public static function setError($msg){

		$_SESSION[User::SESSION_ERROR] = (string)$msg;

	}

	public static function getError(){

		$msg = (isset($_SESSION[User::SESSION_ERROR])) ? $_SESSION[User::SESSION_ERROR] : "";

		User::clearError();

		return $msg;
		
	}

	public static function clearError(){

		$_SESSION[User::SESSION_ERROR] = NULL;
	}
	//error na hora de registrar
	public static function setErrorRegister($msg){

		$_SESSION[User::ERROR_REGISTER] = $msg;
	}

	public static function getErrorRegister(){

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
		
		User::clearErrorRegister();

		return $msg;
	}

	public static function clearErrorRegister(){

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}
	//mensagem de sucesso
	public static function setSuccess($msg){

		$_SESSION[User::SUCCESS] = (string)$msg;

	}

	public static function getSuccess(){

		$msg = (isset($_SESSION[User::SUCCESS])) ? $_SESSION[User::SUCCESS] : "";

		User::clearSuccess();

		return $msg;
		
	}

	public static function clearSuccess(){

		$_SESSION[User::SUCCESS] = NULL;
	}
	//verificar se há outro usuário com o mesmo login
	public static function checkLoginExist($login){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", array(
			':deslogin'=>$login
		));

		return (count($results) > 0);
	}
	//criptografar a senha
	public static function getPasswordHash($password){

		return password_hash($password, PASSWORD_DEFAULT,[
			'cost'=>12
		]);
	}

	public function getOrders(){

		$sql = new Sql();

		$results = $sql->select("
			SELECT * FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart) 
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson 
			WHERE a.iduser = :iduser 
			", [
				':iduser'=>$this->getiduser()
			]);

		if (count($results) > 0) {
			
			$this->setData($results[0]);
			
		}

		return $results;

	}

	
}//

 ?>