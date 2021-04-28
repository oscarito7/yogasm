<h2 class="nav-tab-wrapper woo-nav-tab-wrapper wt-nav-tab">
    <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex') ?>" class="nav-tab <?php echo ($tab == 'import') ? 'nav-tab-active' : ''; ?>"><?php _e('Order', 'wf_order_import_export'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=wf_coupon_csv_im_ex&tab=coupon') ?>" class="nav-tab <?php echo ($tab == 'coupon') ? 'nav-tab-active' : ''; ?>"><?php _e('Coupon', 'wf_order_import_export'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_subscription_order_im_ex&tab=subscription') ?>" class="nav-tab <?php echo ($tab == 'subscription') ? 'nav-tab-active' : ''; ?>"><?php _e('Subscription', 'wf_order_import_export'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=importxml') ?>" class="nav-tab <?php echo ($tab == 'importxml') ? 'nav-tab-active' : ''; ?>"><?php _e('Order XML', 'wf_order_import_export'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&tab=settings&section=order') ?>" class="nav-tab <?php echo ($tab == 'settings') ? 'nav-tab-active' : ''; ?>"><?php _e('Import/Export Settings', 'wf_order_import_export'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=help') ?>" class="nav-tab <?php echo ($tab == 'help') ? 'nav-tab-active' : ''; ?>"><?php _e('Help', 'wf_order_import_export'); ?></a>
    <?php
        $plugin_name = 'ordercsvimportexport';
        $status = get_option($plugin_name . '_activation_status');
        if( !$status ) {
            $status_icon = '<span style="font-size: 16px" class="dashicons dashicons-warning"></span>';
        } else {
            $status_icon = '<span style="font-size: 16px" class="dashicons dashicons-yes"></span>';
        }
    ?>
    <a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex&tab=licence') ?>" class="nav-tab licence-tab <?php echo ($tab == 'licence') ? 'nav-tab-active' : ''; ?>"><?php _e('Licence', 'wf_order_import_export'); echo '('.$status_icon.') '?></a>
</h2>

