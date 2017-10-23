<?php 

namespace Hcode;

use Rain\Tpl;
use \Hcode\Model\User;

class PageAdmin extends Page {

	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

		$this->options = array_merge($this->defaults, $opts);
	// config
	$config = array(
					"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
					"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
					"debug"         => false
				   );

	Tpl::configure( $config );

	// create the Tpl object
	$this->tpl = new Tpl;
	// assign a variable
	$this->setData($this->options["data"]);

		if ($this->options["header"] === true){

			$this->tpl->assign('title','Página Administrativa');
			$this->tpl->assign('name',$_SESSION[User::SESSION]["deslogin"]);
			$this->tpl->assign('sistem','Loja');
			$this->tpl->assign('sist','L');
			$this->tpl->assign('sisname','Adm');
			$this->tpl->draw("header");

		}
	}
	private function setData($data = array()){

		foreach ($data as $key => $value){
			$this->tpl->assign($key,$value);
		}
		
	}

	public function setTpl($name, $data = array(), $returnHTML = false){

		$this->setData($data);

		return $this->tpl->draw($name, $returnHTML);
	}


	public function __destruct(){

		if ($this->options["footer"] === true){
			 $this->tpl->draw("footer");
		}

	}
}


 ?>