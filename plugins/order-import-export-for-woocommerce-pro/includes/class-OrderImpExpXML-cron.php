<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WF_OrderImpExpXML_Cron {

    public $settings;

    public function __construct() {
        add_filter('cron_schedules', array($this, 'wf_auto_export_schedule'));
        add_action('init', array($this, 'wf_new_scheduled_export_orderxml'));
        add_action('wf_order_xml_im_ex_auto_export_orderxml', array($this, 'wf_scheduled_export_orderxml'));
        $this->settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $this->exports_enabled = FALSE;
        if (isset($this->settings['xml_orderxml_auto_export']) && ($this->settings['xml_orderxml_auto_export'] === 'Enabled') && isset($this->settings['xml_enable_ftp_ie']) && ($this->settings['xml_enable_ftp_ie'] === TRUE))
            $this->exports_enabled = TRUE;
    }

    public function wf_auto_export_schedule($schedules) {
        if ($this->exports_enabled) {
            $export_interval = $this->settings['xml_orderxml_auto_export_interval'];
            if ($export_interval) {
                $schedules['orderxml_export_interval'] = array(
                    'interval' => (int) $export_interval * 60,
                    'display' => sprintf(__('Every %d minutes', 'wf_order_import_export'), (int) $export_interval)
                );
            }
        }
        return $schedules;
    }

    public function wf_new_scheduled_export_orderxml() {
        if ($this->exports_enabled) {
            if (!wp_next_scheduled('wf_order_xml_im_ex_auto_export_orderxml')) {
                $start_time = $this->settings['xml_orderxml_auto_export_start_time'];
                $current_time = current_time('timestamp');
                if ($start_time) {
                    if ($current_time > strtotime('today ' . $start_time, $current_time)) {
                        $start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    } else {
                        $start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
                    }
                } else {
                    $export_interval = $this->settings['xml_orderxml_auto_export_interval'];
                    $start_timestamp = strtotime("now +{$export_interval} minutes");
                }
                wp_schedule_event($start_timestamp, 'orderxml_export_interval', 'wf_order_xml_im_ex_auto_export_orderxml');
            }
        }
    }

    public function wf_scheduled_export_orderxml() {        
        include_once( 'exporter/class-OrderImpExpXML-base-exporter.php' );
        $exporter = new OrderImpExpXMLBase_Exporter();
        if (isset($this->settings['xml_orderxml_auto_export_order_status'])) {
            $_POST['order_status'] = $this->settings['xml_orderxml_auto_export_order_status'];
        } 
        if(isset($this->settings['xml_orderxml_auto_export_products'])) {
            $_POST['products'] = $this->settings['xml_orderxml_auto_export_products'];
        }
        if(isset($this->settings['exclude_already_exported_xml'])){
            $_POST['exclude_already_exported'] = $this->settings['exclude_already_exported_xml'];
        }
        $exporter->do_export('shop_order');
    }

    public function clear_wf_scheduled_export_orderxml() {
        wp_clear_scheduled_hook('wf_order_xml_im_ex_auto_export_orderxml');
    }

}