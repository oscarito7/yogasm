<div class="woocommerce">
    <style>

        #customer_email td:first-child{
            position: relative;
        }
        #customer_email td:first-child:before,
        .customer_email_terms:before {
            content: "\f155";
            position: absolute;
            top: 10px;
            font-size: 14px;
            left: 4px;
            color: #d23d3d;
            font-family: dashicons;
            text-decoration: inherit;
            font-weight: 400;
            font-style: normal;
            vertical-align: top;
            text-align: center;
            transition: color .1s ease-in;
            -webkit-font-smoothing: antialiased;
        }
        .customer_email_terms:before {
            left: 0;
            top: 0;
        }
        .customer_email_terms {
            position: relative;
            padding-left: 25px;
        }
    </style>
    <?php
    if (empty($tab)) $tab = 'import'; 
    include_once(dirname(__FILE__) . '/../../views/html-wf-common-header.php');
    ?>

    <ul class="subsubsub" style="margin-left: 15px;">
        <li><a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex') ?>" class=""><?php _e('Export', 'wf_order_import_export'); ?></a> | </li>
        <li><a href="<?php echo admin_url('admin.php?import=woocommerce_wf_order_csv') ?>" class="current"><?php _e('Import', 'wf_order_import_export'); ?></a> </li>
    </ul>
    <br/>
    <div class="tool-box ordimpexp-bg-white ordimpexp-p-20p">

        <form action="<?php echo admin_url('admin.php?import=' . $this->import_page . '&step=2&merge=' . $this->merge . '&status_mail=' . $this->status_mail . '&ord_link_using_sku=' . $this->ord_link_using_sku . '&wtcreateuser=' . $this->wtcreateuser); ?>" method="post">
            <?php wp_nonce_field('import-woocommerce'); ?>
            <input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
            <?php if ($this->file_url_import_enabled) : ?>
                <input type="hidden" name="import_url" value="<?php echo $this->file_url; ?>" />
            <?php endif; ?>
            <h3 class="title"><?php _e('Step 2: Import mapping', 'wf_order_import_export'); ?></h3>
            <?php if ($this->profile == '') { ?>
                <?php _e('Mapping file name:', 'wf_order_import_export'); ?> <input type="text" name="profile" value="" placeholder="Enter filename to save" />
            <?php } else { ?>
                <input type="hidden" name="profile" value="<?php echo $this->profile; ?>" />
            <?php } ?>
            <p><?php _e('Here you can map your imported columns to order data fields.', 'wf_order_import_export'); ?></p>
            <p class="customer_email_terms"><?php _e('Before importing the orders, please make sure you have imported all the users belonging to the orders you are importing. The customer_email field will be used to associate the orders with the users. If customer_email of an order matches with the user in the site, the imported orders will be associated with those users by default. If the user is not available and the "Create user option" is enabled under the settings in the previous screen, plugin will create a new user with the customer_email. If this option is not enabled these orders will be created as guest orders.', 'wf_order_import_export'); ?></p>
            <table class="widefat widefat_importer">
                <thead>
                    <tr>
                        <th><?php _e('Map to', 'wf_order_import_export'); ?></th>
                        <th><?php _e('Column Header', 'wf_order_import_export'); ?></th>
                        <th><?php _e('Evaluation Field', 'wf_order_import_export'); ?>
                            <?php $plugin_url = WC()->plugin_url(); ?>
                            <img class="help_tip" style="float:none;" data-tip="<?php _e('Assign desired value to order_status:</br>=processing</br>Add $5 to OrderTotal:order_total:</br>+5</br>Reduce $5 to OrderTotal:order_total:</br>-5</br>Multiple 1.05 to OrderTotal:order_total:</br>*1.05</br>Divide OrderTotal:order_total by 2:</br>/2</br>Convert date to Woocommerce format by providing your valid PHP date format :</br>@ d/m/yy H:i:s</br>Append a value By WebToffee to order_notes:</br>&By WebToffee</br>Prepend a value WebToffee to order_notes:</br>&WebToffee [VAL].', 'wf_order_import_export'); ?>" src="<?php echo $plugin_url; ?>/assets/images/help.png" height="20" width="20" /> 
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                    $wp_order_attributes = include( dirname(__FILE__) . '/../data/data-wf-reserved-fields-pair.php' );

                    $taxonomy_n_attributes_items = array();

                    foreach ($raw_headers as $key => $column) {
                        if (strstr($key, 'meta:')) {
                            $column = trim(str_replace('meta:', '', $key));
                            $taxonomy_n_attributes_items['meta:' . $column] = 'meta:' . $column . '| Custom Field:' . $column;
                        }
                    }

                    if (function_exists('order_invoice_pdf')) {
                        $gpf = array(
                            "meta:invoie_id" => "meta:invoie_id",
                            "meta:invoie_val" => "meta:invoie_val",
                        );
                        foreach ($gpf as $key => $value) {
                            $wp_order_attributes[$key] = $value;
                        }
                    }

                    foreach ($taxonomy_n_attributes_items as $key => $value) {
                        $wp_order_attributes[$key] = $value;
                    }


                    foreach ($wp_order_attributes as $key => $value) :
                        $sel_key = ($saved_mapping && isset($saved_mapping[$key])) ? $saved_mapping[$key] : $key;
                        $evaluation_value = ($saved_evaluation && isset($saved_evaluation[$key])) ? $saved_evaluation[$key] : '';
                        $evaluation_value = stripslashes($evaluation_value);
                        $values = explode('|', $value);
                        $value = $values[0];
                        $tool_tip = $values[1];
                        ?>
                        <tr id="<?php echo $key; ?>">
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
                                        $hdr = strlen($hdr) > 50 ? substr(strip_tags($hdr), 0, 50) . "..." : $hdr;
                                        ?>
                                        <option value="<?php echo $raw_headers[$hkey]; ?>" <?php selected($sel_key, $hkey); //selected(strtolower($sel_key), $hkey);    ?>><?php echo $raw_headers[$hkey] . " &nbsp;  : &nbsp; " . $hdr; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php do_action('woocommerce_csv_order_data_mapping', $key); ?>
                            </td>
                            <td width="10%"><input type="text" name="eval_field[<?php echo $key; ?>]" value="<?php echo $evaluation_value; ?>"  /></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Start Import >>', 'wf_order_import_export'); ?>" />
                <input type="hidden" name="delimiter" value="<?php echo $this->delimiter ?>" />
                <input type="hidden" name="merge_empty_cells" value="<?php echo $this->merge_empty_cells ?>" />
                <input type="hidden" name="merge" value="<?php echo $this->merge ?>" />
                <input type="hidden" name="wtcreateuser" value="<?php echo $this->wtcreateuser ?>" />
                <input type="hidden" name="status_mail" value="<?php echo $this->status_mail ?>" />
                <input type="hidden" name="ord_link_using_sku" value="<?php echo $this->ord_link_using_sku ?>" />
            </p>
        </form>
    </div>
</div>