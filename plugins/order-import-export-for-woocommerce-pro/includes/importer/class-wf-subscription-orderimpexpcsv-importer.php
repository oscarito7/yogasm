<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_subcription_orderImpExpCsv_Importer {

	/**
	 * Order Exporter Tool
	 */
	public static function load_wp_importer() {
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) ) {
				require $class_wp_importer;
			}
		}
	}

	/**
	 * Order Importer Tool
	 */
	public static function subscription_order_importer() {
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			return;
		}

		self::load_wp_importer();

		// includes
		require_once 'class-wf-subscription-orderimpexpcsv-order-import.php';
		require_once 'class-wf-csv-subscription-parser.php';

		// Dispatch
		$GLOBALS['WF_CSV_Subscription_Order_Import'] = new wf_subcription_orderImpExpCsv_Order_Import();
		$GLOBALS['WF_CSV_Subscription_Order_Import'] ->dispatch();
	}	
}