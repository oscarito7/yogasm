<?php
if (!defined('ABSPATH')) {
    exit;
}

class wf_subcription_orderImpExpCsv_Admin_Screen {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_print_styles', array($this, 'admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));

        add_action('admin_footer-edit.php', array($this, 'add_order_bulk_actions'));
        add_action('load-edit.php', array($this, 'process_order_bulk_actions'));
//        if (is_admin()) {
            add_action('wp_ajax_wc_order_csv_export_single', array($this, 'process_ajax_export_single_order'));
//        }
    }

    /**
     * Notices in admin
     */
    public function admin_notices() {
        if (!function_exists('mb_detect_encoding')) {
            echo '<div class="error"><p>' . __('Order CSV Import Export requires the function <code>mb_detect_encoding</code> to import and export CSV files. Please ask your hosting provider to enable this function.', 'hf_subscription_order_import_export') . '</p></div>';
        }
    }

    /**
     * Admin Menu
     */
    public function admin_menu() {
        $page = add_submenu_page('woocommerce', __('Subscription Order Im-Ex', 'hf_subscription_order_import_export'), __('Subscription Order Im-Ex', 'hf_subscription_order_import_export'), apply_filters('woocommerce_csv_order_role', 'manage_woocommerce'), 'wf_woocommerce_subscription_order_im_ex', array($this, 'output'));
    }
    


    /**
     * Admin Scripts
     */
    public function admin_scripts() {
        global $wp_scripts;
        $wc_path = WF_OrderImpExpCsv_Common_Utils::hf_get_wc_path();
        $screen = get_current_screen();
        $allowed_screen_id = array('wf_woocommerce_order_im_ex_xml','woocommerce_page_wf_woocommerce_order_im_ex_xml',
                'wf_woocommerce_subscription_order_im_ex','wf_coupon_csv_im_ex','woocommerce_page_wf_woocommerce_subscription_order_im_ex',
                'wf_woocommerce_order_im_ex','edit-shop_order',
                'admin',
                'edit-shop_subscription', 'edit-shop_coupon',
                'woocommerce_page_wf_woocommerce_order_im_ex'
            );
        wp_enqueue_script('wc-enhanced-select');
        
        wp_enqueue_style('woocommerce_admin_styles', $wc_path . '/assets/css/admin.css');
        if (in_array($screen->id, $allowed_screen_id)) {
            wp_enqueue_style('woocommerce-subscription-order-csv-importer', plugins_url(basename(plugin_dir_path(WF_SubcriptionOrderImpExpCsv_FILE)) . '/styles/wf-style.css', basename(__FILE__)), '', '1.0.0', 'screen');
            wp_enqueue_style('woocommerce-subscription-order-csv-importer2', plugins_url(basename(plugin_dir_path(WF_SubcriptionOrderImpExpCsv_FILE)) . '/styles/jquery-ui.css', basename(__FILE__)), '', '1.0.0', 'screen');
            wp_enqueue_script('woocommerce-subscription-order-csv-importer', plugins_url(basename(plugin_dir_path(WF_SubcriptionOrderImpExpCsv_FILE)) . '/js/woocommerce-subscription-order-csv-importer.js', basename(__FILE__)), array(), '2.0.0', true);
        }
        wp_localize_script('woocommerce-subscription-order-csv-importer', 'woocommerce_subscription_order_csv_params', array('wt_nonce'=> wp_create_nonce(wf_subscription_order_imp_exp_ID),'calendar_icon' => plugins_url(basename(plugin_dir_path(WF_SubcriptionOrderImpExpCsv_FILE)) . '/images/calendar.png', basename(__FILE__)), 'siteurl' => admin_url('admin-ajax.php')));
        wp_localize_script('woocommerce-subscription-order-csv-importer', 'woocommerce_subscription_order_csv_cron_params', array('sbc_auto_export' => 'Disabled', 'sbc_auto_import' => 'Disabled'));
        wp_enqueue_script('jquery-ui-datepicker');
        
    }

    /**
     * Admin Screen output
     */
    public function output() {
        $tab = 'import';

        if (!empty($_GET['page'])) {
            if ($_GET['page'] == 'wf_woocommerce_subscription_order_im_ex') {
                $tab = 'subscription';
            }
        }
        if (!empty($_GET['tab'])) {
            if ($_GET['tab'] == 'export') {
                $tab = 'export';
            } else if ($_GET['tab'] == 'settings') {
                $tab = 'settings';
            } else if ( $_GET['tab'] == 'subscription' ) {
                $tab = 'subscription';
            } else if($_GET['tab'] == 'importxml')
            {
                $tab = 'importxml';
            }else if($_GET['tab'] == 'help'){
                $tab = 'help';
            }
        }
        include( 'views/html-wf-admin-screen.php' );
    }

    public function add_order_bulk_actions() {
        global $post_type, $post_status;

        if ($post_type == 'shop_subscription' && $post_status != 'trash') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $downloadToXml = $('<option>').val('download_to_csv_wf').text('<?php _e('Download as CSV', 'hf_subscription_order_import_export') ?>');

                    $('select[name^="action"]').append($downloadToXml);
                });
            </script>
            <?php
        } else if ($post_type == 'hf_shop_subscription' && $post_status != 'trash') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $downloadTohf = $('<option>').val('download_to_csv_hf').text('<?php _e('Download as CSV', 'hf_subscription_order_import_export') ?>');

                    $('select[name^="action"]').append($downloadTohf);
                });
            </script>
            <?php
        }
    }

    /**
     * Order page bulk export action
     * 
     */
    public function process_order_bulk_actions() {
        global $typenow;
        if ($typenow == 'shop_subscription' || $typenow == 'hf_shop_subscription') {
            // get the action list
            $wp_list_table = _get_list_table('WP_Posts_List_Table');
            $action = $wp_list_table->current_action();
            if (!in_array($action, array('download_to_csv_wf','download_to_csv_hf'))) {
                return;
            }
            // security check
            check_admin_referer('bulk-posts');

            if (isset($_REQUEST['post'])) {
                $order_ids = array_map('absint', $_REQUEST['post']);
            }
            if (empty($order_ids)) {
                return;
            }
            // give an unlimited timeout if possible
            @set_time_limit(0);

            if ($action == 'download_to_csv_wf' || $action == 'download_to_csv_hf') {
                include_once( 'exporter/class-wf-subscription-orderimpexpcsv-exporter.php' );
                wf_subcription_orderImpExpCsv_Exporter::do_export('shop_subscription', $order_ids);
            }
        }
    }

    /**
     * Single order export
     */
    public function process_ajax_export_single_order() {

        if (!wf_subscription_order_import_export_CSV::hf_user_permission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wf_order_import_export'));
        }
        if (!check_admin_referer('wc_order_csv_export_single')) {
            wp_die(__('You have taken too long, please go back and try again.', 'hf_subscription_order_import_export'));
        }
        $order_id = !empty($_GET['order_id']) ? absint($_GET['order_id']) : '';
        if (!$order_id) {
            die;
        }
        $order_IDS = array(0 => $order_id);
        include_once( 'exporter/class-wf-subscription-orderimpexpcsv-exporter.php' );
        wf_subcription_orderImpExpCsv_Exporter::do_export('shop_subscription', $order_IDS);
        wp_redirect(wp_get_referer());
        exit;
    }

        /**
     * Return an array of subscription status types, similar to @see wc_get_order_statuses()
     * @since  2.0
     * @return array
     */
    public static function hf_get_subscription_statuses() {

        $subscription_statuses = array(
            'wc-pending' => _x('Pending', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-active' => _x('Active', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-on-hold' => _x('On hold', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-cancelled' => _x('Cancelled', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-switched' => _x('Switched', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-expired' => _x('Expired', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-pending-cancel' => _x('Pending Cancellation', 'Subscription status', 'woocommerce-subscriptions'),
        );

        return apply_filters('hf_subscription_statuses', $subscription_statuses);
    }
    
    /**
     * Admin page for importing
     */
    public function admin_import_page() {
        include( 'views/html-wf-subscription-getting-started.php' );
        include( 'views/import/html-wf-import-subscription.php' );
        $post_columns = include( 'exporter/data/data-wf-post-subscription-columns.php' );
        include( 'views/export/html-wf-export-subscription.php' );
    }



    /**
     * Admin Page for exporting
     */
    public function admin_export_page() {
        $post_columns = include( 'exporter/data/data-wf-post-subscription-columns.php' );
        include( 'views/export/html-wf-export-subscription.php' );
    }
    
    
    public function admin_subscription_page() 
    {
        //include( 'views/html-wf-subscription-getting-started.php' );
        include( 'views/import/html-wf-import-subscription.php' );
        $post_columns = include( 'exporter/data/data-wf-post-subscription-columns.php' );
        include( 'views/export/html-wf-export-subscription.php' );
    }


    /**
     * Admin Page for settings
     */
    public function admin_settings_page() {
        include( 'views/settings/html-wf-all-settings.php' );
    }

}

new wf_subcription_orderImpExpCsv_Admin_Screen();
