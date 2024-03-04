var baseURL = $('#baseUrl').val();

function getDashboardGraphStatistics()
{
    // the_url = baseURL+'/admin/getTotalRevenueStatistics';
    var the_url = baseURL+'/get_dashboard_graphs_by_date';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            getDashboardGraphsStatisticsChart(data);
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

            if(data.length != 0) {
                // console.log(data);
                Morris.Bar({
                    element: 'topCampaignsByLeadsBarChart',
                    data: data,
                    xkey: 'lead',
                    ykeys: [1,2],
                    labels: ['Success', 'Rejected'],
                    resize: true,
                    // xLabelAngle: 5,
                    xLabelMargin: 2
                });
            }
        }
    });
}


function getMostChangeCampaigns() {
    the_url = baseURL+'/admin/getMostChangeCampaigns';

    var table = $('#top-ten-campaigns-most-changes'),
        body = $('#top-ten-campaigns-most-changes tbody');
    table.DataTable().destroy();
    body.empty();
    $.ajax({
        type: 'POST',
        url: the_url,
        data: {
            predefined_date_range: $('#predefined_date_range').val(),
            term : $('#term').val(),
            affiliates: $('#affiliate_ids_most_changes').val()
        },
        success: function(data){
            $.each(data, function(x, stat){
                console.log(stat)
                var ratio = parseFloat(stat.to.ratio).toFixed(2),
                    change_factor = parseFloat(stat.change_factor).toFixed(2),
                    net = parseFloat(stat.net).toFixed(2);
                var row = '<tr><td>'+stat.name+'</td><td>'+ratio+'</td><td>'+change_factor+' '+stat.indicator+'</td><td>'+net+'</td></tr>';
                body.append(row);
            });
            table.DataTable().draw();
        }
    });
}

$(document).on('click','#update-top-ten-most-changes-report',function(e) 
{
    e.preventDefault();
    console.log('update');
    getMostChangeCampaigns();
});

$(document).on('click','.close_me_popover',function(e) 
{
    e.preventDefault();
    console.log('CLOSE');
    $(this).parents('.popover').popover('hide');
});

/**
 * Created by magbanua-ariel on 02/02/2016.
 */
$(document).ready(function()
{
    var offer_goes_down_table = $('#offer-goes-down').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/offerGoesDownStats',
            type: 'post',
            'data': function(d)
            {
                d.filter = $('#ogd_filter').val();
            },
            error: function(error){  // error handling
                console.log(error);
            }
        },
        'ordering': false,
        'drawCallback': function(settings)
        {
            $('[data-toggle="popover"]').popover({
                placement: 'bottom',
                html: 'true',
                title : '<span style="color:#f7f7f7"><strong>title</strong></span>'+
                        '<button type="button" class="close close_me_popover">&times;</button>',
                // content : 'test'
            });
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]],
        "sDom": 'l<"offer-goes-down_Toolbar">frtip'
    });
    $("div.offer-goes-down_Toolbar").html('<label>Filter Revenue: </label><input type="number" name="ogd_filter" id="ogd_filter" class="form-control" value="30">');

    $(document).on('change', '#ogd_filter', function(){
        console.log('Refresh');
        offer_goes_down_table.clear();
        offer_goes_down_table.ajax.reload();
    });

    $('#allInbox-table').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/campaignRevenueBreakdown/' + $('#allInboxCampaignID').val(),
            type: 'post',
            error: function(error){  // error handling
                console.log(error);
            }
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]]
    });

    $('#pushPros-table').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/campaignRevenueBreakdown/' + $('#pushProCampaignID').val(),
            type: 'post',
            error: function(error){  // error handling
                console.log(error);
            }
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]]
    });

    $('#path6-table').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/pathSpeed/path6',
            type: 'post',
            error: function(error){  // error handling
                console.log(error);
            }
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]]
    });

    $('#path18-table').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/pathSpeed/path18',
            type: 'post',
            error: function(error){  // error handling
                console.log(error);
            }
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]]
    });

    $('#path19-table').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/pathSpeed/path19',
            type: 'post',
            error: function(error){  // error handling
                console.log(error);
            }
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]]
    });

    $('#path17-table').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, "desc" ]],
        'searching': false,
        'ajax':{
            url: $('#baseUrl').val()+'/dashboard/pathSpeed/path17',
            type: 'post',
            error: function(error){  // error handling
                console.log(error);
            }
        },
        lengthMenu: [[20, 50,100,250],[20, 50,100,250]]
    });

    $('#top-ten-campaigns-most-changes').dataTable();
    getMostChangeCampaigns();

    $('.active_tooltip[data-toggle="tooltip"]').tooltip();

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true,
        endDate: '-0d'
    });

    $('#getStatisticsByDateModal').on('hidden.bs.modal', function () {
        console.log('Close');
        $('#from_date').val('');
        $('#to_date').val('');
    });

    // var baseURL = $('#baseUrl').val();

    $('#getTopCampaignsByDateSubmit').click(function() {
        getTopCampaignsByLeads();
    });

    $('.refreshGraphs').click(function() {
        console.log('Refresh');
        $('#from_date').val('');
        $('#to_date').val('');
        // getDashboardGraphStatistics();
        $('#getStatisticsByDateForm').trigger('submit');
    });

    // getDashboardGraphStatistics();
    getTopCampaignsByLeads();

    // var the_url = baseURL+'/admin/activeCampaigns';
    // $.ajax({
    //     type: 'POST',
    //     url: the_url,
    //     success: function(data){
    //         var tbody = $('#dashboard-campaigns tbody');
    //
    //         //loop through all top 10 campaigns
    //         jQuery.each(data, function(i, val) {
    //             var row = '<tr>'+'<td>'+val.id+'</td>'+'<td>'+val.name+'</td>'+'<td>'+val.cap_type+'</td>'+'<td>'+val.cap_value+'</td>'+'</tr>';
    //             tbody.append(row);
    //         });
    //     }
    // });

    the_url = baseURL+'/admin/topTenCampaignsByRevenueYesterday';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            var tbody = $('#top-ten-revenue-yesterday-campaign tbody');
            
            if(data.length == 0) {
                tbody.find('em').html('No data found.');
            }else {
                tbody.empty();
                jQuery.each(data, function(i, val) {
                    // console.log(val)
                    var row = '<tr>'+'<td>'+val.campaign+'</td><td>'+Number(val.revenue).toFixed(2)+'</td></tr>';
                    tbody.append(row);
                });
            }
        }
    });

    the_url = baseURL+'/admin/topTenCampaignsByRevenueForCurrentWeek';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#top-ten-revenue-week-campaign tbody');
            if(data.length == 0) {
                tbody.find('em').html('No data found.');
            }else {
                tbody.empty();
                //loop through all top 10 campaigns
                jQuery.each(data, function(i, val) {
                    // console.log(val)
                    var row = '<tr>'+'<td>'+val.campaign+'</td><td>'+Number(val.revenue).toFixed(2)+'</td></tr>';
                    tbody.append(row);
                });
            }
        }
    });

    the_url = baseURL+'/admin/topTenCampaignsByRevenueForCurrentMonth';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            var tbody = $('#top-ten-revenue-month-campaign tbody');
            if(data.length == 0) {
                tbody.find('em').html('No data found.');
            }else {
                tbody.empty();
                //loop through all top 10 campaigns
                jQuery.each(data, function(i, val) {
                    // console.log(val)
                    var row = '<tr>'+'<td>'+val.campaign+'</td><td>'+Number(val.revenue).toFixed(2)+'</td></tr>';
                    tbody.append(row);
                });
            }
        }
    });

    the_url = baseURL+'/admin/topTenAffiliatesByRevenueYesterday';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){
            var tbody = $('#top-ten-revenue-yesterday-affiliate tbody');

            if(data.length == 0) {
                tbody.find('em').html('No data found.');
            }else {
                tbody.empty();
                //loop through all top 10 campaigns
                jQuery.each(data, function(i, val) {
                    // console.log(val)
                    var row = '<tr>'+'<td>'+val.affiliate+'<td><td>'+Number(val.revenue).toFixed(2)+'</td></tr>';
                    tbody.append(row);
                });
            }
        }
    });

    the_url = baseURL+'/admin/topTenAffiliatesByRevenueForCurrentWeek';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#top-ten-revenue-week-affiliate tbody');
            if(data.length == 0) {
                tbody.find('em').html('No data found.');
            }else {
                tbody.empty();
                //loop through all top 10 campaigns
                jQuery.each(data, function(i, val) {
                    // console.log(val)
                    var row = '<tr>'+'<td>'+val.affiliate+'<td><td>'+Number(val.revenue).toFixed(2)+'</td></tr>';
                    tbody.append(row);
                });
            }
        }
    });

    the_url = baseURL+'/admin/topTenAffiliatesByRevenueForCurrentMonth';
    $.ajax({
        type: 'POST',
        url: the_url,
        success: function(data){

            var tbody = $('#top-ten-revenue-month-affiliate tbody');

            if(data.length == 0) {
                tbody.find('em').html('No data found.');
            }else {
                tbody.empty();
                //loop through all top 10 campaigns
                jQuery.each(data, function(i, val) {
                    // console.log(val)
                    var row = '<tr>'+'<td>'+val.affiliate+'<td><td>'+Number(val.revenue).toFixed(2)+'</td></tr>';
                    tbody.append(row);
                });
            }
        }
    });

    the_url = baseURL+'/admin/leadCounts';
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

    // $('#getStatisticsByDateForm').trigger('submit');

    $('#affiliate_ids_most_changes').select2({
        //tags: true,
        placeholder: 'Select the id or name of the affiliate.',
        minimumInputLength: 1,
        theme: 'bootstrap',
        language: {
            inputTooShort: function(args) {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            }
        },
        ajax: {
            url: $('#baseUrl').html()+'/search/select/activeAffiliatesIDName',
            dataType: "json",
            type: "POST",
            'data': function (params) {

                var queryParameters = {
                    term: params.term
                };

                return queryParameters;
            },
            processResults: function (data) {
                // console.log(data);
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

    // //top-ten-campaigns-most-changes
    // var topTenCampaignsMostChangesDataTable = $('#top-ten-campaigns-most-changes').DataTable({
    //     'processing': true,
    //     'serverSide': true,
    //     'searching': false,
    //     'order': [[ 0, "asc" ]], //disable the initial ordering
    //     'ajax': {
    //         url: $('#baseUrl').html()+'/admin/campaignRevenueViewChangeServerSide',
    //         type: 'POST',  // method  , by default get
    //         'data': function(d)
    //         {
    //             d.predefined_date_range = $('#predefined_date_range').val();
    //             d.term = $('#term').val();
    //             d.affiliate_ids_most_changes = $('#affiliate_ids_most_changes').val();
    //         },
    //         error: function(error)
    //         {
    //             // error handling
    //             console.log(error);
    //         }
    //     },
    //     'columns':[
    //         {
    //             'data': 'campaign',
    //             'width': '60%'
    //         },
    //         {'data': 'revenue_view'},
    //         {'data': 'change'},
    //         {'data': 'net_percentage'}
    //     ],
    // });

    // $('#update-top-ten-most-changes-report').click(function()
    // {
    //     console.log('Update Report Click');
    //     topTenCampaignsMostChangesDataTable.ajax.reload();
    // });

    /* NOTES */
    var nCatBtn = $('button[data-target="#noteCategoryCollapse"]'),
        nCatOrigHtml = nCatBtn.html(),
        exIcon = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>'
        nCatForm = $('#notes_category_form'),
        nCatAddUrl = $('#baseUrl').html() + '/add_notes_category',
        nCatEditUrl = $('#baseUrl').html() + '/edit_notes_category',
        nCatList = $('#notesCategoryList'),
        nNotesList = $('#notesList'),
        theNotes = [],
        theNoteIds = [],
        loadingLink = '<a href="#" class="list-group-item text-center"><small><em>Loading...</em></small></a>',
        nNoteForm = $('#notes_form'),
        nNoteAddUrl = $('#baseUrl').html() + '/add_note',
        nNoteEditUrl = $('#baseUrl').html() + '/edit_note',
        notesQuill = document.getElementsByClassName('ql-editor'),
        noNotesStr = '<a href="#" class="list-group-item text-center isEmpty"><small><em>No Notes Found...</em></small></a>';
    
    getNotesCategory();

    // noteTracking();

    var toolbarOptions = [
      ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
      ['blockquote'],

      // [{ 'header': 1 }, { 'header': 2 }],               // custom button values
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
      [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
      [{ 'direction': 'rtl' }],                         // text direction

      // [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

      [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
      // [{ 'font': [] }],
      [{ 'align': [] }],

      ['clean']                                         // remove formatting button
    ];

    var quill = new Quill('#noteEditorQuill', {
        modules: {
        toolbar: toolbarOptions
      },
      theme: 'snow'
    });

    quill.on('text-change', function(delta, source) {
      nNoteForm.find('[name="content"]').html(quill.container.firstChild.innerHTML)
    });

    $('#noteCategoryCollapse').on('hidden.bs.collapse', function () {
        nCatBtn.html(nCatOrigHtml);
        $(nCatForm).attr('action', nCatAddUrl);
        $(nCatForm).attr('data-process', 'add_notes_category').data('process', 'add_notes_category');
        $(nCatForm).find('[name="name"]').val('');
        $(nCatForm).find('[name="id"]').val('');
    });

    $('#noteCategoryCollapse').on('show.bs.collapse', function () {
        nCatBtn.html(exIcon);
        $(nCatForm).find('.error').removeClass('error error_label error_field')
    });

    function noteTracking() {
        $.ajax({
            type: 'POST',
            url: baseURL+'/notes_tracking',
            success: function(data){
                // console.log(data);
                $('#notesCategoryList > a span').html('');
                $.each(data, function(x, tracking){
                    $('#notesCategoryList > a[data-id="'+tracking.category_id+'"] span').html(tracking.count);
                });
            }
        });
    }

    function getNotesCategory() {
        // $('#notes_category-table').dataTable();
        if ( ! $.fn.DataTable.isDataTable( '#notes_category-table' ) ) {
            the_url = baseURL+'/notes_category';

            var table = $('#notes_category-table'),
                body = $('#notes_category-table tbody');
            $.ajax({
                type: 'POST',
                url: the_url,
                success: function(data){
                    body.empty();
                    nCatList.empty();
                    $.each(data.categories, function(x, cat){
                        var editBtn = '<button data-id="'+cat.id+'" class="editNoteCategoryBtn btn btn-primary btn-xs" type="button" style="margin-right:5px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>';
                        var deleteBtn = '<button data-id="'+cat.id+'" class="deleteNoteCategoryBtn btn btn-danger btn-xs" type="button"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
                        var row = '<tr><td>'+cat.id+'</td><td>'+cat.name+'</td><td>'+editBtn+deleteBtn+'</td></tr>';
                        body.append(row);
                        
                        nCatList.append('<a href="#" data-id="'+cat.id+'" class="list-group-item">'+cat.name+' <span class="badge"></span></a>');
                    });

                    table.DataTable().draw();

                    $('#notesCategoryList > a span').html('');
                    $.each(data.tracking, function(x, tracking){
                        $('#notesCategoryList > a[data-id="'+tracking.category_id+'"] span').html(tracking.count);
                    });
                }
            });
        }
    }

    function getCategoryNotes(id) {
        $('#notesCategoryList a').removeClass('active');
        $('#notesCategoryList a[data-id="'+id+'"]').addClass('active');

        $('.nCat-notes-div').show();
        $('.nCat-note-div').hide();
        nNotesList.html(loadingLink);

        // console.log(id);

        $.ajax({
            type: 'POST',
            url: baseURL+'/get_notes_by_category/' + id,
            success: function(data){
                // console.log(data);
                theNoteIds.push(id);
                // theNotes[id] = data;
                nNotesList.empty();
                var n = [];
                var newCount = 0
                if(data.length == 0) {
                    nNotesList.append(noNotesStr);
                }else {
                    
                    $.each(data, function(x, note){
                        n[note.id] = note;
                        var ifNewClass = note.ifNew === null ? 'new_note' : '';
                        if(note.ifNew === null) newCount++;
                        // console.log(note.ifNew);
                        // console.log(ifNewClass)
                        nNotesList.append('<a href="#" data-id="'+note.id+'" data-cat="'+id+'" class="list-group-item '+ifNewClass+'">'+note.subject+'</a>');
                    });
                }

                theNotes[id] = n;

                var newCountStr = newCount == 0 ? '' : newCount;
                $('#notesCategoryList > a[data-id="'+id+'"] span').html(newCountStr);

                // console.log(theNoteIds);
                // console.log(theNotes);
            }
        });
    }

    $(document).on('click','.editNoteCategoryBtn',function() 
    {
        var id = $(this).data('id'),
            btn = $(this),
            row = btn.closest('tr'),
            name = $(this).closest('tr').find('td:nth-child(2)').html();
            // console.log(name)
        $(nCatForm).attr('action', nCatEditUrl);
        $(nCatForm).attr('data-process', 'edit_notes_category').data('process', 'edit_notes_category');
        $(nCatForm).find('[name="id"]').val(id);
        $(nCatForm).find('[name="name"]').val(name);
        $('#noteCategoryCollapse').collapse('show');

    });

    $(document).on('click','.deleteNoteCategoryBtn',function()
    {
        var this_category = $(this);
        var id = $(this).data('id');

        var confirmation = confirm('Are you sure you want to delete this category?');

        if(confirmation === true) {
            var the_url = $('#baseUrl').html() + '/delete_notes_category';
            $.ajax({
                type : 'POST',
                url  : the_url,
                data : {
                    'id' : id
                },
                success : function(data) {
                    var table = $('#notes_category-table').DataTable();
                    table.row(this_category.parents('tr')).remove().draw();

                    $('#notesCategoryList a[data-id="'+id+'"]').remove()
                }
            });
        }
    });

    $(document).on('click','#notesCategoryList > a',function(e) 
    {
        e.preventDefault();

        var id = $(this).data('id');

        if(typeof id == 'undefined') return;
        if($(this).hasClass('active')) return;

        getCategoryNotes(id);

        // if(theNoteIds.includes(id) && false) {
        //     //Exists
        //     console.log('Exists');
        //     console.log(theNoteIds);
        //     console.log(theNotes);

        //     nNotesList.empty();
        //     var notes = theNotes[id];
        //     console.log(notes);
        //     if(notes.length == 0) {
        //         nNotesList.append('<a href="#" class="list-group-item text-center isEmpty"><small><em>No Notes Found...</em></small></a>');
        //     }else {
        //         $.each(notes, function(x, note){
        //             console.log(x);
        //             console.log(note);
        //             nNotesList.append('<a href="#" data-id="'+note.id+'" class="list-group-item">'+note.subject+'</a>');
        //         });
        //     }
                

        // }else {
        //     console.log('Get');
            
        // }

            

    });

    //Edit Note
    $(document).on('click','#notesList > a',function(e) 
    {
        e.preventDefault();

        var id = $(this).data('id'),
            category = $(this).data('cat');

        if(typeof id == 'undefined') return;
        if($(this).hasClass('active')) return;

        $('#notesList a').removeClass('active');
        $(this).addClass('active');

        $('.nCat-note-div').show();

        $(nNoteForm).attr('action', nNoteEditUrl);
        $(nNoteForm).attr('data-process', 'edit_note').data('process', 'edit_note');

        var note = theNotes[category][id];
        notesQuill[0].innerHTML = note.content

        nNoteForm.find('[name="subject"]').val(note.subject)
        nNoteForm.find('[name="category_id"]').val(note.category_id)
        nNoteForm.find('[name="id"]').val(note.id)

        if($(this).hasClass('new_note')) {
            $(this).removeClass('new_note')
            theNotes[category][id]['ifNew'] = 1;

            var newCount = Number($('#notesCategoryList a[data-id="'+category+'"] span').html());
                newCountStr = (newCount - 1) == 0 ? ''  : newCount - 1;
            $('#notesCategoryList a[data-id="'+category+'"] span').html(newCountStr);

            $.ajax({
                type: 'POST',
                url: baseURL+'/notes/'+id+'/viewed',
            });
        }

        $('.noteInfo').show();
        nNoteForm.find('#updated_at').html(note.updated_at);
    });


    //Add Note
    $(document).on('click','#addNoteBtn',function(e) 
    {
        e.preventDefault();

        var category = $('#notesCategoryList a.active').data('id');

        $('#notesList a').removeClass('active');

        $(nNoteForm).attr('action', nNoteAddUrl);
        $(nNoteForm).attr('data-process', 'add_note').data('process', 'add_note');

        nNoteForm.find('[name="subject"]').val('')
        nNoteForm.find('[name="category_id"]').val(category)
        nNoteForm.find('[name="id"]').val('')
        notesQuill[0].innerHTML = ''

        $('.nCat-note-div').show();
        $('.noteInfo').hide();
    });

    $(document).on('click','#deleteNote',function(e) 
    {
        e.preventDefault();

        var category = $('#notesCategoryList a.active').data('id')
        var id = $('#notesList a.active').data('id')

        var confirmation = confirm('Are you sure you want to delete this note?');

        if(confirmation === true) {
            var the_url = $('#baseUrl').html() + '/delete_note';
            $.ajax({
                type : 'POST',
                url  : the_url,
                data : {
                    'id' : id
                },
                success : function(data) {
                    getCategoryNotes(category);
                    // if($('#notesList a[data-id="'+id+'"]').hasClass('new_note')) {
                    //     theNotes[category][id]['ifNew'] = 1;

                    //     var newCount = Number($('#notesCategoryList a[data-id="'+category+'"] span').html());
                    //         newCountStr = (newCount - 1) == 0 ? ''  : newCount - 1;
                    //     $('#notesCategoryList a[data-id="'+category+'"] span').html(newCountStr);
                    // }
                    // $('#notesList a[data-id="'+id+'"]').remove();
                }
            });
        }
    });

    

});

$(document).on('click','.downloadAffiliateRevenueReport',function() 
{
    console.log('REVENUE');
    $('#report_type').val('revenue');
    var from_date, to_date;
    if(typeof $(this).data('date') == 'undefined') {
        if($('#currentFromDate').html() == '') {
            from_date = $('#date7DaysAgo').html();
        }else from_date = $('#currentFromDate').html();
        if($('#currentToDate').html() == '') {
            to_date = $('#dateYesterday').html();
        }else to_date = $('#currentToDate').html();
    }else {
        from_date = $(this).data('date');
        to_date = $(this).data('date');
    }
    $('#report_from_date').val(from_date);
    $('#report_to_date').val(to_date);

    // console.log(from_date);
    // console.log(to_date);
    form = $('#downloadReportForm');
    form.submit();

});

$(document).on('click','.downloadAffiliateRegistrationsReport',function() 
{
    console.log('REGISTRATIONS');
    $('#report_type').val('registration');
    var from_date, to_date;
    if(typeof $(this).data('date') == 'undefined') {
        from_date = $('#currentFromDate').html();
        to_date = $('#currentToDate').html();
    }else {
        from_date = $(this).data('date');
        to_date = $(this).data('date');
    }
    $('#report_from_date').val(from_date);
    $('#report_to_date').val(to_date);

    form = $('#downloadReportForm');
    form.submit();
});

function getTotalRevStatsChart(data) {
    console.log(data);
    var daily_total_revenue_data = data.daily_total_revenue;
    /* DAILY TOTAL REVENUE */
    $('#totalRevenueStatisticsChart').html('');
    var revenue_chart =  Morris.Line({
      // ID of the element in which to draw the chart.
      element: 'totalRevenueStatisticsChart',
      // Chart data records -- each entry in this array corresponds to a point on
      // the chart.
      data: daily_total_revenue_data,
      // The name of the data record attribute that contains x-values.
      xkey: 'date',
      // A list of names of data record attributes that contain y-values.
      ykeys: ['received'],
      // Labels for the ykeys -- will be displayed when you hover over the
      // chart.
      labels: ['Received'],
      parseTime:false,
      preUnits: '$',
      hoverCallback: function(index, options, content) {
        // console.log(index);
        return(content + ' Total Success Leads: ' + daily_total_revenue_data[index].total);
        },
      resize: true,
      hideHover: true
    });
}

function getAffRevStatsChart(data) {
    console.log(data);
    var daily_affiliate_revenue_data = data.daily_revenue_affiliate;

    /* DAILY AFFILIATE REVENUE */
    $('#receivedStatisticsByAffiliateChart').html('');
    var revenue_chart =  Morris.Line({
      // ID of the element in which to draw the chart.
      element: 'receivedStatisticsByAffiliateChart',
      // Chart data records -- each entry in this array corresponds to a point on
      // the chart.
      data: daily_affiliate_revenue_data.data,
      // The name of the data record attribute that contains x-values.
      xkey: 'date',
      // A list of names of data record attributes that contain y-values.
      ykeys: daily_affiliate_revenue_data.ykeys,
      // Labels for the ykeys -- will be displayed when you hover over the
      // lineColors: daily_affiliate_revenue_data.colors,
      // chart.
      labels: daily_affiliate_revenue_data.labels,
      parseTime:false,
      resize: true,
      preUnits: '$',
      hideHover: true,
      hoverCallback: function(index, options, content) {
        var data_set = daily_affiliate_revenue_data.data[index];
        var the_date = '<div class="morris-hover-row-label">'+data_set['date']+'</div>';
        var total = '<div class="morris-hover-point" style="color: #0b62a4">Total: $'+data_set['total_revenue']+'</div>';
        var button = '<button data-date="'+data_set['created_at']+'" class="downloadAffiliateRevenueReport btn btn-primary btn-xs"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>'
        return(the_date + total + button);
      }
    });
}

function getAffSurveyersStatsChart(data) {
    console.log(data);

    var daily_affiliate_registrations_data = data.daily_affilate_registrations;

    /* DAILY TOTAL AFFILIATE REGISTRATIONS */
    $('#totalSurveyTakersPerAffiliateChart').html('');
        var revenue_chart =  Morris.Line({
        // ID of the element in which to draw the chart.
        element: 'totalSurveyTakersPerAffiliateChart',
        // Chart data records -- each entry in this array corresponds to a point on
        // the chart.
        data: daily_affiliate_registrations_data.data,
        // The name of the data record attribute that contains x-values.
        xkey: 'date',
        // A list of names of data record attributes that contain y-values.
        ykeys: daily_affiliate_registrations_data.ykeys,
        // Labels for the ykeys -- will be displayed when you hover over the
        // chart.
        labels: daily_affiliate_registrations_data.labels,
        parseTime:false,
        hoverCallback: function(index, options, content) {
            var data_set = daily_affiliate_registrations_data.data[index];
            var the_date = '<div class="morris-hover-row-label">'+data_set['date']+'</div>';
            var total = '<div class="morris-hover-point" style="color: #0b62a4">Total: '+data_set['total_registrations']+'</div>';
            var button = '<button data-date="'+data_set['created_at']+'" class="downloadAffiliateRegistrationsReport btn btn-primary btn-xs"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>'
            return(the_date + total + button);
          // $('#totalSurveyTakersPerAffiliateChart-info').html(content);
          // return(content);
        },
        resize: true,
        hideHover: true
    });
}

$(document).on('submit','#getStatisticsByDateForm',function(e) 
{
    e.preventDefault();
    console.log('SUBMIT');

    var from_date = $('#from_date').val(),
        to_date = $('#to_date').val(),
        form = $('#getStatisticsByDateForm'),
        the_data = form.serialize(),
        ifPassed = true;

    var return_count = 0; 

    $('#from_date').removeClass('error_field error');
    $('#to_date').removeClass('error_field error');

    if(from_date != '' || to_date != '') {
        if(to_date < from_date) {
            ifPassed = false;
            $('#from_date').addClass('error_field error');
            $('#to_date').addClass('error_field error');
        }
    }

    $('#currentFromDate').html(from_date);
    $('#currentToDate').html(to_date);

    if(ifPassed) {
        var the_url = baseURL+'/dashboard_rev_stats';
        $.ajax({
            type: 'POST',
            data: the_data,
            url: the_url,
            success: function(data)
            {
                return_count++
                console.log(return_count);
                getTotalRevStatsChart(data);
                if(return_count >= 3) $('#getStatisticsByDateModal').modal('hide');
            }
        });

        var the_url = baseURL+'/dashboard_affiliate_rev_stats';
        $.ajax({
            type: 'POST',
            data: the_data,
            url: the_url,
            success: function(data)
            {
                return_count++;
                console.log(return_count);
                getAffRevStatsChart(data);
                if(return_count >= 3) $('#getStatisticsByDateModal').modal('hide');
            }
        });

        var the_url = baseURL+'/dashboard_survey_takers';
        $.ajax({
            type: 'POST',
            data: the_data,
            url: the_url,
            success: function(data)
            {
                return_count++;
                console.log(return_count);
                getAffSurveyersStatsChart(data);
                if(return_count >= 3) $('#getStatisticsByDateModal').modal('hide');
            }
        });

    }
});
