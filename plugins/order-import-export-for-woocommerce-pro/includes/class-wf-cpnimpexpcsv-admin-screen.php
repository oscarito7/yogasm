<?php
if (!defined('ABSPATH')) {
    exit;
}

class WF_CpnImpExpCsv_Admin_Screen {

    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_print_styles', array($this, 'admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_footer-edit.php', array($this, 'add_coupons_bulk_actions'));
        add_action('load-edit.php', array($this, 'process_coupons_bulk_actions'));
        add_filter('manage_edit-shop_coupon_columns', array($this, 'coupon_export_column'));
        add_action('manage_shop_coupon_posts_custom_column', array($this, 'coupon_export_column_value'), 2);

        if (is_admin()) {
            add_action('wp_ajax_coupon_export_csv_single', array($this, 'process_ajax_export_single_coupon'));
        }
    }

    public function admin_notices() {
        if (!function_exists('mb_detect_encoding')) {
            echo '<div class="error"><p>' . __('Coupon CSV Import Export requires the function <code>mb_detect_encoding</code> to import and export CSV files. Please ask your hosting provider to enable this function.', 'wf_order_import_export') . '</p></div>';
        }
    }

    public function admin_menu() {
        $page = add_submenu_page('woocommerce', __('Coupon Im-Ex', 'wf_order_import_export'), __('Coupon Im-Ex', 'wf_order_import_export'), apply_filters('coupon_csv_coupon_role', 'manage_woocommerce'), 'wf_coupon_csv_im_ex', array($this, 'output'));
    }

    public function admin_scripts() {
        $wc_path = WF_OrderImpExpCsv_Common_Utils::hf_get_wc_path();
        $screen = get_current_screen();
        $allowed_creen_id = array('wf_woocommerce_order_im_ex_xml', 'wf_woocommerce_order_im_ex_xml',
            'wf_woocommerce_subscription_order_im_ex', 'woocommerce_page_wf_coupon_csv_im_ex', 'wf_woocommerce_subscription_order_im_ex',
            'wf_woocommerce_order_im_ex', 'edit-shop_order',
            'admin',
            'edit-shop_subscription', 'edit-shop_coupon',
            'woocommerce_page_wf_woocommerce_order_im_ex'
        );

        if (in_array($screen->id, $allowed_creen_id)) {
            wp_enqueue_style('woocommerce_admin_styles', $wc_path . '/assets/css/admin.css');
            wp_enqueue_style('woocommerce-coupon-csv-importer1', plugins_url(basename(plugin_dir_path(WF_CpnImpExpCsv_FILE)) . '/styles/wf-style.css', basename(__FILE__)), '', '1.0.0', 'screen');
            wp_enqueue_style('woocommerce-coupon-csv-importer2', plugins_url(basename(plugin_dir_path(WF_CpnImpExpCsv_FILE)) . '/styles/jquery-ui.css', basename(__FILE__)), '', '1.0.0', 'screen');
            wp_enqueue_script('woocommerce-coupon-csv-importer3', plugins_url(basename(plugin_dir_path(WF_CpnImpExpCsv_FILE)) . '/js/coupon-csv-import-export-for-woocommerce.min.js', basename(__FILE__)), '', '1.0.0', 'screen');
        }

        wp_enqueue_script('wc-enhanced-select');
        wp_localize_script('woocommerce-coupon-csv-importer3', 'woocommerce_coupon_csv_cron_params', array('cpn_auto_export' => 'Disabled', 'cpn_auto_import' => 'Disabled'));
        wp_enqueue_script('jquery-ui-datepicker');
    }

    public function output() {
        $tab = 'import';

        if (!empty($_GET['page'])) {
            if ($_GET['page'] == 'wf_coupon_csv_im_ex') {
                $tab = 'coupon';
            }
        }
        if (!empty($_GET['tab'])) {
            if ($_GET['tab'] == 'export') {
                $tab = 'export';
            } else if ($_GET['tab'] == 'settings') {
                $tab = 'settings';
            } else if ($_GET['tab'] == 'coupon') {
                $tab = 'coupon';
            } else if ($_GET['tab'] == 'importxml') {
                $tab = 'importxml';
            } else if ($_GET['tab'] == 'help') {
                $tab = 'help';
            }
        }

        include( 'views/html-wf-admin-screen.php' );
    }

    public function add_coupons_bulk_actions() {
        global $post_type, $post_status;

        if ($post_type == 'shop_coupon' && $post_status != 'trash') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $downloadToCSV = $('<option>').val('download_coupon_csv').text('<?php _e('Download Coupons as CSV', 'wf_order_import_export') ?>');
                    $('select[name^="action"]').append($downloadToCSV);
                });
            </script>
            <?php
        }
    }

    public function process_coupons_bulk_actions() {
        global $typenow;
        if ($typenow == 'shop_coupon') {
            // get the action list
            $wp_list_table = _get_list_table('WP_Posts_List_Table');
            $action = $wp_list_table->current_action();
            if (!in_array($action, array('download_coupon_csv'))) {
                return;
            }
            check_admin_referer('bulk-posts');

            if (isset($_REQUEST['post'])) {
                $coupon_ids = array_map('absint', $_REQUEST['post']);
            }
            if (empty($coupon_ids)) {
                return;
            }
            @set_time_limit(0);

            if ($action == 'download_coupon_csv') {
                include_once( 'exporter/class-wf-cpnimpexpcsv-exporter.php' );
                WF_CpnImpExpCsv_Exporter::do_export('shop_coupon', $coupon_ids);
            }
        }
    }

    public function coupon_export_column($columns) {
        $new_columns = (is_array($columns)) ? $columns : array();
        $new_columns['coupon_export'] = 'Export';
        return $new_columns;
    }

    public function coupon_export_column_value($column) {
        global $post;
        if ($column == 'coupon_export') {
            $action_general = 'download_coupon_csv';
            $url_general = wp_nonce_url(admin_url('admin-ajax.php?action=coupon_export_csv_single&coupon_id=' . $post->ID), 'coupon_export_csv_single');
            $name_general = __('Download to CSV', 'wf_order_import_export');
            printf('<a class="button tips %s" href="%s" data-tip="%s">%s</a>', $action_general, esc_url($url_general), $name_general, $name_general);
        }
    }

    public function process_ajax_export_single_coupon() {
        
        if (!WF_Order_Import_Export_CSV::hf_user_permission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wf_order_import_export'));
        }
        if (!check_admin_referer('coupon_export_csv_single')) {
            wp_die(__('You have taken too long, please go back and try again.', 'wf_order_import_export'));
        }
                
        $coupon_id = !empty($_GET['coupon_id']) ? absint($_GET['coupon_id']) : '';
        if (!$coupon_id) {
            die;
        }
        $coupon_Ids = array(0 => $coupon_id);
        include_once( 'exporter/class-wf-cpnimpexpcsv-exporter.php' );
        WF_CpnImpExpCsv_Exporter::do_export('shop_coupon', $coupon_Ids);
        wp_redirect(wp_get_referer());
        exit;
    }

    public function admin_import_page() {
        include( 'views/html-wf-getting-started.php' );
        include( 'views/import/html-wf-import-coupons.php' );
        $post_columns = include( 'exporter/data/data-wf-post-columns.php' );
        include( 'views/export/html-wf-export-coupons.php' );
    }

    public function admin_export_page() {
        $post_columns = include( 'exporter/data/data-wf-post-columns.php' );
        include( 'views/export/html-wf-export-orders.php' );
    }

    public function admin_coupon_page() {
        //include( 'views/html-wf-getting-started-coupon.php' );
        include( 'views/import/html-wf-import-coupons.php' );
        $post_columns = include( 'exporter/data/data-wf-post-columns-coupon.php' );
        include( 'views/export/html-wf-export-coupons.php' );
    }

    public function admin_settings_page() {
        include( 'views/settings/html-wf-all-settings.php' );
    }

}

new WF_CpnImpExpCsv_Admin_Screen();
