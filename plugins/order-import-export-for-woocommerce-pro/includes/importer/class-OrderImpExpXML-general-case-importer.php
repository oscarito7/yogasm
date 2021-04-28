<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_GeneralCaseImporter {
    
    public function __construct() {
        $this->ie_helper_object = new WT_ie_helper();
    }

    public function wf_order_xml_general_case_import_format($item) 
    {
        $general_order_arr['OrderId']                                       = (string)$item->OrderId;
        $general_order_arr['OrderDate']                                     = (string)$item->OrderDate;
        $general_order_arr['OrderStatus']                                   = (string)$item->OrderStatus;
        $general_order_arr['CustomerEmail']                                 = (string)$item->CustomerEmail;
        $general_order_arr['CustomerPhone']                                 = (string)$item->BillingPhone;
        $general_order_arr['CustomerNote']                                  = (string)$item->CustomerNote;
        $general_order_arr['BillingEmail']                                  = (string)$item->BillingEmail;
        $general_order_arr['RecipientBillingAddressFieldsFirstName']        = (string)$item->BillingFirstName;
        $general_order_arr['RecipientBillingAddressFieldsLastName']         = (string)$item->BillingLastName;
        $general_order_arr['RecipientBillingAddressFieldsCompany']          = (string)$item->BillingCompany;
        $general_order_arr['RecipientBillingAddressFieldsAddress1']         = (string)$item->BillingAddress1;
        $general_order_arr['RecipientBillingAddressFieldsAddress2']         = (string)$item->BillingAddress2;
        $general_order_arr['RecipientBillingAddressFieldsCity']             = (string)$item->BillingCity;
        $general_order_arr['RecipientBillingAddressFieldsState']            = (string)$item->BillingState;
        $general_order_arr['RecipientBillingAddressFieldsPostCode']         = (string)$item->BillingPostCode;
        $general_order_arr['RecipientBillingAddressFieldsCountry']          = (string)$item->BillingCountry;
        $general_order_arr['RecipientShippingAddressFieldsFirstName']       = (string)$item->ShippingFirstName;
        $general_order_arr['RecipientShippingAddressFieldsLastName']        = (string)$item->ShippingLastName;
        $general_order_arr['RecipientShippingAddressFieldsCompany']         = (string)$item->ShippingCompany;
        $general_order_arr['RecipientShippingAddressFieldsAddress1']        = (string)$item->ShippingAddress1;
        $general_order_arr['RecipientShippingAddressFieldsAddress2']        = (string)$item->ShippingAddress2;
        $general_order_arr['RecipientShippingAddressFieldsCity']            = (string)$item->ShippingCity;
        $general_order_arr['RecipientShippingAddressFieldsState']           = (string)$item->ShippingState;
        $general_order_arr['RecipientShippingAddressFieldsPostCode']        = (string)$item->ShippingPostCode;
        $general_order_arr['RecipientShippingAddressFieldsCountry']         = (string)$item->ShippingCountry;
        $general_order_arr['ShippingMethodId']                              = (string)$item->ShippingMethodId;
        $general_order_arr['ShippingMethod']                                = (string)$item->ShippingMethod;
        $general_order_arr['ShippingItems']                                 = (string)$item->ShippingItems;
        $general_order_arr['PaymentMethodId']                               = (string)$item->PaymentMethodId;
        $general_order_arr['PaymentMethod']                                 = (string)$item->PaymentMethod;
        $general_order_arr['OrderDiscountTotal']                            = (string)$item->OrderDiscountTotal;
        $general_order_arr['CartDiscountTotal']                             = (string)$item->CartDiscountTotal;
        $general_order_arr['DiscountTotal']                                 = (string)$item->DiscountTotal;
        $general_order_arr['ShippingTotal']                                 = (string)$item->ShippingTotal;
        $general_order_arr['ShippingTaxTotal']                              = (string)$item->ShippingTaxTotal;
        $general_order_arr['OrderTotal']                                    = (string)$item->OrderTotal;
        $general_order_arr['FeeTotal']                                      = (string)$item->FeeTotal;
        $general_order_arr['TaxTotal']                                      = (string)$item->TaxTotal;
        $general_order_arr['OrderCurrency']                                 = (string)$item->Currency;
        if(!empty($item->OrderLineItems)){
            for($i=0; $i<count($item->OrderLineItems->OrderLineItem); $i++)
            {
                $general_order_arr['Products'][$i]['ID']                        = (string)$item->OrderLineItems->OrderLineItem[$i]->ExternalID;
                $general_order_arr['Products'][$i]['SKU']                       = (string)$item->OrderLineItems->OrderLineItem[$i]->SKU;
                $general_order_arr['Products'][$i]['Name']                      = (string)$item->OrderLineItems->OrderLineItem[$i]->Name;
                $general_order_arr['Products'][$i]['Price']                     = (string)$item->OrderLineItems->OrderLineItem[$i]->Price;
                $general_order_arr['Products'][$i]['Quantity']                  = (string)$item->OrderLineItems->OrderLineItem[$i]->Quantity;
                $general_order_arr['Products'][$i]['Total']                     = (string)$item->OrderLineItems->OrderLineItem[$i]->Total;
                //$general_order_arr['Products'][$i]['Meta']                      = (string)$item->OrderLineItems->OrderLineItem[$i]->Meta;
                if(isset($item->OrderLineItems->OrderLineItem[$i]->VariationID)){
                    $general_order_arr['Products'][$i]['VariationID']                     = (string)$item->OrderLineItems->OrderLineItem[$i]->VariationID;
                }
            }
        }
        if($item->Meta){
            $general_order_arr['Meta']                                      = (array)$item->Meta;
        }
        
        $order['order_number'] = (string)$item->OrderId;
        if ($order['order_number']) {
           $order['postmeta'] =  $general_order_arr;
           $results[] = $order;
        }
        return $results;
    }

    public function wf_xml_process_order_general_new_insert($post)
    {
        
        if(empty($post['postmeta']['OrderDate'])){ $post['postmeta']['OrderDate'] = date();}
        $order_data = array(
            'import_id'     => $post['postmeta']['OrderId'],
            'post_name'     => 'order-' . date('M-d-Y-hi-a', strtotime($post['postmeta']['OrderDate']) ),
            'post_type'     => 'shop_order',
            'post_title'    => 'Order &ndash; ' . date('F d, Y @ h:i A', strtotime($post['postmeta']['OrderDate']) ), 
            'post_status'   => !empty($post['postmeta']['OrderStatus']) ? 'wc-'.$post['postmeta']['OrderStatus'] : 'pending',
            'ping_status'   => 'closed',
            'post_excerpt'  => isset($post['postmeta']['CustomerNote']) ? $post['postmeta']['CustomerNote'] : '',
            'post_author'   => 1,
            'post_password' => uniqid( 'order_' ), 
            'post_date'     => date('Y-m-d H:i:s e', strtotime($post['postmeta']['OrderDate']) ),
            'comment_status' => 'open'
        );
        if(class_exists('HF_Subscription')){
            remove_all_actions('save_post');
        }
        $order_id = wp_insert_post( $order_data, true );

        if ( is_wp_error( $order_id ) ) 
        {
            return FALSE;
        //    $order->errors = $order_id;
        } 
        else
        {
        //    $order->imported = true;
        if(!empty($post['postmeta']['CustomerEmail'])) {
            $found_customer = email_exists($post['postmeta']['CustomerEmail']) ? email_exists($post['postmeta']['CustomerEmail']) : 0;
        } else {
            $found_customer = 0;
        }

            // add a bunch of meta data
            add_post_meta($order_id, '_order_total', $post['postmeta']['OrderTotal'], true);
            add_post_meta($order_id, '_customer_user', $found_customer, true);
            add_post_meta($order_id, '_completed_date', date('Y-m-d H:i:s e', strtotime($post['postmeta']['OrderDate']) ), true);
            add_post_meta($order_id, '_order_currency', $post['postmeta']['OrderCurrency'], true);
            add_post_meta($order_id, '_paid_date', date('Y-m-d H:i:s e', strtotime($post['postmeta']['OrderDate']) ), true);

            //Billing info
            add_post_meta($order_id, '_billing_address_1', $post['postmeta']['RecipientBillingAddressFieldsAddress1'], true);
            add_post_meta($order_id, '_billing_address_2', $post['postmeta']['RecipientBillingAddressFieldsAddress2'], true);
            add_post_meta($order_id, '_billing_city', $post['postmeta']['RecipientBillingAddressFieldsCity'], true);
            add_post_meta($order_id, '_billing_state', $post['postmeta']['RecipientBillingAddressFieldsState'], true);
            add_post_meta($order_id, '_billing_postcode',$post['postmeta']['RecipientBillingAddressFieldsPostCode'], true);
            add_post_meta($order_id, '_billing_country', $post['postmeta']['RecipientBillingAddressFieldsCountry'], true);
            add_post_meta($order_id, '_billing_email', $post['postmeta']['BillingEmail'], true);
            add_post_meta($order_id, '_billing_first_name', $post['postmeta']['RecipientBillingAddressFieldsFirstName'], true);
            add_post_meta($order_id, '_billing_last_name', $post['postmeta']['RecipientBillingAddressFieldsLastName'], true);
            add_post_meta($order_id, '_billing_phone', $post['postmeta']['CustomerPhone'], true);

            //Shipping Info
            add_post_meta($order_id, '_shipping_address_1', $post['postmeta']['RecipientShippingAddressFieldsAddress1'], true);
            add_post_meta($order_id, '_shipping_address_2', $post['postmeta']['RecipientShippingAddressFieldsAddress2'], true);
            add_post_meta($order_id, '_shipping_city', $post['postmeta']['RecipientShippingAddressFieldsCity'], true);
            add_post_meta($order_id, '_shipping_state', $post['postmeta']['RecipientShippingAddressFieldsState'], true);
            add_post_meta($order_id, '_shipping_postcode',$post['postmeta']['RecipientShippingAddressFieldsPostCode'], true);
            add_post_meta($order_id, '_shipping_country', $post['postmeta']['RecipientShippingAddressFieldsCountry'], true);
            add_post_meta($order_id, '_shipping_first_name', $post['postmeta']['RecipientShippingAddressFieldsFirstName'], true);
            add_post_meta($order_id, '_shipping_last_name', $post['postmeta']['RecipientShippingAddressFieldsLastName'], true);


//            update_post_meta($order_id, '_shipping_method', $post['postmeta']['ShippingMethodId']);
//            update_post_meta($order_id, '_shipping_method_title', $post['postmeta']['ShippingMethod']);

            update_post_meta($order_id, '_payment_method', $post['postmeta']['PaymentMethodId']);
            update_post_meta($order_id, '_payment_method_title', $post['postmeta']['PaymentMethod']);

            update_post_meta($order_id, '_order_shipping', $post['postmeta']['ShippingTotal']);
//            update_post_meta($order_id, '_shipping_tax_total', $post['postmeta']['ShippingTaxTotal']);
            update_post_meta($order_id, '_order_shipping_tax', $post['postmeta']['ShippingTaxTotal']);

            update_post_meta($order_id, '_tax_total', $post['postmeta']['TaxTotal']);

            update_post_meta($order_id, '_order_discount', $post['postmeta']['OrderDiscountTotal']);
            update_post_meta($order_id, '_cart_discount', $post['postmeta']['CartDiscountTotal']);
            update_post_meta($order_id, '_order_total', $post['postmeta']['OrderTotal']);
            
            if(!empty($post['postmeta']['Meta'])){
                foreach ($post['postmeta']['Meta'] as $key => $value) {
                    update_post_meta($order_id, $key, $value);
                }  
            }

            $order_items = array();
            $order_item_meta = null;
            // Add product to order
            for($i=0; $i<count($post['postmeta']['Products']); $i++)
            {
                $product_id = $this->ie_helper_object->xa_wc_get_product_id_by_sku( $post['postmeta']['Products'][$i]['SKU'] );
                if ( $product_id )
                {
                    $product = wc_get_product( $product_id);
                    $var_id = '';
                    if(in_array($product->get_type(),array('variation','variable','subscription_variation'))){
                        $var_id = $product_id;
                        $product_id = $product->get_parent_id();
                    }
                    $item_id = wc_add_order_item( $order_id, array(
                        'order_item_name'       => $product ? $product->get_name() : __( 'Unknown Product', 'wf_order_import_export' ),
                        'order_item_type'       => 'line_item'
                    ) );
                    if ( $item_id ) 
                    {
                        //Add item meta data
                        wc_add_order_item_meta($item_id, '_product_id', $product_id);
                        wc_add_order_item_meta( $item_id, '_qty', $post['postmeta']['Products'][$i]['Quantity'] ); 
                        wc_add_order_item_meta( $item_id, '_tax_class', '' );
                        wc_add_order_item_meta( $item_id, '_variation_id', $var_id );
                        wc_add_order_item_meta( $item_id, '_line_total',  $post['postmeta']['Products'][$i]['Total'] );
                        wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( 0 ) );
                        wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( 0 ) );
                    }
                }
            }
            
            
            $shipping_items = $shipping_line_items = array();
            if(!empty($post['postmeta']['ShippingItems'])){
                $shipping_line_items = explode(';', $post['postmeta']['ShippingItems']);
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
            }
            
            // add shipping items
            if(!empty($post['postmeta']['ShippingMethod'])){
                $shipping_item_id = wc_add_order_item( $order_id, array(
                        'order_item_name'       => $post['postmeta']['ShippingMethod'],
                        'order_item_type'       => 'shipping'
                ) );
                if($shipping_item_id){
                    wc_add_order_item_meta($shipping_item_id, 'method_id', $post['postmeta']['ShippingMethodId']);
                    wc_add_order_item_meta($shipping_item_id,'cost',$post['postmeta']['ShippingTotal']);
                    wc_add_order_item_meta($shipping_item_id, 'total_tax', $post['postmeta']['ShippingTaxTotal']);
                    if (!empty($shipping_items)) {
                        foreach ($shipping_items as $shipping_item) {
                            wc_add_order_item_meta($shipping_item_id, $shipping_item['item'], $shipping_item['value']);
                        }
                    }
                }
            }
        }
        return $order_id;
    }

    public function wf_xml_process_order_general_order_exist($post,$import_decision)
    {
        $order_id = $post['postmeta']['OrderId'];
        if(empty($post['postmeta']['OrderDate'])){ $post['postmeta']['OrderDate'] = date();}
        $order_data = array(
            'ID'     => $post['postmeta']['OrderId'],
            'post_name'     => 'order-' . date('M-d-Y-hi-a', strtotime($post['postmeta']['OrderDate']) ),
            'post_type'     => 'shop_order',
            'post_title'    => 'Order &ndash; ' . date('F d, Y @ h:i A', strtotime($post['postmeta']['OrderDate']) ), 
            'post_status'   => !empty($post['postmeta']['OrderStatus']) ? 'wc-'.$post['postmeta']['OrderStatus'] : 'pending',
            'ping_status'   => 'closed',
            'post_excerpt'  => $post['postmeta']['CustomerNote'],
            'post_author'   => 1,
            'post_password' => uniqid( 'order_' ), 
            'post_date'     => date('Y-m-d H:i:s e', strtotime($post['postmeta']['OrderDate']) ),
            'comment_status' => 'open'
        );
        if(class_exists('HF_Subscription')){
            remove_all_actions('save_post');
        }
        wp_update_post($order_data);
       
        if(!empty($post['postmeta']['CustomerEmail'])){
            $found_customer = email_exists($post['postmeta']['CustomerEmail']) ? email_exists($post['postmeta']['CustomerEmail']) : 0;
        } else {
            $found_customer = 0;
        }
        
        update_post_meta($order_id, '_order_total', $post['postmeta']['OrderTotal']);
        update_post_meta($order_id, '_customer_user', $found_customer);
        update_post_meta($order_id, '_order_currency', $post['postmeta']['OrderCurrency']);  
        update_post_meta($order_id, '_completed_date', date('Y-m-d H:i:s e', strtotime($post['postmeta']['OrderDate']) ), true);
        update_post_meta($order_id, '_paid_date', date('Y-m-d H:i:s e', strtotime($post['postmeta']['OrderDate']) ), true);
        //Billing info
        update_post_meta($order_id, '_billing_address_1', $post['postmeta']['RecipientBillingAddressFieldsAddress1']);
        update_post_meta($order_id, '_billing_address_2', $post['postmeta']['RecipientBillingAddressFieldsAddress2']);
        update_post_meta($order_id, '_billing_city', $post['postmeta']['RecipientBillingAddressFieldsCity']);
        update_post_meta($order_id, '_billing_state', $post['postmeta']['RecipientBillingAddressFieldsState']);
        update_post_meta($order_id, '_billing_postcode',$post['postmeta']['RecipientBillingAddressFieldsPostCode']);
        update_post_meta($order_id, '_billing_country', $post['postmeta']['RecipientBillingAddressFieldsCountry']);
        update_post_meta($order_id, '_billing_email', $post['postmeta']['BillingEmail']);
        update_post_meta($order_id, '_billing_first_name', $post['postmeta']['RecipientBillingAddressFieldsFirstName']);
        update_post_meta($order_id, '_billing_last_name', $post['postmeta']['RecipientBillingAddressFieldsLastName']);
        update_post_meta($order_id, '_billing_phone', $post['postmeta']['CustomerPhone']);

        //Shipping Info
        update_post_meta($order_id, '_shipping_address_1', $post['postmeta']['RecipientShippingAddressFieldsAddress1']);
        update_post_meta($order_id, '_shipping_address_2', $post['postmeta']['RecipientShippingAddressFieldsAddress2']);
        update_post_meta($order_id, '_shipping_city', $post['postmeta']['RecipientShippingAddressFieldsCity']);
        update_post_meta($order_id, '_shipping_state', $post['postmeta']['RecipientShippingAddressFieldsState']);
        update_post_meta($order_id, '_shipping_postcode',$post['postmeta']['RecipientShippingAddressFieldsPostCode']);
        update_post_meta($order_id, '_shipping_country', $post['postmeta']['RecipientShippingAddressFieldsCountry']);
        update_post_meta($order_id, '_shipping_first_name', $post['postmeta']['RecipientShippingAddressFieldsFirstName']);
        update_post_meta($order_id, '_shipping_last_name', $post['postmeta']['RecipientShippingAddressFieldsLastName']);

//        update_post_meta($order_id, '_shipping_method', $post['postmeta']['ShippingMethodId']);
//        update_post_meta($order_id, '_shipping_method_title', $post['postmeta']['ShippingMethod']);

        update_post_meta($order_id, '_payment_method', $post['postmeta']['PaymentMethodId']);
        update_post_meta($order_id, '_payment_method_title', $post['postmeta']['PaymentMethod']);

        update_post_meta($order_id, '_order_shipping', $post['postmeta']['ShippingTotal']);
        update_post_meta($order_id, '_shipping_tax_total', $post['postmeta']['ShippingTaxTotal']);


        update_post_meta($order_id, '_tax_total', $post['postmeta']['TaxTotal']);

        update_post_meta($order_id, '_order_discount', $post['postmeta']['OrderDiscountTotal']);
        update_post_meta($order_id, '_cart_discount', $post['postmeta']['CartDiscountTotal']);
        update_post_meta($order_id, '_order_total', $post['postmeta']['OrderTotal']);
        if(!empty($post['postmeta']['Meta'])){
            foreach ($post['postmeta']['Meta'] as $key => $value) {
                update_post_meta($order_id, $key, (string)$value);
            }  
        }


        global $wpdb;
        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        foreach ($items as $key => $product ) 
        {
           wc_delete_order_item( $key );
        }

        // Add product to order
         $order_items = array();
            $order_item_meta = null;
            // Add product to order
            for($i=0; $i<count($post['postmeta']['Products']); $i++)
            {
                $product_id = $this->ie_helper_object->xa_wc_get_product_id_by_sku( $post['postmeta']['Products'][$i]['SKU'] );
                if ( $product_id )
                {
                    $product = wc_get_product( $product_id);
                    $var_id = '';
                    if(in_array($product->get_type(),array('variation','variable','subscription_variation'))){
                        $var_id = $product_id;
                        $product_id = $product->get_parent_id();
                    }

                    $item_id = wc_add_order_item( $order_id, array(
                        'order_item_name'       => $product ? $product->get_name() : __( 'Unknown Product', 'wf_order_import_export' ),
                        'order_item_type'       => 'line_item'
                    ) );
                    if ( $item_id ) 
                    {
                        //Add item meta data
                        wc_add_order_item_meta($item_id, '_product_id', $product_id);
                        wc_add_order_item_meta( $item_id, '_qty', $post['postmeta']['Products'][$i]['Quantity'] ); 
                        wc_add_order_item_meta( $item_id, '_tax_class', '' );
                        wc_add_order_item_meta( $item_id, '_variation_id', $var_id );
                        wc_add_order_item_meta( $item_id, '_line_total',  $post['postmeta']['Products'][$i]['Total'] );
                        wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( 0 ) );
                        wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( 0 ) );
                    }
                }
            }
            
            $shipping_items = $shipping_line_items = array();
            if(!empty($post['postmeta']['ShippingItems'])){
                $shipping_line_items = explode(';', $post['postmeta']['ShippingItems']);
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
            }
            
            // add shipping items
            if(!empty($post['postmeta']['ShippingMethod'])){
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type IN ('shipping')", $order_id));
                $shipping_item_id = wc_add_order_item( $order_id, array(
                        'order_item_name'       => $post['postmeta']['ShippingMethod'],
                        'order_item_type'       => 'shipping'
                ) );
                if($shipping_item_id){
                    wc_add_order_item_meta($shipping_item_id, 'method_id', $post['postmeta']['ShippingMethodId']);
                    wc_add_order_item_meta($shipping_item_id,'cost',$post['postmeta']['ShippingTotal']);
                    wc_add_order_item_meta($shipping_item_id, 'total_tax', $post['postmeta']['ShippingTaxTotal']);
                    if (!empty($shipping_items)) {
                        foreach ($shipping_items as $shipping_item) {
                            wc_add_order_item_meta($shipping_item_id, $shipping_item['item'], $shipping_item['value']);
                        }
                    }
                }
            }
        return $order_id;
    }   
          
}


