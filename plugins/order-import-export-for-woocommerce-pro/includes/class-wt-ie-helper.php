<?php

/* 
 * Common Helper functions
 */

/**
 * Helper class which contains common functions for both import and export.
 */
class WT_ie_helper {

    
	/**
	* Get product id by sku.
	* @param string $sku SKU of the product.
	* @return int|bool Product id or zero.
	*/

	function xa_wc_get_product_id_by_sku( $sku ) {
            global $wpdb;
            $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
            return ( $product_id ) ? intval( $product_id ) : 0;
	}
	
	/**
	 * Get File name by url
	 * @param string $file_url URL of the file.
	 * @return string the base name of the given URL (File name).
	 */
	function xa_wc_get_filename_from_url( $file_url ) {
	    $parts = parse_url( $file_url );
	    if ( isset( $parts['path'] ) ) {
		return basename( $parts['path'] );
	    }
	}

}
