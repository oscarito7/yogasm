<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_OrderImpExpXML_ImportCron {

    public $settings;
    public $file_url;
    public $error_message;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'wf_auto_import_schedule'));
        add_action('init', array($this, 'wf_new_scheduled_import_orderxml'));
        add_action('wf_order_xml_im_ex_auto_import_orderxml', array($this, 'wf_scheduled_import_orderxml'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $this->imports_enabled = FALSE;
        if (isset($this->settings['xml_orderxml_auto_import']) && ($this->settings['xml_orderxml_auto_import'] === 'Enabled') && isset($this->settings['xml_enable_ftp_ie']) && ($this->settings['xml_enable_ftp_ie'] === TRUE))
            $this->imports_enabled = TRUE;
    }

    public function wf_auto_import_schedule($schedules) {
        if ($this->imports_enabled) {
            $import_interval = $this->settings['xml_orderxml_auto_import_interval'];
            if ($import_interval) {
                $schedules['orderxml_import_interval'] = array(
                    'interval' => (int) $import_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $import_interval)
                );
            }
        }
        return $schedules;
    }

    public function wf_new_scheduled_import_orderxml() {
        if ($this->imports_enabled) {
            if (!wp_next_scheduled('wf_order_xml_im_ex_auto_import_orderxml')) {
                $start_time = $this->settings['xml_orderxml_auto_import_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $import_interval = $this->settings['xml_orderxml_auto_import_interval'];
                    $start_timestamp = strtotime("now +{$import_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'orderxml_import_interval', 'wf_order_xml_im_ex_auto_import_orderxml');
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

    public function wf_scheduled_import_orderxml() {

        //error_log("test run by wp-cron" , 3 , ABSPATH . '/wp-content/uploads/wc-logs/my-cron-log.txt');
        define('WP_LOAD_IMPORTERS', true);
        if (!class_exists('WooCommerce')) :
            require ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
        endif;
        $multi_xml_import_enabled = $this->settings['xml_orderxml_multiple_files_import'];
        WF_OrderImpExpXML_ImportCron::orderxml_importer();
        if ($this->handle_ftp_for_autoimport($multi_xml_import_enabled)) {
            $import_type = 'general'; // default set for general , we set more later by dynamic
            if ($this->settings['xml_orderxml_auto_import_merge']) {
                $import_decision = 'overwrite';
            } else {
                $import_decision = 'skip';
            }
            
            if ($multi_xml_import_enabled && is_array($this->file_url)) {
                
                foreach ($this->file_url as $key => $file_url) {
                    $GLOBALS['WF_OrderImpExpXML_OrderImport']->import_start($file_url);
                    $GLOBALS['WF_OrderImpExpXML_OrderImport']->import($import_type, $import_decision);
                    $GLOBALS['WF_OrderImpExpXML_OrderImport']->import_end();

                    unlink($file_url);
                    
                }
            } else {
                $GLOBALS['WF_OrderImpExpXML_OrderImport']->import_start($this->file_url);
                $GLOBALS['WF_OrderImpExpXML_OrderImport']->import($import_type, $import_decision);
                $GLOBALS['WF_OrderImpExpXML_OrderImport']->import_end();
                
                unlink($this->file_url);
            }
            
            die();
        } else {
            $GLOBALS['WF_OrderImpExpXML_OrderImport']->log->add('order-xml-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
        }
    }

    public function clear_wf_scheduled_import_orderxml() {
        wp_clear_scheduled_hook('wf_orderxml_im_ex_auto_import_orderxml');
    }

    private function handle_ftp_for_autoimport($multi_xml_import_enabled = false) {


        $enable_ftp_ie = $this->settings['xml_enable_ftp_ie'];
        if (!$enable_ftp_ie)
            return false;

        $ftp_server = $this->settings['xml_ftp_server'];
        $ftp_user = $this->settings['xml_ftp_user'];
        $ftp_password = $this->settings['xml_ftp_password'];
        $ftp_port = $this->settings['xml_ftp_port'];
        $use_ftps = $this->settings['xml_use_ftps'];
        $use_pasv = $this->settings['xml_use_pasv'];
        $ftp_server_path = $this->settings['xml_orderxml_auto_import_file'];

//        $local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/sample-files/temp-import.xml';
        
        $wp_upload_dir = wp_upload_dir();
        $local_file = $wp_upload_dir['path'].'/ord-temp-import.xml';
        $server_file = $ftp_server_path;



        // if have SFTP Add-on for Import Export for WooCommerce 
        if (class_exists('class_wf_sftp_import_export')) {
            $sftp_import = new class_wf_sftp_import_export();
            if (!$sftp_import->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                $error_message = "Not able to connect to the server please check <b>sFTP Server Host / IP</b> and <b>Port number</b>. \n";
            }

            if (empty($server_file)) {
                $error_message = "Please Complete fill the sFTP Details. \n";
            } else {
                if ($multi_xml_import_enabled) {
                    $server_xml_files = $sftp_import->nlist($server_file, array('xml'));
                    if (is_array($server_xml_files)) {
                        foreach ($server_xml_files as $key => $server_file_name) {
                            $file_contents = $sftp_import->get_contents($server_file . '/' . $server_file_name);
                            if (!empty($file_contents)) {
                                file_put_contents(ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.xml", $file_contents);
                                $this->error_message = "";
                                $success = true;
                                unset($file_contents);
                            } else {
                                $this->error_message = __("Failed to Download Specified file in sFTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>plugins/product-csv-import-export-for-woocommerce/temp-import.csv</b>.");
                                return false;
                            }
                        }
                    }
                } else {
                    $file_contents = $sftp_import->get_contents($server_file);

                    if (!empty($file_contents)) {
                        file_put_contents($local_file, $file_contents);

                        $error_message = "";
                        $success = true;
                    } else {
                        $error_message = "Failed to Download Specified file in sFTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>$local_file</b> .\n";
                    }
                }
            }
        } else {

            $ftp_conn = $use_ftps ? @ftp_ssl_connect($ftp_server, $ftp_port) : ftp_connect($ftp_server, $ftp_port);
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
            
            if($multi_xml_import_enabled){
                $server_xml_files = ftp_nlist($ftp_conn, $ftp_server_path."/*.xml");
                if ($server_xml_files) {
                    foreach ($server_xml_files as $key => $server_file1) {
                        if (@ftp_get($ftp_conn, ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.xml", $server_file1, FTP_BINARY)) {
                            $this->error_message = "";
                            $success = true;
                        } else {
                            $this->error_message = __("Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>plugins/order-import-export-for-woocommerce-pro/temp-import.xml</b> .\n");
                            return false;
                        }
                    }
                }

                if (!$success) {
                    return FALSE;
                    die($this->error_message);
                }
            } else {
                if (@ftp_get($ftp_conn,  $local_file, $server_file, FTP_BINARY)) {
                    $this->error_message = "";
                    $success = true;
                } else {
                    $this->error_message = "Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.\n";
                }
            }

            @ftp_close($ftp_conn);
        }
        if($success){
            if($multi_xml_import_enabled){
                if ($server_xml_files) {
                    foreach ($server_xml_files as $key => $server_file) {
                        if (file_exists(ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.xml")) {
                            
                            $this->file_url[] = ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.xml";
                        }
                    }
                }
            } else {
                $this->file_url = $local_file;
            }
        } else {
            return FALSE;
            die($this->error_message);
        }
        return true;
    }

    public static function orderxml_importer() {
        if (!defined('WP_LOAD_IMPORTERS')) {
            return;
        }

        self::load_wp_importer();

        // includes
        require_once 'importer/class-OrderImpExpXML-order-import.php';
        require_once 'importer/class-OrderImpExpXML-base-xml-parser.php';

        if (!class_exists('WC_Logger')) {
            $class_wc_logger = ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-logger.php';
            if (file_exists($class_wc_logger)) {
                require $class_wc_logger;
            }
        }

        $class_wc_logger = ABSPATH . 'wp-includes/pluggable.php';
        require_once($class_wc_logger);
        wp_set_current_user(1); // escape user access check while running cron

        $GLOBALS['WF_OrderImpExpXML_OrderImport'] = new OrderImpExpXML_OrderImport();
        $GLOBALS['WF_OrderImpExpXML_OrderImport']->import_page = 'woocommerce_wf_import_order_xml_cron';
        //$GLOBALS['WF_OrderImpExpXML_OrderImport']->delimiter = ','; // need to give option in settingn , if some queries are coming
    }

}
