$(document).ready(function()
{
    var the_url = $('#baseUrl').html() + '/getCoregReports';

    var the_rows = [];

    var revStatsTable = $('#coregReport-table').DataTable({
        'processing': true,
        'serverSide': true,
        "order": [[ 13, "desc" ]],
        "searching": false,
        // "info":     false,
        'ajax':{
            url:the_url, // json datasource
            type: 'post',
            'data': function(d)
            {
                d.campaign_id = $('#realCampaignId').val();
                d.affiliate_id = $('#affiliate_id').val();
                d.revenue_tracker_id = $('#revenue_tracker_id').val();
                d.group_by_column = $('#group_by_column').val();
                d.lead_date = $('#lead_date').val();

                if($('#show_inactive_campaign').prop('checked')) {
                    d.show_inactive_campaign = 1;
                }
            },
            "dataSrc": function ( json ) {
                // alert("Done!");
                $('#downloadRevenueReport').removeClass('disabled');
                console.log(json);
                $('#snapshot-date').html(json.date);
                $('#total-net-profit').html(json.profit);
                $('#total-cost').html(json.cost);
                $('#total-revenue').html(json.revenue);
                $('#generateReportBtn').removeAttr('disabled');

                the_rows = json.rows;
                return json.data;
            },
            error: function(data){  // error handling
                console.log(data);
            }  
        },
        "createdRow": function( row, data, dataIndex ) {
            if(the_rows[dataIndex] == 1) {
                $(row).addClass( 'success' );
            }else if(the_rows[dataIndex] == 2){
                $(row).addClass( 'danger' );
            }else if(the_rows[dataIndex] == 3){
                $(row).addClass( 'warning' );
            }else if(the_rows[dataIndex] == 4){
                $(row).addClass( 'info' );
            }
            // console.log('Created Row');
            // console.log(row);
            // console.log(data);
            // console.log(dataIndex);
            // if ( data[7] == "Done" ) {
            //     $(row).addClass( 'danger' );
            // }else {
            //     $(row).addClass( 'success' );
            // }
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
        "sDom": 'lf<"addToolbar">rtip'
    });
    
    $("div.addToolbar").html($('#coregReportLegendDiv').html());
    // $("div.addToolbar").html('<label> Filter: <select id="filter" name="filter" class="form-control input-sm"><option value="2">All</option><option value="0">Unedited</option><option value="1">Edited</option></select></label>');

    $('#coregReport-form').submit(function(e)
    {
        e.preventDefault();

        $('label[for="affiliate_id"]').removeClass('error_label error');
        $('#affiliate_id').removeClass('error_field error');
        $('label[for="revenue_tracker_id"]').removeClass('error_label error');
        $('#revenue_tracker_id').removeClass('error_field error');

        var affiliate = $('#affiliate_id').val(),
            revTracker = $('#revenue_tracker_id').val(),
            this_button = $('#generateReportBtn'),
            noError = true;

        if(isNaN(affiliate) || affiliate < 0) {
            $('label[for="affiliate_id"]').addClass('error_label error');
            $('#affiliate_id').addClass('error_field error');
            noError = false;
        }

        if(isNaN(revTracker) || revTracker < 0) {
            $('label[for="revenue_tracker_id"]').addClass('error_label error');
            $('#revenue_tracker_id').addClass('error_field error');
            noError = false;
        }

        if(noError) {
            $('label[for="affiliate_id"]').removeClass('error_label error');
            $('#affiliate_id').removeClass('error_field error');
            $('label[for="revenue_tracker_id"]').removeClass('error_label error');
            $('#revenue_tracker_id').removeClass('error_field error');
            $('#downloadRevenueReport').addClass('disabled');
            this_button.attr('disabled',true);
            revStatsTable.ajax.reload();
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
        var form = $('#coregReport-form');

        form.find('input:text, input:password, input:file, select, textarea').val('');

        form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        $('#realCampaignId').val('');
        $('.campaignField').trigger('change.select2');
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