<?php
$path = dirname(__DIR__);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

class ImportacaoCustoJusto {

	function callCurl($url, $cabecalho = [], $post = [], $metodo = 'POST', $desabilitaCabecalho = FALSE) {
		try {
			$curl = curl_init();
		
			if ($curl === false) {
				throw new Exception('failed to initialize');
			}
			
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $metodo);
			if ($metodo == 'POST' || $metodo == 'PUT') {
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			}
			
			curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 1);			
			curl_setopt($curl, CURLOPT_PROXY, '192.168.10.254');
			curl_setopt($curl, CURLOPT_PROXYPORT, 3128);
			curl_setopt($curl, CURLOPT_PROXYUSERPWD,'denis.brandl:123');
						
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
			
			if ($response === false) {
				return json_encode([
					'status' => 500,
					'response' => sprintf('Falha ao conectar na API: #%d: %s', curl_error($curl), curl_errno($curl))
				]);
			}
			
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			curl_close($curl);
			
			return json_encode([
				'status' => $status,
				'response' => $response
			]);
			
		}  catch(Exception $e) {
			return json_encode([
				'status' => 500,
				'response' => sprintf('Falha ao conectar na API: #%d: %s',$e->getCode(), $e->getMessage())
			]);
		}
	}
	
	public function importarCategorias() {
		$url = 'https://v2.custojusto.pt/categories/tree';
		$chave = '0dpheEOA23F7aCYwj7Wk';
		
		$arrCategorias = $this->callCurl($url, ['Authorization: ' . $chave], [], 'GET', FALSE);
		$arrCategorias = json_decode($arrCategorias, true);
		
		return array_merge(
			[
				'success' => 'true',
				'retorno' => $arrCategorias
			]
		);
	}
	
	function downloadImagem($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);		
		$imagem = curl_exec($ch);
		
		if (curl_error($ch)) {
			$error_msg = curl_error($ch);
		}		
		
		if (isset($error_msg)) {
			print "Erro no curl";
			print $error_msg;
			exit;
		}
		
		curl_close($ch);	
		return $imagem;
	}	
	
	function validaJson($json){
	   return is_string($json) && is_array(json_decode($json, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
}
	
