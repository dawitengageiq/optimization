$(document).ready(function()
{
    $('.date-range-picker').prop("readonly", true);

    $('[data-toggle="tooltip"]').tooltip();

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true,
        orientation: "top auto",
        endDate: '-0d'
    });

    var baseURL = $('#baseUrl');

    var rowInitComplete = function(){
        console.log('complete');
        $('#host_posted_deals tr').each(function() {
            var tableRow = $(this);
            tableRow.addClass('row-button');
        });
    };

    $('#external_path_revenue').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
        "columnDefs": [
            { "searchable": false, "targets": [1] }
        ],
        language: {
            searchPlaceholder: "Date"
        },
    });
    externalPathStatistics();
    function externalPathStatistics() {
        // console.log('host and post affiliate report')
        // var the_url = baseURL + '/getHandPAffiliateReports';

        var table = $('#external_path_revenue'),
            body = $('#external_path_revenue tbody'),
            footer = $('#external_path_revenue tfoot tr');

        // footer.find(':nth-child(2)').html('0')
        footer.find(':nth-child(2)').html('$0.00')
        table.DataTable().destroy();
        body.empty();

        $.ajax({
            type: 'GET',
            url: baseURL.html() + '/affiliate/externalPathStatistics',
            data: {
                period : $('#hosted_filter').val(),
                start_date : $('#hosted_start_date').val(),
                end_date : $('#hosted_end_date').val(),
            },
            success: function(data){
                // console.log(data);
                var total_leads = 0, total_revenue = 0.0, total_share = 0.0, publisher_share = data.publisher_revenue_share, 
                    share_percentage = publisher_share / 100; 

                $('#publisherRevenueSharePerc').html(publisher_share)

                $.each(data.records, function(x, row){
                    var received = row.received != null ? (row.received).toFixed(2) : 0.0,
                        leadCount = row.count != null ? row.count : 0;

                    var share = (received * share_percentage).toFixed(2);
                    // var row = '<tr class="row-button"><td>'+row.created_at+'</td><td>'+leadCount+'</td><td>$ '+payout+'</td></tr>';
                    var row = '<tr class="row-button"><td>'+row.created_at+'</td><td>$ '+received+'</td><td>$ '+share+'</td></tr>';
                    body.append(row);

                    total_leads += Number(leadCount);
                    total_revenue += Number(received);
                    total_share += Number(share);
                });

                // footer.find(':nth-child(2)').html(total_leads)
                // footer.find(':nth-child(2)').html('$' + (total_revenue).toFixed(2))
                // footer.find(':nth-child(3)').html('$' + (total_share).toFixed(2))
                footer.find(':nth-child(2)').html('$' + data.total_revenue)
                footer.find(':nth-child(3)').html('$' + data.total_share)

                table.DataTable({
                    responsive: true,
                    lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                    "columnDefs": [
                        { "searchable": false, "targets": [1] }
                    ],
                    language: {
                        searchPlaceholder: "Date"
                    },
                }).draw();
            }
        });
    }

    $('#host_posted_deals').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
        "columnDefs": [
            { "searchable": false, "targets": [1,2] }
        ],
        language: {
            searchPlaceholder: "Campaign"
        },
    });
    $('#website_statistics').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
        "columnDefs": [
            { "searchable": false, "targets": [1,2] }
        ],
        language: {
            searchPlaceholder: "Website"
        },
    });
    // $('#handp-subid-table').DataTable();
    
    hostPostAffiliateReport();
    function hostPostAffiliateReport() {
        // console.log('host and post affiliate report')
        // var the_url = baseURL + '/getHandPAffiliateReports';

        var table = $('#host_posted_deals'),
            body = $('#host_posted_deals tbody')
            footer = $('#host_posted_deals tfoot tr');

        footer.find(':nth-child(2)').html('0')
        footer.find(':nth-child(3)').html('$0.00')
        table.DataTable().destroy();
        body.empty();

        $.ajax({
            type: 'GET',
            url: baseURL.html() + '/affiliate/affiliateHostedStatistics',
            data: {
                period : $('#hosted_filter').val(),
                start_date : $('#hosted_start_date').val(),
                end_date : $('#hosted_end_date').val(),
            },
            success: function(data){
                //console.log(data);
                var campaigns = data.campaigns;
                var total_leads = 0, total_revenue = 0.0;
                $.each(data.records, function(x, row){
                    var payout = row.payout != null ? (row.payout).toFixed(2) : 0.0,
                        leadCount = row.leads != null ? row.leads : 0,
                        camp = '<span id="campaignStats-'+row.campaign_id+'-campaign_id" class="stats-col-campaign" data-campaign_id="'+row.campaign_id+'">'+campaigns[row.campaign_id]+'</span>';
                    var row = '<tr class="row-button"><td>'+camp+'</td><td>'+leadCount+'</td><td>$ '+payout+'</td></tr>';
                    body.append(row);

                    total_leads += Number(leadCount);
                    total_revenue += Number(payout);
                });

                footer.find(':nth-child(2)').html(total_leads)
                footer.find(':nth-child(3)').html('$' + (total_revenue).toFixed(2))
                table.DataTable({
                    responsive: true,
                    lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                    "columnDefs": [
                        { "searchable": false, "targets": [1,2] }
                    ],
                    language: {
                        searchPlaceholder: "Campaign"
                    },
                }).draw();
            }
        });
    }

    function campaignWebsiteBreakdown() {
        var table = $('#website_statistics'),
            body = $('#website_statistics tbody'),
            footer = $('#website_statistics tfoot tr');


        footer.find(':nth-child(2)').html('0')
        footer.find(':nth-child(3)').html('$0.00')

        table.DataTable().destroy();
        body.empty();

        $.ajax({
            type: 'GET',
            url: baseURL.html() + '/affiliate/affiliateWebsiteStatistics',
            data: {
                period : $('#modal_filter').val(),
                start_date : $('#modal_start_date').val(),
                end_date : $('#modal_end_date').val(),
                campaign_id : $('#campaign_id').val()
            },
            success: function(data){
                // console.log(data);
                var total_leads = 0, total_revenue = 0.0;
                $.each(data.records, function(x, row){
                    var payout = row.payout != null ? (row.payout).toFixed(2) : 0.0,
                        leadCount = row.leads != null ? row.leads : 0;
                    var row = '<tr class="row-button"><td>'+row.website+'</td><td>'+leadCount+'</td><td>$ '+payout+'</td></tr>';
                    body.append(row);
                    total_leads += Number(leadCount);
                    total_revenue += Number(payout);
                });
                footer.find(':nth-child(2)').html(total_leads)
                footer.find(':nth-child(3)').html('$' + (total_revenue).toFixed(2))
                table.DataTable({
                    responsive: true,
                    lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                    "columnDefs": [
                        { "searchable": false, "targets": [1,2] }
                    ],
                    language: {
                        searchPlaceholder: "Website"
                    },
                }).draw();
            }
        });
    }
    
    // var regPathRevenue = $('#reg_path_revenue').DataTable({
    //     'processing': true,
    //     'serverSide': true,
    //     'order': [], //disable the initial ordering
    //     language: {
    //         searchPlaceholder: "Website"
    //     },
    //     'drawCallback': function(settings) {
    //         $('#reg_path_revenue tr').each(function() {
    //             var tableRow = $(this);
    //             tableRow.addClass('row-button');
    //         });
    //     },
    //     'ajax':{
    //         url: baseURL.html() + '/affiliate/affiliateWebsiteViewsStatistics',
    //         type: 'GET',  // method  , by default get
    //         'data': function(d)
    //         {
    //             d.website_view_start_date = $('#hosted_start_date').val();
    //             d.website_view_end_date = $('#hosted_end_date').val();
    //             d.website_view_filter = $('#hosted_filter').val();
    //         },
    //         error: function()
    //         {
    //             // error handling
    //         }
    //     },
    //     'initComplete': rowInitComplete,
    //     lengthMenu: [[20,50,100,-1],[20,50,100,'ALL']]
    // });;

    $('#reg_path_revenue').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
        "columnDefs": [
            { "searchable": false, "targets": [1] }
        ],
        language: {
            searchPlaceholder: "Website"
        },
        "sDom": 'l<"reg_path_revenue-addToolbar">frtip'
    });
    $('#reg_path_email_checkbox').prop('checked', $('#reg_path_rev_email_value').val() == 1);

    affiliateWebsiteStatistics();
    function affiliateWebsiteStatistics() {
        // console.log('host and post affiliate report')
        // var the_url = baseURL + '/getHandPAffiliateReports';

        var table = $('#reg_path_revenue'),
            body = $('#reg_path_revenue tbody'),
            footer = $('#reg_path_revenue tfoot tr');

        // footer.find(':nth-child(1)').html('0')
        footer.find(':nth-child(2)').html('$0.00')
        table.DataTable().destroy();
        body.empty();

        $.ajax({
            type: 'GET',
            url: baseURL.html() + '/affiliate/affiliateWebsiteViewsStatistics',
            data: {
                period : $('#hosted_filter').val(),
                start_date : $('#hosted_start_date').val(),
                end_date : $('#hosted_end_date').val(),
            },
            success: function(data){
                console.log(data);
                var total_passovers = 0, total_revenue = 0,
                    websites = data.websites;
                    // payouts = data.payouts;

                $.each(data.records, function(x, row){
                    var revenue = (row.payout).toFixed(2),
                        passover = row.count;
                        // payout = Number(payouts[row.website_id]) * 1000;



                    var row = '<tr class="row-button"><td data-id="'+row.website_id+'">'+websites[row.website_id]+'</td><td> '+passover+'</td><td>$ '+revenue+'</td></tr>';
                    body.append(row);

                    total_passovers += Number(passover);
                    total_revenue += Number(revenue);
                });

                // footer.find(':nth-child(2)').html(total_leads)
                footer.find(':nth-child(2)').html((total_passovers))
                footer.find(':nth-child(3)').html('$' + (total_revenue).toFixed(2))

                table.DataTable({
                    responsive: true,
                    lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                    "columnDefs": [
                        { "searchable": false, "targets": [1] }
                    ],
                    language: {
                        searchPlaceholder: "Website"
                    },
                    "sDom": 'l<"reg_path_revenue-addToolbar">frtip'
                }).draw();
                $("div.reg_path_revenue-addToolbar").html('<label class="checkbox-inline"><input type="checkbox" name="reg_path_email" id="reg_path_email_checkbox" value="1" class="this_field"> Automatically send this report to email</label>');
                $('#reg_path_email_checkbox').prop('checked', $('#reg_path_rev_email_value').val() == 1);
            }
        });
    }

    $(document).on('change','#reg_path_email_checkbox', function() {
        var value = $(this).prop('checked');
        $.ajax({
        type: 'GET',
        url: baseURL.html() + '/affiliate/user_meta/update',
        data: {
            key : 'reg_path_revenue_email_report',
            value : $('#reg_path_email_checkbox').prop('checked') ? 1 : 0
        },
        success: function(data){
            console.log(data);
        }
    });

    });

    $('#predefined_form').submit(function(e)
    {
        e.preventDefault();
        //Return to default
        $('#hosted_end_date').removeClass('error_field');

        //Check if dates are valid
        if(Date.parse($('#hosted_start_date').val()) > Date.parse($('#hosted_end_date').val())) {
            $('#hosted_end_date').addClass('error_field');
        }else {
            //remove the currently selected ordering
            // hostPostedDealsDataTable.order([]);
            // hostPostedDealsDataTable.ajax.reload(rowInitComplete);

            hostPostAffiliateReport();

            // regPathRevenue.order([]);
            // regPathRevenue.ajax.reload();

            affiliateWebsiteStatistics();

            externalPathStatistics();

        }
    });

    $('#website_sub_revenue').submit(function(e)
    {
        e.preventDefault();
        //remove the currently selected ordering
        // websiteStatisticsDataTable.order([]);
        // websiteStatisticsDataTable.ajax.reload();
        campaignWebsiteBreakdown();
    });

    //url for the website table server side
    // var websiteStatisticsDataTable = $('#website_statistics').DataTable({
    //     'processing': true,
    //     'serverSide': true,
    //     language: {
    //         searchPlaceholder: "Website"
    //     },
    //     'order': [], //disable the initial ordering
    //     'ajax':{
    //          url: baseURL.html() + '/affiliate/affiliateWebsiteStatistics',
    //          type: 'GET',  // method  , by default get
    //          'data': function(d)
    //          {
    //              d.modal_start_date = $('#modal_start_date').val();
    //              d.modal_end_date = $('#modal_end_date').val();
    //              d.modal_filter = $('#modal_filter').val();
    //              d.campaign_id = $('#campaign_id').val();
    //          },
    //          error: function(){  //error handling
    //          }
    //     },
    //     lengthMenu: [[20,50,100,-1],[20,50,100,'ALL']]
    // });

    $(document).on('click','.row-button', function() {
        //get the form input from the parent
        var hostedStartDate = $('#hosted_start_date').val();
        var hostedEndDate = $('#hosted_end_date').val();
        var hostedPredefine = $('#hosted_filter').val();

        //clear the data range
        $('#modal_start_date').val('');
        $('#modal_end_date').val('');

        //assign form values
        $('#modal_start_date').val(hostedStartDate);
        $('#modal_end_date').val(hostedEndDate);
        $('#modal_filter').val(hostedPredefine);

        var campaignColumn = $(this).find('td:first');
        var campaign = $(campaignColumn).find($('span'));
        var campaignID = campaign.data('campaign_id');

        if(campaignID==undefined)
        {
            return;
        }

        //assign the proper campaign ID
        $('#campaign_id').val(campaignID);
        // console.log(campaignID);
        // console.log(hostedPredefine);

        //get the campaign name ang copy it to the modal title
        var campaignName = $('#campaignStats-'+campaignID+'-campaign_id').text();
        $('#campaign_name').text(campaignName);

        campaignWebsiteBreakdown();

        // //clear the search filter
        // websiteStatisticsDataTable.search('');
        // //reload the data set
        // websiteStatisticsDataTable.ajax.reload();

        $('#website_sub_revenue').modal('show');

    });

    $('#hosted_filter').change(function()
    {
        //clear the date range
        $('#hosted_start_date').val('');
        $('#hosted_end_date').val('');
    });

    $('#modal_filter').change(function()
    {
        //clear the date range
        $('#modal_start_date').val('');
        $('#modal_end_date').val('');
    });

    var lastValue1 = '';
    $('.hosted-date-picker').on('propertychange change paste input',function()
    {
        if ($(this).val() != lastValue1) {
            lastValue1 = $(this).val();
            $('#hosted_filter').val('');
        }
    });

    var lastValue2 = '';
    $('.modal-date-picker').on('propertychange change paste input',function()
    {
        if ($(this).val() != lastValue2) {
            lastValue2 = $(this).val();
            $('#modal_filter').val('');
        }
    });

    $('.hosted-date-picker').change(function() {
        //Return to default
        $('#hosted_end_date').removeClass('error_field');

        //Check if dates are valid
        if(Date.parse($('#hosted_start_date').val()) > Date.parse($('#hosted_end_date').val())) {
            $('#hosted_end_date').addClass('error_field');
        }
    });

    $(document).on('click','#reg_path_revenue tbody tr td:first-child', function(e){

        if($(this).parent('tr').children().length > 1)
        {
            var modal = $('#regPathRevenueModal'),
                website_id = $(this).data('id');
            console.log(website_id)
            var table = $('#reg_path_breakdown-table'),
                body = $('#reg_path_breakdown-table tbody'),
                footer = $('#reg_path_breakdown-table tfoot tr');
            footer.find(':nth-child(2)').html('')
            footer.find(':nth-child(3)').html('')
            table.DataTable().destroy();
            body.empty();
            $.ajax({
                type: 'GET',
                url: baseURL.html() + '/affiliate/getRegRevenueBreakdown',
                data: {
                    period : $('#hosted_filter').val(),
                    start_date : $('#hosted_start_date').val(),
                    end_date : $('#hosted_end_date').val(),
                    website_id: website_id
                },
                success: function(data){
                    console.log(data);
                    var total_passovers = 0, total_revenue = 0,
                        payouts = data.payouts;

                    $.each(data.records, function(x, row){
                        var revenue = (row.payout).toFixed(2),
                            passover = row.count;
                            payout = Number(payouts[row.website_id]) * 1000;
                        var row = '<tr class="row-button"><td>'+row.date+'</td><td> '+passover+'</td><td> '+payout+' CPM</td><td>$ '+revenue+'</td></tr>';
                        body.append(row);

                        total_passovers += Number(passover);
                        total_revenue += Number(revenue);
                    });

                    // footer.find(':nth-child(2)').html(total_leads)
                    footer.find(':nth-child(2)').html((total_passovers))
                    footer.find(':nth-child(4)').html('$' + (total_revenue).toFixed(2))

                    table.DataTable({
                        responsive: true,
                        searching: false,
                        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]]
                    }).draw();
                    
                }
            });

            if(!modal.hasClass('in'))
            {
                modal.modal('show');
            }
        }
    });

});
