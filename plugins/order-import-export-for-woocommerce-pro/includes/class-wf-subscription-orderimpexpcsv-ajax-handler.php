<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_subcription_orderImpExpCsv_AJAX_Handler {

    /**
     * Constructor
     */
    public function __construct() {
            add_action( 'wp_ajax_woocommerce_csv_subscription_order_import_request', array( $this, 'csv_order_import_request' ) );
            add_action('wp_ajax_subscription_csv_export_mapping_change', array($this, 'export_mapping_change_columns'));
    }

    /**
     * Ajax event for importing a CSV
     */
    public function csv_order_import_request() {
            define( 'WP_LOAD_IMPORTERS', true );
            wf_subcription_orderImpExpCsv_Importer::subscription_order_importer();
    }
    
    	 /**
     * Ajax event for changing mapping of export CSV
     */
    public function export_mapping_change_columns() {        
        $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');
        if (!wp_verify_nonce($nonce,wf_subscription_order_imp_exp_ID) || !wf_subscription_order_import_export_CSV::hf_user_permission()) {
            wp_die(__('Access Denied', 'wf_order_import_export'));
        }
        
        $selected_profile = !empty($_POST['v_new_profile']) ? sanitize_text_field($_POST['v_new_profile']) : '';

        $post_columns = array();
        if (!$selected_profile) {
            $post_columns = include( 'exporter/data/data-wf-post-subscription-columns' );
        }

        $export_profile_array = get_option('wt_subscription_csv_export_mapping');

        if (!empty($export_profile_array[$selected_profile])) {
            $post_columns = $export_profile_array[$selected_profile];
        }


        $res = "<tr>
                      <td style='padding: 10px;'>
                          <a href='#' id='pselectall' onclick='return false;' >".__('Select all','wf_order_import_export')."</a> &nbsp;/&nbsp;
                          <a href='#' id='punselectall' onclick='return false;'>".__('Unselect all','wf_order_import_export')."</a>
                      </td>
                  </tr>
                  
                <th style='text-align: left;'>
                    <label for='v_columns'>".__('Column','wf_order_import_export')."</label>
                </th>
                <th style='text-align: left;'>
                    <label for='v_columns_name'>".__('Column Name','wf_order_import_export')."</label>
                </th>";


        foreach ($post_columns as $pkey => $pcolumn) {

            $res.="<tr>
                <td>
                    <input name= 'columns[$pkey]' type='checkbox' value='$pkey' checked>
                    <label for='columns[$pkey]'>$pkey</label>
                </td>
                <td>";

            $res.="<input type='text' name='columns_name[$pkey]'  value='$pcolumn' class='input-text' />
                </td>
            </tr>";
        }

        echo $res;
        exit;
    }
	
}

new wf_subcription_orderImpExpCsv_AJAX_Handler();