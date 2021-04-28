<?php
$settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);

$sbc_ftp_server = isset($settings['sbc_ftp_server']) ? $settings['sbc_ftp_server'] : '';
$sbc_ftp_user = isset($settings['sbc_ftp_user']) ? $settings['sbc_ftp_user'] : '';
$sbc_ftp_password = isset($settings['sbc_ftp_password']) ? $settings['sbc_ftp_password'] : '';
$sbc_ftp_port = isset($settings['sbc_ftp_port']) ? $settings['sbc_ftp_port'] : 21;
$sbc_use_ftps = isset($settings['sbc_use_ftps']) ? $settings['sbc_use_ftps'] : '';
$sbc_use_pasv = isset($settings['sbc_use_pasv']) ? $settings['sbc_use_pasv'] : '';
$sbc_ftp_path = isset($settings['sbc_ftp_path']) ? $settings['sbc_ftp_path'] : '/';
$sbc_enable_ftp_ie = isset($settings['sbc_enable_ftp_ie']) ? $settings['sbc_enable_ftp_ie'] : '';

$cpn_ftp_server = isset($settings['cpn_ftp_server']) ? $settings['cpn_ftp_server'] : '';
$cpn_ftp_user = isset($settings['cpn_ftp_user']) ? $settings['cpn_ftp_user'] : '';
$cpn_ftp_password = isset($settings['cpn_ftp_password']) ? $settings['cpn_ftp_password'] : '';
$cpn_ftp_port = isset($settings['cpn_ftp_port']) ? $settings['cpn_ftp_port'] : 21;
$cpn_use_ftps = isset($settings['cpn_use_ftps']) ? $settings['cpn_use_ftps'] : '';
$cpn_use_pasv = isset($settings['cpn_use_pasv']) ? $settings['cpn_use_pasv'] : '';
$cpn_ftp_path = isset($settings['cpn_ftp_path']) ? $settings['cpn_ftp_path'] : '/';
$sku_checkbox = isset($settings['sku_checkbox']) ? $settings['sku_checkbox'] : '';
$cpn_enable_ftp_ie = isset($settings['cpn_enable_ftp_ie']) ? $settings['cpn_enable_ftp_ie'] : '';

$ord_ftp_server = isset($settings['ord_ftp_server']) ? $settings['ord_ftp_server'] : '';
$ord_ftp_user = isset($settings['ord_ftp_user']) ? $settings['ord_ftp_user'] : '';
$ord_ftp_password = isset($settings['ord_ftp_password']) ? $settings['ord_ftp_password'] : '';
$ord_ftp_port = isset($settings['ord_ftp_port']) ? $settings['ord_ftp_port'] : 21;
$ord_use_ftps = isset($settings['ord_use_ftps']) ? $settings['ord_use_ftps'] : '';
$ord_ftp_path = isset($settings['ord_ftp_path']) ? $settings['ord_ftp_path'] : '/';
$ord_use_pasv = isset($settings['ord_use_pasv']) ? $settings['ord_use_pasv'] : '';
$ord_enable_ftp_ie = isset($settings['ord_enable_ftp_ie']) ? $settings['ord_enable_ftp_ie'] : '';

$sbc_auto_export = isset($settings['sbc_auto_export']) ? $settings['sbc_auto_export'] : 'Disabled';
$sbc_auto_export_start_time = isset($settings['sbc_auto_export_start_time']) ? $settings['sbc_auto_export_start_time'] : '';
$sbc_auto_export_interval = isset($settings['sbc_auto_export_interval']) ? $settings['sbc_auto_export_interval'] : '';
$sbc_auto_export_order_status = isset($settings['sbc_auto_export_order_status']) ? $settings['sbc_auto_export_order_status'] : '';
$sbc_auto_export_ftp_file_name = isset($settings['sbc_auto_export_ftp_file_name']) ? $settings['sbc_auto_export_ftp_file_name'] : null;

$sbc_auto_import = isset($settings['sbc_auto_import']) ? $settings['sbc_auto_import'] : 'Disabled';
$sbc_auto_import_start_time = isset($settings['sbc_auto_import_start_time']) ? $settings['sbc_auto_import_start_time'] : '';
$sbc_auto_import_interval = isset($settings['sbc_auto_import_interval']) ? $settings['sbc_auto_import_interval'] : '';
$sbc_auto_import_profile = isset($settings['sbc_auto_import_profile']) ? $settings['sbc_auto_import_profile'] : '';
$sbc_auto_import_merge = isset($settings['sbc_auto_import_merge']) ? $settings['sbc_auto_import_merge'] : 0;
$sbc_auto_import_file = isset($settings['sbc_auto_import_file']) ? $settings['sbc_auto_import_file'] : null;
$sub_auto_export_profile = isset($settings['sub_auto_export_profile']) ? $settings['sub_auto_export_profile'] : '';

//For Order Test FTP 
$xa_ordr_all_piep_ftp = array('admin_ajax_url' => admin_url('admin-ajax.php'),'wt_nonce'=> wp_create_nonce(WF_ORDER_IMP_EXP_ID));
wp_localize_script('woocommerce-order-csv-importer', 'xa_ordr_piep_test_ftp', $xa_ordr_all_piep_ftp);

wp_localize_script('woocommerce-subscription-order-csv-importer', 'woocommerce_subscription_order_csv_cron_params', array('sbc_auto_export' => $sbc_auto_export, 'sbc_auto_import' => $sbc_auto_import));
if ($sbc_scheduled_export_timestamp = wp_next_scheduled('hf_subscription_order_csv_im_ex_auto_export')) {
    $sbc_scheduled_export_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $sbc_scheduled_export_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $sbc_scheduled_export_desc = __('There is no export scheduled.', 'wf_order_import_export');
}
if ($sbc_scheduled_import_timestamp = wp_next_scheduled('hf_subscription_order_csv_im_ex_auto_import')) {
    $sbc_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $sbc_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $sbc_scheduled_import_desc = __('There is no import scheduled.', 'wf_order_import_export');
}


$cpn_auto_export = isset($settings['cpn_auto_export']) ? $settings['cpn_auto_export'] : 'Disabled';
$cpn_auto_export_start_time = isset($settings['cpn_auto_export_start_time']) ? $settings['cpn_auto_export_start_time'] : '';
$cpn_auto_export_interval = isset($settings['cpn_auto_export_interval']) ? $settings['cpn_auto_export_interval'] : '';
$cpn_auto_export_ftp_file_name = isset($settings['cpn_auto_export_ftp_file_name']) ? $settings['cpn_auto_export_ftp_file_name'] : null;

$cpn_auto_import = isset($settings['cpn_auto_import']) ? $settings['cpn_auto_import'] : 'Disabled';
$cpn_auto_import_start_time = isset($settings['cpn_auto_import_start_time']) ? $settings['cpn_auto_import_start_time'] : '';
$cpn_auto_import_interval = isset($settings['cpn_auto_import_interval']) ? $settings['cpn_auto_import_interval'] : '';
$cpn_auto_import_profile = isset($settings['cpn_auto_import_profile']) ? $settings['cpn_auto_import_profile'] : '';
$cpn_auto_import_merge = isset($settings['cpn_auto_import_merge']) ? $settings['cpn_auto_import_merge'] : 0;
$cpn_auto_import_file = isset($settings['cpn_auto_import_file']) ? $settings['cpn_auto_import_file'] : null;

wp_localize_script('woocommerce-coupon-csv-importer3', 'woocommerce_coupon_csv_cron_params', array('cpn_auto_export' => $cpn_auto_export, 'cpn_auto_import' => $cpn_auto_import));
if ($cpn_scheduled_timestamp = wp_next_scheduled('wf_coupon_csv_im_ex_auto_export_coupons')) {
    $cpn_scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $cpn_scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $cpn_scheduled_desc = __('There is no export scheduled.', 'wf_order_import_export');
}
if ($cpn_scheduled_import_timestamp = wp_next_scheduled('wf_coupon_csv_im_ex_auto_import_coupons')) {
    $cpn_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $cpn_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $cpn_scheduled_import_desc = __('There is no import scheduled.', 'wf_order_import_export');
}


$ord_auto_export = isset($settings['ord_auto_export']) ? $settings['ord_auto_export'] : 'Disabled';
$ord_auto_export_start_time = isset($settings['ord_auto_export_start_time']) ? $settings['ord_auto_export_start_time'] : '';
$ord_auto_export_interval = isset($settings['ord_auto_export_interval']) ? $settings['ord_auto_export_interval'] : '';
$ord_auto_export_profile = isset($settings['ord_auto_export_profile']) ? $settings['ord_auto_export_profile'] : '';
$ord_auto_export_ftp_file_name = isset($settings['ord_auto_export_ftp_file_name']) ? $settings['ord_auto_export_ftp_file_name'] : null;

$ord_auto_export_order_status = isset($settings['ord_auto_export_order_status']) ? $settings['ord_auto_export_order_status'] : '';
$ord_auto_export_products = isset($settings['ord_auto_export_products']) ? $settings['ord_auto_export_products'] : array();
$ord_auto_export_email_order = isset($settings['ord_auto_export_email_order']) ? $settings['ord_auto_export_email_order'] :'';
$ord_auto_export_coupon_order = isset($settings['ord_auto_export_coupon_order']) ? $settings['ord_auto_export_coupon_order'] : '';
$ord_auto_date_from = isset($settings['ord_auto_date_from']) ? $settings['ord_auto_date_from'] : NULL;
$ord_auto_date_to = isset($settings['ord_auto_date_to']) ? $settings['ord_auto_date_to'] : NULL;

$ord_auto_import = isset($settings['ord_auto_import']) ? $settings['ord_auto_import'] : 'Disabled';
$ord_auto_import_delimiter = !empty($settings['ord_auto_import_delimiter']) ? $settings['ord_auto_import_delimiter'] : ',';
$ord_auto_import_start_time = isset($settings['ord_auto_import_start_time']) ? $settings['ord_auto_import_start_time'] : '';
$ord_auto_import_interval = isset($settings['ord_auto_import_interval']) ? $settings['ord_auto_import_interval'] : '';
$ord_auto_import_profile = isset($settings['ord_auto_import_profile']) ? $settings['ord_auto_import_profile'] : '';
$ord_auto_import_merge = isset($settings['ord_auto_import_merge']) ? $settings['ord_auto_import_merge'] : 0;
$ord_link_using_sku_cron = isset($settings['ord_link_using_sku_cron']) ? $settings['ord_link_using_sku_cron'] : 0;
$ord_auto_import_file = isset($settings['ord_auto_import_file']) ? $settings['ord_auto_import_file'] : null;
$wtcreateuser_cron = isset($settings['wtcreateuser_cron']) ? $settings['wtcreateuser_cron'] : 0;

wp_localize_script('woocommerce-order-csv-importer', 'woocommerce_order_csv_cron_params', array('ord_auto_export' => $ord_auto_export, 'ord_auto_import' => $ord_auto_import));
if ($ord_scheduled_timestamp = wp_next_scheduled('wf_order_csv_im_ex_auto_export_order')) {
    $ord_scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $ord_scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $ord_scheduled_desc = __('There is no export scheduled.', 'wf_order_import_export');
}
if ($ord_scheduled_import_timestamp = wp_next_scheduled('wf_order_csv_im_ex_auto_import_order')) {
    $ord_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_order_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $ord_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $ord_scheduled_import_desc = __('There is no import scheduled.', 'wf_order_import_export');
}

$xml_ftp_server = isset($settings['xml_ftp_server']) ? $settings['xml_ftp_server'] : '';
$xml_ftp_user = isset($settings['xml_ftp_user']) ? $settings['xml_ftp_user'] : '';
$xml_ftp_password = isset($settings['xml_ftp_password']) ? $settings['xml_ftp_password'] : '';
$xml_ftp_port = isset($settings['xml_ftp_port']) ? $settings['xml_ftp_port'] : 21;
$xml_use_ftps = isset($settings['xml_use_ftps']) ? $settings['xml_use_ftps'] : '';
$xml_use_pasv = isset($settings['xml_use_pasv']) ? $settings['xml_use_pasv'] : '';
$xml_enable_ftp_ie = isset($settings['xml_enable_ftp_ie']) ? $settings['xml_enable_ftp_ie'] : '';

$xml_ftp_path = isset($settings['xml_ftp_path']) ? $settings['xml_ftp_path'] : '/';
$xml_export_ftp_file_name = isset($settings['xml_export_ftp_file_name']) ? $settings['xml_export_ftp_file_name'] : null;
$xml_orderxml_auto_export_order_status = isset($settings['xml_orderxml_auto_export_order_status']) ? $settings['xml_orderxml_auto_export_order_status'] : '';
$xml_orderxml_auto_export_products = isset($settings['xml_orderxml_auto_export_products']) ? $settings['xml_orderxml_auto_export_products'] : array();


$xml_orderxml_auto_export = isset($settings['xml_orderxml_auto_export']) ? $settings['xml_orderxml_auto_export'] : 'Disabled';
$xml_orderxml_auto_export_start_time = isset($settings['xml_orderxml_auto_export_start_time']) ? $settings['xml_orderxml_auto_export_start_time'] : '';
$xml_orderxml_auto_export_interval = isset($settings['xml_orderxml_auto_export_interval']) ? $settings['xml_orderxml_auto_export_interval'] : '';

$xml_orderxml_auto_import = isset($settings['xml_orderxml_auto_import']) ? $settings['xml_orderxml_auto_import'] : 'Disabled';
$xml_orderxml_auto_import_start_time = isset($settings['xml_orderxml_auto_import_start_time']) ? $settings['xml_orderxml_auto_import_start_time'] : '';
$xml_orderxml_auto_import_interval = isset($settings['xml_orderxml_auto_import_interval']) ? $settings['xml_orderxml_auto_import_interval'] : '';

$xml_orderxml_auto_import_merge = isset($settings['xml_orderxml_auto_import_merge']) ? $settings['xml_orderxml_auto_import_merge'] : 0;
$xml_orderxml_multiple_files_import = isset($settings['xml_orderxml_multiple_files_import']) ? $settings['xml_orderxml_multiple_files_import'] : 0;
$csv_ordercsv_multiple_files_import = isset($settings['csv_ordercsv_multiple_files_import']) ? $settings['csv_ordercsv_multiple_files_import'] : 0;
$xml_orderxml_auto_import_file = isset($settings['xml_orderxml_auto_import_file']) ? $settings['xml_orderxml_auto_import_file'] : null;
$exclude_already_exported_xml = isset($settings['exclude_already_exported_xml']) ? $settings['exclude_already_exported_xml'] : 0;

$exclude_already_exported = isset($settings['exclude_already_exported']) ? $settings['exclude_already_exported'] : 0;
$export_to_separate_columns = isset($settings['export_to_separate_columns']) ? $settings['export_to_separate_columns'] : 0;
$include_meta = isset($settings['include_meta']) ? $settings['include_meta'] : 0;

// Order ipmort from URL 
$ord_enable_url_ie = isset($settings['ord_enable_url_ie']) ? $settings['ord_enable_url_ie'] : '';
$ord_auto_import_url = isset($settings['ord_auto_import_url']) ? $settings['ord_auto_import_url'] : null;
$ord_auto_import_url_delimiter = !empty($settings['ord_auto_import_url_delimiter']) ? $settings['ord_auto_import_url_delimiter'] : ',';
$ord_auto_import_url_start_time = isset($settings['ord_auto_import_url_start_time']) ? $settings['ord_auto_import_url_start_time'] : '';
$ord_auto_import_url_interval = isset($settings['ord_auto_import_url_interval']) ? $settings['ord_auto_import_url_interval'] : '';
$ord_auto_import_url_profile = isset($settings['ord_auto_import_url_profile']) ? $settings['ord_auto_import_url_profile'] : '';
$ord_auto_import_url_merge = isset($settings['ord_auto_import_url_merge']) ? $settings['ord_auto_import_url_merge'] : '';

wp_localize_script('woocommerce-order-xml-importerjs', 'woocommerce_order_xml_cron_params', array('xml_orderxml_auto_export' => $xml_orderxml_auto_export, 'xml_orderxml_auto_import' => $xml_orderxml_auto_import));
if ($xml_orderxml_scheduled_timestamp = wp_next_scheduled('wf_order_xml_im_ex_auto_export_orderxml')) {
    $xml_orderxml_scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'wf_customer_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $xml_orderxml_scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $xml_orderxml_scheduled_desc = __('There is no export scheduled.', 'wf_customer_import_export');
}
if ($xml_orderxml_scheduled_import_timestamp = wp_next_scheduled('wf_order_xml_im_ex_auto_import_orderxml')) {
    $xml_orderxml_scheduled_import_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_customer_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $xml_orderxml_scheduled_import_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $xml_orderxml_scheduled_import_desc = __('There is no import scheduled.', 'wf_customer_import_export');
}
if ($ord_scheduled_import_url_timestamp = wp_next_scheduled('wf_woocommerce_csv_im_ex_auto_import_orders_from_url')) {
    $ord_scheduled_import_url_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'wf_csv_import_export'), get_date_from_gmt(date('Y-m-d H:i:s', $ord_scheduled_import_url_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
    $ord_scheduled_import_url_desc = __('There is no import scheduled.', 'wf_csv_import_export');
}

$order_statuses = wc_get_order_statuses();

$subscription_order_statuses = wf_subcription_orderImpExpCsv_Admin_Screen::hf_get_subscription_statuses();
?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var x = "<?php echo $section; ?>" ;
        if(x === "order"){
            $('#subscription').css({"display": "none","position":"absolute"});
            $('#coupon').css({"display": "none","position":"absolute"});
            $('#xml').css({"display": "none","position":"absolute"});
            $('#url').css({"display": "none","position":"absolute"});
        }else if(x === "subscription"){
            $('#order').css({"display": "none","position":"absolute"});
            $('#coupon').css({"display": "none","position":"absolute"});
            $('#xml').css({"display": "none","position":"absolute"});
            $('#url').css({"display": "none","position":"absolute"});
        }else if(x === "coupon"){
            $('#order').css({"display": "none","position":"absolute"});
            $('#subscription').css({"display": "none","position":"absolute"});
            $('#xml').css({"display": "none","position":"absolute"});
            $('#url').css({"display": "none","position":"absolute"});
        }else if(x === 'xml'){
            $('#order').css({"display": "none","position":"absolute"});
            $('#subscription').css({"display": "none","position":"absolute"});
            $('#coupon').css({"display": "none","position":"absolute"});
            $('#url').css({"display": "none","position":"absolute"});
        }else if(x === 'url'){
            $('#order').css({"display": "none","position":"absolute"});
            $('#subscription').css({"display": "none","position":"absolute"});
            $('#coupon').css({"display": "none","position":"absolute"});
            $('#xml').css({"display": "none","position":"absolute"});
        }
    });
</script>

<div class="ordimpexp tool-box">
    <form action="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&action=settings&section='.$section); ?>" method="post">
         <?php wp_nonce_field( WF_ORDER_IMP_EXP_ID, 'wt_nonce' ); ?>
        
        <div class="tool-box bg-white p-20p" id="order">
            <h3 class="title aw-title"><?php _e('FTP Settings for Import/Export Orders CSV', 'wf_order_import_export'); ?></h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="ord_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="ord_enable_ftp_ie" id="ord_enable_ftp_ie" class="checkbox" <?php checked($ord_enable_ftp_ie, 1); ?> />
                        <p style="font-size: 12px"><?php _e('Check to enable FTP', 'wf_order_import_export'); ?></p> 
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div>
                            <table class="form-table" id="ord_export_section_all">
                                <tr>
                                    <th>
                                        <label for="ord_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_ftp_server" id="ord_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $ord_ftp_server; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP server hostname', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_ftp_user" id="ord_ftp_user" placeholder="" value="<?php echo $ord_ftp_user; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP username', 'wf_order_import_export'); ?></p>                                
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" name="ord_ftp_password" id="ord_ftp_password" placeholder="" value="<?php echo $ord_ftp_password; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP password', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_ftp_port"><?php _e('FTP Port', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_ftp_port" id="ord_ftp_port" placeholder="" value="<?php echo $ord_ftp_port; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your port number', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="ord_use_ftps" id="ord_use_ftps" class="checkbox" <?php checked($ord_use_ftps, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to send data over a network with SSL encryption', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="ord_use_pasv" id="ord_use_pasv" class="checkbox" <?php checked($ord_use_pasv, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to turn passive mode on', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <input type="button" id="ordr_test_ftp_connection" class="button button-primary" value="<?php _e('Test FTP', 'wf_order_import_export'); ?>" />
                                        <span class ="spinner " ></span>
                                    </th>
                                    <td id="ordr_ftp_test_notice"></td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_ftp_path"><?php _e('Export Path', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_ftp_path" id="ord_ftp_path"  value="<?php echo $ord_ftp_path; ?>"/>
                                        <p style="font-size: 12px"><?php _e('Specify the path in the server to which the CSV file will be exported', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f1f1f1">
                                    <th>
                                        <label for="ord_auto_export_ftp_file_name"><?php _e('Export Filename', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_auto_export_ftp_file_name" id="ord_auto_export_ftp_file_name"  value="<?php echo $ord_auto_export_ftp_file_name; ?>" placeholder="For example sample.csv"/>
                                        <p style="font-size: 12px"><?php _e('Specify the name of the CSV file exported', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="ord_auto_export"><?php _e('Automatically Export Orders', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="ord_auto_export" name="ord_auto_export">
                                            <option <?php if ($ord_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($ord_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable exporting order automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="ord_export_section">
                                    <tr>
                                        <th>
                                            <label for="ord_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ord_auto_export_start_time" id="ord_auto_export_start_time"  value="<?php echo $ord_auto_export_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $ord_scheduled_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ord_auto_export_interval" id="ord_auto_export_interval"  value="<?php echo $ord_auto_export_interval; ?>"  />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="exclude_already_exported"><?php _e('Exclude Already Exported', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input name= "exclude_already_exported" id="exclude_already_exported"  class="checkbox" type="checkbox" <?php checked($exclude_already_exported, 1); ?>  >
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="export_to_separate_columns"><?php _e('Export Line Items Into Separate Columns', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input name= "export_to_separate_columns" id="export_to_separate_columns" class="checkbox" type="checkbox" <?php checked($export_to_separate_columns, 1); ?> >
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="include_meta"><?php _e('Include hidden meta data', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input name= "include_meta" id="include_meta" class="checkbox" type="checkbox" <?php checked($include_meta, 1); ?> >
                                        </td>
                                    </tr>
                                    <?php
                                    $ord_exp_mapping_from_db = get_option('xa_ordr_csv_export_mapping');
                                    if (!empty($ord_exp_mapping_from_db)) {
                                        ?>
                                        <tr>
                                            <th>
                                                <label for="ord_auto_export_profile"><?php _e('Select an export mapping file.'); ?></label>
                                            </th>
                                            <td>
                                                <select name="ord_auto_export_profile">
                                                    <option value="">--Select--</option>
                                                    <?php foreach ($ord_exp_mapping_from_db as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php selected($key, $ord_auto_export_profile); ?>><?php echo $key; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_export_order_status"><?php _e('Order Statuses', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <select id="ord_auto_export_order_status" name="ord_auto_export_order_status[]" data-placeholder="<?php _e('All Orders', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                                                <?php
                                                foreach ($order_statuses as $key => $column) { ?>                                                    
                                                    <?php
                                                    if(!empty($ord_auto_export_order_status) && in_array($key, $ord_auto_export_order_status)){
                                                    echo '<option value="' . $key . '" selected>' . $column . '</option>';
                                                    }else {
                                                    echo '<option value="' . $key . '" >' . $column . '</option>';
                                                    }
                                                }                                                                                               
                                                ?>
                                            </select>
                                            <p style="font-size: 12px"><?php _e('Orders with these status will be exported.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="v_products"><?php _e('Products', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="v_products" name="products[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wf_order_import_export'); ?>">
                                            <?php
                                                $product_ids = $ord_auto_export_products;

						foreach ( $product_ids as $product_id ) {
                                                    $product = wc_get_product( $product_id );
                                                    if ( is_object( $product ) ) {
                                                            echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                                                    }
						}
                                            ?>
                                            </select>
                                            <p style="font-size: 12px"><?php _e('Export orders for the selected specific products.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th>
                                            <label for="v_email"><?php _e('Email', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
   
                                          <select class="wc-customer-search" multiple="multiple" style="width: 50%;" id="v_email" name="email[]" data-placeholder="<?php esc_attr_e('Search for a Customer&hellip;', 'wf_order_import_export'); ?>">
                                           <?php
                                              if (!empty($ord_auto_export_email_order)) {
                                                  foreach ($ord_auto_export_email_order as $user_id) {
                                                      $user = get_user_by('id', absint($user_id));
                                                      $user_string = sprintf(
                                                              /* translators: 1: user display name 2: user ID 3: user email */
                                                              esc_html__('%1$s', 'wf_customer_import_export'), $user->user_email
                                                      );
                                                      echo '<option value="' . esc_attr($user_id) . '" selected=' . "selected" . '>' . wp_kses_post($user_string) . '<option>';
                                                  }
                                              }
                                              ?>
                                          </select>
                                            <p style="font-size: 12px"><?php _e('Export orders based on customer email.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="v_coupons"><?php _e('Coupons', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="coupon" id="v_coupons" value="<?php echo $ord_auto_export_coupon_order ?>" placeholder="<?php _e('Enter coupon codes separated by \',\'', 'wf_order_import_export'); ?>" class="input-text" />
                                            <p style="font-size: 12px"><?php _e('Export orders based on coupons applied.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f1f1f1">
                                        <th>
                                            <label for="ord_auto_export_order_dates"><?php _e('Order Date between', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ord_auto_date_from" id="datepick_auto1" placeholder="<?php _e('From date', 'wf_order_import_export'); ?>" value="<?php echo $ord_auto_date_from; ?>" class="input-text" /> -
                                            <input type="text" name="ord_auto_date_to" id="datepick_auto2" placeholder="<?php _e('To date', 'wf_order_import_export'); ?>" value="<?php echo $ord_auto_date_to; ?>" class="input-text" />
                                            <p style="font-size: 12px"><?php _e('Orders between these date will be exported.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                </tbody>
                                
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="ord_auto_import"><?php _e('Automatically Import Orders', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="ord_auto_import" name="ord_auto_import">
                                            <option <?php if ($ord_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($ord_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable importing order automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="ord_import_section">
                                    <tr>
                                        <th>
                                            <label for="csv_ordercsv_multiple_files_import"><?php _e('Import Multiple CSV Files', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="csv_ordercsv_multiple_files_import" id="csv_ordercsv_multiple_files_import"  class="checkbox" <?php checked($csv_ordercsv_multiple_files_import, 1); ?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_import_file"><?php _e('Import File', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ord_auto_import_file" id="ord_auto_import_file" value="<?php echo $ord_auto_import_file; ?>" placeholder="For example /root/temp/a.csv"/>
                                            <p style="font-size: 12px"><?php _e('Complete CSV path to import multiple files and also include filename in case of single file.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="wtcreateuser_cron"><?php _e('Create user', 'wf_order_import_export'); ?></label><br/></th>
                                        <td>
                                            <input type="checkbox" id="wtcreateuser_cron" name="wtcreateuser_cron" class="checkbox" <?php checked($wtcreateuser_cron, 1); ?> />
                                            <p><small style="color:red;"><?php _e('If the user (customer) belonging to an order doesn\'t exist in the target site, a new user will be created. Leave this option unchecked if you do not want new users to be created. ', 'wf_order_import_export'); ?></small></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_import_delimiter"><?php _e('Delimiter', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" id="ord_auto_import_delimiter" name="ord_auto_import_delimiter" placeholder="," size="2" value="<?php echo $ord_auto_import_delimiter; ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ord_auto_import_start_time" id="ord_auto_import_start_time"  value="<?php echo $ord_auto_import_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $ord_scheduled_import_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ord_auto_import_interval" id="ord_auto_import_interval"  value="<?php echo $ord_auto_import_interval; ?>"  />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_import_merge"><?php _e('Update Orders if exist', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="ord_auto_import_merge" id="ord_auto_import_merge"  class="checkbox" <?php checked($ord_auto_import_merge, 1); ?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="ord_link_using_sku_cron"><?php _e('Link products using SKU instead of Product ID','wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="ord_link_using_sku_cron" id="ord_link_using_sku_cron" class="checkbox" <?php checked($ord_link_using_sku_cron, 1); ?> />
                                        </td>
                                    </tr>
                                    <?php
                                    $ord_mapping_from_db = get_option('wf_order_csv_imp_exp_mapping');
                                    if (!empty($ord_mapping_from_db)) {
                                        ?>
                                        <tr>
                                            <th>
                                                <label for="ord_auto_import_profile"><?php _e('Select a mapping file.'); ?></label>
                                            </th>
                                            <td>
                                                <select name="ord_auto_import_profile" id="ord_auto_import_profile">
                                                    <option value="">--Select--</option>
                                                    <?php foreach ($ord_mapping_from_db as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php selected($key, $ord_auto_import_profile); ?>><?php echo $key; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>   
        
        
        <div class="tool-box bg-white p-20p" id="subscription">
            <h3 class="title aw-title"><?php _e('FTP Settings for Import/Export Subscriptions', 'wf_order_import_export'); ?></h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="sbc_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="sbc_enable_ftp_ie" id="sbc_enable_ftp_ie" class="checkbox" <?php checked($sbc_enable_ftp_ie, 1); ?> />
                        <p style="font-size: 12px"><?php _e('Check to enable FTP', 'wf_order_import_export'); ?></p> 
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div>
                            <table class="form-table" id="sbc_export_section_all">
                                <tr>
                                    <th>
                                        <label for="sbc_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="sbc_ftp_server" id="sbc_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $sbc_ftp_server; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP server hostname', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="sbc_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="sbc_ftp_user" id="sbc_ftp_user" value="<?php echo $sbc_ftp_user; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP username', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="sbc_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" name="sbc_ftp_password" id="sbc_ftp_password"  value="<?php echo $sbc_ftp_password; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP password', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="sbc_ftp_port"><?php _e('FTP Port', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="sbc_ftp_port" id="sbc_ftp_port"  value="<?php echo $sbc_ftp_port; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your port number', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="sbc_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="sbc_use_ftps" id="sbc_use_ftps" class="checkbox" <?php checked($sbc_use_ftps, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to send data over a network with SSL encryption', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="sbc_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="sbc_use_pasv" id="sbc_use_pasv" class="checkbox" <?php checked($sbc_use_pasv, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to turn passive mode on', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="sbc_ftp_path"><?php _e('Export Path', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="sbc_ftp_path" id="sbc_ftp_path"  value="<?php echo $sbc_ftp_path; ?>"/>
                                        <p style="font-size: 12px"><?php _e('Exported CSV will be stored in the above directory.', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f1f1f1">
                                    <th>
                                        <label for="sbc_auto_export_ftp_file_name"><?php _e('Export Filename', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="sbc_auto_export_ftp_file_name" id="sbc_auto_export_ftp_file_name"  value="<?php echo $sbc_auto_export_ftp_file_name; ?>" placeholder="For example sample.csv"/>
                                        <p style="font-size: 12px"><?php _e('Specify the name of the CSV file exported', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="sbc_auto_export"><?php _e('Automatically Export Subscriptions', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="sbc_auto_export" name="sbc_auto_export">
                                            <option <?php if ($sbc_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($sbc_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable exporting subscription order automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="sbc_export_section">
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="sbc_auto_export_start_time" id="sbc_auto_export_start_time"  value="<?php echo $sbc_auto_export_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $sbc_scheduled_export_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="sbc_auto_export_interval" id="sbc_auto_export_interval"  value="<?php echo $sbc_auto_export_interval; ?>"  />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_export_order_status"><?php _e('Order Statuses', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <select id="sbc_auto_export_order_status" name="sbc_auto_export_order_status[]" data-placeholder="<?php _e('All Orders', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                                                <?php
                                                foreach ($subscription_order_statuses as $key => $column) {
                                                    if(!empty($sbc_auto_export_order_status) && in_array($key, $sbc_auto_export_order_status)){
                                                    echo '<option value="' . $key . '" selected>' . $column . '</option>';
                                                    }else {
                                                    echo '<option value="' . $key . '" >' . $column . '</option>';
                                                    }
                                                }                                                                                               
                                                ?>
                                            </select>
                                            <p style="font-size: 12px"><?php _e('Orders with these status will be exported.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <?php 
                                    $sub_ord_exp_mapping_from_db = get_option('wt_subscription_csv_export_mapping');
                                    if (!empty($sub_ord_exp_mapping_from_db)) {
                                        ?>
                                        <tr style="border-bottom: 1px solid #f1f1f1">
                                            <th>
                                                <label for="sub_auto_export_profile"><?php _e('Select an export mapping file.', 'wf_order_import_export'); ?></label>
                                            </th>
                                            <td>
                                                <select name="sub_auto_export_profile">
                                                    <option value="">--Select--</option>
                                                    <?php foreach ($sub_ord_exp_mapping_from_db as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php selected($key, $sub_auto_export_profile); ?>><?php echo $key; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="sbc_auto_import"><?php _e('Automatically Import Subscriptions', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="sbc_auto_import" name="sbc_auto_import">
                                            <option <?php if ($sbc_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($sbc_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable importing subscription order automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="sbc_import_section">
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_import_file"><?php _e('Import File', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="sbc_auto_import_file" id="sbc_auto_import_file" value="<?php echo $sbc_auto_import_file; ?>" placeholder="For example /root/temp/a.csv"/>
                                            <p style="font-size: 12px"><?php _e('Complete CSV path including filename..', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="sbc_auto_import_start_time" id="sbc_auto_import_start_time"  value="<?php echo $sbc_auto_import_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $sbc_scheduled_import_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="sbc_auto_import_interval" id="sbc_auto_import_interval"  value="<?php echo $sbc_auto_import_interval; ?>"  />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="sbc_auto_import_merge"><?php _e('Merge Orders if exist', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="sbc_auto_import_merge" id="sbc_auto_import_merge"  class="checkbox" <?php checked($sbc_auto_import_merge, 1); ?> />
                                        </td>
                                    </tr>
                                    <?php
                                    $sbc_mapping_from_db = get_option('wf_subcription_order_csv_imp_exp_mapping');
                                    if (!empty($sbc_mapping_from_db)) {
                                        ?>
                                        <tr>
                                            <th>
                                                <label for="sbc_auto_import_profile"><?php _e('Select a mapping file.'); ?></label>
                                            </th>
                                            <td>
                                                <select name="sbc_auto_import_profile" id="sbc_auto_import_profile">
                                                    <option value="">--Select--</option>
                                                    <?php foreach ($sbc_mapping_from_db as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php selected($key, $sbc_auto_import_profile); ?>><?php echo $key; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        
        <div class="tool-box bg-white p-20p" id="coupon">
            <h3 class="title aw-title"><?php _e('FTP Settings for Import/Export Coupons', 'wf_order_import_export'); ?></h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="cpn_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="cpn_enable_ftp_ie" id="cpn_enable_ftp_ie" class="checkbox" <?php checked($cpn_enable_ftp_ie, 1); ?> />
                        <p style="font-size: 12px"><?php _e('Check to enable FTP', 'wf_order_import_export'); ?></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div>
                            <table class="form-table" id="cpn_export_section_all">
                                <tr>
                                    <th>
                                        <label for="cpn_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="cpn_ftp_server" id="cpn_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $cpn_ftp_server; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP server hostname', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="cpn_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="cpn_ftp_user" id="cpn_ftp_user" placeholder="" value="<?php echo $cpn_ftp_user; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP username', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="cpn_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" name="cpn_ftp_password" id="cpn_ftp_password" placeholder="" value="<?php echo $cpn_ftp_password; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP password', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="cpn_ftp_port"><?php _e('FTP Port', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="cpn_ftp_port" id="cpn_ftp_port" placeholder="" value="<?php echo $cpn_ftp_port; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your port number', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="cpn_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="cpn_use_ftps" id="cpn_use_ftps" class="checkbox" <?php checked($cpn_use_ftps, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to send data over a network with SSL encryption', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="cpn_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="cpn_use_pasv" id="cpn_use_pasv" class="checkbox" <?php checked($cpn_use_pasv, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to turn passive mode on', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="cpn_ftp_path"><?php _e('Export Path', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="cpn_ftp_path" id="cpn_ftp_path"  value="<?php echo $cpn_ftp_path; ?>"/>
                                        <p style="font-size: 12px"><?php _e('Specify the path in the server to which the CSV file will be exported', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f1f1f1">
                                    <th>
                                        <label for="cpn_auto_export_ftp_file_name"><?php _e('Export Filename', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="cpn_auto_export_ftp_file_name" id="cpn_auto_export_ftp_file_name"  value="<?php echo $cpn_auto_export_ftp_file_name; ?>" placeholder="For example sample.csv"/>
                                        <p style="font-size: 12px"><?php _e('Specify the name of the CSV file exported', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="cpn_auto_export"><?php _e('Automatically Export Coupons', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="cpn_auto_export" name="cpn_auto_export">
                                            <option <?php if ($cpn_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($cpn_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable exporting coupon automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="cpn_export_section">
                                    <tr>
                                        <th>
                                            <label for="cpn_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="cpn_auto_export_start_time" id="cpn_auto_export_start_time"  value="<?php echo $cpn_auto_export_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $cpn_scheduled_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px dotted #f1f1f1">
                                        <th>
                                            <label for="cpn_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="cpn_auto_export_interval" id="cpn_auto_export_interval"  value="<?php echo $cpn_auto_export_interval; ?>"  />
                                        </td>
                                    </tr>
                                </tbody>
                                
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="cpn_auto_import"><?php _e('Automatically Import Coupons', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="cpn_auto_import" name="cpn_auto_import">
                                            <option <?php if ($cpn_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($cpn_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable importing coupon automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="cpn_import_section">
                                    <tr>
                                        <th>
                                            <label for="cpn_auto_import_file"><?php _e('Import File', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="cpn_auto_import_file" id="cpn_auto_import_file" value="<?php echo $cpn_auto_import_file; ?>" placeholder="For example /root/temp/a.csv"/>
                                            <p style="font-size: 12px"><?php _e('Complete CSV path including filename.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="cpn_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="cpn_auto_import_start_time" id="cpn_auto_import_start_time"  value="<?php echo $cpn_auto_import_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $cpn_scheduled_import_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="cpn_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="cpn_auto_import_interval" id="cpn_auto_import_interval"  value="<?php echo $cpn_auto_import_interval; ?>"  />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="cpn_auto_import_merge"><?php _e('Merge Coupons if exist', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="cpn_auto_import_merge" id="cpn_auto_import_merge"  class="checkbox" <?php checked($cpn_auto_import_merge, 1); ?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="sku_checkbox"><?php _e('Use product SKU instead of product ID in coupon restriction settings', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="sku_checkbox" id="sku_checkbox" class="checkbox" <?php checked($sku_checkbox, 1); ?> />
                                        </td>
                                    </tr>
                                    <?php
                                    $cpn_mapping_from_db = get_option('wf_cpn_csv_imp_exp_mapping');
                                    if (!empty($cpn_mapping_from_db)) {
                                        ?>
                                        <tr>
                                            <th>
                                                <label for="cpn_auto_import_profile"><?php _e('Select a mapping file.'); ?></label>
                                            </th>
                                            <td>
                                                <select name="cpn_auto_import_profile" id="cpn_auto_import_profile">
                                                    <option value="">--Select--</option>
                                                    <?php foreach ($cpn_mapping_from_db as $key => $value) { ?>
                                                        <option value="<?php echo $key; ?>" <?php selected($key, $cpn_auto_import_profile); ?>><?php echo $key; ?></option>

                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        
        <div class="tool-box bg-white p-20p" id="xml">
            <h3 class="title aw-title"><?php _e('FTP Settings for Import/Export Order XML', 'wf_order_import_export'); ?></h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="xml_enable_ftp_ie"><?php _e('Enable FTP', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="xml_enable_ftp_ie" id="xml_enable_ftp_ie" class="checkbox" <?php checked($xml_enable_ftp_ie, 1); ?> />
                        <p style="font-size: 12px"><?php _e('Check to enable FTP', 'wf_order_import_export'); ?></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div>
                            <table class="form-table" id="xml_orderxml_export_section_all">
                                <tr>
                                    <th>
                                        <label for="xml_ftp_server"><?php _e('FTP Server Host/IP', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="xml_ftp_server" id="xml_ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'wf_order_import_export'); ?>" value="<?php echo $xml_ftp_server; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP server hostname', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="xml_ftp_user"><?php _e('FTP User Name', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="xml_ftp_user" id="xml_ftp_user" value="<?php echo $xml_ftp_user; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP username', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="xml_ftp_password"><?php _e('FTP Password', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" name="xml_ftp_password" id="xml_ftp_password"  value="<?php echo $xml_ftp_password; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your FTP password', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="xml_ftp_port"><?php _e('FTP Port', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="xml_ftp_port" id="xml_ftp_port"  value="<?php echo $xml_ftp_port; ?>" class="input-text" />
                                        <p style="font-size: 12px"><?php _e('Enter your port number', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="xml_use_ftps"><?php _e('Use FTPS', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="xml_use_ftps" id="xml_use_ftps" class="checkbox" <?php checked($xml_use_ftps, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to send data over a network with SSL encryption', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="xml_use_pasv"><?php _e('Enable Passive mode', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="xml_use_pasv" id="xml_use_pasv" class="checkbox" <?php checked($xml_use_pasv, 1); ?> />
                                        <p style="font-size: 12px"><?php _e('Enable this to turn passive mode on', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <?php /* <tr>
                                  <th>
                                  <input type="button" id="ordr_test_ftp_connection" class="button button-primary" value="<?php _e('Test FTP', 'wf_order_import_export'); ?>" />
                                  <span class ="spinner " ></span>
                                  </th>
                                  <td id="ordr_ftp_test_notice"></td>
                                  </tr> */ ?>
                                <tr>
                                    <th>
                                        <label for="xml_ftp_path"><?php _e('Export Path', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="xml_ftp_path" id="xml_ftp_path"  value="<?php echo $xml_ftp_path; ?>"/>
                                        <p style="font-size: 12px"><?php _e('Exported XML will be stored in the above directory.', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="xml_export_ftp_file_name"><?php _e('Export Filename', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="xml_export_ftp_file_name" id="xml_export_ftp_file_name"  value="<?php echo $xml_export_ftp_file_name; ?>" placeholder="For example sample.xml"/>
                                        <p style="font-size: 12px"><?php _e('Exported XML will have the above file name(if specified).', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="xml_orderxml_auto_export"><?php _e('Automatically Export orders', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="xml_orderxml_auto_export" name="xml_orderxml_auto_export">
                                            <option <?php if ($xml_orderxml_auto_export === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($xml_orderxml_auto_export === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable exporting order xml automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="xml_orderxml_export_section">
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_auto_export_start_time"><?php _e('Export Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="xml_orderxml_auto_export_start_time" id="xml_orderxml_auto_export_start_time"  value="<?php echo $xml_orderxml_auto_export_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $xml_orderxml_scheduled_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_auto_export_interval"><?php _e('Export Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="xml_orderxml_auto_export_interval" id="xml_orderxml_auto_export_interval"  value="<?php echo $xml_orderxml_auto_export_interval; ?>"  />
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px dotted #f1f1f1">
                                        <th>
                                            <label for="xml_orderxml_auto_export_order_status"><?php _e('Order Statuses', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <select id="xml_orderxml_auto_export_order_status" name="xml_orderxml_auto_export_order_status[]" data-placeholder="<?php _e('All Orders', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                                                <?php
                                                foreach ($order_statuses as $key => $column) {
                                                    if(!empty($xml_orderxml_auto_export_order_status) && in_array($key, $xml_orderxml_auto_export_order_status)){
                                                    echo '<option value="' . $key . '" selected>' . $column . '</option>';
                                                    }else {
                                                    echo '<option value="' . $key . '" >' . $column . '</option>';
                                                    }
                                                }                                                                                              
                                                ?>
                                            </select>
                                            <p style="font-size: 12px"><?php _e('Orders with these status will be exported.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px dotted #f1f1f1">
                                        <th>
                                            <label for="exclude_already_exported_xml"><?php _e('Exclude Already Exported', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input name= "exclude_already_exported_xml" id="exclude_already_exported_xml"  class="checkbox" type="checkbox" <?php checked($exclude_already_exported_xml, 1); ?>  >
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="v_products_xml"><?php _e('Products', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="v_products_xml" name="products_xml[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wf_order_import_export'); ?>">
                                            <?php
                                                $product_ids = $xml_orderxml_auto_export_products;

						foreach ( $product_ids as $product_id ) {
                                                    $product = wc_get_product( $product_id );
                                                    if ( is_object( $product ) ) {
                                                            echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                                                    }
						}
                                            ?>
                                            </select>
                                            <p style="font-size: 12px"><?php _e('Export orders for the selected specific products.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                </tbody>
                                
                                <tr style="border-bottom: 1px dotted #f1f1f1">
                                    <th>
                                        <label for="xml_orderxml_auto_import"><?php _e('Automatically Import Orders', 'wf_order_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <select class="" style="" id="xml_orderxml_auto_import" name="xml_orderxml_auto_import">
                                            <option <?php if ($xml_orderxml_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'wf_order_import_export'); ?></option>
                                            <option <?php if ($xml_orderxml_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'wf_order_import_export'); ?></option>
                                        </select>
                                        <p style="font-size: 12px"><?php _e('Select to enable importing order xml automatically', 'wf_order_import_export'); ?></p>
                                    </td>
                                </tr>
                                
                                <tbody class="xml_orderxml_import_section">
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_multiple_files_import"><?php _e('Import Multiple XML Files', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="xml_orderxml_multiple_files_import" id="xml_orderxml_multiple_files_import"  class="checkbox" <?php checked($xml_orderxml_multiple_files_import, 1); ?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_auto_import_file"><?php _e('Import File', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="xml_orderxml_auto_import_file" id="xml_orderxml_auto_import_file" value="<?php echo $xml_orderxml_auto_import_file; ?>" placeholder="For example /root/temp/a.xml"/>
                                            <p style="font-size: 12px"><?php _e('Complete XML path to import multiple files and also include filename in case of single file.', 'wf_order_import_export'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_auto_import_start_time"><?php _e('Import Start Time', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="xml_orderxml_auto_import_start_time" id="xml_orderxml_auto_import_start_time"  value="<?php echo $xml_orderxml_auto_import_start_time; ?>"/>
                                            <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_order_import_export'), date_i18n(wc_time_format())) . ' ' . $xml_orderxml_scheduled_import_desc; ?></span>
                                            <br/>
                                            <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_order_import_export'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="xml_orderxml_auto_import_interval" id="xml_orderxml_auto_import_interval"  value="<?php echo $xml_orderxml_auto_import_interval; ?>"  />
                                        </td>
                                    </tr>                                    
                                    <tr>
                                        <th>
                                            <label for="xml_orderxml_auto_import_merge"><?php _e('Update Order if Exist', 'wf_order_import_export'); ?></label>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="xml_orderxml_auto_import_merge" id="xml_orderxml_auto_import_merge"  class="checkbox" <?php checked($xml_orderxml_auto_import_merge, 1); ?> />
                                        </td>
                                    </tr>
                                </tbody>
                                
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        
        <div class="tool-box bg-white p-20p" id="url">
            <h3 class="title aw-title"><?php _e('URL Settings to Import Orders', 'wf_csv_import_export'); ?></h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="ord_enable_url_ie"><?php _e('Enable URL Import', 'wf_csv_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="ord_enable_url_ie" id="ord_enable_url_ie" class="checkbox" <?php checked($ord_enable_url_ie, 1); ?> />
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table class="form-table" id="ord_import_from_url_section_all">
                            <tbody class="ord_import_from_url_section">
                                <tr>
                                    <th>
                                        <label for="ord_auto_import_url"><?php _e('Import URL', 'wf_csv_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_auto_import_url" id="ord_auto_import_url" value="<?php echo $ord_auto_import_url; ?>" placeholder="For example /root/temp/a.csv"/>
                                        <p style="font-size: 12px"><?php _e('Complete CSV path including name.', 'wf_csv_import_export'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_auto_import_url_delimiter"><?php _e('Delimiter', 'wf_csv_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="ord_auto_import_url_delimiter" name="ord_auto_import_url_delimiter" placeholder="," size="2" value="<?php echo $ord_auto_import_url_delimiter; ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_auto_import_url_start_time"><?php _e('Import Start Time', 'wf_csv_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_auto_import_url_start_time" id="ord_auto_import_url_start_time"  value="<?php echo $ord_auto_import_url_start_time; ?>"/>
                                        <span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'wf_csv_import_export'), date_i18n(wc_time_format())) . ' ' . $ord_scheduled_import_url_desc; ?></span>
                                        <br/>
                                        <span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'wf_csv_import_export'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_auto_import_url_interval"><?php _e('Import Interval [ Minutes ]', 'wf_csv_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="ord_auto_import_url_interval" id="ord_auto_import_url_interval"  value="<?php echo $ord_auto_import_url_interval; ?>"  />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="ord_auto_import_url_merge"><?php _e('Update Order if exist', 'wf_csv_import_export'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="ord_auto_import_url_merge" id="ord_auto_import_url_merge"  class="checkbox" <?php checked($ord_auto_import_url_merge, 1); ?> />
                                    </td>
                                </tr>
                                <?php
                                $ord_mapping_from_db = get_option('wf_order_csv_imp_exp_mapping');
                                if (!empty($ord_mapping_from_db)) {
                                    ?>
                                    <tr>
                                        <th>
                                            <label for="ord_auto_import_url_profile"><?php _e('Select a mapping file.'); ?></label>
                                        </th>
                                        <td>
                                            <select name="ord_auto_import_url_profile">
                                                <option value="">--Select--</option>
                                                <?php foreach ($ord_mapping_from_db as $key => $value) { ?>
                                                    <option value="<?php echo $key; ?>" <?php selected($key, $ord_auto_import_url_profile); ?>><?php echo $key; ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        
        <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'wf_order_import_export'); ?>" /></p>
    </form>
</div>