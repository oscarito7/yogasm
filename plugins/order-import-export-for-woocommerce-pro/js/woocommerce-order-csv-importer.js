jQuery(document).ready(function (a) {
    "use strict";
    a("#v_start_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_csv_import_params.calendar_icon,
        buttonImageOnly: !0
    }), a("#v_end_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_csv_import_params.calendar_icon,
        buttonImageOnly: !0
    }), a("#datepick_auto1").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,//this option for allowing user to select month
        changeYear: true,
    }), a("#datepick_auto2").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,//this option for allowing user to select month
        changeYear: true,
    }),
    a("select[name=ord_auto_export]").change(function () {
        if ("Disabled" === a(this).val()) {
            a(".ord_export_section").hide();
        } else {
            a(".ord_export_section").show();
        }
    });

    if (woocommerce_order_csv_cron_params.ord_auto_export === 'Disabled') {
        a(".ord_export_section").hide();
    };
    a("select[name=ord_auto_import]").change(function () {
        if ("Disabled" === a(this).val()) {
            a(".ord_import_section").hide();
        } else {
            a(".ord_import_section").show();
        }
    })
    if (woocommerce_order_csv_cron_params.ord_auto_import === 'Disabled') {
        a(".ord_import_section").hide();
    }
    // Listen for click on toggle checkbox
    a('#selectall').click(function (event) {
        // Iterate each checkbox
        a(':checkbox').each(function () {
            this.checked = true;
        });
    });
    a('#unselectall').click(function (event) {
        // Iterate each checkbox
        a(':checkbox').each(function () {
            this.checked = false;
        });
    });


    // Triggered When Test FTP Button is clicked for Order
    a('#ordr_test_ftp_connection').click(function () {
        a('.spinner').addClass('is-active');
        var use_ftp = a("#ord_use_ftps").prop("checked") ? 1 : 0;
        a.ajax({
            url: xa_ordr_piep_test_ftp.admin_ajax_url,
            type: 'POST',
            data: {
                action: 'order_test_ftp_connection',
                ftp_host: a('#ord_ftp_server').val(),
                ftp_port: a('#ord_ftp_port').val(),
                ftp_userid: a('#ord_ftp_user').val(),
                ftp_password: a('#ord_ftp_password').val(),
                use_ftps: use_ftp,
                wt_nonce: xa_ordr_piep_test_ftp.wt_nonce
            },
            success: function (response) {
                a('.spinner').removeClass('is-active');
                a('#ordr_ftp_test_msg').remove();
                a('#ordr_ftp_test_notice').prepend(response);
                a("#ordr_ftp_test_msg").delay(8000).fadeOut(300);
            }
        });
    });

    a("select[name=ord_export_profile]").change(function () {
        var selected_profile = this.value;
        a("#v_new_profile").val(selected_profile);
        var data = {
            action: 'order_csv_export_mapping_change',
            v_new_profile: selected_profile,
            wt_nonce: woocommerce_order_csv_import_params.wt_nonce
            
        };
        a.ajax({
            url: woocommerce_order_csv_import_params.siteurl + '?page=wf_woocommerce_order_im_ex',
            data: data,
            type: 'POST',
            success: function (response) {
                a("#datagrid").html(response);
            }});
    });

});