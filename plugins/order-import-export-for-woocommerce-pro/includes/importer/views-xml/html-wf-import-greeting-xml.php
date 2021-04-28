<?php
$ftp_server		= '';
$ftp_user		= '';
$ftp_password		= '';
$ftp_port		= '';
$use_ftps		= '';
$use_pasv		= '';
$enable_ftp_ie		= '';
$ftp_server_path	= '';

if (!empty($ftp_settings)) {

	$ftp_server	= ! empty($ftp_settings[ 'ftp_server' ]) ? $ftp_settings[ 'ftp_server' ] : '';
	$ftp_user	= ! empty($ftp_settings[ 'ftp_user' ]) ? $ftp_settings[ 'ftp_user' ] : '';
	$ftp_password	= ! empty($ftp_settings[ 'ftp_password' ]) ? $ftp_settings[ 'ftp_password' ] : '';
	$ftp_port	= ! empty($ftp_settings[ 'ftp_port' ]) ? $ftp_settings[ 'ftp_port' ] : 21;
	$use_ftps	= ! empty($ftp_settings[ 'use_ftps' ]) ? $ftp_settings[ 'use_ftps' ] : '';
	$use_pasv	= ! empty($ftp_settings[ 'use_pasv' ]) ? $ftp_settings[ 'use_pasv' ] : '';
	$enable_ftp_ie	= ! empty($ftp_settings[ 'enable_ftp_ie' ]) ? $ftp_settings[ 'enable_ftp_ie' ] : '';
	$ftp_server_path= ! empty($ftp_settings[ 'ftp_server_path' ]) ? $ftp_settings[ 'ftp_server_path' ] : '';
}
$tab = (isset($_GET['tab']) && !empty($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'importxml';
?>

<div class="woocommerce">

   <?php include_once(dirname(__FILE__) . '/../../views/html-wf-common-header.php'); ?>
<div class="tool-box ordimpexp-bg-white ordimpexp-p-20p">

    <p><?php _e('You can import orders (in XML format) in to the shop using any of below methods.', 'wf_order_import_export'); ?></p>



<?php if (!empty($upload_dir['error'])) : ?>

        <div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:', 'wf_order_import_export'); ?></p>

            <p><strong><?php echo $upload_dir['error']; ?></strong></p></div>

    <?php else : ?>

        <form enctype="multipart/form-data" id="import-upload-form" method="POST" action="<?php echo esc_attr(wp_nonce_url($action, 'import-upload')); ?>" name="import_data">

            <table class="form-table">

                <tbody>

                    <tr>

                        <th>
                            <?php _e('XML Type','wf_order_import_export'); ?>
                        </th>

                        <td>
                        
                            <select id="v_order_import_type" name="order_import_type" data-placeholder="<?php _e('Orders Import Type', 'wf_order_import_export'); ?>" onchange="showDiv(this)">
                                <option value="general"><?php _e("WooCommerce",'wf_order_import_export') ?></option>
                                <option value="stamps"><?php _e("Stamps.Com",'wf_order_import_export') ?></option>
                                <option value="fedex"><?php _e("FedEx",'wf_order_import_export') ?></option>
                                <option value="ups"><?php _e("UPS WorldShip",'wf_order_import_export') ?></option>
                                <option value="endicia"><?php _e("Endicia",'wf_order_import_export') ?></option>
                            </select>
                            <div id="add_edit_choice">                           
                                    <?php _e('For existing order,','wf_order_import_export'); ?>
                                &nbsp;
                                <input type="radio" name="order_import_type_decision" value="skip" checked /> <?php _e('Skip','wf_order_import_export'); ?>
                            
                                <input type="radio" name="order_import_type_decision" value="overwrite" /> <?php _e('Overwrite','wf_order_import_export'); ?>
                            </div>
                        </td>
                        
                    </tr>
                    <script type="text/javascript">
                        function showDiv(elem){
                            if(elem.value == 'general')
                              document.getElementById('add_edit_choice').style.display = "block";
                            else
                              document.getElementById('add_edit_choice').style.display = "none";  
                        }
                    </script>
                    <tr>

                        <th>

                            <label for="upload"><?php _e('Method 1: Select a file from your computer', 'wf_order_import_export'); ?></label>

                        </th>

                        <td>

                            <input type="file" id="upload" name="import" size="25" />

                            <input type="hidden" name="action" value="save" />

                            <input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />

                            <small><?php printf(__('Maximum size: %s'), $size); ?></small>

                        </td>

                    </tr>

    <?php
    $mapping_from_db = get_option('wf_order_xml_imp_exp_mapping');

    if (!empty($mapping_from_db)) {
        ?>

                        <tr>

                            <th>

                                <label for="profile"><?php _e('Select a mapping file.', 'wf_order_import_export'); ?></label>

                            </th>

                            <td>

                                <select name="profile">

                                    <option value="">--Select--</option>

        <?php foreach ($mapping_from_db as $key => $value) { ?>

                                        <option value="<?php echo $key; ?>"><?php echo $key; ?></option>



        <?php } ?>

                                </select>

                            </td>

                        </tr>

                                <?php } ?>

                    <tr>

                        <th>

                            <label for="ftp"><?php _e('Method 2: Provide FTP Details:', 'wf_order_import_export'); ?></label>

                        </th>

                        <td>

                            <table class="form-table">

                                <tr>

                                    <th>

                                        <label for="enable_ftp_ie"><?php _e('Enable FTP import/export', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="checkbox" name="enable_ftp_ie" id="enable_ftp_ie" class="checkbox" <?php checked($enable_ftp_ie, 1); ?> />

                                    </td>

                                </tr>

                                <tr>

                                    <th>

                                        <label for="ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="text" name="ftp_server" id="ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $ftp_server; ?>" class="input-text" />

                                    </td>

                                </tr>

                                <tr>

                                    <th>

                                        <label for="ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="text" name="ftp_user" id="ftp_user"  value="<?php echo $ftp_user; ?>" class="input-text" />

                                    </td>

                                </tr>

                                <tr>

                                    <th>

                                        <label for="ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="password" name="ftp_password" id="ftp_password"  value="<?php echo $ftp_password; ?>" class="input-text" />

                                    </td>

                                </tr>
				
				<tr>

                                    <th>

                                        <label for="ftp_port"><?php _e('FTP Port', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="text" name="ftp_port" id="ftp_port"  value="<?php echo $ftp_port; ?>" class="input-text" />

                                    </td>

                                </tr>

                                <tr>

                                    <th>

                                        <label for="ftp_server_path"><?php _e('FTP Server Path', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="text" name="ftp_server_path" id="ftp_server_path"  value="<?php echo $ftp_server_path; ?>" class="input-text" />

                                    </td>

                                </tr>



                                <tr>

                                    <th>

                                        <label for="use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="checkbox" name="use_ftps" id="use_ftps" class="checkbox" <?php checked($use_ftps, 1); ?> />

                                    </td>

                                </tr>
                                <tr>

                                    <th>

                                        <label for="use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>

                                    </th>

                                    <td>

                                        <input type="checkbox" name="use_pasv" id="use_pasv" class="checkbox" <?php checked($use_pasv, 1); ?> />

                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Upload file and import'); ?>" />
            </p>
        </form>

<?php endif; ?>

</div>
</div>