$(document).ready(function()
{
    var the_url = $('#baseUrl').html() + '/getHistoryCronJob';
    var cronTable = $('#cron-job-table').DataTable({
        "order": [[ 0, "desc" ]],
        "columns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            { "orderable": false }
        ],
        'processing': true,
        'serverSide': true,
        'ajax':{
            url:the_url, // json datasource
            type: 'post',
            "dataSrc": function ( json ) {
                console.log(json);
                return json.data;
            }
        },
        "createdRow": function( row, data, dataIndex ) {
            if ( data[7] == "Done" ) {
                $(row).addClass( 'danger' );
            }else {
                $(row).addClass( 'success' );
            }
        },
        lengthMenu: [[1000,2000,3000],[1000,2000,3000]]
    });

    // setInterval(function(){
    //     cronTable.ajax.reload();
    // }, 60000);

    $(document).on('click','.more-details',function()
    {
        var this_cron = $(this);
        var id = this_cron.data('id'),
            lq = this_cron.data('lq'),
            lp = this_cron.data('lp'),
            lw = this_cron.data('lw'),
            ts = this_cron.data('ts'),
            te = this_cron.data('te'),
            ti = this_cron.data('ti'),
            status = this_cron.data('status'),
            leads = $('#crn-'+id+'-leadids').val().split(',');

        // Total # of cell per row - total # of lead ids radius by 10 + total # of lead ids
        var total_num_cells = 10 - (leads.length % 10) + leads.length,
            total_num_rows = total_num_cells / 10,
            leads_table = '';

        for(x = 0; x < total_num_cells; x++) {
            if(x % 10 == 0) {
                leads_table += '<tr>';
            }

            if(typeof leads[x] == 'undefined' || leads[x] == 'undefined') {
                lead_display = '';
            }else {
                lead_display = leads[x];
            }
            leads_table += '<td>'+lead_display+'</td>';
            if(x % 10 == 9) {
                leads_table += '</tr>';
            }
        }

        $('#leads-id-table tbody').html(leads_table);
        $('#cj-id').html(id);
        $('#cj-s').html(status);
        $('#cj-q').html(lq);
        $('#cj-p').html(lp);
        $('#cj-w').html(lw);
        $('#cj-ts').html(ts);
        $('#cj-te').html(te);
        $('#cj-ti').html(ti);

        $('#more-details-modal').modal('show');
    });
});