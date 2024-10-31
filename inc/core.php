<?php
add_action('admin_menu', 'mwp_inter_wc_add_custom_menu');
function mwp_inter_wc_add_custom_menu() {
    add_submenu_page(
        'woocommerce',
        'Banco Inter',
        'Banco Inter',
        'manage_options',
        'mwp-inter-woocommerce-settings',
        'mwp_inter_wc_custom_menu_callback'
    );
}
function mwp_inter_wc_register_settings() {
    register_setting('mwp_inter_wc_settings', 'mwp_inter_wc_token');
    register_setting('mwp_inter_wc_settings', 'mwp_inter_wc_secret_token');
    register_setting('mwp_inter_wc_settings', 'mwp_inter_wc_conta_corrente');
    register_setting('mwp_inter_wc_settings', 'mwp_inter_wc_chave_pix');
    register_setting('mwp_inter_wc_settings', 'mwp_inter_wc_beneficiario');
    register_setting('mwp_inter_wc_settings', 'mwp_inter_wc_cidade');
}
function mwp_inter_wc_save_settings() {
	if (!isset($_POST['mwp_inter_wc_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mwp_inter_wc_settings_nonce'])), 'mwp_inter_wc_settings_nonce')) {
		wp_die(esc_html__('Ação não autorizada!', 'mwp-gateway-banco-inter'));
	}
    update_option('mwp_inter_wc_token', sanitize_text_field($_POST['mwp_inter_wc_token']));
    update_option('mwp_inter_wc_secret_token', sanitize_text_field($_POST['mwp_inter_wc_secret_token']));
    update_option('mwp_inter_wc_conta_corrente', sanitize_text_field($_POST['mwp_inter_wc_conta_corrente']));
    update_option('mwp_inter_wc_chave_pix', sanitize_text_field($_POST['mwp_inter_wc_chave_pix']));
    update_option('mwp_inter_wc_beneficiario', sanitize_text_field($_POST['mwp_inter_wc_beneficiario']));
    update_option('mwp_inter_wc_cidade', sanitize_text_field($_POST['mwp_inter_wc_cidade']));
	if (!empty($_FILES['mwp_inter_wc_patch_key']['tmp_name'])) {
		$uploaded_file = array(
			'name'     => sanitize_file_name($_FILES['mwp_inter_wc_patch_key']['name']),
			'type'     => sanitize_mime_type($_FILES['mwp_inter_wc_patch_key']['type']),
			'tmp_name' => sanitize_text_field($_FILES['mwp_inter_wc_patch_key']['tmp_name']),
			'error'    => intval($_FILES['mwp_inter_wc_patch_key']['error']),
			'size'     => intval($_FILES['mwp_inter_wc_patch_key']['size'])
		);
		$upload_overrides = array(
			'test_form' => false,
			'mimes' => array(
				'key' => 'application/octet-stream'
			)
		);
		$movefile = wp_handle_upload($uploaded_file, $upload_overrides);
		if ($movefile && !isset($movefile['error'])) {
			$new_file_path = $movefile['file'];
			$random_name = substr(md5_file($new_file_path), 0, 15);
			$file_extension = pathinfo($new_file_path, PATHINFO_EXTENSION);
			$new_file_path_with_random_name = dirname($new_file_path) . '/' . $random_name . '.' . $file_extension;
			if (rename($new_file_path, $new_file_path_with_random_name)) {
				update_option('mwp_inter_wc_patch_key_path', sanitize_text_field($new_file_path_with_random_name));
			} else {
				error_log('Falha ao renomear o arquivo Patch Key para ' . sanitize_text_field($random_name));
			}
		} else {
			error_log('Erro durante o upload do arquivo Patch Key: ' . print_r($movefile, true));
		}
	}
	if (!empty($_FILES['mwp_inter_wc_patch_ctr']['tmp_name'])) {
		$uploaded_file = array(
			'name'     => sanitize_file_name($_FILES['mwp_inter_wc_patch_ctr']['name']),
			'type'     => sanitize_mime_type($_FILES['mwp_inter_wc_patch_ctr']['type']),
			'tmp_name' => sanitize_text_field($_FILES['mwp_inter_wc_patch_ctr']['tmp_name']),
			'error'    => intval($_FILES['mwp_inter_wc_patch_ctr']['error']),
			'size'     => intval($_FILES['mwp_inter_wc_patch_ctr']['size'])
		);
		$upload_overrides = array(
			'test_form' => false,
			'mimes' => array(
				'crt' => 'application/x-x509-ca-cert'
			)
		);
		$movefile = wp_handle_upload($uploaded_file, $upload_overrides);
		if ($movefile && !isset($movefile['error'])) {
			$new_file_path = $movefile['file'];
			$random_name = substr(md5_file($new_file_path), 0, 15);
			$file_extension = pathinfo($new_file_path, PATHINFO_EXTENSION);
			$new_file_path_with_random_name = dirname($new_file_path) . '/' . $random_name . '.' . $file_extension;
			if (rename($new_file_path, $new_file_path_with_random_name)) {
				update_option('mwp_inter_wc_patch_ctr_path', sanitize_text_field($new_file_path_with_random_name));
			} else {
				error_log('Falha ao renomear o arquivo Patch CTR para ' . sanitize_text_field($random_name));
			}
		} else {
			error_log('Erro durante o upload do arquivo Patch CTR: ' . print_r($movefile, true));
		}
	}
    wp_redirect(admin_url('admin.php?page=mwp-inter-woocommerce-settings&updated=true'));
    exit;
}
function mwp_inter_get_auth_token($scope) {
	$dados = "client_id=" . get_option('mwp_inter_wc_token') . "&client_secret=" . get_option('mwp_inter_wc_secret_token') . "&scope=".$scope."&grant_type=client_credentials";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/oauth/v2/token");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'
	));
	curl_setopt($ch, CURLOPT_SSLCERT, get_option('mwp_inter_wc_patch_ctr_path'));
	curl_setopt($ch, CURLOPT_SSLKEY, get_option('mwp_inter_wc_patch_key_path'));
	$response = curl_exec($ch);
	$token = json_decode($response);
	curl_close($ch);
	return $token->access_token;
}
function mwp_inter_request($url,$dados,$token,$method){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_POST, true);
	if(!empty($dados)){
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '.$token,
		'Content-Type: application/json',
	));
	curl_setopt($ch, CURLOPT_SSLCERT, get_option('mwp_inter_wc_patch_ctr_path'));
	curl_setopt($ch, CURLOPT_SSLKEY, get_option('mwp_inter_wc_patch_key_path'));
	$response = curl_exec($ch);
	$response = json_decode($response);
	curl_close($ch);
	return $response;
}
function mwp_inter_create_file_base64($code,$folder,$name,$extension){
	$imageData = base64_decode($code);
	$upload_dir = wp_upload_dir();
	$inter_dir = trailingslashit( $upload_dir['basedir'] ) . $folder;
	if ( ! file_exists( $inter_dir ) ) {
		mkdir( $inter_dir );
	}
	$outputFile = trailingslashit( $inter_dir ) . $name . '.'.$extension;
	file_put_contents( $outputFile, $imageData );
}
add_action('admin_post_save_mwp_inter_wc_settings', 'mwp_inter_wc_save_settings');
add_action('admin_init', 'mwp_inter_wc_register_settings');