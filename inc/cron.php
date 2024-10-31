<?php
function mwp_inter_cron() {
	global $wpdb;
	$token = mwp_inter_get_auth_token("boleto-cobranca.write boleto-cobranca.read webhook.write");
	$queryString = http_build_query([
		'dataInicial' => date('Y-m-d', strtotime('-7 days')),
		'dataFinal' => date('Y-m-d', strtotime('+7 days')),
		'situacao' => 'RECEBIDO',
		'tipoOrdenacao' => 'ASC',
		'itensPorPagina' => 200,
		'paginaAtual' => 1
	]);
	$response = mwp_inter_request("https://cdpj.partners.bancointer.com.br/cobranca/v3/cobrancas?".$queryString,"",$token,"GET");
	foreach($response->cobrancas as $cobrancas){
		if($cobrancas->cobranca->situacao == "RECEBIDO"){
			$order = wc_get_order($cobrancas->cobranca->seuNumero);
			if ( ! empty( $order ) && ! is_wp_error( $order ) ) {
				if($order->get_status()=="pending"){
					$order->update_status('processing', 'order_note');
					$order->reduce_order_stock();
				}
				
			}
		}
	}
}
function mwp_inter_create_cron() {
    if ( ! wp_next_scheduled( 'mwp_inter_cron' ) ) {
        wp_schedule_event( time(), 'hourly', 'mwp_inter_cron' );
    }
}
add_action( 'wp', 'mwp_inter_create_cron' );
add_action( 'mwp_inter_cron', 'mwp_inter_cron' );