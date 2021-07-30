<?php
$path = dirname(__DIR__, 4);
require_once($path."/wp-load.php");
require_once($path.'/wp-admin/includes/file.php');
require_once($path.'/wp-admin/includes/image.php' );

class ImportacaoWordpress {

	public function exportarProdutosPorCategoria($arrCategoria) {
		
		$arrUpload_dir = wp_upload_dir();
		$upload_dir = $arrUpload_dir['basedir'];		
		
		$args = array(
			'post_status' => 'publish',
			'post_type' => 'product',
			'nopaging' => true,
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'     =>  $arrCategoria,
					'operator'  => 'IN'
				)
			)
		);
		
		$consulta = new WP_Query($args);
		
		$resultadoConsulta = $consulta->posts;
		
		$arrProdutos = [];
		foreach($resultadoConsulta as $produto) {
			$arrProduto = [];			
			
			$product = wc_get_product( $produto->ID);
			
			if (empty($produto->post_title) || empty($produto->post_content)) {
				continue;
			}
			
			$arrProduto['body'] = $produto->post_content;			
			$arrProduto['subject'] = $produto->post_title;			
			$arrProduto['price'] = $product->get_price();
			$arrProduto['image'] = get_the_post_thumbnail_url($produto->ID, 'original');
			$arrProduto['id_produto'] = $produto->ID;
			$arrProduto['id_custojusto'] = get_post_meta($produto->ID, 'id_custojusto', true);
			
			$arrProdutos[] = $arrProduto;
		}
		
		return $arrProdutos;
	}
	
	function callCurl($url, $cabecalho = [], $post = [], $metodo = 'POST', $desabilitaCabecalho = FALSE, $opcoes = []) {
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $metodo);
		if ($metodo == 'POST') {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		}
		
		if (!isset($cabecalho['Content-Type'])) {
			$cabecalho[] = "Content-Type: application/json";
		}
		$cabecalho = array_merge( 
						[
							"Accept: application/json"
						], 
						$cabecalho
					);		
		
		if (!$desabilitaCabecalho) {
			curl_setopt(
				$curl,
				CURLOPT_HTTPHEADER,
				$cabecalho
			);
		}
		
		$response = curl_exec($curl);
		if (curl_error($curl)) {
			$error_msg = curl_error($curl);
		}		
		
		if (isset($error_msg)) {
			print "Erro no curl";
			print $error_msg;
			exit;
		}
		
		curl_close($curl);
		
		
		return $response;
	}
}
	
