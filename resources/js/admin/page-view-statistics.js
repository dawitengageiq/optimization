$(document).ready(function(){

    var baseURL = $('#baseUrl');

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('#affiliate_id').select2({
        //tags: true,
        placeholder: 'Select the id or name of the affiliate.',
        minimumInputLength: 1,
        maximumSelectionLength: 0,
        theme: 'bootstrap',
        language: {
            inputTooShort: function(args) {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            }
        },
        ajax: {
            url: baseURL.html()+'/search/select/activeAffiliatesIDName',
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
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    var the_url = baseURL.html() + '/admin/getPageViewStats';
    var pvsAvg;
    var pageViewStatisticsTable = $('#page-view-statistics-table').DataTable({
        'processing': true,
        'serverSide': true,
        language: {
            searchPlaceholder: "Affiliate / Revenue Tracker"
        },
        "order": [],
        'ajax':{
            url:the_url, // json datasource
            type: 'post',
            // 'data': $('#search-leads').serialize(),
            'data': function(d)
            {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.affiliate_id = $('#affiliate_id').val();
                d.group_by = $('#group_by').val();
                d.date_range = $('#date_range').val();
                d.s1 = $('#s1').val();
                d.s2 = $('#s2').val();
                d.s3 = $('#s3').val();
                d.s4 = $('#s4').val();
                d.s5 = $('#s5').val();
                d.sib_s1 = $('#sib_s1').prop('checked');
                d.sib_s2 = $('#sib_s2').prop('checked');
                d.sib_s3 = $('#sib_s3').prop('checked');
                d.sib_s4 = $('#sib_s4').prop('checked');
            },
            "dataSrc": function (json) {

                var downloadReportButton = $('#download-page-view-statistics-report');

                if(json.data.length > 0)
                {
                    downloadReportButton.removeClass('disabled');

                    var arrayURLStr = '';
                    if(json.affiliate_id != null)
                    {for (var i=0; i<json.affiliate_id.length; i++){
                        arrayURLStr += '&affiliate_id['+i+']='+json.affiliate_id[i];
                    }}

                    var orderColumnStr = '';
                    if(json.order_column !== undefined)
                    {
                        orderColumnStr = '&order_column='+json.order_column;
                    }

                    var s1 = $('#s1').val(),
                        s2 = $('#s2').val(),
                        s3 = $('#s3').val(),
                        s4 = $('#s4').val(),
                        s5 = $('#s5').val();

                    // var downloadURL = baseURL.html()+'/admin/downLoadPageViewStatisticsReport?affiliate_id='+json.affiliate_id+'&date_from='+json.date_from+'&date_to='+json.date_to+'&group_by='+json.group_by;
                    var downloadURL = baseURL.html()+'/admin/downLoadPageViewStatisticsReport?'+arrayURLStr+'&date_from='+json.date_from+'&date_to='+json.date_to+'&group_by='+json.group_by+orderColumnStr+'&order_dir='+json.order_dir+'&is_download=true&date_range='+json.date_range;
                        downloadURL += '&s1=' + s1 + '&s2=' + s2 + '&s3=' + s3 + '&s4=' + s4 + '&s5=' + s5 + '&sib_s1=' + $('#sib_s1').prop('checked') + '&sib_s2=' + $('#sib_s2').prop('checked') + '&sib_s3=' + $('#sib_s3').prop('checked') + '&sib_s4=' + $('#sib_s4').prop('checked');
                    console.log(downloadURL);
                    downloadReportButton.attr('href', downloadURL);
                }
                else
                {
                    downloadReportButton.addClass('disabled');
                }

                $(pageViewStatisticsTable.column(9).footer()).html(json.total_lp);
                $(pageViewStatisticsTable.column(10).footer()).html(json.total_rp);
                $(pageViewStatisticsTable.column(11).footer()).html(json.total_to1);
                $(pageViewStatisticsTable.column(12).footer()).html(json.total_to2);
                $(pageViewStatisticsTable.column(13).footer()).html(json.total_mo1);
                $(pageViewStatisticsTable.column(14).footer()).html(json.total_mo2);
                $(pageViewStatisticsTable.column(15).footer()).html(json.total_mo3);
                $(pageViewStatisticsTable.column(16).footer()).html(json.total_mo4);
                $(pageViewStatisticsTable.column(17).footer()).html(json.total_lfc1);
                $(pageViewStatisticsTable.column(18).footer()).html(json.total_lfc2);
                $(pageViewStatisticsTable.column(19).footer()).html(json.total_tbr1);
                $(pageViewStatisticsTable.column(20).footer()).html(json.total_pd);
                $(pageViewStatisticsTable.column(21).footer()).html(json.total_tbr2);
                $(pageViewStatisticsTable.column(22).footer()).html(json.total_iff);
                $(pageViewStatisticsTable.column(23).footer()).html(json.total_rex);
                $(pageViewStatisticsTable.column(24).footer()).html(json.total_ads);
                $(pageViewStatisticsTable.column(25).footer()).html(json.total_cpawall);
                $(pageViewStatisticsTable.column(26).footer()).html(json.total_exitpage);
                $(pageViewStatisticsTable.column(27).footer()).html(json.thirty_day_average);


                pvsAvg = json.thirty_day_average;
                return json.data;
            },
            error: function(data){  // error handling
                console.log(data);
            }
        },
        "createdRow": function( row, data, dataIndex ) {

            var average = Number(pvsAvg);
                ceiling = average * .95;

            if(data.percentage >= ceiling && data.percentage < average) {
                $(row).find('td:last-child').addClass( 'warning' );
            }
            else if(data.percentage < ceiling && data.percentage < average){
                $(row).find('td:last-child').addClass( 'danger' );
            }

            // console.log('Created Row');
            // console.log(row);
            // console.log(data);
            // console.log(dataIndex);
            // console.log(pvsAvg)
        },
        'columns':[
            {'data':'date'},
            {'data':'affiliate_name'},
            {'data':'affiliate_id'},
            {'data':'revenue_tracker_id'},
            {'data':'s1'},
            {'data':'s2'},
            {'data':'s3'},
            {'data':'s4'},
            {'data':'s5'},
            {'data':'lp'},
            {'data':'rp'},
            {'data':'to1'},
            {'data':'to2'},
            {'data':'mo1'},
            {'data':'mo2'},
            {'data':'mo3'},
            {'data':'mo4'},
            {'data':'lfc1'},
            {'data':'lfc2'},
            {'data':'tbr1'},
            {'data':'pd'},
            {'data':'tbr2'},
            {'data':'iff'},
            {'data':'rex'},
            {'data':'ads'},
            {'data':'cpawall'},
            {'data':'exitpage'},
            {'data':'percentage'}
        ],
        lengthMenu: [[25, 50, 100, -1],[25, 50, 100, 'All']]
    });

    $('#remove_affiliate_id_selections').click(function(){
        // Clear the ID selections
        $('#affiliate_id').val(null).trigger('change');
    });

    $(document).on('submit', function(e){

        e.preventDefault();

        // pageViewStatisticsTable.order([[ 0, "asc" ]]);
        pageViewStatisticsTable.ajax.reload();

    });

    $('#clear').click(function(){

        $('.date-field').val('');
        $('#affiliate_id').val('').trigger('change');
    });

    $('#date_range').change(function()
    {
        if($(this).val() != '')
        {
            $('#date_from').val('');
            $('#date_to').val('');
        }
    });

    $('.date-field').change(function()
    {
        if($(this).val() != '')
        {
            $('#date_range').val('');
        }
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
