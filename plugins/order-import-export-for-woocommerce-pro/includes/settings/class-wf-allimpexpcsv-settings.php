<?php

if (!defined('ABSPATH')) {
    exit;
}

class wf_allImpExpCsv_Settings {

    /**
     * Order Exporter Tool
     */
    public static function save_settings() {
        $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');
        if (!wp_verify_nonce($nonce,WF_ORDER_IMP_EXP_ID) || !WF_Order_Import_Export_CSV::hf_user_permission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wf_order_import_export'));
        } 
        global $wpdb;
        
        $ord_enable_ftp_ie = !empty($_POST['ord_enable_ftp_ie']) ? true : false;
        $ord_ftp_server = !empty($_POST['ord_ftp_server']) ? sanitize_text_field($_POST['ord_ftp_server']) : '';
        $ord_ftp_user = !empty($_POST['ord_ftp_user']) ? wp_unslash($_POST['ord_ftp_user']) : '';
        $ord_ftp_password = !empty($_POST['ord_ftp_password']) ? wp_unslash($_POST['ord_ftp_password']) : '';
        $ord_ftp_port = !empty($_POST['ord_ftp_port']) ? absint($_POST['ord_ftp_port']) : 21;
        $ord_use_ftps = !empty($_POST['ord_use_ftps']) ? true : false;
        $ord_use_pasv = !empty($_POST['ord_use_pasv']) ? true : false;
        $ord_ftp_path = !empty($_POST['ord_ftp_path']) ? sanitize_text_field($_POST['ord_ftp_path']) : '/';
        $ord_auto_export_ftp_file_name = !empty($_POST['ord_auto_export_ftp_file_name']) ? sanitize_text_field($_POST['ord_auto_export_ftp_file_name']) : null;
        
        $ord_auto_export = !empty($_POST['ord_auto_export']) ? sanitize_text_field($_POST['ord_auto_export']) : 'Disabled';
        $ord_auto_export_start_time = !empty($_POST['ord_auto_export_start_time']) ? sanitize_text_field($_POST['ord_auto_export_start_time']) : '';
        $ord_auto_export_interval = !empty($_POST['ord_auto_export_interval']) ? absint($_POST['ord_auto_export_interval']) : '';
        $ord_auto_export_profile = !empty($_POST['ord_auto_export_profile']) ? sanitize_text_field($_POST['ord_auto_export_profile']) : '';        
        $ord_auto_export_order_status = isset($_POST['ord_auto_export_order_status']) ? wc_clean($_POST['ord_auto_export_order_status']) : NULL;
        $ord_auto_date_from = isset($_POST['ord_auto_date_from']) ? sanitize_text_field($_POST['ord_auto_date_from']) : NULL;
        $ord_auto_date_to = isset($_POST['ord_auto_date_to']) ? sanitize_text_field($_POST['ord_auto_date_to']) : NULL;
        $ord_auto_export_products = isset($_POST['products']) ? array_map('intval',$_POST['products']) : NULL;
        $ord_auto_export_email_order = isset($_POST['email']) ? wc_clean($_POST['email']) : NULL;
        $ord_auto_export_coupon_order = isset($_POST['coupon']) ? wc_clean($_POST['coupon']) : NULL;
        $exclude_already_exported = !empty($_POST['exclude_already_exported']) ? true : false;
        $export_to_separate_columns = !empty($_POST['export_to_separate_columns']) ? true : false;
        $include_meta = !empty($_POST['include_meta']) ? true :false;
        
        $ord_auto_import = !empty($_POST['ord_auto_import']) ? sanitize_text_field($_POST['ord_auto_import']) : 'Disabled';
        $csv_ordercsv_multiple_files_import = !empty($_POST['csv_ordercsv_multiple_files_import']) ? true : false;
        $ord_auto_import_file = !empty($_POST['ord_auto_import_file']) ? sanitize_text_field($_POST['ord_auto_import_file']) : null;
        $wtcreateuser_cron = !empty($_POST['wtcreateuser_cron']) ? true : false;
        $ord_auto_import_delimiter = !empty($_POST['ord_auto_import_delimiter']) ? $_POST['ord_auto_import_delimiter'] : ','; // WPCS: CSRF ok, input var ok. 
        $ord_auto_import_start_time = !empty($_POST['ord_auto_import_start_time']) ? sanitize_text_field($_POST['ord_auto_import_start_time']) : '';
        $ord_auto_import_interval = !empty($_POST['ord_auto_import_interval']) ? absint($_POST['ord_auto_import_interval']) : '';
        $ord_auto_import_profile = !empty($_POST['ord_auto_import_profile']) ? sanitize_text_field($_POST['ord_auto_import_profile']) : '';
        $ord_auto_import_merge = !empty($_POST['ord_auto_import_merge']) ? true : false;
        $ord_link_using_sku_cron = !empty($_POST['ord_link_using_sku_cron']) ? true : false;
        
        
        $sbc_enable_ftp_ie = !empty($_POST['sbc_enable_ftp_ie']) ? true : false;
        $sbc_ftp_server = !empty($_POST['sbc_ftp_server']) ? sanitize_text_field($_POST['sbc_ftp_server']) : '';
        $sbc_ftp_user = !empty($_POST['sbc_ftp_user']) ? wp_unslash($_POST['sbc_ftp_user']) : '';
        $sbc_ftp_password = !empty($_POST['sbc_ftp_password']) ? wp_unslash($_POST['sbc_ftp_password']) : '';
        $sbc_ftp_port = !empty($_POST['sbc_ftp_port']) ? absint($_POST['sbc_ftp_port']) : 21;
        $sbc_use_ftps = !empty($_POST['sbc_use_ftps']) ? true : false;
        $sbc_use_pasv = !empty($_POST['sbc_use_pasv']) ? true : false;
        $sbc_ftp_path = !empty($_POST['sbc_ftp_path']) ? sanitize_text_field($_POST['sbc_ftp_path']) : '/';
        $sbc_auto_export_ftp_file_name = !empty($_POST['sbc_auto_export_ftp_file_name']) ? sanitize_text_field($_POST['sbc_auto_export_ftp_file_name']) : null;
        
        $sbc_auto_export = !empty($_POST['sbc_auto_export']) ? sanitize_text_field($_POST['sbc_auto_export']) : 'Disabled';
        $sbc_auto_export_start_time = !empty($_POST['sbc_auto_export_start_time']) ? sanitize_text_field($_POST['sbc_auto_export_start_time']) : '';
        $sbc_auto_export_interval = !empty($_POST['sbc_auto_export_interval']) ? sanitize_text_field($_POST['sbc_auto_export_interval']) : '';
        $sbc_auto_export_order_status = !empty($_POST['sbc_auto_export_order_status']) ? wc_clean($_POST['sbc_auto_export_order_status']) : '';
        $sub_auto_export_profile = !empty($_POST['sub_auto_export_profile']) ? sanitize_text_field($_POST['sub_auto_export_profile']) : ''; 
        
        $sbc_auto_import = !empty($_POST['sbc_auto_import']) ? sanitize_text_field($_POST['sbc_auto_import']) : 'Disabled';
        $sbc_auto_import_file = !empty($_POST['sbc_auto_import_file']) ? sanitize_text_field($_POST['sbc_auto_import_file']) : null;
        $sbc_auto_import_start_time = !empty($_POST['sbc_auto_import_start_time']) ? sanitize_text_field($_POST['sbc_auto_import_start_time']) : '';
        $sbc_auto_import_interval = !empty($_POST['sbc_auto_import_interval']) ? absint($_POST['sbc_auto_import_interval']) : '';
        $sbc_auto_import_profile = !empty($_POST['sbc_auto_import_profile']) ? sanitize_text_field($_POST['sbc_auto_import_profile']) : '';
        $sbc_auto_import_merge = !empty($_POST['sbc_auto_import_merge']) ? true : false;


        $cpn_enable_ftp_ie = !empty($_POST['cpn_enable_ftp_ie']) ? true : false;
        $cpn_ftp_server = !empty($_POST['cpn_ftp_server']) ? sanitize_text_field($_POST['cpn_ftp_server']) : '';
        $cpn_ftp_user = !empty($_POST['cpn_ftp_user']) ? wp_unslash($_POST['cpn_ftp_user']) : '';
        $cpn_ftp_password = !empty($_POST['cpn_ftp_password']) ? wp_unslash($_POST['cpn_ftp_password']) : '';
        $cpn_ftp_port = !empty($_POST['cpn_ftp_port']) ? absint($_POST['cpn_ftp_port']) : 21;
        $cpn_use_ftps = !empty($_POST['cpn_use_ftps']) ? true : false;
        $cpn_use_pasv = !empty($_POST['cpn_use_pasv']) ? true : false;
        $cpn_ftp_path = !empty($_POST['cpn_ftp_path']) ? sanitize_text_field($_POST['cpn_ftp_path']) : '/';
        $cpn_auto_export_ftp_file_name = !empty($_POST['cpn_auto_export_ftp_file_name']) ? sanitize_text_field($_POST['cpn_auto_export_ftp_file_name']) : null;

        $cpn_auto_export = !empty($_POST['cpn_auto_export']) ? sanitize_text_field($_POST['cpn_auto_export']) : 'Disabled';
        $cpn_auto_export_start_time = !empty($_POST['cpn_auto_export_start_time']) ? sanitize_text_field($_POST['cpn_auto_export_start_time']) : '';
        $cpn_auto_export_interval = !empty($_POST['cpn_auto_export_interval']) ? absint($_POST['cpn_auto_export_interval']) : '';

        $cpn_auto_import = !empty($_POST['cpn_auto_import']) ? sanitize_text_field($_POST['cpn_auto_import']) : 'Disabled';
        $cpn_auto_import_file = !empty($_POST['cpn_auto_import_file']) ? sanitize_text_field($_POST['cpn_auto_import_file']) : null;
        $cpn_auto_import_start_time = !empty($_POST['cpn_auto_import_start_time']) ? sanitize_text_field($_POST['cpn_auto_import_start_time']) : '';
        $cpn_auto_import_interval = !empty($_POST['cpn_auto_import_interval']) ? absint($_POST['cpn_auto_import_interval']) : '';
        $cpn_auto_import_profile = !empty($_POST['cpn_auto_import_profile']) ? sanitize_text_field($_POST['cpn_auto_import_profile']) : '';
        $cpn_auto_import_merge = !empty($_POST['cpn_auto_import_merge']) ? true : false;
        $sku_checkbox = !empty($_POST['sku_checkbox']) ? true : false;
        

        $xml_enable_ftp_ie = !empty($_POST['xml_enable_ftp_ie']) ? true : false;
        $xml_ftp_server = !empty($_POST['xml_ftp_server']) ? sanitize_text_field($_POST['xml_ftp_server']) : '';
        $xml_ftp_user = !empty($_POST['xml_ftp_user']) ? wp_unslash($_POST['xml_ftp_user']) : '';
        $xml_ftp_password = !empty($_POST['xml_ftp_password']) ? wp_unslash($_POST['xml_ftp_password']) : '';
        $xml_ftp_port = !empty($_POST['xml_ftp_port']) ? absint($_POST['xml_ftp_port']) : 21;
        $xml_use_ftps = !empty($_POST['xml_use_ftps']) ? true : false;
        $xml_use_pasv = !empty($_POST['xml_use_pasv']) ? true : false;        
        $xml_ftp_path = !empty($_POST['xml_ftp_path']) ? sanitize_text_field($_POST['xml_ftp_path']) : '/';
        $xml_export_ftp_file_name = !empty($_POST['xml_export_ftp_file_name']) ? sanitize_text_field($_POST['xml_export_ftp_file_name']) : null;

        $xml_orderxml_auto_export = !empty($_POST['xml_orderxml_auto_export']) ? sanitize_text_field($_POST['xml_orderxml_auto_export']) : 'Disabled';
        $xml_orderxml_auto_export_start_time = !empty($_POST['xml_orderxml_auto_export_start_time']) ? sanitize_text_field($_POST['xml_orderxml_auto_export_start_time']) : '';
        $xml_orderxml_auto_export_interval = !empty($_POST['xml_orderxml_auto_export_interval']) ? absint($_POST['xml_orderxml_auto_export_interval']) : '';
        $xml_orderxml_auto_export_order_status = isset($_POST['xml_orderxml_auto_export_order_status']) ? wc_clean($_POST['xml_orderxml_auto_export_order_status']) : NULL;
        $xml_orderxml_auto_export_products = isset($_POST['products_xml']) ? wc_clean($_POST['products_xml']) : NULL;
        $exclude_already_exported_xml = !empty($_POST['exclude_already_exported_xml']) ? true : false;

        $xml_orderxml_auto_import = !empty($_POST['xml_orderxml_auto_import']) ? sanitize_text_field($_POST['xml_orderxml_auto_import']) : 'Disabled';
        $xml_orderxml_auto_import_start_time = !empty($_POST['xml_orderxml_auto_import_start_time']) ? sanitize_text_field($_POST['xml_orderxml_auto_import_start_time']) : '';
        $xml_orderxml_auto_import_interval = !empty($_POST['xml_orderxml_auto_import_interval']) ? absint($_POST['xml_orderxml_auto_import_interval']) : '';       
        $xml_orderxml_auto_import_file = !empty($_POST['xml_orderxml_auto_import_file']) ? sanitize_text_field($_POST['xml_orderxml_auto_import_file']) : null;
        $xml_orderxml_auto_import_merge = !empty($_POST['xml_orderxml_auto_import_merge']) ? true : false;
        $xml_orderxml_multiple_files_import = !empty($_POST['xml_orderxml_multiple_files_import']) ? true : false;
        
        
        $ord_enable_url_ie = !empty($_POST['ord_enable_url_ie']) ? true : false;
        $ord_auto_import_url = !empty($_POST['ord_auto_import_url']) ? sanitize_text_field($_POST['ord_auto_import_url']) : '';
        $ord_auto_import_url_delimiter = !empty($_POST['ord_auto_import_url_delimiter']) ? $_POST['ord_auto_import_url_delimiter'] : ','; // WPCS: CSRF ok, input var ok. 
        $ord_auto_import_url_start_time = !empty($_POST['ord_auto_import_url_start_time']) ? sanitize_text_field($_POST['ord_auto_import_url_start_time']) : '';
        $ord_auto_import_url_interval = !empty($_POST['ord_auto_import_url_interval']) ? absint($_POST['ord_auto_import_url_interval']) : '';
        $ord_auto_import_url_profile = !empty($_POST['ord_auto_import_url_profile']) ? sanitize_text_field($_POST['ord_auto_import_url_profile']) : '';
        $ord_auto_import_url_merge = !empty($_POST['ord_auto_import_url_merge']) ? true : false;
        
        $settings = array();
        $settings['ord_enable_ftp_ie'] = $ord_enable_ftp_ie;
        $settings['ord_ftp_server'] = $ord_ftp_server;
        $settings['ord_ftp_user'] = $ord_ftp_user;
        $settings['ord_ftp_password'] = $ord_ftp_password;
        $settings['ord_ftp_port'] = $ord_ftp_port;
        $settings['ord_use_ftps'] = $ord_use_ftps;
        $settings['ord_use_pasv'] = $ord_use_pasv;
        $settings['ord_ftp_path'] = $ord_ftp_path;

        $settings['ord_auto_export'] = $ord_auto_export;
        $settings['ord_auto_export_start_time'] = $ord_auto_export_start_time;
        $settings['ord_auto_export_interval'] = $ord_auto_export_interval;
        $settings['ord_auto_export_profile'] = $ord_auto_export_profile;
        $settings['ord_auto_export_ftp_file_name'] = $ord_auto_export_ftp_file_name;
        $settings['ord_auto_export_order_status'] = $ord_auto_export_order_status;
        $settings['ord_auto_date_from'] = $ord_auto_date_from;
        $settings['ord_auto_date_to'] = $ord_auto_date_to;
        $settings['ord_auto_export_products'] = $ord_auto_export_products;
        $settings['ord_auto_export_email_order'] = $ord_auto_export_email_order;
        $settings['ord_auto_export_coupon_order'] = $ord_auto_export_coupon_order;
        $settings['csv_ordercsv_multiple_files_import'] = $csv_ordercsv_multiple_files_import;
        $settings['exclude_already_exported'] = $exclude_already_exported;
        $settings['export_to_separate_columns'] = $export_to_separate_columns;
        $settings['include_meta'] = $include_meta;
        
        $settings['ord_auto_import'] = $ord_auto_import;
        $settings['ord_auto_import_file'] = $ord_auto_import_file;
        $settings['wtcreateuser_cron'] = $wtcreateuser_cron;
        $settings['ord_auto_import_delimiter'] = $ord_auto_import_delimiter;
        $settings['ord_auto_import_start_time'] = $ord_auto_import_start_time;
        $settings['ord_auto_import_interval'] = $ord_auto_import_interval;
        $settings['ord_auto_import_profile'] = $ord_auto_import_profile;
        $settings['ord_auto_import_merge'] = $ord_auto_import_merge;
        $settings['ord_link_using_sku_cron'] = $ord_link_using_sku_cron;
        
        
        $settings['sbc_enable_ftp_ie'] = $sbc_enable_ftp_ie;
        $settings['sbc_ftp_server'] = $sbc_ftp_server;
        $settings['sbc_ftp_user'] = $sbc_ftp_user;
        $settings['sbc_ftp_password'] = $sbc_ftp_password;
        $settings['sbc_ftp_port'] = $sbc_ftp_port;
        $settings['sbc_use_ftps'] = $sbc_use_ftps;
        $settings['sbc_use_pasv'] = $sbc_use_pasv;
        $settings['sbc_ftp_path'] = $sbc_ftp_path;

        $settings['sbc_auto_export'] = $sbc_auto_export;
        $settings['sbc_auto_export_start_time'] = $sbc_auto_export_start_time;
        $settings['sbc_auto_export_interval'] = $sbc_auto_export_interval;
        $settings['sbc_auto_export_order_status'] = $sbc_auto_export_order_status;
        $settings['sbc_auto_export_ftp_file_name'] = $sbc_auto_export_ftp_file_name;
        $settings['sub_auto_export_profile'] = $sub_auto_export_profile;

        $settings['sbc_auto_import'] = $sbc_auto_import;
        $settings['sbc_auto_import_file'] = $sbc_auto_import_file;
        $settings['sbc_auto_import_start_time'] = $sbc_auto_import_start_time;
        $settings['sbc_auto_import_interval'] = $sbc_auto_import_interval;
        $settings['sbc_auto_import_profile'] = $sbc_auto_import_profile;
        $settings['sbc_auto_import_merge'] = $sbc_auto_import_merge;

        
        $settings['cpn_enable_ftp_ie'] = $cpn_enable_ftp_ie;
        $settings['cpn_ftp_server'] = $cpn_ftp_server;
        $settings['cpn_ftp_user'] = $cpn_ftp_user;
        $settings['cpn_ftp_password'] = $cpn_ftp_password;
        $settings['cpn_ftp_port'] = $cpn_ftp_port;
        $settings['cpn_use_ftps'] = $cpn_use_ftps;
        $settings['cpn_use_pasv'] = $cpn_use_pasv;
        $settings['cpn_ftp_path'] = $cpn_ftp_path;

        $settings['cpn_auto_export'] = $cpn_auto_export;
        $settings['cpn_auto_export_start_time'] = $cpn_auto_export_start_time;
        $settings['cpn_auto_export_interval'] = $cpn_auto_export_interval;
        $settings['cpn_auto_export_ftp_file_name'] = $cpn_auto_export_ftp_file_name;

        $settings['cpn_auto_import'] = $cpn_auto_import;
        $settings['cpn_auto_import_file'] = $cpn_auto_import_file;
        $settings['cpn_auto_import_start_time'] = $cpn_auto_import_start_time;
        $settings['cpn_auto_import_interval'] = $cpn_auto_import_interval;
        $settings['cpn_auto_import_profile'] = $cpn_auto_import_profile;
        $settings['cpn_auto_import_merge'] = $cpn_auto_import_merge;
        $settings['sku_checkbox'] = $sku_checkbox;


        $settings['xml_enable_ftp_ie'] = $xml_enable_ftp_ie;
        $settings['xml_ftp_server'] = $xml_ftp_server;
        $settings['xml_ftp_user'] = $xml_ftp_user;
        $settings['xml_ftp_password'] = $xml_ftp_password;
        $settings['xml_ftp_port'] = $xml_ftp_port;
        $settings['xml_use_ftps'] = $xml_use_ftps;
        $settings['xml_use_pasv'] = $xml_use_pasv;
        $settings['xml_ftp_path'] = $xml_ftp_path;
        $settings['xml_export_ftp_file_name'] = $xml_export_ftp_file_name;

        $settings['xml_orderxml_auto_export'] = $xml_orderxml_auto_export;
        $settings['xml_orderxml_auto_export_start_time'] = $xml_orderxml_auto_export_start_time;
        $settings['xml_orderxml_auto_export_interval'] = $xml_orderxml_auto_export_interval;
        $settings['xml_orderxml_auto_export_order_status'] = $xml_orderxml_auto_export_order_status;
        $settings['xml_orderxml_auto_export_products'] = $xml_orderxml_auto_export_products;
        $settings['exclude_already_exported_xml'] = $exclude_already_exported_xml;

        $settings['xml_orderxml_auto_import'] = $xml_orderxml_auto_import;
        $settings['xml_orderxml_auto_import_start_time'] = $xml_orderxml_auto_import_start_time;
        $settings['xml_orderxml_auto_import_interval'] = $xml_orderxml_auto_import_interval;
        $settings['xml_orderxml_auto_import_file'] = $xml_orderxml_auto_import_file;       
        $settings['xml_orderxml_auto_import_merge'] = $xml_orderxml_auto_import_merge;
        $settings['xml_orderxml_multiple_files_import'] = $xml_orderxml_multiple_files_import;
        
        
        $settings['ord_enable_url_ie'] = $ord_enable_url_ie;
        $settings['ord_auto_import_url'] = $ord_auto_import_url;
        $settings['ord_auto_import_url_delimiter'] = $ord_auto_import_url_delimiter;
        $settings['ord_auto_import_url_start_time'] = $ord_auto_import_url_start_time;
        $settings['ord_auto_import_url_interval'] = $ord_auto_import_url_interval;
        $settings['ord_auto_import_url_profile'] = $ord_auto_import_url_profile;
        $settings['ord_auto_import_url_merge'] = $ord_auto_import_url_merge;

        $settings_db = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);

        $sbc_orig_export_start_inverval = '';
        if (isset($settings_db['sbc_auto_export_start_time']) && isset($settings_db['sbc_auto_export_interval'])) {
            $sbc_orig_export_start_inverval = $settings_db['sbc_auto_export_start_time'] . $settings_db['sbc_auto_export_interval'];
        }
        $sbc_orig_import_start_inverval = '';
        if (isset($settings_db['sbc_auto_import_start_time']) && isset($settings_db['sbc_auto_import_interval'])) {
            $sbc_orig_import_start_inverval = $settings_db['sbc_auto_import_start_time'] . $settings_db['sbc_auto_import_interval'];
        }

        $cpn_orig_export_start_inverval = '';
        if (isset($settings_db['cpn_auto_export_start_time']) && isset($settings_db['cpn_auto_export_interval'])) {
            $cpn_orig_export_start_inverval = $settings_db['cpn_auto_export_start_time'] . $settings_db['cpn_auto_export_interval'];
        }
        $cpn_orig_import_start_inverval = '';
        if (isset($settings_db['cpn_auto_import_start_time']) && isset($settings_db['cpn_auto_import_interval'])) {
            $cpn_orig_import_start_inverval = $settings_db['cpn_auto_import_start_time'] . $settings_db['cpn_auto_import_interval'];
        }

        $ord_orig_export_start_inverval = '';
        if (isset($settings_db['ord_auto_export_start_time']) && isset($settings_db['ord_auto_export_interval'])) {
            $ord_orig_export_start_inverval = $settings_db['ord_auto_export_start_time'] . $settings_db['ord_auto_export_interval'];
        }
        $ord_orig_import_start_inverval = '';
        if (isset($settings_db['ord_auto_import_start_time']) && isset($settings_db['ord_auto_import_interval'])) {
            $ord_orig_import_start_inverval = $settings_db['ord_auto_import_start_time'] . $settings_db['ord_auto_import_interval'];
        }

        $xml_orderxml_orig_export_start_inverval = '';
        if (isset($settings_db['xml_orderxml_auto_export_start_time']) && isset($settings_db['xml_orderxml_auto_export_interval'])) {
            $xml_orderxml_orig_export_start_inverval = $settings_db['xml_orderxml_auto_export_start_time'] . $settings_db['xml_orderxml_auto_export_interval'];
        }
        $xml_orderxml_orig_import_start_inverval = '';
        if (isset($settings_db['xml_orderxml_auto_import_start_time']) && isset($settings_db['xml_orderxml_auto_import_interval'])) {
            $xml_orderxml_orig_import_start_inverval = $settings_db['xml_orderxml_auto_import_start_time'] . $settings_db['xml_orderxml_auto_import_interval'];
        }
        
        $ord_orig_auto_import_url_start_interval = '';
        if(isset($settings_db['ord_auto_import_url_start_time']) && isset($settings_db['ord_auto_import_url_interval'])){
            $ord_orig_auto_import_url_start_interval = $settings_db['ord_auto_import_url_start_time'] . $settings_db['ord_auto_import_url_interval'];
        }

        update_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', $settings);

        // clear scheduled export event in case export interval was changed
        if (($sbc_orig_export_start_inverval !== $settings['sbc_auto_export_start_time'] . $settings['sbc_auto_export_interval']) || (!$sbc_enable_ftp_ie) || ($sbc_auto_export === 'Disabled')) {
            // note this resets the next scheduled execution time to the time options were saved + the interval
            wp_clear_scheduled_hook('hf_subscription_order_csv_im_ex_auto_export');
        }

        // clear scheduled import event in case import interval was changed
        if (($sbc_orig_import_start_inverval !== $settings['sbc_auto_import_start_time'] . $settings['sbc_auto_import_interval']) || (!$sbc_enable_ftp_ie) || ($sbc_auto_import === 'Disabled')) {
            // note this resets the next scheduled execution time to the time options were saved + the interval
            wp_clear_scheduled_hook('hf_subscription_order_csv_im_ex_auto_import');
        }

        if (($cpn_orig_export_start_inverval !== $settings['cpn_auto_export_start_time'] . $settings['cpn_auto_export_interval']) || (!$cpn_enable_ftp_ie) || ($cpn_auto_export === 'Disabled')) {
            wp_clear_scheduled_hook('wf_coupon_csv_im_ex_auto_export_coupons');
        }

        if (($cpn_orig_import_start_inverval !== $settings['cpn_auto_import_start_time'] . $settings['cpn_auto_import_interval']) || (!$cpn_enable_ftp_ie) || ($cpn_auto_import === 'Disabled')) {
            wp_clear_scheduled_hook('wf_coupon_csv_im_ex_auto_import_coupons');
        }

        if (($ord_orig_export_start_inverval !== $settings['ord_auto_export_start_time'] . $settings['ord_auto_export_interval']) || (!$ord_enable_ftp_ie) || ($ord_auto_export === 'Disabled')) {
            wp_clear_scheduled_hook('wf_order_csv_im_ex_auto_export_order');
        }

        if (($ord_orig_import_start_inverval !== $settings['ord_auto_import_start_time'] . $settings['ord_auto_import_interval']) || (!$ord_enable_ftp_ie) || ($ord_auto_import === 'Disabled')) {
            wp_clear_scheduled_hook('wf_order_csv_im_ex_auto_import_order');
        }

        if (($xml_orderxml_orig_export_start_inverval !== $settings['xml_orderxml_auto_export_start_time'] . $settings['xml_orderxml_auto_export_interval']) || (!$xml_enable_ftp_ie) || ($xml_orderxml_auto_export === 'Disabled')) {
            wp_clear_scheduled_hook('wf_order_xml_im_ex_auto_export_orderxml');
        }

        if (($xml_orderxml_orig_import_start_inverval !== $settings['xml_orderxml_auto_import_start_time'] . $settings['xml_orderxml_auto_import_interval']) || (!$xml_enable_ftp_ie) || ($xml_orderxml_auto_import === 'Disabled')) {
            wp_clear_scheduled_hook('wf_order_xml_im_ex_auto_import_orderxml');
        }
        
        if(($ord_orig_auto_import_url_start_interval !== $settings['ord_auto_import_url_start_time'] . $settings['ord_auto_import_url_interval']) || (!$ord_enable_url_ie)){
            wp_clear_scheduled_hook('wf_woocommerce_csv_im_ex_auto_import_orders_from_url');
        }

    wp_redirect(admin_url('/admin.php?page=' . WF_WOOCOMMERCE_ORDER_IM_EX . '&tab=settings&section='.sanitize_text_field($_GET['section'])));
        exit;
    }
}
