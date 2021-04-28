<?php

if (!defined('ABSPATH')) {
    exit;
}

class wf_subcription_orderImpExpCsv_Exporter {

    /**
     * Order Exporter Tool
     */
    public static function do_export($post_type = 'shop_subscription', $order_IDS = array()) {
        global $wpdb;
        if (!class_exists('WooCommerce')) :
            require WP_PLUGIN_DIR.'/woocommerce/woocommerce.php';
            require WP_PLUGIN_DIR.'/woocommerce/includes/class-wc-order-factory.php';
            WC()->init();
        endif;

        $export_limit = !empty($_POST['limit']) ? absint($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $export_offset = !empty($_POST['offset']) ? absint($_POST['offset']) : 0;
        $csv_columns = include( 'data/data-wf-post-subscription-columns.php' );
        $user_columns_name = !empty($_POST['columns_name']) ? wc_clean($_POST['columns_name']) : $csv_columns;
        $export_columns = !empty($_POST['columns']) ? wc_clean($_POST['columns']) : array();
        $end_date = empty($_POST['end_date']) ? date('Y-m-d 23:59', current_time('timestamp')) : sanitize_text_field($_POST['end_date']) . ' 23:59:59.99';
        $start_date = empty($_POST['start_date']) ? date('Y-m-d 00:00', 0) : sanitize_text_field($_POST['start_date']);
        $delimiter = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ',';// WPCS: CSRF ok, input var ok. 
        $email = !empty($_POST['email']) ? wc_clean($_POST['email']) : array();
        $delimiter = self::wt_set_csv_delimiter($delimiter);
        $products = !empty($_POST['products']) ? wc_clean($_POST['products']): array();
        $coupons = !empty($_POST['coupons']) ? explode(',',trim(strtolower(wc_clean($_POST['coupons'])))) : array();
        $new_profile = !empty($_POST['new_profile']) ? sanitize_text_field($_POST['new_profile']) : '';
        if ($new_profile !== '') {
            $mapped = array();
            if (!empty($export_columns)) {
                foreach ($export_columns as $key => $value) {
                    $mapped[$key] = $user_columns_name[$key];
                }
            }
            $export_profile_array = get_option('wt_subscription_csv_export_mapping');
            $export_profile_array[$new_profile] = $mapped;
            update_option('wt_subscription_csv_export_mapping', $export_profile_array);
        }
        
         if (!empty($_POST['auto_export_profile'])) {
            $export_profile_array = get_option('wt_subscription_csv_export_mapping');
            $user_columns_name = array();
            $user_columns_name = $export_profile_array[sanitize_text_field($_POST['auto_export_profile'])];
            foreach ($user_columns_name as $column => $value) {
                $export_columns[$column] = $column;
            }
        }

        if ($limit > $export_limit)
            $limit = $export_limit;

        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $enable_ftp_ie = isset($settings['sbc_enable_ftp_ie']) ? $settings['sbc_enable_ftp_ie'] : '';
        if($enable_ftp_ie){
            $ftp_server = isset($settings['sbc_ftp_server']) ? $settings['sbc_ftp_server'] : '';
            $ftp_user = isset($settings['sbc_ftp_user']) ? $settings['sbc_ftp_user'] : '';
            $ftp_password = isset($settings['sbc_ftp_password']) ? $settings['sbc_ftp_password'] : '';
            $ftp_port = isset($settings['sbc_ftp_port']) ? $settings['sbc_ftp_port'] : 21;
            $use_ftps = isset($settings['sbc_use_ftps']) ? $settings['sbc_use_ftps'] : '';
            $use_pasv = isset($settings['sbc_use_pasv']) ? $settings['sbc_use_pasv'] : '';
            $remote_path = isset($settings['sbc_ftp_path']) ? $settings['sbc_ftp_path'] : null;
        }

        $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ob_end_clean();

        if ($enable_ftp_ie) {
            $upload_path = wp_upload_dir();
            $file_path = $upload_path['path'] . '/';
            $file = (!empty($settings['sbc_auto_export_ftp_file_name'])) ? $file_path . $settings['sbc_auto_export_ftp_file_name'] : $file_path . $post_type . "-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv";
            $fp = fopen($file, 'w');
        } else {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename=woocommerce-subscription-order-export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');

            $fp = fopen('php://output', 'w');
        }

        // Headers
        $subscription_plugin = 'WC';
        if(class_exists('HF_Subscription')){
            $subscription_plugin = 'HF';
        }
        if (empty($order_IDS)) {
            $query_args = array(
                'fields' => 'ids',
                'post_type' => ($subscription_plugin == 'WC') ? 'shop_subscription' : 'hf_shop_subscription',
                'posts_per_page' => $export_limit,
                'post_status' => 'any',
                'offset' => $export_offset,
                'date_query' => array(
                    array(
                        'before' => $end_date,
                        'after' => $start_date,
                        'inclusive' => true,
                    ),
                ),
            );
            if (!empty($_POST['order_status'])) {
                $statuses = wc_clean($_POST['order_status']);
                if (!empty($statuses) && is_array($statuses)) {
                    $query_args['post_status'] = implode(',', $statuses);
                    if (!in_array($query_args['post_status'], array('any', 'trash'))) {
                        $query_args['post_status'] = wf_subcription_orderImpExpCsv_Exporter::hf_sanitize_subscription_status_keys($query_args['post_status']);
                    }
                }
            }
                       
            if (!empty($_POST['payment_methods'])) {
                $payment_methods = wc_clean($_POST['payment_methods']);
                $meta_query = array('relation' => 'OR');
                foreach ($payment_methods as $key => $value) {
                    $value = strtolower($value);
                    $meta_query[] = array(
                        'key' => '_payment_method',
                        'value' => $value,
                    );
                }
                $query_args['meta_query'][] =$meta_query;
            }
            
            if (!empty($_POST['next_pay_date'])) {
                
               $query_args['meta_query'][]  = array(
                        'key' => '_schedule_next_payment',
                        'value' => sanitize_text_field($_POST['next_pay_date']),
                        'compare' => 'LIKE'
                    );
                  
            }
            
            if (class_exists('Polylang_Woocommerce')) {//OCSEIPFW-224
                $query_args = apply_filters('woocommerce_get_subscriptions_query_args', $query_args, array());
            } else {
                $query_args = apply_filters('woocommerce_get_subscriptions_query_args', $query_args);
            }

            $subscription_post_ids = get_posts($query_args);
            
            if (!empty($email)) {
                    global $wpdb;
                    $query = "SELECT wp_posts.ID FROM wp_posts INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id ) WHERE ( ( wp_postmeta.meta_key = '_customer_user' AND wp_postmeta.meta_value IN ('". implode("','", $email) ."') ) ) AND wp_posts.post_type = 'shop_subscription' " ;
                    $subscription_order_ids = $wpdb->get_col($query);
                    $subscription_post_ids = array_intersect($subscription_order_ids, $subscription_post_ids);                 
            }
            
            if (!empty($products) ) {
               $prod_subscription_ids = wcs_get_subscriptions_for_product($products);
               $subscription_post_ids = array_intersect($prod_subscription_ids, $subscription_post_ids);                
            }
            if (!empty($coupons)) {
                $coupon_subscription_ids = self::wt_get_subscription_of_coupons($coupons);
                $subscription_post_ids = array_intersect($coupon_subscription_ids, $subscription_post_ids);               
            } 
            
            $subscriptions = array();
            foreach ($subscription_post_ids as $post_id) {
                $subscriptions[$post_id] = wf_subcription_orderImpExpCsv_Exporter::hf_get_subscription($post_id);
            }
            $subscriptions = apply_filters('hf_retrieved_subscriptions', $subscriptions);
        } else {
            foreach ($order_IDS as $post_id) {
                $subscriptions[$post_id] = wf_subcription_orderImpExpCsv_Exporter::hf_get_subscription($post_id);
            }
            $subscriptions = apply_filters('hf_retrieved_subscriptions', $subscriptions);
        }
        
        // Variable to hold the CSV data we're exporting
        $row = array();
        // Export header rows
        foreach ($csv_columns as $column => $value) {
            if (!isset($user_columns_name[$column]))
                continue;
            $temp_head = esc_attr($user_columns_name[$column]);
            if (!$export_columns || in_array($column, $export_columns))
                $row[] = $temp_head;
        }

        //Alter CSV Header 
        $row = apply_filters('hf_alter_subscription_csv_header_columns', $row, $export_columns, $csv_columns, $subscriptions);
        $row = array_map('wf_subcription_orderImpExpCsv_Exporter::wrap_column', $row);
        fwrite($fp, implode($delimiter, $row) . "\n");
        unset($row);
        
        if($subscription_plugin == 'WC'){
            // Loop orders
            foreach ($subscriptions as $order_id) {
                $data = wf_subcription_orderImpExpCsv_Exporter::get_subscriptions_csv_row($order_id, $csv_columns, $export_columns);
                // Add to csv
                $row = array_map('wf_subcription_orderImpExpCsv_Exporter::wrap_column', $data);
                fwrite($fp, implode($delimiter, $row) . "\n");
                unset($row);
                unset($data);
            }
        }else{
            // Loop orders
            foreach ($subscriptions as $order_id) {
                $data = wf_subcription_orderImpExpCsv_Exporter::get_wt_subscriptions_csv_row($order_id, $csv_columns, $export_columns);
                // Add to csv
                $row = array_map('wf_subcription_orderImpExpCsv_Exporter::wrap_column', $data);
                fwrite($fp, implode($delimiter, $row) . "\n");
                unset($row);
                unset($data);
            }
        }
        if ($enable_ftp_ie) {
            // Upload ftp path with filename
            $remote_file = ( substr($remote_path, -1) != '/' ) ? ( $remote_path . "/" . basename($file) ) : ( $remote_path . basename($file) );
            
            // if have SFTP Add-on for Import Export for WooCommerce 
            if (class_exists('class_wf_sftp_import_export')) {
                $sftp_export = new class_wf_sftp_import_export();
                if (!$sftp_export->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                    $wf_order_ie_msg = 2;
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_subscription_order_im_ex&wf_subcription_order_ie_msg=' . $wf_order_ie_msg));
                    die;
                }
                if ($sftp_export->put_contents($remote_file, file_get_contents($file))) {
                    $wf_order_ie_msg = 1;
                } else {
                    $wf_order_ie_msg = 2;
                }
                wp_redirect(admin_url('/admin.php?page=wf_woocommerce_subscription_order_im_ex&wf_subcription_order_ie_msg=' . $wf_order_ie_msg));
                die;
            }
            
            
            if ($use_ftps) {
                $ftp_conn = @ftp_ssl_connect($ftp_server, $ftp_port) or die("Could not connect to $ftp_server");
            } else {
                $ftp_conn = @ftp_connect($ftp_server, $ftp_port) or die("Could not connect to $ftp_server");
            }
            $login = @ftp_login($ftp_conn, $ftp_user, $ftp_password);
            if ($use_pasv)
                ftp_pasv($ftp_conn, TRUE);
            // upload file
            if (@ftp_put($ftp_conn, $remote_file, $file, FTP_ASCII)) {
                $wf_subcription_order_ie_msg = 1;
                wp_redirect(admin_url('/admin.php?page=wf_woocommerce_subscription_order_im_ex&wf_subcription_order_ie_msg=' . $wf_subcription_order_ie_msg));
            } else {
                $wf_subcription_order_ie_msg = 2;
                wp_redirect(admin_url('/admin.php?page=wf_woocommerce_subscription_order_im_ex&wf_subcription_order_ie_msg=' . $wf_subcription_order_ie_msg));
            }
            // close connection
            @ftp_close($ftp_conn);
            unlink($file);
        }

        fclose($fp);
        exit;
    }

    public static function format_data($data) {
        //if (!is_array($data));
        //$data = (string) urldecode($data);
        $enc = mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true);
        $data = ( $enc == 'UTF-8' ) ? $data : utf8_encode($data);
        return $data;
    }

    /**
     * Wrap a column in quotes for the CSV
     * @param  string data to wrap
     * @return string wrapped data
     */
    public static function wrap_column($data) {
        return '"' . str_replace('"', '""', $data) . '"';
    }

    public static function hf_sanitize_subscription_status_keys($status_key) {
        if (!is_string($status_key) || empty($status_key)) {
            return '';
        }
        $status_key = ( 'wc-' === substr($status_key, 0, 3) ) ? $status_key : sprintf('wc-%s', $status_key);
        return $status_key;
    }

    public static function hf_get_subscription($subscription) {
        if (is_object($subscription) && self::hf_is_subscription($subscription)) {
            $subscription = $subscription->id;
        }
        $subscription_plugin = 'WC';
        if(class_exists('HF_Subscription')){
            $subscription_plugin = 'HF';
        }
        if($subscription_plugin == 'WC'){
        if (!class_exists('WC_Subscription')):
            require WP_PLUGIN_DIR.'/woocommerce-subscriptions/wcs-functions.php';
            require WP_PLUGIN_DIR.'/woocommerce-subscriptions/includes/class-wc-subscription.php';
        endif;
        $subscription = new WC_Subscription($subscription);
        }else{
        if (!class_exists('HF_Subscription')):
            require WP_PLUGIN_DIR.'/xa-woocommerce-subscriptions/includes/subscription-common-functions.php';
            require WP_PLUGIN_DIR.'/xa-woocommerce-subscriptions/includes/components/class-subscription.php';
        endif;
        $subscription = new HF_Subscription($subscription);
        }
        if (!self::hf_is_subscription($subscription)) {
            $subscription = false;
        }
        return apply_filters('hf_get_subscription', $subscription);
    }

    public static function hf_is_subscription($subscription) {
        if (is_object($subscription) && (is_a($subscription, 'WC_Subscription') || is_a($subscription, 'HF_Subscription'))) {
            $is_subscription = true;
        } elseif (is_numeric($subscription) && ('shop_subscription' == get_post_type($subscription) || 'hf_shop_subscription' == get_post_type($subscription))) {
            $is_subscription = true;
        } else {
            $is_subscription = false;
        }
        return apply_filters('hf_is_subscription', $is_subscription, $subscription);
    }

    /*
     * Takes the subscription and builds the CSV row based on the headers which have been set by user, 
     * return ready to write row.
     * @param WC_Subscription $subscription
     * @param $csv_columns array selected of columns to export
     */

    public static function get_subscriptions_csv_row($subscription, $csv_columns, $export_columns = array()) {
        if (empty($export_columns)) {
            $export_columns = $csv_columns;
        }
        $fee_total = $fee_tax_total = 0;
        $fee_items = $shipping_items = array();
        if (0 != sizeof(array_intersect(array_keys($csv_columns), array('fee_total', 'fee_tax_total', 'fee_items')))) {
            foreach ($subscription->get_fees() as $fee_id => $fee) {
                $fee_items[] = implode('|', array(
                    'name:' . html_entity_decode($fee['name'], ENT_NOQUOTES, 'UTF-8'),
                    'total:' . wc_format_decimal($fee['line_total'], 2),
                    'tax:' . wc_format_decimal($fee['line_tax'], 2),
                    'tax_class:' . $fee['tax_class'],
                ));
                $fee_total += $fee['line_total'];
                $fee_tax_total += $fee['line_tax'];
            }
        }
        
        $line_items_shipping = $subscription->get_items('shipping');
        foreach ($line_items_shipping as $item_id => $item) {
            if (is_object($item)) {
                if ($meta_data = $item->get_formatted_meta_data('')) :
                    foreach ($meta_data as $meta_id => $meta) :
                        if (in_array($meta->key, $line_items_shipping)) {
                            continue;
                        }
                        // html entity decode is not working preoperly
                        $shipping_items[] = implode('|', array('item:' . wp_kses_post($meta->display_key), 'value:' . str_replace('&times;', 'X', strip_tags($meta->display_value))));
                    endforeach;
                endif;
            }
        }

        if (!function_exists('get_user_by')) {
            require ABSPATH . 'wp-includes/pluggable.php';
        }

        $user_values = get_user_by('ID',(WC()->version < '2.7') ? $subscription->customer_user : $subscription->get_customer_id());
        
        // Preparing data for export
        foreach ($export_columns as $header_key => $_) {
            switch ($header_key) {
                case 'subscription_id':
                    $value = (WC()->version < '2.7') ? $subscription->id : $subscription->get_id();
                    break;
                case 'subscription_status':
                    $value = (WC()->version < '2.7') ? $subscription->post_status : $subscription->get_status();
                    break;
                case 'customer_id':
                    $value = (WC()->version < '2.7') ? $subscription->customer_user : $subscription->get_customer_id();
                    break;
                case 'customer_username':
                    $value = is_object($user_values) ? $user_values->user_login : '';
                    break;
                case 'customer_email':
                    $value = is_object($user_values) ? $user_values->user_email : '';
                    break;
                case 'fee_total':
                case 'fee_tax_total':
                    $value = ${$header_key};
                    break;
                case 'order_shipping_tax':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_shipping_tax();
                    break;
                case 'order_total':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total();
                    break;
                case 'order_tax':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total_tax();
                    break;
                case 'order_shipping':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total_shipping();
                    break;
                case 'cart_discount_tax':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_discount_tax();
                    break;
                case 'cart_discount':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total_discount();
                    break;
                case 'date_created':
                case 'trial_end_date':
                case 'next_payment_date':
                case 'last_order_date_created':
                case 'end_date':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_date($header_key);
                    break;
                case 'billing_period':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_period();
                    break;
                case 'billing_interval':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_interval();
                    break;
                case 'payment_method':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_payment_method();
                    break;
                case 'payment_method_title':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_payment_method_title();
                    break;
                case 'billing_first_name':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_first_name();
                    break;
                case 'billing_last_name':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_last_name();
                    break;
                case 'billing_email':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_email();
                    break;
                case 'billing_phone':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_phone();
                    break;
                case 'billing_address_1':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_address_1();
                    break;
                case 'billing_address_2':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_address_2();
                    break;
                case 'billing_postcode':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_postcode();
                    break;
                case 'billing_city':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_city();
                    break;
                case 'billing_state':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_state();
                    break;
                case 'billing_country':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_country();
                    break;
                case 'billing_company':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_billing_company();
                    break;
                case 'shipping_first_name':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_first_name();
                    break;
                case 'shipping_last_name':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_last_name();
                    break;
                case 'shipping_address_1':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_address_1();
                    break;
                case 'shipping_address_2':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_address_2();
                    break;
                case 'shipping_postcode':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_postcode();
                    break;
                case 'shipping_city':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_city();
                    break;
                case 'shipping_state':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_state();
                    break;
                case 'shipping_country':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_country();
                    break;
                case 'shipping_company':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_shipping_company();
                    break;
                case 'customer_note':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_customer_note();
                    break;
                case 'order_currency':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_currency();
                    break;
                case 'post_parent':
                    if (!empty($subscription->get_parent() ))
                        $value = $subscription->get_parent_id();
                    else
                        $value = 0;
                    break;
                case 'order_notes':
                    remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'));
                    $notes = get_comments(array('post_id' => (WC()->version < '2.7') ? $subscription->id : $subscription->get_id(), 'approve' => 'approve', 'type' => 'order_note'));
                    add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'));
                    $order_notes = array();
                    foreach ($notes as $note) {
                        $order_notes[] = str_replace(array("\r", "\n"), ' ', $note->comment_content);
                    }
                    if (!empty($order_notes)) {
                        $value = implode(';', $order_notes);
                    } else {
                        $value = '';
                    }
                    break;
                case 'renewal_orders':
                    $renewal_orders = $subscription->get_related_orders('ids', 'renewal');
                    if (!empty($renewal_orders)) {
                        $value = implode('|', $renewal_orders);
                    } else {
                        $value = '';
                    }
                    break;
                case 'order_items':
                    $value = '';
                    $line_items = array();
                    foreach ($subscription->get_items() as $item_id => $item) {
                        $product = $subscription->get_product_from_item($item);
                        if (!is_object($product)) {
                            $product = new WC_Product(0);
                        }
                        
//                        $product_id = self::hf_get_canonical_product_id($item);
                        $item_meta = self::get_order_line_item_meta($item_id);
                        $prod_type = (WC()->version < '3.0.0') ? $product->product_type : $product->get_type();
                        $line_item = array(
                            'product_id' => (WC()->version < '2.7.0') ? $product->id : (($prod_type == 'variable' || $prod_type == 'variation' || $prod_type == 'subscription_variation') ? $product->get_parent_id() : $product->get_id()),
                            'name' => html_entity_decode($item['name'], ENT_NOQUOTES, 'UTF-8'),
                            'sku' => $product->get_sku(),
                            'quantity' => $item['qty'],
                            'total' => wc_format_decimal($subscription->get_line_total($item), 2),
                            'sub_total' => wc_format_decimal($subscription->get_line_subtotal($item), 2),
                        );
                        
                        // add line item tax
                        $line_tax_data = isset($item['line_tax_data']) ? $item['line_tax_data'] : array();
                        $tax_data = maybe_unserialize($line_tax_data);
                        $tax_detail = isset($tax_data['total']) ? wc_format_decimal(wc_round_tax_total(array_sum((array) $tax_data['total'])), 2) : '';
                        if ($tax_detail != '0.00' && !empty($tax_detail)) {
                            $line_item['tax'] = $tax_detail;
                        }
                        $line_tax_ser = maybe_serialize($line_tax_data);
                        $line_item['tax_data'] = $line_tax_ser;
                        
                        foreach ($item_meta as $key => $value) {
                            switch ($key) {
                                case '_qty':
                                case '_variation_id':
                                case '_product_id':
                                case '_line_total':
                                case '_line_subtotal':
                                case '_tax_class':
                                case '_line_tax':
                                case '_line_tax_data':
                                case '_line_subtotal_tax':
                                    break;

                                default:
                                    if(is_object($value))
                                    $value = $value->meta_value;
                                    if (is_array($value))
                                        $value = implode(',', $value);
                                    $line_item[$key] = $value;
                                    break;
                            }
                        }

                        if ($prod_type === 'variable' || $prod_type === 'variation' || $prod_type === 'subscription_variation') {
                            $line_item['_variation_id'] = (WC()->version > '2.7') ? $product->get_id() : $product->variation_id;
                        }
                        foreach ($line_item as $name => $value) {
                            $line_item[$name] = $name . ':' . $value;
                        }
                        $line_item = implode('|', $line_item);

                        if ($line_item) {
                            $line_items[] = $line_item;
                        }
                    }
                    if (!empty($line_items)) {
                        $value = implode('||', $line_items);
                    }
                    break;
                case 'coupon_items':
                    $coupon_items = array();
                    foreach ($subscription->get_items('coupon') as $_ => $coupon_item) {
                        $coupon = new WC_Coupon($coupon_item['name']);
                        $coupon_post = get_post($coupon->id);
                        $coupon_items[] = implode('|', array(
                            'code:' . $coupon_item['name'],
                            'description:' . ( is_object($coupon_post) ? $coupon_post->post_excerpt : '' ),
                            'amount:' . wc_format_decimal($coupon_item['discount_amount'], 2),
                        ));
                    }
                    if (!empty($coupon_items)) {
                        $value = implode(';', $coupon_items);
                    } else {
                        $value = '';
                    }
                    break;
                case 'download_permissions':
                    $value = (WC()->version < '2.7') ? ($subscription->download_permissions_granted ? $subscription->download_permissions_granted : 0) :($subscription->is_download_permitted());
                    break;
                case 'shipping_method':
                    $shipping_lines = array();
                    foreach ($subscription->get_shipping_methods() as $shipping_item_id => $shipping_item) {
                        $shipping_lines[] = implode('|', array(
                            'method_id:' . $shipping_item['method_id'],
                            'method_title:' . $shipping_item['name'],
                            'total:' . wc_format_decimal($shipping_item['cost'], 2),
                            )
                        );
                    }
                    if (!empty($shipping_lines)) {
                        $value = implode(';', $shipping_lines);
                    } else {
                        $value = '';
                    }
                    break;
                case 'fee_items':
                    $value = implode(';', $fee_items);
                    break;
                case 'shipping_items':
                    $value = implode(';', $shipping_items);
                    break;
                case 'tax_items':
                    $tax_items = array();
                    foreach ($subscription->get_tax_totals() as $tax_code => $tax) {
                        $tax_items[] = implode('|', array(
                            'rate_id:' . $tax->rate_id,
                            'code:' . $tax_code,
                            'total:' . wc_format_decimal($tax->amount, 2),
                            'label:'.$tax->label,                
                            'tax_rate_compound:'.$tax->is_compound,
                        ));
                    }
                    if (!empty($tax_items)) {
                        $value = implode(';', $tax_items);
                    } else {
                        $value = '';
                    }
                    break;
                default :
                    if(strstr($header_key, 'meta:')){
                        $value = maybe_serialize(get_post_meta((WC()->version < '2.7') ? $subscription->id : $subscription->get_id(), str_replace('meta:', '', $header_key),TRUE));
                    } else {
                        $value = '';
                    }
            }
            $csv_row[$header_key] = $value;
        }

        $data = array();
        foreach ($export_columns as $header_key => $_) {
            if (!isset($csv_row[$header_key])) {
                $csv_row[$header_key] = '';
            }

            // Strict string comparison, as values like '0' are valid
            $value = ( '' !== $csv_row[$header_key] ) ? $csv_row[$header_key] : '';
            $data[] = $value;
        }
        return apply_filters('hf_alter_subscription_data', $data, $export_columns, $csv_columns);
    }

    public static function hf_get_canonical_product_id($item) {
        return (!empty($item['variation_id']) ) ? $item['variation_id'] : $item['product_id'];
    }
    
    public static function get_order_line_item_meta($item_id){
        global $wpdb;
        $filtered_meta = apply_filters('wt_subscription_export_select_line_item_meta',array());
        $filtered_meta = !empty($filtered_meta) ? implode("','",$filtered_meta) : '';
        $query = "SELECT meta_key,meta_value
            FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = '$item_id'";
        if(!empty($filtered_meta)){
            $query .= " AND meta_key IN ('".$filtered_meta."')";
        }
        $meta_keys = $wpdb->get_results($query , OBJECT_K );
        return $meta_keys;
    }
    
    
    
    /*
     * Takes the subscription and builds the CSV row based on the headers which have been set by user, 
     * return ready to write row.
     * @param WC_Subscription $subscription
     * @param $csv_columns array selected of columns to export
     */

    public static function get_wt_subscriptions_csv_row($subscription, $csv_columns, $export_columns = array()) {
        if (empty($export_columns)) {
            $export_columns = $csv_columns;
        }
        $fee_total = $fee_tax_total = 0;
        $fee_items = $shipping_items = array();

        if (0 != sizeof(array_intersect(array_keys($csv_columns), array('fee_total', 'fee_tax_total', 'fee_items')))) {
            foreach ($subscription->get_fees() as $fee_id => $fee) {
                $fee_items[] = implode('|', array(
                    'name:' . html_entity_decode($fee['name'], ENT_NOQUOTES, 'UTF-8'),
                    'total:' . wc_format_decimal($fee['line_total'], 2),
                    'tax:' . wc_format_decimal($fee['line_tax'], 2),
                    'tax_class:' . $fee['tax_class'],
                ));
                $fee_total += $fee['line_total'];
                $fee_tax_total += $fee['line_tax'];
            }
        }
        
        $line_items_shipping = $subscription->get_items('shipping');
        foreach ($line_items_shipping as $item_id => $item) {
            if (is_object($item)) {
                if ($meta_data = $item->get_formatted_meta_data('')) :
                    foreach ($meta_data as $meta_id => $meta) :
                        if (in_array($meta->key, $line_items_shipping)) {
                            continue;
                        }
                        // html entity decode is not working preoperly
                        $shipping_items[] = implode('|', array('item:' . wp_kses_post($meta->display_key), 'value:' . str_replace('&times;', 'X', strip_tags($meta->display_value))));
                    endforeach;
                endif;
            }
        }
        if (!function_exists('get_user_by')) {
            require ABSPATH . 'wp-includes/pluggable.php';
        }
        $user_values = get_user_by('ID', (WC()->version < '2.7') ? $subscription->customer_user : $subscription->get_customer_id());
        
        // Preparing data for export
        foreach ($export_columns as $header_key => $_) {
            switch ($header_key) {
                case 'subscription_id':
                    $value = $subscription->get_id();
                    break;
                case 'subscription_status':
                    $value = $subscription->get_status();
                    break;
                case 'customer_id':
                    $value = is_object($user_values) ? $user_values->ID : '';
                    break;
                case 'customer_username':
                    $value = is_object($user_values) ? $user_values->user_login : '';
                    break;
                case 'customer_email':
                    $value = is_object($user_values) ? $user_values->user_email : '';
                    break;
                case 'fee_total':
                case 'fee_tax_total':
                    $value = ${$header_key};
                    break;
                case 'order_shipping':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total_shipping();
                    break;
                case 'order_shipping_tax':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_shipping_tax();
                    break;
                case 'order_tax':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total_tax();
                    break;
                case 'cart_discount':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_total_discount();
                    break;
                case 'cart_discount_tax':
                    $value = (WC()->version < '2.7') ? (empty($subscription->{$header_key}) ? 0 : $subscription->{$header_key}) : $subscription->get_discount_tax();
                    break;
                case 'order_total':
                    $value = empty($subscription->get_total()) ? 0 : $subscription->get_total();
                    break;
                case 'date_created':
                    $value = $subscription->get_date('date_created');
                    break;
                case 'trial_end_date':
                    $value = $subscription->get_date('trial_end_date');
                    break;
                case 'next_payment_date':
                    $value = $subscription->get_date('next_payment_date');
                    break;
                case 'last_order_date_created':
                    $value = $subscription->get_date('last_order_date_created');
                    break;
                case 'end_date':
                    $value = $subscription->get_date('end_date');
                    break;
                case 'order_currency':
                    $value = (WC()->version < '2.7') ? $subscription->{$header_key} :$subscription->get_currency();
                    break;
                case 'billing_period':
                case 'billing_interval':
                case 'payment_method':
                case 'payment_method_title':
                case 'billing_first_name':
                case 'billing_last_name':
                case 'billing_email':
                case 'billing_phone':
                case 'billing_address_1':
                case 'billing_address_2':
                case 'billing_postcode':
                case 'billing_city':
                case 'billing_state':
                case 'billing_country':
                case 'billing_company':
                case 'shipping_first_name':
                case 'shipping_last_name':
                case 'shipping_address_1':
                case 'shipping_address_2':
                case 'shipping_postcode':
                case 'shipping_city':
                case 'shipping_state':
                case 'shipping_country':
                case 'shipping_company':
                case 'customer_note':
                    
                    $m_key = "get_$header_key";
                    
                    if(method_exists($subscription, $m_key)){
                        $value = $subscription->{$m_key}();
                    }else{
                        $value = $subscription->{$header_key};
                    }
                    break;
                case 'post_parent':
                        $post = get_post( $subscription->get_id() );
                        $value = $post->post_parent;
                    break;
                case 'order_notes':
                    remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'));
                    $notes = get_comments(array('post_id' => $subscription->get_id(), 'approve' => 'approve', 'type' => 'order_note'));
                    add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'));
                    $order_notes = array();
                    foreach ($notes as $note) {
                        $order_notes[] = str_replace(array("\r", "\n"), ' ', $note->comment_content);
                    }
                    if (!empty($order_notes)) {
                        $value = implode(';', $order_notes);
                    } else {
                        $value = '';
                    }
                    break;
                case 'renewal_orders':
                    $renewal_orders = $subscription->get_related_orders('ids', 'renewal');
                    if (!empty($renewal_orders)) {
                        $value = implode('|', $renewal_orders);
                    } else {
                        $value = '';
                    }
                    break;
                case 'order_items':
                    $value = '';
                    $line_items = array();
                    foreach ($subscription->get_items() as $item_id => $item) {
                        $product = $subscription->get_product_from_item($item);
                        if (!is_object($product)) {
                            $product = new WC_Product(0);
                        }
                        
//                        $product_id = self::hf_get_canonical_product_id($item);
                        $item_meta = self::get_order_line_item_meta($item_id);
                        $prod_type = (WC()->version < '3.0.0') ? $product->product_type : $product->get_type();
                        $line_item = array(
                            'product_id' => (WC()->version < '2.7.0') ? $product->id : (($prod_type == 'variable' || $prod_type == 'variation' || $prod_type == 'subscription_variation') ? $product->get_parent_id() : $product->get_id()),
                            'name' => html_entity_decode($item['name'], ENT_NOQUOTES, 'UTF-8'),
                            'sku' => $product->get_sku(),
                            'quantity' => $item['qty'],
                            'total' => wc_format_decimal($subscription->get_line_total($item), 2),
                            'sub_total' => wc_format_decimal($subscription->get_line_subtotal($item), 2),
                        );
                        
                        // add line item tax
                        $line_tax_data = isset($item['line_tax_data']) ? $item['line_tax_data'] : array();
                        $tax_data = maybe_unserialize($line_tax_data);
                        $tax_detail = isset($tax_data['total']) ? wc_format_decimal(wc_round_tax_total(array_sum((array) $tax_data['total'])), 2) : '';
                        if ($tax_detail != '0.00' && !empty($tax_detail)) {
                            $line_item['tax'] = $tax_detail;
                        }
                        $line_tax_ser = maybe_serialize($line_tax_data);
                        $line_item['tax_data'] = $line_tax_ser;
                        
                        foreach ($item_meta as $key => $value) {
                            switch ($key) {
                                case '_qty':
                                case '_variation_id':
                                case '_product_id':
                                case '_line_total':
                                case '_line_subtotal':
                                case '_tax_class':
                                case '_line_tax':
                                case '_line_tax_data':
                                case '_line_subtotal_tax':
                                    break;

                                default:
                                    if(is_object($value))
                                    $value = $value->meta_value;
                                    if (is_array($value))
                                        $value = implode(',', $value);
                                    $line_item[$key] = $value;
                                    break;
                            }
                        }

                        if ($prod_type === 'variable' || $prod_type === 'variation' || $prod_type === 'subscription_variation') {
                            $line_item['_variation_id'] = (WC()->version > '2.7') ? $product->get_id() : $product->variation_id;
                        }
                        foreach ($line_item as $name => $value) {
                            $line_item[$name] = $name . ':' . $value;
                        }
                        $line_item = implode('|', $line_item);

                        if ($line_item) {
                            $line_items[] = $line_item;
                        }
                    }
                    if (!empty($line_items)) {
                        $value = implode('||', $line_items);
                    }
                    break;
                case 'coupon_items':
                    $coupon_items = array();
                    foreach ($subscription->get_items('coupon') as $_ => $coupon_item) {
                        $coupon = new WC_Coupon($coupon_item['name']);
                        $coupon_post = get_post($coupon->id);
                        $coupon_items[] = implode('|', array(
                            'code:' . $coupon_item['name'],
                            'description:' . ( is_object($coupon_post) ? $coupon_post->post_excerpt : '' ),
                            'amount:' . wc_format_decimal($coupon_item['discount_amount'], 2),
                                )
                        );
                    }
                    if (!empty($coupon_items)) {
                        $value = implode(';', $coupon_items);
                    } else {
                        $value = '';
                    }
                    break;
                case 'download_permissions':
                    $value = (WC()->version < '2.7') ? ($subscription->download_permissions_granted ? $subscription->download_permissions_granted : 0) :($subscription->is_download_permitted());
                    break;
                case 'shipping_method':
                    $shipping_lines = array();
                    foreach ($subscription->get_shipping_methods() as $shipping_item_id => $shipping_item) {
                        $shipping_lines[] = implode('|', array(
                            'method_id:' . $shipping_item['method_id'],
                            'method_title:' . $shipping_item['name'],
                            'total:' . wc_format_decimal($shipping_item['cost'], 2),
                        ));
                    }
                    if (!empty($shipping_lines)) {
                        $value = implode(';', $shipping_lines);
                    } else {
                        $value = '';
                    }
                    break;
                case 'fee_items':
                    $value = implode(';', $fee_items);
                    break;
                 case 'shipping_items':
                    $value = implode(';', $shipping_items);
                    break;
                case 'tax_items':
                    $tax_items = array();
                    foreach ($subscription->get_tax_totals() as $tax_code => $tax) {
                        $tax_items[] = implode('|', array(
                            'rate_id:' . $tax->rate_id,
                            'code:' . $tax->label,
                            'total:' . wc_format_decimal($tax->amount, 2),
                            'label:'.$tax->label,
                            'tax_rate_compound:'.$tax->is_compound,
                        ));
                    }
                    if (!empty($tax_items)) {
                        $value = implode(';', $tax_items);
                    } else {
                        $value = '';
                    }
                    break;
                default :
                    if(strstr($header_key, 'meta:')){
                        $value = maybe_serialize(get_post_meta((WC()->version < '2.7') ? $subscription->id : $subscription->get_id(), str_replace('meta:', '', $header_key),TRUE));
                    } else {
                        $value = '';
                    }
            }
            $csv_row[$header_key] = $value;
        }
        $data = array();
        foreach ($export_columns as $header_key => $_) {
            if (!isset($csv_row[$header_key])) {
                $csv_row[$header_key] = '';
            }
            // Strict string comparison, as values like '0' are valid
            $value = ( '' !== $csv_row[$header_key] ) ? $csv_row[$header_key] : '';
            $data[] = $value;
        }
        return apply_filters('hf_alter_subscription_data', $data, $export_columns, $csv_columns);
    }
    public static function wt_set_csv_delimiter($delemiter=','){
        $delemiter = strtolower($delemiter);
        switch ($delemiter) {
            case 'tab':
                $delemiter =   "\t";
                break;
            
            case 'space':
                $delemiter =   " ";
                break;
        }
        return $delemiter;
    }
    public static function wt_get_subscription_of_coupons($coupons){
         global $wpdb;
         $query = "SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
            LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
            WHERE po.post_type = 'shop_subscription'
            AND oi.order_item_type = 'coupon'
            AND oi.order_item_name IN ('". implode("','", $coupons) ."')";
            $subscription_ids = $wpdb->get_col($query);
            return $subscription_ids;
        
    }
}
