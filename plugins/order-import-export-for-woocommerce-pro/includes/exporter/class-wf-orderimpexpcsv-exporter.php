<?php

if (!defined('ABSPATH')) {
    exit;
}

class WF_OrderImpExpCsv_Exporter {

    public static $temp_order_metadata;
    public static $line_item_meta;
    public static $include_hidden_meta;

    /**
     * Order Exporter Tool
     */
    public static function wf_order_xml_general_case_export_format($formated_orders, $raw_orders) {
        $order_details = array();
        foreach ($raw_orders as $order) {
            foreach ($order as $key => $value) {
                $order_data[$key] = trim($value, '"');
            }
            $order_details[] = $order_data;
        }
        $formated_orders = array('Orders' => array('Order' => $order_details));
        return apply_filters('hf_general_order_export', $formated_orders);
    }

    public static function do_export($post_type = 'shop_order', $order_IDS = array(), $xmldata = '0') {
        global $wpdb;
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        $export_limit = !empty($_POST['limit']) ? absint($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $export_offset = !empty($_POST['offset']) ? absint($_POST['offset']) : 0;
        $csv_columns = include( 'data/data-wf-post-columns.php' );
        $exclude_hidden_meta_columns = include( 'data/data-wf-exclude-hidden-meta-columns.php' );
        $user_columns_name = !empty($_POST['columns_name']) ? wc_clean($_POST['columns_name']) : $csv_columns;
        $export_columns = !empty($_POST['columns']) ? wc_clean($_POST['columns']) : array();
        $new_profile = !empty($_POST['new_profile']) ? sanitize_text_field($_POST['new_profile']) : '';
        if ($new_profile !== '') {
            $mapped = array();
            if (!empty($export_columns)) {
                foreach ($export_columns as $key => $value) {
                    $mapped[$key] = $user_columns_name[$key];
                }
            }
            $export_profile_array = get_option('xa_ordr_csv_export_mapping');
            $export_profile_array[$new_profile] = $mapped;
            update_option('xa_ordr_csv_export_mapping', $export_profile_array);
        }

        if (!empty($_POST['auto_export_profile'])) {
            $export_profile_array = get_option('xa_ordr_csv_export_mapping');
            $user_columns_name = array();
            $user_columns_name = $export_profile_array[sanitize_text_field($_POST['auto_export_profile'])];
            foreach ($user_columns_name as $column => $value) {
                $export_columns[$column] = $column;
            }
        }

        $export_order_statuses = !empty($_POST['order_status']) ? wc_clean($_POST['order_status']) : 'any';
        $products = !empty($_POST['products']) ? array_map('intval',$_POST['products']) : array();
        $email = !empty($_POST['email']) ? wc_clean($_POST['email']) : array();
        $coupons = !empty($_POST['coupons']) ? explode(',',trim(strtolower(wc_clean($_POST['coupons'])))) : array();
        $end_date = empty($_POST['end_date']) ? date('Y-m-d 23:59:59.99', current_time('timestamp')) : sanitize_text_field($_POST['end_date']) . ' 23:59:59.99';
        $start_date = empty($_POST['start_date']) ? date('Y-m-d 00:00:00', 0) : sanitize_text_field($_POST['start_date']). ' 00:00:00';
        $delimiter = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ',';// WPCS: CSRF ok, input var ok. 
        $delimiter = self::wt_set_csv_delimiter($delimiter);
        $exclude_already_exported = !empty($_POST['exclude_already_exported']) ? true : false;
        $export_to_separate_columns = !empty($_POST['export_to_separate_columns']) ? true : false;
        self::$include_hidden_meta = !empty($_POST['include_meta']) ? true : false;

        if ($limit > $export_limit)
            $limit = $export_limit;

        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);

        $enable_ftp_ie = isset($settings['ord_enable_ftp_ie']) ? $settings['ord_enable_ftp_ie'] : '';
        if($enable_ftp_ie){
            $ftp_server = isset($settings['ord_ftp_server']) ? $settings['ord_ftp_server'] : '';
            $ftp_user = isset($settings['ord_ftp_user']) ? $settings['ord_ftp_user'] : '';
            $ftp_password = isset($settings['ord_ftp_password']) ? $settings['ord_ftp_password'] : '';
            $ftp_port = isset($settings['ord_ftp_port']) ? $settings['ord_ftp_port'] : 21;
            $use_ftps = isset($settings['ord_use_ftps']) ? $settings['ord_use_ftps'] : '';
            $use_pasv = isset($settings['ord_use_pasv']) ? $settings['ord_use_pasv'] : '';
            $remote_path = isset($settings['ord_ftp_path']) ? $settings['ord_ftp_path'] : null;
        }
        
        if(self::$include_hidden_meta){
            self::$temp_order_metadata = apply_filters('wt_hidden_meta_columns',self::get_all_metakeys('shop_order'));
        }
        //ord_auto_export_ftp_file_name

        $exclude_already_exported_cron = isset($settings['exclude_already_exported']) ? $settings['exclude_already_exported'] : '';
        $export_to_separate_columns_cron = isset($settings['export_to_separate_columns']) ? $settings['export_to_separate_columns'] : '';
        $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
           @ob_end_clean();
           
        if (empty($order_IDS)) {
                if (!empty($email)&&empty($products) && empty($coupons)) {
                   
                    $args = array(
                        'customer_id' => $email,
                    );
                   $ord_email= wc_get_orders($args);
                      foreach ($ord_email as $id) {
                     $order_id[] =$id->get_id();   
                    }  
                    $order_ids =$order_id; 
                }else
                if(!empty($products) && empty($coupons) && empty($email)){
                    $order_ids = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                }elseif (!empty($coupons) && empty($products) && empty($email)) {
                    $order_ids = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                }elseif (!empty($coupons) && !empty($products) && empty($email)) {
                    $ord_prods = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $ord_coups = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $order_ids = array_intersect($ord_prods, $ord_coups);
                }elseif (!empty($coupons) && empty($products) && !empty($email)) {
                    $ord_coups = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    
                    $args = array(
                        'customer_id' => $email,
                    );
                    $ord_email = wc_get_orders($args);
                     foreach ($ord_email as $id) {
                     $order_id[] =$id->get_id();   
                    } 
                    $order_ids = array_intersect($order_id, $ord_coups);
                }elseif (empty($coupons) && !empty($products) && !empty($email)) {
                    $ord_prods = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    
                    $args = array(
                        'customer_id' => $email,
                    );
                    
                    $ord_email = wc_get_orders($args);
                    foreach ($ord_email as $id) {
                     $order_id[] =$id->get_id();   
                    }                  
               
                    $order_ids = array_intersect($ord_prods, $order_id);
                  
                }elseif (!empty($coupons) && !empty($products) && !empty($email)) {
                    $ord_prods = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $ord_coups = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                   
                    $args = array(
                        'customer_id' => $email,
                    );
                    $ord_email = wc_get_orders($args);
                     foreach ($ord_email as $id) {
                     $order_id[] =$id->get_id();   
                    }
                    $order_ids = array_intersect($ord_prods, $ord_coups,$order_id);
                }
                else {
                    $query_args = array(
                            'fields' => 'ids',
                            'post_type' => 'shop_order',
                            'post_status' => $export_order_statuses,
                            'posts_per_page' => $export_limit,
                            'offset' => $export_offset,
                            'date_query' => array(
                                    array(
                                            'before' => $end_date,
                                            'after' => $start_date,
                                            'inclusive' => true,
                                    ),
                            ),
                    );

                    if ($exclude_already_exported || ($exclude_already_exported_cron && $enable_ftp_ie)) {
                        $query_args['meta_query'][] = (array(
                                        'key' => 'wf_order_exported_status',
                                        'value' => FALSE,
                                        'compare' => 'NOT EXISTS',
                        ));
                    }
                    $query_args = apply_filters('wt_orderimpexpcsv_export_query_args',$query_args);
                    $query = new WP_Query($query_args);
                    $order_ids = $query->posts;
                }
            } else {
                $order_ids = $order_IDS;
            }

        if ($xmldata == '1') {
            if($export_to_separate_columns || ($export_to_separate_columns_cron && $enable_ftp_ie)){
                self::$line_item_meta = self::get_all_line_item_metakeys();                
            }
            // Export header rows
            include_once( 'class-OrderImpExpXML-order-exp-xml-gen.php' );
            $export = new OrderImpExpXML_OrderExpXMLGeneral_map($order_ids);
            $order_details = $export->get_orders($order_ids);
            $data_array = array('Orders' => array('Order' => $order_details));
            $data_array = WF_OrderImpExpCsv_Exporter::wf_order_xml_general_case_export_format($data_array, $order_details);
            $filename = 'wc_order_xml';
            $export->do_xml_export($filename, $export->get_order_details_xml($data_array));
        } else {
            if ($enable_ftp_ie) {
                $upload_path = wp_upload_dir();
                $file_path = $upload_path['path'] . '/';
                //$file = $post_type . "-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv"; 
                 $file_name = apply_filters('wt_order_export_filename', $file_path . $post_type . "-export-" . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv") ;

                $file = (!empty($settings['ord_auto_export_ftp_file_name'])) ? $file_path . $settings['ord_auto_export_ftp_file_name'] : $file_name ;
                $fp = fopen($file, 'w');
            } else {
                $file_name = apply_filters('wt_order_export_filename','woocommerce-order-export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . '.csv') ;
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename='.$file_name);
                header('Pragma: no-cache');
                header('Expires: 0');
         
                $fp = fopen('php://output', 'w');
            }

            // Headers

          /*  if (empty($order_IDS)) {
                if (!empty($email)&&empty($products) && empty($coupons)) {
                    $user = get_user_by('email', $email);
                    $id = $user->ID;
                    $args = array(
                        'customer_id' => $id,
                    );
                    $order_ids = wc_get_orders($args);
                }else
                if(!empty($products) && empty($coupons) && empty($email)){
                    $order_ids = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                }elseif (!empty($coupons) && empty($products) && empty($email)) {
                    $order_ids = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                }elseif (!empty($coupons) && !empty($products) && empty($email)) {
                    $ord_prods = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $ord_coups = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $order_ids = array_intersect($ord_prods, $ord_coups);
                }elseif (!empty($coupons) && empty($products) && !empty($email)) {
                    $ord_coups = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $user = get_user_by('email', $email);
                    $id = $user->ID;
                    $args = array(
                        'customer_id' => $id,
                    );
                    $ord_email = wc_get_orders($args);
                    $order_ids = array_intersect($ord_email, $ord_coups);
                }elseif (empty($coupons) && !empty($products) && !empty($email)) {
                    $ord_prods = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $user = get_user_by('email', $email);
                    $id = $user->ID;
                    $args = array(
                        'customer_id' => $id,
                    );
                    $ord_email = wc_get_orders($args);
                    $order_ids = array_intersect($ord_email, $ord_prods);
                }elseif (!empty($coupons) && !empty($products) && !empty($email)) {
                    $ord_prods = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $ord_coups = WF_OrderImpExpCsv_Exporter::hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported);
                    $user = get_user_by('email', $email);
                    $id = $user->ID;
                    $args = array(
                        'customer_id' => $id,
                    );
                    $ord_email = wc_get_orders($args);
                    $order_ids = array_intersect($ord_prods, $ord_coups,$ord_email);
                }
                else {
                    $query_args = array(
                            'fields' => 'ids',
                            'post_type' => 'shop_order',
                            'post_status' => $export_order_statuses,
                            'posts_per_page' => $export_limit,
                            'offset' => $export_offset,
                            'date_query' => array(
                                    array(
                                            'before' => $end_date,
                                            'after' => $start_date,
                                            'inclusive' => true,
                                    ),
                            ),
                    );

                    if ($exclude_already_exported || ($exclude_already_exported_cron && $enable_ftp_ie)) {
                        $query_args['meta_query'][] = (array(
                                        'key' => 'wf_order_exported_status',
                                        'value' => FALSE,
                                        'compare' => 'NOT EXISTS',
                        ));
                    }
                    $query_args = apply_filters('wt_orderimpexpcsv_export_query_args',$query_args);
                    $query = new WP_Query($query_args);
                    $order_ids = $query->posts;
                }
            } else {
                $order_ids = $order_IDS;
            }*/
            $order_ids = apply_filters('wt_orderimpexpcsv_alter_order_ids',$order_ids);
            // Variable to hold the CSV data we're exporting
            $row = array();
            
            // Export header rows
            foreach ($csv_columns as $column => $value) {
                if (!isset($user_columns_name[$column]))
                    continue;
                $temp_head = esc_attr($user_columns_name[$column]);
                if (!$export_columns || in_array($column, $export_columns))
                    $row[] = self::format_data ($temp_head);
            }
          
            if(self::$include_hidden_meta){
                $found_order_meta = array();
                // Some of the values may not be usable (e.g. arrays of arrays) but the worse
                // that can happen is we get an empty column.
                foreach (self::$temp_order_metadata as $meta) {
                    if (!$meta)
                        continue;
                    if(in_array(substr($meta, 1), array_keys($csv_columns)))
                        continue;
                    if (in_array($meta, $exclude_hidden_meta_columns) || in_array($meta, array_keys($csv_columns)))
                        continue;
                    $found_order_meta[] = $meta;
                }
                $found_order_meta = array_diff($found_order_meta, array_keys($csv_columns));
                $export_column_count = count($export_columns);
                $csv_column_count = count($row);
                $rows1 = $row;
                foreach ($found_order_meta as $key => $val) {
                    $rows1[] = 'meta:'.self::format_data($val);
                }
                $row = $rows1;
            }

            $max_line_items = WF_OrderImpExpCsv_Exporter::get_max_line_items($order_ids);
            for ($i = 1; $i <= $max_line_items; $i++) {
                $row[] = "line_item_{$i}";
            }
            
            if($export_to_separate_columns || ($export_to_separate_columns_cron && $enable_ftp_ie)){
                self::$line_item_meta = self::get_all_line_item_metakeys();
                for ($i = 1; $i <= $max_line_items; $i++) {
                    foreach (self::$line_item_meta as $meta_value){
                        $new_val = str_replace("_", " ", $meta_value);
                        $row["line_item_{$i}_name"] = "Product Item {$i} Name";
                        $row["line_item_{$i}_product_id"] = "Product Item {$i} id";
                        $row["line_item_{$i}_sku"] = "Product Item {$i} SKU";
                        $row["line_item_{$i}_quantity"] = "Product Item {$i} Quantity";
                        $row["line_item_{$i}_total"] = "Product Item {$i} Total";
                        $row["line_item_{$i}_subtotal"] = "Product Item {$i} Subtotal";                        
                        if(in_array($meta_value,array("_product_id","_qty","_variation_id","_line_total","_line_subtotal","_tax_class","_line_tax","_line_tax_data","_line_subtotal_tax"))){
                            continue;
                        } else {
                            $row["line_item_{$i}_$meta_value"] = "Product Item {$i} $new_val";
                        }
                    }
                }
            }
            $filter_args = array('export_columns' => $export_columns , 'csv_columns' => $csv_columns , 'max_line_items' => $max_line_items , 'order_ids' => $order_ids);
            $row = apply_filters('hf_alter_csv_header', $row, $filter_args); //Alter CSV Header
            
            $row = array_map('WF_OrderImpExpCsv_Exporter::wrap_column', $row);
            fwrite($fp, implode($delimiter, $row) . "\n");
            unset($row);
            // Loop orders
            foreach ($order_ids as $order_id) {
                //$row = array();   
                $data = WF_OrderImpExpCsv_Exporter::get_orders_csv_row($order_id, $export_columns, $max_line_items, $user_columns_name);
                // Add to csv

                $row = array_map('WF_OrderImpExpCsv_Exporter::wrap_column', $data);
                fwrite($fp, implode($delimiter, $row) . "\n");
                unset($row);
                unset($data);
                // updating records with expoted status 
                update_post_meta($order_id, 'wf_order_exported_status', TRUE);
            }

            if ($enable_ftp_ie) {
                
                // Upload ftp path with filename
                $remote_file = ( substr($remote_path, -1) != '/' ) ? ( $remote_path . "/" . basename($file) ) : ( $remote_path . basename($file) );
                
                // if have SFTP Add-on for Import Export for WooCommerce 
                if (class_exists('class_wf_sftp_import_export')) {
                    $sftp_export = new class_wf_sftp_import_export();
                    if (!$sftp_export->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                        $wf_order_ie_msg = 2;
                        wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex&wf_order_ie_msg=' . $wf_order_ie_msg));
                        die;
                    }
                    if ($sftp_export->put_contents($remote_file, file_get_contents($file))) {
                        $wf_order_ie_msg = 1;
                    } else {
                        $wf_order_ie_msg = 2;
                    }
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex&wf_order_ie_msg=' . $wf_order_ie_msg));
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
                    $wf_order_ie_msg = 1;
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex&wf_order_ie_msg=' . $wf_order_ie_msg));
                } else {
                    $wf_order_ie_msg = 2;
                    wp_redirect(admin_url('/admin.php?page=wf_woocommerce_order_im_ex&wf_order_ie_msg=' . $wf_order_ie_msg));
                }

                // close connection
                @ftp_close($ftp_conn);
                unlink($file);
            }
            fclose($fp);
            exit;
        }
    }

    public static function format_data($data) {
        if (!is_array($data));
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

    public static function get_max_line_items($order_ids) {
        $max_line_items = 0;
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            $line_items_count = count($order->get_items());
            if ($line_items_count >= $max_line_items) {
                $max_line_items = $line_items_count;
            }
        }
        return $max_line_items;
    }

    public static function get_orders_csv_row($order_id, $export_columns, $max_line_items, $user_columns_name = array()) {
        $csv_columns = include( 'data/data-wf-post-columns.php' );
        // Get an instance of the WC_Order object
        $order = wc_get_order($order_id);
        $line_items = $shipping_items = $fee_items = $tax_items = $coupon_items = $refund_items = array();

        // get line items
        foreach ($order->get_items() as $item_id => $item) {
            $product = $order->get_product_from_item($item);
            if (!is_object($product)) {
                $product = new WC_Product(0);
            }
            //$item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($item_id, '', false) : $order->get_item_meta($item_id);
            $item_meta = self::get_order_line_item_meta($item_id);
            $prod_type = (WC()->version < '3.0.0') ? $product->product_type : $product->get_type();
            $line_item = array(
                    'name' => html_entity_decode(!empty($item['name']) ? $item['name'] : $product->get_title(), ENT_NOQUOTES, 'UTF-8'),
                    'product_id' => (WC()->version < '2.7.0') ? $product->id : (($prod_type == 'variable' || $prod_type == 'variation' || $prod_type == 'subscription_variation') ? $product->get_parent_id() : $product->get_id()),
                    'sku' => $product->get_sku(),
                    'quantity' => $item['qty'],
                    'total' => wc_format_decimal($order->get_line_total($item), 2),
                    'sub_total' => wc_format_decimal($order->get_line_subtotal($item), 2),
                    //'meta' => html_entity_decode($meta, ENT_NOQUOTES, 'UTF-8'),
            );
            
            //add line item tax
            $line_tax_data = isset($item['line_tax_data']) ? $item['line_tax_data'] : array();
            $tax_data = maybe_unserialize($line_tax_data);
            $tax_detail = isset($tax_data['total']) ? wc_format_decimal(wc_round_tax_total(array_sum((array) $tax_data['total'])), 2) : '';
            if ($tax_detail != '0.00' && !empty($tax_detail)) {
                $line_item['tax'] = $tax_detail;
                $line_tax_ser = maybe_serialize($line_tax_data);
                $line_item['tax_data'] = $line_tax_ser;
            }
            
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

            $refunded = wc_format_decimal($order->get_total_refunded_for_item($item_id), 2);
            if ($refunded != '0.00') {
                $line_item['refunded'] = $refunded;
            }
        
            if ($prod_type === 'variable' || $prod_type === 'variation' || $prod_type === 'subscription_variation') {
                $line_item['_variation_id'] = (WC()->version > '2.7') ? $product->get_id() : $product->variation_id;
            }
            $line_items[] = $line_item;
        }

        /*
          foreach ($order->get_shipping_methods() as $_ => $shipping_item) {

          $shipping_items[] = implode('|', array(
          'method:' . $shipping_item['name'],
          'total:' . wc_format_decimal($shipping_item['cost'], 2),
          ));
          }
         * 
         */
        //shipping items is just product x qty under shipping method
        $line_items_shipping = $order->get_items('shipping');
        
        foreach ($line_items_shipping as $item_id => $item) {
            $item_meta = self::get_order_line_item_meta($item_id);
            foreach ($item_meta as $key => $value) {
                switch ($key){
                    case 'Items':
                    case 'method_id':
                    case 'taxes':
                        if(is_object($value))
                            $value = $value->meta_value;
                        if (is_array($value))
                            $value = implode(',', $value);
                        $meta[$key] = $value;
                        break;
                        
                }
            }
            foreach (array('Items','method_id','taxes') as $value){
                if(!isset($meta[$value])){
                    $meta[$value] = '';
                }
            }
            $shipping_items[] = trim(implode('|', array('items:' .$meta['Items'], 'method_id:' .$meta['method_id'], 'taxes:' .$meta['taxes'])));  
        }

        //get fee and total
        $fee_total = 0;
        $fee_tax_total = 0;

        foreach ($order->get_fees() as $fee_id => $fee) {
            $fee_items[] = implode('|', array(
                'name:' .  html_entity_decode($fee['name'], ENT_NOQUOTES, 'UTF-8'),
                'total:' . wc_format_decimal($fee['line_total'], 2),
                'tax:' . wc_format_decimal($fee['line_tax'], 2),
                'tax_data:' . maybe_serialize($fee['line_tax_data'])
            ));
            $fee_total += $fee['line_total'];
            $fee_tax_total += $fee['line_tax'];
        }

        // get tax items
        foreach ($order->get_tax_totals() as $tax_code => $tax) {
            $tax_items[] = implode('|', array(
                'rate_id:'.$tax->rate_id,
                'code:' . $tax_code,
                'total:' . wc_format_decimal($tax->amount, 2),
                'label:'.$tax->label,
                'tax_rate_compound:'.$tax->is_compound,
            ));
        }

        // add coupons
        foreach ($order->get_items('coupon') as $_ => $coupon_item) {
            $discount_amount = !empty($coupon_item['discount_amount']) ? $coupon_item['discount_amount'] : 0;
            $coupon_items[] = implode('|', array(
                    'code:' . $coupon_item['name'],
                    'amount:' . wc_format_decimal($discount_amount, 2),
            ));
        }

        foreach ($order->get_refunds() as $refunded_items){
            
            if ((WC()->version < '2.7.0')) {
                $refund_items[] = implode('|', array(
                    'amount:' . $refunded_items->get_refund_amount(),
                    'reason:' . $refunded_items->reason,
                    'date:' . date('Y-m-d H:i:s', strtotime( $refunded_items->date_created )),
                ));
            } else {
                $refund_items[] = implode('|', array(
                    'amount:' . $refunded_items->get_amount(),
                    'reason:' . $refunded_items->get_reason(),
                    'date:' . date('Y-m-d H:i:s', strtotime( $refunded_items->get_date_created())),
                ));
            }       
            
        }
        
        if (version_compare(WC_VERSION, '2.7', '<')) {
            $order_data = array(
                    'order_id' => $order->id,
                    'order_number' => $order->get_order_number(),
                    'order_date' => date('Y-m-d H:i:s', strtotime(get_post($order->id)->post_date)),
                    'status' => $order->get_status(),
                    'shipping_total' => $order->get_total_shipping(),
                    'shipping_tax_total' => wc_format_decimal($order->get_shipping_tax(), 2),
                    'fee_total' => wc_format_decimal($fee_total, 2),
                    'fee_tax_total' => wc_format_decimal($fee_tax_total, 2),
                    'tax_total' => wc_format_decimal($order->get_total_tax(), 2),
                    'cart_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
                    'order_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
                    'discount_total' => wc_format_decimal($order->get_discount_total(), 2),
                    'order_total' => wc_format_decimal($order->get_total(), 2),
                 //   'refunded_total' => wc_format_decimal($order->get_total_refunded(), 2),
                    'order_currency' => $order->get_order_currency(),
                    'payment_method' => $order->payment_method,
                    'shipping_method' => $order->get_shipping_method(),
                    'customer_id' => $order->get_user_id(),
                    'customer_user' => $order->get_user_id(),
                    'customer_email' => ($a = get_userdata($order->get_user_id() )) ? $a->user_email : '',
                    'billing_first_name' => $order->billing_first_name,
                    'billing_last_name' => $order->billing_last_name,
                    'billing_company' => $order->billing_company,
                    'billing_email' => $order->billing_email,
                    'billing_phone' => $order->billing_phone,
                    'billing_address_1' => $order->billing_address_1,
                    'billing_address_2' => $order->billing_address_2,
                    'billing_postcode' => $order->billing_postcode,
                    'billing_city' => $order->billing_city,
                    'billing_state' => $order->billing_state,
                    'billing_country' => $order->billing_country,
                    'shipping_first_name' => $order->shipping_first_name,
                    'shipping_last_name' => $order->shipping_last_name,
                    'shipping_company' => $order->shipping_company,
                    'shipping_address_1' => $order->shipping_address_1,
                    'shipping_address_2' => $order->shipping_address_2,
                    'shipping_postcode' => $order->shipping_postcode,
                    'shipping_city' => $order->shipping_city,
                    'shipping_state' => $order->shipping_state,
                    'shipping_country' => $order->shipping_country,
                    'customer_note' => $order->customer_note,
                    'wt_import_key' => $order->get_order_number(),
                    'shipping_items' => self::format_data(implode(';', $shipping_items)),
                    'fee_items' => implode('||', $fee_items),
                    'tax_items' => implode(';', $tax_items),
                    'coupon_items' => implode(';', $coupon_items),
                    'refund_items' => implode(';', $refund_items),
                    'order_notes' => implode('||', WF_OrderImpExpCsv_Exporter::get_order_notes($order)),
                    'download_permissions' => $order->download_permissions_granted ? $order->download_permissions_granted : 0,
            );
        } else {
            $order_data = array(
                    'order_id' => $order->get_id(),
                    'order_number' => $order->get_order_number(),
                    'order_date' => date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
                    'status' => $order->get_status(),
                    'shipping_total' => $order->get_total_shipping(),
                    'shipping_tax_total' => wc_format_decimal($order->get_shipping_tax(), 2),
                    'fee_total' => wc_format_decimal($fee_total, 2),
                    'fee_tax_total' => wc_format_decimal($fee_tax_total, 2),
                    'tax_total' => wc_format_decimal($order->get_total_tax(), 2),
                    'cart_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
                    'order_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
                    'discount_total' => wc_format_decimal($order->get_total_discount(), 2),
                    'order_total' => wc_format_decimal($order->get_total(), 2),
                //    'refunded_total' => wc_format_decimal($order->get_total_refunded(), 2),
                    'order_currency' => $order->get_currency(),
                    'payment_method' => $order->get_payment_method(),
                    'shipping_method' => $order->get_shipping_method(),
                    'customer_id' => $order->get_user_id(),
                    'customer_user' => $order->get_user_id(),
                    'customer_email' => ($a = get_userdata($order->get_user_id() )) ? $a->user_email : '',
                    'billing_first_name' => $order->get_billing_first_name(),
                    'billing_last_name' => $order->get_billing_last_name(),
                    'billing_company' => $order->get_billing_company(),
                    'billing_email' => $order->get_billing_email(),
                    'billing_phone' => $order->get_billing_phone(),
                    'billing_address_1' => $order->get_billing_address_1(),
                    'billing_address_2' => $order->get_billing_address_2(),
                    'billing_postcode' => $order->get_billing_postcode(),
                    'billing_city' => $order->get_billing_city(),
                    'billing_state' => $order->get_billing_state(),
                    'billing_country' => $order->get_billing_country(),
                    'shipping_first_name' => $order->get_shipping_first_name(),
                    'shipping_last_name' => $order->get_shipping_last_name(),
                    'shipping_company' => $order->get_shipping_company(),
                    'shipping_address_1' => $order->get_shipping_address_1(),
                    'shipping_address_2' => $order->get_shipping_address_2(),
                    'shipping_postcode' => $order->get_shipping_postcode(),
                    'shipping_city' => $order->get_shipping_city(),
                    'shipping_state' => $order->get_shipping_state(),
                    'shipping_country' => $order->get_shipping_country(),
                    'customer_note' => $order->get_customer_note(),
                    'wt_import_key' => $order->get_order_number(),
                    'shipping_items' => self::format_data(implode(';', $shipping_items)),
                    'fee_items' => implode('||', $fee_items),
                    'tax_items' => implode(';', $tax_items),
                    'coupon_items' => implode(';', $coupon_items),
                    'refund_items' => implode(';', $refund_items),
                    'order_notes' => implode('||', (defined('WC_VERSION') && (WC_VERSION >= 3.2)) ? WF_OrderImpExpCsv_Exporter::get_order_notes_new($order) : WF_OrderImpExpCsv_Exporter::get_order_notes($order)),
                    'download_permissions' => $order->is_download_permitted() ? $order->is_download_permitted() : 0,
            );
        }
        foreach ($order_data as $key => $value) {
            if (!$export_columns || in_array($key, $export_columns)) {
                // need to modify code
            } else {
                unset($order_data[$key]);
            }
        }
        
        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $enable_ftp_ie = isset($settings['ord_enable_ftp_ie']) ? $settings['ord_enable_ftp_ie'] : '';
        $export_to_separate_columns = !empty($_POST['export_to_separate_columns']) ? true : false;
        $export_to_separate_columns_cron = isset($settings['export_to_separate_columns']) ? $settings['export_to_separate_columns'] : '';
        $exclude_hidden_meta_columns = include( 'data/data-wf-exclude-hidden-meta-columns.php' );
        
        if(self::$include_hidden_meta){
            $found_order_meta = array();
            // Some of the values may not be usable (e.g. arrays of arrays) but the worse
            // that can happen is we get an empty column.
            foreach (self::$temp_order_metadata as $meta) {
                if (!$meta)
                    continue;
                if(in_array(substr($meta,1), array_keys($csv_columns)))
                    continue;
                if (in_array($meta, $exclude_hidden_meta_columns) || in_array($meta, array_keys($csv_columns)))
                    continue;
                $found_order_meta[] = $meta;
            }
                
            $found_order_meta = array_diff($found_order_meta, array_keys($csv_columns));
            $rows = $order_data;
//            foreach ($export_columns as $key => $value) {
//                $rows[$value] = ($order_data[$value]);
//                unset($order_data[$value]);
//            }
            foreach ($found_order_meta as $key => $value) {
                $rows["meta:".$value] = self::format_data(maybe_serialize(get_post_meta($rows['order_id'], $value, TRUE)));
            }
//            foreach ($order_data as $key => $value) {
//                $rows[$key] = maybe_unserialize($value);
//            }
            $order_data = $rows;
        }
        $li = 1;
        foreach ($line_items as $line_item) {
            foreach ($line_item as $name => $value) {
                $line_item[$name] = $name . ':' . $value;
            }
            $line_item = implode(apply_filters('wt_change_item_separator','|'), $line_item);
            $order_data["line_item_{$li}"] = $line_item;
            $li++;
        }

        for ($i = 1; $i <= $max_line_items; $i++) {
            $order_data["line_item_{$i}"] = !empty($order_data["line_item_{$i}"]) ? self::format_data($order_data["line_item_{$i}"]) : '';
        }
        
        if($export_to_separate_columns || ($export_to_separate_columns_cron && $enable_ftp_ie)){
            $line_item_values = self::get_all_metakeys_and_values($order);
            for ($i = 1; $i <= $max_line_items; $i++) {
                $line_item_array = explode('|', $order_data["line_item_{$i}"]);
                foreach (self::$line_item_meta as $meta_val){
                    $order_data["line_item_{$i}_name"] = !empty($line_item_array[0]) ? substr($line_item_array[0], strpos($line_item_array[0], ':') + 1) : '';
                    $order_data["line_item_{$i}_product_id"] = !empty($line_item_array[1]) ? substr($line_item_array[1], strpos($line_item_array[1], ':') + 1) : '';
                    $order_data["line_item_{$i}_sku"] = !empty($line_item_array[2]) ? substr($line_item_array[2], strpos($line_item_array[2], ':') + 1) : '';
                    $order_data["line_item_{$i}_quantity"] = !empty($line_item_array[3]) ? substr($line_item_array[3], strpos($line_item_array[3], ':') + 1) : '';
                    $order_data["line_item_{$i}_total"] = !empty($line_item_array[4]) ? substr($line_item_array[4], strpos($line_item_array[4], ':') + 1) : '';
                    $order_data["line_item_{$i}_subtotal"] = !empty($line_item_array[5]) ? substr($line_item_array[5], strpos($line_item_array[5], ':') + 1) : '';
                    if(in_array($meta_val,array("_product_id","_qty","_variation_id","_line_total","_line_subtotal","_tax_class","_line_tax","_line_tax_data","_line_subtotal_tax"))){
                        continue;
                    } else {
                        $order_data["line_item_{$i}_$meta_val"] = !empty($line_item_values[$i][$meta_val]) ? $line_item_values[$i][$meta_val] : '';
                    }
                }
            }
        }
        $order_data_filter_args = array('export_columns' => $export_columns, 'user_columns_name' => $user_columns_name, 'max_line_items' => $max_line_items);
        return apply_filters('hf_alter_csv_order_data', $order_data, $order_data_filter_args);
    }

    public static function get_order_notes($order) {
        $callback = array('WC_Comments', 'exclude_order_comments');
        $args = array(
                'post_id' => (WC()->version < '2.7.0') ? $order->id : $order->get_id(),
                'approve' => 'approve',
                'type' => 'order_note'
        );
        remove_filter('comments_clauses', $callback);
        $notes = get_comments($args);
        add_filter('comments_clauses', $callback);
        $notes = array_reverse($notes);
        $order_notes = array();
        foreach ($notes as $note) {
            $date = $note->comment_date;
            $customer_note = 0;
            if (get_comment_meta($note->comment_ID, 'is_customer_note', '1')){
                    $customer_note = 1;
            }
            $order_notes[] = implode('|', array(
                'content:' .str_replace(array("\r", "\n"), ' ', $note->comment_content),
                'date:'.(!empty($date) ? $date : current_time( 'mysql' )),
                'customer:'.$customer_note,
                'added_by:'.$note->added_by
             ));
        }
        return $order_notes;
    }
    
    public static function get_order_notes_new($order) {
        $notes = wc_get_order_notes(array('order_id' => $order->get_id(),'order_by' => 'date_created','order' => 'ASC'));
        $order_notes = array();
        foreach ($notes as $note) {
            $order_notes[] = implode('|', array(
                'content:' .str_replace(array("\r", "\n"), ' ', $note->content),
                'date:'.$note->date_created->date('Y-m-d H:i:s'),
                'customer:'.$note->customer_note,
                'added_by:'.$note->added_by
             ));
        }
        return $order_notes;
    }
    
    public static function get_all_metakeys($post_type = 'shop_order') {
        global $wpdb;
        $meta = $wpdb->get_col($wpdb->prepare(
                        "SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ) ORDER BY pm.meta_key", $post_type
        ));
        //sort($meta);
        return $meta;
    }

    public static function get_all_line_item_metakeys(){
        global $wpdb;
        $filter_meta = apply_filters('wt_order_export_select_line_item_meta',array());
        $filter_meta = !empty($filter_meta) ? implode("','",$filter_meta) : '';
        $query = "SELECT DISTINCT om.meta_key
            FROM {$wpdb->prefix}woocommerce_order_itemmeta AS om 
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON om.order_item_id = oi.order_item_id
            WHERE oi.order_item_type = 'line_item'";
        if(!empty($filter_meta)){
            $query .= " AND om.meta_key IN ('".$filter_meta."')";
        }
        $meta_keys = $wpdb->get_col($query);
        return $meta_keys;
    }
    
    public static function get_order_line_item_meta($item_id){
        global $wpdb;
        $filtered_meta = apply_filters('wt_order_export_select_line_item_meta',array());
        $filtered_meta = !empty($filtered_meta) ? implode("','",$filtered_meta) : '';
        $query = "SELECT meta_key,meta_value
            FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = '$item_id'";
        if(!empty($filtered_meta)){
            $query .= " AND meta_key IN ('".$filtered_meta."')";
        }
        $meta_keys = $wpdb->get_results($query , OBJECT_K );
        return $meta_keys;
    }

    public static function hf_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported){
        global $wpdb;
        $query = "SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
            LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
            WHERE po.post_type = 'shop_order'
            AND oi.order_item_type = 'line_item'
            AND om.meta_key IN ('_product_id','_variation_id')
            AND om.meta_value IN ('". implode("','", $products) ."')
            AND (po.post_date BETWEEN '$start_date' AND '$end_date')";
        if($export_order_statuses != 'any'){
            $query .= " AND po.post_status IN ( '" . implode("','", $export_order_statuses) . "' )";
        }
        if($exclude_already_exported){
            $query .= " AND pm.meta_key = 'wf_order_exported_status' AND pm.meta_value=1";
        }
        $query .= " LIMIT ".intval($export_limit).' '. (!empty($export_offset)? 'OFFSET '.intval($export_offset):'') ;
        $order_ids = $wpdb->get_col($query);
        return $order_ids;
    }
    
    public static function hf_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date,$exclude_already_exported){
        global $wpdb;
        $query = "SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
            LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
            WHERE po.post_type = 'shop_order'
            AND oi.order_item_type = 'coupon'
            AND oi.order_item_name IN ('". implode("','", $coupons) ."')
            AND (po.post_date BETWEEN '$start_date' AND '$end_date')";
        if($export_order_statuses != 'any'){
            $query .= " AND po.post_status IN ( '" . implode("','", $export_order_statuses) . "' )";
        }
        if($exclude_already_exported){
            $query .= " AND pm.meta_key = 'wf_order_exported_status' AND pm.meta_value=1";
        } 
        $query .= " LIMIT ".intval($export_limit).' '. (!empty($export_offset)? 'OFFSET '.intval($export_offset):'') ;
        $order_ids = $wpdb->get_col($query);
        return $order_ids;
    }

    public static function get_all_metakeys_and_values($order = null){
        $line_item_values = array();
        $in = 1;
        foreach ($order->get_items() as $item_id => $item) {
            //$item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($item_id, '', false) : $order->get_item_meta($item_id);
            $item_meta = self::get_order_line_item_meta($item_id);
            foreach ($item_meta as $key => $value) {
                switch ($key) {
                    case '_qty':
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
                        $line_item_value[$key] = $value;
                        break;
                }
                
            }
                $line_item_values[$in] = !empty($line_item_value) ? $line_item_value : '';
                $in++;
        }
        return $line_item_values;
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
