var baseURL = 'https://leadreactor.ourcutebaby.com';

function getTotalRevenueStatistics()
{
    // the_url = baseURL+'/admin/getTotalRevenueStatistics';
    var the_url = baseURL+'/get_total_revenue_statistics_by_date';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            getTotalRevenueStatisticsChart(data);
        }
    });
}

function getAffiliateRevenueStatistics()
{

    // the_url = baseURL+'/admin/getRevenueStatisticsByAffiliate';
    var the_url = baseURL+'/get_affiliate_revenues_by_date';

    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            getAffiliateRevenueStatisticsChart(data);
        }
    });
}

function getAffiliateTakersStatistics()
{
    // the_url = baseURL+'/admin/getTotalSurveyTakersPerAffiliate';
    var the_url = baseURL+'/get_affiliate_survey_takers_by_date';

    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            getAffiliateSurveyTakersStatisticsChart(data);
        }
    });
}

function getTopCampaignsByLeads()
{

    var date = $('#date_top_campaigns_by_leads').val();
    var the_url = baseURL+'/admin/getTopCampaignsByLeads';

    $.ajax({

        type: 'POST',
        url: the_url,
        data: {
            'date'  : date
        },

        success: function(data)
        {

            if(date == '') {
                var displayDate = $('#dateYesterday').html();
            }
            else
            {
                var displayDate = date;
            }

            $('#topCampaignsByLeadsDate').html(displayDate);
            $('#topCampaignsByLeadsBarChart').html('');

            // console.log(data);
            Morris.Bar({
                element: 'topCampaignsByLeadsBarChart',
                data: data,
                xkey: 'lead',
                ykeys: [1,2],
                labels: ['Success', 'Rejected'],
                resize: true,
                // xLabelAngle: 40,
                xLabelMargin: 10
            });
        }
    });
}



/**
 * Created by magbanua-ariel on 02/02/2016.
 */
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    // var baseURL = $('#baseUrl').val();

    $('#getTopCampaignsByDateSubmit').click(function() {
        getTopCampaignsByLeads();
    });

    $('#refreshTotalRevenueStatisticsChart').click(function() {
        console.log('Refresh');
        $('#total_revenue_from_date').val('');
        $('#total_revenue_to_date').val('');
        getTotalRevenueStatistics();
    });

    $('#refreshRevenueByAffStatisticsChart').click(function() {
        console.log('Refresh');
        $('#affiliate_revenues_from_date').val('');
        $('#affiliate_revenues_to_date').val('');
        getAffiliateRevenueStatistics();
    });

    $('#refreshSurveyTakersByAffStatisticsChart').click(function() {
        console.log('Refresh');
        $('#affiliate_takers_from_date').val('');
        $('#affiliate_takers_to_date').val('');
        getAffiliateTakersStatistics();
    });

    getTotalRevenueStatistics();

    getTopCampaignsByLeads();

    getAffiliateRevenueStatistics();

    getAffiliateTakersStatistics();

    var the_url = baseURL+'/advertiser/activeCampaigns';

    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#dashboard-campaigns tbody');

            //loop through all top 10 campaigns
            jQuery.each(data, function(i, val) {
                // console.log(val)
                var row = '<tr>'+'<td>'+val.id+'</td>'+'<td>'+val.name+'</td>'+'<td>'+val.cap_type+'</td>'+'<td>'+val.cap_value+'</td>'+'</tr>';
                tbody.append(row);
            });
        }
    });

    the_url = baseURL+'/advertiser/topTenCampaignsByRevenueYesterday';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#top-ten-revenue-yesterday-campaign tbody');

            //loop through all top 10 campaigns
            jQuery.each(data, function(i, val) {
                // console.log(val)
                var row = '<tr>'+'<td>'+val.campaign+'</td>'+'<td>'+val.cost+'</td>'+'<td>'+val.revenue+'</td>'+'<td>'+val.profit+'</td>'+'</tr>';
                tbody.append(row);
            });
        }
    });

    the_url = baseURL+'/advertiser/topTenCampaignsByRevenueForCurrentWeek';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#top-ten-revenue-week-campaign tbody');

            //loop through all top 10 campaigns
            jQuery.each(data, function(i, val) {
                // console.log(val)
                var row = '<tr>'+'<td>'+val.campaign+'</td>'+'<td>'+val.cost+'</td>'+'<td>'+val.revenue+'</td>'+'<td>'+val.profit+'</td>'+'</tr>';
                tbody.append(row);
            });
        }
    });

    the_url = baseURL+'/advertiser/topTenCampaignsByRevenueForCurrentMonth';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#top-ten-revenue-month-campaign tbody');

            //loop through all top 10 campaigns
            jQuery.each(data, function(i, val) {
                // console.log(val)
                var row = '<tr>'+'<td>'+val.campaign+'</td>'+'<td>'+val.cost+'</td>'+'<td>'+val.revenue+'</td>'+'<td>'+val.profit+'</td>'+'</tr>';
                tbody.append(row);
            });
        }
    });

    the_url = baseURL+'/advertiser/leadCounts';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            //loop through all top 10 campaigns
            jQuery.each(data, function(i, val) {
                // console.log(val)
                $(val.lead_type).html(val.lead_count);
            });

        }
    });
});
