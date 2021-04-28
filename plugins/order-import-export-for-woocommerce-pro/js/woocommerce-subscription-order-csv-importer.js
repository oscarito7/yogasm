jQuery(document).ready(function(a) {
    "use strict";
     a("#v_start_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_subscription_order_csv_params.calendar_icon,
        buttonImageOnly: !0
    }),a("#v_end_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_subscription_order_csv_params.calendar_icon,
        buttonImageOnly: !0
    }),a("#v_next_pay_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_subscription_order_csv_params.calendar_icon,
        buttonImageOnly: !0
    }),
    a("select[name=sbc_auto_export]").change(function() {
        if("Disabled" === a(this).val()){
            a(".sbc_export_section").hide();
        }else{
            a(".sbc_export_section").show();
        }
    })
    if(woocommerce_subscription_order_csv_cron_params.sbc_auto_export === 'Disabled'){
        a(".sbc_export_section").hide();
    };
    a("select[name=sbc_auto_import]").change(function() {
        if("Disabled" === a(this).val()){
            a(".sbc_import_section").hide();
        }else{
            a(".sbc_import_section").show();
        }
    })
    if(woocommerce_subscription_order_csv_cron_params.sbc_auto_import === 'Disabled'){
        a(".sbc_import_section").hide();
    }
    
    // Listen for click on toggle checkbox
    a('#selectall').click(function(event) {   
            // Iterate each checkbox
           a(':checkbox').each(function() {
                this.checked = true;
            });
    });
    a('#unselectall').click(function(event) {   
            // Iterate each checkbox
           a(':checkbox').each(function() {
                this.checked = false;
            });
    });
    
    // mapping profile change
    a("select[name=sub_export_profile]").change(function () {
        var selected_profile = this.value;
        a("#v_new_profile").val(selected_profile);
        var data = {
            action: 'subscription_csv_export_mapping_change',
            v_new_profile: selected_profile,
        };
        a.ajax({
            url: woocommerce_subscription_order_csv_params.siteurl + '?page=wf_woocommerce_subscription_order_im_ex',
            data: data,
            type: 'POST',
            success: function (response) {
                a("#datagrid").html(response);
            }
        });
    });
    
});