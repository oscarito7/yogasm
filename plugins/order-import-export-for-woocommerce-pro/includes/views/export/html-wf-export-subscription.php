<div class="tool-box ordimpexp-bg-white ordimpexp-p-20p ordimpexp-mtop-20p">
    <?php
    $order_statuses = $this->hf_get_subscription_statuses();
    ?>
    <h3 class="title"><?php _e('Export Subscription Orders in CSV Format:', 'wf_order_import_export'); ?></h3>
    <p><?php _e('Export and download your subscription orders in CSV format. This file can be used to import subscription orders back into your Woocommerce shop.', 'wf_order_import_export'); ?></p>
    <form action="<?php echo admin_url('admin.php?page=wf_woocommerce_subscription_order_im_ex&action=export'); ?>" method="post">

        <table class="form-table">
            <tr>
                <th>
                    <label for="v_order_status"><?php _e('Order Statuses', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select id="v_order_status" name="order_status[]" data-placeholder="<?php _e('All Orders', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple">
                        <?php
                        foreach ($order_statuses as $key => $column) {
                            echo '<option value="' . $key . '">' . $column . '</option>';
                        }
                        ?>
                    </select>
                                                        
                    <p style="font-size: 12px"><?php _e('Orders with these status will be exported.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>  
            <tr>
                <th>
                    <label for="v_offset"><?php _e('Offset', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="offset" id="v_offset" placeholder="<?php _e('0', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of orders to skip before returning.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>            
            <tr>
                <th>
                    <label for="v_limit"><?php _e('Limit', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="limit" id="v_limit" placeholder="<?php _e('Unlimited', 'wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('The number of orders to return.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
             <tr>
                <th>
                    <label for="v_email"><?php _e('Email', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                     <select class="wc-customer-search" multiple="multiple" style="width: 50%;" id="v_email" name="email[]" data-placeholder="<?php esc_attr_e('Search for a Customer&hellip;', 'wf_order_import_export'); ?>"></select>

                    <p style="font-size: 12px"><?php _e('Export orders based on email.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
             <tr>
                <th>
                    <label for="v_products"><?php _e('Products', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="v_products" name="products[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wf_order_import_export'); ?>"></select>
                    <p style="font-size: 12px"><?php _e('Export orders for the selected specific products.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_coupons"><?php _e('Coupons', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="coupons" id="v_coupons" placeholder="<?php _e('Enter coupon codes separated by \',\'','wf_order_import_export'); ?>" class="input-text" />
                    <p style="font-size: 12px"><?php _e('Export orders based on coupons applied.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_start_date"><?php _e('Start Date', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="start_date"  id="v_start_date" />
                    <p>Format: <code>YYYY-MM-DD.</code></p>         
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_end_date"><?php _e('End Date', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="end_date"  id="v_end_date" />
                    <p>Format: <code>YYYY-MM-DD.</code></p>
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
                    <label for="v_next_pay_date"><?php _e('Next Payment Date', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="text" name="next_pay_date"  id="v_next_pay_date" />
                    <p>Format: <code>YYYY-MM-DD.</code></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="v_payment_method"><?php _e('Payment methods', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select id="v_payment_methods" name="payment_methods[]" data-placeholder="<?php _e('Payment Methods', 'wf_order_import_export'); ?>" class="wc-enhanced-select" multiple="multiple"> 
					<?php foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) { ?>
                        <option value="<?php echo $gateway->id ?>" ><?php echo $gateway->get_title() ?></option>
					<?php } ?>
                     </select>
                    <!--<p style="font-size: 12px"><?php _e('Column seperator for exported file', 'wf_order_import_export'); ?></p>-->
                </td>
            </tr>
            
             <?php
            $export_mapping_from_db = get_option('wt_subscription_csv_export_mapping');
            if (!empty($export_mapping_from_db)) {
                ?>
                <tr>
                    <th>
                        <label for="sub_export_profile"><?php _e('Select a mapping file for export.', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <select name="sub_export_profile">
                            <option value="">--Select--</option>
                            <?php foreach ($export_mapping_from_db as $key => $value) { ?>
                                <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            <?php } ?>
            
             <tr>
                    <th>
                        <label for="v_sub_new_profile"><?php _e('Save the export mapping', 'wf_order_import_export'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="new_profile" id="v_new_profile" class="input-text" />
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
                <?php 
                ?>
                <?php foreach ($post_columns as $pkey => $pcolumn) {
                            
                         ?>
            <tr>
                <td>
                    <input name= "columns[<?php echo $pkey; ?>]" type="checkbox" value="<?php echo $pkey; ?>" checked>
                    <label for="columns[<?php echo $pkey; ?>]"><?php _e($pcolumn, 'wf_order_import_export'); ?></label>
                </td>
                <td>
                     <input type="text" name="columns_name[<?php echo $pkey; ?>]"  value="<?php echo $pkey; ?>" class="input-text" />
                </td>
            </tr>
                <?php } ?>
                
            </table><br/>
            </tr>
            
            
            
            
            

        </table>
        <p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Export Orders', 'wf_order_import_export'); ?>" /></p>
    </form>
</div>