<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_SubcriptionOrderImpExpCsv_Cron {

    public $settings;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'hf_auto_export_schedule'));
        add_action('init', array($this, 'hf_new_scheduled_export'));
        add_action('hf_subscription_order_csv_im_ex_auto_export', array($this, 'hf_scheduled_export_subscription_orders'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $this->exports_enabled = FALSE;
        if (isset($this->settings['sbc_auto_export']) && ($this->settings['sbc_auto_export'] === 'Enabled') && isset($this->settings['sbc_enable_ftp_ie']) && $this->settings['sbc_enable_ftp_ie'] === TRUE)
            $this->exports_enabled = TRUE;
    }

    public function hf_auto_export_schedule($schedules) {
        if ($this->exports_enabled) {
            $export_interval = $this->settings['sbc_auto_export_interval'];
            if ($export_interval) {
                $schedules['sbc_export_interval'] = array(
                    'interval' => (int) $export_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $export_interval)
                );
            }
        }
        return $schedules;
    }

    public function hf_new_scheduled_export() {
        if ($this->exports_enabled) {
            if (!wp_next_scheduled('hf_subscription_order_csv_im_ex_auto_export')) {
                $start_time = $this->settings['sbc_auto_export_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $export_interval = $this->settings['sbc_auto_export_interval'];
                    $start_timestamp = strtotime("now +{$export_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'sbc_export_interval', 'hf_subscription_order_csv_im_ex_auto_export');
            }
        }
    }

    public function hf_scheduled_export_subscription_orders() {
        include_once( 'exporter/class-wf-subscription-orderimpexpcsv-exporter.php' );

        if (isset($this->settings['sub_auto_export_profile'])) {
            $_POST['auto_export_profile'] = $this->settings['sub_auto_export_profile'];
        } else {
            $_POST['auto_export_profile'] = '';
        }

        if (isset($this->settings['sbc_auto_export_order_status'])) {
            $_POST['order_status'] = $this->settings['sbc_auto_export_order_status'];
        } 
        wf_subcription_orderImpExpCsv_Exporter::do_export('shop_subscription');
    }

    public function clear_hf_scheduled_export() {
        wp_clear_scheduled_hook('hf_subscription_order_csv_im_ex_auto_export');
    }

}