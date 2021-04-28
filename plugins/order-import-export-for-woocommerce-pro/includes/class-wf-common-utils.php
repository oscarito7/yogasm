<?php

if (!defined('WPINC')) {
    die();
}

class WF_OrderImpExpCsv_Common_Utils {

    public static function is_woocommerce_prior_to($version) {

        $woocommerce_is_pre_version = (!defined('WC_VERSION') || version_compare(WC_VERSION, $version, '<')) ? true : false;
        return $woocommerce_is_pre_version;
    }
    
    /**
     * Get WC Plugin path without fail on any version
     */
    public static function hf_get_wc_path() {
        if (function_exists('WC')) {
            $wc_path = WC()->plugin_url();
        } else {
            $wc_path = plugins_url() . '/woocommerce';
        }
        return $wc_path;
    }

}
