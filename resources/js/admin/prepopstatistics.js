$(document).ready(function()
{
    $('#affiliate_id').select2({
        theme: 'bootstrap'
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    var baseURL = $('#baseUrl');

    var prepopStatisticsURL = baseURL.html()+'/admin/prepopReportStatistics';

    // var prepopDataTable = $('#prepop-table').DataTable({
    //     'processing': true,
    //     'serverSide': true,
    //     'searching': false,
    //     'order': [[ 0, "asc" ]], //disable the initial ordering
    //     'ajax':{
    //         url: prepopStatisticsURL,
    //         type: 'POST',  // method  , by default get
    //         'data': function(d)
    //         {
    //             d.group_by = $('#group_by').val();
    //             d.date_from = $('#date_from').val();
    //             d.date_to = $('#date_to').val();
    //             d.predefined_date = $('#predefined_date').val();
    //             d.affiliate_id = $('#affiliate_id').val();
    //             d.s1 = $('#s1').val();
    //             d.s2 = $('#s2').val();
    //             d.s3 = $('#s3').val();
    //             d.s4 = $('#s4').val();
    //             d.s5 = $('#s5').val();
    //             d.sib_s1 = $('#sib_s1').prop('checked');
    //             d.sib_s2 = $('#sib_s2').prop('checked');
    //             d.sib_s3 = $('#sib_s3').prop('checked');
    //             d.sib_s4 = $('#sib_s4').prop('checked');
    //         },
    //         error: function()
    //         {
    //             // error handling
    //         },
    //         'dataSrc': function(json)
    //         {
    //             var dataCount = json.data.length;

    //             var downloadReportButton = $('#download_report');

    //             //if dataCount is greater than 0 meaning there's data to be downloaded
    //             if(dataCount>0)
    //             {
    //                 //$('#download_report').show();
    //                 //var downloadReport = $('#download_report');
    //                 //downloadReport.css('display','inline-block');
    //                 //add additional inform in download url
    //                 //var originalURL = downloadReport.attr('href');
    //                 //originalURL = originalURL+'?grouping='+json.grouping+'&date_range='+json.date_range;
    //                 //var downloadURL = $('#baseUrl').html()+'/downloadPrepopStatisticsReport?grouping='+json.grouping+'&date_range='+json.date_range;

    //                 var s1 = $('#s1').val(),
    //                     s2 = $('#s2').val(),
    //                     s3 = $('#s3').val(),
    //                     s4 = $('#s4').val(),
    //                     s5 = $('#s5').val();

    //                 downloadReportButton.removeClass('disabled');

    //                 var downloadURL = $('#baseUrl').html()+'/downloadPrepopStatisticsReport?group_by='+json.group_by+'&date_from='+json.date_from+'&date_to='+json.date_to+'&predefined_date='+json.predefined_date+'&affiliate_id='+json.affiliate_id+'&report_title='+json.report_title;
    //                     downloadURL += '&s1=' + s1 + '&s2=' + s2 + '&s3=' + s3 + '&s4=' + s4 + '&s5=' + s5 + '&sib_s1=' + $('#sib_s1').prop('checked') + '&sib_s2=' + $('#sib_s2').prop('checked') + '&sib_s3=' + $('#sib_s3').prop('checked') + '&sib_s4=' + $('#sib_s4').prop('checked');
    //                 downloadReportButton.attr('href', downloadURL);

    //                 // downloadReportButton.show();
    //                 downloadReportButton.css('display', 'inline-block');
    //             }
    //             else
    //             {
    //                 downloadReportButton.hide();
    //                 downloadReportButton.addClass('disabled');
    //             }

    //             return json.data;
    //         }
    //     },
    //     'columns':[
    //         {'data': 'date','width': '10%'},
    //         {'data':'affiliate_id'},
    //         {'data':'revenue_tracker_id'},
    //         {'data':'s1', 'width' : '10%'},
    //         {'data':'s2'},
    //         {'data':'s3'},
    //         {'data':'s4'},
    //         {'data':'s5'},
    //         {'data':'total_clicks'},
    //         {'data':'prepopped'},
    //         {'data':'not_prepopped'},
    //         {'data':'prepopped_with_errors',},
    //         {'data':'percentage_prepopped'},
    //         {'data':'percentage_unprepopped'},
    //         {'data':'percentage_prepopped_with_errors'},
    //         {'data':'profit_margin'}
    //     ],
    //     //'initComplete': rowInitComplete,
    //     lengthMenu: [[25,50,100,-1],[25,50,100,'ALL']]
    // });

    // $('#generate_report').click(function()
    // {
    //     //prepopDataTable.order([[ 0, "asc" ]]);
    //     prepopDataTable.ajax.reload();
    // });

    function prePopStatisticsReport() {
        the_url = baseURL.html() + '/admin/getprepopReportStats';

        var table = $('#prepop-table'),
            body = $('#prepop-table tbody');

        var downloadReportButton = $('#download_report');
        var groupBy = $('#group_by').val();

        table.DataTable().destroy();
        body.empty();

        downloadReportButton.addClass('disabled');

        $.ajax({
            type: 'POST',
            url: the_url,
            data: {
                group_by : $('#group_by').val(),
                date_from : $('#date_from').val(),
                date_to : $('#date_to').val(),
                predefined_date : $('#predefined_date').val(),
                affiliate_id : $('#affiliate_id').val(),
                s1 : $('#s1').val(),
                s2 : $('#s2').val(),
                s3 : $('#s3').val(),
                s4 : $('#s4').val(),
                s5 : $('#s5').val(),
                sib_s1 : $('#sib_s1').prop('checked'),
                sib_s2 : $('#sib_s2').prop('checked'),
                sib_s3 : $('#sib_s3').prop('checked'),
                sib_s4 : $('#sib_s4').prop('checked'),
            },
            success: function(data){
                console.log(data);
                console.log(data.records.length)

                if(data.records.length > 0)
                {
                    var params = data.params;

                    var s1 = $('#s1').val(),
                        s2 = $('#s2').val(),
                        s3 = $('#s3').val(),
                        s4 = $('#s4').val(),
                        s5 = $('#s5').val();

                    var downloadURL = $('#baseUrl').html()+'/downloadPrepopStatisticsReport?group_by='+$('#group_by').val()+'&date_from='+$('#date_from').val()+'&date_to='+$('#date_to').val()+'&predefined_date='+$('#predefined_date').val()+'&affiliate_id='+$('#affiliate_id').val()+'&report_title='+data.report_title;
                        downloadURL += '&s1=' + s1 + '&s2=' + s2 + '&s3=' + s3 + '&s4=' + s4 + '&s5=' + s5 + '&sib_s1=' + $('#sib_s1').prop('checked') + '&sib_s2=' + $('#sib_s2').prop('checked') + '&sib_s3=' + $('#sib_s3').prop('checked') + '&sib_s4=' + $('#sib_s4').prop('checked');
                    downloadReportButton.attr('href', downloadURL);
                    downloadReportButton.removeClass('disabled');
                }

                $.each(data.records, function(x, row){

                    var createdAt = '', affiliate_id = '', revenue_tracker_id = '', s1 = '', s2 = '', s3 = '', s4 = '', s5 = '';
                    if(groupBy == 'affiliate_id'){
                        createdAt = row.created_at;
                        affiliate_id = row.affiliate_id;
                    }else if(groupBy == 'revenue_tracker_id') {
                        createdAt = row.created_at;
                        revenue_tracker_id = row.revenue_tracker_id
                    }else{
                        createdAt = row.created_at;
                        affiliate_id = row.affiliate_id;
                        revenue_tracker_id = row.revenue_tracker_id
                        s1 = row.s1 === null ? '' : row.s1;
                        s2 = row.s2 === null ? '' : row.s2;
                        s3 = row.s3 === null ? '' : row.s3;
                        s4 = row.s4 === null ? '' : row.s4;
                        s5 = row.s5 === null ? '' : row.s5;
                    }

                    s1 = params.sib_s1 == 'true' ? s1 : '';
                    s2 = params.sib_s2 == 'true' ? s2 : '';
                    s3 = params.sib_s3 == 'true' ? s3 : '';
                    s4 = params.sib_s4 == 'true' ? s4 : '';
                    s5 = params.sib_s5 == 'true' ? s5 : '';

                    var prepop_percentage = (row.prepop_count / row.total_clicks) * 100,
                    not_prepop_percentage = (row.no_prepop_count / row.total_clicks) * 100,
                    error_percentage = (row.prepop_with_errors_count / row.total_clicks) * 100;
                    var trow = '<tr><td>'+createdAt+'</td><td>'+affiliate_id+'</td><td>'+revenue_tracker_id+'</td><td>'+s1+'</td>';
                        trow += '<td>'+s2+'</td><td>'+s3+'</td><td>'+s4+'</td><td>'+s5+'</td><td>'+row.total_clicks+'</td>';
                        trow += '<td>'+row.prepop_count+'</td><td>'+row.no_prepop_count+'</td><td>'+row.prepop_with_errors_count+'</td>';
                        trow += '<td>'+prepop_percentage.toFixed(2)+'%</td><td>'+not_prepop_percentage.toFixed(2)+'%</td><td>'+error_percentage.toFixed(2)+'%</td></tr>';
                    
                    body.append(trow);
                });

                table.DataTable({
                    "order": [[ 1, "asc" ]],
                    // language: {
                    //     searchPlaceholder: "Affiliate / Revenue Tracker"
                    // },
                    lengthMenu: [[25, 50, 100, -1],[25, 50, 100, 'All']]
                }).draw();
            }
        });
    }

    $('#prepop-table').dataTable();
    prePopStatisticsReport();

    $('#generate_report').click(function(e){

        e.preventDefault();
        prePopStatisticsReport()

    });

    $('#predefined_date').change(function()
    {
        //clear the date range
        $('#date_from').val('');
        $('#date_to').val('');
    });

    var lastValue1 = '';
    $('.date-picker').on('propertychange change paste input',function()
    {
        if ($(this).val() != lastValue1) {
            lastValue1 = $(this).val();
            $('#predefined_date').val('');
        }
    });

    $('#clear').click(function()
    {
        $('#date_from').val('');
        $('#date_to').val('');
        $('#affiliate_id').val('').trigger('change');
        $('#predefined_date').val('yesterday').trigger('change');
        $('#group_by').val('created_at').trigger('change');

        //clear the results as well
        // prepopDataTable.ajax.reload();
    });

    $('#group_by').change(function()
    {
        if($(this).val() != 'created_at') {
            $('.sibs').prop('checked', true).attr('disabled', true);
        }else {
            $('.sibs').removeAttr('disabled');
        }
    });
});