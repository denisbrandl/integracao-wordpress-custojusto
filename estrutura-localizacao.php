<?php

/*
$distritos = $objImportacaoWordpress->callCurl('https://v2qa.custojusto.pt/locations', ['Content-Type: application/json', 'Authorization: 323BW83AEG952ZnnPSCA'], [] , 'GET', FALSE);
$arrDistritos = json_decode($distritos, true);
$arrLocalizacoes = [];
foreach ($arrDistritos['locations'] as $distrito) {
	$conselhos = $objImportacaoWordpress->callCurl('https://v2qa.custojusto.pt/locations/' . $distrito['locationId'], ['Content-Type: application/json', 'Authorization: 323BW83AEG952ZnnPSCA'], [] , 'GET', FALSE);	
	$arrConselhos = json_decode($conselhos, true);

	$arrConselho = [];	
	foreach ($arrConselhos['locations'] as $conselho) {

		$freguesias = $objImportacaoWordpress->callCurl(
								'https://v2qa.custojusto.pt/locations/' .
									$distrito['locationId'].
									'/'.
									$conselho['locationId'], 
								[
									'Content-Type: application/json',
									'Authorization: 323BW83AEG952ZnnPSCA'
								],
								[],
								'GET',
								FALSE
						);	
		$arrFreguesias = json_decode($freguesias, true);
		$arrFreguesia = [];
		foreach ($arrFreguesias['locations'] as $freguesia) {
			$arrFreguesia[] =
			[
					'freguesia_id' => $freguesia['locationId'],
					'freguesia_nome' => $freguesia['locationName'],
			];				
		}
	
		$arrConselho[$conselho['locationId']] = [
				'conselho_id' => $conselho['locationId'],
				'conselho_nome' => $conselho['locationName'],
				'freguesia' => $arrFreguesia	
		];
	
		$arrItens = [
			'distrito' => [
				'distrito_id' => $distrito['locationId'],
				'distrito_nome' => $distrito['locationName'],
				'conselho' => [
					$arrConselho	
				]
			]
		];
	
	}
	
	$arrLocalizacoes[] = $arrItens;
}

file_put_contents('localizacoes.json', json_encode($arrLocalizacoes));
die('Fim json localizacoes');
*/



/*
$categorias = $objImportacaoWordpress->callCurl('https://v2qa.custojusto.pt/partner/categories', ['Content-Type: application/json', 'Authorization: 323BW83AEG952ZnnPSCA'], [] , 'GET', FALSE);

file_put_contents('categorias.json', $categorias);
*/

$jsonLocalizacoes = file_get_contents('localizacoes.json');


$arrLocalizacoes = json_decode($jsonLocalizacoes, true);

if (count($_GET) == 0) {
	return false;
}

if (isset($_GET['distritos']) && $_GET['distritos'] == 1) {
	$arrDistritos = [];
	foreach ($arrLocalizacoes as $localizacao) {
		$arrDistritos[$localizacao['distrito']['distrito_id']] = $localizacao['distrito']['distrito_nome'];
	}
	
	header('Content-Type: application/json');
	echo json_encode($arrDistritos);
	die();
}

if (isset($_GET['distrito']) && $_GET['distrito'] >= 1) {
	$arrConselhos = [];
	foreach ($arrLocalizacoes as $localizacao) {
		if ($localizacao['distrito']['distrito_id'] == $_GET['distrito']) {
			foreach($localizacao['distrito']['conselho'] as $conselhos_key => $conselhos) {
				foreach ($conselhos as $key => $value) {
					$arrConselhos[$value['conselho_id']] = $value['conselho_nome'];	
				}
			}
			
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode($arrConselhos);
	die();
}

if (isset($_GET['conselho']) && $_GET['conselho'] >= 1) {
	$arrFreguesias = [];
	foreach ($arrLocalizacoes as $localizacao) {
		foreach ($localizacao['distrito']['conselho'] as $conselhos) {
			foreach ($conselhos as $key => $freguesias) {
				if ($key == $_GET['conselho']) {
					foreach ($freguesias['freguesia'] as $freguesia) {
						$arrFreguesias[$freguesia['freguesia_id']] = $freguesia['freguesia_nome'];
					}
				}
			}
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode($arrFreguesias);
	die();
}
