<?php
add_action('rest_api_init', 'mwp_inter_endpoint');
function mwp_inter_endpoint() {
    register_rest_route('inter/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'mwp_inter_webhook_callback',
		'permission_callback' => '__return_true',
    ));
}
function mwp_inter_webhook_callback(WP_REST_Request $request) {
	global $wpdb;
    $params = $request->get_params();
    $params_string = print_r($params, true);
    $log_file = WP_CONTENT_DIR . '/webhook_log.txt';
    $log_message = "Webhook Callback Data:\n" . $params_string . "\n";
    error_log($log_message, 3, $log_file);
	if(isset($params['pix'][0]['txid'])){
		$txid_value = $params['pix'][0]['txid'];
		$query = $wpdb->prepare("
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = 'txid' 
			AND meta_value = %s
		", $txid_value);
		$order_id = $wpdb->get_var($query);
		if ($order_id) {
			$order = wc_get_order($order_id);
			if ($order) {
				$order->update_status('processing', 'order_note');
				$order->reduce_order_stock();
			} else {
				echo 'Erro ao carregar o pedido.';
			}
		} else {
			echo 'Pedido não encontrado para o txid fornecido.';
		}
	}
	if(isset($params['boleto'][0]['nossoNumero'])){
		if($params['boleto'][0]['situacao']=="PAGO"){
			$txid_value = $params['boleto'][0]['nossoNumero'];
			$query = $wpdb->prepare("
				SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = 'nossoNumero' 
				AND meta_value = %s
			", $txid_value);
			$order_id = $wpdb->get_var($query);
			if ($order_id) {
				$order = wc_get_order($order_id);
				if ($order) {
					$order->update_status('processing', 'order_note');
					$order->reduce_order_stock();
				} else {
					echo 'Erro ao carregar o pedido.';
				}
			} else {
				echo 'Pedido não encontrado para o txid fornecido.';
			}
		}
	}
}