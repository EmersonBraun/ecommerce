<?php 
namespace Braun\Model;

use \Braun\DB\Sql;
use\Braun\Model;

class Pagination extends Model{

	public static function getPage($item, $page = 1, $itemsPerPage = 10){

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		switch (strtolower($item)) {

			case 'user':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
			");

			break;

			case 'category':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
			");

			break;

			case 'order':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_orders 
			ORDER BY idorder
			LIMIT $start, $itemsPerPage;
			");

			break;

			case 'product':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
			");

			break;
			
		}
		
		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}

	public static function getPageSearch($item, $search, $page = 1, $itemsPerPage = 10){

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		switch (strtolower($item)) {
			case 'user':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			WHERE b.desperson LIKE :search 
			OR b.desemail = :search
			OR a.deslogin LIKE :search
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
			",[
				':search'=>$search
			]);

			break;

			case 'category':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
			", [
				':search'=>'%'.$search.'%'
			]);

			break;

			case 'order':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_orders 
			WHERE vltotal LIKE :search
			ORDER BY idorder
			LIMIT $start, $itemsPerPage;
			", [
				':search'=>'%'.$search.'%'
			]);

			break;

			case 'product':

			$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			WHERE desproduct LIKE :search
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
			", [
				':search'=>'%'.$search.'%'
			]);

			break;
			
		}
		

		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}

}//
?>