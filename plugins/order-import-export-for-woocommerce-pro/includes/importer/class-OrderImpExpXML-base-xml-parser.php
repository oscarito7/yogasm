<?php
/**
 * WooCommerce XML Importer class for managing parsing of XML files.
 */
class OrderImpExpXML_Parser {

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
    public function __construct($post_type = 'shop_order') {
        $this->post_type = $post_type;
        
    }

    /**
     * Parse the data
     * @param  string  $file      [description]
     * @return array
     */
    public function parse_data($file) {
        // Set locale
        $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
        if ($enc)
            setlocale(LC_ALL, 'en_US.' . $enc);
        @ini_set('auto_detect_line_endings', true);

        $xml = simplexml_load_file($file);
        $root_tag = $xml->getName();
        $xml_array = array();
        $xml_array[$root_tag] = $xml;
        return array($xml_array);
    }

    /**
     * Parse orders
     * @param  array  $item
     * @param  integer $merge_empty_cells
     * @return array
     */
    public function parse_orders($item,$import_type) {

        global $WF_XML_Order_Import, $wpdb;
        $postmeta = $default_order_meta = $order = $order_meta_data = array();
        switch($import_type){
            case 'general':
                if ( ! class_exists( 'OrderImpExpXML_GeneralCaseImporter' ) )
                    include_once 'class-OrderImpExpXML-general-case-importer.php' ;
                require_once(dirname(__DIR__) . '/class-wt-ie-helper.php');
                $general_import_obj = new OrderImpExpXML_GeneralCaseImporter();
                $results = $general_import_obj->wf_order_xml_general_case_import_format($item);
                break;
            case 'stamps':
                if ( ! class_exists( 'OrderImpExpXML_StampsImporter' ) )
                    include_once 'class-OrderImpExpXML-stamps-importer.php' ;
                $general_import_obj = new OrderImpExpXML_StampsImporter();
                $results = $general_import_obj->wf_order_xml_stamps_import_format($item);
                break;
            case 'fedex':
                if ( ! class_exists( 'OrderImpExpXML_FedexImporter' ) )
                    include_once 'class-OrderImpExpXML-fedex-importer.php' ;
                $general_import_obj = new OrderImpExpXML_FedexImporter();
                $results = $general_import_obj->wf_order_xml_fedex_import_format($item);
                break;
            case 'endicia':
                if ( ! class_exists( 'OrderImpExpXML_EndiciaImporter' ) )
                    include_once 'class-OrderImpExpXML-endicia-importer.php' ;
                $general_import_obj = new OrderImpExpXML_EndiciaImporter();
                $results = $general_import_obj->wf_order_xml_endicia_import_format($item);
                break;
            case 'ups':
                if ( ! class_exists( 'OrderImpExpXML_UpsImporter' ) )
                    include_once 'class-OrderImpExpXML-ups-importer.php' ;
                $general_import_obj = new OrderImpExpXML_UpsImporter();
                $results = $general_import_obj->wf_order_xml_ups_import_format($item);
                break;
        }
        // Result
        return array( $this->post_type => $results );
    }

}
