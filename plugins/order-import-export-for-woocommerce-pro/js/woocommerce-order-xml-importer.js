jQuery(document).ready(function (a) {
    if(jQuery("#xml_orderxml_auto_import").val()=='Disabled')
    {
      jQuery(".xml_orderxml_import_section").hide();
    }
    else
    {
      jQuery(".xml_orderxml_import_section").show();
    }
    if(jQuery("#xml_orderxml_auto_export").val()=='Disabled')
    {
      jQuery(".xml_orderxml_export_section").hide();
    }
    else
    {
      jQuery(".xml_orderxml_export_section").show();
    }
    "use strict";
    a("#v_start_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_xml_import_params.calendar_icon,
        buttonImageOnly: !0
    }), a("#v_end_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_xml_import_params.calendar_icon,
        buttonImageOnly: !0
    }),
    a("select[name=xml_orderxml_auto_export]").change(function () {
        if ("Disabled" === a(this).val()) {
            a(".xml_orderxml_export_section").hide();
        } else {
            a(".xml_orderxml_export_section").show();
        }
    });

    if (woocommerce_order_xml_cron_params.xml_orderxml_auto_export === 'Disabled') {
        a(".xml_orderxml_export_section").hide();
    };
    a("select[name=xml_orderxml_auto_import]").change(function () {
        if ("Disabled" === a(this).val()) {
            a(".xml_orderxml_import_section").hide();
        } else {
            a(".xml_orderxml_import_section").show();
        }
    })
    if (woocommerce_order_xml_cron_params.xml_orderxml_auto_import === 'Disabled') {
        a(".xml_orderxml_import_section").hide();
    }
});