<script type="text/javascript">
    function showTr(elem){
        if(elem.value !== 'general'){
            document.getElementById('general_meta').style.display = "none";
            document.getElementById('ex_already_exp').style.display = "none";
        }else{
            document.getElementById('general_meta').style.display = "table-row";
            document.getElementById('ex_already_exp').style.display = "table-row";
        }
    }
</script>
<div class="tool-box export-screen ordimpexp-bg-white ordimpexp-p-20p ordimpexp-mtop-20p">
    <?php
    $order_statuses = wc_get_order_statuses();
    ?>
    <h3 class="title"><?php _e('Export Orders in XML Format:', 'wf_order_import_export'); ?></h3>
    <h5><?php _e('(For sample format of XML export, <a href="'.admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&tab=help').'"> Click Here </a>)','wf_order_import_export')?></h5>
    <p><?php _e('Export and download your orders in XML format.', 'wf_order_import_export'); ?></p>
    <form action="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex_xml&action=export'); ?>" method="post">

        <table class="form-table"> 
            <tr>
                <th>
                    <label for="v_order_export_type"><?php _e('Order Export Type', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select id="v_order_export_type" name="order_export_type" data-placeholder="<?php _e('Orders Export Type', 'wf_order_import_export'); ?>" onchange="showTr(this)">
                        <option value="general"><?php _e("WooCommerce",'wf_order_import_export') ?></option>
                        <option value="stamps"><?php _e("Stamps.Com",'wf_order_import_export') ?></option>
                        <option value="fedex"><?php _e("FedEx",'wf_order_import_export') ?></option>
                        <option value="ups"><?php _e("UPS WorldShip",'wf_order_import_export') ?></option>
                        <option value="endicia"><?php _e("Endicia",'wf_order_import_export') ?></option>
                    </select>
                                                        
                    <p style="font-size: 12px"><?php _e('Orders with these type XML will be exported.', 'wf_order_import_export'); ?></p>
                </td>
            </tr>
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
                    <label for="v_products"><?php _e('Products', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <select class="wc-product-search" multiple="multiple" id="v_products" name="products[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wf_order_import_export'); ?>"></select>

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
            <tr id="ex_already_exp">
                <th>
                    <label for="exclude_already_exported"><?php _e('Exclude already exported', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input name="exclude_already_exported" id="exclude_already_exported" type="checkbox" >
                </td>
            </tr>
            <tr id="general_meta">
                <th>
                    <label for="include_xml_meta"><?php _e('Include hidden meta', 'wf_order_import_export'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="include_xml_meta" id="include_xml_meta" />
                </td>
            </tr>
        

        </table>
        <p class="submit" style="padding-left: 10px;"><input type="submit" class="button button-primary" value="<?php _e('Export Orders', 'wf_order_import_export'); ?>" /></p>
    </form>
</div>