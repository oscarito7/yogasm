<div class="woocommerce">
    <div class="icon32" id="icon-woocommerce-importer"><br></div>
    <!-- <h2><b><?php //_e('Order/Coupon/Subscription CSV/XML Import Export Settings', 'wf_order_import_export'); ?></b></h2> -->
    <?php
    include_once('html-wf-common-header.php');
    switch ($tab) {
        case "export" :
            $this->admin_export_page();
            break;
        case "settings" :
            $this->admin_settings_page();
            break;
        case "coupon" :
            $this->admin_coupon_page();
            break;
        case "subscription" :
            $this->admin_subscription_page();
            break;
        case "importxml":
            $this->admin_import_page();
            break;
        case "help";
            $this->admin_help_page();
            break;
        case "licence" :
            $this->admin_licence_page($plugin_name);
            break;
        default :
            $this->admin_import_page();
            break;
    }
    ?>
</div>