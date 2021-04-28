<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_SubscriptionOrderImpExpCsv_ImportCron {

    public $settings;
    public $file_url;
    public $error_message;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'hf_auto_import_schedule'));
        add_action('init', array($this, 'hf_new_scheduled_import'));
        add_action('hf_subscription_order_csv_im_ex_auto_import', array($this, 'hf_scheduled_import_subscription_order'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        //$this->settings_ftp_import = get_option('hf_subscription_order_importer_ftp', null);
        $this->imports_enabled = FALSE;
        if (isset($this->settings['sbc_auto_import']) && ($this->settings['sbc_auto_import'] === 'Enabled') && isset($this->settings['sbc_enable_ftp_ie']) && $this->settings['sbc_enable_ftp_ie'] === TRUE)
            $this->imports_enabled = TRUE;
        
    }

    public function hf_auto_import_schedule($schedules) {
        if ($this->imports_enabled) {
            $import_interval = $this->settings['sbc_auto_import_interval'];
            if ($import_interval) {
                $schedules['sbc_import_interval'] = array(
                    'interval' => (int) $import_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $import_interval)
                );
            }
        }
        return $schedules;
    }

    public function hf_new_scheduled_import() {
        if ($this->imports_enabled) {
            if (!wp_next_scheduled('hf_subscription_order_csv_im_ex_auto_import')) {
                $start_time = $this->settings['sbc_auto_import_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $import_interval = $this->settings['sbc_auto_import_interval'];
                    $start_timestamp = strtotime("now +{$import_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'sbc_import_interval', 'hf_subscription_order_csv_im_ex_auto_import');
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

    public function hf_scheduled_import_subscription_order() {
         
        //error_log("test run by wp-cron" , 3 , ABSPATH . '/wp-content/uploads/wc-logs/my-cron-log.txt');
        define( 'WP_LOAD_IMPORTERS', true );
        if ( ! class_exists( 'WooCommerce' ) ) :
            require  ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
        endif;

        self::subcription_order_importer();
            
        if($this->handle_ftp_for_autoimport()){
            $mapping = array();
            $eval_field = '';
            $start_pos = 0;
            $end_pos = '';
            
            if($this->settings['sbc_auto_import_profile']!== ''){
                $profile_array = get_option('wf_subcription_order_csv_imp_exp_mapping');
                $mapping = $profile_array[$this->settings['sbc_auto_import_profile']][0];
                $eval_field = $profile_array[$this->settings['sbc_auto_import_profile']][1];
            }else{
                $this->error_message = 'Please set a mapping profile';
                $GLOBALS['HF_CSV_Subscription_Order_Import']->log->add( 'hf-subscription-csv-import', __( 'Failed processing import. Reason:'.$this->error_message, 'wf_order_import_export' ) );
            }
        if($this->settings['sbc_auto_import_merge']){ $_GET['merge'] = 1; } else { $_GET['merge'] = 0; }    
          
        //echo wp_next_scheduled('wf_woocommerce_csv_im_ex_auto_import_products').'<br/>';
        //echo date('Y-m-d H:i:s' , wp_next_scheduled('wf_woocommerce_csv_im_ex_auto_import_products'));
        //echo $_GET['merge'];exit;
        
        $GLOBALS['HF_CSV_Subscription_Order_Import']->import_start( $this->file_url, $mapping, $start_pos, $end_pos, $eval_field );
	$GLOBALS['HF_CSV_Subscription_Order_Import']->import();
	$GLOBALS['HF_CSV_Subscription_Order_Import']->import_end();
        
        unlink($this->file_url);
        
        //do_action('wf_new_scheduled_import');
        //wp_clear_scheduled_hook('wf_woocommerce_csv_im_ex_auto_import_products');
        //do_action('wf_new_scheduled_import');
        
        die();
        }else{
            $GLOBALS['HF_CSV_Subscription_Order_Import']->log->add( 'hf-subscription-csv-import', __( 'Fetching file failed. Reason:'.$this->error_message, 'wf_order_import_export' ) );
        }
        
    }

    public function clear_hf_scheduled_import() {
        wp_clear_scheduled_hook('hf_subscription_order_csv_im_ex_auto_import');
    }    
    
    private function handle_ftp_for_autoimport(){            
                
        $enable_ftp_ie          = $this->settings['sbc_enable_ftp_ie' ];
        if(!$enable_ftp_ie) return false;

        $ftp_server             = $this->settings[ 'sbc_ftp_server' ];
        $ftp_user               = $this->settings[ 'sbc_ftp_user' ];
        $ftp_password		= $this->settings[ 'sbc_ftp_password' ] ;
        $ftp_port		= $this->settings[ 'sbc_ftp_port' ] ;
        $use_ftps               = $this->settings[ 'sbc_use_ftps' ];
        $use_pasv               = $this->settings[ 'sbc_use_pasv' ];
        $ftp_server_path        = $this->settings[ 'sbc_auto_import_file' ];


//		$local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import.csv';
                $wp_upload_dir = wp_upload_dir();
                $local_file = $wp_upload_dir['path'].'/sub-temp-import.csv';
        $server_file = $ftp_server_path;

        // if have SFTP Add-on for Import Export for WooCommerce 
        if (class_exists('class_wf_sftp_import_export')) {
            $sftp_import = new class_wf_sftp_import_export();
            if (!$sftp_import->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                $this->error_message = "Not able to connect to the server please check <b>sFTP Server Host / IP</b> and <b>Port number</b>. \n";
                $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
            }

            if (empty($server_file)) {
                $this->error_message = "Please completely fill the sFTP Details. \n";
                $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
            } else {
                $file_contents = $sftp_import->get_contents($server_file);

                if (!empty($file_contents)) {
                    file_put_contents($local_file, $file_contents);

                    $this->error_message = "";
                    $success = true;
                } else {
                    $this->error_message = "Failed to Download Specified file in sFTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>$local_file</b> .\n";
                    $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
                }
            }
        } else {

            $ftp_conn = $use_ftps ? @ftp_ssl_connect($ftp_server, $ftp_port) : @ftp_connect($ftp_server, $ftp_port);
            $this->error_message = "";
            $success = false;
            if ($ftp_conn == false) {
                $this->error_message = "Not able to connect to the server please check <b>FTP Server Host / IP</b> and <b>Port number</b>. \n";
            }

            if (empty($this->error_message)) {
                if (@ftp_login($ftp_conn, $ftp_user, $ftp_password) == false) {
                    $this->error_message = "Connected to FTP Server.<br/>But, not able to login please check <b>FTP User Name</b> and <b>Password.</b>\n";
                }
            }
            if ($use_pasv)
                ftp_pasv($ftp_conn, TRUE);
            if (empty($this->error_message)) {

                if (@ftp_get($ftp_conn, $local_file, $server_file, FTP_BINARY)) {
                    $this->error_message = "";
                    $success = true;
                } else {
                    $this->error_message = "Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.\n";
                }
            }

            @ftp_close($ftp_conn);
        }
        if($success){
			$this->file_url = $local_file;
        }else{
                die($this->error_message);
        }	
        return true;
    }
        
    public static function subcription_order_importer() {
        if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
                return;
        }

        self::load_wp_importer();

        // includes
        require_once 'importer/class-wf-subscription-orderimpexpcsv-order-import.php';
        require_once 'importer/class-wf-csv-subscription-parser.php';

        if (!class_exists('WC_Logger')) {
        $class_wc_logger = ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-logger.php';
        if (file_exists($class_wc_logger)) {
        require $class_wc_logger;
        }
        }

        $class_wc_logger = ABSPATH . 'wp-includes/pluggable.php';
        require_once($class_wc_logger);
        wp_set_current_user(1); // escape user access check while running cron

        $GLOBALS['HF_CSV_Subscription_Order_Import'] = new wf_subcription_orderImpExpCsv_Order_Import();
        $GLOBALS['HF_CSV_Subscription_Order_Import']->import_page = 'woocommerce_subscription_csv_cron';
        $GLOBALS['HF_CSV_Subscription_Order_Import']->delimiter = ','; // need to give option in settingn , if some queries are coming
    }
}