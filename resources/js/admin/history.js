$.fn.dataTable.ext.errMode = 'throw';

var section = 0;
var reference_id = 0;
var table_history;
var table_details;
var history_of = '';
var open_modal = false;

function redrawDetails(query_string) {
    if(typeof table_details === 'object') table_details.clear().destroy();

    table_details = $('#details-table').DataTable( {
        'ajax': $('#baseUrl').val() + '/user-activities/' + section + '/details' + query_string,
        'processing': true,
        'serverSide': true,
        'searching': false,
        'sorting': false,
        'ordering': false,
        'paging': false,
        'columnDefs': [
            {
                targets: 0,
                className: 'text-center',
            },
            {
                targets: 1,
                className: 'text-center',
            },
            {
                targets: -1,
                className: 'text-center',
            }
        ]
    } );
}

function redrawHistory () {
    if(open_modal) return;

    table_history = $('#history-table').DataTable( {
        'ajax': $('#baseUrl').val() + '/user-activities/' + section + '/details/' + reference_id,
        'processing': true,
        'serverSide': true,
        'searching': false,
        'sorting': false,
        'ordering': false,
        'columnDefs': [
            {
                targets: 1,
                className: 'text-center',
            },
            {
                targets: 2,
                className: 'text-center',
            },
            {
                targets: 3,
                className: 'text-center',
            },
            {
                targets: 4,
                className: 'text-center',
            },
            {
                targets: 5,
                width: '90px',
                className: 'text-center',
            },
            {
                targets: 6,
                width: '90px',
                className: 'text-center',
            },
            {
                targets: -1,
                'data': null,
                className: 'align-middle text-center',
                'defaultContent': '<a href="javascript:void(0)" class=""><i class="fa fa-list"></i></i></a>'
            }
        ]
    } );
}

function closeNav() {
    document.getElementById("history_details").style.width = "0";
    $('#details_wrap').hide();
}

$(function() {
    section = $('#section_id').val();

    // Click on details
    $('.table-history tbody').on( 'click', 'a, i', function () {
        var data = table_history.row( $(this).parents('tr') ).data();
        query_string = '/' + data[3] + '/' + data[4] + '?date=' + data[6];
        $('.history_details h3 span').text(data[4]);

        redrawDetails(query_string);

        document.getElementById("history_details").style.width = "100%";
        // setTimeout(function(){
            $('#details_wrap').show();
        // }, 100);
    } );

    // Tab toggle
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if(target == '#tab-info') return;

        reference_id = $('#reference_id').val();

        redrawHistory();

        open_modal = true;
    });

    // close modal
    $(".modal").on("hide.bs.modal", function () {
        $('#info-tab').trigger('click');

        if(typeof table_history === 'object' && $('#tab-with-history').is(":visible") == true) table_history.clear().destroy();

        closeNav();

        open_modal = false;
    });

});
