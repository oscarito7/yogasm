<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_subcription_orderImpExpCsv_System_Status_Tools {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_debug_tools', array( $this, 'tools' ) );
	}

	/**
	 * Tools we add to WC
	 * @param  array $tools
	 * @return array
	 */
	public function tools( $tools ) {
		$tools['delete_trashed_subscription_orders'] = array(
			'name'		=> __( 'Delete Trashed Subscription Orders','wf_order_import_export'),
			'button'	=> __( 'Delete  Trashed Subscription Orders','wf_order_import_export' ),
			'desc'		=> __( 'This tool will delete all  Trashed Subscription Orders.', 'wf_order_import_export' ),
			'callback'  => array( $this, 'delete_trashed_subscription_orders' )
		);
		$tools['delete_all_subscription_orders'] = array(
			'name'		=> __( 'Delete Subscription Orders','wf_order_import_export'),
			'button'	=> __( 'Delete ALL Subscription Orders','wf_order_import_export' ),
			'desc'		=> __( 'This tool will delete all subscription orders allowing you to start fresh.', 'wf_order_import_export' ),
			'callback'  => array( $this, 'delete_all_subscription_orders' )
		);
		return $tools;
	}

	/**
	 * Delete Trashed Subscription Orders
	 */
	public function delete_trashed_subscription_orders() {
		global $wpdb;
		// Delete Trashed Orders
		$result  = absint( $wpdb->delete( $wpdb->posts, array( 'post_type' => 'shop_subscription' , 'post_status' => 'trash') ) );
                
                // Delete meta and term relationships with no post
		$wpdb->query( "DELETE pm
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} wp ON wp.ID = pm.post_id
			WHERE wp.ID IS NULL" );        
                // Delete order items with no post
                $wpdb->query( "DELETE oi
                        FROM {$wpdb->prefix}woocommerce_order_items oi
                        LEFT JOIN {$wpdb->posts} wp ON wp.ID = oi.order_id
                        WHERE wp.ID IS NULL" );        
                // Delete order item meta with no post
                $wpdb->query( "DELETE om
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta om
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi ON oi.order_item_id = om.order_item_id
                        WHERE oi.order_item_id IS NULL" );
		echo '<div class="updated"><p>' . sprintf( __( '%d Subscription Orders Deleted', 'wf_order_import_export' ), ( $result ) ) . '</p></div>';
	}

	/**
	 * Delete all Subscription Orders
	 */
	public function delete_all_subscription_orders() {
		global $wpdb;

		// Delete Subscription Orders
		$result = absint( $wpdb->delete( $wpdb->posts, array( 'post_type' => 'shop_subscription' ) ) );

		// Delete meta and term relationships with no post
		$wpdb->query( "DELETE pm
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} wp ON wp.ID = pm.post_id
			WHERE wp.ID IS NULL" );
                // Delete order items with no post
                $wpdb->query( "DELETE oi
                        FROM {$wpdb->prefix}woocommerce_order_items oi
                        LEFT JOIN {$wpdb->posts} wp ON wp.ID = oi.order_id
                        WHERE wp.ID IS NULL" );        
                // Delete order item meta with no post
                $wpdb->query( "DELETE om
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta om
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi ON oi.order_item_id = om.order_item_id
                        WHERE oi.order_item_id IS NULL" );
		echo '<div class="updated"><p>' . sprintf( __( '%d Subscription Orders Deleted', 'wf_order_import_export' ), $result ) . '</p></div>';
	}	
}

new wf_subcription_orderImpExpCsv_System_Status_Tools();