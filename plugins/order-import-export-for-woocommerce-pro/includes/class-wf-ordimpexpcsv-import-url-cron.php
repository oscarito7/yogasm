<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_OrdImpExpCsv_ImportUrlCron {

    public $settings;
    public $file_url;
    public $error_message;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'wf_auto_import_url_schedule'));
        add_action('init', array($this, 'wf_new_scheduled_import_order_url'));
        add_action('wf_woocommerce_csv_im_ex_auto_import_orders_from_url', array($this, 'wf_scheduled_import_url_orders'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $this->imports_enabled = FALSE;
        if (isset($this->settings['ord_enable_url_ie']) && $this->settings['ord_enable_url_ie'] === TRUE)
            $this->imports_enabled = TRUE;
    }

    public function wf_auto_import_url_schedule($schedules) {
        if ($this->imports_enabled) {
            $import_interval = $this->settings['ord_auto_import_url_interval'];
            if ($import_interval) {
                $schedules['ord_url_import_interval'] = array(
                    'interval' => (int) $import_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $import_interval)
                );
            }
        }
        return $schedules;
    }

    public function wf_new_scheduled_import_order_url() {
        if ($this->imports_enabled) {
            if (!wp_next_scheduled('wf_woocommerce_csv_im_ex_auto_import_orders_from_url')) {
                $start_time = $this->settings['ord_auto_import_url_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $import_interval = $this->settings['ord_auto_import_url_interval'];
                    $start_timestamp = strtotime("now +{$import_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'ord_url_import_interval', 'wf_woocommerce_csv_im_ex_auto_import_orders_from_url');
            }
        }
    }

    public static function load_wp_importer() {
        // Load Importer API
        require_once ABSPATH . 'wp-admin/includes/import.php';

        if (!class_exists('WP_Importer')) {
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            if (file_exists($class_wp_importer)) {
                require $class_wp_importer;
            }
        }
    }

    public function wf_scheduled_import_url_orders() {

        if (!defined('WP_LOAD_IMPORTERS'))
            define('WP_LOAD_IMPORTERS', true);
        if (!class_exists('WooCommerce')) :
            require ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
        endif;
        $delimiter = (!empty($this->settings['ord_auto_import_delimiter']) ) ? $this->settings['ord_auto_import_delimiter'] : ',';
        $multi_csv_url_import_array = apply_filters('wt_multi_csv_url_order_import_array', FALSE);
        WF_OrdImpExpCsv_ImportUrlCron::order_importer($delimiter);
        $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', "---------------".__('Start: Cron Import started at ', 'wf_order_import_export').date('Y-m-d H:i:s')."---------------");
        if ($this->handle_ftp_for_autoimport($multi_csv_url_import_array)) {
            $mapping = array();
            $eval_field = '';
            $start_pos = 0;
            $end_pos = '';
            if ($this->settings['ord_auto_import_url_profile'] !== '') {
                $profile_array = get_option('wf_order_csv_imp_exp_mapping');
                $mapping = $profile_array[$this->settings['ord_auto_import_url_profile']][0];
                $eval_field = $profile_array[$this->settings['ord_auto_import_url_profile']][1];
                $start_pos = 0;
                $end_pos = '';
            }
            if ($this->settings['ord_auto_import_url_merge']) {
                $_GET['merge'] = 1;
            } else {
                $_GET['merge'] = 0;
            }
            if($multi_csv_url_import_array != FALSE && is_array($this->file_url)){
                foreach ($this->file_url as $key => $file_url) {
                    $GLOBALS['WF_CSV_Order_Import']->import_start($file_url, $mapping, $start_pos, $end_pos, $eval_field);
                    $GLOBALS['WF_CSV_Order_Import']->import();
                    $GLOBALS['WF_CSV_Order_Import']->import_end();

                    unlink($file_url);
                    
                }
            }else{
                $GLOBALS['WF_CSV_Order_Import']->import_start(ABSPATH .$this->file_url, $mapping, $start_pos, $end_pos, $eval_field);
                $GLOBALS['WF_CSV_Order_Import']->import();
                $GLOBALS['WF_CSV_Order_Import']->import_end();
                
                unlink($this->file_url);
            }
            $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', '---------------'.__('End: Cron Import (URL) ended at ', 'wf_order_import_export').date('Y-m-d H:i:s')."--------------- \n");
            die();
        } else {
            $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
            $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', '---------------'.__('End: Cron Import (URL) ended with errors at ', 'wf_order_import_export').date('Y-m-d H:i:s')."--------------- \n");
        }
    }

    public function clear_wf_scheduled_import_url_orders() {
        wp_clear_scheduled_hook('wf_woocommerce_csv_im_ex_auto_import_orders_from_url');
    }

    private function handle_ftp_for_autoimport($multi_csv_url_import_array = false) {
        if ($multi_csv_url_import_array != false && is_array($multi_csv_url_import_array) && !empty($multi_csv_url_import_array)) {
            foreach ($multi_csv_url_import_array as $key => $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $file_url = $GLOBALS['WF_CSV_Order_Import']->get_data_from_url($url);
                    $copy_to= substr($file_url,0, -8).$key.'.csv.txt';
                    copy($file_url, $copy_to);
                    $this->file_url[]=$copy_to;
                    unlink($file_url);
                }
            }
            if (!empty($this->file_url)) {
                return true;
            } else {
                $this->error_message = __("Sorry, The entered URL is not valid.", 'wf_order_import_export');
                die($this->error_message);
            }
        } else {
            if (filter_var($this->settings['ord_auto_import_url'], FILTER_VALIDATE_URL)) {
                $this->file_url = $GLOBALS['WF_CSV_Order_Import']->get_data_from_url($this->settings['ord_auto_import_url']);
                return true;
            } else {
                $this->error_message = __("Sorry, The entered URL is not valid.", 'wf_order_import_export');
                die($this->error_message);
            }
        }
    }

    public static function order_importer($delimiter) {
        if (!defined('WP_LOAD_IMPORTERS')) {
            return;
        }

        self::load_wp_importer();

        // includes
        require_once 'importer/class-wf-orderimpexpcsv-order-import.php';
        require_once 'importer/class-wf-csv-parser.php';

        if (!class_exists('WC_Logger')) {
            $class_wc_logger = ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-logger.php';
            if (file_exists($class_wc_logger)) {
                require $class_wc_logger;
            }
        }

        $class_wc_logger = ABSPATH . 'wp-includes/pluggable.php';
        require_once($class_wc_logger);
        wp_set_current_user(1); // escape user access check while running cron

        $GLOBALS['WF_CSV_Order_Import'] = new WF_OrderImpExpCsv_Order_Import();
        $GLOBALS['WF_CSV_Order_Import']->import_page = 'order_csv_cron';
        $GLOBALS['WF_CSV_Order_Import']->delimiter = $delimiter;
    }
}
