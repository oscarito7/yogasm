<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_UpsImporter {

    public function wf_order_xml_ups_import_format($item) 
    {
        $default_fedex_order_meta['CustomerTransactionIdentifier']  = (string)$item['FDXRateReply']->ReplyHeader->CustomerTransactionIdentifier;
        $default_fedex_order_meta['DimWeightUsed']                  = (string)$item['FDXRateReply']->EstimatedCharges->DimWeightUsed;
        $default_fedex_order_meta['RateScale']                      = (string)$item['FDXRateReply']->EstimatedCharges->RateScale;
        $default_fedex_order_meta['RateZone']                       = (string)$item['FDXRateReply']->EstimatedCharges->RateZone;
        $default_fedex_order_meta['BilledWeight']                   = (string)$item['FDXRateReply']->EstimatedCharges->BilledWeight;
        $default_fedex_order_meta['BaseCharge']                     = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->BaseCharge;
        $default_fedex_order_meta['TotalDiscount']                  = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->TotalDiscount;
        $default_fedex_order_meta['Surcharges Fuel']                = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->Surcharges->Fuel;
        $default_fedex_order_meta['Surcharges Other']               = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->Surcharges->Other;
        $default_fedex_order_meta['TotalSurcharge']                 = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->TotalSurcharge;
        $default_fedex_order_meta['NetCharge']                      = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->NetCharge;
        $default_fedex_order_meta['TotalRebate']                    = (string)$item['FDXRateReply']->EstimatedCharges->DiscountedCharges->NetCharge;
        $order['order_number'] = (string)$item['FDXRateReply']->OrderID;
        if ($order['order_number'])
        {
           $order['postmeta'] =  $default_fedex_order_meta;
           $results[] = $order;
        }
        return $results;
    }

    
          
}


