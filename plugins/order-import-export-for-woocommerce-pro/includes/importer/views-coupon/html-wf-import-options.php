<div class="woocommerce">
<?php
if (empty($tab)) $tab = 'coupon';
include_once(dirname(__FILE__) . '/../../views/html-wf-common-header.php');
?>

<ul class="subsubsub" style="margin-left: 15px;">
    <li><a href="<?php echo admin_url('admin.php?page=wf_coupon_csv_im_ex') ?>" class=""><?php _e('Export', 'wf_csv_import_export'); ?></a> | </li>
    <li><a href="<?php echo admin_url('admin.php?import=coupon_csv') ?>" class="current"><?php _e('Import', 'wf_csv_import_export'); ?></a> </li>
</ul>
<br/>
<div class="tool-box ordimpexp-bg-white ordimpexp-p-20p">
<form action="<?php echo admin_url('admin.php?import=' . $this->import_page . '&step=2&merge=' . $this->merge); ?>" method="post">
    <?php wp_nonce_field('import-woocommerce'); ?>
    <input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
    <?php if ($this->file_url_import_enabled) : ?>
     <input type="hidden" name="import_url" value="<?php echo $this->file_url; ?>" />
    <?php endif; ?>
        <h3 class="title"><?php _e('Step 2: Import mapping', 'wf_order_import_export'); ?></h3>
    <?php if($this->profile == ''){?>
        <?php _e('Mapping file name:', 'wf_order_import_export'); ?> <input type="text" name="profile" value="" placeholder="Enter filename to save" />
    <?php }else{ ?>
        <input type="hidden" name="profile" value="<?php echo $this->profile; ?>" />
    <?php } ?>
    <p><?php _e('Here you can map your imported columns to coupon data fields.', 'wf_order_import_export'); ?></p>
    <table class="widefat widefat_importer">
        <thead>
            <tr>
                <th><?php _e('Map to', 'wf_order_import_export'); ?></th>
                <th><?php _e('Column Header', 'wf_order_import_export'); ?></th>
                <th><?php _e('Evaluation Field', 'wf_order_import_export'); ?>
                    <?php $plugin_url = WF_OrderImpExpCsv_Common_Utils::hf_get_wc_path(); ?>
                    <img class="help_tip" style="float:none;" data-tip="<?php _e('Assign constant value WebToffee to post_author:</br>=WebToffee</br>Add $5 to Minimum/Maximum Amount:</br>+5</br>Reduce $5 to Minimum/Maximum Amount:</br>-5</br>Multiple 1.05 to Minimum/Maximum Amount:</br>*1.05</br>Divide Minimum/Maximum Amount by 2:</br>/2</br>Append a value By WebToffee to post_title:</br>&By WebToffee</br>Prepend a value WebToffee to post_title:</br>&WebToffee [VAL].', 'wf_order_import_export'); ?>" src="<?php echo $plugin_url; ?>/assets/images/help.png" height="20" width="20" /> 
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            $wpost_attributes = include( dirname(__FILE__) . '/../data-coupon/data-wf-reserved-fields-pair.php' );
            
            $taxonomy_n_attributes_items = array();
            foreach ($raw_headers as $key => $column) {
               if (strstr($key, 'meta:')) {
                    $column = trim(str_replace('meta:', '', $key));
                    $taxonomy_n_attributes_items['meta:' . $column] = 'meta:' . $column . '| Custom Field:' . $column;
                } 
            }
            foreach ($taxonomy_n_attributes_items as $key => $value) {
                $wpost_attributes[$key] = $value;
            }

            foreach ($wpost_attributes as $key => $value) :
                $sel_key = ($saved_mapping && isset($saved_mapping[$key])) ? $saved_mapping[$key] : $key;
                $evaluation_value = ($saved_evaluation && isset($saved_evaluation[$key])) ? $saved_evaluation[$key] : '';
                $evaluation_value = stripslashes($evaluation_value);
                $values = explode('|',$value);
                $value = $values[0];
                $tool_tip = $values[1];
                ?>
                <tr>
                    <td width="25%">
                        <img class="help_tip" style="float:none;" data-tip="<?php echo $tool_tip; ?>" src="<?php echo $plugin_url; ?>/assets/images/help.png" height="20" width="20" /> 
                        <select name="map_to[<?php echo $key; ?>]" disabled="true" 
                                style=" -webkit-appearance: none;
                                    -moz-appearance: none;
                                    text-indent: 1px;
                                    text-overflow: '';
                                    background-color: #f1f1f1;
                                    border: none;
                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07) inset;
                                    color: #32373c;
                                    outline: 0 none;
                                    transition: border-color 50ms ease-in-out 0s;">
                            <option value="<?php echo $key; ?>" <?php if ($key == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
                        </select>                             
                    </td>
                    <td width="25%">
                        <select name="map_from[<?php echo $key; ?>]">
                            <option value=""><?php _e('Do not import', 'wf_order_import_export'); ?></option>
                            <?php
                            foreach ($row as $hkey => $hdr):
                                $hdr = strlen($hdr) > 50 ? substr($hdr, 0, 50) . "..." : $hdr;
                                ?>
                                <option value="<?php echo $raw_headers[$hkey]; ?>" <?php selected(strtolower($sel_key), $hkey); ?>><?php echo $raw_headers[$hkey] . " &nbsp;  : &nbsp; " . $hdr; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php do_action('coupon_csv_coupon_data_mapping', $key); ?>
                    </td>
                    <td width="10%"><input type="text" name="eval_field[<?php echo $key; ?>]" value="<?php echo $evaluation_value; ?>"  /></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" class="button button-primary" value="<?php esc_attr_e('Submit', 'wf_order_import_export'); ?>" />
        <input type="hidden" name="delimiter" value="<?php echo $this->delimiter ?>" />
        <input type="hidden" name="sku_checkbox" value="<?php echo $this->sku_checkbox ?>" />
<?php /*        <input type="hidden" name="merge_empty_cells" value="<?php echo $this->merge_empty_cells ?>" /> */ ?>
    </p>
</form>
</div>
</div>