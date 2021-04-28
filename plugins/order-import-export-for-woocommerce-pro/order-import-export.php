<?php

/*
  Plugin Name: Order / Coupon / Subscription Export Import Plugin for WooCommerce
  Plugin URI: https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/
  Description: Export and Import Order detail including line items, subscriptions and coupons from and to your WooCommerce Store.
  Author: WebToffee
  Author URI: https://www.webtoffee.com/shop/
  Version: 2.4.7
  Text Domain: wf_order_import_export
  WC tested up to: 3.9.3
 */

if (!defined('WPINC')) {
    return;
}


define("WF_ORDER_IMP_EXP_ID", "wf_order_imp_exp");
define("WF_WOOCOMMERCE_ORDER_IM_EX", "wf_woocommerce_order_im_ex");

define("WF_CPN_IMP_EXP_ID", "wf_cpn_imp_exp");
define("wf_coupon_csv_im_ex", "wf_coupon_csv_im_ex");

define("wf_subscription_order_imp_exp_ID", "wf_subscription_order_imp_exp");
define("wf_woocommerce_subscription_order_im_ex", "wf_woocommerce_subscription_order_im_ex");

define("wf_all_imp_exp_ID", "wf_all_imp_exp");

define("WF_ORDER_IMP_EXP_XML_ID", "wf_order_imp_exp_xml");
define("wf_woocommerce_order_im_ex_xml", "wf_woocommerce_order_im_ex_xml");

/**
 * Check if WooCommerce is active
 */
register_activation_hook(__FILE__, 'wt_order_register_activation_hook_callback');

function wt_order_register_activation_hook_callback() {
    if(!class_exists( 'WooCommerce' )){
        deactivate_plugins(basename(__FILE__));
        wp_die(__("WooCommerce is not installed/actived. it is required for this plugin to work properly. Please activate WooCommerce.", "wf_order_import_export"), "", array('back_link' => 1));
    }
    if (is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php')) {
        deactivate_plugins(basename(__FILE__));
        wp_die(__("Looks like you have both free and premium version installed on your site! Prior to activating premium, deactivate and delete the free version. For any issue kindly contact our support team here: <a target='_blank' href='https://www.webtoffee.com/support/'>support</a>", "wf_order_import_export"), "", array('back_link' => 1));
    }
    update_option('wt_ord_pro_plugin_installed_date', date('Y-m-d H:i:s'));
//    set_transient('_welcome_screen_activation_redirect', true, 30);
}
//if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!class_exists('WF_Order_Import_Export_CSV')) :

        /**
         * Main CSV Import class
         */
        class WF_Order_Import_Export_CSV {

            public $cron_import_ord;
            public $cron_export_ord;
            public $cron_import_url;

            /**
             * Constructor
             */
            public function __construct() {
                define('WF_OrderImpExpCsv_FILE', __FILE__);

                if (is_admin()) {
                    add_action('admin_notices', array($this, 'wf_order_ie_admin_notice'), 15);
                }

                add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wf_plugin_action_links'));
                add_action('init', array($this, 'load_plugin_textdomain'));
                add_action('init', array($this, 'catch_export_request'), 20);
                add_action('init', array($this, 'catch_save_settings'), 20);
                add_action('admin_init', array($this, 'register_importers'));

                include_once( 'includes/class-wf-orderimpexpcsv-system-status-tools.php' );
                include_once( 'includes/class-wf-orderimpexpcsv-admin-screen.php' );
                include_once( 'includes/importer/class-wf-orderimpexpcsv-importer.php' );

                include_once( 'includes/class-wf-common-utils.php' );

                require_once( 'includes/class-wf-ordimpexpcsv-cron.php' );
                $this->cron_export_ord = new WF_OrdImpExpCsv_Cron();
                //$this->cron_export->wf_scheduled_export_order();
                register_activation_hook(__FILE__, array($this->cron_export_ord, 'wf_new_scheduled_export_order'));
                register_deactivation_hook(__FILE__, array($this->cron_export_ord, 'clear_wf_scheduled_export_order'));

                require_once( 'includes/class-wf-ordimpexpcsv-import-cron.php' );
                $this->cron_import_ord = new WF_OrdImpExpCsv_ImportCron();
                //$this->cron_import->wf_scheduled_import_order();
                register_activation_hook(__FILE__, array($this->cron_import_ord, 'wf_new_scheduled_import_order'));
                register_deactivation_hook(__FILE__, array($this->cron_import_ord, 'clear_wf_scheduled_import_order'));

                // Order import from URL 
                require_once( 'includes/class-wf-ordimpexpcsv-import-url-cron.php' );
                $this->cron_import_url = new WF_OrdImpExpCsv_ImportUrlCron();
                register_activation_hook(__FILE__, array($this->cron_import_url, 'wf_new_scheduled_import_order_url'));
                register_deactivation_hook(__FILE__, array($this->cron_import_url, 'clear_wf_scheduled_import_url_orders'));

                if (defined('DOING_AJAX')) {
                    include_once( 'includes/class-wf-orderimpexpcsv-ajax-handler.php' );
                }

                include_once( 'includes/class-wf-export-status-column.php' );
            }

            public function wf_plugin_action_links($links) {
                $plugin_links = array(
                    '<a href="' . admin_url('admin.php?page=wf_woocommerce_order_im_ex') . '">' . __('Import Export', 'wf_order_import_export') . '</a>',
                    '<a href="https://www.webtoffee.com/category/documentation/order-import-export-plugin-for-woocommerce/" target="_blank">' . __('Documentation', 'wf_order_import_export') . '</a>',
                    '<a href="https://www.webtoffee.com/support/" target="_blank">' . __('Support', 'wf_order_import_export') . '</a>'
                );
                return array_merge($plugin_links, $links);
            }

            function wf_order_ie_admin_notice() {
                global $pagenow;
                global $post;

                if (!isset($_GET["wf_order_ie_msg"]) && empty($_GET["wf_order_ie_msg"])) {
                    return;
                }

                $wf_order_ie_msg = absint($_GET["wf_order_ie_msg"]);

                switch ($wf_order_ie_msg) {
                    case "1":
                        echo '<div class="update"><p>' . __('Successfully uploaded via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                    case "2":
                        echo '<div class="error"><p>' . __('Error while uploading via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                }
            }

            /**
             * Add screen ID
             */
            public function woocommerce_screen_ids($ids) {
                $ids[] = 'admin'; // For import screen
                return $ids;
            }

            /**
             * Handle localisation
             */
            public function load_plugin_textdomain() {
                load_plugin_textdomain('wf_order_import_export', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            }

            /**
             * Catches an export request and exports the data. This class is only loaded in admin.
             */
            public function catch_export_request() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_order_im_ex') {
                    switch ($_GET['action']) {
                        case "export" :
                            $user_ok = self::hf_user_permission();
                            if ($user_ok) {
                                include_once( 'includes/exporter/class-wf-orderimpexpcsv-exporter.php' );
                                if (!empty($_GET['xml']))
                                    WF_OrderImpExpCsv_Exporter::do_export('shop_order', '', '1');
                                else
                                    WF_OrderImpExpCsv_Exporter::do_export('shop_order');
                            } else {
                                wp_redirect(wp_login_url());
                            }
                            break;
                    }
                }
            }

            public function catch_save_settings() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_order_im_ex') {
                    switch ($_GET['action']) {
                        case "settings" :
                            //include_once( 'includes/settings/class-wf-orderimpexpcsv-settings.php' );
                            //WF_OrderImpExpCsv_Settings::save_settings( );
                            include_once( 'includes/settings/class-wf-allimpexpcsv-settings.php' );
                            wf_allImpExpCsv_Settings::save_settings();
                            break;
                    }
                }
            }

            /**
             * Register importers for use
             */
            public function register_importers() {
                include_once ( 'includes/wf_api_manager/wf-api-manager-config.php' );
                register_importer('woocommerce_wf_order_csv', 'WooCommerce Order (CSV/XML)', __('Import <strong>Orders</strong> to your store via a csv file.', 'wf_order_import_export'), 'WF_OrderImpExpCsv_Importer::order_importer');
                register_importer('order_csv_cron', 'WooCommerce Orders (CSV/XML)', __('Cron Import <strong>order</strong> to your store via a csv file.', 'wf_order_import_export'), 'WF_OrdImpExpCsv_ImportCron::order_importer');
            }

            public static function hf_user_permission() {
                // Check if user has rights to export
                $current_user = wp_get_current_user();
                $current_user->roles = apply_filters('hf_add_user_roles', $current_user->roles);
                $current_user->roles = array_unique($current_user->roles);
                $user_ok = false;

                $wf_roles = apply_filters('hf_user_permission_roles', array('administrator', 'shop_manager'));
                if ($current_user instanceof WP_User) {
                    $can_users = array_intersect($wf_roles, $current_user->roles);
                    if (!empty($can_users)) {
                        $user_ok = true;
                    }
                }
                return $user_ok;
            }

        }

        endif;

    new WF_Order_Import_Export_CSV();

    if (!class_exists('WF_Coupon_Import_Export_CSV')) :

        class WF_Coupon_Import_Export_CSV {

            public $cron_import_cpn;
            public $cron_export_cpn;

            /**
             * Constructor
             */
            public function __construct() {
                define('WF_CpnImpExpCsv_FILE', __FILE__);


                if (is_admin()) {
                    add_action('admin_notices', array($this, 'wf_coupon_ie_admin_notice'), 15);
                }

                add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                //add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wf_plugin_action_links' ) );
                add_action('init', array($this, 'load_plugin_textdomain'));
                add_action('init', array($this, 'catch_export_request'), 20);
                add_action('init', array($this, 'catch_save_settings'), 20);
                add_action('admin_init', array($this, 'register_importers'));

                include_once( 'includes/class-wf-cpnimpexpcsv-system-status-tools.php' );
                include_once( 'includes/class-wf-cpnimpexpcsv-admin-screen.php' );
                include_once( 'includes/importer/class-wf-cpnimpexpcsv-importer.php' );
                require_once( 'includes/class-wf-cpnimpexpcsv-cron.php' );

                $this->cron_export_cpn = new WF_CpnImpExpCsv_Cron();
                register_activation_hook(__FILE__, array($this->cron_export_cpn, 'wf_new_scheduled_export_coupon'));
                register_deactivation_hook(__FILE__, array($this->cron_export_cpn, 'clear_wf_scheduled_export_coupon'));



                if (defined('DOING_AJAX')) {
                    include_once( 'includes/class-wf-cpnimpexpcsv-ajax-handler.php' );
                }

                require_once( 'includes/class-wf-cpnimpexpcsv-import-cron.php' );
                $this->cron_import_cpn = new WF_CpnImpExpCsv_ImportCron();
                register_activation_hook(__FILE__, array($this->cron_import_cpn, 'wf_new_scheduled_import_coupon'));
                register_deactivation_hook(__FILE__, array($this->cron_import_cpn, 'clear_wf_scheduled_import_coupon'));
            }

            function wf_coupon_ie_admin_notice() {
                global $pagenow;
                global $post;

                if (!isset($_GET["wf_coupon_ie_msg"]) && empty($_GET["wf_coupon_ie_msg"])) {
                    return;
                }

                $wf_coupon_ie_msg = absint($_GET["wf_coupon_ie_msg"]);

                switch ($wf_coupon_ie_msg) {
                    case "1":
                        echo '<div class="update"><p>' . __('Successfully uploaded via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                    case "2":
                        echo '<div class="error"><p>' . __('Error while uploading via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                    case "3":
                        echo '<div class="error"><p>' . __('Please choose the file in CSV/XML format either using Method 1 or Method 2.', 'wf_order_import_export') . '</p></div>';
                        break;
                }
            }

            /**
             * Add screen ID
             */
            public function woocommerce_screen_ids($ids) {
                $ids[] = 'admin'; // For import screen
                return $ids;
            }

            /**
             * Handle localisation
             */
            public function load_plugin_textdomain() {
                load_plugin_textdomain('wf_order_import_export', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            }

            /**
             * Catches an export request and exports the data. This class is only loaded in admin.
             */
            public function catch_export_request() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_coupon_csv_im_ex') {
                    switch ($_GET['action']) {
                        case "export" :
                            $user_ok = self::hf_user_permission();
                            if ($user_ok) {
                                include_once( 'includes/exporter/class-wf-cpnimpexpcsv-exporter.php' );
                                WF_CpnImpExpCsv_Exporter::do_export('shop_coupon');
                            } else {
                                wp_redirect(wp_login_url());
                            }
                            break;
                    }
                }
            }

            public function catch_save_settings() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_coupon_csv_im_ex') {
                    switch ($_GET['action']) {
                        case "settings" :
                            //include_once( 'includes/settings/class-wf-cpnimpexpcsv-settings.php' );
                            //WF_CpnImpExpCsv_Settings::save_settings( );
                            include_once( 'includes/settings/class-wf-allimpexpcsv-settings.php' );
                            wf_allImpExpCsv_Settings::save_settings();
                            break;
                    }
                }
            }

            /**
             * Register importers for use
             */
            public function register_importers() {
                include_once ( 'includes/wf_api_manager/wf-api-manager-config.php' );
                register_importer('coupon_csv', 'WooCommerce Coupons (CSV)', __('Import <strong>coupon</strong> to your store via a csv file.', 'wf_order_import_export'), 'WF_CpnImpExpCsv_Importer::coupon_importer');
                register_importer('coupon_csv_cron', 'WooCommerce Coupons (CSV)', __('Cron Import <strong>coupon</strong> to your store via a csv file.', 'wf_order_import_export'), 'WF_CpnImpExpCsv_ImportCron::coupon_importer');
                wp_enqueue_script('woocommerce-order-xml-importer', plugins_url(basename(plugin_dir_path(WF_OrderImpExpXML_FILE)) . '/js/hf_order_admin.js', basename(__FILE__)), array(), '1.0.0', true);
            }

            public static function hf_user_permission() {
                // Check if user has rights to export
                $current_user = wp_get_current_user();
                $current_user->roles = apply_filters('hf_add_user_roles', $current_user->roles);
                $current_user->roles = array_unique($current_user->roles);
                $user_ok = false;

                $wf_roles = apply_filters('hf_user_permission_roles', array('administrator', 'shop_manager'));
                if ($current_user instanceof WP_User) {
                    $can_users = array_intersect($wf_roles, $current_user->roles);
                    if (!empty($can_users)) {
                        $user_ok = true;
                    }
                }
                return $user_ok;
            }

        }

        endif;

    new WF_Coupon_Import_Export_CSV();





    if (!class_exists('wf_subscription_order_import_export_CSV')) :

        /**
         * Main CSV Import class
         */
        class wf_subscription_order_import_export_CSV {

            public $cron_import_sbc;
            public $cron_export_sbc;

            /**
             * Constructor
             */
            public function __construct() {
                define('WF_SubcriptionOrderImpExpCsv_FILE', __FILE__);

                if (is_admin()) {
                    add_action('admin_notices', array($this, 'wf_subcription_order_ie_admin_notice'), 15);
                }

                add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                //add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wf_plugin_action_links' ) );
                add_action('init', array($this, 'load_plugin_textdomain'));
                add_action('init', array($this, 'catch_export_request'), 20);
                add_action('init', array($this, 'catch_save_settings'), 20);
                add_action('admin_init', array($this, 'register_importers'));

                include_once( 'includes/class-wf-subscription-orderimpexpcsv-system-status-tools.php' );
                include_once( 'includes/class-wf-subscription-orderimpexpcsv-admin-screen.php' );
                include_once( 'includes/importer/class-wf-subscription-orderimpexpcsv-importer.php' );



                require_once( 'includes/class-wf-subscription-orderimpexpcsv-cron.php' );
                $this->cron_export_sbc = new WF_SubcriptionOrderImpExpCsv_Cron();
                //$this->cron_export->hf_scheduled_export_subscription_orders();
                register_activation_hook(__FILE__, array($this->cron_export_sbc, 'hf_new_scheduled_export'));
                register_deactivation_hook(__FILE__, array($this->cron_export_sbc, 'clear_hf_scheduled_export'));

                require_once( 'includes/class-wf-subscription-orderimpexpcsv-import-cron.php' );
                $this->cron_import_sbc = new WF_SubscriptionOrderImpExpCsv_ImportCron();
                //$this->cron_import->hf_scheduled_import_subscription_orders();
                register_activation_hook(__FILE__, array($this->cron_import_sbc, 'hf_new_scheduled_import'));
                register_deactivation_hook(__FILE__, array($this->cron_import_sbc, 'clear_hf_scheduled_import'));




                if (defined('DOING_AJAX')) {
                    include_once( 'includes/class-wf-subscription-orderimpexpcsv-ajax-handler.php' );
                }
            }

            function wf_subcription_order_ie_admin_notice() {
                global $pagenow;
                global $post;

                if (!isset($_GET["wf_subcription_order_ie_msg"]) && empty($_GET["wf_subcription_order_ie_msg"])) {
                    return;
                }

                $wf_subcription_order_ie_msg = absint($_GET["wf_subcription_order_ie_msg"]);

                switch ($wf_subcription_order_ie_msg) {
                    case "1":
                        echo '<div class="update"><p>' . __('Successfully uploaded via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                    case "2":
                        echo '<div class="error"><p>' . __('Error while uploading via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                }
            }

            /**
             * Add screen ID
             */
            public function woocommerce_screen_ids($ids) {
                $ids[] = 'admin'; // For import screen
                return $ids;
            }

            /**
             * Handle localisation
             */
            public function load_plugin_textdomain() {
                load_plugin_textdomain('wf_order_import_export', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            }

            /**
             * Catches an export request and exports the data. This class is only loaded in admin.
             */
            public function catch_export_request() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_subscription_order_im_ex') {
                    switch ($_GET['action']) {
                        case "export" :
                            $user_ok = self::hf_user_permission();
                            if ($user_ok) {
                                include_once( 'includes/exporter/class-wf-subscription-orderimpexpcsv-exporter.php' );
                                wf_subcription_orderImpExpCsv_Exporter::do_export('shop_subscription');
                            } else {
                                wp_redirect(wp_login_url());
                            }
                            break;
                    }
                }
            }

            public function catch_save_settings() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_subscription_order_im_ex') {
                    switch ($_GET['action']) {
                        case "settings" :
                            include_once( 'includes/settings/class-wf-allimpexpcsv-settings.php' );
                            wf_allImpExpCsv_Settings::save_settings();
                            break;
                    }
                }
            }

            /**
             * Register importers for use
             */
            public function register_importers() {
                include_once ( 'includes/wf_api_manager/wf-api-manager-config.php' );
                register_importer('woocommerce_wf_subscription_order_csv', 'WooCommerce Subscription Order (CSV)', __('Import <strong>Subcription Orders</strong> to your store via a csv file.', 'wf_order_import_export'), 'wf_subcription_orderImpExpCsv_Importer::subscription_order_importer');
                register_importer('woocommerce_subscription_csv_cron', 'WooCommerce Subscription Order Cron(CSV)', __('Cron Import <strong>subscription orders</strong> to your store via a csv file.', 'wf_order_import_export'), 'WF_SubscriptionOrderImpExpCsv_ImportCron::subcription_order_importer');
            }

            public static function hf_user_permission() {
                // Check if user has rights to export
                $current_user = wp_get_current_user();
                $current_user->roles = apply_filters('hf_add_user_roles', $current_user->roles);
                $current_user->roles = array_unique($current_user->roles);
                $user_ok = false;

                $wf_roles = apply_filters('hf_user_permission_roles', array('administrator', 'shop_manager'));
                if ($current_user instanceof WP_User) {
                    $can_users = array_intersect($wf_roles, $current_user->roles);
                    if (!empty($can_users)) {
                        $user_ok = true;
                    }
                }
                return $user_ok;
            }

        }

        endif;

    new wf_subscription_order_import_export_CSV();

    if (!class_exists('OrderImpExpXML_Basic')) :

        /**
         * Main XML Import class
         */
        class OrderImpExpXML_Basic {

            public $cron_import_xml;
            public $cron_export_xml;

            /**
             * Constructor
             */
            public function __construct() {
                define('WF_OrderImpExpXML_FILE', plugin_dir_url(__FILE__) . 'sample-files/');
                define('WF_OrderImpExpXML_baseFILE', __FILE__);
                
                if (is_admin()) {
                    add_action('admin_notices', array($this, 'wf_order_ie_admin_notice'), 15);
                }

                add_filter('woocommerce_screen_ids', array($this, 'woocommerce_screen_ids'));
                // add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wf_plugin_action_links'));
                add_action('init', array($this, 'load_plugin_textdomain'));
                add_action('init', array($this, 'catch_export_request'), 20);
                add_action('init', array($this, 'catch_save_settings'), 20);
                add_action('admin_init', array($this, 'register_importers'));

                include_once( 'includes/class-OrderImpExpXML-admin-screen.php' );
                include_once( 'includes/importer/class-OrderImpExpXML-importer.php' );


                require_once( 'includes/class-OrderImpExpXML-cron.php' );
                $this->cron_export_xml = new WF_OrderImpExpXML_Cron();
                //$this->cron_export->wf_scheduled_export_orderxml();
                register_activation_hook(__FILE__, array($this->cron_export_xml, 'wf_new_scheduled_export_orderxml'));
                register_deactivation_hook(__FILE__, array($this->cron_export_xml, 'clear_wf_scheduled_export_orderxml'));

                require_once( 'includes/class-OrderImpExpXML-import-cron.php' );
                $this->cron_import_xml = new WF_OrderImpExpXML_ImportCron();
                //$this->cron_import->wf_scheduled_import_orderxml();
                register_activation_hook(__FILE__, array($this->cron_import_xml, 'wf_new_scheduled_import_orderxml'));
                register_deactivation_hook(__FILE__, array($this->cron_import_xml, 'clear_wf_scheduled_import_orderxml'));


                if (defined('DOING_AJAX')) {
                    include_once( 'includes/class-OrderImpExpXML-ajax-handler.php' );
                }
            }

            function wf_order_ie_admin_notice() {
                global $pagenow;
                global $post;

                if (!isset($_GET["wf_order_ie_msg"]) && empty($_GET["wf_order_ie_msg"])) {
                    return;
                }

                $wf_order_ie_msg = absint($_GET["wf_order_ie_msg"]);

                switch ($wf_order_ie_msg) {
                    case "1":
                        echo '<div class="update"><p>' . __('Successfully uploaded via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                    case "2":
                        echo '<div class="error"><p>' . __('Error while uploading via FTP.', 'wf_order_import_export') . '</p></div>';
                        break;
                }
            }

            /**
             * Add screen ID
             */
            public function woocommerce_screen_ids($ids) {
                $ids[] = 'admin'; // For import screen
                return $ids;
            }

            /**
             * Handle localisation
             */
            public function load_plugin_textdomain() {
                load_plugin_textdomain('wf_order_import_export', false, dirname(plugin_basename(__FILE__)) . '/lang/');
            }

            /**
             * Catches an export request and exports the data. This class is only loaded in admin.
             */
            public function catch_export_request() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_order_im_ex_xml') {
                    switch ($_GET['action']) {
                        case "export" :
                            $user_ok = self::hf_user_permission();
                            if ($user_ok) {
                                include_once( 'includes/exporter/class-OrderImpExpXML-base-exporter.php' );
                                $order_imp_exp_obj = new OrderImpExpXMLBase_Exporter();
                                $order_imp_exp_obj->do_export('shop_order');
                            } else {
                                wp_redirect(wp_login_url());
                            }
                            break;
                    }
                }
            }

            public function catch_save_settings() {
                if (!empty($_GET['action']) && !empty($_GET['page']) && $_GET['page'] == 'wf_woocommerce_order_im_ex_xml') {
                    switch ($_GET['action']) {
                        case "settings" :
                            include_once( 'includes/settings/class-wf-allimpexpcsv-settings.php' );
                            wf_allImpExpCsv_Settings::save_settings();
                            break;
                    }
                }
            }

            /**
             * Register importers for use
             */
            public function register_importers() {
                include_once ( 'includes/wf_api_manager/wf-api-manager-config.php' );
                register_importer('woocommerce_wf_import_order_xml', 'WooCommerce Order XML', __('Import <strong>Orders</strong> details to your store via a xml file.', 'wf_order_import_export'), 'OrderImpExpXML_Importer::order_importer');
                register_importer('woocommerce_wf_import_order_xml_cron', 'WooCommerce Order XML Cron', __('Cron Import <strong>Orders</strong> details to your store via a xml file.', 'wf_order_import_export'), 'WF_OrderImpExpXML_ImportCron::orderxml_importer');
            }

            public static function hf_user_permission() {
                // Check if user has rights to export
                $current_user = wp_get_current_user();
                $current_user->roles = apply_filters('hf_add_user_roles', $current_user->roles);
                $current_user->roles = array_unique($current_user->roles);
                $user_ok = false;

                $wf_roles = apply_filters('hf_user_permission_roles', array('administrator', 'shop_manager'));
                if ($current_user instanceof WP_User) {
                    $can_users = array_intersect($wf_roles, $current_user->roles);
                    if (!empty($can_users)) {
                        $user_ok = true;
                    }
                }
                return $user_ok;
            }

        }

        endif;

    new OrderImpExpXML_Basic();
//}

