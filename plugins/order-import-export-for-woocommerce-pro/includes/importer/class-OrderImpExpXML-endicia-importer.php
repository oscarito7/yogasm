<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_EndiciaImporter {

    public function wf_order_xml_endicia_import_format($item) 
    {
        $default_fedex_order_meta['Account']                    = (string)$item->Account;
        $default_fedex_order_meta['DeviceID']                   = (string)$item->DeviceID;
        $default_fedex_order_meta['AccountName']                = (string)$item->AccountName;
        $default_fedex_order_meta['TransactionID']              = (string)$item->TransactionID;
        $default_fedex_order_meta['TransactionDateTime']        = (string)$item->TransactionDateTime;
        $default_fedex_order_meta['TransactionType']            = (string)$item->TransactionType;
        $default_fedex_order_meta['AccountBalance']             = (string)$item->AccountBalance;
        $default_fedex_order_meta['PostmarkDateTime']           = (string)$item->PostmarkDateTime;
        $default_fedex_order_meta['FinalPostage']               = (string)$item->FinalPostage;
        $default_fedex_order_meta['MailClass']                  = (string)$item->MailClass;
        $default_fedex_order_meta['TrackingNumber']             = (string)$item->PIC;
        $default_fedex_order_meta['WeightOz']                   = (string)$item->WeightOz;
        $default_fedex_order_meta['Records Description']        = (string)$item->Description;
        $default_fedex_order_meta['Records Value']              = (string)$item->Value;
        $default_fedex_order_meta['CostCenter']                 = (string)$item->CostCenter;
        $default_fedex_order_meta['Insurance']                  = (string)$item->Insurance;
        $default_fedex_order_meta['ReplyPostage']               = (string)$item->ReplyPostage;
        $default_fedex_order_meta['Reprinted']                  = (string)$item->Reprinted;
        $default_fedex_order_meta['CustomsContentType']         = (string)$item->CustomsContentType;
        $default_fedex_order_meta['Services USPSTracking']      = (string)$item->Services['USPSTracking'];
        
        $order['order_number'] = (string)$item['ID'];
        if ($order['order_number'])
        {
           $order['postmeta'] =  $default_fedex_order_meta;
           $results[] = $order;
        }
        return $results;
    }

    
          
}


