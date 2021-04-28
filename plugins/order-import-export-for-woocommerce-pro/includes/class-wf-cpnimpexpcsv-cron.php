<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_CpnImpExpCsv_Cron {

    public $settings;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'wf_auto_export_schedule'));
        add_action('init', array($this, 'wf_new_scheduled_export_coupon'));
        add_action('wf_coupon_csv_im_ex_auto_export_coupons', array($this, 'wf_scheduled_export_coupons'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $this->exports_enabled = FALSE;
        if (isset($this->settings['cpn_auto_export']) && ($this->settings['cpn_auto_export'] === 'Enabled') && isset($this->settings['cpn_enable_ftp_ie']) && $this->settings['cpn_enable_ftp_ie'] === TRUE)
            $this->exports_enabled = TRUE;
    }

    public function wf_auto_export_schedule($schedules) {
        if ($this->exports_enabled) {
            $export_interval = $this->settings['cpn_auto_export_interval'];
            if ($export_interval) {
                $schedules['cpn_export_interval'] = array(
                    'interval' => (int) $export_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $export_interval)
                );
            }
        }
        return $schedules;
    }

    public function wf_new_scheduled_export_coupon() {
        if ($this->exports_enabled) {
            if (!wp_next_scheduled('wf_coupon_csv_im_ex_auto_export_coupons')) {
                $start_time = $this->settings['cpn_auto_export_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $export_interval = $this->settings['cpn_auto_export_interval'];
                    $start_timestamp = strtotime("now +{$export_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'cpn_export_interval', 'wf_coupon_csv_im_ex_auto_export_coupons');
            }
        }
    }

    public function wf_scheduled_export_coupons() {
        include_once( 'exporter/class-wf-cpnimpexpcsv-exporter.php' );
        WF_CpnImpExpCsv_Exporter::do_export('shop_coupon');
    }

    public function clear_wf_scheduled_export_coupon() {
        wp_clear_scheduled_hook('wf_coupon_csv_im_ex_auto_export_coupons');
    }

}