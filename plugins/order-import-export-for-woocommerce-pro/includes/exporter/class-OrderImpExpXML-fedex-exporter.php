<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_FedexExporter {

    public function generate_xml_fedex($order_ids){
        
        include_once( 'class-OrderImpExpXML-order-exp-xml-general.php' );
        $export = new OrderImpExpXML_OrderExpXMLGeneral($order_ids);
        $order_details = $export->get_orders($order_ids);
        $data_array = array('Orders' => array('Order' => $order_details));
        
        $data_array = OrderImpExpXML_FedexExporter::wf_order_xml_fedex_export_format($data_array, $order_details);
        foreach($data_array as $id => $data) {
            $open_shipments_array [] = $data;
        }
        $order_data_array = array(
            'Print' => $open_shipments_array
        );
        $filename='order_fedex_xml';
        $xmlns = 'http://www.fedex.com/fsmapi';
        $dt = new DateTime();
        $export->do_xml_export($filename, $export->get_order_details_xml($order_data_array, $xmlns));
        return $xmlns;
    }
    public function wf_order_xml_fedex_export_format($formated_orders, $raw_orders) {
        $order_details = array();

        foreach ($raw_orders as $order) {
            $order_data = array(
                'Item' => array(
                    'RequestHeader' => array(
                        'CustomerTransactionIdentifier' => 'US Ship', 
                        'AccountNumber' => 12356789, 
                        'MeterNumber' => 1234567, 
                        'CarrierCode' => 'FDXE', 
                    ),
                    'ShipDate' => $order['OrderDate'],
                    'ShipTime' => '',
                    'DropoffType' => 'REGULARPICKUP',
                    'Service' => 'PRIORITYOVERNIGHT' ,
                    'Packaging' => 'FEDEXBOC\X', 
                    'WeightUnits' => $order['OrderLineItems']['weight_unit'],
                    'Weight' => $order['OrderLineItems']['total_weight'],
                    'Origin' => array( 
                        'Contact' => array(
                            'PersonName'=> get_user_meta(1, 'billing_first_name', true).' '.get_user_meta(1, 'billing_last_name', true) ,
                            'CompanyName' => get_user_meta(1, 'billing_company', true),
                            'Department' => '',
                            'PhoneNumber' => get_user_meta(1, 'billing_phone', true),
                            'PagerNumber' => '',
                            'FaxNumber' => '',
                            'E-MailAddress' => get_option('admin_email'),
                        ),
                        'Address' => array(
                            'Line1' => get_user_meta(1, 'billing_address_1', true),
                            'Line2' => get_user_meta(1, 'billing_address_2', true),
                            'City' => get_user_meta(1, 'billing_city', true) ,
                            'StateOrProvinceCode' => get_user_meta(1, 'billing_state', true) ,
                            'PostalCode' => get_user_meta(1, 'billing_postcode', true) ,
                            'CountryCode'=> get_user_meta(1, 'billing_country', true)
                        )
                    ),
                    'Destination' => array(
                        'Contact' => array(
                            'PersonName'=> $order['ShippingFullName'],
                            'CompanyName' => $order['ShippingCompany'],
                            'Department' => '',
                            'PhoneNumber' => $order['ShippingFullName'],
                            'PagerNumber' => $order['BillingPhone'],
                            'FaxNumber' => '',
                            'E-MailAddress' => $order['BillingEmail'],
                        ),
                        'Address' => array(
                            'Line1' => $order['ShippingAddress1'],
                            'Line2' => $order['ShippingAddress2'],
                            'City' => $order['ShippingCity'],
                            'StateOrProvinceCode' => $order['ShippingState'],
                            'PostalCode' => $order['ShippingPostCode'],
                            'CountryCode'=> $order['ShippingCountry']
                        )
                    ),
                    'Payment' => array(
                        'PayorType' => 'SENDER'
                    ),
                    'ReferenceInfo' => array(
                        'CustomerReference' => 'OrderId: '.$order['OrderId']." CustomerId: ".$order['CustomerId'], 
                    ),
                    'Label' => array(
                        'Type' => '2DCOMMON',
                        'ImageType'=>'PNG'
                    ),
                )
            );
           $order_details[] = $order_data;
        }
        return apply_filters('hf_fedex_order_export',$order_details);
    }
}


