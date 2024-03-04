$(document).ready(function()
{
    //var the_url = $('#baseUrl').html() + '/admin/creativeStatistics';
    var creativeStatsTable = $('#creativeStats-table').DataTable({
        lengthMenu: [[100,1000,2000,3000],[100,1000,2000,3000]],
        "oSearch": {"bSmart": false},
        'order': [[ 5, "desc" ]],
        // "searching": false,
        "columnDefs": [ {
            "targets": 1,
            "orderable": false
        } ]
    });

    $('#creativeId_thead').html('<input type="text" placeholder="Search Creative ID" />');

    $( 'input', creativeStatsTable.column(1).header()).on( 'keyup change', function () {
        var that = creativeStatsTable.column(1);
        if ( that.search() !== this.value ) {
            that
                .search( this.value )
                .draw();
        }
    } );

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

    // $('.lead_date').change(function() {
    //     if($(this).val() != '') {
    //         $('#date_range').val('');
    //     }
    // });

    $('#clear').click(function()
    {
        var form = $('#revStats-form');

        form.find('input:text, input:password, input:file, select, textarea').val('');
        $('#group_by').val('date');
        $('#realCampaignId').val('');
        $('.campaignField').trigger('change.select2');
        form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
    });

    $('#generateReportBtn').click(function(e) {
        e.preventDefault();
        var from_date = $('#lead_date_from').val(),
            to_date = $('#lead_date_to').val(),
            form = $('#revStats-form');

        $('label[for="lead_date_from"]').removeClass('error_label error');
        $('#lead_date_from').removeClass('error_field error');
        $('label[for="lead_date_to"]').removeClass('error_label error');
        $('#lead_date_to').removeClass('error_field error');

        if(from_date == '' || to_date == '') {
            form.submit();
        }
        else if(from_date != '' && to_date != '' && to_date >= from_date) {
            $('label[for="lead_date_from"]').removeClass('error_label error');
            $('#lead_date_from').removeClass('error_field error');
            $('label[for="lead_date_to"]').removeClass('error_label error');
            $('#lead_date_to').removeClass('error_field error');
            form.submit();
        }else {
            $('label[for="lead_date_from"]').addClass('error_label error');
            $('#lead_date_from').addClass('error_field error');
            $('label[for="lead_date_to"]').addClass('error_label error');
            $('#lead_date_to').addClass('error_field error');
        }
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
});
