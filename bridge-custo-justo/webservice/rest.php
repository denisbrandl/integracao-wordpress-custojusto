<?php
require_once(dirname(__DIR__).'/classes/ImportacaoCustoJusto.php');
$objImportacaoWordpress = new ImportacaoCustoJusto();
$mensagem_retorno = "";	
$retorno = [];
$requisicao =$_SERVER["REQUEST_METHOD"];
$variaveis = file_get_contents("php://input");
$arrVariaveis = json_decode($variaveis, true);

$chave_configurada = '123';

if (!isset($_SERVER['HTTP_CHAVE']) || empty($_SERVER['HTTP_CHAVE']) ) {
	header('Content-Type: application/json');
	echo json_encode(
		[
			'retorno' => 'Chave não informada',
			'success' => 'false'
		]
	);
	exit;
}

$chave = $_SERVER['HTTP_CHAVE'];

if ($chave !== $chave_configurada) {
	header('Content-Type: application/json');
	echo json_encode(
		[
			'retorno' => 'Chave incorreta!',
			'success' => 'false'
		]
	);
	exit;
}

// if (!$objImportacaoWordpress->validaJson($arrVariaveis)) {
	// header('Content-Type: application/json');
	// echo json_encode(
		// [
			// 'retorno' => 'Parâmetros incorretos!',
			// 'success' => 'false'
		// ]
	// );
	// exit;	
// }

$arrProdutos = json_decode($arrVariaveis['produtos_exportar'], true);
$arrRetornoCustoJusto = [];
if (is_array($arrProdutos)) {
	
	foreach ($arrProdutos as $produto) {
		if (!is_dir('./imagens_temp')) {
			mkdir('./imagens_temp', 0777);
		}
		
		$imagem_id = 0;
		$id_custojusto = $produto['id_custojusto'];
		if (!empty($produto['image'])) {
			$imagem_salva = $objImportacaoWordpress->downloadImagem($produto['image']);
			$fp = fopen('./imagens_temp/'.$produto['id_produto'].'.jpg', 'w');
			fwrite($fp, $imagem_salva);
			fclose($fp);

			unset($imagem_salva);

			$retornoImagem = $objImportacaoWordpress->callCurl(
				'https://v2qa.custojusto.pt/images/ads',
				$cabecalho = ['Authorization: ' . $arrVariaveis['token_custo_justo'], 'Content-Type: multipart/form-data'],
				$post = ['file' => new CURLFILE( realpath('./imagens_temp/' . $produto['id_produto'].'.jpg'))],
				$metodo = 'POST',
				$desabilitaCabecalho = FALSE
			);				

			$arrRetornoImagem = json_decode($retornoImagem, true);
			

			if ($arrRetornoImagem['status'] == 200) {
				if (isset($arrRetornoImagem['response']['image'])) {
					$imagem_id = $arrRetornoImagem['response']['image']['id'];
				}
			}
			
			unlink('./imagens_temp/'.$produto['id_produto'].'.jpg');
		}
		
		$postProduto = [
						"author" => [
							"email" => $arrVariaveis['integracao_custo_justo_email'],
							"name" => $arrVariaveis['integracao_custo_justo_nome_empresa'],
							"phone" => $arrVariaveis['integracao_custo_justo_telefone'],
							"phoneDisabled" => false,
							"professionalAd" => true,
							"salesmanDisabled" => false,
							"vatNumber" => "999999990"
						],
						"body" => strtolower($produto['body']),
						"category" => $arrVariaveis['categoria_produtos_custo_justo'],
						"location" => [
							"area" => (int) $arrVariaveis['integracao_custo_justo_conselho'],
							"cp6" => $arrVariaveis['integracao_custo_justo_codigo_postal'],
							"district" => (int) $arrVariaveis['integracao_custo_justo_distrito'],
							"subArea" => (int) $arrVariaveis['integracao_custo_justo_freguesia']
						],
						"partner" => [
							"externalAdID" => "app-euromotores-" . $produto['id_produto'],
							"externalGroupID" => "partner-group-name"
						],
						"price" => (int) $produto['price'],
						"subject" => ucwords(strtolower(substr($produto['subject'],0,30))),
						"type" => "s"					
		];

		if ($imagem_id > 0) {
			$postProduto = array_merge(
				$postProduto,
				[
					"images" => [
							$imagem_id
					]
				]
			);
		}
		
		
		$metodo = 'POST';
		$editar_anuncio_custojusto = '';
		if ((int) $produto['id_custojusto'] > 0) {
			$metodo = 'PUT';
			$editar_anuncio_custojusto = '/' . $produto['id_custojusto'];
		}
		
		$retornoProduto = $objImportacaoWordpress->callCurl(
			'https://v2qa.custojusto.pt/partner/entries' . $editar_anuncio_custojusto,
			$cabecalho = ['Authorization: ' . $arrVariaveis['token_custo_justo'], 'Content-Type: application/json'],
			json_encode($postProduto),
			$metodo,
			$desabilitaCabecalho = FALSE
		);
		
		$arrRetornoProduto = json_decode($retornoProduto, true);
		if ($arrRetornoProduto['status'] == 417 && isset($arrRetornoProduto['response']) ) {			
			$arrResponse = json_decode($arrRetornoProduto['response'], true);
			$mensagens_retorno = '';
			foreach ($arrResponse['results'] as $key => $resposta) {
				$_field = '';
				$_value = '';	
				if (isset($resposta['field'])) {
					$_field = $resposta['field'];
					$_value = $resposta['value'];
				}
				
				$mensagens_retorno .= sprintf('%s - %s', $_field, $_value);
			}
			
			$arrRetornoCustoJusto[] = [
				'id_produto' => $produto['id_produto'],
				'id_custojusto' => $id_custojusto,
				'success' => '0',
				'msg' => $mensagens_retorno
			];						
		} else if ($arrRetornoProduto['status'] == 500) {
			$arrResponse = json_decode($arrRetornoProduto['response'], true);
			$arrRetornoCustoJusto[] = [
				'id_produto' => $produto['id_produto'],
				'id_custojusto' => $id_custojusto,
				'success' => '0',
				'msg' => sprintf('%s', $arrRetornoProduto['response'])
			];									
		} else {
			if (isset($arrRetornoProduto['response']['results'][0]['field']) && $arrRetornoProduto['response']['results'][0]['field'] == 'adID') {
				$id_custojusto = $arrRetornoProduto['response']['results'][0]['value'];
			}
			$arrRetornoCustoJusto[] = [
				'id_produto' => $produto['id_produto'],
				'id_custojusto' => $id_custojusto,
				'success' => '1',
				'msg' => ''
			];
		}
		
	}
}

$retorno = array_merge($retorno, ['produtos' => $arrRetornoCustoJusto] );

header('Content-Type: application/json');
echo json_encode($retorno);