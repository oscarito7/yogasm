<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_EndiciaExporter {

    public function generate_xml_endicia($order_ids){
        
        include_once( 'class-OrderImpExpXML-order-exp-xml-general.php' );
        $export = new OrderImpExpXML_OrderExpXMLGeneral($order_ids);
        $order_details = $export->get_orders($order_ids);
        $data_array = array('Orders' => array('Order' => $order_details));
        $data_array = OrderImpExpXML_EndiciaExporter::wf_order_xml_endicia_export_format($data_array, $order_details);

        foreach($data_array as $id => $data) {
            $open_shipments_array [] = $data;
        }
        $order_data_array = array(
            'Print' => $open_shipments_array
        );
        

        $filename='order_endicia_xml';
        $xmlns = '';
        $dt = new DateTime();
        $export->do_xml_export($filename, $export->get_order_details_xml($order_data_array, $xmlns));
        return $xmlns;
    }
    public function wf_order_xml_endicia_export_format($formated_orders, $raw_orders) {
        $order_details = array();
        
        foreach ($raw_orders as $order) 
        {
            $item = '';
            foreach($order['OrderLineItems'] as $order_items)
            {
                if(isset($order_items['Name']) && $order_items['Name'] !=''){$item .= ', '.$order_items['Name']; }
//                if(isset($order_items['Quantity']) && $order_items['Quantity'] !=''){$quantity += $order_items['Quantity']; }
//                if(isset($order_items['Quantity']) && $order_items['Quantity'] !=''){$quantity += $order_items['Quantity']; }
            }            
            if ($order['StoreCountry'] == $order['ShippingCountry']) {
                $order_data = array('Item' => array(
                    'DAZzle' => array(
                        '@attributes' => array(
                                'Layout' => 'c:\Users\Public\Documents\Endicia\DAZzle\Dymo4XL Label.lyt',
                                'Prompt'  => 'YES',
                                'Test'    => 'NO',
                                'Start'   => 'PRINTING',
                                'OutputFile' => 'C:\DAZZLE sample.XML',
                                'AutoClose' => 'NO',
                                'PartnerID' => 'A1B2'
                            ),
                        'Package' => array(
                            '@attributes' => array(
                                'ID' => 1
                            ),
                            'MailClass'     => 'PRIORITY',
                            'DateAdvance'   => 4,
                            'PackageType'   => 'FLATRATEENVELOPE',
                            'WeightOz'      => $order['OrderLineItems']['total_weight'],
                            'Width'         => $order['OrderLineItems']['total_width'],
                            'Length'        => $order['OrderLineItems']['total_length'],
                            'Depth'         => $order['OrderLineItems']['total_width'],
                            'OversizeRate'  => FALSE,
                            'Services'      => array(
                                '@attributes' => array(
                                    'CertifiedMail'     => 'OFF',
                                    'USPSTracking' => 'OFF',
                                ),

                            ),
                            'Value'         => 27.00,
                            'Description'   => $item,
                            'ReferenceID'   => $order['OrderId'],
                            'ToName'        => $order['ShippingFirstName'].' '.$order['ShippingLastName'],
                            'ToAddress1'    => $order['ShippingAddress1'],
                            'ToCity'        => $order['ShippingCity'],
                            'ToState'       => $order['ShippingState'],
                            'ToPostalCode'  => $order['ShippingPostCode'],
                            'ToEMail'       => $order['BillingEmail'], 
                        ),
                    ),),
                );
            }
            else
            {
                $order_data = array('Item' => array(
                    'DAZzle' => array(
                        '@attributes' => array(
                                'Layout' => 'c:\Users\Public\Documents\Endicia\DAZzle\International Label - Small 6x4.lyt',
                                'Prompt'  => 'YES',
                                'Test'    => 'NO',
                                'Start'   => 'PRINTING',
                                'OutputFile' => 'C:\singleintlout.xml',
                                'AutoClose' => 'NO',
                                'PartnerID' => 'A1B2'
                            ),
                        'Package' => array(
                            '@attributes' => array(
                                'ID' => 1
                            ),
                            'MailClass'     => 'PRIORITY',
                            'PackageType'   => 'FLATRATEENVELOPE',
                            'WeightOz'      => $order['OrderLineItems']['total_weight'],
                            'Services'      => array(
                                '@attributes' => array(
                                    'RegisteredMail'    => 'OFF',
                                    'InsuredMail'       => 'ON',
                                    'CertifiedMail'     => 'OFF',
                                    'RestrictedDelivery' => 'OFF',
                                    'CertificateOfMailing' => 'OFF',
                                    'ReturnReceipt' => 'OFF',
                                    'USPSTracking' => 'OFF',
                                    'SignatureConfirmation' => 'OFF',
                                    'COD' => 'OFF'
                                ),
                            ),

                            'Value'         => 24.45,
                            'Description'   => $item,
                            'CustomsDescription1' => $item,
                            'CustomsQuantity1' => $order['OrderLineItems']['total_qty'],
                            'CustomsWeight1' => $order['OrderLineItems']['total_weight'],
                            'CustomsValue1' => 24.45,
                            'CustomsCountry1' => 'Italy',
                            'ContentsType' => $item,
                            'LicenseNo' => 123456789,
                            'CertificateNo' => 79146,
                            'Comments' => 'Comments',
                            'SendersCustomsReference'   => $order['OrderId'],
                            'ImportersCustomsReference' => 147,
                            'AesItnExemption' => 'NOEEI 30.37(h)',
                            'International' => array(
                                '@attributes' => array(
                                    'IfNonDeliverable' => 'Forward',
                                    'Address1'         => get_user_meta(1, 'billing_address_1', true),
                                    'Address2'         => get_user_meta(1, 'billing_address_2', true),
                                    'Address3'         => get_user_meta(1, 'billing_city', true).', '.get_user_meta(1, 'billing_postcode', true),
                                    'Address4'         => get_user_meta(1, 'billing_state', true).', '.get_user_meta(1, 'billing_country', true)
                                ),
                            ),
                            'CustomsSigner' => 'Joe Shipper',
                            'CustomsCertify' => 'TRUE',
                            'ToName'        => $order['ShippingFirstName'].' '.$order['ShippingLastName'],
                            'ToAddress1'    => $order['ShippingAddress1'],
                            'ToCity'        => $order['ShippingCity'],
                            'ToState'       => $order['ShippingState'],
                            'ToPostalCode'  => $order['ShippingPostCode'],
                            'ToEMail'       => $order['BillingEmail'], 
                            'ReturnAddressPhone' => get_user_meta(1, 'billing_phone', true),
                            'ReturnAddressEmail' => get_option('admin_email'),
                            'RubberStamp1'  => 'Item Description',
                            'RubberStamp2'  => '381099999'
                        ),
                    ),),
                );
            }
           $order_details[] = $order_data;
        }
        //$formated_orders = array('Print' => array('Item' => $order_details));
         return apply_filters('hf_endicia_order_export',$order_details);
    }
}


