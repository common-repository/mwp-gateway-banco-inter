<?php
include "phpqrcode/qrlib.php"; 
add_filter( 'woocommerce_payment_gateways', 'mwp_inter_pix_gateway_class' );
function mwp_inter_pix_gateway_class( $gateways ) {
	$gateways[] = 'mwp_inter_pix';
	return $gateways;
}
add_action( 'plugins_loaded', 'mwp_inter_pix_init_gateway_class' );
function mwp_inter_pix_init_gateway_class() {
	class mwp_inter_pix extends WC_Payment_Gateway {
 		public function __construct() {
			$this->id = 'mwp_inter_pix';
			$this->icon = '';
			$this->has_fields = true;
			$this->method_title = __( 'Banco Inter | Pix', 'mwp_inter' );
			$this->method_description = __( 'Pague com pix através do Banco Inter', 'mwp_inter' );
			$this->supports = array(
				'products'
			);
			$this->init_form_fields();
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
 		}
 		public function init_form_fields(){
			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'mwp_inter' ),
					'label'       => __( 'Habilitar Pix', 'mwp_inter' ),
					'type'        => 'checkbox',
					'description' => __( 'Habilite para receber pagamentos através de pix no Banco Inter', 'mwp_inter' ),
					'default'     => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'mwp_inter' ),
					'type'        => 'text',
					'default'     => __( 'Pix', 'mwp_inter' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'mwp_inter' ),
					'type'        => 'textarea',
					'default'     => __( 'Pague com Pix através do Banco Inter', 'mwp_inter' ),
				),
			);
	 	}
		public function payment_fields() {
			if ( $this->description ) {

			}
		}
	 	public function payment_scripts() {
			if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
				return;
			}
			if ( 'no' === $this->enabled ) {
				return;
			}
			
			wp_enqueue_style( 'mwp-inter-style', MWP_INTER_PLUGIN_URL . 'assets/css/style.css' );

	 	}
		public function validate_fields() {
			if( empty( $_POST[ 'billing_first_name' ]) ) {
				wc_add_notice(  'First name is required!', 'error' );
				return false;
			}

			return true;
		}
		public function thankyou_page($order_id) {
			$order = wc_get_order($order_id);
			$pix = get_post_meta($order->get_id(), 'qrcode', true);
			if($order->get_status()=="processing"){
				echo '
				<section class="mwp-inter-thankyou" id="'.$this->id.'-thankyou">
					<div class="content">
						<svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="50" cy="50" r="50" fill="#43D19E"/>
						<circle cx="68" cy="63" r="11" fill="white"/>
						<path d="M54.2326 63.3333C54.2326 62.5778 54.32 61.8444 54.4293 61.1111H32.3721V47.7778H67.3488V50C68.8791 50 70.3437 50.2889 71.7209 50.7778V34.4444C71.7209 31.9778 69.7754 30 67.3488 30H32.3721C29.9456 30 28 31.9778 28 34.4444V61.1111C28 62.2899 28.4606 63.4203 29.2806 64.2538C30.1005 65.0873 31.2125 65.5556 32.3721 65.5556H54.4293C54.32 64.8222 54.2326 64.0889 54.2326 63.3333ZM32.3721 34.4444H67.3488V38.8889H32.3721V34.4444Z" fill="white"/>
						<path d="M62 63.1299L66.4 68L74 60.289L72.144 58L66.4 63.8279L63.856 61.2468L62 63.1299Z" fill="#43D19E"/>
						</svg>
						<h3>'.__( 'Obrigado', 'mwp_inter' ).' '.$order->get_billing_first_name().'!</h3>
						<p>'.__( 'Sua compra foi efetuada com sucesso e em breve você irá receber mais informações sobre sua encomenda!', 'mwp_inter' ).'</p>
						<div class="button-thankyou">
							<button>Minha conta</button>
							<button>Voltar para a loja</button>
						</div>
					</div>
				</section>
				';
			}else{
				echo '
				<section class="mwp-inter-thankyou" id="'.$this->id.'-thankyou">
					<div class="content">
						<h3>'.__( 'Olá, ', 'mwp_inter' ).' '.$order->get_billing_first_name().',<br/>'.__( 'Pague com pix!', 'mwp_inter' ).'</h3>
						<p>'.__( 'Abra o aplicativo do seu banco em seu telefone e escaneie o QRCode.', 'mwp_inter' ).'</p>
						<img src="'. $pix . '" width="200" height="auto" />
						<p>'.__( 'ou efetue o pagamento com o código copia e cola abaixo:', 'mwp_inter' ).'</p>
						<div class="button-thankyou-copy">
							<input value="'.get_post_meta($order->get_id(), 'copypast', true).'" />
							<button><svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 0H3.03571C2.9375 0 2.85714 0.0803571 2.85714 0.178571V1.42857C2.85714 1.52679 2.9375 1.60714 3.03571 1.60714H14.1071V16.9643C14.1071 17.0625 14.1875 17.1429 14.2857 17.1429H15.5357C15.6339 17.1429 15.7143 17.0625 15.7143 16.9643V0.714286C15.7143 0.319196 15.3951 0 15 0ZM12.1429 2.85714H0.714286C0.319196 2.85714 0 3.17634 0 3.57143V15.4174C0 15.6071 0.0758927 15.7879 0.209821 15.9219L4.07813 19.7902C4.12723 19.8393 4.18304 19.8795 4.2433 19.9129V19.9554H4.33705C4.41518 19.9844 4.49777 20 4.58259 20H12.1429C12.5379 20 12.8571 19.6808 12.8571 19.2857V3.57143C12.8571 3.17634 12.5379 2.85714 12.1429 2.85714ZM4.95536 18.5714H4.95089L1.60714 15.2277V15.2232H4.95536V18.5714Z" fill="white"/></svg>Copiar</button>
						</div>
					</div>
				</section>
				<script type="text/javascript">
				jQuery(document).ready(function($) {
				$(".button-thankyou-copy button").click(function(){
					navigator.clipboard.writeText($(".button-thankyou-copy input").val());
				});
				});
				</script>
				';
			}
		}
		public function process_payment($order_id){
			global $woocommerce;
			$order = wc_get_order($order_id);
			$token = mwp_inter_get_auth_token("cob.write webhook.write");
			$dados = array(
				"calendario" => array(
					"expiracao" => 3600
				),
				"devedor" => array(
					"cpf" => preg_replace('/[^0-9]/', '', $order->get_meta( '_billing_cpf' )),
					"nome" => $order->get_billing_first_name()." ".$order->get_billing_last_name()
				),
				"valor" => array(
					"original" => $order->get_total(),
					"modalidadeAlteracao" => 1
				),
				"chave" => get_option('mwp_inter_wc_chave_pix'),
				"solicitacaoPagador" => "Pedido ".$order->get_id()." realizado na loja ".get_bloginfo('name')." ".get_bloginfo('url'),
			);
			$response = mwp_inter_request("https://cdpj.partners.bancointer.com.br/pix/v2/cob",json_encode($dados),$token,"POST");
			ob_start();
			QRCode::png($response->pixCopiaECola, null,'M',5);
			$imageString = base64_encode( ob_get_contents() );
			ob_end_clean();
			mwp_inter_create_file_base64($imageString,"inter",$order_id,"png");
			$upload_dir = wp_upload_dir();
			$diretorio = trailingslashit( $upload_dir['baseurl'] ) . 'inter';
			update_post_meta($order->get_id(), 'txid', $response->txid);
			update_post_meta($order->get_id(), 'copypast', $response->pixCopiaECola);
			update_post_meta($order->get_id(), 'qrcode', $diretorio.'/'.$order_id . '.png');
			$json = '{"webhookUrl": "'.get_bloginfo('url').'/wp-json/inter/v1/webhook"}';
			mwp_inter_request("https://cdpj.partners.bancointer.com.br/pix/v2/webhook/".get_option('mwp_inter_wc_chave_pix'),$json,$token,"PUT");
			$order->update_status('pending', 'order_note');
			$woocommerce->cart->empty_cart();
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			);
		}
 	}
}