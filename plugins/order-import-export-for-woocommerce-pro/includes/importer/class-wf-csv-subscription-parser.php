<?php

/**
 * WooCommerce CSV Importer class for managing parsing of CSV files.
 */
class WF_CSV_Subscription_Parser {

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
    public function __construct($post_type = 'shop_subscription') {
        $this->post_type = $post_type;
        $this->order_meta_fields = array(
            'subscription_status',
            'billing_period',
            'billing_interval',
            'order_shipping',
            'order_shipping_tax',
            'order_tax',
            'cart_discount',
            'cart_discount_tax',
            'order_total',
            'order_currency',
            'payment_method',
            'payment_method_title',
            'billing_first_name',
            'billing_last_name',
            'billing_email',
            'billing_phone',
            'billing_address_1',
            'billing_address_2',
            'billing_postcode',
            'billing_city',
            'billing_state',
            'billing_country',
            'billing_company',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_postcode',
            'shipping_city',
            'shipping_state',
            'shipping_country',
            'shipping_company',
            'download_permissions'
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
    
    public function hf_make_user_active( $user_id ) {
	$this->hf_update_users_role( $user_id, 'default_subscriber_role' );
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
                            $processed_value = $date->format('Y-m-d H:i:s');
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
    public function parse_subscription_orders($parsed_data, $raw_headers, $merging, $record_offset) {
        $data = $parsed_data;
        global $WF_CSV_Subscription_Order_Import, $wpdb;
        
        $post_meta = array();
        $result = array();

        $result['customer_id'] = $data['customer_id'];
        $result['subscription_id'] = !empty($data['subscription_id']) ? $data['subscription_id'] : 0;
        $result['customer_username'] = $data['customer_username'];
        $result['customer_email'] = $data['customer_email'];
        $result['payment_method'] = $data['payment_method'];

        $missing_shipping_addresses = $missing_billing_addresses = array();
        
        $tax_rates = array();

        foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates") as $_row) {
            $tax_rates[$_row->tax_rate_id] = $_row;
        }

        foreach ($this->order_meta_fields as $column) {
            switch ($column) {
                case 'cart_discount':
                case 'cart_discount_tax':
                case 'order_shipping':
                case 'order_shipping_tax':
                case 'order_total':
                    $value = (!empty($data[$column])) ? $data[$column] : 0;
                    $post_meta[] = array('key' => '_' . $column, 'value' => $value);
                    break;

                case 'payment_method':
                    $payment_method = (!empty($data[$column]) ) ? strtolower($data[$column]) : '';
                    $title = (!empty($data['payment_method_title']) ) ? $data['payment_method_title'] : $payment_method;

                    if (!empty($payment_method) && 'manual' != $payment_method) {
                        $post_meta[] = array('key' => '_' . $column, 'value' => $payment_method);
                        $post_meta[] = array('key' => '_payment_method_title', 'value' => $title);
                    }
                    break;

                case 'shipping_address_1':
                case 'shipping_city':
                case 'shipping_postcode':
                case 'shipping_state':
                case 'shipping_country':
                case 'billing_address_1':
                case 'billing_city':
                case 'billing_postcode':
                case 'billing_state':
                case 'billing_country':
                case 'billing_phone':
                case 'billing_company':
                case 'billing_email':
                    $value = (!empty($data[$column]) ) ? $data[$column] : '';

                    if (empty($value)) {
                    //    $metadata = get_user_meta($user_id, $column);
                        $value = (!empty($metadata[0]) ) ? $metadata[0] : '';
                    }

                    if (empty($value) && 'billing_email' == $column) {
                        $value = (!empty($data['customer_email']) ) ? $data['customer_email'] : get_userdata($user_id)->user_email;
                    }

                    if (empty($value)) {
                        if (0 === strpos($column, 'billing_')) {
                            $missing_billing_addresses[] = $column;
                        } else {
                            $missing_shipping_addresses[] = $column;
                        }
                    }

                    $post_meta[] = array('key' => '_' . $column, 'value' => $value);
                    break;

                default:
                    $value = (!empty($data[$column]) ) ? $data[$column] : '';
                    $post_meta[] = array('key' => '_' . $column, 'value' => $value);
            }
        }
        
        // Get any custom meta fields
        foreach ($data as $key => $value) {
            if (!$value) {
                continue;
            }

            // Handle meta: columns - import as custom fields
            if (strstr($key, 'meta:')) {

                // Get meta key name
                $meta_key = ( isset($raw_headers[$key]) ) ? $raw_headers[$key] : $key;
                $meta_key = trim(str_replace('meta:', '', $meta_key));
                
                if(!empty($value)){
                    // Add to postmeta array
                    $post_meta[] = array(
                        'key' => esc_attr($meta_key),
                        'value' => $value,
                    );
                }
            }
        }
        if (empty($data['subscription_status'])) {
            $status = 'pending';
            $WF_CSV_Subscription_Order_Import->log->add(sprintf(__('No subscription status was specified. The subscription will be created with the status "pending". ', 'wf_order_import_export')), 'wf_order_import_export');
        } else {
            $status = $data['subscription_status'];
        }
        $result['subscription_status'] = $status;
        $dates_to_update = array('start' => (!empty($data['date_created'])) ? gmdate('Y-m-d H:i:s', strtotime($data['date_created'])) : '');
        foreach (array('last_order_date_created', 'trial_end_date', 'next_payment_date', 'end_date') as $date_type) {
            $dates_to_update[$date_type] = (!empty($data[$date_type]) ) ? gmdate('Y-m-d H:i:s', strtotime($data[$date_type])) : '';
            $result[$date_type] = $dates_to_update[$date_type];
        }
        foreach ($dates_to_update as $date_type => $datetime) {
            if (empty($datetime)) {
                continue;
            }
            switch ($date_type) {
                case 'end_date' :
                    if (!empty($dates_to_update['last_order_date_created']) && strtotime($datetime) <= strtotime($dates_to_update['last_order_date_created'])) {
                        $WF_CSV_Subscription_Order_Import->log->add(sprintf(__('The %s date must occur after the last payment date.', 'wf_order_import_export'), $date_type),'wf_order_import_export');
                    }
                    if (!empty($dates_to_update['next_payment_date']) && strtotime($datetime) <= strtotime($dates_to_update['next_payment_date'])) {
                        $WF_CSV_Subscription_Order_Import->log->add(sprintf(__('The %s date must occur after the next payment date.', 'wf_order_import_export'), $date_type), 'wf_order_import_export');
                    }
                case 'next_payment_date' :
                    if (!empty($dates_to_update['trial_end_date']) && strtotime($datetime) < strtotime($dates_to_update['trial_end_date'])) {
                        $WF_CSV_Subscription_Order_Import->log->add(sprintf(__('The %s date must occur after the trial end date.', 'wf_order_import_export'), $date_type), 'wf_order_import_export');
                    }
                case 'trial_end_date' :
                    if (strtotime($datetime) <= strtotime($dates_to_update['start'])) {
                        $WF_CSV_Subscription_Order_Import->log->add(sprintf(__('The %s must occur after the start date.', 'wf_order_import_export'), $date_type), 'wf_order_import_export');
                    }
            }
        }
        $result['start_date'] = $dates_to_update['start'];
        $result['dates_to_update'] = $dates_to_update;
        $result['post_parent'] = isset($data['post_parent']) ? $data['post_parent'] : 0;
        $result['billing_interval'] = (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1;
        $result['billing_period'] = (!empty($data['billing_period']) ) ? $data['billing_period'] : '';
        $result['created_via'] = 'importer';
        $result['customer_note'] = (!empty($data['customer_note']) ) ? $data['customer_note'] : '';
        $result['currency'] = (!empty($data['order_currency']) ) ? $data['order_currency'] : '';
        $result['post_meta'] = $post_meta;

        if (!empty($data['order_notes'])) {
            $result['order_notes'] = $data['order_notes'];
        }
        
        if (!empty($data['renewal_orders'])) {
            $result['renewal_orders'] = $data['renewal_orders'];
        }

        if (!empty($data['coupon_items'])) {
            $result['coupon_items'] = $data['coupon_items'];
        }

        if (!empty($data['tax_items'])) {
            $tax_item = explode(';', $data['tax_items']);
            $tax_items = array();
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
            
            $result['tax_items'] = $tax_items;
        }

        if (!empty($data['order_items'])) {
            $_order_items = explode('||', $data['order_items']);
            foreach ($_order_items as $item) {
                if(!empty($item)){
                    $_item_meta = explode(apply_filters('wt_subscription_change_item_separator','|'), $item);
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
                            $product_name = $value;
                            break;
                        case 'product_id':
                            $product_id = $value;
                            break;
                        case 'sku':
                            $sku = $value;
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

                $order_items[] = array('product_id' => $product_id, 'sku' => $sku, 'qty' => $qty, 'total' => $total, 'sub_total' => $sub_total,'tax' => $tax, 'tax_data' => $tax_data,'meta' => $item_meta, 'name' => $product_name);

                /*
                $product_id = array_shift($_item_meta);
                $product_id = substr($product_id, strpos($product_id, ":") + 1);
                $product_name = array_shift($_item_meta);
                $product_name = substr($product_name, strpos($product_name, ":") + 1);
                $sku = array_shift($_item_meta);
                $sku = substr($sku, strpos($sku, ":") + 1);
                $qty = array_shift($_item_meta);
                $qty = substr($qty, strpos($qty, ":") + 1);
                $total = array_shift($_item_meta);
                $total = substr($total, strpos($total, ":") + 1);
                $sub_total = array_shift($_item_meta);
                $sub_total = substr($sub_total, strpos($sub_total, ":") + 1);
                $tax = array_shift($_item_meta);
                $tax = substr($tax, strpos($tax, ":") + 1);
                $tax_data = array_shift($_item_meta);
                $tax_data = substr($tax_data, strpos($tax_data, ":") + 1);
                
                 // get any additional item meta
                $item_meta = array();
                foreach ($_item_meta as $pair) {

                    // replace any escaped pipes
                    $pair = str_replace('\|', '|', $pair);

                    // find the first ':' and split into name-value
                    $split = strpos($pair, ':');
                    $name = substr($pair, 0, $split);
                    $value = substr($pair, $split + 1);

                    $item_meta[$name] = $value;
                }
                $order_items[] = array('product_id' => $product_id, 'sku' => $sku, 'qty' => $qty, 'total' => $total, 'sub_total' => $sub_total,'tax' => $tax, 'tax_data' => $tax_data,'meta' => $item_meta, 'name' => $product_name);
                
                */
            }
                       
            $result['order_items'] = $order_items;
        }
        
        if (!empty($data['order_currency'])) {
            $result['order_currency'] = $data['order_currency'];
        }

        if (!empty($data['fee_items'])) {
            $result['fee_items'] = $data['fee_items'];
        }

        if (!empty($data['shipping_method'])) {
            $result['shipping_method'] = $data['shipping_method'];
        }
        
        $shipping_items = $shipping_line_items = array();
        if(!empty($data['shipping_items'])){
            $shipping_line_items = explode(';', $data['shipping_items']);
            $shipping_item_data = array();
            foreach ($shipping_line_items as $shipping_line_item) {
                foreach (explode('|', $shipping_line_item) as $piece) {
                    list( $name, $value ) = explode(':', $piece);
                    $shipping_item_data[trim($name)] = trim($value);
                }
                if(!isset($shipping_item_data['item'])){
                    $shipping_item_data['item'] = '';
                }
                if(!isset($shipping_item_data['value'])){
                    $shipping_item_data['value'] = 0;
                }
                $shipping_items[] = array(
                    'item' => $shipping_item_data['item'],
                    'value' => $shipping_item_data['value']
                ); 
            }
            $result['shipping_items'] = $shipping_items;
        }

        $skipped = 0; // do the valiation in parsing before import and update skipped count and message
        return array($this->post_type => $result, 'skipped' => $skipped);
    }

    function hf_currency_formatter($price) {
        $decimal_seperator = wc_get_price_decimal_separator();
        return preg_replace("[^0-9\\'.$decimal_seperator.']", "", $price);
    }
  
}
