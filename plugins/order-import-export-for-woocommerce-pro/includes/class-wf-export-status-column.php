<?php

if (!defined('ABSPATH')) {
    return;
}

if (!class_exists('OrderImpExpFlag_List')) {

    class OrderImpExpFlag_List {

        /**
         * Constructor
         */
        public function __construct() {
            add_action('load-edit.php', array($this, 'orderList_load'));
        }

        function orderList_load() {
            $screen = get_current_screen();
            if (!isset($screen->post_type) || 'shop_order' != $screen->post_type) {
                return;
            }

            add_filter("manage_{$screen->id}_columns", array($this, 'orderList_add_columns'));
            add_action("manage_{$screen->post_type}_posts_custom_column", array($this, 'orderList_column_cb'), 10, 2);
        }

        function orderList_add_columns($cols) {
            $cols['exported'] = __('Exported', 'wf_order_import_export');
            return $cols;
        }

        function orderList_column_cb($col, $post_id) {
            if ('exported' == $col) {
                $exported_status = get_post_meta($post_id, 'wf_order_exported_status');
                if (TRUE==$exported_status) {
                 echo '<span class="dashicons dashicons-yes"></span>';   
                } else {
                 echo '<span class="dashicons dashicons-no-alt"></span>';  
                }
            }
        }

    }

}
new OrderImpExpFlag_List();
