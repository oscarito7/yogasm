<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_UPSExporter {

    public function generate_xml_ups($order_ids) {

        include_once( 'class-OrderImpExpXML-order-exp-xml-general.php' );
        $export = new OrderImpExpXML_OrderExpXMLGeneral($order_ids);
        $order_details = $export->get_orders($order_ids);
        $data_array = array('Orders' => array('Order' => $order_details));
        $data_array = OrderImpExpXML_UPSExporter::wf_order_xml_ups_export_format($data_array, $order_details);
        $order_data_array = array(
            'Shipments' => $data_array
        );

        $filename = 'order_ups_xml';
        $xmlns = 'http://www.ups.com/XMLSchema/CT/WorldShip/ImpExp/ShipmentImport/v1_0_0';
        $dt = new DateTime();
        $modified_details = apply_filters('wt_ups_order_pre_export_data', $order_data_array, $xmlns);
        if (!empty($modified_details['order_data_array']))
            $order_data_array = $modified_details['order_data_array'];
        if (!empty($modified_details['xmlns']))
            $xmlns = $modified_details['xmlns'];
        $export->do_xml_export($filename, $export->get_order_details_xml($order_data_array, $xmlns));
        return $xmlns;
    }

    public function wf_order_xml_ups_export_format($formated_orders, $raw_orders) {
        $order_details = array();
        foreach ($raw_orders as $order) {
            $item = '';
            foreach ($order['OrderLineItems'] as $order_items) {
                if (isset($order_items['Name']) && $order_items['Name'] != '') {
                    if ($item == '') {
                        $item .= $order_items['Name'];
                    } else {
                        $item .= ', ' . $order_items['Name'];
                    }
                }
            }
            if ($order['StoreCountry'] == $order['ShippingCountry']) {
                $ShipmentOption = 'SC';
            } else {
                $ShipmentOption = '';
            }

            $order_data = array(
                'Shipment' => array(
                    '@attributes' => array(
                    //'ShipmentOption' => $ShipmentOption,
                    //'ProcessStatus' => ''
                    ),
                    'ShipmentKey' => $order['OrderId'],
                    'ShipTo' => array(
                        'CompanyOrName' => !empty($order['ShippingCompany']) ? $order['ShippingCompany'] : $order['ShippingFirstName'],
                        'Attention' => $order['ShippingFirstName']. ' '.$order['ShippingLastName'],
                        'Address1' => $order['ShippingAddress1'],
                        'Address2' => $order['ShippingAddress2'],
                        'CountryTerritory' => $order['ShippingCountry'],
                        'CityOrTown' => $order['ShippingCity'],
                        'StateProvinceCounty' => $order['ShippingState'],
                        'PostalCode' => $order['ShippingPostCode'],
                        'Telephone' => $order['BillingPhone'],
                        'EmailAddress' => $order['BillingEmail']
                    ),
                    'ShipFrom' => array(
                        'CompanyOrName' => get_user_meta(1, 'billing_company', true),
                        'Attention' => 'Sender',
                        'Address1' => get_user_meta(1, 'billing_address_1', true),
                        'CountryTerritory' => get_user_meta(1, 'billing_country', true),
                        'CityOrTown' => get_user_meta(1, 'billing_city', true),
                        'StateProvinceCounty' => get_user_meta(1, 'billing_state', true),
                        'PostalCode' => get_user_meta(1, 'billing_postcode', true),
                        'Telephone' => get_user_meta(1, 'billing_phone', true),
                        'EmailAddress' => get_option('admin_email')
                    ),
                    'ShipmentInformation' => array(
                        'ServiceType' => ($order['ShippingMethod'])?$order['ShippingMethod']:'GND',
                        'DescriptionOfGoods' => $item,
                        'GoodsNotInFreeCirculation' => 0,
                        'BillTransportationTo' => 'Shipper'
                    ),
                    'Packages' => array(
                        'Package' => array(
                                'PackageType' => 'CP',
                                'Weight' => $order['OrderLineItems']['total_weight'],
                                'Length' => $order['OrderLineItems']['total_length'],
                                'Width' => $order['OrderLineItems']['total_width'],
                                'Height' => $order['OrderLineItems']['total_height'],
                                'ReferenceNumbers' => array('Reference1' => 'OrderId:' . $order['OrderId'] . ' CustId:' . $order['CustomerId']),
                        ) 
                    ),
                ),
            );
            $order_details[] = $order_data;
        }
        return apply_filters('hf_ups_order_export', $order_details);
    }

}