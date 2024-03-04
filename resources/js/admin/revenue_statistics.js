$(document).ready(function()
{
    var the_url = $('#baseUrl').html() + '/getRevenueStatistics';
    var revStatsTable = $('#revStats-table').DataTable({
        'processing': true,
        'serverSide': true,
        "order": [[ 0, "desc" ]],
        "searching": false,
        // "info":     false,
        'ajax':{
            url:the_url, // json datasource
            type: 'post',
            // 'data': $('#search-leads').serialize(),
            'data': function(d)
            {
                d.campaign_id = $('#realCampaignId').val();
                d.affiliate_id = $('#affiliate_id').val();
                d.lead_status = $('#lead_status').val();
                d.containing_data = $('#containing_data').val();
                d.s1 = $('#s1').val();
                d.s2 = $('#s2').val();
                d.s3 = $('#s3').val();
                d.s4 = $('#s4').val();
                d.s5 = $('#s5').val();
                d.lead_date_from = $('#lead_date_from').val();
                d.lead_date_to = $('#lead_date_to').val();
                d.date_range = $('#date_range').val();
                d.group_by = $('#group_by').val();
                d.group_by_column = $('#group_by_column').val();
                d.table = $('#table').val();
                d.affiliate_type = $('#affiliate_type').val();
                d.sib_s1 = $('#sib_s1').prop('checked');
                d.sib_s2 = $('#sib_s2').prop('checked');
                d.sib_s3 = $('#sib_s3').prop('checked');
                d.sib_s4 = $('#sib_s4').prop('checked');

                if($('#show_inactive_campaign').prop('checked')) {
                    d.show_inactive_campaign = 1;
                }
            },
            "dataSrc": function ( json ) {
                // alert("Done!");
                $('#downloadRevenueReport').removeClass('disabled');
                console.log(json);
                $(revStatsTable.column(14).footer()).html(json.totalLeadCount);
                $(revStatsTable.column(15).footer()).html(json.totalCost);
                $(revStatsTable.column(16).footer()).html(json.totalRevenue);
                $(revStatsTable.column(17).footer()).html(json.totalProfit);
                $('#generateReportBtn').removeAttr('disabled');
                return json.data;
            },
            error: function(data){  // error handling
                console.log(data);
            }
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
    });

    $('#revStats-form').submit(function(e)
    {
        e.preventDefault();
        var from_date = $('#lead_date_from').val(),
            to_date = $('#lead_date_to').val(),
            this_button = $('#generateReportBtn');


        if(from_date == '' || to_date == '') {
            //remove the currently selected ordering
            $('#downloadRevenueReport').addClass('disabled');
            this_button.attr('disabled',true);
            revStatsTable.order([]);
            revStatsTable.ajax.reload();
        }
        else if(from_date != '' && to_date != '' && to_date >= from_date) {
            $('label[for="lead_date_from"]').removeClass('error_label error');
            $('#lead_date_from').removeClass('error_field error');
            $('label[for="lead_date_to"]').removeClass('error_label error');
            $('#lead_date_to').removeClass('error_field error');

            //remove the currently selected ordering
            $('#downloadRevenueReport').addClass('disabled');
            this_button.attr('disabled',true);
            revStatsTable.order([]);
            revStatsTable.ajax.reload();
        }else {
            $('label[for="lead_date_from"]').addClass('error_label error');
            $('#lead_date_from').addClass('error_field error');
            $('label[for="lead_date_to"]').addClass('error_label error');
            $('#lead_date_to').addClass('error_field error');
        }
    });

    $('#date_range').change(function() {
        if($(this).val() != '') {
            $('#lead_date_from').val('');
            $('#lead_date_to').val('');
        }
    });

    $('.lead_date').change(function() {
        if($(this).val() != '') {
            $('#date_range').val('');
        }
    });

    $('#clear').click(function()
    {
        var form = $('#revStats-form');

        form.find('input:text, input:password, input:file, select, textarea').val('');
        $('#group_by').val('date');

        form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $('#realCampaignId').val('');
        $('.campaignField').trigger('change.select2');
    });

    $('.show-details').click(function()
    {
        var leadID = $(this).data('lead_id');
        var getDetailsURL = $('#baseUrl').val()+'/admin/leads/'+leadID+'/getLeadDetails';

        console.log(leadID);

        $('#id').val(leadID);

        $.ajax({
            type: 'POST',
            url: getDetailsURL,
            success: function(data)
            {
                console.log(data);

                if(data.leadDataCSV!==undefined || data.leadDataCSV!==null)
                {
                    $('#lead_csv').val(data.leadDataCSV.value)
                }

                if(data.leadMessage!==undefined || data.leadMessage!==null)
                {
                    $('#message').val(data.leadMessage.value)
                }

                if(data.leadDataADV!==undefined || data.leadDataADV!==null)
                {
                    $('#advertiser_data').val(data.leadDataADV.value)
                }

                if(data.leadSentResult!==undefined || data.leadSentResult!==null)
                {
                    $('#sent_result').val(data.leadSentResult.value)
                }
            },
            error: function(data)
            {
                console.log(data);
            }
        });

        $('#lead_details_modal').modal('show');
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('.campaignField').select2({
        theme: 'bootstrap'
    });

    $('#group_by_column').change(function()
    {
        if($(this).val() != '') {
            $('.sibs').prop('checked', true).attr('disabled', true);
        }else {
            $('.sibs').removeAttr('disabled');
        }
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
});