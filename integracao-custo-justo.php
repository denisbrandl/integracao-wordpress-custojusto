<?php
/*
Plugin Name: Customizações - Integração Custo Justo
Description: Integração com a API Custo Justo
Version: 1.0
License: GPL
*/

/* PAINEL DE SINCRONIZAÇÃO */
add_action('admin_menu', 'integracao_custo_justo_create_menu');

function integracao_custo_justo_create_menu() {

	//create new top-level menu
	add_menu_page('Integração Custo Justo', 'Integração Custo Justo', 'administrator', 'integracao-custo-justo-main', 'integracao_custo_justo_plugin_settings_page' , 'dashicons-share-alt' );
	
	add_submenu_page( 'integracao-custo-justo-main', 'Configurações de acesso - Custo Justo', 'Configurações de acesso',
    'administrator', 'integracao-custo-justo-configuracoes', 'integracao_custo_justo_configuracoes');
	
	add_submenu_page( 'integracao-custo-justo-main', 'Sincronização Manual', 'Sincronização Manual',
    'administrator', 'integracao-custo-justo-sincronizacao-manual', 'integracao_custo_justo_plugin_settings_page');	

	//call register settings function
	add_action( 'admin_init', 'register_integracao_custo_justo_plugin_settings' );
	
	remove_submenu_page('integracao-custo-justo-main','integracao-custo-justo-main');
}


function register_integracao_custo_justo_plugin_settings() {
	register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_chave_custo_justo' );
	register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_token_plugin');
}

function integracao_custo_justo_configuracoes()
{
   
?>
<div class="wrap">
<h1>Configurações de acesso</h1>

<form method="post" action="options.php" id="frmConfiguracaoCustoJusto">
    <?php settings_fields( 'integracao-custo-justo-settings-group' ); ?>
    <?php do_settings_sections( 'integracao-custo-justo-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Token Custo Justo</th>
        <td><input type="text" style="width:50%" name="integracao_custo_justo_chave_custo_justo" value="<?php echo esc_attr( get_option('integracao_custo_justo_chave_custo_justo') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Token Plugin</th>
        <td><input type="text" style="width:50%" name="integracao_custo_justo_token_plugin" value="<?php echo esc_attr( get_option('integracao_custo_justo_token_plugin') ); ?>" /></td>
        </tr>		
		
    </table>
    
    <?php submit_button(); ?>

</form>
</div>

<script language="text/javascript">

</script>


<?php
}

function integracao_custo_justo_scripts_js($hook) {

	$url_plugin = plugin_dir_url( __FILE__ );	
	
    wp_enqueue_script('integracao_custo_justo_scripts_js', plugin_dir_url(__FILE__) . '/integracao-custo-justo.js?'.date('his'));
	wp_localize_script('integracao_custo_justo_scripts_js', 'custoJustoVariaveis', array(
		'pluginsUrl' => $url_plugin,
		'distrito' => get_option('integracao_custo_justo_distrito'),
		'conselho' => get_option('integracao_custo_justo_conselho'),
		'freguesia' => get_option('integracao_custo_justo_freguesia')
	));
}

add_action('admin_enqueue_scripts', 'integracao_custo_justo_scripts_js');


function integracao_custo_justo_plugin_settings_page() {
	
	$url_plugin = plugin_dir_url( __FILE__ );
?>
<div class="wrap">
<h1>Sincronização de anúncios - Integração Custo Justo</h1>

<style>
#progress {
  width: 100%;
  background-color: #ddd;
}

#barraProgresso {
  width: 1%;
  height: 30px;
  background-color: #04AA6D;
  color: #fff;
}
</style>

<form method="post" action="options.php" id="frmSincronizacaoCustoJusto">
    <?php settings_fields( 'integracao-custo-justo-settings-group' ); ?>
    <?php do_settings_sections( 'integracao-custo-justo-settings-group' ); ?>
	
	<?php
	
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_email');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_nome_empresa');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_telefone');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_codigo_postal');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_distrito');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_conselho');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_freguesia');
		register_setting( 'integracao-custo-justo-settings-group', 'integracao_custo_justo_tipo_negocio');	
	
		$taxonomy     = 'product_cat';
		$orderby      = 'name';  
		$show_count   = 1;
		$pad_counts   = 0;
		$hierarchical = 1;
		$title        = '';  
		$empty        = 0;
		
		$categoria_produtos_wordpress = '';

		$args = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty
		);
		$all_categories = get_categories( $args );
		foreach ($all_categories as $cat) {
			if($cat->category_parent == 0) {
				$category_id = $cat->term_id;
				$categoria_produtos_wordpress .= sprintf('<option value="%s">%s (%s) </option>', $category_id, !empty($cat->description) ? $cat->description : $cat->name, $cat->count  );

				$args2 = array(
							'taxonomy'     => $taxonomy,
							'child_of'     => 0,
							'parent'       => $category_id,
							'orderby'      => $orderby,
							'show_count'   => $show_count,
							'pad_counts'   => $pad_counts,
							'hierarchical' => $hierarchical,
							'title_li'     => $title,
							'hide_empty'   => $empty
						);
				$sub_cats = get_categories( $args2 );
				if($sub_cats) {
					foreach($sub_cats as $sub_category) {
						$categoria_produtos_wordpress .= sprintf('<option value="%s">-- %s (%s)</option>', $sub_category->term_id, !empty($sub_category->description) ? $sub_category->description : $sub_category->name, $sub_category->count);
					}   
				}
			}       
		}


		$jsonCategoriasCustoJusto = file_get_contents($url_plugin.'categorias.json');
		$arrCategoriasCustoJusto = json_decode($jsonCategoriasCustoJusto, true);
		
		$categoria_produtos_custo_justo = '';
		foreach ($arrCategoriasCustoJusto as $key => $categoria) {
			$categoria_produtos_custo_justo .= sprintf('<optgroup label="%s">', $categoria['name']);
			if ($categoria['hasChildren'] == '1') {
				foreach ($categoria['subCategory'] as $subcategoria) {
					if ($subcategoria['hasChildren'] == '1') {
						$categoria_produtos_custo_justo .= sprintf('<option value="%s" disabled>- %s</option>', $subcategoria['subCategoryID'], $subcategoria['name']);
						foreach ($subcategoria['subSubCategory'] as $subsubcategoria) {
							$categoria_produtos_custo_justo .= sprintf('<option value="%s">-- %s</option>', $subsubcategoria['subSubCategoryID'], $subsubcategoria['name']);
						}
					} else {
						$categoria_produtos_custo_justo .= sprintf('<option value="%s">- %s</option>', $subcategoria['subCategoryID'], $subcategoria['name']);
					}
				}				
			}
			$categoria_produtos_custo_justo .= sprintf('</optgroup>');
		}
		
	?>
	
    <table class="form-table">
		<?php 
			$sincronizacao_em_andamento = file_exists($caminho_plugin.'status.json');
			if (!$sincronizacao_em_andamento) {
		?>	
		
		
		<tr valign="top">
        <th scope="row">Categoria dos produtos - Origem</th>
        <td><select name="categoria_produtos_wordpress" id="categoria_produtos_wordpress"><?php echo $categoria_produtos_wordpress; ?></select></td>
        </tr>
		
		<tr valign="top">
        <th scope="row">Categoria dos produtos - Destino (Custo Justo)</th>
        <td><select name="categoria_produtos_custo_justo" id="categoria_produtos_custo_justo"><?php echo $categoria_produtos_custo_justo; ?></select></td>
        </tr>		
		
        <tr valign="top">
        <th scope="row">Nome da empresa</th>
        <td><input type="text" style="width:50%" id="integracao_custo_justo_nome_empresa" name="integracao_custo_justo_nome_empresa" value="<?php echo esc_attr( get_option('integracao_custo_justo_nome_empresa') ); ?>" /></td>
        </tr>			
		
        <tr valign="top">
        <th scope="row">E-mail</th>
        <td><input type="text" style="width:50%" id="integracao_custo_justo_email" name="integracao_custo_justo_email" value="<?php echo esc_attr( get_option('integracao_custo_justo_email') ); ?>" /></td>
        </tr>		

        <tr valign="top">
        <th scope="row">Telefone<br><small style="color:red;">(Não utilize o código DDI)</small></th>
        <td><input type="text" style="width:50%" id="integracao_custo_justo_telefone" name="integracao_custo_justo_telefone" value="<?php echo esc_attr( get_option('integracao_custo_justo_telefone') ); ?>" /></td>
        </tr>		
		
        <tr valign="top">
        <th scope="row">Código Postal</th>
        <td><input type="text" style="width:50%" id="integracao_custo_justo_codigo_postal" name="integracao_custo_justo_codigo_postal" value="<?php echo esc_attr( get_option('integracao_custo_justo_codigo_postal') ); ?>" /></td>
        </tr>				
		
        <tr valign="top">
        <th scope="row">Distrito</th>
        <td><select name="integracao_custo_justo_distrito" id="integracao_custo_justo_distrito">
		</select></td>
        </tr>	

        <tr valign="top">
        <th scope="row">Conselho</th>
        <td><select name="integracao_custo_justo_conselho" id="integracao_custo_justo_conselho"></select></td>
        </tr>		
		
		<tr valign="top">
        <th scope="row">Freguesia</th>
        <td><select name="integracao_custo_justo_freguesia" id="integracao_custo_justo_freguesia"></select></td>
        </tr>				
		
        <tr valign="top">
        <th scope="row">Tipo de negócio</th>
        <td>
			<label for="tipoNegocioParticular"><input   <?php echo get_option('integracao_custo_justo_tipo_negocio') == 1 ? 'checked' : ''; ?> type="radio" name="integracao_custo_justo_tipo_negocio" value="1" id="tipoNegocioParticular">Particular</label>
			<label for="tipoNegocioProfissional"><input <?php echo get_option('integracao_custo_justo_tipo_negocio') == 2 ? 'checked' : ''; ?> type="radio" name="integracao_custo_justo_tipo_negocio" value="2" id="tipoNegocioProfissional">Profissional</label>
		</td>
        </tr>		
		
        <tr valign="top">
        <th scope="row">Iniciar sincronização manual</th>
        <td><input type="button" name="iniciaSincronizacao" id="iniciaSincronizacao" value="Iniciar sincronização" /></td>
        </tr>
        <tr>
			<td colspan="2">O processo de sincronização pode levar vários minutos, por favor seja paciente ;)</td>
        </tr>
        <?php } else { ?>
        <tr>
			<td colspan="2">Desculpe, já existe outra sincronização em andamento!</td>
        </tr>        
        <?php } ?>
    </table>
	<div>
		<textarea style="width:100%;min-height:30%;" disabled id="logIntegracao"></textarea>
	</div>
	<div class="progress" style="display:none;">
		<div class="progress-bar" id="barraProgresso" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="">
			0%
		</div>
	</div>    
</form>
</div>
<script type="text/javascript">
	function sincronizacaoManual() {
		var logIntegracao = '';
		var integracao_custo_justo_tipo_negocio = '1';
		if(jQuery('#tipoNegocioProfissional').is(':checked')) {
			integracao_custo_justo_tipo_negocio = '2';	
		}
		jQuery.ajax({
			url: "<?php echo $url_plugin;?>importacao.php",
			type: "GET",
			data: "categoria_produtos_wordpress="+jQuery('#categoria_produtos_wordpress').val()+
				  "&categoria_produtos_custo_justo="+jQuery('#categoria_produtos_custo_justo').val()+
				  "&integracao_custo_justo_email="+jQuery('#integracao_custo_justo_email').val() +
				  "&integracao_custo_justo_nome_empresa="+jQuery('#integracao_custo_justo_nome_empresa').val() +
				  "&integracao_custo_justo_telefone="+jQuery('#integracao_custo_justo_telefone').val().replace(/(\+[0-9]{2})/gm, '') +
				  "&integracao_custo_justo_codigo_postal="+jQuery('#integracao_custo_justo_codigo_postal').val() +
				  "&integracao_custo_justo_distrito="+jQuery('#integracao_custo_justo_distrito').val() +
				  "&integracao_custo_justo_conselho="+jQuery('#integracao_custo_justo_conselho').val() +
				  "&integracao_custo_justo_freguesia="+jQuery('#integracao_custo_justo_freguesia').val() +
				  "&integracao_custo_justo_tipo_negocio="+integracao_custo_justo_tipo_negocio,
			datatype: "JSON",
			success: function(data) {
				jQuery.each(data, function(key, value) {
					if (value.success == '-1') {
						logIntegracao = logIntegracao + value.msg + '\n';						
					} else {
						jQuery.each(value.produtos, function(key1, value1) {
							if (value1.success == '1') {
								logIntegracao = logIntegracao + 'Produto: ' + value1.id_produto + ' atualizado no Custo Justo com o ID ' + value1.id_custojusto + '\n';
							} else {
								logIntegracao = logIntegracao + 'Erro ao integrar o produto ' + value1.id_produto + ' - ' + value1.msg + '\n';
							}
						});
					}
					jQuery("#logIntegracao").val(logIntegracao);
				});
				jQuery('#iniciaSincronizacao').prop('disabled', false);
			}
		});
		t = setTimeout("updateStatus()", 3000);
	}
	
	function updateStatus() {  
		jQuery.getJSON('<?php echo $url_plugin;?>status.json', function(data){ 
			var items = [];
			pbvalue = 0;
			if(data){
				var total = data['total'];
				var current = data['current']; 
				var pbvalue = Math.floor((current / total) * 100);
				if(pbvalue>0){ 
				
					document.getElementById("barraProgresso").setAttribute("aria-valuenow", "50%");
					
					document.getElementById("barraProgresso").style.width = pbvalue + "%";

					document.getElementById("barraProgresso").innerHTML = pbvalue + "%";
					
					if (pbvalue >= 100) {
					 document.getElementById("barraProgresso").classList.add("progress-bar-success");
					}
				} 
			} 

			if(pbvalue < 100){ 
				t = setTimeout("updateStatus()", 3000); 
			} 
		});   
	}	
	
	jQuery('#iniciaSincronizacao').click( function() {
		document.getElementById("barraProgresso").setAttribute("aria-valuenow", "0%");		
		document.getElementById("barraProgresso").style.width = "0%";
		document.getElementById("barraProgresso").innerHTML = "0%";
		document.getElementById("barraProgresso").classList.remove("progress-bar-success");
		jQuery("#logIntegracao").val('');
		jQuery('.progress').show();
		jQuery('#iniciaSincronizacao').prop('disabled', true);
		sincronizacaoManual();
	});
</script>	
<?php } ?>