/*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
$.widget.bridge('uibutton', $.ui.button);
$.widget.bridge('uitooltip', $.ui.tooltip);

$(document).ready(function()
{
    $('.rejection_rate_field').change(function() {
        var min_high_orig = $('#min_high_reject_rate_hidden').val(),
            min_high_field = $('#min_high_reject_rate').val(),
            max_high_orig = $('#max_high_reject_rate_hidden').val(),
            max_high_field = $('#max_high_reject_rate').val();

        if( (min_high_orig != min_high_field) || (max_high_orig != max_high_field)) {
            //There's a change
            $('#update_rejection_rate').val(1);
        }else {
            $('#update_rejection_rate').val(0);
        }
    });

    $('#max_high_reject_rate').keyup(function() {
        var max_high = Number($(this).val());
        var min_critical = 0;
        if(max_high < 100) min_critical = max_high + .1;
        else min_critical = 100;
        $('#min_critical_reject_rate').val(min_critical);
    });

    $('#stack_order_table').sortable({
        helper: function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        },
        update: function(event, ui) {
            // '[1,3,2,7,4,5,6,8,9,10,11,12]';
            var pathJsonString = '[';
            $('#stack_order_table li').each( function(e) {
                pathJsonString += $(this).data('id') +',';
            });
            pathJsonString = pathJsonString.slice(0,-1);
            pathJsonString += ']';
            $('#stack_page_order').val(pathJsonString);
        }
    }).disableSelection();

    $('#campaign_reordering').change(function() {
        console.log('campaign_reordering toggle: ' + $(this).prop('checked'));

        if($(this).prop('checked'))
        {
            $(this).val(1);
            $('#campaign_reordering_status').val(1);
        }
        else
        {
            $(this).val(0);
            $('#campaign_reordering_status').val(0);
        }
    });

    $('#mixed_coreg_campaign_reordering').change(function() {
        console.log('mixed_coreg_campaign_reordering toggle: ' + $(this).prop('checked'));

        if($(this).prop('checked'))
        {
            $(this).val(1);
            $('#mixed_coreg_campaign_reordering_status').val(1);
        }
        else
        {
            $(this).val(0);
            $('#mixed_coreg_campaign_reordering_status').val(0);
        }
    });

    $('.email_recipient').change(function() {
        $('#recipientChange').val(1);
    });

    $('.hrk_txtb').change(function() {
        $('#high_rejection_keywords_update').val(1);
    });

    var pixelHeadCodeMirror = CodeMirror.fromTextArea(document.getElementById('uni_pixel_header'), {
        theme: 'default',
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true
    });

    pixelHeadCodeMirror.on('change',function(cm){
        $('#header_pixel_status').val(1);
    });

    var pixelBodyCodeMirror = CodeMirror.fromTextArea(document.getElementById('uni_pixel_body'), {
        theme: 'default',
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true
    });

    pixelBodyCodeMirror.on('change',function(cm){
        $('#body_pixel_status').val(1);
    });

    var pixelFootCodeMirror = CodeMirror.fromTextArea(document.getElementById('uni_pixel_footer'), {
        theme: 'default',
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true
    });

    pixelFootCodeMirror.on('change',function(cm){
        $('#footer_pixel_status').val(1);
    });

    $('#campaign_filter_process_status').change(function() {
        $('#campaign_filter_process_status_update').val(1);
    });

    $('#cake_conversions_archiving_age_in_days').change(function() {
        $('#cake_conversions_archiving_age_in_days_update').val(1);
    });

    $('#campaign_type_view_count').change(function() {
        $('#campaign_type_view_count_update').val(1);
    });

    $('#user_nos_before_not_displaying_campaign').change(function() {
        $('#user_nos_before_not_displaying_campaign_update').val(1);
    });

    $('#num_leads_to_process_for_send_pending_leads').change(function() {
        $('#num_leads_to_process_for_send_pending_leads_update').val(1);
    });

    $('#send_pending_lead_cron_expiration').change(function() {
        $('#send_pending_lead_cron_expiration_update').val(1);
    });

    $('#cplchecker_excluded_campaigns').change(function() {
        $('#cplchecker_excluded_campaigns_update').val(1);
    });

    $('#nocpl_recipient').change(function() {
        $('#nocpl_recipient_update').val(1);
    });

    $('#optoutreport_recipient').change(function() {
        $('#optoutreport_recipient_update').val(1);
    });

    $('#ccpaadminemail_recipient').change(function() {
        $('#ccpaadminemail_recipient_update').val(1);
    });
    $('#optoutexternal_recipient').change(function() {
        $('#optoutexternal_recipient_update').val(1);
    });
    $('#optoutemail_replyto').change(function() {
        $('#optoutemail_replyto_update').val(1);
    });
    $('#publisher_percentage_revenue').change(function() {
        $('#publisher_percentage_revenue_update').val(1);
    });
    $('#data_feed_excluded_affiliates').change(function() {
        $('#data_feed_excluded_affiliates_update').val(1);
    });
    $('#excessive_affiliate_subids').change(function() {
        $('#excessive_affiliate_subids_update').val(1);
    });

    CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
    CKEDITOR.config.allowedContent = true;
    
    $('#epicdemand_tcpa').ckeditor({
        removeButtons : 'Underline,Subscript,Superscript,Scayt,RemoveFormat,About,Copy,Paste,PasteText,PasteFromWord,Cut,Anchor'
    });

    $('#pfr_tcpa').ckeditor({
        removeButtons : 'Underline,Subscript,Superscript,Scayt,RemoveFormat,About,Copy,Paste,PasteText,PasteFromWord,Cut,Anchor'
    });

    $('#admired_tcpa').ckeditor({
        removeButtons : 'Underline,Subscript,Superscript,Scayt,RemoveFormat,About,Copy,Paste,PasteText,PasteFromWord,Cut,Anchor'
    });

    $('#clinical_trial_tcpa').ckeditor({
        removeButtons : 'Underline,Subscript,Superscript,Scayt,RemoveFormat,About,Copy,Paste,PasteText,PasteFromWord,Cut,Anchor'
    });

    for (var i in CKEDITOR.instances) {
        CKEDITOR.instances[i].on('change', function(e) {
            if(this.name == 'epicdemand_tcpa') {
                $('#epicdemand_tcpa_status').val(1)
            }else if(this.name == 'pfr_tcpa'){
                $('#pfr_tcpa_status').val(1)
            }else if(this.name == 'admired_tcpa'){
                $('#admired_tcpa_status').val(1)
            }else if(this.name == 'clinical_trial_tcpa'){
                $('#clinical_trial_tcpa_status').val(1)
            }
        });
    }

    $('#full_rejection_alert_status_checkbox').change(function() {
        console.log('full_rejection_alert_status_checkbox toggle: ' + $(this).prop('checked'));

        if($(this).prop('checked'))
        {
            $(this).val(1);
            $('#full_rejection_alert_status').val(1);
        }
        else
        {
            $(this).val(0);
            $('#full_rejection_alert_status').val(0);
        }
        $('#full_rejection_alert_status_update').val(1)
    });
    $('#full_rejection_alert_min_leads,#full_rejection_alert_check_days,#full_rejection_advertiser_email_status,#full_rejection_deactivate_campaign_status, #full_rejection_excluded_campaigns').change(function() {
        $('#full_rejection_alert_status_update').val(1);
    });

    $('#clickLogTrafficSourceSelect').select2({
        placeholder: 'Select the traffic source',
        minimumInputLength: 1,
        maximumSelectionLength: 0,
        theme: 'bootstrap',
        language: {
            inputTooShort: function() {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            },
            searching: function() {
                return "Searching...";
            }
        },
        ajax: {
            url: $('#baseUrl').html()+'/search/select/revenueTrackers',
            dataType: "json",
            type: "POST",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                };

                return queryParameters;
            },
            processResults: function (data) {
                console.log(data);
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.revenue_tracker_id,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    $(document).on('click','#clearClickLogTrafficSourceSelect',function(e)
    {
        $('#clickLogTrafficSourceSelect').val(null).trigger('change');
    });

    $(document).on('click','#addClickLogTrafficBtn',function(e)
    {
        
        var sourceID = $('#clickLogTrafficSourceSelect').val();

        if(sourceID != null) {
            if(confirm('Are you sure you want to add this source?')) {
                console.log($('#clickLogTrafficSourceSelect').val());

                $.ajax({
                    type: 'POST',
                    url: $('#baseUrl').html() + '/add_click_log_source',
                    data: {
                        id : sourceID,
                    },
                    success: function(response){
                        console.log(response);
                        if(response.status == 'success') {
                            var table = $('#clicks-log-source-table').DataTable();
                            table.ajax.reload();
                            alert(response.message)
                        }else{
                            $('#clickLogTrafficSourceSelect').val(null).trigger('change');
                            alert(response.message)
                        }
                    }
                });
            }
        }else {
            alert('Select source first');
        }
    });

    $(document).on('click','.remove-click-log-source',function(e)
    {
        
        var sourceID = $(this).data('id');
        console.log(sourceID);

        if(confirm('Are you sure you want to remove this source?')) {
            $.ajax({
                type: 'POST',
                url: $('#baseUrl').html() + '/delete_click_log_source',
                data: {
                    id : sourceID,
                },
                success: function(response){
                    var table = $('#clicks-log-source-table').DataTable();
                    table.ajax.reload();
                    alert('Source successfully removed!')
                }
            });
        }
    });

    var clickLogsTable = $('#clicks-log-source-table').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax':{
            url:$('#baseUrl').html() + '/getClickLogSources', // json datasource
            type: 'post', 
            "dataSrc": function ( json ) {
                console.log(json);
                var rows = [];

                $(json.data).each(function(i, row) {
                    var btn = '<button data-id="'+row.id+'" type="button" class="remove-click-log-source btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span></button>';
                    rows.push([
                        row.affiliate_id + ' / ' + row.revenue_tracker_id,
                        row.created_at,
                        btn
                    ])
                });
                return rows;
            },
        },
        'columns': [
            null,
            null,
            { 'orderable': false }
        ],
    });

    $('#click_log_num_days, #clic_logs_apply_all_affiliates').change(function() {
        $('#click_log_tracing_update').val(1);
    });

    if($('#clic_logs_apply_all_affiliates').prop('checked')) {
        $('#addClickLogTrafficBtn').addClass('disabled');
    }
    
    $(document).on('change','#clic_logs_apply_all_affiliates',function(e)
    {
        if($(this).prop('checked')) {
            $('#addClickLogTrafficBtn').addClass('disabled');
        }else {
            $('#addClickLogTrafficBtn').removeClass('disabled');
        }
    });
});