$(document).ready(function()
{
	/*
	$('.table-datatable').DataTable({
		responsive: true,
		"order": [[ 0, "desc" ]],
		lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
	});
	*/

    /*
    $('.search-select').select2({
        theme: 'bootstrap',
        dropdownParent: $("#TrkFormModal")
    });
    */
    // Timepicker
    $('#timepicker').timepicker({
        minuteStep: 5,
        showInputs: false,
        disableFocus: false
    });
    $('#timepicker').timepicker().on('show.timepicker', function(e) {
        // Dsiable minutes
        $('a[data-action="incrementMinute"]').css('cursor', 'not-allowed').removeAttr('data-action');
        $('a[data-action="decrementMinute"]').css('cursor', 'not-allowed').removeAttr('data-action');
    });

    var campaign_name = JSON.parse($('#campaign_names').val()),
        campaign_status = JSON.parse($('#campaign_statuses').val()),
        revTrackerCampaignTypeReferenceDates = null;

    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('meta[name="_token"]').attr('content')
        }
    });

    $('.search-revenueTracker-select').select2({
        //tags: true,
        placeholder: 'Select the id or name of the affiliate.',
        minimumInputLength: 1,
        dropdownParent: $("#TrkFormModal"),
        theme: 'bootstrap',
        language: {
            inputTooShort: function(args) {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            }
        },
        ajax: {
            url: $('#baseUrl').html()+'/search/select/activeRevenueTrackers',
            dataType: "json",
            type: "POST",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                };
                return queryParameters;
            },
            processResults: function (data) {
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

    var affiliateSelect = $('.search-affiliate-select').select2({
        //tags: true,
        placeholder: 'Select the id or name of the affiliate.',
        minimumInputLength: 1,
        dropdownParent: $("#TrkFormModal"),
        theme: 'bootstrap',
        language: {
            inputTooShort: function(args) {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            }
        },
        ajax: {
            url: $('#baseUrl').html()+'/search/select/activeAffiliates',
            dataType: "json",
            type: "POST",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                };
                console.log(params);
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

    $('#link').change(function()
	{
		//get the text value
		var inputURL = $(this).val();

		//check if text has http:// or https://
		var hasHTTP = inputURL.indexOf('http://') > -1;
		var hasHTTPs = inputURL.indexOf('https://') > -1;

		if(!hasHTTP && !hasHTTPs)
		{
			inputURL = 'http://'+inputURL;
		}

		$(this).val(inputURL);

	});

	var dataContactsURL = $('#baseUrl').html() + '/revenue_trackers';
	$('#tracker-table').DataTable({
		'processing': true,
		'serverSide': true,
		'columns': [
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
			{ 'orderable': false }
		],
        columnDefs: [
            // { width: '3%', targets: 0 },
            // // { width: '10%', targets: 2 },
            // { width: '15%', targets: [1,3] },
            // { width: '20%', targets: 4 },
            // { width: '7%', targets: 5 },
            { width: '15%', targets: 11 },
        ],
		"order": [[ 4, "asc" ]],
		'ajax':{
			url:dataContactsURL, // json datasource
			type: 'post',  // method
			error: function(){  // error handling

			},
            "dataSrc": function ( json ) {
                $('#downloadRevenuetracker').removeAttr('disabled');
                return json.data;
            },
		},
		//lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
		lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
        "sDom": 'l<"revTrackerToolbar">frtip'
	});

    $("div.revTrackerToolbar").html('<a id="downloadRevenuetracker" href="' + $('#baseUrl').html() + '/downloadRevenueTrackers' +'" class="btn btn-primary" disabled><span class="glyphicon glyphicon-save" aria-hidden="true"></span></button>');

	$('#addTrkBtn').click(function()
	{
		var this_modal = $('#TrkFormModal');
		var url = $('#baseUrl').html() + '/add_revenue_tracker';

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').attr('data-process', 'add_revenue_tracker').data('process','add_revenue_tracker');
		this_modal.find('.this_form').attr('data-confirmation', '').data('confirmation','');
		this_modal.find('.modal-title').html('Add Revenue Tracker');
		this_modal.find('#this_id').val('');

        //clear the affiliate and revenue tracker dropdown
        this_modal.find('#affiliate').val('').trigger('change');
        this_modal.find('#revenue_tracker').val('').trigger('change');

        $('#subid_breakdown').val(1);
        $('#currentSIBSetting-div').show();
        $('#sib_s1').prop('checked', true);
        $('#sib_s2').prop('checked', true);
        $('#sib_s3').prop('checked', true);
        $('#sib_s4').prop('checked', true);

		$('.this_modal_submit').removeAttr('disabled');

		this_modal.modal('show');
	});

	/***
	 * onclick listener for editing affiliate
	 */
	$(document).on('click','.editTracker',function()
	{
		var this_modal = $('#TrkFormModal');
		var id = $(this).data('id');
		var url = $('#baseUrl').html() + '/edit_revenue_tracker';
        var details = $.parseJSON($('#trk-'+id+'-details').val());
        console.log(details);

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','edit_revenue_tracker');
		this_modal.find('.this_form').attr('data-process', 'edit_revenue_tracker');
		this_modal.find('.this_form').data('confirmation','Are you sure you want to edit this revenue tracker?');
		this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to edit this revenue tracker?');
		this_modal.find('.modal-title').html('Edit Revenue Tracker');
		this_modal.find('#this_id').val(id);

		var this_trk = '#trk-'+id+'-';

		$('#website').val(details.website);

        var affiliateID = $(this_trk+'aff').data('affiliate');
        var affiliateText = $(this_trk+'aff').html();
        var affiliateSelect = $('#affiliate');
        //$('#affiliate').val(affiliateID).trigger('change');
        affiliateSelect.empty();
        affiliateSelect.append('<option value="'+affiliateID+'">'+affiliateText+'</option>');
        affiliateSelect.select2('data', {id:affiliateID, text:affiliateText},true);
        affiliateSelect.trigger('change');
        affiliateSelect.prop("disabled", true);
        affiliateSelect.after('<input type="hidden" name="affiliate" value="'+affiliateID+'" id="dummyAffiliateId"/>');

		$('#campaign').val(details.campaign_id);
		$('#offer').val(details.offer_id);

        var revenueTrackerID = $(this_trk+'rti').html();
        var revenueTrackerText = $(this_trk+'rti').data('revenue_tracker_name');
        var revenueTrackerSelect = $('#revenue_tracker');
        revenueTrackerSelect.empty();
        revenueTrackerSelect.append('<option value="'+revenueTrackerID+'">'+revenueTrackerText+'</option>');
        revenueTrackerSelect.select2('data', {id:revenueTrackerID, text:revenueTrackerText},true);
        revenueTrackerSelect.trigger('change');
		//$('#revenue_tracker').val($(this_trk+'rti').html()).trigger('change');

		$('#s1').val(details.s1);
		$('#s2').val(details.s2);
		$('#s3').val(details.s3);
		$('#s4').val(details.s4);
		$('#s5').val(details.s5);
		$('#link').val(details.tracking_link);
		$('#notes').val(details.note);
		$('#type').val(details.path_type);
		$('#crg_limit').val(details.crg_limit);
		$('#ext_limit').val(details.ext_limit);
		$('#lnk_limit').val(details.lnk_limit);
        $('#fire').val(details.fire_at);
        $('#pixel').val(details.pixel);
        $('#order_type').val(details.order_type);
        $('#pixel_header').val(details.pixel_header);
        $('#pixel_body').val(details.pixel_body);
        $('#pixel_footer').val(details.pixel_footer);
        $('#exit_page').val(details.exit_page_id);
        $('#subid_breakdown').val(details.subid_breakdown);
        $('#new_subid_breakdown_status').val(details.new_subid_breakdown_status);

        if(details.subid_breakdown == 0) $('#currentSIBSetting-div').hide();
        else $('#currentSIBSetting-div').show();
        $('#sib_s1').prop('checked', details.sib_s1 == 1);
        $('#sib_s2').prop('checked', details.sib_s2 == 1);
        $('#sib_s3').prop('checked', details.sib_s3 == 1);
        $('#sib_s4').prop('checked', details.sib_s4 == 1);

        if(details.new_subid_breakdown_status == 0 || details.new_subid_breakdown_status == null) $('#newSIBSetting-div').hide();
        else $('#newSIBSetting-div').show();

        $('#nsib_s1').prop('checked', details.nsib_s1 == 1);
        $('#nsib_s2').prop('checked', details.nsib_s2 == 1);
        $('#nsib_s3').prop('checked', details.nsib_s3 == 1);
        $('#nsib_s4').prop('checked', details.nsib_s4 == 1);

		this_modal.modal('show');

	});

    $('#reorder').change(function() {
        console.log('reorder toggle: ' + $(this).prop('checked'));

        if($(this).prop('checked'))
        {
            $(this).val(1);
            $('#mixed_coreg_campaign_order_form').find('#timepicker, input[name="mixed_coreg_recurrence"]').prop( "disabled", false );
        }
        else
        {
            $(this).val(0);
            $('#mixed_coreg_campaign_order_form').find('#timepicker, input[name="mixed_coreg_recurrence"]').prop( "disabled", true );
        }
    });

    $('#mixed_coreg_reorder').change(function() {
        console.log('reorder toggle: ' + $(this).prop('checked'));

        if($(this).prop('checked'))
        {
            $(this).val(1);
            $('#timepicker, input[name="mixed_coreg_recurrence"]').prop( "disabled", false );
            $('[name="mixed_coreg_views"]').prop( "disabled", false );
        }
        else
        {
            $(this).val(0);
            $('#mixed_coreg_campaign_order_form').find('#timepicker, input[name="mixed_coreg_recurrence"]').prop( "disabled", true );
            $('[name="mixed_coreg_views"]').prop( "disabled", true );
        }
    });

	/***
	 * onclick listener for deleting affiliate
	 */
	$(document).on('click','.deleteTracker',function()
	{
		var this_tracker = $(this);
		var id = $(this).data('id');
		var the_url = $('#baseUrl').val() + '/delete_revenue_tracker';
		var confirmation = true;

		confirmation = confirm('Are you sure you want to delete revenue tracker id: ' + $('#trk-'+id+'-rti').html() +'?');

		if(confirmation === true)
		{
			$.ajax({
				type: 'POST',
				data: {
					'id'	:	id
				},
				url: the_url,
				success: function(){
					var table = $('#tracker-table').DataTable();
					table.row(this_tracker.parents('tr')).remove().draw();
				}
			});
		}
	});


	$('#TrkFormModal').on('hide.bs.modal', function (event)
	{
		var form = $(this).find('.this_form');

		$('.this_modal_submit').html('Save');

		form.find('.this_field').each(function()
		{
			$(this).val('');
		});

        form.find('#order_type').val(0);
        form.find('#subid_breakdown').val(0);
        form.find('#new_subid_breakdown_status').val('');
		form.find('#this_id').val('');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

        $('#affiliate').removeAttr('disabled');
        $('#dummyAffiliateId').remove();
		$('.this_error_wrapper').hide();
	});

    $(document).on('click','.mixedCoregCampaignOrdering',function()
    {
        var id = $(this).data('id');
        var rt_id = $(this).data('rt_id');
        var modal = $('#mixedCoregCampaignOrderFormModal');
        var form = $('#mixed_coreg_campaign_order_form');

        // console.log(id);
        // console.log(rt_id);

        form.find('#mixed_coreg_rev_tracker_id').val(id);
        form.find('#mixed_coreg_rev_tracker_display').val(id);
        form.find('#this_id').val(rt_id);

        $.ajax({
            type: 'POST',
            url: $('#baseUrl').html() + '/revenue_tracker_mixed_coreg_campaign_order/' + id,
            error: function(data)
            {
                var errors = data;
            },
            success: function(data)
            {
                //console.log(data);
                var campaignOrder = data.campaignOrder;
                var reorderReferenceDate = data.reorderReferenceDate;

                // time picker
                $('#mixed_coreg_reference_date').val(reorderReferenceDate);
                $('#timepicker').timepicker('setTime', data.daily.daily_time);
                if(data.daily.recurence == 'daily') {
                    $('#recurrence_daily').prop('checked', true);
                    $('#recurrence_views').prop('checked', false);
                } else {
                    $('#recurrence_views').prop('checked', true);
                    $('#recurrence_daily').prop('checked', false);
                }

                //console.log(campaignOrder);
                //console.log(reorderReferenceDate);

                var mixedCoregCampaignOrderList = $('.mixedCoregCampaignOrderList');
                mixedCoregCampaignOrderList.html('');

                var hasOrder = true,
                    orderBy = $('#trk-'+rt_id+'-mixed_coreg_order_by').val(),
                    views = $('#trk-'+rt_id+'-mixed_coreg_campaign_views').val(),
                    limit = $('#trk-'+rt_id+'-mixed_coreg_campaign_limit').val();

                if(parseInt(limit) == 0) {
                    limit = null;
                }

                if(campaignOrder == null || campaignOrder == '' || campaignOrder == '[]') {
                    // console.log(campaignOrder);
                    campaignOrder = $('#default_mixed_coreg_campaign_order').val();
                    hasOrder = false;
                    orderBy = 2;
                    views = 0;
                    limit = null;
                }

                form.find('#mixed_coreg_order_by').val(orderBy);
                form.find('#mixed_coreg_views').val(views);
                form.find('#mixed_coreg_limit').val(limit);

                //the order status is using bootstrap toggle
                var orderStatus = $('#trk-'+rt_id+'-mixed_coreg_order_status').val();

                if(orderStatus==1)
                {
                    form.find('#mixed_coreg_reorder').bootstrapToggle('on');
                }
                else
                {
                    form.find('#mixed_coreg_reorder').bootstrapToggle('off');
                }

                console.log(campaignOrder);
                campaignOrder = JSON.parse(campaignOrder);
                //$('input[name="campaign_type_order['+ct_id+']"]').val(JSON.stringify(this_campaign_order));

                $.each(campaignOrder, function(id, campaign )
                {
                    $('#mixedCoregCampaignOrderList').append('<li class="list-group-item" id="ct_'+id+'_id_'+campaign+'" data-campaign_id="'+campaign+'" >'+campaign + ' - ' +campaign_name[campaign]+' <span class="pull-right">'+campaign_status[campaign]+'</span></li>');
                });

                $('input[name="mixed_coreg_campaign_order"]').val(JSON.stringify(campaignOrder));

                //set the sortable for each campaign type ordering
                mixedCoregCampaignOrderList.sortable({
                    helper: function(e, ui) {
                        ui.children().each(function() {
                            $(this).width($(this).width());
                        });
                        return ui;
                    },
                    update: function(event, ui) {

                        var pathJsonString = '[';

                        var listGroupItemsSelector = '#'+$(this).attr('id')+' li';
                        //var listGroupContainerSelector = 'input[name="campaign_type_order['+$(this).data('campaign_type')+']"]';

                        $(listGroupItemsSelector).each( function() {
                            pathJsonString += $(this).data('campaign_id') +',';
                        });

                        pathJsonString = pathJsonString.slice(0,-1);
                        pathJsonString += ']';

                        //set the container of the final order
                        $('input[name="mixed_coreg_campaign_order"]').val(pathJsonString);
                    }

                }).disableSelection();
                modal.modal('show');
            }
        });
    });

    $(document).on('click','.trackerCampaignType',function()
    {
        var id = $(this).data('id'),
            rt_id = $(this).data('rt_id'),
            modal = $('#CmpOrdrFormModal'),
            form = $('#campaign_order_form'),
            details = $.parseJSON($('#trk-'+rt_id+'-details').html());

        // console.log(id);
        // console.log(details);

        form.find('#rev_tracker_id').val(id);
        form.find('#rev_tracker_display').val(id);
        form.find('#this_id').val(rt_id);

        $.ajax({
            type: 'POST',
            url: $('#baseUrl').html() + '/revenue_tracker_campaign_order/' + id,
            error: function(data)
            {
                var errors = data;
            },
            success: function(data)
            {
                console.log(data);
                $('.campOrderGrpList').html('');

                var campaignOrder = data.campaignOrder,
                    hasOrder = true,
                    orderBy = details.order_by,
                    views = details.views;

                if(campaignOrder == null) {
                    // console.log(campaignOrder);
                    campaignOrder = JSON.parse($('#default_campaign_order').val());
                    hasOrder = false;
                    orderBy = 2;
                    views = 0;
                }

                revTrackerCampaignTypeReferenceDates = data.referenceDate;

                form.find('#order_by').val(orderBy);
                form.find('#views').val(views);

                //the order status is using bootstrap toggle
                var orderStatus = details.order_status;
                if(orderStatus==1) form.find('#reorder').bootstrapToggle('on');
                else form.find('#reorder').bootstrapToggle('off');

                // $.each(campaignOrder, function( campaign_type_id, order ) {
                //     console.log(campaign_type_id);
                //     console.log(order);
                // });

                form.find('#campaign_type_constants option').each(function() {

                    var ct_id = $( this ).val();

                    if (typeof campaignOrder[ct_id] == 'undefined') {
                      return true;
                    }

                    // console.log($( this ).text());
                    // console.log($( this ).val());
                    var this_campaign_order = campaignOrder[ct_id];
                    // console.log('here');
                    // console.log(this_campaign_order);

                    if(hasOrder) this_campaign_order = JSON.parse(this_campaign_order);
                    // console.log('here 2');
                    // console.log(this_campaign_order);
                    // console.log(ct_id);
                    // console.log(this_campaign_order);
                    // if(ct_id == 1) console.log(this_campaign_order);

                    /*if(! hasOrder)*/ $('input[name="campaign_type_order['+ct_id+']"]').val(JSON.stringify(this_campaign_order));
                    $.each(this_campaign_order, function(id, campaign ) {

                        // console.log(campaign);
                        // if(ct_id == 1) {
                        //     console.log(campaign);
                        //     console.log(campaign_name[campaign]);
                        // }

                        $('#campTypeOrderList_'+ct_id).append('<li class="list-group-item" id="ct_'+id+'_id_'+campaign+'" data-campaign_id="'+campaign+'" >'+campaign + ' - ' +campaign_name[campaign]+' <span class="pull-right">'+campaign_status[campaign]+'</span></li>');
                    });

                });

                form.find('#campaign_type_constants').trigger('change');
                modal.modal('show');

            }
        });
    });

    $('#campaign_type_constants').change(function(){

        var key = $(this).val();
        var sortableListListGroupContainerID = '#campOrderContainer_'+key;

        //hide all sortable container for campaign types
        $('.campaignOrderContainer').hide();

        $(sortableListListGroupContainerID).show();

        if(revTrackerCampaignTypeReferenceDates != null) {
            $('#reference_date').val(revTrackerCampaignTypeReferenceDates[key]);
        }else {
            $('#reference_date').val('');
        }
    });

    $('.campOrderGrpList').each(function() {

        //var campaignType = $(this).data('campaign_type');
        var listGroupID = '#'+$(this).attr('id');
        var listGroup = $(listGroupID);

        var listGroupContainerSelector = $('input[name="campaign_type_order['+$(this).data('campaign_type')+']"]'); //'#campaign_type_ordering_hidden_container_'+$(this).data('campaign_type');
        var listContainer = $(listGroupContainerSelector);

        if(listContainer.val() == '')
        {

            var pathJsonString = '[';

            //initialize the container if it is empty
            $(listGroupID+' li').each( function() {
                pathJsonString += $(this).data('campaign_id') +',';
            });

            pathJsonString = pathJsonString.slice(0,-1);

            //if blank don't include the closing bracket
            if(pathJsonString != '')
            {
                pathJsonString += ']';
            }

            listContainer.val(pathJsonString);
        }

        //set the sortable for each campaign type ordering
        listGroup.sortable({
            helper: function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            },
            update: function(event, ui) {

                var pathJsonString = '[';

                var listGroupItemsSelector = '#'+$(this).attr('id')+' li';
                var listGroupContainerSelector = 'input[name="campaign_type_order['+$(this).data('campaign_type')+']"]';

                $(listGroupItemsSelector).each( function() {
                    pathJsonString += $(this).data('campaign_id') +',';
                });

                pathJsonString = pathJsonString.slice(0,-1);
                pathJsonString += ']';

                $(listGroupContainerSelector).val(pathJsonString);
            }

        }).disableSelection();
    });

    $('#CmpOrdrFormModal').on('hide.bs.modal', function (event)
    {
        var form = $(this).find('.this_form');

        $('.this_modal_submit').html('Save');
        $('.campOrderGrpList').html('');

        $('.campOrderGrpList').html('');

        form.find('.this_field').each(function()
        {
            $(this).val('');
        });

        form.find('#this_id').val('');

        form.find('.error').each(function(){
            $(this).removeClass('error');
            $(this).removeClass('error_field');
            $(this).removeClass('error_label');
        });

        $('.this_error_wrapper').hide();

        $('#reference_date').val('');
        $('#campaign_type_constants').val(1).trigger('change');
        revTrackerCampaignTypeReferenceDates = null;

    });

    //Set Exit Page
    $('#exit_page_rev_tracker_selection').select2({
        //tags: true,
        placeholder: 'Select the id of the Revenue Tracker.',
        minimumInputLength: 1,
        theme: 'bootstrap',
        language: {
            inputTooShort: function(args) {
                return "Please enter the id of the Revenue Tracker. (Enter 1 or more characters)";
            }
        },
        ajax: {
            url: $('#baseUrl').html()+'/search/select/availableRevTrackersForExitPage',
            dataType: "json",
            type: "POST",
            data: function (params) {

                var queryParameters = {
                    term: params.term,
                    exit_page_id: $('#exit_page_selected').val(),
                    selected: $('#exit_page_rev_tracker_selection').val()
                };

                return queryParameters;
            },
            processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.revenue_tracker_id,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    $(document).on('change','#exit_page_rev_tracker_selection',function(){
        if($('#exit_page_rev_tracker_selection option:selected').length > 0) {
            $('.setExitPageBtn').removeAttr('disabled');
            $('#remove_revenue_tracker_id_selections').show();
        }else {
            $('.setExitPageBtn').attr('disabled', true);
            $('#remove_revenue_tracker_id_selections').hide();
        }
    });

    $(document).on('click','#remove_revenue_tracker_id_selections',function(){
        $('#exit_page_rev_tracker_selection').val(null).trigger('change');
    });

    var exit_page_table = $('#exitPageTracker-table').DataTable({
        'processing': true,
        'serverSide': true,
        "autoWidth": false,
        // "searching": false,
        "columnDefs": [
            { "orderable": false, "targets": [0] }
        ],
        "order": [[ 1, "asc" ]],
        // "deferLoading": 0,
        'ajax':{
            url: $('#baseUrl').html() + '/exitPageRevTrackers',
            type: 'post',
            'data': function(d)
            {
                d.exit_page_id = $('#exit_page_selected').val();
            },
            "dataSrc": function ( json ) {
                $('#selectAllEPRT').prop('checked', false);
                $('#removeExitPageRevTrackerBtn').attr('disabled', true);
                return json.data;
            },
            error: function(data) //error handling
            {
                console.log(data);
            }
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
        "sDom": 'l<"setExitPageToolbar">frtip'
    });

    $("div.setExitPageToolbar").html('<input id="removeExitPageRevTrackerBtn" class="btn btn-primary pull-right btn-xs" type="submit" value="Set to Default">');

    $(document).on('click','#setExitPageBtn',function()
    {
        $('#setExitPageModal').modal('show');
    });

    $(document).on('change','#exit_page_selected',function()
    {
        $('#exit_page_rev_tracker_selection').val(null).trigger('change');
        exit_page_table.clear().search('');
        exit_page_table.ajax.reload();
    });

    $(document).on('change','.eprt-checkbox',function()
    {
        var count = $('.eprt-checkbox:checked').length,
            exit_page = $('#exit_page_selected').val();

        if(count > 0 && exit_page != '') {
            $('#removeExitPageRevTrackerBtn').removeAttr('disabled');
        }else {
            $('#removeExitPageRevTrackerBtn').attr('disabled', true);
        }

        if(count == $('.eprt-checkbox').length) {
            $('#selectAllEPRT').prop('checked', true);
        }else {
            $('#selectAllEPRT').prop('checked', false);
        }
    });

    $('#selectAllEPRT').on('change', function (event) 
    {
        $('.eprt-checkbox').prop('checked', $(this).prop("checked")).trigger('change');
    });

    $('#setExitPageModal').on('hide.bs.modal', function (event)
    {
        $('#exit_page_rev_tracker_selection').val(null).trigger('change');
        $('#exit_page_selected').val(null).trigger('change');
    });

    $(document).on('click','#new_subid_breakdown_status',function()
    {
        var val = $(this).val();

        if(val == 1) {
            $('#newSIBSetting-div').show();
        }else {
            $('#newSIBSetting-div').hide();
            $('#nsib_s1').prop('checked', false)
            $('#nsib_s2').prop('checked', false)
            $('#nsib_s3').prop('checked', false)
            $('#nsib_s4').prop('checked', false)
        }
    });
});
