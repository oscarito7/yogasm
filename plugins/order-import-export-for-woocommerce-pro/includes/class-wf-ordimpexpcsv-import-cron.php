<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_OrdImpExpCsv_ImportCron {

    public $settings;
    public $file_url;
    public $error_message;
    public $ftp_conn;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'wf_auto_import_schedule'));
        add_action('init', array($this, 'wf_new_scheduled_import_order'));
        add_action('wf_order_csv_im_ex_auto_import_order', array($this, 'wf_scheduled_import_order'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        //$this->settings_ftp_import = get_option('hf_order_importer_ftp', null);
        $this->imports_enabled = FALSE;
        if (isset($this->settings['ord_auto_import']) && ($this->settings['ord_auto_import'] === 'Enabled') && isset($this->settings['ord_enable_ftp_ie']) && $this->settings['ord_enable_ftp_ie'] === TRUE)
            $this->imports_enabled = TRUE;
    }

    public function wf_auto_import_schedule($schedules) {
        if ($this->imports_enabled) {
            $import_interval = $this->settings['ord_auto_import_interval'];
            if ($import_interval) {
                $schedules['ord_import_interval'] = array(
                    'interval' => (int) $import_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $import_interval)
                );
            }
        }
        return $schedules;
    }

    public function wf_new_scheduled_import_order() {
        if ($this->imports_enabled) {
            if (!wp_next_scheduled('wf_order_csv_im_ex_auto_import_order')) {
                $start_time = $this->settings['ord_auto_import_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $import_interval = $this->settings['ord_auto_import_interval'];
                    $start_timestamp = strtotime("now +{$import_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'ord_import_interval', 'wf_order_csv_im_ex_auto_import_order');
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

    public function wf_scheduled_import_order() {

        if (!defined('WP_LOAD_IMPORTERS'))
            define('WP_LOAD_IMPORTERS', true);
        if (!class_exists('WooCommerce')) :
            require ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
        endif;
        $multi_csv_import_enabled = $this->settings['csv_ordercsv_multiple_files_import'];
        $arg['delimiter'] = (!empty($this->settings['ord_auto_import_delimiter']) ) ? $this->settings['ord_auto_import_delimiter'] : ',';
        $arg['createuser'] = !empty($this->settings['wtcreateuser_cron']) ? $this->settings['wtcreateuser_cron'] : 0;
        $arg['ord_link_using_sku'] = !empty($this->settings['ord_link_using_sku_cron']) ? $this->settings['ord_link_using_sku_cron'] : 0;
        WF_OrdImpExpCsv_ImportCron::order_importer($arg);
        $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', "---------------" . __('Start: Cron Import started at ', 'wf_order_import_export') . date('Y-m-d H:i:s') . "---------------");
        if ($this->handle_ftp_for_autoimport($multi_csv_import_enabled)) {
            $mapping = array();
            $eval_field = '';
            $start_pos = 0;
            $end_pos = '';
            if ($this->settings['ord_auto_import_profile'] !== '') {
                $profile_array = get_option('wf_order_csv_imp_exp_mapping');
                $mapping = $profile_array[$this->settings['ord_auto_import_profile']][0];
                $eval_field = $profile_array[$this->settings['ord_auto_import_profile']][1];
                $start_pos = 0;
                $end_pos = '';
            }
//            else {
//                $this->error_message = 'Please set a mapping profile';
//                $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Failed processing import. Reason:' . $this->error_message, 'wf_order_import_export'));
//            }
            if ($this->settings['ord_auto_import_merge']) {
                $_GET['merge'] = 1;
            } else {
                $_GET['merge'] = 0;
            }
            $delete_server_file = apply_filters('hf_delete_remote_csv_after_order_import', FALSE); // To delete the CSV file from server after importing the CSV.
            if ($multi_csv_import_enabled && is_array($this->file_url)) {
                foreach ($this->file_url as $key => $file_url) {
                    $GLOBALS['WF_CSV_Order_Import']->import_start($file_url, $mapping, $start_pos, $end_pos, $eval_field);
                    $GLOBALS['WF_CSV_Order_Import']->import();
                    $GLOBALS['WF_CSV_Order_Import']->import_end();

                    unlink($file_url);
                }

                if ($delete_server_file) {
                    $this->delete_file_from_server($multi_csv_import_enabled);
                }
            } else {
                $GLOBALS['WF_CSV_Order_Import']->import_start($this->file_url, $mapping, $start_pos, $end_pos, $eval_field);
                $GLOBALS['WF_CSV_Order_Import']->import();
                $GLOBALS['WF_CSV_Order_Import']->import_end();
                unlink($this->file_url);
                if ($delete_server_file) {
                    $this->delete_file_from_server();
                }
            }
            die();
        } else {
            $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
        }
    }

    public function clear_wf_scheduled_import_order() {
        wp_clear_scheduled_hook('wf_order_csv_im_ex_auto_import_order');
    }

    private function handle_ftp_for_autoimport($multi_csv_import_enabled = false) {


        $enable_ftp_ie = $this->settings['ord_enable_ftp_ie'];
        if (!$enable_ftp_ie)
            return false;

        $ftp_server = $this->settings['ord_ftp_server'];
        $ftp_user = $this->settings['ord_ftp_user'];
        $ftp_password = $this->settings['ord_ftp_password'];
        $ftp_port = $this->settings['ord_ftp_port'];
        $use_ftps = $this->settings['ord_use_ftps'];
        $use_pasv = $this->settings['ord_use_pasv'];
        $ftp_server_path = $this->settings['ord_auto_import_file'];

//        $local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import.csv';
        $wp_upload_dir = wp_upload_dir();
        $local_file = $wp_upload_dir['path'] . '/ord-temp-import.csv';

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
                if ($multi_csv_import_enabled) {
                    $server_csv_files = $sftp_import->nlist($server_file, array('xml', 'csv'));
                    if (is_array($server_csv_files)) {
                        foreach ($server_csv_files as $key => $server_file_name) {
                            $file_contents = $sftp_import->get_contents($server_file . '/' . $server_file_name);
                            if (!empty($file_contents)) {
                                file_put_contents(ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.csv", $file_contents);
                                $this->error_message = "";
                                $success = true;
                                unset($file_contents);
                            } else {
                                $this->error_message = __("Failed to Download Specified file in sFTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>$local_file</b>.", 'wf_order_import_export');
                                return false;
                            }
                        }
                    }
                } else {
                    $file_contents = $sftp_import->get_contents($server_file);

                    if (!empty($file_contents)) {
                        file_put_contents($local_file, $file_contents);

                        $this->error_message = "";
                        $success = true;
                    } else {
                        $this->error_message = "Failed to Download Specified file in sFTP Server File Path3.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>$local_file</b> .\n";
                        $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Fetching file failed. Reason:' . $this->error_message, 'wf_order_import_export'));
                    }
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

            if ($multi_csv_import_enabled) {
                $server_csv_files = ftp_nlist($ftp_conn, $ftp_server_path . "/");
                if ($server_csv_files) {
                    foreach ($server_csv_files as $key => $server_file1) {
                        
                        if (!in_array(substr($server_file1, -3), array('xml', 'csv'))) {
                                unset($server_csv_files[$key]);
                                continue;
                            }
                            
                        if (@ftp_get($ftp_conn, ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.csv", $server_file1, FTP_BINARY)) {
                            $this->error_message = "";
                            $success = true;
                        } else {
                            $this->error_message = __("Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>plugins/order-import-export-for-woocommerce-pro/temp-import.csv</b> .\n");
                            return false;
                        }
                    }
                }

                if (!$success) {
                    return FALSE;
                    die($this->error_message);
                }
            } else {
                if (empty($this->error_message)) {

                    if (@ftp_get($ftp_conn, $local_file, $server_file, FTP_BINARY)) {
                        $this->error_message = "";
                        $success = true;
                    } else {
                        $this->error_message = "Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.\n";
                    }
                }
            }

            @ftp_close($ftp_conn);
        }
        if ($success) {
            if ($multi_csv_import_enabled) {
                if ($server_csv_files) {

                    foreach ($server_csv_files as $key => $server_file) {

                        $file = ABSPATH . "wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import_$key.csv";
                        if (file_exists($file)) { 
                            if ($GLOBALS['WF_CSV_Order_Import']->hf_mime_content_type($file) === 'application/xml' || $GLOBALS['WF_CSV_Order_Import']->hf_mime_content_type($file) === 'text/xml') // introduced XML import
                                $file = $GLOBALS['WF_CSV_Order_Import']->xml_import($file);
                            $this->file_url[] = $file;
                        }
                    }
                }
            } else {
                $file = $local_file;
                if ($GLOBALS['WF_CSV_Order_Import']->hf_mime_content_type($file) === 'application/xml' || $GLOBALS['WF_CSV_Order_Import']->hf_mime_content_type($file) === 'text/xml') // introduced XML import
                    $file = $GLOBALS['WF_CSV_Order_Import']->xml_import($file);
                $this->file_url = $file;
//                $this->file_url =  $local_file;
            }
        } else {
            die($this->error_message);
        }
        return true;
    }

    public static function order_importer($arg) {
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
        $GLOBALS['WF_CSV_Order_Import']->delimiter = (!empty($arg['delimiter']) ? $arg['delimiter'] : ',');
        $GLOBALS['WF_CSV_Order_Import']->wtcreateuser = !empty($arg['createuser']) ? $arg['createuser'] : 0;
        $GLOBALS['WF_CSV_Order_Import']->ord_link_using_sku = !empty($arg['ord_link_using_sku']) ? $arg['ord_link_using_sku'] : 0;
    }

    public function delete_file_from_server($multi_csv_import_enabled = false) {
        $enable_ftp_ie = $this->settings['ord_enable_ftp_ie'];
        if (!$enable_ftp_ie)
            return false;

        $ftp_server = $this->settings['ord_ftp_server'];
        $ftp_user = $this->settings['ord_ftp_user'];
        $ftp_password = $this->settings['ord_ftp_password'];
        $ftp_port = !empty($this->settings['ord_ftp_port']) ? $this->settings['ord_ftp_port'] : 21;
        $use_ftps = $this->settings['ord_use_ftps'];
        $use_pasv = $this->settings['ord_use_pasv'];
        $ftp_server_path = isset($this->settings['ord_auto_import_file']) ? $this->settings['ord_auto_import_file'] : null;

        $server_file = $ftp_server_path;

        $this->error_message = "";
        $success = false;

        $ftp_conn = $use_ftps ? @ftp_ssl_connect($ftp_server, $ftp_port) : ftp_connect($ftp_server, $ftp_port);
        if ($ftp_conn == false) {
            $this->error_message = __("Could not connect to the host. Server Host Name / IP or Port may be wrong.\n");
            $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Failed to delete file from server. Reason:' . $this->error_message, 'wf_order_import_export'));
        }

        if (empty($this->error_message)) {
            if (@ftp_login($ftp_conn, $ftp_user, $ftp_password) == false) {
                $this->error_message = __("Connected to host but could not login. Server UserID or Password may be wrong or Try with / without FTPS .\n");
                $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Failed to delete file from server. Reason:' . $this->error_message, 'wf_order_import_export'));
            }
        }
        if (empty($this->error_message)) {
            if ($use_pasv) {
                ftp_pasv($ftp_conn, TRUE);
            }
            if ($multi_csv_import_enabled == true) {
                $server_csv_files = ftp_nlist($ftp_conn, $ftp_server_path . "/*.csv");
                if ($server_csv_files) {
                    $s_count = $f_count = 0;
                    foreach ($server_csv_files as $key => $server_file1) {
                        if (ftp_delete($ftp_conn, $server_file1)) {
                            $s_count++;
                        } else {
                            $f_count++;
                        }
                    }

                    if ($s_count > 0) {
                        $success = true;
                    }
                    if ($f_count > 0) {
                        $this->error_message = __("Failed to Delete Specified file in FTP Server File Path.");
                        $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Failed to delete file from server. Reason:' . $this->error_message, 'wf_order_import_export'));
                    }
                }
            } else {
                if (ftp_delete($ftp_conn, $server_file)) {
                    $success = true;
                } else {
                    $this->error_message = __("Could not delete $file");
                    $GLOBALS['WF_CSV_Order_Import']->hf_order_log_data_change('hf-order-csv-import', __('Failed to delete file from server. Reason:' . $this->error_message, 'wf_order_import_export'));
                }
            }
        }

        if ($ftp_conn) {
            ftp_close($ftp_conn);
        }


        if ($success) {
            return true;
        } else {
            die($this->error_message);
        }

        return true;
    }

}
