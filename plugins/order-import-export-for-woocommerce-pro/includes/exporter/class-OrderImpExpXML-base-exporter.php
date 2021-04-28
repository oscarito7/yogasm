<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXMLBase_Exporter {
    
    public $include_hidden_meta = 0;
    
    /**
     * Order Exporter Tool
     */
    public function do_export($post_type = 'shop_order', $order_IDS = array()) {
        global $wpdb;
        $export_limit = !empty($_POST['limit']) ? absint($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $export_offset = !empty($_POST['offset']) ? absint($_POST['offset']) : 0;
        $this->include_hidden_meta = !empty($_POST['include_xml_meta']) ? true : false;

        if(!empty($_GET['method']))
            $_POST['order_export_type'] = sanitize_text_field ($_GET['method']);

        $export_format = !empty($_POST['order_export_type']) ? sanitize_text_field($_POST['order_export_type']): 'general';

        $export_order_statuses = !empty($_POST['order_status']) ? wc_clean($_POST['order_status']) : 'any';
        $products = !empty($_POST['products']) ? array_map('intval',$_POST['products']) : array();
        $coupons = !empty($_POST['coupons']) ? explode(',',trim(strtolower(wc_clean($_POST['coupons'])))) : array();
        $end_date   = empty($_POST['end_date']) ? date('Y-m-d 23:59', current_time('timestamp')) : sanitize_text_field($_POST['end_date']) . ' 23:59:59.99';
        $start_date = empty($_POST['start_date']) ? date('Y-m-d 00:00', 0) : sanitize_text_field($_POST['start_date']);
        $delimiter  = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ','; // WPCS: CSRF ok, input var ok.
        $exclude_already_exported = !empty($_POST['exclude_already_exported']) ? true : false;


        if ($limit > $export_limit)
            $limit = $export_limit;


        // Headers
        global $order_ids;
        if (empty($order_IDS)) {
            if(!empty($products) && empty($coupons)){
                $order_ids = $this->wt_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date);
            } elseif (!empty($coupons) && empty($products)) {
                $order_ids = $this->wt_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date);
            } elseif (!empty($coupons) && !empty($products)) {
                $ord_prods = $this->wt_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date);
                $ord_coups = $this->wt_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date);
                $order_ids = array_intersect($ord_prods, $ord_coups);
            } else {
                $query_args = array(
                    'fields' => 'ids',
                    'post_type' => 'shop_order',
                    'post_status' => $export_order_statuses,
                    'posts_per_page' => $export_limit,
                    'offset' => $export_offset,
                    'date_query' => array(
                        array(
                            'before' => $end_date,
                            'after' => $start_date,
                            'inclusive' => true,
                        ),
                    ),
                );
                if ($exclude_already_exported) {
                    $query_args['meta_query'] = array(array(
                        'key' => 'wf_order_exported_status',
                        'value' => FALSE,
                        'compare' => 'NOT EXISTS',
                    ));
                }

               $query = new WP_Query($query_args);
               $order_ids = $query->posts;
            }
        } else {
            $order_ids = $order_IDS;
        }

        $filename = 'order_';
        $xmlns = '';
        $xmlns = $this->export_formation($export_format, $order_ids);
        die();
    }
    
    public function export_formation($export_format,$order_ids){
        switch ($export_format) {
            case 'stamps':
                if ( ! class_exists( 'OrderImpExpXML_StampsExporter' ) )
                    include_once 'class-OrderImpExpXML-stamps-exporter.php' ;
                $general_exporter_obj = new OrderImpExpXML_StampsExporter();
                $general_exporter_obj->generate_xml_stamps($order_ids);
                break;
            case 'general' :
                if ( ! class_exists( 'OrderImpExpXML_GeneralCaseExporter' ) )
                    include_once 'class-OrderImpExpXML-general-case-exporter.php' ;
                $general_exporter_obj = new OrderImpExpXML_GeneralCaseExporter();
                $general_exporter_obj->generate_xml_general_case($order_ids,$this->include_hidden_meta);
                break;
            case 'fedex' :
               if ( ! class_exists( 'OrderImpExpXML_FedexExporter' ) )
                    include_once 'class-OrderImpExpXML-fedex-exporter.php' ;
                $fedex_exporter_obj = new OrderImpExpXML_FedexExporter();
                $fedex_exporter_obj->generate_xml_fedex($order_ids);
                break;
            case 'ups' :
                if ( ! class_exists( 'OrderImpExpXML_UPSExporter' ) )
                    include_once 'class-OrderImpExpXML-ups-exporter.php' ;
                $ups_exporter_obj = new OrderImpExpXML_UPSExporter();
                $ups_exporter_obj->generate_xml_ups($order_ids);
                break;
            case 'endicia' :
                if ( ! class_exists( 'OrderImpExpXML_EndiciaExporter' ) )
                    include_once 'class-OrderImpExpXML-endicia-exporter.php' ;
                $endicia_exporter_obj = new OrderImpExpXML_EndiciaExporter();
                $endicia_exporter_obj->generate_xml_endicia($order_ids);
                break;
        }
    }
    
    public function wt_get_orders_of_products($products,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date){
        global $wpdb;
        
        $query = "SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
            LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
            WHERE po.post_type = 'shop_order'
            AND oi.order_item_type = 'line_item'
            AND om.meta_key IN ('_product_id','_variation_id')
            AND om.meta_value IN ('". implode("','", $products) ."')
            AND (po.post_date BETWEEN '$start_date' AND '$end_date')";
            if($export_order_statuses != 'any'){
                $query .= " AND po.post_status IN ( '" . implode("','", $export_order_statuses) . "' )";
            } 
            $query .= " LIMIT ".intval($export_limit).' '. (!empty($export_offset)? 'OFFSET '.intval($export_offset):'') ;
  
        $order_ids = $wpdb->get_col($query);
  
        return $order_ids;
    }
    
    public function wt_get_orders_of_coupons($coupons,$export_order_statuses,$export_limit,$export_offset,$end_date,$start_date){
        global $wpdb;
        
        $query = "SELECT DISTINCT po.ID FROM {$wpdb->posts} AS po
            LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON oi.order_id = po.ID
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON om.order_item_id = oi.order_item_id
            WHERE po.post_type = 'shop_order'
            AND oi.order_item_type = 'coupon'
            AND oi.order_item_name IN ('". implode("','", $coupons) ."')
            AND (po.post_date BETWEEN '$start_date' AND '$end_date')";
            if($export_order_statuses != 'any'){
                $query .= " AND po.post_status IN ( '" . implode("','", $export_order_statuses) . "' )";
            } 
            $query .= " LIMIT ".intval($export_limit).' '. (!empty($export_offset)? 'OFFSET '.intval($export_offset):'') ;
  
        $order_ids = $wpdb->get_col($query);
  
        return $order_ids;
    }
}


