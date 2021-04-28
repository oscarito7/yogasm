<div class="tool-box import-screen ordimpexp-bg-white ordimpexp-p-20p">
    <h3 class="title"><?php _e('Import Orders in XML Format:', 'wf_order_import_export'); ?></h3>
    <h5><?php _e('(For sample format of XML import, <a href="'.admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=help').'"> Click Here </a>)','wf_order_import_export')?></h5>
    <p><?php _e('Import Orders in XML format from different sources (  from your computer OR from another server via FTP )', 'wf_order_import_export'); ?></p>
    <p class="submit" style="padding-left: 10px;">
        <?php
        $import_url = admin_url('admin.php?import=woocommerce_wf_import_order_xml&tab=importxml');
        ?>
        <a class="button button-primary" id="mylink" href="<?php echo $import_url; ?>"><?php _e('Import Orders', 'wf_order_import_export'); ?></a>
        &nbsp;
        <br>
    </p>
</div>
