<?php

if (!defined('ABSPATH')) {
    exit;
}

class WF_OrderImpExpCsv_AJAX_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_woocommerce_csv_order_import_request', array($this, 'csv_order_import_request'));
        add_action('wp_ajax_order_test_ftp_connection', array($this, 'test_ftp_credentials'));
		add_action('wp_ajax_order_csv_export_mapping_change', array($this, 'export_mapping_change_columns'));
       
    }

    /**
     * Ajax event for importing a CSV
     */
    public function csv_order_import_request() {
        define('WP_LOAD_IMPORTERS', true);
        WF_OrderImpExpCsv_Importer::order_importer();
    }
    
    /**
     * Ajax event to test FTP details
     */
    public function test_ftp_credentials() { 
        $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');
        if (!wp_verify_nonce($nonce,WF_ORDER_IMP_EXP_ID) || !WF_Order_Import_Export_CSV::hf_user_permission()) {
            wp_die(__('Access Denied', 'wf_order_import_export'));
        }
        
        $wf_ordr_ftp_details = array();
        $wf_ordr_ftp_details['host'] = !empty($_POST['ftp_host']) ? sanitize_text_field($_POST['ftp_host']) : '';
        $wf_ordr_ftp_details['port'] = !empty($_POST['ftp_port']) ? absint($_POST['ftp_port']) : 21;
        $wf_ordr_ftp_details['userid'] = !empty($_POST['ftp_userid']) ? wp_unslash($_POST['ftp_userid']) : '';
        $wf_ordr_ftp_details['password'] = !empty($_POST['ftp_password']) ? wp_unslash($_POST['ftp_password']) : '';
        $wf_ordr_ftp_details['use_ftps'] = !empty($_POST['use_ftps']) ? absint($_POST['use_ftps']) : 0;
        
        $ftp_conn = (!empty($wf_ordr_ftp_details['use_ftps'])) ? @ftp_ssl_connect($wf_ordr_ftp_details['host'], $wf_ordr_ftp_details['port']) : @ftp_connect($wf_ordr_ftp_details['host'], $wf_ordr_ftp_details['port']);
        if ($ftp_conn == false) {
            die("<div id= 'ordr_ftp_test_msg' style = 'color : red'>".__('Could not connect to Host. Server host / IP or Port may be wrong.','wf_order_import_export')."</div>");
        }
        if (@ftp_login($ftp_conn, $wf_ordr_ftp_details['userid'], $wf_ordr_ftp_details['password'])) {
            die("<div id= 'ordr_ftp_test_msg' style = 'color : green'>".__('Successfully logged in.','wf_order_import_export')."</div>");
        } else {
            die("<div id= 'ordr_ftp_test_msg' style = 'color : blue'>".__('Connected to host but could not login. Server UserID or Password may be wrong or Try with / without FTPS.','wf_order_import_export')."</div>");
        }
    }
	 /**
     * Ajax event for changing mapping of export CSV
     */
    public function export_mapping_change_columns() {
        $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');
        if (!wp_verify_nonce($nonce,WF_ORDER_IMP_EXP_ID) || !WF_Order_Import_Export_CSV::hf_user_permission()) {
            wp_die(__('Access Denied', 'wf_order_import_export'));
        }
        $selected_profile = !empty($_POST['v_new_profile']) ? sanitize_text_field($_POST['v_new_profile']) : '';

        $post_columns = array();
        if (!$selected_profile) {
            $post_columns = include( 'exporter/data/data-wf-post-columns.php' );
        }

        $export_profile_array = get_option('xa_ordr_csv_export_mapping');

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

new WF_OrderImpExpCsv_AJAX_Handler();
