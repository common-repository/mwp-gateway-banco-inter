<?php
function mwp_inter_wc_custom_menu_callback() {
    ?>
    <div class="wrap">
        <h2>Banco Inter Configurações</h2>
        <p>Plugin de integração com o Banco Inter desenvolvimento por <a href="https://mestresdowp.com.br" target="blank">Mestres do WP</a></p>
        <p>Veja a documentação <a href="https://docs.mestresdowp.com.br/banco-inter-documentacao/" target="blank">clicando aqui</a></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <?php
			if (isset($_GET['remove_patch_key'])) {
				update_option('mwp_inter_wc_patch_key_path', '');
			}
			if (isset($_GET['remove_patch_ctr'])) {
				update_option('mwp_inter_wc_patch_ctr_path', '');
			}
            wp_nonce_field('mwp_inter_wc_settings_nonce', 'mwp_inter_wc_settings_nonce');
            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Token:</th>
                    <td><input type="text" name="mwp_inter_wc_token" value="<?php echo esc_attr(get_option('mwp_inter_wc_token')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Secret Token:</th>
                    <td><input type="text" name="mwp_inter_wc_secret_token" value="<?php echo esc_attr(get_option('mwp_inter_wc_secret_token')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Patch Key (Arquivo .key):</th>
                    <td>
                        <?php
                        $patch_key_path = get_option('mwp_inter_wc_patch_key_path');
                        if (!empty($patch_key_path) && file_exists($patch_key_path)) {
                            echo esc_attr(basename($patch_key_path));
                            echo '<a href="' . esc_url(admin_url('admin.php?page=mwp-inter-woocommerce-settings&remove_patch_key=true')) . '">' . esc_html__('Remover', 'mwp-gateway-banco-inter') . '</a>';
                        } else {
                            echo '<input type="file" name="mwp_inter_wc_patch_key" accept=".key" />';
                        }
                        ?>
					</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Patch CTR (Arquivo .crt):</th>
                    <td>
                        <?php
                        $patch_ctr_path = get_option('mwp_inter_wc_patch_ctr_path');
                        if (!empty($patch_ctr_path) && file_exists($patch_ctr_path)) {
							echo esc_attr(basename($patch_ctr_path));
                            echo '<a href="' . esc_url(admin_url('admin.php?page=mwp-inter-woocommerce-settings&remove_patch_ctr=true')) . '">' . esc_html__('Remover', 'mwp-gateway-banco-inter') . '</a>';
                        } else {
                            echo '<input type="file" name="mwp_inter_wc_patch_ctr" accept=".crt" />';
                        }
                        ?>
					</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Conta Corrente:</th>
                    <td><input type="text" name="mwp_inter_wc_conta_corrente" value="<?php echo esc_attr(get_option('mwp_inter_wc_conta_corrente')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Chave PIX:</th>
                    <td><input type="text" name="mwp_inter_wc_chave_pix" value="<?php echo esc_attr(get_option('mwp_inter_wc_chave_pix')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Beneficiário:</th>
                    <td><input type="text" name="mwp_inter_wc_beneficiario" value="<?php echo esc_attr(get_option('mwp_inter_wc_beneficiario')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Cidade:</th>
                    <td><input type="text" name="mwp_inter_wc_cidade" value="<?php echo esc_attr(get_option('mwp_inter_wc_cidade')); ?>" /></td>
                </tr>
            </table>
            
            <?php submit_button('Salvar Configurações'); ?>
            <input type="hidden" name="action" value="save_mwp_inter_wc_settings">
        </form>
    </div>
    <?php
}