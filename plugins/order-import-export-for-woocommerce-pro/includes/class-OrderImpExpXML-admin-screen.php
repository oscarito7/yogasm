<?php
if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_AdminScreen {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_print_styles', array($this, 'admin_scripts'));
        add_action('admin_footer-edit.php', array($this, 'add_order_bulk_actions'));
        add_action('load-edit.php', array($this, 'process_order_bulk_actions'));
        if (is_admin()) {
            add_action('wp_ajax_wc_order_xml_export_single', array($this, 'process_ajax_export_single_order'));
        }
    }

    /**
     * Notices in admin
     */
    public function admin_notices() {
        if (!function_exists('mb_detect_encoding')) {
            echo '<div class="error"><p>' . __('Order XML Import Export requires the function <code>mb_detect_encoding</code> to import and export XML files. Please ask your hosting provider to enable this function.', 'wf_order_import_export') . '</p></div>';
        }
    }

    /**
     * Admin Menu
     */
    public function admin_menu() {
        $page = add_submenu_page('woocommerce', __('Order XML Im-Ex', 'wf_order_import_export'), __('Order XML Im-Ex', 'wf_order_import_export'), apply_filters('woocommerce_csv_order_role', 'manage_woocommerce'), 'wf_woocommerce_order_im_ex_xml', array($this, 'output'));
    }

    /**
     * Admin Scripts
     */
    public function admin_scripts() {
        global $wp_scripts;
        $wc_path = WF_OrderImpExpCsv_Common_Utils::hf_get_wc_path();
        $screen = get_current_screen();
        $allowed_creen_id = array('wf_woocommerce_order_im_ex_xml','woocommerce_page_wf_woocommerce_order_im_ex_xml',
                'wf_woocommerce_subscription_order_im_ex','wf_coupon_csv_im_ex','wf_woocommerce_subscription_order_im_ex',
                'wf_woocommerce_order_im_ex','edit-shop_order',
                'admin',
                'edit-shop_subscription', 'edit-shop_coupon',
                'woocommerce_page_wf_woocommerce_order_im_ex'
            );

        if (in_array($screen->id, $allowed_creen_id)) {
            wp_enqueue_style('woocommerce-order-xml-importer', plugins_url(basename(plugin_dir_path(WF_OrderImpExpXML_baseFILE)) . '/styles/wf-style.css', basename(__FILE__)), '', '1.0.0', 'screen');
            wp_enqueue_style('woocommerce-order-xml-importer2', plugins_url(basename(plugin_dir_path(WF_OrderImpExpXML_baseFILE)) . '/styles/jquery-ui.css', basename(__FILE__)), '', '1.0.0', 'screen');
            wp_enqueue_script('woocommerce-order-xml-importerjs', plugins_url(basename(plugin_dir_path(WF_OrderImpExpXML_baseFILE)) . '/js/woocommerce-order-xml-importer.js', basename(__FILE__)), array(), '2.0.0', true);
                    
        }
        
        wp_enqueue_script('wc-enhanced-select');
        //wp_enqueue_script('chosen');
        wp_enqueue_style('woocommerce_admin_styles', $wc_path . '/assets/css/admin.css');
        
        wp_localize_script('woocommerce-order-xml-importerjs', 'woocommerce_order_xml_import_params', array('calendar_icon' => plugins_url(basename(plugin_dir_path(WF_OrderImpExpXML_baseFILE)) . '/images/calendar.png', basename(__FILE__))));
        wp_localize_script('woocommerce-order-xml-importerjs', 'woocommerce_order_xml_cron_params', array('orderxml_auto_export' => 'Disabled', 'orderxml_auto_import' => 'Disabled'));
        wp_enqueue_script('jquery-ui-datepicker');
        // $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
        // wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css');
    }

    /**
     * Admin Screen output
     */
    public function output() {
        $tab = 'import';
        if(! empty( $_GET['page'] ))
        {
            if ( $_GET['page'] == 'wf_woocommerce_order_im_ex_xml' ) {
                $tab = 'importxml';
            }
        }
        if (!empty($_GET['tab'])) {

            if( $_GET['tab'] == 'export' ) {
                $tab = 'export';
            }
            else if ( $_GET['tab'] == 'settings' ) {
                $tab = 'settings';
            }
            else if ( $_GET['tab'] == 'coupon' ) {
                $tab = 'coupon';
            }
            else if ($_GET['tab'] == 'importxml') {
                $tab = 'importxml';
            } else if ($_GET['tab'] == 'settings') {
                $tab = 'settings';
            }else if($_GET['tab'] == 'help'){
                $tab = 'help';
            }

        }
        include( 'views/html-wf-admin-screen.php' );
    }

    public function add_order_bulk_actions() {
        global $post_type, $post_status;

        if ($post_type == 'shop_order' && $post_status != 'trash') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('select[name^="action"]').append($('<option>').val('download_to_general_xml_wf').text('<?php _e('Download as WooCommerce XML', 'wf_order_import_export') ?>'));
                    $('select[name^="action"]').append($('<option>').val('download_to_stamps_xml_wf').text('<?php _e('Download as Stamps XML', 'wf_order_import_export') ?>'));
                     $('select[name^="action"]').append($('<option>').val('download_to_fedex_xml_wf').text('<?php _e('Download as FedEx XML', 'wf_order_import_export') ?>'));
                    $('select[name^="action"]').append($('<option>').val('download_to_ups_xml_wf').text('<?php _e('Download as UPS WorldShip XML', 'wf_order_import_export') ?>'));
                    $('select[name^="action"]').append($('<option>').val('download_to_endicia_xml_wf').text('<?php _e('Download as Endicia XML', 'wf_order_import_export') ?>'));
                });
            </script>
            <?php
        }
    }

    /**
     * Order page bulk export action
     * 
     */
    public function process_order_bulk_actions()
    {
        global $typenow;
        if ($typenow == 'shop_order')
        {
            $wp_list_table = _get_list_table('WP_Posts_List_Table');
            $action = $wp_list_table->current_action();
            if (isset($_REQUEST['post'])) {
                $order_ids = array_map('absint', $_REQUEST['post']);
            }
            if (empty($order_ids)) {
                return;
            }
            @set_time_limit(0);
            include_once( 'exporter/class-OrderImpExpXML-base-exporter.php' );
            $order_imp_exp_obj = new OrderImpExpXMLBase_Exporter();
            switch ($action)
            {
                case 'download_to_general_xml_wf' :
                    $order_imp_exp_obj->export_formation('general',$order_ids);
                    break;
                case 'download_to_stamps_xml_wf' :
                    $order_imp_exp_obj->export_formation('stamps',$order_ids);
                    break;
                case 'download_to_fedex_xml_wf' :
                    $order_imp_exp_obj->export_formation('fedex',$order_ids);
                    break;
                case 'download_to_ups_xml_wf' :
                    $order_imp_exp_obj->export_formation('ups',$order_ids);
                    break;
                case 'download_to_endicia_xml_wf' :
                    $order_imp_exp_obj->export_formation('endicia',$order_ids);
                    break;
            }
        }
    }

    /**
     * Single order export
     */
    public function process_ajax_export_single_order() {

        if (!WF_Order_Import_Export_CSV::hf_user_permission()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wf_order_import_export'));
        }
        if (!check_admin_referer('wc_order_xml_export_single')) {
            wp_die(__('You have taken too long, please go back and try again.', 'wf_order_import_export'));
        }
        
        $order_id = !empty($_GET['order_id']) ? absint($_GET['order_id']) : '';
        if (!$order_id) {
            die;
        }
        $order_IDS = array(0 => $order_id);
        include_once( 'exporter/class-OrderImpExpXML-base-exporter.php' );
        $order_imp_exp_obj = new OrderImpExpXMLBase_Exporter();
        $order_imp_exp_obj->do_export('shop_order', $order_IDS);
        wp_redirect(wp_get_referer());
        exit;
    }

    /**
     * Admin page for importing
     */
    public function admin_import_page() {

        include( 'views/import/html-wf-import-orders-xml.php' );
        include( 'views/export/html-wf-export-orders-xml.php' );
    }

    /**
     * Admin Page for exporting
     */
    public function admin_orderxml_export_page() {
        include( 'views/export/html-wf-export-orders-xml.php' );
    }

     /**
     * Admin Page for exporting
     */
    public function admin_export_page() {
        include( 'views/export/html-wf-export-orders-xml.php' );
    }

    /**
     * Admin Page for settings
     */
    public function admin_settings_page() {
        include( 'views/settings/html-wf-settings.php' );
    }

    /**
     * Admin Page for help & documentation
     */
    public function admin_help_page() {
        include( 'views/help/html-wf-help.php' );
    }
    
}

new OrderImpExpXML_AdminScreen();
