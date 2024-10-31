<?php
/**
* Plugin Name: MWP Gateway for Banco Inter
* Plugin URI: https://www.mestresdowp.com.br/banco-inter/
* Description: Plugin gratuito para integração com o Banco Inter para pagamentos via Boleto Bancário e Pix com retorno automático..
* Version: 1.2
* Author: Mestres do WP
* Author URI: http://www.mestresdowp.com.br
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: mwp-gateway-banco-inter
 */
 /*
Copyright 2021  Mestres do WP  (email : contato@mestresdowp.com.br)
*/
defined('ABSPATH') || exit;
define('MWP_INTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MWP_INTER_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once MWP_INTER_PLUGIN_DIR . 'inc/core.php';
include_once MWP_INTER_PLUGIN_DIR . 'inc/cron.php';
include_once MWP_INTER_PLUGIN_DIR . 'inc/admin/view.php';
include_once MWP_INTER_PLUGIN_DIR . 'inc/webhook.php';
include_once MWP_INTER_PLUGIN_DIR . 'gateways/billet.php';
include_once MWP_INTER_PLUGIN_DIR . 'gateways/pix.php';


function mwp_inter_scripts() {
    wp_add_inline_script('mwp-inter-script', '
        jQuery(document).ready(function($) {
            $(".button-thankyou-copy button").click(function(){
                navigator.clipboard.writeText($(".button-thankyou-copy input").val());
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'mwp_inter_scripts');