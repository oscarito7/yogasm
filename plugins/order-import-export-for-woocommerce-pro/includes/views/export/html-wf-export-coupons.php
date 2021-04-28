<div class="tool-box ordimpexp-bg-white ordimpexp-p-20p ordimpexp-mtop-20p">
    <h3 class="title"><?php _e('Export Coupon in CSV Format:', 'wf_order_import_export'); ?></h3>
    <p><?php _e('Export and download your coupons in CSV format. This file can be used to import coupons back into your Woocommerce shop.', 'wf_order_import_export'); ?></p>
    <form action="<?php echo admin_url('admin.php?page=wf_coupon_csv_im_ex&action=export'); ?>" method="post">

        <table class="form-table">
            <tr>
                <th>
                    <label for="v_offset"><?php _e('Offset', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="offset" id="v_offset" placeholder="<?php _e('0', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of coupons to skip before returning.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>            
            <tr>
                <th>
                    <label for="v_limit"><?php _e('Limit', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="limit" id="v_limit" placeholder="<?php _e('Unlimited', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of coupons to return.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_cpn_categories"><?php _e('Coupon Types', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select id="v_cpn_categories" name="cpn_categories[]" data-placeholder="<?php _e('Any Types', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                        <?php

                        $coupon_categories   = wc_get_coupon_types();
                        foreach ($coupon_categories as $category_id => $category_name) {
                            echo '<option value="' . $category_id . '">' . $category_name . '</option>';
                        }
                        ?>
                    </select>
                                                        
                    <p style="font-size: 12px"><?php _e('Coupons under these types will be exported.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
<?php /*
            <tr>
                <th>
                    <label for="coupon_code"><?php _e('Coupon code', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="coupon_code" id="coupon_code" placeholder="<?php _e('any', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The coupon codes to be exported.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="c_products"><?php _e('Products', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="c_products" name="c_products[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wf_order_import_export'); ?>"></select>

                    <p style="font-size: 12px"><?php _e('Export coupons for the selected specific products.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
*/ ?>
            <tr>
                <th>
                    <label for="v_sortcolumn"><?php _e('Sort Columns', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="sortcolumn" id="v_sortcolumn" placeholder="<?php _e('ID', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('What columns to sort pages by, comma-separated.', 'wf_order_import_export'); ?> </p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="c_amount"><?php _e('Coupon Amount', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="number" min="0" name="coupon_amount_from" id="c_amount" placeholder="<?php _e('From amount', 'wf_order_import_export'); ?>" class="input-text" /> -
                     <input type="number" min="0" name="coupon_amount_to" id="c_amount" placeholder="<?php _e('To amount', 'wf_order_import_export'); ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="c_date"><?php _e('Coupon Expiry Date', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="coupon_exp_date_from" id="datepicker1" placeholder="<?php _e('From date', 'wf_order_import_export'); ?>" class="input-text" /> -
                    <input type="text" name="coupon_exp_date_to" id="datepicker2" placeholder="<?php _e('To date', 'wf_order_import_export'); ?>" class="input-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_delimiter"><?php _e('Delimiter', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="delimiter" id="v_delimiter" placeholder="<?php _e(',', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('Column seperator for exported file', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_columns"><?php _e('Columns', 'wf_order_import_export'); ?></label>
                </th>
            <table id="datagrid">
                                <!-- select all boxes -->
                <tr>
                      <td style="padding: 10px;">
                          <a href="#" id="selectall" onclick="return false;" >Select all</a> &nbsp;/&nbsp;
                          <a href="#" id="unselectall" onclick="return false;">Unselect all</a>
                      </td>
                </tr>
                <th style="text-align: left;">
                    <label for="v_columns"><?php _e('Column', 'wf_order_import_export'); ?></label>
                </th>
                <th style="text-align: left;">
                    <label for="v_columns_name"><?php _e('Column Name', 'wf_order_import_export'); ?></label>
                </th>
                <?php foreach ($post_columns as $pkey => $pcolumn) {
                            
                         ?>
            <tr>
                <td>
                    <input name= "columns[<?php echo $pkey; ?>]" type="checkbox" value="<?php echo $pkey; ?>" checked>
                    <label for="columns[<?php echo $pkey; ?>]"><?php _e($pcolumn, 'wf_order_import_export'); ?></label>
                </td>
                <td>
                    <?php 
                    $tmpkey = $pkey;
                    if (strpos($pkey, 'yoast') === false) {
                            $tmpkey = ltrim($pkey, '_');
                        }
                    ?>
                     <input type="text" name="columns_name[<?php echo $pkey; ?>]"  value="<?php echo $tmpkey; ?>" class="input-text" />
                </td>
            </tr>
                <?php } ?>
                
            </table><br/>
            </tr>
            
        </table>
        <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Export Coupons', 'wf_order_import_export'); ?>" /></p>
    </form>
</div>