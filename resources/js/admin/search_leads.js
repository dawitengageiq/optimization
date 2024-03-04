$(document).ready(function()
{
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    $('.draggable-drilldown-modal').draggable({
        handle: ".modal-header"
    });

    // var the_url = $('#baseUrl').html() + '/getSearchLeads';
    // var searchLeadsTable = $('#leads-table').DataTable({
    //     responsive: true,
    //     "order": [[ 0, "desc" ]],
    //     lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
    //     columnDefs: [
    //         { width: '7%', targets: 0 },
    //         { width: '1%', targets: [9,10] }
    //     ],
    //     "searching": false,
    //     'processing': true,
    //     'serverSide': true,
    //     'ajax':{
    //         url:the_url, // json datasource
    //         type: 'post',
    //         // 'data': $('#search-leads').serialize(),
    //         'data': function(d)
    //         {
    //             d.lead_id = $('#lead_id').val();
    //             d.campaign_id = $('#campaign_id').val();
    //             d.affiliate_id = $('#affiliate_id').val();
    //             d.lead_email = $('#lead_email').val();
    //             d.lead_status = $('#lead_status').val();
    //             d.lead_date_from = $('#lead_date_from').val();
    //             d.lead_date_to = $('#lead_date_to').val();
    //             d.s1 = $('#s1').val();
    //             d.s2 = $('#s2').val();
    //             d.s3 = $('#s3').val();
    //             d.s4 = $('#s4').val();
    //             d.s5 = $('#s5').val();
    //             d.table = $('#table').val();
    //         },
    //         "dataSrc": function ( json ) {
    //             // alert("Done!");
    //             $('#downloadSearchedLeads').removeAttr('disabled');
    //             console.log('Load');
    //             return json.data;
    //         },
    //         error: function(data){  // error handling
    //             console.log(data);
    //         }
    //     },
    //     lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    // });

    $('.campaignField').select2({
        theme: 'bootstrap'
    });

    var searchLeadsTable = $('#leads-table').DataTable({
        responsive: true,
        "order": [[ 0, "desc" ]],
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
        columnDefs: [
            { width: '7%', targets: 0 },
            { width: '1%', targets: [9,10] }
        ],
        "searching": false
        // lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });

    $(document).on('change','#show_inactive_campaign',function(e)
    {
        console.log($(this).prop('checked'))
        if($(this).prop('checked')) {
            $('#showAllCampaigns-container').show();
            $('#showActiveCampaigns-container').hide();
            $('#realCampaignId').val($('[name="fake_campaign_id_all"]').val());
        }else {
            $('#showAllCampaigns-container').hide();
            $('#showActiveCampaigns-container').show();
            $('#realCampaignId').val($('[name="fake_campaign_id_active"]').val());
        }
    });

    $(document).on('change','[name="fake_campaign_id_all"]',function(e)
    {
        console.log('all');
        console.log($(this).val())
        if($('#show_inactive_campaign').prop('checked')) {
            $('#realCampaignId').val($('[name="fake_campaign_id_all"').val());
            console.log('update the other one');
            $('[name="fake_campaign_id_active"]').val($(this).val()).trigger('change');
        }
        
    });

    $(document).on('change','[name="fake_campaign_id_active"]',function(e)
    {
        console.log('active');
        console.log($(this).val())
        if(!$('#show_inactive_campaign').prop('checked')) {
            $('#realCampaignId').val($('[name="fake_campaign_id_active"').val());
            console.log('update the other one');
            $('[name="fake_campaign_id_all"]').val($(this).val()).trigger('change');
        }
    });

    $(document).on('click','#searchedLeadsBtn',function(e)
    {
        e.preventDefault();
        $('label[for="lead_date_from"]').removeClass('error_label error');
        $('#lead_date_from').removeClass('error_field error');
        $('label[for="lead_date_to"]').removeClass('error_label error');
        $('#lead_date_to').removeClass('error_field error');

        console.log('Test');
        var from_date = $('#lead_date_from').val(),
            to_date = $('#lead_date_to').val();

        if(from_date == '' || to_date == '') {
            $('#search-leads').submit();
            // $('#downloadSearchedLeads').attr('disabled',true);
            // searchLeadsTable.ajax.reload();
        } else if(from_date != '' && to_date != '' && to_date >= from_date) {
            $('label[for="lead_date_from"]').removeClass('error_label error');
            $('#lead_date_from').removeClass('error_field error');
            $('label[for="lead_date_to"]').removeClass('error_label error');
            $('#lead_date_to').removeClass('error_field error');
            $('#search-leads').submit();
            // $('#downloadSearchedLeads').attr('disabled',true);
            // searchLeadsTable.ajax.reload();
        } else {
            $('label[for="lead_date_from"]').addClass('error_label error');
            $('#lead_date_from').addClass('error_field error');
            $('label[for="lead_date_to"]').addClass('error_label error');
            $('#lead_date_to').addClass('error_field error');
        }
    });

    $('#leads-table tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        } else {
            searchLeadsTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });

    // $('#table').change(function()
    // {
    //     var selectedTable = $(this).val();
    //
    //     if(selectedTable=='leads')
    //     {
    //         //show the resend button
    //         $('#resend-leads').show();
    //     }
    //     else
    //     {
    //         $('#resend-leads').hide();
    //     }
    // });

    $('#clear').click(function()
    {
        var form = $('#search-leads');

        form.find('input:text, input:password, input:file, select, textarea').val('');
        form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $('#realCampaignId').val('');
        $('.campaignField').trigger('change.select2');
        $('#limit_rows').val(200);
        $('#table').val('leads');
    });

    $(document).on('click','.show-details',function()
    {
        var lead = $(this),
            leadID = lead.data('lead_id'),
            leadName = lead.data('name'),
            leadRCount = lead.data('rcount'),
            leadRDate = lead.data('rdate'),
            getDetailsURL = '',
            the_table = $('#table').val();

        
        if(the_table == 'leads_archive') {
            getDetailsURL = $('#baseUrl').val()+'/admin/leadsArchive/'+leadID+'/getLeadDetails';
        }else {
            getDetailsURL = $('#baseUrl').val()+'/admin/leads/'+leadID+'/getLeadDetails';
        }
        console.log(getDetailsURL);

        /* Modal Name */
        $('#lead_details_modal .modal-title').html('Leads Detail: <b>' + leadID + ' - ' + leadName + '</b>');

        // console.log(leadID);

        $('#id').val(leadID);

        $('#retry_count').val(leadRCount);
        $('#retry_date').val(leadRDate);

        $.ajax({
            type: 'POST',
            url: getDetailsURL,
            data: {
                table : the_table
            },
            success: function(data)
            {
                console.log(data);

                // if(data.leadDataCSV!==undefined || data.leadDataCSV!==null)
                if(data.leadDataCSV)
                {
                    $('#lead_csv').val(data.leadDataCSV.value)
                }

                //if(data.leadMessage!==undefined || data.leadMessage!==null)
                if(data.leadMessage)
                {
                    $('#message').val(data.leadMessage.value)
                }

                // if(data.leadDataADV!==undefined || data.leadDataADV!==null)
                if(data.leadDataADV)
                {
                    $('#advertiser_data').val(data.leadDataADV.value)
                }

                //if(data.leadSentResult!==undefined || data.leadSentResult!==null)
                if(data.leadSentResult)
                {
                    $('#sent_result').val(data.leadSentResult.value)
                }
                $('#lead_details_modal').modal('show');
            },
            error: function(data)
            {
                console.log(data);
            }
        });

        //$('#lead_details_modal').modal('show');
    });

    $('#lead_details_modal').on('hide.bs.modal', function (event)
    {
        $('#lead_csv').val('');
        $('#message').val('');
        $('#advertiser_data').val('');
        $('#sent_result').val('');
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('#select-all').click(function()
    {
        //$('#leads-table').closest('tr').find('.checkbox').prop('checked', this.checked);
        $('#leads-table tr').each(function(i,row){
            var tableRow = $(row);
            tableRow.find('.checkbox').prop('checked',true);
        });
    });

    $('#de-select-all').click(function()
    {
        $('#leads-table tr').each(function(i,row){
            var tableRow = $(row);
            tableRow.find('.checkbox').prop('checked',false);
        });
    });

    $('#resend-leads').click(function()
    {
        console.log('resend leads');

        // get the table
        var tableName = $(this).data('table');
        console.log('table: '+tableName);

        var url = $('#baseUrl').val() + '/updateLeadsToPendingStatus/';
        var leadIDs = [];

        //get all lead id
        $('#leads-table tr').each(function(i,row){
            var tableRow = $(row);
            var checkBox = tableRow.find('.checkbox');
            var leadID = checkBox.data('lead_id');

            if(checkBox.prop('checked') && leadID!==undefined)
            {
                leadIDs.push(leadID);
            }
        });

        var strLeadIDs = '';
        // pass all ids via url
        for(var i=0;i<leadIDs.length;i++)
        {
            strLeadIDs += leadIDs[i]+',';
        }

        // remove the last comma
        strLeadIDs = strLeadIDs.substr(0,strLeadIDs.length-1);
        console.log(strLeadIDs);

        if(strLeadIDs !== ''){
            url += strLeadIDs;
            // include the table name in the request
            url += '?table='+tableName;

            console.log(url);

            $.ajax({
                type: 'POST',
                url: url,
                success: function(data)
                {
                    console.log(data);
                    alert('Leads updated to pending!');
                    //submit the form to refresh the entire page
                    $('#search-leads').submit();
                }
            });
        }
    });

    // $('#search-leads').submit(function() {
    //     $('#searchedLeadsBtn').attr('disabled',true);
    //     // $('#downloadSearchedLeads').attr('disabled',true);
    //     // $('#clear').attr('disabled',true);
    // });

    $('#max_high_reject_rate').keyup(function() {
        var max_high = Number($(this).val());
        var min_critical = 0;
        if(max_high < 100) min_critical = max_high + .1;
        else min_critical = 100;
        $('#min_critical_reject_rate').val(min_critical);
    });
});

$(document).on('click','.openSettingBtn',function(e) 
{
    e.preventDefault();
    var btn = $(this),
        modal = $('#settingsModal .appendHere');
    btn.attr('disabled', true);
    $.ajax({
        type: 'GET',
        url: $('#baseUrl').val() + '/getLeadRejectionRateSettings/',
        success: function(data)
        {
            console.log(data);
            btn.removeAttr('disabled');

            $('#min_high_reject_rate').val(data.rates[0]);
            $('#max_high_reject_rate').val(data.rates[1]);
            $('#min_critical_reject_rate').val(Number(data.rates[1]) + .1);
            $('.lrrs-div').remove();
            $.each(data.keywords, function(key, words){
                var name = data.names[key];
                modal.append('<div class="col-md-6 lrrs-div" style="margin-top:5px"><label>'+name+'</label><textarea class="form-control this_field hrk_txtb" rows="2" name="high_rejection_keywords['+key+']">'+words+'</textarea></div>')           

            });
            $('#settingsModal').modal('show');
        }
    });
    
});