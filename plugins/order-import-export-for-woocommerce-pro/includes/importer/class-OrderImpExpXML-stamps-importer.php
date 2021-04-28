<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_StampsImporter {

    public function wf_order_xml_stamps_import_format($item) 
    {
        global $WF_Stamps_XML_Order_Import, $wpdb;
        $postmeta = $default_stamps_order_meta = $order = $order_meta_data = array();
        
        $default_stamps_order_meta['TrackingNumber'] = (string)$item->TrackingNumber;
        $default_stamps_order_meta['ActualMailingDate'] = (string)$item->ActualMailingDate;
        $default_stamps_order_meta['DesiredMailingDate'] = (string)$item->DesiredMailingDate;
        $default_stamps_order_meta['HidePostageAmount'] = (string)$item->HidePostageAmount;
        $default_stamps_order_meta['MailClass'] = (string)$item->MailClass;
        $default_stamps_order_meta['Mailpiece'] = (string)$item->Mailpiece;
        $default_stamps_order_meta['ShipMethod'] = (string)$item->ShipMethod;
        $default_stamps_order_meta['PostageCost MailClass'] = (string)$item->PostageCost->MailClass[0];
        $default_stamps_order_meta['PostageCost Total'] = (string)$item->PostageCost->Total[0];
        $default_stamps_order_meta['TrackingService'] = (string)$item->Services->TrackingService[0];
        $default_stamps_order_meta['TrackingNumber'] = (string)$item->TrackingNumber;
        $default_stamps_order_meta['WeightOz'] = (string)$item->WeightOz;
        
        
        //apply filter if any alteration for meta names and values or filed to be done
        $default_stamps_order_meta = apply_filters('hf_alter_stamps_order_meta',$default_stamps_order_meta , $item );
        $order['order_number'] = (string)$item->OrderID;
        if ($order['order_number']) {
           $order['postmeta'] =  $default_stamps_order_meta;
           $results[] = $order;
        }
        return $results;
    }

    
          
}


