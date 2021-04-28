<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OrderImpExpXML_AJAXHandler {

    /**
     * Constructor
     */
    public function __construct() {
            add_action( 'wp_ajax_woocommerce_xml_order_import_request', array( $this, 'wf_xml_order_import_request' ) );
    }

    /**
     * Ajax event for importing a XML
     */
    public function wf_xml_order_import_request() {
            define( 'WP_LOAD_IMPORTERS', true );
            OrderImpExpXML_Importer::order_importer();
    }
}

new OrderImpExpXML_AJAXHandler();