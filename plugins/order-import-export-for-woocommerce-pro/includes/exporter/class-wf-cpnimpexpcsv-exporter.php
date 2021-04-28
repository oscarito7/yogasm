<?php

if (!defined('ABSPATH')) {
    exit;
}

class WF_CpnImpExpCsv_Exporter {

    public static function do_export($post_type = 'shop_coupon', $coupon_ids = array()) {
        global $wpdb;

        if (!empty($coupon_ids)) {
            $selected_coupon_ids = $coupon_ids;
        } else {
            $selected_coupon_ids = array();
        }
        $cpn_categories = !empty($_POST['cpn_categories']) ? wc_clean($_POST['cpn_categories']) : array('fixed_cart', 'percent', 'fixed_product', 'percent_product');
        $export_limit = !empty($_POST['limit']) ? absint($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $current_offset = !empty($_POST['offset']) ? absint($_POST['offset']) : 0;
        $sortcolumn = !empty($_POST['sortcolumn']) ? wc_clean($_POST['sortcolumn']) : 'ID';
        $delimiter = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ','; // WPCS: CSRF ok, input var ok.
        $delimiter = self::wt_set_csv_delimiter($delimiter);
        $coupon_amount_from = !empty($_POST['coupon_amount_from']) ? floatval($_POST['coupon_amount_from']) : 0;
        $coupon_amount_to = !empty($_POST['coupon_amount_to']) ? floatval($_POST['coupon_amount_to']) : 0;
        $coupon_exp_date_from = !empty($_POST['coupon_exp_date_from']) ? sanitize_text_field($_POST['coupon_exp_date_from']) : '0000-00-00';
        $coupon_exp_date_to = !empty($_POST['coupon_exp_date_to']) ? sanitize_text_field($_POST['coupon_exp_date_to']) : '0000-00-00';
        $csv_columns = include( 'data/data-wf-post-columns-coupon.php' );
        $user_columns_name = !empty($_POST['columns_name']) ? wc_clean($_POST['columns_name']) : $csv_columns;
        $export_columns = !empty($_POST['columns']) ? wc_clean($_POST['columns']) : '';
//        $products = !empty($_POST['c_products']) ? $_POST['c_products'] : array();
//        self::$coupon_code = !empty($_POST['coupon_code']) ? $_POST['coupon_code'] : '';
        if ($limit > $export_limit)
            $limit = $export_limit;
        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $enable_ftp_ie = isset($settings['cpn_enable_ftp_ie']) ? $settings['cpn_enable_ftp_ie'] : '';
        if($enable_ftp_ie){
            $ftp_server = isset($settings['cpn_ftp_server']) ? $settings['cpn_ftp_server'] : '';
            $ftp_user = isset($settings['cpn_ftp_user']) ? $settings['cpn_ftp_user'] : '';
            $ftp_password = isset($settings['cpn_ftp_password']) ? $settings['cpn_ftp_password'] : '';
            $ftp_port = isset($settings['cpn_ftp_port']) ? $settings['cpn_ftp_port'] : 21;
            $use_ftps = isset($settings['cpn_use_ftps']) ? $settings['cpn_use_ftps'] : '';
            $use_pasv = isset($settings['cpn_use_pasv']) ? $settings['cpn_use_pasv'] : '';
            $remote_path = isset($settings['cpn_ftp_path']) ? $settings['cpn_ftp_path'] : null;
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
            $file = (!empty($settings['cpn_auto_export_ftp_file_name'])) ? $file_path . $settings['cpn_auto_export_ftp_file_name'] : $file_path . "coupon-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv";
            $fp = fopen($file, 'w');
        } else {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename=woocommerce-coupon-export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');

            $fp = fopen('php://output', 'w');
        }

        $all_meta_pkeys = self::get_all_metakeys('shop_coupon');
        $all_meta_keys = $all_meta_pkeys;
        $found_coupon_meta = array();
        foreach ($all_meta_keys as $meta) {
            if (!$meta)
                continue;
            if (!in_array($meta, array_keys($csv_columns)) && substr((string) $meta, 0, 1) == '_')
                continue;

            if (in_array($meta, array_keys($csv_columns)))
                continue;
            $found_coupon_meta[] = $meta;
        }
        
        $found_coupon_meta = array_diff($found_coupon_meta, array_keys($csv_columns));
        
        $row = array();
        foreach ($csv_columns as $column => $value) {
            $temp_head = esc_attr($user_columns_name[$column]);
            if (!$export_columns || in_array($column, $export_columns))
                $row[] = $temp_head;
        }

        if (!$export_columns || in_array('meta', $export_columns)) {
            foreach ($found_coupon_meta as $coupon_meta) {
                $row[] = 'meta:' . self::format_data($coupon_meta);
            }
        }

        $row = apply_filters('hf_alter_coupon_csv_header', $row); //Alter Coupon CSV Header

        $row = array_map('WF_CpnImpExpCsv_Exporter::wrap_column', $row);
        fwrite($fp, implode($delimiter, $row) . "\n");
        unset($row);

        while ($export_count < $export_limit) {
            $coupon_args = apply_filters('coupon_csv_product_export_args', array(
                'numberposts' => $limit,
                'post_status' => array('publish', 'pending', 'private', 'draft'),
                'post_type' => 'shop_coupon',
                'orderby' => $sortcolumn,
                'suppress_filters' => false,
                'order' => 'ASC',
                'offset' => $current_offset
                    ));

            if (!empty($cpn_categories)) {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'discount_type',
                        'value' => $cpn_categories,
                        'compare' => 'IN',
                ));
            }
            if ($coupon_amount_from != 0 && $coupon_amount_to == 0) {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'coupon_amount',
                        'value' => $coupon_amount_from,
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                ));
            }
            if ($coupon_amount_to != 0 && $coupon_amount_from == 0) {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'coupon_amount',
                        'value' => $coupon_amount_to,
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                ));
            }
            if ($coupon_amount_to != 0 && $coupon_amount_from != 0) {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'coupon_amount',
                        'value' => array($coupon_amount_from, $coupon_amount_to),
                        'compare' => 'BETWEEN',
                        'type' => 'NUMERIC'
                ));
            }
            if ($coupon_exp_date_from != '0000-00-00' && $coupon_exp_date_to == '0000-00-00') {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'date_expires',
                        'value' => strtotime($coupon_exp_date_from),
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                ));
            }
            if ($coupon_exp_date_to != '0000-00-00' && $coupon_exp_date_from == '0000-00-00') {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'date_expires',
                        'value' => strtotime($coupon_exp_date_to),
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                ));
            }
            if ($coupon_exp_date_to != '0000-00-00' && $coupon_exp_date_from != '0000-00-00') {
                $coupon_args['meta_query'] = array(
                    array(
                        'key' => 'date_expires',
                        'value' => array(strtotime($coupon_exp_date_from), strtotime($coupon_exp_date_to)),
                        'compare' => 'BETWEEN',
                        'type' => 'NUMERIC'
                ));
            }
            
            if (!empty($selected_coupon_ids)) {
                $coupon_args['meta_query'] = array();
                $coupon_args['post__in'] = $selected_coupon_ids;
            }
            $coupons = get_posts($coupon_args);
            if (!$coupons || is_wp_error($coupons))
                break;
            foreach ($coupons as $coupon) {
                foreach ($csv_columns as $column => $value) {
                    if (!$export_columns || in_array($column, $export_columns)) {
                        if (isset($coupon->$column)) {
                            if (is_array($coupon->$column)) {
                                $coupon->$column = implode(",", $coupon->$column);
                            }
                            if($column == 'product_ids'){
                                $hf_val = self::format_data($coupon->$column);
                                $sku = self::get_sku_from_id($hf_val);
                                $row[] = str_replace(',', '|', $hf_val);
                                continue;
                            }
                            if($column == 'exclude_product_ids'){
                                $ex_val = self::format_data($coupon->$column);
                                $exsku = self::get_sku_from_id($ex_val);
                                $row[] = str_replace(',', '|', $ex_val);
                                continue;
                            }
                            $row[] = self::format_data($coupon->$column);
                        } elseif (isset($coupon->$column) && !is_array($coupon->$column)) {
                            if ($column === 'post_title') {
                                $row[] = sanitize_text_field($coupon->$column);
                            } else {
                                $row[] = self::format_data($coupon->$column);
                            }
                        }
                        elseif ($column === 'product_SKUs') {
                            $row[] = !empty($sku) ? $sku : '';
                            unset($sku);
                        }
                        elseif ($column === 'exclude_product_SKUs') {
                            $row[] = !empty($exsku) ? $exsku : '';
                            unset($exsku);
                        }
                        else {
                            $row[] = '';
                        }
                    }
                }
                if (!$export_columns || in_array('meta', $export_columns)) {
                    foreach ($found_coupon_meta as $product_meta) {
                        if (isset($coupon->meta->$product_meta)) {
                            $row[] = self::format_data($coupon->meta->$product_meta);
                        } else {
                            $row[] = '';
                        }
                    }
                }
                $row = apply_filters('hf_alter_coupon_csv_data', $row); // Alter Coupon CSV data if needed
                $row = array_map('WF_CpnImpExpCsv_Exporter::wrap_column', $row);
                fwrite($fp, implode($delimiter, $row) . "\n");
                unset($row);
            }
            
            $current_offset += $limit;
            $export_count += $limit;
            unset($coupons);
        }

        if ($enable_ftp_ie) {
            
            $remote_file = ( substr($remote_path, -1) != '/' ) ? ( $remote_path . "/" . basename($file) ) : ( $remote_path . basename($file) );

            // if have SFTP Add-on for Import Export for WooCommerce 
            if (class_exists('class_wf_sftp_import_export')) {
                $sftp_export = new class_wf_sftp_import_export();
                if (!$sftp_export->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                    $wf_order_ie_msg = 2;
                    wp_redirect(admin_url('/admin.php?page=wf_coupon_csv_im_ex&wf_coupon_ie_msg=' . $wf_order_ie_msg));
                    die;
                }
                if ($sftp_export->put_contents($remote_file, file_get_contents($file))) {
                    $wf_order_ie_msg = 1;
                } else {
                    $wf_order_ie_msg = 2;
                }
                wp_redirect(admin_url('/admin.php?page=wf_coupon_csv_im_ex&wf_coupon_ie_msg=' . $wf_order_ie_msg));
                die;
            }
            
            // Upload ftp path with filename
            if ($use_ftps) {
                $ftp_conn = @ftp_ssl_connect($ftp_server, $ftp_port) or die(__("Could not connect to $ftp_server",'wf_order_import_export'));
            } else {
                $ftp_conn = @ftp_connect($ftp_server, $ftp_port) or die(__("Could not connect to $ftp_server",'wf_order_import_export'));
            }
            $login = @ftp_login($ftp_conn, $ftp_user, $ftp_password);

            if ($use_pasv)
                @ftp_pasv($ftp_conn, TRUE);

            //upload file
            if (@ftp_put($ftp_conn, $remote_file, $file, FTP_ASCII)) {
                $wf_coupon_ie_msg = 1;
                wp_redirect(admin_url('/admin.php?page=wf_coupon_csv_im_ex&wf_coupon_ie_msg=' . $wf_coupon_ie_msg));
            } else {
                $wf_coupon_ie_msg = 2;
                wp_redirect(admin_url('/admin.php?page=wf_coupon_csv_im_ex&wf_coupon_ie_msg=' . $wf_coupon_ie_msg));
            }
            @ftp_close($ftp_conn);
            unlink($file);
        }
        fclose($fp);
        exit;
    }

    public static function format_data($data) {
        if (!is_array($data))
            ;
        $data = (string) urldecode($data);
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

    /**
     * Get a list of all the meta keys for a post type. This includes all public, private,
     * used, no-longer used etc. They will be sorted once fetched.
     */
    public static function get_all_metakeys($post_type = 'shop_coupon') {
        global $wpdb;

        $meta = $wpdb->get_col($wpdb->prepare(
                        "SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ( 'publish', 'pending', 'private', 'draft' )", $post_type
                ));

        sort($meta);

        return $meta;
    }
    
    public static function get_sku_from_id($val){
        $pro_id = explode(",", $val);
        $sku_arr = array();
        if($pro_id){
            foreach ($pro_id as $value){
                $product_exist = get_post_type($value);
                if ($product_exist == 'product' || $product_exist == 'product_variation'){
                    $psku = get_post_meta($value,'_sku',TRUE);
                    if(!empty($psku)){
                        $sku_arr[] = $psku;
                    }
                }
            }
        }
        $new_sku = implode("|", $sku_arr);
        return $new_sku;
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
}
