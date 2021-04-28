<div class="woocommerce">
<?php
if (empty($tab)) $tab = 'importxml'; 
include_once(dirname(__FILE__) . '/../../views/html-wf-common-header.php');
?>
<div class="tool-box ordimpexp-bg-white ordimpexp-p-20p">
<form action="<?php echo admin_url('admin.php?import=' . $this->import_page . '&step=2&merge=' . $merge); ?>" method="post" id="nomap">
    <?php wp_nonce_field('import-woocommerce'); ?>
    <input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
    <input type="hidden" name="import_type" value="<?php echo $import_type; ?>" />
    <input type="hidden" name="import_decision" value="<?php echo $import_decision; ?>" />
    <?php if ($this->file_url_import_enabled) : ?>
        <input type="hidden" name="import_url" value="<?php echo $this->file_url; ?>" />
    <?php endif; ?>
    <p class="submit">
        <input style="display:none" type="submit" class="button button-primary" value="<?php esc_attr_e('Submit', 'wf_order_import_export'); ?>" />
        <input type="hidden" name="merge_empty_cells" value="<?php echo $this->merge_empty_cells ?>" />
    </p>
</form>
</div>
</div>
<script type="text/javascript"> 
jQuery(document).ready(function(){
   jQuery("form#nomap").submit();
});
</script>