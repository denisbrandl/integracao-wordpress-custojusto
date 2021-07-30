<?php
set_time_limit(0);
ini_set('display_errors', 1);
require ('./classes/ImportacaoWordpress.php');

$fp = fopen('status.json', 'w');
fwrite($fp,'0');
fclose($fp);

$objImportacaoWordpress = new ImportacaoWordpress();
$integracao_custo_justo_chave_custo_justo = get_option('integracao_custo_justo_chave_custo_justo' );
$integracao_custo_justo_token_plugin = get_option('integracao_custo_justo_token_plugin');

$integracao_custo_justo_nome_empresa = $_GET['integracao_custo_justo_nome_empresa'];
$integracao_custo_justo_email = $_GET['integracao_custo_justo_email'];
$integracao_custo_justo_telefone = $_GET['integracao_custo_justo_telefone'];
$integracao_custo_justo_codigo_postal = $_GET['integracao_custo_justo_codigo_postal'];
$integracao_custo_justo_distrito = $_GET['integracao_custo_justo_distrito'];
$integracao_custo_justo_conselho = $_GET['integracao_custo_justo_conselho'];
$integracao_custo_justo_freguesia = $_GET['integracao_custo_justo_freguesia'];
$integracao_custo_justo_tipo_negocio = $_GET['integracao_custo_justo_tipo_negocio'];
$categoria_produtos_custo_justo = $_GET['categoria_produtos_custo_justo'];

/*
$integracao_custo_justo_nome_empresa = get_option('integracao_custo_justo_nome_empresa');
$integracao_custo_justo_email = get_option('integracao_custo_justo_email');
$integracao_custo_justo_telefone = get_option('integracao_custo_justo_telefone');
$integracao_custo_justo_codigo_postal = get_option('integracao_custo_justo_codigo_postal');
$integracao_custo_justo_distrito = get_option('integracao_custo_justo_distrito');
$integracao_custo_justo_conselho = get_option('integracao_custo_justo_conselho');
$integracao_custo_justo_freguesia = get_option('integracao_custo_justo_freguesia');
$integracao_custo_justo_tipo_negocio = get_option('integracao_custo_justo_tipo_negocio');

*/

$arrCategorias = [
	$_GET['categoria_produtos_wordpress']
];

$arrProdutos = $objImportacaoWordpress->exportarProdutosPorCategoria($arrCategorias);

$totalDeProdutos = count($arrProdutos);
$quantidadeDeRepeticoes = ceil($totalDeProdutos / 10);
$produtoAtual = 0;
if (empty($arrProdutos)) {
	header('Content-Type: application/json');
	echo json_encode(
		[
			'produtos' => ['success' => '-1', 'msg' => 'Sem produtos para exportar :-(']
		]
	);
	
	$fp = fopen('status.json', 'w');
	fwrite($fp,
		json_encode(
			array(
				'total' => $totalDeProdutos,
				'current' => $totalDeProdutos
			)
		)
	);
	fclose($fp);
	
	exit;
}

$url_plugin = plugin_dir_url( __FILE__ );	
$url_integracao_custo_justo = $url_plugin .'bridge-custo-justo/webservice/rest.php';
$metodo = 'POST';
$ignorar_cabecalho = FALSE;

$arrParametrosConfiguracao = [
	'token_custo_justo' => $integracao_custo_justo_chave_custo_justo,	
	'integracao_custo_justo_token_plugin' => $integracao_custo_justo_token_plugin,
	'integracao_custo_justo_nome_empresa' => $integracao_custo_justo_nome_empresa,
	'integracao_custo_justo_email' => $integracao_custo_justo_email,
	'integracao_custo_justo_telefone' => preg_replace('/(\+[0-9]{2})/m', '', $integracao_custo_justo_telefone),
	'integracao_custo_justo_codigo_postal' => $integracao_custo_justo_codigo_postal,
	'integracao_custo_justo_distrito' => $integracao_custo_justo_distrito,
	'integracao_custo_justo_conselho' => $integracao_custo_justo_conselho,
	'integracao_custo_justo_freguesia' => $integracao_custo_justo_freguesia,
	'integracao_custo_justo_tipo_negocio' => $integracao_custo_justo_tipo_negocio,
	'categoria_produtos_custo_justo' => $categoria_produtos_custo_justo
];

$arrMultiplosProdutos = array_chunk($arrProdutos, 10);

$arrRetornoFinal = [];
for ($i = 0; $i < $quantidadeDeRepeticoes; $i++) {
	
	$arrParametrosConfiguracao['produtos_exportar'] = json_encode($arrMultiplosProdutos[$i]);
	$arrParametros = json_encode($arrParametrosConfiguracao);
	
	$produtoAtual = $produtoAtual + count($arrMultiplosProdutos[$i]);
	
	$enviarProdutos = $objImportacaoWordpress->callCurl(
		$url_integracao_custo_justo,
		[
			'chave: ' . $integracao_custo_justo_token_plugin
		],
		$arrParametros,
		$metodo,
		$ignorar_cabecalho
	);

	$arrEnviarProdutos = json_decode($enviarProdutos, true);
	if (is_array($arrEnviarProdutos)) {
		foreach ($arrEnviarProdutos['produtos'] as $produtos) {
			update_post_meta( $produtos['id_produto'], 'id_custojusto', $produtos['id_custojusto']);
		}
	}
	$arrRetornoFinal[] = $arrEnviarProdutos;
	
	$fp = fopen('status.json', 'w');
	fwrite($fp,
		json_encode(
			array(
				'total' => $totalDeProdutos,
				'current' => $produtoAtual
			)
		)
	);
	fclose($fp);	
}

$fp = fopen('status.json', 'w');
fwrite($fp,'0');
fclose($fp);

header('Content-Type: application/json');
echo json_encode($arrRetornoFinal);
