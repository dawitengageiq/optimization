$(document).ready(function()
{
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    var alertContainer = $('.alert');
    alertContainer.on('click','.close-internal-report-error',function(){
        $(this).closest('.alert').fadeOut();
        $(this).closest('.internal-error-wrapper').hide();
    });

    alertContainer.on('click','.close-handp-report-error',function(){
        $(this).closest('.alert').fadeOut();
        $(this).closest('.handp-error-wrapper').hide();
    });

    alertContainer.on('click','.close-internal-iframe-report-error',function(){
        $(this).closest('.alert').fadeOut();
        $(this).closest('.internal-iframe-error-wrapper').hide();
    });

    //draggable drilldown modal
    $('.draggable-drilldown-modal').draggable({
        handle: ".modal-header"
    });

    var dateRangePicker = $('.date-range-picker');
    dateRangePicker.prop("readonly", true);

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true,
        orientation: "top auto",
        // endDate: '-0d',
        // startDate: '-30d'
    });

    var lastValue1 = '';
    dateRangePicker.on('propertychange change paste input',function()
    {
        if ($(this).val() != lastValue1) {
            lastValue1 = $(this).val();
            $('#snapshot_period').val('none');
        }
    });

    dateRangePicker.change(function() {

        var endDate = $('#end_date');
        //Return to default
        endDate.removeClass('error_field');

        //Check if dates are valid
        if(Date.parse($('#start_date').val()) > Date.parse(endDate.val())) {
            $('#end_date').addClass('error_field');
        }
    });

    var snapShotPeriod = $('#snapshot_period');
    snapShotPeriod.change(function()
    {
        //clear the date range
        $('#start_date').val('');
        $('#end_date').val('');
    });

    /*
     $('.modal-content').resizable({
     //alsoResize: ".modal-dialog",
     minHeight: 300,
     minWidth: 300
     });
     */

    //hide first the download buttons
    $('.download-report-btn').hide();

    //change the link of download csv when snapshot is changed
    var baseURL = $('#baseUrl').html();
    var reportsURL = $('#reportsUrl').html();
    var snapShotSelect = snapShotPeriod;
    var snapshotPeriod = snapShotSelect.val();

    var dateRangePayload = '?start_date='+$('#start_date').val()+'&end_date='+$('#end_date').val();

    $('a#downloadXLSInternal').attr('href', baseURL+'/downloadAffiliateReportXLS/1/'+snapshotPeriod+dateRangePayload);
    $('a#downloadXLSHandP').attr('href', baseURL+'/downloadAffiliateReportXLS/2/'+snapshotPeriod+dateRangePayload);

    snapShotSelect.change(function()
    {
        var snapshotPeriod = $(this).val();
        console.log(snapshotPeriod);

        var dateRangePayload = '?start_date='+$('#start_date').val()+'&end_date='+$('#end_date').val();

        $('a#downloadXLSInternal').attr('href', baseURL+'/downloadAffiliateReportXLS/1/'+snapshotPeriod+dateRangePayload);
        $('a#downloadXLSHandP').attr('href', baseURL+'/downloadAffiliateReportXLS/2/'+snapshotPeriod+dateRangePayload);
    });

    var the_affiliate_url = baseURL + '/getAffiliateReports';
    // var the_affiliate_url = reportsURL + '/affiliate_reports/datatable';
    var the_iframe_affiliate_url = baseURL + '/getIframeAffiliateReports';
    // var handpAffiliateReportsURL = baseURL + '/getHandPAffiliateReports';
    // var handpAffiliateSubIDReportsURL = baseURL + '/getHandPAffiliateSubIDReports';
    var the_iframe_website_url = baseURL + '/getIframeWebsiteReports';
    var the_iframe_revenueTracker_url = baseURL + '/getIframeRevenueTrackerReports';

    function internalAffiliateReport() {
        console.log('internal affiliate report')
        the_url = baseURL+'/getAffiliateReports';
        //the_url = reportsURL + '/affiliate_reports/datatable';

        var table = $('#internal-table'),
            body = $('#internal-table tbody'),
            footer = $('#internal-table tfoot tr');
        footer.find(':nth-child(2)').html('')
        footer.find(':nth-child(3)').html('')
        footer.find(':nth-child(4)').html('')
        footer.find(':nth-child(5)').html('')
        footer.find(':nth-child(6)').html('')
        footer.find(':nth-child(7)').html('')
        table.DataTable().destroy();
        body.empty();
        $.ajax({
            type: 'POST',
            url: the_url,
            data: {
                period : $('#snapshot_period').val(),
                affiliate_type : 1,
                start_date : $('#start_date').val(),
                end_date : $('#end_date').val(),
            },
            success: function(data){

                var total_clicks = 0, total_payout = 0.0, total_leads = 0, total_revenue = 0.0, total_weget = 0.0, total_margin = 0.0;
                var affiliates = data.affiliates;

                $.each(data.records, function(x, row){

                    var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                        leadCount = row.leads != null ? row.leads : 0,
                        payout = row.payout != null ? (row.payout).toFixed(3) : 0.0,
                        weGet = (revenue - payout).toFixed(2),
                        margin = (weGet == 0 || revenue ==  0) ? 0 : ((weGet / revenue) * 100).toFixed(2),
                        affiliate = affiliates[row.affiliate_id],
                        clicks = row.clicks != null ? row.clicks : 0;

                    var affCell = '<span class="ar_aff_id" data-id="'+row.affiliate_id+'" data-type="1">'+affiliate+'</span>';
                    var row = '<tr><td>'+affCell+'</td><td>'+clicks+'</td><td>$ '+payout+'</td><td>'+leadCount+'</td><td> $'+revenue+'</td><td> $'+weGet+'</td><td>'+margin+'%</td></tr>';
                    body.append(row);

                    total_clicks += Number(clicks);
                    total_payout += Number(payout);
                    total_leads += Number(leadCount);
                    total_revenue += Number(revenue);
                    total_weget += Number(weGet);
                });

                total_margin = total_revenue == 0 ? 'N/A' : (((total_revenue - total_payout) / total_revenue ) * 100.00).toFixed(2) + '%';

                footer.find(':nth-child(2)').html(total_clicks)
                footer.find(':nth-child(3)').html('$' + (total_payout).toLocaleString())
                footer.find(':nth-child(4)').html(total_leads)
                footer.find(':nth-child(5)').html('$' + (total_revenue).toLocaleString())
                footer.find(':nth-child(6)').html('$' + (total_weget).toLocaleString())
                footer.find(':nth-child(7)').html(total_margin)

                table.DataTable({language: {
                    searchPlaceholder: "Affiliate"
                }}).draw();
            }
        });
    }

    $('#internal-table').dataTable();
    $('#affiliate_website-table').dataTable();
    $('#subIDBreakdownTable').dataTable();
    internalAffiliateReport();

    $('#handp-table').DataTable();
    // $('#handp-subid-table').DataTable();
    
    hostPostAffiliateReport();
    function hostPostAffiliateReport() {
        console.log('host and post affiliate report')
        var the_url = baseURL + '/getHandPAffiliateReports';

        var table = $('#handp-table'),
            body = $('#handp-table tbody'),
            footer = $('#handp-table tfoot tr');
        footer.find(':nth-child(2)').html('')
        footer.find(':nth-child(3)').html('')
        footer.find(':nth-child(4)').html('')
        footer.find(':nth-child(5)').html('')
        footer.find(':nth-child(6)').html('')
        table.DataTable().destroy();
        body.empty();
        $.ajax({
            type: 'POST',
            url: the_url,
            data: {
                period : $('#snapshot_period').val(),
                affiliate_type : 1,
                start_date : $('#start_date').val(),
                end_date : $('#end_date').val(),
            },
            success: function(data){
                // console.log(data);
                var total_clicks = 0, total_payout = 0.0, total_leads = 0, total_revenue = 0.0, total_weget = 0.0, total_margin = 0.0;
                var affiliates = data.affiliates;

                $.each(data.records, function(x, row){

                    var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                        leadCount = row.leads != null ? row.leads : 0,
                        payout = row.payout != null ? (row.payout).toFixed(2) : 0.0,
                        weGet = (revenue - payout).toFixed(2),
                        margin = (weGet == 0 || revenue ==  0) ? 0 : ((weGet / revenue) * 100).toFixed(2),
                        affiliate = affiliates[row.affiliate_id],
                        clicks = row.clicks != null ? row.clicks : 0;

                    var affCell = '<span class="ar_aff_id" data-id="'+row.affiliate_id+'" data-type="1">'+affiliate+'</span>';
                    var row = '<tr><td>'+affCell+'</td><td>'+leadCount+'</td><td>$ '+payout+'</td><td> $'+revenue+'</td><td> $'+weGet+'</td><td>'+margin+'%</td></tr>';
                    body.append(row);

                    total_clicks += Number(clicks);
                    total_payout += Number(payout);
                    total_leads += Number(leadCount);
                    total_revenue += Number(revenue);
                    total_weget += Number(weGet);
                });

                total_margin = total_revenue == 0 ? 'N/A' : (((total_revenue - total_payout) / total_revenue ) * 100.00).toFixed(2) + '%';

                footer.find(':nth-child(2)').html(total_leads)
                footer.find(':nth-child(3)').html('$' + (total_payout).toLocaleString())
                footer.find(':nth-child(4)').html('$' + (total_revenue).toLocaleString())
                footer.find(':nth-child(5)').html('$' + (total_weget).toLocaleString())
                footer.find(':nth-child(6)').html(total_margin)

                table.DataTable({language: {
                    searchPlaceholder: "Affiliate"
                }}).draw();
            }
        });
    }

    var internal_iframe_table = $('#internal-iframe-table').DataTable({
        responsive: true,
        'processing': true,
        'serverSide': true,
        language: {
            searchPlaceholder: "Affiliate"
            // "sSearch": "<span>Affiliate:</span> _INPUT_" //search
        },
        lengthMenu: [[25,50,100,-1],[25,50,100,"All"]],
        'order': [[ 0, "asc" ]],
        'ajax':{
            url: the_iframe_affiliate_url, // json datasource
            type: 'post',
            'data': function(d)
            {
                d.period = $('#snapshot_period').val();
                d.affiliate_type = 1;
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            },
            'dataSrc': function (json)
            {
                console.log(json);

                $(internal_iframe_table.column(1).footer()).html(json.totalPassovers);
                $(internal_iframe_table.column(2).footer()).html(json.totalPayout);
                $(internal_iframe_table.column(3).footer()).html(json.totalLeads);
                $(internal_iframe_table.column(4).footer()).html(json.totalRevenue);
                $(internal_iframe_table.column(5).footer()).html(json.totalWeGet);
                $(internal_iframe_table.column(6).footer()).html(json.totalMargin);

                return json.data;
            }
        },
        'columns':[
            {'data':'affiliate'},
            {'data':'passovers'},
            {'data':'payout'},
            {'data':'leads'},
            {'data':'revenue'},
            {'data':'we_get'},
            {'data':'margin'}
        ]
    });

    var current_affiliate_id = 0;
    var current_affiliate_type = 0;
    var both_table;

    $('#revenueTrackersDetailsTable').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]]
    });

    $('#handp-subid-table').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]]
    });

    $('#subIDCampaignDetailsTable').DataTable({
        responsive: true,
        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]]
    });

    $(document).on('click','#handp-subid-table tbody tr td:first-child', function(e){

        if($(this).parent('tr').children().length > 1)
        {
            var affiliate = $.parseJSON($(this).find('.affiliate-details').html());
            var affiliateID = affiliate.affiliate_id;
            var s1 = affiliate.s1,
                s2 = affiliate.s2,
                s3 = affiliate.s3,
                s4 = affiliate.s4,
                s5 = affiliate.s5;
            var affiliateCDName = affiliateID + '/' + s1 + ' - ' + s2 + ' - ' + s3 + ' - ' + s4 + ' - ' + s5;

            var subIDCampaignDetailsModal = $('#subIDCampaignDetailsModal');
            $('#subID').html(affiliateCDName);

            var table = $('#subIDCampaignDetailsTable'),
                body = $('#subIDCampaignDetailsTable tbody'),
                footer = $('#subIDCampaignDetailsTable tfoot tr');
            footer.find(':nth-child(2)').html('')
            footer.find(':nth-child(3)').html('')
            table.DataTable().destroy();
            body.empty();
            $.ajax({
                type: 'POST',
                url: baseURL + '/getHandPSubIDReports',
                data: {
                    affiliate_id : affiliateID,
                    s1 : s1,
                    s2 : s2,
                    s3 : s3,
                    s4 : s4,
                    s5 : s5,
                    period : $('#snapshot_period').val(),
                    start_date : $('#start_date').val(),
                    end_date : $('#end_date').val(),
                },
                success: function(data){
                    //console.log(data);
                    var total_leads = 0, total_revenue = 0.0;
                    var campaigns = data.campaigns;

                    $.each(data.records, function(x, row){
                        // if(row.campaign_id == 1) {
                        //     console.log(row);
                        // }
                        var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                            leadCount = row.leads != null ? row.leads : 0;

                        var row = '<tr><td>'+campaigns[row.campaign_id]+'</td><td>'+leadCount+'</td><td>$ '+revenue+'</td></tr>';
                        body.append(row);

                        total_leads += Number(leadCount);
                        total_revenue += Number(revenue);
                    });

                    footer.find(':nth-child(2)').html(total_leads)
                    footer.find(':nth-child(3)').html('$' + (total_revenue).toLocaleString())

                    table.DataTable({
                        responsive: true,
                        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                        language: {
                            searchPlaceholder: "Campaign"
                        },
                    }).draw();
                }
            });

            // var subIDCampaignDetailsTable = $('#subIDCampaignDetailsTable');
            // subIDCampaignDetailsTable.DataTable().clear();
            // subIDCampaignDetailsTable.DataTable().destroy();

            // var subIDCampaignTable = subIDCampaignDetailsTable.DataTable({
            //     responsive: true,
            //     'processing': true,
            //     'serverSide': true,
            //     language: {
            //         searchPlaceholder: "Campaign"
            //     },
            //     lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
            //     'order': [[ 0, "asc" ]],
            //     'ajax':{
            //         url: the_subIDCampaignDetails_url, //json datasource
            //         type: 'post',
            //         'data': function(d)
            //         {
            //             d.period = $('#snapshot_period').val();
            //             d.affiliate_id = affiliateID;
            //             d.s1 = s1;
            //             d.s2 = s2;
            //             d.s3 = s3;
            //             d.s4 = s4;
            //             d.s5 = s5;
            //             d.start_date = $('#start_date').val();
            //             d.end_date = $('#end_date').val();
            //         },
            //         'dataSrc': function (json)
            //         {
            //             console.log(json);

            //             $(subIDCampaignTable.column(1).footer()).html(json.totalLeads);
            //             $(subIDCampaignTable.column(2).footer()).html(json.totalRevenue);

            //             // $('#handpSubIDModal').css('-webkit-filter','blur(5px) grayscale(90%)');

            //             return json.data;
            //         }
            //     },
            //     'columns':[
            //         {'data':'campaign'},
            //         {'data':'leads'},
            //         {'data':'revenue'}
            //     ],
            //     //'scrollY':        $(window).height()/2,
            //     //'scrollCollapse': true,
            //     //'paging':         false,
            //     'drawCallback': function(settings)
            //     {
            //         //$('.dataTables_scrollBody thead tr').addClass('hidden');
            //         //$('.dataTables_scrollBody tfoot tr').addClass('hidden');
            //         //$('.dataTables_scrollBody thead tr th').removeClass('sorting_asc sorting_desc sorting');
            //         //$('.dataTables_scrollBody thead').css('display','none');
            //         //$('.dataTables_scrollBody tfoot').css('display','none');
            //     },
            //     'initComplete': function()
            //     {
            //         //$('.dataTables_scrollBody thead tr').addClass('hidden');
            //         //$('.dataTables_scrollBody tfoot tr').addClass('hidden');
            //         //$('.dataTables_scrollBody thead tr th').removeClass('sorting_asc sorting_desc sorting');
            //         //$('.dataTables_scrollBody thead').css('display','none');
            //         //$('.dataTables_scrollBody tfoot').css('display','none');
            //     }
            // });

            if(!subIDCampaignDetailsModal.hasClass('in'))
            {
                subIDCampaignDetailsModal.modal('show');
            }
        }
    });

    // $('#subIDCampaignDetailsModal').on('hidden.bs.modal', function (e) {
    //   $('#handpSubIDModal').css('-webkit-filter','');
    // });
    var revenueTracker;
    function revSubIDBreakdown() {
        var affiliateID = revenueTracker.data('id');
        var revenueTrackerID = revenueTracker.data('rtid');
        var websiteName = revenueTracker.data('webname');

        console.log('affiliate_id: '+affiliateID);
        console.log('revenue_tracker_id: '+revenueTrackerID);
        console.log('webname: '+websiteName);

        $('#downloadSubReportBtn').addClass('disabled');

        //SubID Breakdown
        $('#AffRevTitle').html(websiteName);

        the_url = baseURL+'/getRevTrackerSubIDReports';
        //the_url = reportsURL + '/affiliate_reports/datatable';

        var table = $('#subIDBreakdownTable'),
            body = $('#subIDBreakdownTable tbody'),
            footer = $('#subIDBreakdownTable tfoot tr');
        footer.find(':nth-child(6)').html('')
        footer.find(':nth-child(7)').html('')
        footer.find(':nth-child(8)').html('')
        footer.find(':nth-child(9)').html('')
        footer.find(':nth-child(10)').html('')
        footer.find(':nth-child(11)').html('')
        table.DataTable().destroy();
        body.empty();
        $.ajax({
            type: 'POST',
            url: the_url,
            data: {
                affiliate_id : affiliateID,
                revenue_tracker_id : revenueTrackerID,
                period : $('#snapshot_period').val(),
                start_date : $('#start_date').val(),
                end_date : $('#end_date').val(),
                sib_s1: $('#filter-subid-form #sib_s1').prop('checked'),
                sib_s2: $('#filter-subid-form #sib_s2').prop('checked'),
                sib_s3: $('#filter-subid-form #sib_s3').prop('checked'),
                sib_s4: $('#filter-subid-form #sib_s4').prop('checked'),
            },
            success: function(data){
                // console.log(data);
                var total_clicks = 0, total_payout = 0.0, total_leads = 0, total_revenue = 0.0, total_weget = 0.0, total_margin = 0.0;

                $.each(data.records, function(x, row){

                    var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                        leadCount = row.leads != null ? row.leads : 0,
                        payout = row.payout != null ? (row.payout).toFixed(3) : 0.0,
                        weGet = (revenue - payout).toFixed(2),
                        margin = (weGet == 0 || revenue ==  0) ? 0 : ((weGet / revenue) * 100).toFixed(2),
                        clicks = row.clicks != null ? row.clicks : 0,
                        s1 = $('#filter-subid-form #sib_s1').prop('checked') ? row.s1 : '',
                        s2 = $('#filter-subid-form #sib_s2').prop('checked') ? row.s2 : '',
                        s3 = $('#filter-subid-form #sib_s3').prop('checked') ? row.s3 : '',
                        s4 = $('#filter-subid-form #sib_s4').prop('checked') ? row.s4 : '',
                        s5 = row.s5;

                    var webCell = s1 + '<textarea class="subid-details hidden">'+ JSON.stringify(row)+'</textarea>';
                    var row = '<tr><td>'+webCell+'</td><td>'+s2+'</td><td>'+s3+'</td><td>'+s4+'</td><td>'+s5+'</td><td>'+clicks+'</td><td>$ '+payout+'</td><td>'+leadCount+'</td><td> $'+revenue+'</td><td> $'+weGet+'</td><td>'+margin+'%</td></tr>';
                    body.append(row);

                    total_clicks += Number(clicks);
                    total_payout += Number(payout);
                    total_leads += Number(leadCount);
                    total_revenue += Number(revenue);
                    total_weget += Number(weGet);
                });

                total_margin = total_revenue == 0 ? 'N/A' : (((total_revenue - total_payout) / total_revenue ) * 100.00).toFixed(2) + '%';

                footer.find(':nth-child(6)').html(total_clicks)
                footer.find(':nth-child(7)').html('$' + (total_payout).toLocaleString())
                footer.find(':nth-child(8)').html(total_leads)
                footer.find(':nth-child(9)').html('$' + (total_revenue).toLocaleString())
                footer.find(':nth-child(10)').html('$' + (total_weget).toLocaleString())
                footer.find(':nth-child(11)').html(total_margin)

                table.DataTable({
                    responsive: true,
                    lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                }).draw();

                var dlHref = baseURL+'/downloadRevTrackerSubIDReports?affiliate_id='+affiliateID+'&revenue_tracker_id='+revenueTrackerID+'&period='+$('#snapshot_period').val()
                    +'&start_date='+$('#start_date').val()+'&end_date='+$('#end_date').val()+'&sib_s1='+$('#filter-subid-form #sib_s1').prop('checked')+'&sib_s2='+$('#filter-subid-form #sib_s2').prop('checked')+
                    '&sib_s3='+$('#filter-subid-form #sib_s3').prop('checked')+'&sib_s4='+$('#filter-subid-form #sib_s4').prop('checked');
                console.log(dlHref)
                $('#downloadSubReportBtn').removeClass('disabled').attr('href', dlHref);
            }
        });
    }

    $(document).on('click','#affiliate_website-table tbody tr td:first-child', function(e){

        if($(this).parent('tr').children().length > 1)
        {
            revenueTracker = $(this).find('.revenue-tracker');
            revSubIDBreakdown();

            $('#subIDDetailsModal').modal('show');
        }
    });

    $(document).on('submit','#filter-subid-form', function(e){

        e.preventDefault();
        revSubIDBreakdown();
    });

    $('#subIDDetailsModal').on('hidden.bs.modal', function (e) {
      $('#filter-subid-form #sib_s1').prop('checked', false);
      $('#filter-subid-form #sib_s2').prop('checked', false);
      $('#filter-subid-form #sib_s3').prop('checked', false);
      $('#filter-subid-form #sib_s4').prop('checked', false);
    });

    $(document).on('click','#subIDBreakdownTable tbody tr td:nth-child(1)', function(e){

        if($(this).parent('tr').children().length > 1)
        {
            var revenueTracker = $.parseJSON($(this).find('.subid-details').html());
            var affiliateID = revenueTracker.affiliate_id;
            var revenueTrackerID = revenueTracker.revenue_tracker_id;
            var s1 = $('#filter-subid-form #sib_s1').prop('checked') ? revenueTracker.s1 : '',
                s2 = $('#filter-subid-form #sib_s2').prop('checked') ? revenueTracker.s2 : '',
                s3 = $('#filter-subid-form #sib_s3').prop('checked') ? revenueTracker.s3 : '',
                s4 = $('#filter-subid-form #sib_s4').prop('checked') ? revenueTracker.s4 : '',
                s5 = revenueTracker.s5;
            var websiteName = s1 + ' - ' + s2 + ' - ' + s3 + ' - ' + s4 + ' - ' + s5;

            console.log('affiliate_id: '+affiliateID);
            console.log('revenue_tracker_id: '+revenueTrackerID);
            console.log('webname: '+websiteName);
           
            var revenueTrackerDetailsModal = $('#revenueTrackerDetailsModal');
            $('#revenueTrackerWebsite').html(websiteName);

            the_url = baseURL + '/getRevenueTrackerReports';
            //the_url = reportsURL + '/affiliate_reports/datatable';

            var table = $('#revenueTrackersDetailsTable'),
                body = $('#revenueTrackersDetailsTable tbody'),
                footer = $('#revenueTrackersDetailsTable tfoot tr');
            footer.find(':nth-child(2)').html('')
            footer.find(':nth-child(3)').html('')
            table.DataTable().destroy();
            body.empty();
            $.ajax({
                type: 'POST',
                url: the_url,
                data: {
                    affiliate_id : affiliateID,
                    revenue_tracker_id : revenueTrackerID,
                    s1 : s1,
                    s2 : s2,
                    s3 : s3,
                    s4 : s4,
                    s5 : s5,
                    period : $('#snapshot_period').val(),
                    start_date : $('#start_date').val(),
                    end_date : $('#end_date').val(),
                    sib_s1: $('#filter-subid-form #sib_s1').prop('checked'),
                    sib_s2: $('#filter-subid-form #sib_s2').prop('checked'),
                    sib_s3: $('#filter-subid-form #sib_s3').prop('checked'),
                    sib_s4: $('#filter-subid-form #sib_s4').prop('checked'),
                },
                success: function(data){
                    // console.log(data);
                    var total_leads = 0, total_revenue = 0.0;
                    var campaigns = data.campaigns;

                    $.each(data.records, function(x, row){
                        if(row.campaign_id == 1) {
                            console.log(row);
                        }
                        var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                            leadCount = row.lead_count != null ? row.lead_count : 0;

                        var webCell = row.s1 + '<textarea class="subid-details hidden">'+ JSON.stringify(row)+'</textarea>';
                        var row = '<tr><td>'+campaigns[row.campaign_id]+'</td><td>'+leadCount+'</td><td>$ '+revenue+'</td></tr>';
                        body.append(row);

                        total_leads += Number(leadCount);
                        total_revenue += Number(revenue);
                    });

                    footer.find(':nth-child(2)').html(total_leads)
                    footer.find(':nth-child(3)').html('$' + (total_revenue).toLocaleString())

                    table.DataTable({
                        responsive: true,
                        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                        language: {
                            searchPlaceholder: "Campaign"
                        },
                    }).draw();
                }
            });

            if(!revenueTrackerDetailsModal.hasClass('in'))
            {
                revenueTrackerDetailsModal.modal('show');
            }
        }
    });
    
    // $('#revenueTrackerDetailsModal').on('hidden.bs.modal', function (e) {
    //   $('#subIDDetailsModal').css('-webkit-filter','');
    // });

    $(document).on('click','#iframe_affiliate_website-table tbody tr td:first-child', function(e){

        if($(this).parent('tr').children().length > 1)
        {
            var revenueTracker = $(this).find('.revenue-tracker');
            var affiliateID = revenueTracker.data('id');
            var revenueTrackerID = revenueTracker.data('rtid');
            var websiteName = revenueTracker.data('webname');

            var iframeRevenueTrackerDetailsModal = $('#iframeRevenueTrackerDetailsModal');

            console.log('affiliate_id: '+affiliateID);
            console.log('revenue_tracker_id: '+revenueTrackerID);
            console.log('webname: '+websiteName);

            //$('#snapshot_period option:selected').html()
            $('#iframeRevenueTrackerWebsite').html(websiteName);

            var iframeRevenueTrackersDetailsTable = $('#iframeRevenueTrackersDetailsTable');
            iframeRevenueTrackersDetailsTable.DataTable().clear();
            iframeRevenueTrackersDetailsTable.DataTable().destroy();

            var iframeRevenueTrackersTable = iframeRevenueTrackersDetailsTable.DataTable({
                responsive: true,
                'processing': true,
                'serverSide': true,
                language: {
                    searchPlaceholder: "Campaign"
                },
                lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                'order': [[ 0, "asc" ]],
                'ajax':{
                    url: the_iframe_revenueTracker_url, // json datasource
                    type: 'post',
                    'data': function(d)
                    {
                        d.period = $('#snapshot_period').val();
                        d.affiliate_id = affiliateID;
                        d.revenue_tracker_id = revenueTrackerID;
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    },
                    'dataSrc': function (json)
                    {
                        console.log(json);

                        $(iframeRevenueTrackersTable.column(1).footer()).html(json.totalLeads);
                        $(iframeRevenueTrackersTable.column(2).footer()).html(json.totalRevenue);

                        return json.data;
                    }
                },
                'columns':[
                    {'data':'campaign'},
                    {'data':'leads'},
                    {'data':'revenue'}
                ],
                //'scrollY':        $(window).height()/2,
                //'scrollCollapse': true,
                //'paging':         false,
                'drawCallback': function(settings)
                {
                    //$('.dataTables_scrollBody thead tr').addClass('hidden');
                    //$('.dataTables_scrollBody tfoot tr').addClass('hidden');
                    //$('.dataTables_scrollBody thead tr th').removeClass('sorting_asc sorting_desc sorting');
                    //$('.dataTables_scrollBody thead').css('display','none');
                    //$('.dataTables_scrollBody tfoot').css('display','none');
                },
                'initComplete': function()
                {
                    //$('.dataTables_scrollBody thead tr').addClass('hidden');
                    //$('.dataTables_scrollBody tfoot tr').addClass('hidden');
                    //$('.dataTables_scrollBody thead tr th').removeClass('sorting_asc sorting_desc sorting');
                    //$('.dataTables_scrollBody thead').css('display','none');
                    //$('.dataTables_scrollBody tfoot').css('display','none');
                }
            });

            if(!iframeRevenueTrackerDetailsModal.hasClass('in'))
            {
                iframeRevenueTrackerDetailsModal.modal('show');
            }
        }
    });

    //Website Statistics breakdown
    $(document).on('click','#internal-iframe-table tbody tr td:first-child',function(e)
    {
        if($(this).parent('tr').children().length > 1)
        {

            var affiliate = $(this).find('.ar_aff_id');
            var aff_id = affiliate.data('id');

            $('#pr_aff_cmp').html($(this).find('.ar_aff_id').html());
            current_affiliate_id = aff_id;

            var affiliateWebSiteTable = $('#iframe_affiliate_website-table');
            affiliateWebSiteTable.DataTable().clear();
            affiliateWebSiteTable.DataTable().destroy();

            var website_table = affiliateWebSiteTable.DataTable({
                responsive: true,
                'processing': true,
                'serverSide': true,
                language: {
                    searchPlaceholder: "Website"
                },
                lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                'order': [[ 0, "asc" ]],
                'ajax':{
                    url: the_iframe_website_url, // json datasource
                    type: 'post',
                    'data': function(d)
                    {
                        d.period = $('#snapshot_period').val();
                        d.affiliate_id = current_affiliate_id;
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    },
                    'dataSrc': function (json)
                    {
                        console.log(json);

                        $(website_table.column(1).footer()).html(json.totalPassovers);
                        $(website_table.column(2).footer()).html(json.totalPayout);
                        $(website_table.column(3).footer()).html(json.totalLeads);
                        $(website_table.column(4).footer()).html(json.totalRevenue);
                        $(website_table.column(5).footer()).html(json.totalWeGet);
                        $(website_table.column(6).footer()).html(json.totalMargin);

                        return json.data;
                    }
                },
                'columns':[
                    {'data':'website_name'},
                    {'data':'passovers'},
                    {'data':'payout'},
                    {'data':'leads'},
                    {'data':'revenue'},
                    {'data':'we_get'},
                    {'data':'margin'}
                ]
            });

            //website_table.order([]);
            //website_table.ajax.reload();
            $('#prIframeAffiliateWebsiteModal').modal('show');
        }
    });

    //Inter Affiliate Reports Website Statistics breakdown
    $(document).on('click','#internal-table tbody tr td:first-child',function(e)
    {
        if($(this).parent('tr').children().length > 1) {
            var affiliate = $(this).find('.ar_aff_id');
            var aff_id = affiliate.data('id');
            var affiliate_type = affiliate.data('type');

            $('#pr_aff_cmp').html($(this).find('.ar_aff_id').html());
            current_affiliate_id = aff_id;
            current_affiliate_type = affiliate_type;

            the_url = baseURL+'/getWebsiteReports';
            //the_url = reportsURL + '/affiliate_reports/datatable';

            var table = $('#affiliate_website-table'),
                body = $('#affiliate_website-table tbody'),
                footer = $('#affiliate_website-table tfoot tr');
            footer.find(':nth-child(2)').html('')
            footer.find(':nth-child(3)').html('')
            footer.find(':nth-child(4)').html('')
            footer.find(':nth-child(5)').html('')
            footer.find(':nth-child(6)').html('')
            footer.find(':nth-child(7)').html('')
            table.DataTable().destroy();
            body.empty();
            $.ajax({
                type: 'POST',
                url: the_url,
                data: {
                    affiliate_id : current_affiliate_id,
                    period : $('#snapshot_period').val(),
                    affiliate_type : affiliate_type,
                    start_date : $('#start_date').val(),
                    end_date : $('#end_date').val(),
                },
                success: function(data){
                    var total_clicks = 0, total_payout = 0.0, total_leads = 0, total_revenue = 0.0, total_weget = 0.0, total_margin = 0.0;
                    var websites = data.websites;

                    $.each(data.records, function(x, row){

                        var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                            leadCount = row.leads != null ? row.leads : 0,
                            payout = row.payout != null ? (row.payout).toFixed(3) : 0.0,
                            weGet = (revenue - payout).toFixed(2),
                            margin = (weGet == 0 || revenue ==  0) ? 0 : ((weGet / revenue) * 100).toFixed(2),
                            website = websites[row.revenue_tracker_id],
                            clicks = row.clicks != null ? row.clicks : 0;

                        var webCell = '<span class="revenue-tracker" data-id="'+ row.affiliate_id+'" data-rtid="'+row.revenue_tracker_id+'" data-webname="'+website+'">'+website+'</span>';
                        var row = '<tr><td>'+webCell+'</td><td>'+clicks+'</td><td>$ '+payout+'</td><td>'+leadCount+'</td><td> $'+revenue+'</td><td> $'+weGet+'</td><td>'+margin+'%</td></tr>';
                        body.append(row);

                        total_clicks += Number(clicks);
                        total_payout += Number(payout);
                        total_leads += Number(leadCount);
                        total_revenue += Number(revenue);
                        total_weget += Number(weGet);
                    });

                    total_margin = total_revenue == 0 ? 'N/A' : (((total_revenue - total_payout) / total_revenue ) * 100.00).toFixed(2) + '%';

                    footer.find(':nth-child(2)').html(total_clicks)
                    footer.find(':nth-child(3)').html('$' + (total_payout).toLocaleString())
                    footer.find(':nth-child(4)').html(total_leads)
                    footer.find(':nth-child(5)').html('$' + (total_revenue).toLocaleString())
                    footer.find(':nth-child(6)').html('$' + (total_weget).toLocaleString())
                    footer.find(':nth-child(7)').html(total_margin)

                    table.DataTable({
                        responsive: true,
                        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                        language: {
                            searchPlaceholder: "Website"
                        }
                    }).draw();
                }
            });
            $('#prAffiliateWebsiteModal').modal('show');
        }
    });

    //subID Statistics breakdown
    $(document).on('click','#handp-table tbody tr td:first-child',function(e)
    {
        if($(this).parent('tr').children().length > 1)
        {
            var affiliate = $(this).find('.ar_aff_id');
            var aff_id = affiliate.data('id');
            var affiliate_type = affiliate.data('type');

            $('#handp_aff_cmp').html($(this).find('.ar_aff_id').html());
            current_affiliate_id = aff_id;
            current_affiliate_type = affiliate_type;

            var table = $('#handp-subid-table'),
                body = $('#handp-subid-table tbody'),
                footer = $('#handp-subid-table tfoot tr');
            footer.find(':nth-child(7)').html('')
            footer.find(':nth-child(8)').html('')
            footer.find(':nth-child(9)').html('')
            footer.find(':nth-child(10)').html('')
            footer.find(':nth-child(11)').html('')
            table.DataTable().destroy();
            body.empty();
            $.ajax({
                type: 'POST',
                url: baseURL + '/getHandPAffiliateSubIDReports',
                data: {
                    affiliate_id : current_affiliate_id,
                    period : $('#snapshot_period').val(),
                    affiliate_type : current_affiliate_type,
                    affiliate_id : current_affiliate_id,
                    start_date : $('#start_date').val(),
                    end_date : $('#end_date').val(),
                },
                success: function(data){
                    // console.log(data);
                    var total_payout = 0.0, total_leads = 0, total_revenue = 0.0, total_weget = 0.0, total_margin = 0.0;
                    var websites = data.websites;

                    $.each(data.records, function(x, row){

                        var revenue = row.revenue != null ? (row.revenue).toFixed(2) : 0.0,
                            leadCount = row.leads != null ? row.leads : 0,
                            payout = row.payout != null ? (row.payout).toFixed(2) : 0.0,
                            weGet = (revenue - payout).toFixed(2),
                            margin = (weGet == 0 || revenue ==  0) ? 0 : ((weGet / revenue) * 100).toFixed(2);

                        var webCell = row.affiliate_id + '<textarea class="affiliate-details hidden">'+ JSON.stringify(row)+'</textarea>';
                        var row = '<tr><td>'+webCell+'</td><td>'+row.s1+'</td><td>'+row.s2+'</td><td>'+row.s3+'</td><td>'+row.s4+'</td><td>'+row.s5+'</td><td>'+leadCount+'</td><td>$ '+payout+'</td><td> $'+revenue+'</td><td> $'+weGet+'</td><td>'+margin+'%</td></tr>';
                        body.append(row);

                        total_payout += Number(payout);
                        total_leads += Number(leadCount);
                        total_revenue += Number(revenue);
                        total_weget += Number(weGet);
                    });

                    total_margin = total_revenue == 0 ? 'N/A' : (((total_revenue - total_payout) / total_revenue ) * 100.00).toFixed(2) + '%';

                    footer.find(':nth-child(7)').html(total_leads)
                    footer.find(':nth-child(8)').html('$' + (total_payout).toLocaleString())
                    footer.find(':nth-child(9)').html('$' + (total_revenue).toLocaleString())
                    footer.find(':nth-child(10)').html('$' + (total_weget).toLocaleString())
                    footer.find(':nth-child(11)').html(total_margin)

                    table.DataTable({
                        responsive: true,
                        lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
                        language: {
                            searchPlaceholder: "Sub ID"
                        }
                    }).draw();
                }
            });

            // var subIDTable = handpSubIDTable.DataTable({
            //     responsive: true,
            //     'processing': true,
            //     'serverSide': true,
            //     language: {
            //         searchPlaceholder: "Sub ID"
            //     },
            //     lengthMenu: [[10,15,25,50,100,-1],[10,15,25,50,100,"All"]],
            //     'order': [[ 0, "asc" ]],
            //     'ajax':{
            //         url: handpAffiliateSubIDReportsURL, // json datasource
            //         type: 'post',
            //         'data': function(d)
            //         {
            //             d.period = $('#snapshot_period').val();
            //             d.affiliate_type = current_affiliate_type;
            //             d.affiliate_id = current_affiliate_id;
            //             d.start_date = $('#start_date').val();
            //             d.end_date = $('#end_date').val();
            //         },
            //         'dataSrc': function (json)
            //         {
            //             console.log(json);

            //             $(subIDTable.column(2).footer()).html(json.totalLeads);
            //             $(subIDTable.column(3).footer()).html(json.totalPayout);
            //             $(subIDTable.column(4).footer()).html(json.totalRevenue);
            //             $(subIDTable.column(5).footer()).html(json.totalWeGet);
            //             $(subIDTable.column(6).footer()).html(json.totalMargin);

            //             return json.data;
            //         }
            //     },
            //     'columns':[
            //         {'data':'affiliate'},
            //         {'data':'s1'},
            //         {'data':'s2'},
            //         {'data':'s3'},
            //         {'data':'s4'},
            //         {'data':'s5'},
            //         {'data':'leads'},
            //         {'data':'payout'},
            //         {'data':'revenue'},
            //         {'data':'we_get'},
            //         {'data':'margin'}
            //     ]
            // });

            // website_table.order([]);
            // website_table.ajax.reload();
            $('#handpSubIDModal').modal('show');
        }
    });

    $('[href="#internal_tab"]').click(function()
    {
        $('.alert-danger-wrapper').hide();
        $('.alert-warning-wrapper').hide();
        $('.alert-success-wrapper').hide();
        $('.internal-error-wrapper').hide();
    });

    $('[href="#handp_tab"]').click(function()
    {
        $('.alert-danger-wrapper').hide();
        $('.alert-warning-wrapper').hide();
        $('.alert-success-wrapper').hide();
        $('.handp-error-wrapper').hide();
    });

    $('[href="#internal_iframe_tab"]').click(function()
    {
        $('.alert-danger-wrapper').hide();
        $('.alert-warning-wrapper').hide();
        $('.alert-success-wrapper').hide();
        $('.internal-iframe-error-wrapper').hide();
    });

    $('[href="#both_tab"]').click(function()
    {
        if(!$.fn.DataTable.isDataTable('#both-table'))
        {
            both_table = $('#both-table').DataTable({
                responsive: true,
                'processing': true,
                'serverSide': true,
                language: {
                    searchPlaceholder: "Affiliate"
                    // "sSearch": "<span>Affiliate:</span> _INPUT_" //search
                },
                lengthMenu: [[25,50,100,-1],[25,50,100,"All"]],
                'order': [[ 0, "asc" ]],
                'ajax':{
                    url:the_affiliate_url, // json datasource
                    type: 'post',
                    'data': function(d)
                    {
                        d.period = $('#snapshot_period').val();
                        d.affiliate_type = 0;
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    },
                    'dataSrc': function (json)
                    {
                        console.log(json);

                        $(both_table.column(1).footer()).html(json.totalClicks);
                        $(both_table.column(2).footer()).html(json.totalPayout);
                        $(both_table.column(3).footer()).html(json.totalLeads);
                        $(both_table.column(4).footer()).html(json.totalRevenue);
                        $(both_table.column(5).footer()).html(json.totalWeGet);
                        $(both_table.column(6).footer()).html(json.totalMargin);

                        return json.data;
                    }
                },
                'columns':[
                    {'data':'affiliate'},
                    {'data':'clicks'},
                    {'data':'payout'},
                    {'data':'leads'},
                    {'data':'revenue'},
                    {'data':'we_get'},
                    {'data':'margin'}
                ]
            });
        }
    });

    $('.generate-handp-excel-report').click(function(){

        var generateButton = $(this);
        //var affiliateType = generateButton.data('affiliate_type');
        var downloadLinkID = generateButton.data('download_link_id');
        var snapShot = snapShotSelect.val();

        var originalGenerateText = generateButton.html();

        var generateURL = baseURL+'/generateHandPAffiliateReportsXLS/'+snapShot;

        generateButton.attr('disabled','true');
        generateButton.html('<i class="fa fa-spin fa-spinner"></i>');

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        var payloadData = {
            start_date: startDate,
            end_date: endDate
        };

        $.ajax({
            type: 'GET',
            url: generateURL,
            data: payloadData,
            error: function(data)
            {
                var errors = data.responseJSON;
                console.log(data);

                var errorContainer = $('.this_errors');

                $('.this_error_wrapper').show();
                errorContainer.html('');
                errorContainer.show();

                var errorsHtml = '<ul>';

                $.each( errors, function( key, value ) {
                    errorsHtml += '<li> <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> ' + value[0] + '</li>'; //showing only the first error.
                    form.find('label[for="'+key+'"]').addClass('error_label error');
                    form.find('#'+key).addClass('error_field error');
                });

                errorsHtml += '</ul>';
                errorContainer.append(errorsHtml);

                generateButton.html(originalGenerateText);
                generateButton.removeAttr('disabled');

            },
            success: function(data)
            {
                $('.this_error_wrapper').show();
                console.log(data.status);
                console.log(data.message);
                console.log(data.key);
                console.log(data.file);

                var alertDangerWrapper = $('.alert-danger-wrapper');
                var alertSuccessWrapper = $('.alert-success-wrapper');

                alertDangerWrapper.hide();
                $('.alert-warning-wrapper').hide();
                alertSuccessWrapper.hide();

                switch(data.status)
                {
                    case 'generate_failed':

                        alertDangerWrapper.show();
                        var alertDangerContent = $('.alert-danger-content');
                        alertDangerContent.html('');
                        alertDangerContent.append(data.message);
                        break;

                    default:

                        alertSuccessWrapper.show();
                        var alertSuccessContent = $('.alert-success-content');
                        alertSuccessContent.html('');
                        alertSuccessContent.append(data.message);

                        console.log(downloadLinkID);
                        console.log(snapShot);

                        // var dateRangePayload = '?start_date='+$('#start_date').val()+'&end_date='+$('#end_date').val();

                        //update the download button
                        var downloadURL = baseURL+'/downloadAffiliateReportXLS?file_name_dl='+encodeURI(data.file);
                        $(downloadLinkID).attr('href', downloadURL);
                        // $(downloadLinkID).attr('href', baseURL+'/downloadHandPAffiliateReportXLS/'+snapShot+dateRangePayload);

                        //show the DL link
                        $(downloadLinkID).show();

                        break;
                }

                generateButton.html(originalGenerateText);
                generateButton.removeAttr('disabled');
            }
        });

    });

    $('.generate-iframe-excel-report').click(function()
    {
        var generateButton = $(this);
        //var affiliateType = generateButton.data('affiliate_type');
        var downloadLinkID = generateButton.data('download_link_id');
        var snapShot = snapShotSelect.val();

        var originalGenerateText = generateButton.html();

        var generateURL = baseURL+'/generateIframeAffiliateReportXLS/'+snapShot;

        generateButton.attr('disabled','true');
        generateButton.html('<i class="fa fa-spin fa-spinner"></i>');

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        var payloadData = {
            start_date: startDate,
            end_date: endDate
        };

        $.ajax({
            type: 'GET',
            url: generateURL,
            data: payloadData,
            error: function(data)
            {
                var errors = data.responseJSON;
                console.log(data);

                var errorsContainer = $('.this_errors');
                $('.this_error_wrapper').show();
                errorsContainer.html('');
                errorsContainer.show();

                var errorsHtml = '<ul>';
                $.each( errors, function( key, value ) {
                    errorsHtml += '<li> <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> ' + value[0] + '</li>'; //showing only the first error.
                    form.find('label[for="'+key+'"]').addClass('error_label error');
                    form.find('#'+key).addClass('error_field error');
                });

                errorsHtml += '</ul>';

                errorsContainer.append(errorsHtml);

                generateButton.html(originalGenerateText);
                generateButton.removeAttr('disabled');

            },
            success: function(data)
            {
                $('.this_error_wrapper').show();
                console.log(data.status);
                console.log(data.message);
                console.log(data.key);
                console.log(data.file);

                var alertDangerWrapper = $('.alert-danger-wrapper');
                alertDangerWrapper.hide();
                $('.alert-warning-wrapper').hide();
                var alertSuccessWrapper = $('.alert-success-wrapper');
                alertSuccessWrapper.hide();

                switch(data.status)
                {
                    case 'generate_failed':

                        alertDangerWrapper.show();
                        var alertDangerContent = $('.alert-danger-content');
                        alertDangerContent.html('');
                        alertDangerContent.append(data.message);
                        break;

                    default:

                        alertSuccessWrapper.show();
                        var alertSuccessContent = $('.alert-success-content');
                        alertSuccessContent.html('');
                        alertSuccessContent.append(data.message);

                        console.log(downloadLinkID);
                        console.log(snapShot);

                        // var dateRangePayload = '?start_date='+$('#start_date').val()+'&end_date='+$('#end_date').val();

                        //update the download button
                        var downloadURL = baseURL+'/downloadAffiliateReportXLS?file_name_dl='+encodeURI(data.file);
                        $(downloadLinkID).attr('href', downloadURL);
                        // $(downloadLinkID).attr('href', baseURL+'/downloadIframeAffiliateReportXLS/'+snapShot+dateRangePayload);

                        //show the DL link
                        $(downloadLinkID).show();

                        break;
                }

                generateButton.html(originalGenerateText);
                generateButton.removeAttr('disabled');
            }
        });

    });

    $('.generate-excel-report').click(function()
    {
        var generateButton = $(this);
        var affiliateType = generateButton.data('affiliate_type');
        var downloadLinkID = generateButton.data('download_link_id');
        var downloadLinkIDNSB = $('#downloadXLSInternalNSB');
        var snapShot = snapShotSelect.val();

        var originalGenerateText = generateButton.html();

        $(downloadLinkID).hide();
        $(downloadLinkIDNSB).hide();

        //$('a#downloadXLSInternal').attr('href', baseURL+'/downloadAffiliateReportXLS/1/'+snapshotPeriod);
        var generateURL = baseURL+'/generateAffiliateReportXLS/'+affiliateType+'/'+snapShot
        // var generateURL = reportsURL+'/affiliate_reports/generate/'+affiliateType+'/'+snapShot

        generateButton.attr('disabled','true');
        generateButton.html('<i class="fa fa-spin fa-spinner"></i>');

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        var payloadData = {
            start_date: startDate,
            end_date: endDate
        };

        $.ajax({
            type: 'GET',
            url: generateURL,
            data: payloadData,
            error: function(data)
            {
                var errors = data.responseJSON;
                console.log(data);

                var errorsContainer = $('.this_errors');
                $('.this_error_wrapper').show();
                errorsContainer.html('');
                errorsContainer.show();

                var errorsHtml = '<ul>';
                $.each( errors, function( key, value ) {
                    errorsHtml += '<li> <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> ' + value[0] + '</li>'; //showing only the first error.
                    form.find('label[for="'+key+'"]').addClass('error_label error');
                    form.find('#'+key).addClass('error_field error');
                });

                errorsHtml += '</ul>';

                errorsContainer.append(errorsHtml);

                generateButton.html(originalGenerateText);
                generateButton.removeAttr('disabled');

            },
            success: function(data)
            {
                $('.this_error_wrapper').show();
                console.log(data);

                var alertDangerWrapper = $('.alert-danger-wrapper');
                alertDangerWrapper.hide();
                $('.alert-warning-wrapper').hide();
                var alertSuccessWrapper = $('.alert-success-wrapper');
                alertSuccessWrapper.hide();

                switch(data.status)
                {
                    case 'generate_failed':

                        alertDangerWrapper.show();
                        var alertDangerContent = $('.alert-danger-content');
                        alertDangerContent.html('');
                        alertDangerContent.append(data.message);
                        break;
                    default:

                        alertSuccessWrapper.show();
                        var alertSuccessContent = $('.alert-success-content');
                        alertSuccessContent.html('');
                        alertSuccessContent.append(data.message);

                        // var dateRangePayload = '?start_date='+$('#start_date').val()+'&end_date='+$('#end_date').val();

                        if(data.status == 'generate_successful') {
                            //update the download button
                            // $(downloadLinkID).attr('href', baseURL+'/downloadAffiliateReportXLS/'+affiliateType+'/'+snapShot+dateRangePayload);
                            var downloadURL = reportsURL+'/downloadAffiliateReportXLS?file_name_dl='+encodeURI(data.file);
                            // var downloadURL = reportsURL+'/affiliate_reports/download/?file_name_dl='+encodeURI(data.file);
                            $(downloadLinkID).attr('href', downloadURL);

                            //show the DL link
                            $(downloadLinkID).show();

                            if(data.hasSubidBreakdown) {
                                var downloadURL = reportsURL+'/downloadAffiliateReportXLS?file_name_dl='+encodeURI(data.nsb_file);
                                // var downloadURL = reportsURL+'/affiliate_reports/download/?file_name_dl='+encodeURI(data.nsb_file);
                                $(downloadLinkIDNSB).attr('href', downloadURL);
                                $(downloadLinkIDNSB).show();
                            }

                        }

                        break;
                }

                generateButton.html(originalGenerateText);
                generateButton.removeAttr('disabled');
            }
        });

    });

    //at first the internal tab is the current active tab.
    var activeTab = '#internal_tab';

    $(document).on('submit','#updatePublisherReportsTable',function(e)
    {
        e.preventDefault();

        $('.pr_sp_periord').html($('#snapshot_period option:selected').html());
        console.log(activeTab);

        var snapShot = $('#snapshot_period').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if($.fn.DataTable.isDataTable('#internal-table' ) && activeTab == '#internal_tab')
        {
            //update the hidden fields/variables
            $('#internal_tab_snap_shot').val(snapShot);
            $('#internal_tab_start_date').val(startDate);
            $('#internal_tab_end_date').val(endDate);

            // internal_table.order([[ 0, "asc" ]]);
            // internal_table.ajax.reload();
            internalAffiliateReport();
        }

        if ($.fn.DataTable.isDataTable('#handp-table') && activeTab == '#handp_tab')
        {
            //update the hidden fields/variables
            $('#handp_tab_snap_shot').val(snapShot);
            $('#hand_tab_start_date').val(startDate);
            $('#handp_tab_end_date').val(endDate);

            // handp_table.order([[ 0, "asc" ]]);
            // handp_table.ajax.reload();

            hostPostAffiliateReport();
        }

        if ($.fn.DataTable.isDataTable('#internal-iframe-table') && activeTab == '#internal_iframe_tab')
        {
            //update the hidden fields/variables
            $('#internal_iframe_tab_snap_shot').val(snapShot);
            $('#internal_iframe_tab_start_date').val(startDate);
            $('#internal_iframe_end_date').val(endDate);

            console.log('awts');

            internal_iframe_table.order([[ 0, "asc" ]]);
            internal_iframe_table.ajax.reload();
        }

        if($.fn.DataTable.isDataTable('#both-table') && activeTab == '#both')
        {
            both_table.order([[ 0, "asc" ]]);
            both_table.ajax.reload();
        }
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {

        var currentTarget = $(e.target).attr("href") // activated tab

        var snapShot, startDate, endDate;
        var snapShotPeriodField = $('#snapshot_period');
        var startDateField = $('#start_date');
        var endDateField = $('#end_date');

        switch(currentTarget)
        {
            case '#handp_tab':

                snapShot = $('#handp_tab_snap_shot').val();
                startDate = $('#hand_tab_start_date').val();
                endDate = $('#handp_tab_end_date').val();

                break;

            case '#internal_tab':

                snapShot = $('#internal_tab_snap_shot').val();
                startDate = $('#internal_tab_start_date').val();
                endDate = $('#internal_tab_end_date').val();

                break;

            case '#internal_iframe_tab':

                snapShot = $('#internal_iframe_tab_snap_shot').val();
                startDate = $('#internal_iframe_tab_start_date').val();
                endDate = $('#internal_iframe_tab_end_date').val();

                break;
        }

        //update the form fields/variables
        snapShotPeriodField.val(snapShot);
        startDateField.val(startDate);
        endDateField.val(endDate);

        activeTab = currentTarget;
    });

    $('a[data-toggle="tab"]').on('hide.bs.tab', function (e) {

        var currentTarget = $(e.target).attr("href"); //activated tab
        console.log(currentTarget);

        var snapShot, startDate, endDate;
        var snapShotPeriodField = $('#snapshot_period');
        var startDateField = $('#start_date');
        var endDateField = $('#end_date');

        snapShot = snapShotPeriodField.val();
        startDate = startDateField.val();
        endDate = endDateField.val();

        switch(currentTarget)
        {
            case '#handp_tab':

                $('#handp_tab_snap_shot').val(snapShot);
                $('#hand_tab_start_date').val(startDate);
                $('#handp_tab_end_date').val(endDate);

                break;

            case '#internal_tab':

                $('#internal_tab_snap_shot').val(snapShot);
                $('#internal_tab_start_date').val(startDate);
                $('#internal_tab_end_date').val(endDate);

                break;

            case '#internal_iframe_tab':

                $('#internal_iframe_tab_snap_shot').val(snapShot);
                $('#internal_iframe_tab_start_date').val(startDate);
                $('#internal_iframe_tab_end_date').val(endDate);

                break;
        }
    });
});