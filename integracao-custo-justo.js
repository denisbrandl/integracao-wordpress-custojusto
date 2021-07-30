jQuery(document).ready(function() {
	jQuery.ajax({
		url: custoJustoVariaveis.pluginsUrl + "/estrutura-localizacao.php",
		type: "GET",
		data: "distritos=1",
		datatype: "json",
		success: function(dados) {
			jQuery.each(dados, function(k, v) {		
				if (custoJustoVariaveis.distrito == k) {
					jQuery('#integracao_custo_justo_distrito').append('<option selected="selected" value=' + k + '>' + v + '</option>');
				} else {
					jQuery('#integracao_custo_justo_distrito').append('<option value=' + k + '>' + v + '</option>');
				}
			});
			consultaConselhosPorDistrito();
		}
	});
	
	function consultaConselhosPorDistrito() {
		jQuery('#integracao_custo_justo_conselho').find('option').remove().end();
		jQuery.ajax({
			url: custoJustoVariaveis.pluginsUrl + "/estrutura-localizacao.php",
			type: "GET",
			data: "distrito=" + jQuery('#integracao_custo_justo_distrito').val(),
			datatype: "json",
			success: function(dados) {
				jQuery.each(dados, function(k, v) {
					if (custoJustoVariaveis.conselho == k) {
						jQuery('#integracao_custo_justo_conselho').append('<option selected="selected" value=' + k + '>' + v + '</option>');
					} else {
						jQuery('#integracao_custo_justo_conselho').append('<option value=' + k + '>' + v + '</option>');						
					}
				});
				consultaFreguesiasPorConselhos();
			}
		});
	}
	
	function consultaFreguesiasPorConselhos() {
		jQuery('#integracao_custo_justo_freguesia').find('option').remove().end();
		jQuery.ajax({
			url: custoJustoVariaveis.pluginsUrl + "/estrutura-localizacao.php",
			type: "GET",
			data: "conselho=" + jQuery('#integracao_custo_justo_conselho').val(),
			datatype: "json",
			success: function(dados) {
				jQuery.each(dados, function(k, v) {
					if (custoJustoVariaveis.freguesia == k) {
						jQuery('#integracao_custo_justo_freguesia').append('<option selected="selected" value=' + k + '>' + v + '</option>');
					} else {
						jQuery('#integracao_custo_justo_freguesia').append('<option value=' + k + '>' + v + '</option>');
					}
				});
			}
		});
	}	
	
	jQuery('#integracao_custo_justo_distrito').change(function() {
		consultaConselhosPorDistrito();
	});
	
	jQuery('#integracao_custo_justo_conselho').change(function() {
		consultaFreguesiasPorConselhos();
	});	
});