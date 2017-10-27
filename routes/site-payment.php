<?php 

use \Braun\Page;
use \Braun\Model\Address;
use \Braun\Model\User;
use \Braun\Model\Cart;
use \Braun\Model\Order;
use \Braun\Model\OrderStatus;

//pagamento
$app->get("/checkout",function(){

    User::verifyLogin(false);

    $address = new Address;

    $cart = Cart::getFromSession();

    if(isset($_GET['zipcode'])){

    	$_GET['zipcode'] = $cart->getdeszipcode();
    }

    if(isset($_GET['zipcode'])){

    	$address->loadFromCEP($_GET['zipcode']);

    	$cart->setdeszipcode($_GET['zipcode']);

    	$cart->save();

    	$cart->getCalculateTotal();
    }

    if(!$address->getdesaddress()) $address->setdesaddress('');
    if(!$address->getdescomplement()) $address->setdescomplement('');
    if(!$address->getdesdistrict()) $address->setdesdistrict('');
    if(!$address->getdescity()) $address->setdescity('');
    if(!$address->getdesstate()) $address->setdesstate('');
    if(!$address->getdescountry()) $address->setdescountry('');
    if(!$address->getdeszipcode()) $address->setdeszipcode('');

    $page = new Page();

    $page->setTpl("checkout",[
        'cart'=>$cart->getValues(),
        'address'=>$address->getValues(),
        'products'=>$cart->getProducts(),
        'error'=>Address::getError(),
    ]);

});

$app->post("/checkout",function(){
	//verifica login
	User::verifyLogin(false);
	//verifica se falta algum dado
	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		
		Address::setError("Informe o CEP");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		
		Address::setError("Informe o endereço");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		
		Address::setError("Informe o bairro");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		
		Address::setError("Informe a cidade");
		header("Location: /checkout");
		exit;
	}
	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		
		Address::setError("Informe o estado.");
		header("Location: /checkout");
		exit;
	}
	//dados de usuário
	$user = User::getFromSession();
	//acertos para endereço
	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();
	//dados carrinho
	$cart = Cart::getFromSession();
	//atualizar valor total
	$cart->getCalculateTotal();
	//gerar ordem
	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'idaddress'=>$address->getidaddress(),
		'vltotal'=>$cart->getvltotal()
		]);

	$order->save();
	
	header("Location: /order/".$order->getidorder());
	exit;
});
$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment",[
		'order'=>$order->getValues()
	]);

});

$app->get("/boleto/:idorder", function($idorder){

User::verifyLogin(false);

$order = new Order();

$order->get((int)$idorder);

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 10;
$taxa_boleto = 5.00;
$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(".", "",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $order->getdesperson();
$dadosboleto["endereco1"] = utf8_encode($order->getdesaddress())." ".utf8_encode($order->getdesdistrict());
$dadosboleto["endereco2"] = utf8_encode($order->getdescity())." - ".utf8_encode($order->getdesstate())." - CEP: ".$order->getdeszipcode();

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
$dadosboleto["demonstrativo3"] = "";
$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "";		
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - ITAÚ
$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

// DADOS PERSONALIZADOS - ITAÚ
$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

// SEUS DADOS
$dadosboleto["identificacao"] = "Hcode Treinamentos";
$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

// NÃO ALTERAR!
$path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR.
"boletophp".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR;

require_once($path."funcoes_itau.php");
require_once($path."layout_itau.php");

});

?>