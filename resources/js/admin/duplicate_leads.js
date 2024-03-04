$(document).ready(function()
{
    var table = $('.table-datatable').DataTable({
        responsive: true,
        "order": [[ 6, "desc" ]],
        lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
    });

    $('#clear').click(function()
    {
        $('.this_field').val('');
        $('#limit_rows').val(200);
        $('#table').val(1);
    });

    $('#leads-table tbody').on( 'click', 'tr', function () {

        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $(document).on('click','#duplicateLeadsSearchBtn',function(e)
    {
        e.preventDefault();
        var from_date = $('#lead_date_from').val(),
            to_date = $('#lead_date_to').val();

        if(from_date != '' && to_date != '' && to_date >= from_date) {
            $('label[for="lead_date_from"]').removeClass('error_label error');
            $('#lead_date_from').removeClass('error_field error');
            $('label[for="lead_date_to"]').removeClass('error_label error');
            $('#lead_date_to').removeClass('error_field error');
            $('#search-leads').submit();
        }else {
            $('label[for="lead_date_from"]').addClass('error_label error');
            $('#lead_date_from').addClass('error_field error');
            $('label[for="lead_date_to"]').addClass('error_label error');
            $('#lead_date_to').addClass('error_field error');
        }
    });

    $('#search-leads').submit(function() {
        $('#duplicateLeadsSearchBtn').attr('disabled',true);
        // $('#downloadSearchedLeads').attr('disabled',true);
        // $('#clear').attr('disabled',true);
    });
});