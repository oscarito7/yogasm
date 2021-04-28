<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_OrdImpExpCsv_Cron {

    public $settings;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'wf_auto_export_schedule'));
        add_action('init', array($this, 'wf_new_scheduled_export_order'));
        add_action('wf_order_csv_im_ex_auto_export_order', array($this, 'wf_scheduled_export_order'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $this->exports_enabled = FALSE;
        if (isset($this->settings['ord_auto_export']) && ($this->settings['ord_auto_export'] === 'Enabled') && isset($this->settings['ord_enable_ftp_ie']) && $this->settings['ord_enable_ftp_ie'] === TRUE)
            $this->exports_enabled = TRUE;
    }

    public function wf_auto_export_schedule($schedules) {
        if ($this->exports_enabled) {
            $export_interval = $this->settings['ord_auto_export_interval'];
            if ($export_interval) {
                $schedules['ord_export_interval'] = array(
                    'interval' => (int) $export_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $export_interval)
                );
            }
        }
        return $schedules;
    }

    public function wf_new_scheduled_export_order() {
        // echo wp_next_scheduled('wf_order_csv_im_ex_auto_export_order'); exit;
        if ($this->exports_enabled) {

            if (!wp_next_scheduled('wf_order_csv_im_ex_auto_export_order')) {
                $start_time = $this->settings['ord_auto_export_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $export_interval = $this->settings['ord_auto_export_interval'];
                    $start_timestamp = strtotime("now +{$export_interval} minutes");
                }
                // die($start_timestamp);
                wp_schedule_event($start_timestamp, 'ord_export_interval', 'wf_order_csv_im_ex_auto_export_order');
            }
        }
    }

    public function wf_scheduled_export_order() {
        include_once( 'exporter/class-wf-orderimpexpcsv-exporter.php' );
        if (isset($this->settings['ord_auto_export_profile'])) {
            $_POST['auto_export_profile'] = $this->settings['ord_auto_export_profile'];
        } else {
            $_POST['auto_export_profile'] = '';
        }
        if (isset($this->settings['ord_auto_export_order_status'])) {
            $_POST['order_status'] = $this->settings['ord_auto_export_order_status'];
        }
        if (isset($this->settings['ord_auto_export_products'])) {
            $_POST['products'] = $this->settings['ord_auto_export_products'];
        }
        if (isset($this->settings['include_meta'])) {
            $_POST['include_meta'] = $this->settings['include_meta'];
        }
        if (isset($this->settings['ord_auto_date_from'])) {
            $_POST['start_date'] = $this->settings['ord_auto_date_from'];
        }
        if (isset($this->settings['ord_auto_date_to'])) {
            $_POST['end_date'] = $this->settings['ord_auto_date_to'];
        }
        
         if (isset($this->settings['ord_auto_export_email_order'])) {
            $_POST['email'] = $this->settings['ord_auto_export_email_order'];
        }
        if (isset($this->settings['ord_auto_export_coupon_order'])) {
            $_POST['coupons'] = $this->settings['ord_auto_export_coupon_order'];
        }
 
        WF_OrderImpExpCsv_Exporter::do_export('shop_order');
    }

    public function clear_wf_scheduled_export_order() {
        wp_clear_scheduled_hook('wf_order_csv_im_ex_auto_export_order');
    }

}
