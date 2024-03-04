/**
 * Created by magbanua-ariel on 12/02/2016.
 */
$(document).ready(function()
{
    var surveyTakersURL = $('#baseUrl').html() + '/surveyTakers';
    var surveyTakersTable = $('#survey-takers-table').DataTable({
        'processing': true,
        'serverSide': true,
        "columns": [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
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
        'ajax':{
            url:surveyTakersURL, // json datasource
            type: 'post',
            'data': function(d)
            {
                d.id = $('#id').val();
                d.email = $('#email').val();
                d.gender = $('#gender').val();
                d.zip = $('#zip').val();
                d.affiliate_id = $('#affiliate_id').val();
                d.first_name = $('#first_name').val();
                d.city = $('#city').val();
                d.source_url = $('#source_url').val();
                d.revenue_tracker = $('#revenue_tracker').val();
                d.last_name = $('#first_name').val();
                d.state = $('#state').val();
                d.phone = $('#phone').val();
                d.ip = $('#ip').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.s1 = $('#s1').val();
                d.s2 = $('#s2').val();
                d.s3 = $('#s3').val();
                d.s4 = $('#s4').val();
                d.s5 = $('#s5').val();
            },
            "dataSrc": function ( json ) {
                $('#downloadSurveyTakers').removeAttr('disabled');
                return json.data;
            },
        },
        //lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
        // "sDom": 'lf<"addToolbar">rtip',
        // language: {
        //     search: "_INPUT_",
        //     searchPlaceholder: "Search"
        // },
        "searching": false,
        "order": [[ 0, "desc" ]],
        "deferLoading": 0,
    });


    // var table_colums = {
    //     // id : 'ID', 
    //     affiliate_id : 'Affiliate ID', 
    //     revenue_tracker_id : 'Rev. Tracker ID', 
    //     email : 'Email',
    //     name : 'Name',   
    //     gender : 'Gender',
    //     city : 'City',
    //     state : 'State',
    //     zip : 'Zip',
    //     source_url : 'Source URL',
    //     created_at : 'Created At'
    // }, col_options = '';

    // $.each(table_colums, function( index, value ) {
    //     col_options += '<option value="'+index+'">'+value+'</option>';
    // });

    // $("div.addToolbar").html('<label> Search: <select id="filter" name="filter" class="form-control input-sm">'+col_options+'</select></label>');

    $(document).on('click','#getSurveyTakers', function(e)
    {
        e.preventDefault();
        $('#downloadSurveyTakers').attr('disabled','true');
        surveyTakersTable.ajax.reload();
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('#clear').click(function()
    {
        var form = $('#surveyTakers-form');

        form.find('input:text, select').val('');
    });
});

$(document).on('click','.more-details', function()
{
    var id = $(this).data('id'),
        json = $('#st-'+id+'-md').val(),
        details = $.parseJSON(json),
        ifMobile = 'false',
        ifSent = 'Sent';

    if(details.is_mobile == 1) ifMobile = 'true';
    if(details.status == 0) ifSent = 'Pending';
    else if(details.status == 2) ifSent = 'Processing';

    $('#id-value').text(details.id);
    $('#affiliate-value').text(details.affiliate_id);
    $('#rev_tracker-value').text(details.revenue_tracker_id);
    $('#fname-value').text(details.first_name);
    $('#lname-value').text(details.last_name);
    $('#email-value').text(details.email);
    $('#zip-value').text(details.zip);
    $('#city-value').text(details.city);
    $('#state-value').text(details.state);
    $('#birthdate-value').text(details.birthdate);
    $('#gender-value').text(details.gender);  
    $('#address1-value').text(details.address1);
    $('#address2-value').text(details.address2);
    $('#ethnicity-value').text(details.ethnic_group);
    $('#phone-value').text(details.phone);
    $('#ip-value').text(details.ip);
    $('#mobile-value').text(ifMobile);
    $('#source_url-value').text(details.source_url);
    $('#status-value').text(ifSent);
    $('#response-value').text(details.response);
    $('#created_at-value').text(details.created_at);
    $('#updated_at-value').text(details.updated_at);
    $('#more-details-modal').modal('show');
});

$('#more-details-modal').on('hide.bs.modal', function (event) {
    $('.md-deets').html('');
});