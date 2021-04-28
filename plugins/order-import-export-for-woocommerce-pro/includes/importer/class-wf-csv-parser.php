<?php

/**
 * WooCommerce CSV Importer class for managing parsing of CSV files.
 */
class WF_CSV_Parser_Ord {

    var $row;
    var $post_type;
    var $posts = array();
    var $processed_posts = array();
    var $file_url_import_enabled = true;
    var $log;
    var $merged = 0;
    var $skipped = 0;
    var $imported = 0;
    var $errored = 0;
    var $id;
    var $file_url;
    var $delimiter;

    /**
     * Constructor
     */
    public function __construct($post_type = 'shop_order') {
        $this->post_type = $post_type;

        $this->order_meta_fields = array(
            "order_tax",
            "order_shipping",
            "order_shipping_tax",
            "shipping_total",
            "shipping_tax_total",
            "fee_total",
            "fee_tax_total",
            "tax_total",
            "discount_total",
            "customer_user",
            "cart_discount",
            "order_discount",
            "order_total",
            "order_currency",
            "payment_method",
            "customer_email",
            "billing_first_name",
            "billing_last_name",
            "billing_company",
            "billing_address_1",
            "billing_address_2",
            "billing_city",
            "billing_state",
            "billing_postcode",
            "billing_country",
            "billing_email",
            "billing_phone",
            "shipping_first_name",
            "shipping_last_name",
            "shipping_company",
            "shipping_address_1",
            "shipping_address_2",
            "shipping_city",
            "shipping_state",
            "shipping_postcode",
            "shipping_country",
            "shipping_method",
            "Download Permissions Granted",
            "download_permissions"
        );
    }

    /**
     * Format data from the csv file
     * @param  string $data
     * @param  string $enc
     * @return string
     */
    public function format_data_from_csv($data, $enc) {
        return ( $enc == 'UTF-8' ) ? $data : utf8_encode($data);
    }

    /**
     * Parse the data
     * @param  string  $file      [description]
     * @param  string  $delimiter [description]
     * @param  array  $mapping   [description]
     * @param  integer $start_pos [description]
     * @param  integer  $end_pos   [description]
     * @return array
     */
    public function parse_data($file, $delimiter, $mapping, $start_pos = 0, $end_pos = null, $eval_field) {
        // Set locale
        $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
        if ($enc)
            setlocale(LC_ALL, 'en_US.' . $enc);
        @ini_set('auto_detect_line_endings', true);

        $parsed_data = array();
        $raw_headers = array();

        // Put all CSV data into an associative array
        if (( $handle = fopen($file, "r") ) !== FALSE) {

            $header = fgetcsv($handle, 0, $delimiter);
            if(substr($header[0],0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
                $header[0]= str_replace('"','',substr($header[0],3));
            }
            if ($start_pos != 0)
                fseek($handle, $start_pos);

            while (( $postmeta = fgetcsv($handle, 0, $delimiter) ) !== FALSE) {
                $row = array();

                foreach ($header as $key => $heading) {
                    $s_heading = $heading;

                    // Check if this heading is being mapped to a different field
                    if (isset($mapping[$s_heading])) {
                        if ($mapping[$s_heading] == 'import_as_meta') {

                            $s_heading = 'meta:' . $s_heading;
                        } else {
                            $s_heading = esc_attr($mapping[$s_heading]);
                        }
                    }
                    foreach ($mapping as $mkey => $mvalue) {
                        if (trim($mvalue) === trim($heading)) {
                            $s_heading = $mkey;
                        }
                    }

                    if ($s_heading == '')
                        continue;

                    // Add the heading to the parsed data
                    $row[$s_heading] = ( isset($postmeta[$key]) ) ? $this->format_data_from_csv($postmeta[$key], $enc) : '';
                    if (!empty($eval_field[$s_heading]))
                        $row[$s_heading] = $this->evaluate_field($row[$s_heading], $eval_field[$s_heading]);

                    // Raw Headers stores the actual column name in the CSV
                    $raw_headers[$s_heading] = $heading;
                }
                $parsed_data[] = $row;

                unset($postmeta, $row);

                $position = ftell($handle);

                if ($end_pos && $position >= $end_pos)
                    break;
            }
            fclose($handle);
        }
        $parsed_data = apply_filters('wt_order_import_csv_parsed_datas',$parsed_data, $raw_headers, $position );
        return array($parsed_data, $raw_headers, $position);
    }

    private function evaluate_field($value, $evaluation_field) {
        $processed_value = $value;
        if (!empty($evaluation_field)) {
            $operator = substr($evaluation_field, 0, 1);
            if (in_array($operator, array('=', '+', '-', '*', '/', '&', '@'))) {
                $eval_val = substr($evaluation_field, 1);
                switch ($operator) {
                    case '=':
                        $processed_value = trim($eval_val);
                        break;
                    case '+':
                        $processed_value = $this->hf_currency_formatter($value) + $eval_val;
                        break;
                    case '-':
                        $processed_value = $value - $eval_val;
                        break;
                    case '*':
                        $processed_value = $value * $eval_val;
                        break;
                    case '/':
                        $processed_value = $value / $eval_val;
                        break;
                    case '@':
                        if (!(bool) strtotime($value)) {
                            $value = str_replace("/", "-", $value);
                            $eval_val = str_replace("/", "-", $eval_val);
                        }
                        if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                            $date = DateTime::createFromFormat($eval_val, $value);
                            if($date){
                                $processed_value = $date->format('Y-m-d H:i:s');
                            }
                        } else {
                            $processed_value = date("d-m-Y H:i:s", strtotime($value));
                        }

                        break;
                    case '&':
                        if (strpos($eval_val, '[VAL]') !== false) {
                            $processed_value = str_replace('[VAL]', $value, $eval_val);
                        } else {
                            $processed_value = $value . $eval_val;
                        }
                        break;
                }
            }
        }
        return $processed_value;
    }

    /**
     * Parse orders
     * @param  array  $item
     * @param  integer $merge_empty_cells
     * @return array
     */
    public function parse_orders($parsed_data, $raw_headers, $merging, $record_offset,$ord_link_using_sku) {
        $item = $parsed_data;
        $item = apply_filters('wt_woocommerce_csv_order_parse_data_before',$item );        
        global $WF_CSV_Order_Import, $wpdb;
        //$allow_unknown_products = isset( $_POST['allow_unknown_products'] ) && $_POST['allow_unknown_products'] ? true : false;
        $allow_unknown_products = true;
        $results = array();

        $row = 0;
        $skipped = 0;

        $available_methods = WC()->shipping()->load_shipping_methods();
        $available_gateways = WC()->payment_gateways->payment_gateways();
        $shop_order_status = $this->wc_get_order_statuses_neat();

        $tax_rates = array();

        foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates") as $_row) {
            $tax_rates[$_row->tax_rate_id] = $_row;
        }

        $row++;

        if ($row <= $record_offset) {
            $WF_CSV_Order_Import->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> Row %s - skipped due to record offset.', 'wf_order_import_export'), $row));
            unset($item);
            return;
        }

        $postmeta = $order = array();

        $order_number_formatted = @$item['order_id'];
        $order_number = (!empty($item['order_number']) ? $item['order_number'] : ( is_numeric($order_number_formatted) ? $order_number_formatted : 0 ) );


        if ($order_number_formatted) {
            // verify that this order number isn't already in use
            $query_args = array(
                'numberposts' => 1,
                'meta_key' => apply_filters('woocommerce_order_number_formatted_meta_name', '_order_number_formatted'),
                'meta_value' => $order_number_formatted,
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'fields' => 'ids',
            );

            $order_id = 0;
            $orders = get_posts($query_args);
            if (!empty($orders)) {
                list( $order_id ) = get_posts($query_args);
            }

            $order_id = apply_filters('woocommerce_find_order_by_order_number', $order_id, $order_number_formatted);

            if ($order_id) {
                // skip if order ID already exist. 
                $WF_CSV_Order_Import->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> > Skipped. Order %s already exists.', 'wf_order_import_export'), $order_number_formatted));
                $skipped++;
                unset($item);
                return;
            }
        }

        // handle the special (optional) customer_user field
        if (isset($item['customer_id']) && $item['customer_id']) {
            // attempt to find the customer user
            $found_customer = false;
            if (is_int($item['customer_id'])) {

                $found_customer = get_user_by('id', $item['customer_id']);

                if (!$found_customer) {

                    $WF_CSV_Order_Import->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> > Skipped. Cannot find customer with id %s.', 'wf_order_import_export'), $item['customer_id']));
                    $skipped++;
                    unset($item);
                    return;
                    ;
                }
            } elseif (is_email($item['customer_id'])) {
                // check by email
                $found_customer = email_exists($item['customer_id']);
            }

            if (!$found_customer) {
                $found_customer = username_exists($item['customer_id']);
            }

            if (!$found_customer) {
                // guest checkout
                $item['customer_id'] = 0;
            } else {
                $item['customer_id'] = $found_customer; // user id
            }
        } elseif (isset($item['customer_email']) && $item['customer_email']) {
            // see if we can link to user by customer email id
            $found_customer = email_exists($item['customer_email']);
            if ($found_customer)
                $item['customer_id'] = $found_customer;
            else
                $item['customer_id'] = 0;  // guest checkout
        } elseif (isset($item['billing_email']) && $item['billing_email']) {
            // see if we can link to user by billing email id
            $found_customer = email_exists($item['billing_email']);
            if ($found_customer)
                $item['customer_id'] = $found_customer;
            else
                $item['customer_id'] = 0;  // guest checkout
        } else {
            // guest checkout
            $item['customer_id'] = 0;
        }


        if (!empty($item['status'])) {
            $order['status'] = $item['status'];
            $found_status = false;
            $available_statuses = array();
            foreach ($shop_order_status as $status_slug => $status_name) {
                if (0 == strcasecmp($status_slug, $item['status']))
                    $found_status = true;
                $available_statuses[] = $status_slug;
            }

            if (!$found_status) {
                $WF_CSV_Order_Import->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> > Skipped. Unknown order status %s (%s).', 'wf_order_import_export'), $item['status'], implode($available_statuses, ', ')));
                $skipped++;
                unset($item);
                return;
            }
        }

        if (!empty($item['order_date'])){
            $item['date'] = $item['order_date'];
        } elseif(isset($item['order_date'])) {
            $item['date'] = time();
        }
        if (!empty($item['date'])) {
            if (false === ( $item['date'] = strtotime($item['date']) )) {
                // invalid date format
                $WF_CSV_Order_Import->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> > Skipped. Invalid date format %s.', 'wf_order_import_export'), $item['date']));
                $skipped++;
                unset($item);
                return;
            }
            $order['date'] = $item['date'];
        }


        $order_notes = array();
        if (!empty($item['order_notes'])) {
            $order_notes = explode("||", $item['order_notes']);
        }

        // build the order data object
        $order['order_id'] = !empty($item['order_id']) ? $item['order_id'] : null;
        $order['order_comments'] = !empty($item['customer_note']) ? $item['customer_note'] : null;
        $order['wt_import_key'] = !empty($item['wt_import_key']) ? $item['wt_import_key'] : null;
        $order['notes'] = $order_notes;
        if (!is_null($order_number))
            $order['order_number'] = $order_number;  // optional order number, for convenience
        if ($order_number_formatted)
            $order['order_number_formatted'] = $order_number_formatted;

        // totals
        $order_tax = $order_shipping_tax = null;

        // Get any known order meta fields, and default any missing ones to 0/null
        // the provided shipping/payment method will be used as-is, and if found in the list of available ones, the respective titles will also be set
        foreach ($this->order_meta_fields as $column) {

            switch ($column) {

                case 'shipping_method':
                    if (isset($item[$column])) {
                        $value = $item[$column];

                        // look up shipping method by id or title
                        $shipping_method = isset($available_methods[$value]) ? $value : null;

                        if (!$shipping_method) {
                            // try by title
                            foreach ($available_methods as $method) {
                                if (0 === strcasecmp($method->title, $value)) {
                                    $shipping_method = $method->id;
                                    break;  // go with the first one we find
                                }
                            }
                        }

                        if ($shipping_method) {
                            // known shipping method found
                            $postmeta[] = array('key' => '_shipping_method', 'value' => $shipping_method);
                            $postmeta[] = array('key' => '_shipping_method_title', 'value' => $available_methods[$shipping_method]->title);
                        } elseif ($value) {
                            // Standard format, shipping method but no title
                            $postmeta[] = array('key' => '_shipping_method', 'value' => $value);
                            $postmeta[] = array('key' => '_shipping_method_title', 'value' => '');
                        } else {
                            // none
                            $postmeta[] = array('key' => '_shipping_method', 'value' => '');
                            $postmeta[] = array('key' => '_shipping_method_title', 'value' => '');
                        }
                    }
                    break;

                case 'payment_method':
                    if (isset($item[$column])) {
                        $value = $item[$column];

                        // look up shipping method by id or title
                        $payment_method = isset($available_gateways[$value]) ? $value : null;
                        if (!$payment_method) {
                            // try by title
                            foreach ($available_gateways as $method) {
                                if (0 === strcasecmp($method->title, $value)) {
                                    $payment_method = $method->id;
                                    break;  // go with the first one we find
                                }
                            }
                        }

                        if ($payment_method) {
                            // known payment method found
                            $postmeta[] = array('key' => '_payment_method', 'value' => $payment_method);
                            $postmeta[] = array('key' => '_payment_method_title', 'value' => $available_gateways[$payment_method]->title);
                        } elseif ($value) {
                            // Standard format, payment method but no title
                            $postmeta[] = array('key' => '_payment_method', 'value' => $value);
                            $postmeta[] = array('key' => '_payment_method_title', 'value' => '');
                        } else {
                            // none
                            $postmeta[] = array('key' => '_payment_method', 'value' => '');
                            $postmeta[] = array('key' => '_payment_method_title', 'value' => '');
                        }
                    }
                    break;

                // handle numerics
                case 'order_shipping':  // legacy
                case 'shipping_total':
                    if (isset($item[$column])) {
                        $order_shipping = $item[$column];  // save the order shipping total for later use
                        $postmeta[] = array('key' => '_order_shipping', 'value' => number_format((float) $order_shipping, 2, '.', ''));
                    }
                    break;
                case 'order_shipping_tax':  // legacy
                case 'shipping_tax_total':
                    // ignore blanks but allow zeroes
                    if (isset($item[$column]) && is_numeric($item[$column])) {
                        $order_shipping_tax = $item[$column];
                    }
                    break;
                case 'order_tax':  // legacy
                case 'tax_total':
                    // ignore blanks but allow zeroes
                    if (isset($item[$column]) && is_numeric($item[$column])) {
                        $order_tax = $item[$column];
                    }
                    break;
                case 'order_discount':
                case 'cart_discount':
                case 'order_total':
                    if (isset($item[$column])) {
                        $value = $item[$column];
                        $postmeta[] = array('key' => '_' . $column, 'value' => number_format((float) $value, 2, '.', ''));
                    }
                    break;

                case 'billing_country':
                case 'shipping_country':
                    if (isset($item[$column])) {
                        $value = $item[$column];
                        // support country name or code by converting to code
                        $country_code = array_search($value, WC()->countries->countries);
                        if ($country_code)
                            $value = $country_code;
                        $postmeta[] = array('key' => '_' . $column, 'value' => $value);
                    }
                    break;

                case 'Download Permissions Granted':
                case 'download_permissions_granted':
                    if (isset($item['download_permissions_granted'])) {
                        $postmeta[] = array('key' => '_download_permissions_granted', 'value' => $item['download_permissions_granted']);
                    }
                    break;
                case 'download_permissions':
                    if (isset($item['download_permissions'])) {
                        $postmeta[] = array('key' => '_download_permissions_granted', 'value' => $item['download_permissions']);
                        $postmeta[] = array('key' => '_download_permissions', 'value' => $item['download_permissions']);
                    }
                    break;

                default:

                    if (isset($item[$column])) {
                        $postmeta[] = array('key' => '_' . $column, 'value' => $item[$column]);
                    }
            }
        }

        if (!empty($item['customer_id']))  // update postmeta after find user by customer_id or customer_email or billing_email 
            $postmeta[] = array('key' => '_customer_user', 'value' => $item['customer_id']);

        // Get any custom meta fields
        foreach ($item as $key => $value) {

            if (!$value) {
                continue;
            }

            // Handle meta: columns - import as custom fields
            if (strstr($key, 'meta:')) {

                // Get meta key name
                $meta_key = ( isset($raw_headers[$key]) ) ? $raw_headers[$key] : $key;
                $meta_key = trim(str_replace('meta:', '', $meta_key));

                // Add to postmeta array
                $postmeta[] = array(
                    'key' => esc_attr($meta_key),
                    'value' => $value,
                );
            }
        }

        $order_shipping_methods = array();
        $_shipping_methods = array();

        // pre WC 2.1 format of a single shipping method, left for backwards compatibility of import files
        if (isset($item['shipping_method']) && $item['shipping_method']) {
            // collect the shipping method id/cost
            $_shipping_methods[] = array(
                $item['shipping_method'],
                isset($item['shipping_cost']) ? $item['shipping_cost'] : null
            );
        }

        // collect any additional shipping methods
        $i = null;
        if (isset($item['shipping_method_1'])) {
            $i = 1;
        } elseif (isset($item['shipping_method_2'])) {
            $i = 2;
        }

        if (!is_null($i)) {
            while (!empty($item['shipping_method_' . $i])) {

                $_shipping_methods[] = array(
                    $item['shipping_method_' . $i],
                    isset($item['shipping_cost_' . $i]) ? $item['shipping_cost_' . $i] : null
                );
                $i++;
            }
        }

        // if the order shipping total wasn't set, calculate it
        if (!isset($order_shipping)) {

            $order_shipping = 0;
            foreach ($_shipping_methods as $_shipping_method) {
                $order_shipping += $_shipping_method[1];
            }
            $postmeta[] = array('key' => '_order_shipping' . $column, 'value' => number_format((float) $order_shipping, 2, '.', ''));
        } elseif (isset($order_shipping) && 1 == count($_shipping_methods) && is_null($_shipping_methods[0][1])) {
            // special case: if there was a total order shipping but no cost for the single shipping method, use the total shipping for the order shipping line item
            $_shipping_methods[0][1] = $order_shipping;
        }

        foreach ($_shipping_methods as $_shipping_method) {

            // look up shipping method by id or title
            $shipping_method = isset($available_methods[$_shipping_method[0]]) ? $_shipping_method[0] : null;

            if (!$shipping_method) {
                // try by title
                foreach ($available_methods as $method) {
                    if (0 === strcasecmp($method->title, $_shipping_method[0])) {
                        $shipping_method = $method->id;
                        break;  // go with the first one we find
                    }
                }
            }

            if ($shipping_method) {
                // known shipping method found
                $order_shipping_methods[] = array('cost' => $_shipping_method[1], 'title' => $available_methods[$shipping_method]->title);
            } elseif ($_shipping_method[0]) {
                // Standard format, shipping method but no title
                $order_shipping_methods[] = array('cost' => $_shipping_method[1], 'title' => '');
            }
        }

        $order_items = array();

        // standard format
        if (isset($item['line_item_1']) && !empty($item['line_item_1'])) {
            // one or more order items
            $i = 1;
            while (isset($item['line_item_' . $i]) && !empty($item['line_item_' . $i])) {
                $variation = FALSE;
                //$_item_meta = preg_split("~\\\\.(*SKIP)(*FAIL)|\|~s", $item['line_item_' . $i]);
                $_item_meta = array();
                if ($item['line_item_' . $i] && empty($_item_meta)) {
                    $_item_meta = explode(apply_filters('wt_change_item_separator','|'), $item['line_item_' . $i]);
                }
                
                // get any additional item meta
                $item_meta = array();
                foreach ($_item_meta as $pair) {

                    // replace any escaped pipes
                    $pair = str_replace('\|', '|', $pair);

                    // find the first ':' and split into name-value
                    $split = strpos($pair, ':');
                    $name = substr($pair, 0, $split);
                    $value = substr($pair, $split + 1);
                    switch ($name) {
                        case 'name':
                            $unknown_product_name = $value;
                            break;
                        case 'product_id':
                            $product_identifier_by_id = $value;
                            break;
                        case 'sku':
                            $product_identifier_by_sku = $value;
                            break;
                        case 'quantity':
                            $qty = $value;
                            break;
                        case 'total':
                            $total = $value;
                            break;
                        case 'sub_total':
                            $sub_total = $value;
                            break;
                        case 'tax':
                            $tax = $value;
                            break;
                        case 'tax_data':
                            $tax_data = $value;
                            break;
                        default :
                            $item_meta[$name] = $value;
                    }
                    
                }
                
                if($ord_link_using_sku || (empty($product_identifier_by_id))){
                    $product_sku = !empty($product_identifier_by_sku) ? $product_identifier_by_sku : '';
                    if ($product_sku){
                        $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $product_sku));
                        if(!empty($product_id)){
                            if(get_post_type($product_id) == 'product_variation'){
                                $variation = TRUE;
                                $variation_id = $product_id;
                                $product_id = wp_get_post_parent_id($variation_id);
                                $item_meta['_variation_id'] = $variation_id;
                            }
                        }
                    } else {
                        $product_id = '';
                    }
                } else {
                    if (!empty($product_identifier_by_id)) {
                        // product by product_id
                        $product_id = $product_identifier_by_id;

                        // not a product
                        if (!in_array(get_post_type($product_id), array('product', 'product_variation'))) {
                            $product_id = '';
                        }
                    } else {
                        $product_id = '';
                    }
                }

                if (!$allow_unknown_products && !$product_id) {
                    // unknown product
                    $WF_CSV_Order_Import->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> > Skipped. Unknown order item: %s.', 'wf_order_import_export'), $product_identifier));
                    $skipped++;
                    $i++;
                    continue;  // break outer loop
                }

                
                $order_items[$i] = array(
                    'product_id'    => !empty($product_id) ? $product_id : 0,
                    'qty'           => !empty($qty) ? $qty : 0,
                    'total'         => !empty($total) ? $total : 0,
                    'sub_total'     => !empty($sub_total) ? $sub_total : 0,
                    'tax'           => !empty($tax) ? $tax : 0,
                    'meta'          => $item_meta,
                    'product_name'  => !empty($unknown_product_name) ? $unknown_product_name : ''
                );
                if(!empty($tax_data)){
                    $order_items[$i]['tax_data'] = $tax_data;
                }
                unset($product_id);
                $i++;
            }
        }


        // standard format
        $coupon_item = array();

        if(isset($item['coupon_items']) && !empty($item['coupon_items'])){
            $coupon_item = explode(';', $item['coupon_items']);
        }
        
        //added since refund not importing 
        $refund_item = array();
        if(isset($item['refund_items']) && !empty($item['refund_items'])){
            $refund_item = explode(';', $item['refund_items']);
        }


        $tax_items = array();

        // standard tax item format which supports multiple tax items in numbered columns containing a pipe-delimated, colon-labeled format
        if (isset($item['tax_items']) && !empty($item['tax_items'])) {
            // one or more order tax items
            // get the first tax item
            $tax_item = explode(';', $item['tax_items']);
//            $tax_amount_sum = $shipping_tax_amount_sum = 0;
            foreach ($tax_item as $tax) {

                $tax_item_data = array();

                // turn "label: Tax | tax_amount: 10" into an associative array
                foreach (explode('|', $tax) as $piece) {
                    list( $name, $value ) = explode(':', $piece);
                    $tax_item_data[trim($name)] = trim($value);
                }
                
                // default rate id to 0 if not set
                if (!isset($tax_item_data['rate_id'])) {
                    $tax_item_data['rate_id'] = 0;
                }

                // have a tax amount or shipping tax amount
                if (isset($tax_item_data['total']) || isset($tax_item_data['shipping_tax_amount'])) {
                    // try and look up rate id by label if needed
                    if (isset($tax_item_data['label']) && $tax_item_data['label'] && !$tax_item_data['rate_id']) {
                        foreach ($tax_rates as $tax_rate) {

                            if (0 === strcasecmp($tax_rate->tax_rate_name, $tax_item_data['label'])) {
                                // found the tax by label
                                $tax_item_data['rate_id'] = $tax_rate->tax_rate_id;
                                break;
                            }
                        }
                    }

                    // check for a rate being specified which does not exist, and clear it out (technically an error?)
                    if ($tax_item_data['rate_id'] && !isset($tax_rates[$tax_item_data['rate_id']])) {
                        $tax_item_data['rate_id'] = 0;
                    }

                    // default label of 'Tax' if not provided
                    if (!isset($tax_item_data['label']) || !$tax_item_data['label']) {
                        $tax_item_data['label'] = 'Tax';
                    }

                    // default tax amounts to 0 if not set
                    if (!isset($tax_item_data['total'])) {
                        $tax_item_data['total'] = 0;
                    }
                    if (!isset($tax_item_data['shipping_tax_amount'])) {
                        $tax_item_data['shipping_tax_amount'] = 0;
                    }

                    // handle compound flag by using the defined tax rate value (if any)
                    if (!isset($tax_item_data['tax_rate_compound'])) {
                        $tax_item_data['tax_rate_compound'] = '';
                        if ($tax_item_data['rate_id']) {
                            $tax_item_data['tax_rate_compound'] = $tax_rates[$tax_item_data['rate_id']]->tax_rate_compound;
                        }
                    }
                    
                    $tax_items[] = array(
                        'title' => $tax_item_data['code'],
                        'rate_id' => $tax_item_data['rate_id'],
                        'label' => $tax_item_data['label'],
                        'compound' => $tax_item_data['tax_rate_compound'],
                        'tax_amount' => $tax_item_data['total'],
                        'shipping_tax_amount' => $tax_item_data['shipping_tax_amount'],
                    );
                }
            }
        }

        // add the order tax totals to the order meta
        $postmeta[] = array('key' => '_order_tax', 'value' => number_format((float) $order_tax, 2, '.', ''));
        $postmeta[] = array('key' => '_order_shipping_tax', 'value' => number_format((float) $order_shipping_tax, 2, '.', ''));

        // fee items
        $fee_items = $fee_line_items = array();
        if (isset($item['fee_items']) && !empty($item['fee_items'])) {
            $fee_line_items = explode('||', $item['fee_items']);

            $fee_item_data = array();
            foreach ($fee_line_items as $fee_line_item) {
                $fee_item_meta = explode('|', $fee_line_item);
                $name = array_shift($fee_item_meta);
                $name = substr($name, strpos($name, ":") + 1);
                $total = array_shift($fee_item_meta);
                $total = substr($total, strpos($total, ":") + 1);
                $tax = array_shift($fee_item_meta);
                $tax = substr($tax, strpos($tax, ":") + 1);
                $tax_data = array_shift($fee_item_meta);
                $tax_data = substr($tax_data, strpos($tax_data, ":") + 1);

                $fee_items[] = array(
                    'name' => $name,
                    'total' => $total,
                    'tax' => $tax,
                    'tax_data' => $tax_data
                );
            }
        }
        
        $shipping_items = $shipping_line_items = array();
        if(isset($item['shipping_items']) && !empty($item['shipping_items'])){
            $shipping_line_items = explode('|', $item['shipping_items']);
            $items = array_shift($shipping_line_items);
            $items = substr($items, strpos($items, ":") + 1);
            $method_id = array_shift($shipping_line_items);
            $method_id = substr($method_id, strpos($method_id, ":") + 1);
            $taxes = array_shift($shipping_line_items);
            $taxes = substr($taxes, strpos($taxes, ":") + 1);
            
            $shipping_items = array(
                'Items' => $items,
                'method_id' => $method_id,
                'taxes' => $taxes
            );
        }

        if ($order) {
            $order['postmeta'] = $postmeta;
            $order['order_items'] = $order_items;
            $order['coupon_items'] = $coupon_item;
            $order['refund_items'] = $refund_item;
            $order['order_shipping'] = $order_shipping_methods;
            $order['tax_items'] = $tax_items;
            $order['fee_items'] = $fee_items;
            $order['shipping_items'] = $shipping_items;
            // the order array will now contain the necessary name-value pairs for the wp_posts table, and also any meta data in the 'postmeta' array
            $results[] = apply_filters('wt_orderimpexpcsv_alter_parsed_order_data',$order,$item);
        }
        
        // Result
        return array(
            $this->post_type => $results,
            'skipped' => $skipped,
        );
    }

    function hf_currency_formatter($price) {
        $decimal_seperator = wc_get_price_decimal_separator();
        return preg_replace("[^0-9\\'.$decimal_seperator.']", "", $price);
    }

    private function wc_get_order_statuses_neat() {
        $order_statuses = array();
        foreach (wc_get_order_statuses() as $slug => $name) {
            $order_statuses[preg_replace('/^wc-/', '', $slug)] = $name;
        }
        return $order_statuses;
    }
}