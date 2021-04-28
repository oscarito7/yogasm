<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_StampsExporter {

    public function generate_xml_stamps($order_ids){
        
        include_once( 'class-OrderImpExpXML-order-exp-xml-general.php' );
        $export = new OrderImpExpXML_OrderExpXMLGeneral($order_ids);
        $order_details = $export->get_orders($order_ids);
        $data_array = array('Orders' => array('Order' => $order_details));
        $data_array = OrderImpExpXML_StampsExporter::wf_order_xml_stamps_export_format($data_array, $order_details);

        foreach($data_array as $id => $data) {
            $open_shipments_array [] = $data;
        }
        $order_data_array = array(
            'Print' => $open_shipments_array
        );
        $filename='order_stamps_xml';
        $xmlns = 'http://stamps.com/xml/namespace/2009/8/Client/BatchProcessingV1';
        $export->do_xml_export($filename, $export->get_order_details_xml($order_data_array, $xmlns));
        return $xmlns;
    }
    public function wf_order_xml_stamps_export_format($formated_orders, $raw_orders) {
        $order_details = array();
        foreach ($raw_orders as $order) {
            if ($order['StoreCountry'] == $order['ShippingCountry']) {
                $order_data = array('Item' => array(
                    'OrderDate' => $order['OrderDate'],
                    'OrderID' => $order['OrderId'],
                    'ShipMethod' => $order['ShippingMethod'],
                    'MailClass' => 'first class',
                    'Mailpiece' => 'package',
                    'DeclaredValue' => $order['OrderTotal'],
                    'Recipient' => array(
                        'AddressFields' => array(
                            'FirstName' => $order['ShippingFirstName'],
                            'LastName' => $order['ShippingLastName'],
                            'Address1' => $order['ShippingAddress1'],
                            'Address2' => $order['ShippingAddress2'],
                            'Company' => $order['ShippingCompany'],
                            'City' => $order['ShippingCity'],
                            'State' => $order['ShippingState'],
                            'ZIP' => $order['ShippingPostCode'],
                            'Country' => $order['ShippingCountry'],
                            'OrderedPhoneNumbers' => array(
                                'Number' => $order['BillingPhone']
                            ),
                            'OrderedEmailAddresses' => array(
                                'Address' => $order['BillingEmail']
                            )
                        ),
                    ),
                    'WeightOz' => $order['OrderLineItems']['total_weight'],
                    'RecipientEmailOptions' => array(
                        'ShipmentNotification' => 'false',
                    ),)
                );
            }
            else
            {
                $order_data = array('Item' => array(
                    'OrderDate' => $order['OrderDate'],
                    'OrderID' => $order['OrderId'],
                    'Recipient' => array(
                        'AddressFields' => array(
                            'FirstName' => $order['ShippingFirstName'],
                            'LastName' => $order['ShippingLastName'],
                            'Address1' => $order['ShippingAddress1'],
                            'Address2' => $order['ShippingAddress2'],
                            'Company' => $order['ShippingCompany'],
                            'City' => $order['ShippingCity'],
                            'State' => $order['ShippingState'],
                            'ZIP' => $order['ShippingPostCode'],
                            'Country' => $order['ShippingCountry'],
                            'OrderedPhoneNumbers' => array(
                                'Number' => $order['BillingPhone']
                            ),
                            'OrderedEmailAddresses' => array(
                                'Address' => $order['BillingEmail']
                            )
                        ),
                    ),
                    'WeightOz' => $order['OrderLineItems']['total_weight'],
                    'RecipientEmailOptions' => array(
                        'ShipmentNotification' => 'false',
                    ),
                    'CustomsInfo' => array(
                        'Contents' => array(
                            'Item' => array(
                                'Description' => 'HF' . $order['OrderId'],
                                'Quantity' => $order['OrderLineItems']['total_qty'],
                                 'Value' => $order['OrderTotal'],
                                 'WeightOz' => $order['OrderLineItems']['total_weight']
                            )
                        ),
                        'ContentsType' => 'other',
                        'DeclaredValue' => $order['OrderTotal'],
                        'UserAcknowledged' => TRUE

                    ),)
                );
            }
            
            if (count($order['OrderLineItems']) >=7 ) {
                unset($order['OrderLineItems']['total_weight']);
                unset($order['OrderLineItems']['total_qty']);
                unset($order['OrderLineItems']['weight_unit']);
                unset($order['OrderLineItems']['total_height']);
                unset($order['OrderLineItems']['total_width']);
                unset($order['OrderLineItems']['total_length']);
                $count = 0;

                foreach ($order['OrderLineItems'] as $lineItems) {
                    if(!empty($lineItems)){
                        $count++;
                        foreach ($lineItems as $key => $value) {
                            if('ExternalID' === $key){
                             $order_data['Item']['OrderContents'][$count]['Item'][$key] = $value;
                            }elseif ('Name' === $key){
                             $order_data['Item']['OrderContents'][$count]['Item'][$key] = $value;
                            }elseif('Price' === $key){
                             $order_data['Item']['OrderContents'][$count]['Item'][$key] = $value;
                            }elseif('Quantity' === $key){
                             $order_data['Item']['OrderContents'][$count]['Item'][$key] = $value;
                            }elseif('Total' === $key){
                             $order_data['Item']['OrderContents'][$count]['Item'][$key] = $value;
                            }
                        }
                    }
                }
            }
            $order_details[] = $order_data;
        }
       // $formated_orders = array('Print' => array('Item' => $order_details));
        return apply_filters('hf_stamps_order_export', $order_details);
    }
}