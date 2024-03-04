$(document).ready(function(){

    var baseURL = $('#baseUrl');

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('#affiliate_id').select2({
        placeholder: 'Select the id or name of the affiliate.',
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

    $('#remove_affiliate_id_selections').click(function(){
        // Clear the ID selections
        $('#affiliate_id').val(null).trigger('change');
    });

    // var the_url = baseURL.html() + '/admin/getClicksVsRegsStats';
    // var clicksVsRegistrationTable = $('#clicks-vs-registration-table').DataTable({
    //     'processing': true,
    //     'serverSide': true,
    //     language: {
    //         searchPlaceholder: "Affiliate / Revenue Tracker"
    //     },
    //     "order": [],
    //     'ajax':{
    //         url: the_url, // json datasource
    //         type: 'post',
    //         // 'data': $('#search-leads').serialize(),
    //         'data': function(d)
    //         {
    //             d.date_from = $('#date_from').val();
    //             d.date_to = $('#date_to').val();
    //             d.affiliate_id = $('#affiliate_id').val();
    //             d.group_by = $('#group_by').val();
    //             d.date_range = $('#date_range').val();
    //             d.s1 = $('#s1').val();
    //             d.s2 = $('#s2').val();
    //             d.s3 = $('#s3').val();
    //             d.s4 = $('#s4').val();
    //             d.s5 = $('#s5').val();
    //         },
    //         "dataSrc": function (json) {
    //             var downloadReportButton = $('#download-clicks-vs-registration-report');

    //             if(json.data.length > 0)
    //             {
    //                 downloadReportButton.removeClass('disabled');
    //                 /*
    //                 var affiliatesArray = encodeURI(JSON.stringify(json.affiliate_id));
    //                 console.log(json.affiliate_id);
    //                 console.log(affiliatesArray);
    //                 */
    //                 var arrayURLStr = '';
    //                 for (var i=0; i<json.affiliate_id.length; i++){
    //                     arrayURLStr += '&affiliate_id['+i+']='+json.affiliate_id[i];
    //                 }
    //                 // console.log(json.order_column + ' ' + json.order_dir);

    //                 var orderColumnStr = '';
    //                 if(json.order_column !== undefined)
    //                 {
    //                     orderColumnStr = '&order_column='+json.order_column;
    //                 }

    //                 var s1 = $('#s1').val(),
    //                     s2 = $('#s2').val(),
    //                     s3 = $('#s3').val(),
    //                     s4 = $('#s4').val(),
    //                     s5 = $('#s5').val();

    //                 // var downloadURL = baseURL.html()+'/admin/downLoadClicksVsRegistrationReport?affiliate_id='+affiliatesArray+'&date_from='+json.date_from+'&date_to='+json.date_to+'&group_by='+json.group_by;
    //                 var downloadURL = baseURL.html()+'/admin/downLoadClicksVsRegistrationReport?'+arrayURLStr+'&date_from='+json.date_from+'&date_to='+json.date_to+'&group_by='+json.group_by+orderColumnStr+'&order_dir='+json.order_dir+'&is_download=true&date_range='+json.date_range;
    //                     downloadURL += '&s1=' + s1 + '&s2=' + s2 + '&s3=' + s3 + '&s4=' + s4 + '&s5=' + s5;
    //                 console.log(downloadURL);
    //                 downloadReportButton.attr('href', downloadURL);
    //             }
    //             else
    //             {
    //                 downloadReportButton.addClass('disabled');
    //             }

    //             $(clicksVsRegistrationTable.column(9).footer()).html(json.totalRegistrationCount);
    //             $(clicksVsRegistrationTable.column(10).footer()).html(json.totalClicks);
    //             $(clicksVsRegistrationTable.column(11).footer()).html(json.totalPercentage);

    //             return json.data;
    //         },
    //         error: function(data){  // error handling
    //             console.log(data);
    //         }
    //     },
    //     'columns':[
    //         {'data':'date'},
    //         {'data':'affiliate_name'},
    //         {'data':'affiliate_id'},
    //         {'data':'revenue_tracker_id'},
    //         {'data':'s1'},
    //         {'data':'s2'},
    //         {'data':'s3'},
    //         {'data':'s4'},
    //         {'data':'s5'},
    //         {'data':'registration_count'},
    //         {'data':'clicks'},
    //         {'data':'percentage'}
    //     ],
    //     lengthMenu: [[25, 50, 100, -1],[25, 50, 100, 'All']]
    // });

    function clicksRegsReport() {
        the_url = baseURL.html() + '/admin/getClicksVsRegsStats';

        var table = $('#clicks-vs-registration-table'),
            body = $('#clicks-vs-registration-table tbody'),
            footer = $('#clicks-vs-registration-table tfoot tr');

        var downloadReportButton = $('#download-clicks-vs-registration-report');
        var groupBy = $('#group_by').val();

        footer.find(':nth-child(10)').html('')
        footer.find(':nth-child(11)').html('')
        footer.find(':nth-child(12)').html('')
        table.DataTable().destroy();
        body.empty();
        $.ajax({
            type: 'POST',
            url: the_url,
            data: {
                date_from : $('#date_from').val(),
                date_to : $('#date_to').val(),
                affiliate_id : $('#affiliate_id').val(),
                group_by : groupBy,
                date_range : $('#date_range').val(),
                s1 : $('#s1').val(),
                s2 : $('#s2').val(),
                s3 : $('#s3').val(),
                s4 : $('#s4').val(),
                s5 : $('#s5').val(),
                sib_s1 : $('#sib_s1').prop('checked'),
                sib_s2 : $('#sib_s2').prop('checked'),
                sib_s3 : $('#sib_s3').prop('checked'),
                sib_s4 : $('#sib_s4').prop('checked')
            },
            success: function(data){
                console.log(data);
                console.log(data.records.length)

                var total_regs = 0, total_clicks = 0;
                var affiliates = data.affiliates;

                var downloadReportButton = $('#download-clicks-vs-registration-report');
                if(data.records.length > 0)
                {
                    var arrayURLStr = '';
                    for (var i=0; i<data.affiliate_id != null?data.affiliate_id.length:0; i++){
                        arrayURLStr += '&affiliate_id['+i+']='+data.affiliate_id[i];
                    }

                    var s1 = $('#s1').val(),
                        s2 = $('#s2').val(),
                        s3 = $('#s3').val(),
                        s4 = $('#s4').val(),
                        s5 = $('#s5').val();

                    // var downloadURL = baseURL.html()+'/admin/downLoadClicksVsRegistrationReport?affiliate_id='+affiliatesArray+'&date_from='+json.date_from+'&date_to='+json.date_to+'&group_by='+json.group_by;
                    var downloadURL = baseURL.html()+'/admin/downLoadClicksVsRegistrationReport?'+arrayURLStr+'&date_from='+$('#date_from').val()+'&date_to='+$('#date_to').val()+'&group_by='+groupBy+'&is_download=true&date_range='+$('#date_range').val();
                        downloadURL += '&s1=' + s1 + '&s2=' + s2 + '&s3=' + s3 + '&s4=' + s4 + '&s5=' + s5 + '&sib_s1=' + $('#sib_s1').prop('checked') + '&sib_s2=' + $('#sib_s2').prop('checked') + '&sib_s3=' + $('#sib_s3').prop('checked') + '&sib_s4=' + $('#sib_s4').prop('checked');
                    downloadReportButton.attr('href', downloadURL);
                    downloadReportButton.removeClass('disabled');
                }else downloadReportButton.addClass('disabled');

                $.each(data.records, function(x, row){

                    var affiliate = affiliates[row.affiliate_id],
                        regs = Number(row.registration_count),
                        clicks = Number(row.clicks),
                        percentage = (regs > 0 && clicks > 0) ? Number((regs / clicks) * 100.0).toFixed(2) : 0

                        var createdAt = '', s1 = '', s2 = '', s3 = '', s4 = '', s5 = '';
                        if(groupBy == 'created_at'){
                            createdAt = row.created_at;
                            s1 = $('#sib_s1').prop('checked') ? row.s1 : '';
                            s2 = $('#sib_s2').prop('checked') ? row.s2 : '';
                            s3 = $('#sib_s3').prop('checked') ? row.s3 : '';
                            s4 = $('#sib_s4').prop('checked') ? row.s4 : '';
                            s5 = row.s5;
                        }else if(groupBy == 'custom') {
                            createdAt = row.created_at;
                        }else if(groupBy == 'per_sub_id') {
                            s1 = row.s1;s2 = row.s2;s3 = row.s3;s4 = row.s4;s5 = row.s5;
                        }

                    var row = '<tr><td>'+createdAt+'</td><td>'+affiliate+'</td><td>'+row.affiliate_id+'</td><td>'+row.revenue_tracker_id+'</td><td>'+s1+'</td><td>'+s2+'</td><td>'+s3+'</td><td>'+s4+'</td><td>'+s5+'</td><td>'+regs+'</td><td>'+clicks+'</td><td>'+percentage+'%</td></tr>';
                    body.append(row);

                    total_regs += Number(regs);
                    total_clicks += Number(clicks);
                });

                total_percentage = (total_regs > 0 && total_clicks > 0) ? Number((total_regs / total_clicks) * 100.0).toFixed(2) : 0;

                footer.find(':nth-child(10)').html(total_regs)
                footer.find(':nth-child(11)').html(total_clicks)
                footer.find(':nth-child(12)').html(total_percentage + '%')

                table.DataTable({
                    "order": [[ 1, "asc" ]],
                    language: {
                        searchPlaceholder: "Affiliate / Revenue Tracker"
                    },
                    lengthMenu: [[25, 50, 100, -1],[25, 50, 100, 'All']]
                }).draw();
            }
        });
    }

    $('#clicks-vs-registration-table').dataTable();
    clicksRegsReport()

    $(document).on('submit', function(e){

        e.preventDefault();

        // clicksVsRegistrationTable.order([[ 0, "asc" ]]);
        // clicksVsRegistrationTable.ajax.reload();
        clicksRegsReport()

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

    $('#addAffiliateID').click(function(){
        // Get the selected affiliate and add it on the list.
        var affiliateID = $('#affiliate_id').val();
        console.log(affiliateID);
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
