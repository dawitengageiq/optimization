var inactiveAdvertisers = [],
	inactiveCategories = [],
	eiqFrameID = $('#eiq_iframe_id').val(),
	currentCampaign,
	campaignContentsExt = []
	;

$(document).ready(function()
{
	$('[data-toggle="tooltip"]').tooltip();

    $('.alert').on('click','.close',function(){
        $(this).closest('.alert').fadeOut();
        $(this).closest('.internal-error-wrapper').hide();
    });

	//select2 implementation for advertiser select tags
	$('.advertiser-select-add').select2({
		theme: 'bootstrap',
		dropdownParent: $("#addCmpFormModal")
	});

	//select2 implementation for advertiser select tags
	$('.advertiser-select-edit').select2({
		theme: 'bootstrap',
		dropdownParent: $("#editCampaignAdvertiserDiv")
	});

	var dataCampaignsURL = $('#baseUrl').html() + '/campaigns';
	// console.log(dataCampaignsURL);

	var campaignsTable = $('#campaign-table');
	var advertiserID = campaignsTable.data('advertiser');
	var affiliateID = campaignsTable.data('affiliate');

	var extraData = {};

	if(advertiserID!=undefined)
	{
		extraData = {
			'awesome_advertiser_id' : advertiserID
		}
	}
	else if(affiliateID!=undefined)
	{
		extraData = {
			'awesome_affiliate_id' : affiliateID
		}
	}

	// console.log(extraData);
	var showInactive = 0;

	var campaign_datatable = campaignsTable.DataTable({
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
			null,
			{ 'orderable': false }
		],
		"order": [],
		'ajax':{
			url:dataCampaignsURL, //json datasource
			// type: 'GET',  //LIVE
			type: 'post',  // KARLA ver
			// 'data' : extraData,
			'data': function(d)
            {
                d.json_content_filter = $('#jsonContentFilter').val();
                d.awesome_advertiser_id = advertiserID!=undefined ? advertiserID : '';
                d.awesome_affiliate_id = affiliateID!=undefined ? affiliateID : '';
                d.show_inactive = showInactive;
            },
			error: function(data) //error handling
			{
				console.log(data);
			}
		},
		lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
		"sDom": 'lf<"addToolbar">rtip'
	});
	var toolBarHtml = '<label>New Design Filter: <select class="form-control" id="jsonContentFilter" name="json_content"><option value="" selected="selected">Show ALL</option><option value="true">w/ ND</option><option value="false">w/o ND</option></select></label>';
	toolBarHtml += '<label id="show_inactive_campaigns"><input type="checkbox" name="show_inactive_campaigns">Show Inactive Campaigns<a href="#" data-container="body" data-toggle="tooltip" data-placement="bottom" title="By default, all INACTIVE campaigns are hidden and will not be shown in the Campaign Table List. You can show them by simply checking the box of this option and automatically, all INACTIVE campaigns will be displayed accordingly."><i class="fa fa-question-circle"></i></a></label>';
	$("div.addToolbar").html(toolBarHtml);

	$('#show_inactive_campaigns a').click(function(e){e.preventDefault();});
	$('#show_inactive_campaigns a').tooltip();
	$(document).on('change', '#show_inactive_campaigns [name="show_inactive_campaigns"]', function(){
        // console.log('change');
        showInactive = $(this).prop('checked') ? 1 : 0;
        // console.log(showInactive)
        // campaign_datatable.clear();
        campaign_datatable.ajax.reload();//.draw();
    });

	$(document).on('change', '#jsonContentFilter', function(){
        console.log('Reload');

        // campaign_datatable.clear();
        campaign_datatable.ajax.reload();//.draw();
    });

	$('#campaign-table tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            campaign_datatable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });

	var lead_cap_select = '<select class="form-control full_width_form_field">';
		lead_cap_select += '<option value=""></option>';
		lead_cap_select += '<option value="0">Unlimited</option>';
		lead_cap_select += '<option value="1">Daily</option>';
		lead_cap_select += '<option value="2">Weekly</option>';
		lead_cap_select += '<option value="3">Monthly</option>';
		lead_cap_select += '</select>';

	$('.this_isa_popover').popover();

	$('.date-wrapper input').datetimepicker({
		format: 'YYYY-MM-DD'
	});

	$('.time-wrapper input').datetimepicker({
        format: 'LT'
    });

	$('.img_type').change(function() {
		var img_wrap = $(this).closest('.image_wrapper');
		//1 - upload
		//2 - img url
		//check if add or edit
		if(img_wrap.data('type') == 'add') {
			img_wrap.find('.imgPreview').hide();
			img_wrap.find('.imgPreview img').attr('src','');
			img_wrap.find('#image').val('');
		}

		var type = $(this).val();
		console.log(type);
		if(type == 2 ) {
			img_wrap.find('#image').attr('type','text');
		}else {
			img_wrap.find('#image').attr('type','file');
		}
	});

	$(".campaign_img").change(function () 
	{
		var this_input = $(this);
		var img_wrap = $(this).closest('.image_wrapper');
		var img_type = img_wrap.find('input[name="img_type"]:checked').val();

		this_input.removeClass('error_field error');

		if($(this).val() != '') {
			$('.imgPreview').show();
        	if(img_type == 1) {
        		imgPreview($(this),$('.imgPreview img'));
        	}else {
        		//check if url is valid
				$('.imgPreview img')
				    .on('load', function() { console.log("image loaded correctly"); })
				    .on('error', function() { 
				    	if(this_input.val() != '') {
				    		console.log(this_input.val());
				    		this_input.addClass('error_field error').val('');
				    		console.log("error loading image"); 
				    	}
				    })
				    .attr("src", $(this).val());
				;

				//$('.imgPreview img').attr('src',$(this).val());   		
        	}
		}
		else 
		{
			$('.campaign_img').removeClass('error_field error');
			$('.imgPreview').hide();
			$('.imgPreview img').attr('src','');
		}
    });
	
	/* Lead Cap Type */
    $('.leadCapType').on('change', function() {
    	var select = $(this);
    	var leadCapVal_div = '', leadCapType_div = '';

    	if(select.data('form') == 'add') {
			leadCapVal_div = $('#leadCapVal_add');
			leadCapType_div = $('#leadCapType_add');
		}else {
			leadCapVal_div = $('#leadCapVal_edit');
			leadCapType_div = $('#leadCapType_edit');
		}

    	if(select.val() == 0){
    		leadCapType_div.removeClass('col-md-6').addClass('col-md-12');
    		leadCapVal_div.find('input').removeAttr('required');
    		leadCapVal_div.hide();
    	}else {
    		leadCapType_div.removeClass('col-md-12').addClass('col-md-6');
    		leadCapVal_div.find('input').attr('required','true');
    		leadCapVal_div.show();
    	}
    });

    /* Lead Cap Type */
    $('.campaignType').on('change', function() {
    	var select = $(this);
    	var linkoutOfferID_div = '', olrProgId_div = '';

    	if(select.data('form') == 'add') {
			linkoutOfferID_div = $('#linkoutOfferID_add');
			olrProgId_div = $('#olrProgramID_add');
		}else {
			linkoutOfferID_div = $('#linkoutOfferID_edit');
			olrProgId_div = $('#olrProgramID_edit');
		}

    	if(select.val() == 5 || select.val() == 6){
    		linkoutOfferID_div.find('input').attr('required','true');
    		linkoutOfferID_div.show();
    	}else {
    		linkoutOfferID_div.find('input').removeAttr('required');
    		linkoutOfferID_div.hide();
    	}

    	if(select.val() != 5 && select.val() != 4 && select.val() != 6){
    		olrProgId_div.find('input').attr('required','true');
    		olrProgId_div.show();
    	}else {
    		olrProgId_div.find('input').removeAttr('required');
    		olrProgId_div.hide();
    	}
    });

	$('#addCmpBtn').click(function() 
	{
		var this_modal = $('#addCmpFormModal');
		var url = $('#baseUrl').html() + '/add_campaign';

		this_modal.find('.form_with_file').attr('action',url);
		this_modal.find('.form_with_file').attr('data-process', 'add_campaign');
		this_modal.find('.modal-title').html('Add Campaign');
		this_modal.find('#this_id').val('');
		this_modal.find('#default_payout').val(0).attr('disabled', true);
		this_modal.find('#default_received').val(0).attr('disabled', true);
		
		$('.this_modal_submit').removeAttr('disabled');

		this_modal.modal('show');
	});

	function containsObject(obj,list){

		for (var i = 0; i < list.length; i++) {
			if (list[i].value === obj.value) {
				return true;
			}
		}

		return false;
	}

	var stackCodeMirror = CodeMirror.fromTextArea(document.getElementById('cmpCnt-stack-content'), {
	  	theme: 'default',
	    lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        // mode: 'javascript',
        indentUnit: 4,
        indentWithTabs: true,
        readOnly: true
	});

	//PAYOUTS HISTORY
	var campaign_payout_history_table = $('#campaignPayoutsHistory-table').DataTable({
		'processing': true,
		'serverSide': true,
		"autoWidth": false,
        "searching": false,
        "bSort" : false,
        "deferLoading": 0,
		'ajax':{
			url: $('#baseUrl').html() + '/get_campaign_payout_history',
			type: 'post',
			'data': function(d)
            {
                d.campaign = $('.this_campaign').val();
            },
            "dataSrc": function ( json ) {
                return json.data;
            },
			error: function(data) //error handling
			{
				console.log(data);
			}
		},
		lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
	});

	/***
	 * onclick listener for editing campaign
	 */
	$(document).on('click','.editCampaign',function() 
	{
		$(this).closest('tr').addClass('selected');

		var editCampaignButton = $('.editCampaign');
		editCampaignButton.find('span').removeClass().addClass('glyphicon glyphicon-pencil');
		editCampaignButton.attr('disabled','true');

		var this_button = $(this);
		this_button.find('span').removeClass().addClass('fa fa-spin fa-spinner');

		/* Info */
		var this_modal = $('#editCmpFormModal');
		var id = $(this).data('id');
		currentCampaign = id;

		//$('#info_tab form .this_field').attr('disabled',true);
		this_modal.find('.this_campaign').val(id);

		var this_cmp = '#cmp-' + id + '-';

		$('#modal-campaign-title').html(id + ' - '+ $(this_cmp+'name').html());
		
		if(typeof $(this_cmp+'img').data('img') == 'undefined' || $(this_cmp+'img').data('img') == '' ) {
			this_modal.find('.imgPreview').show();
			this_modal.find('.imgPreview img').attr('src', $(this_cmp+'img img').attr('src'));
		}

		//Lead Cap Type & Value
		var leadCapVal_div = $('#leadCapVal_edit'),
			cap_type = $(this_cmp+'lct').data('type');	
		if(cap_type == 0) {
			leadCapVal_div.find('input').removeAttr('required');
			leadCapVal_div.hide();
		}else {
			leadCapVal_div.find('input').attr('required','true');
    		leadCapVal_div.show();
    		$('#leadCapType_edit').removeClass('col-md-12').addClass('col-md-6');
		} 

		var advertiserID = $(this_cmp+'adv').data('adv');
		// console.log(advertiserID);

		var advertiserSelect = this_modal.find('#advertiser');

		//add the advertiser if it is inactive. This happens when the advertiser is changed to inactive.
		if(advertiserID!=='')
		{
			//check if advertiser is inactive. If inactive just include it in the selection list
			var url = $('#baseUrl').val() + '/advertisers/'+advertiserID+'/status';

			$.ajax({
				type: 'POST',
				url: url,
				success: function(data)
				{
					if(!data.active)
					{
						var advertiserExists = $("#advertiser option[value='"+data.advertiser_id+"']").length > 0;

						if(!advertiserExists)
						{
							//insert it in the current pool of options
							advertiserSelect.append($('<option>',{
								value: data.advertiser_id,
								text: data.name
							}));

							advertiserSelect.val(advertiserID).trigger('change');
							advertiserSelect.prop('disabled',false);
							console.log(data.advertiser_id+" was added!");
						}

						var inactiveAdvertiser = {
							value: data.advertiser_id,
							text: data.name
						};

						if(!containsObject(inactiveAdvertiser,inactiveAdvertisers)){
							inactiveAdvertisers.push(inactiveAdvertiser);
						}
					}
				}
			});
		}

		var allowEdit = true;

		if(Number($(this_cmp+'crtDiff').val()) <= 30) {
			if($('#allowed_force_edit').val() != '1') {
				allowEdit = false;
			} 
		}

		var campaign_type = $(this_cmp+'type').data('type');
		this_modal.find('#date-created-container').html( $(this_cmp+'created').val());
		this_modal.find('#name').val( $(this_cmp+'name').html());
		this_modal.find('#priority').val( $(this_cmp+'prio').html());
		this_modal.find('#campaign_type').val(campaign_type).trigger('change');
		advertiserSelect.val(advertiserID).trigger('change');
		this_modal.find('#lead_type').val(cap_type);
		this_modal.find('#lead_value').val( $(this_cmp+'lcv').html());
		this_modal.find('#default_payout').val( $(this_cmp+'dpyt').val());
		this_modal.find('#default_received').val( $(this_cmp+'drcv').html());
		this_modal.find('#description').val( $(this_cmp+'desc').val());
		this_modal.find('#notes').val( $(this_cmp+'notes').html());
		this_modal.find('input[name="status"][value="'+$(this_cmp+'stat').data('status')+'"]').prop('checked', true);
		this_modal.find('#linkout_offer_id').val($(this_cmp+'lnkOutOffer').val());
		this_modal.find('#program_id').val($(this_cmp+'prgId').val());
		this_modal.find('#rate').val( $(this_cmp+'rate').html());
		this_modal.find('#publisher_name').val( $(this_cmp+'pubN').val());
		this_modal.find('#advertiser_email').val( $(this_cmp+'advEmail').val());
		this_modal.find('input[name="is_external"][value="'+$(this_cmp+'pubE').val()+'"]').prop('checked', true);

		if(!allowEdit) {
			this_modal.find('#default_payout').attr('disabled', true);
			this_modal.find('#default_received').attr('disabled', true);
		}

		var categoryID = $(this_cmp+'ctgry').val();
		var categorySelect = this_modal.find('#category');
		categorySelect.val(categoryID).trigger('change');

		//check if advertiser is inactive. If inactive just include it in the selection list
		var categoryStatusURL = $('#baseUrl').val() + '/categories/'+categoryID+'/status';

		$.ajax({
			type: 'POST',
			url: categoryStatusURL,
			success: function(data)
			{
				if(!data.active)
				{
					var categoryExists = $("#category option[value='"+data.category_id+"']").length > 0;

					if(!categoryExists)
					{
						//insert it in the current pool of options
						categorySelect.append($('<option>',{
							value: data.category_id,
							text: data.name
						}));

						categorySelect.val(categoryID).trigger('change');
						// console.log(data.category_id+" was added!");
					}

					var inactiveCategory = {
						value: data.category_id,
						text: data.name
					};

					if(!containsObject(inactiveCategory,inactiveCategories)){
						inactiveCategories.push(inactiveCategory);
					}
				}
			}
		});
		
		/* UNIVERSAL */
		$('.this_campaign').val(id);
		var filter_table = $('#campaign-filter-table').DataTable();
		filter_table.rows().remove().draw();	

		var filter_group_table = $('#campaign-filter-group-table').DataTable();
		filter_group_table.rows().remove().draw();

		var affiliate_table = $('#campaign-affiliate-table').DataTable();
		affiliate_table.rows().remove().draw();
		// affiliate_table.ajax.reload();

		var payout_table = $('#campaign-payout-table').DataTable();
		payout_table.rows().remove().draw();	

		var the_url = $('#baseUrl').html() + '/get_campaign_info';

		$.ajax({
			type: 'POST', // LIVE Ver
			// type: 'GET', //KARLA Ver
			data: {
				'id'	:	id
			},
			url: the_url,
			// error: function(xhr, status, error) {
  			// 	console.log(error);
			// },
			success: function(data) {
				// console.log(data);
				// Pass data campaign extension
				// window.Vue.setContentData(data.content.high_paying);

				/* FILTER GROUP START*/
				var filter_group_list = data.filter_group_list;
				var filter_group_select = $('.filterGroupList');
				filter_group_select.find('option').remove();
				// filter_group_select.append('<option value=""></option>');
				if(filter_group_list != null) {
					var counter = 0;
					$.each(filter_group_list, function(name,index){
						filter_group_select.append('<option value="'+index+'">'+name+'</option>');
						counter++;
					});	
					filter_group_select.attr('size',counter);
				}
				
				var filter_groups_data = data.filter_groups;
				filter_group_table.rows().remove().draw();	
				// console.log(filter_groups_data.length);
				if(filter_groups_data.length > 0) {
					$.each(filter_groups_data, function(index,filter_group){
						var fg_id = filter_group.id;
						var fg_name = filter_group.name;
						var fg_desc = filter_group.description;
						var fg_stat = filter_group.status;
						var fg_stat_name = 'Inactive';
						if(fg_stat != 0) fg_stat_name = 'Active';

						var nameCol = '<span id="cfg-'+fg_id+'-name">'+fg_name+'</span>';
						var descCol = '<span id="cfg-'+fg_id+'-desc">'+fg_desc+'</span>';
						var statCol = '<span id="cfg-'+fg_id+'-stat" data-status="'+fg_stat+'">'+fg_stat_name+'</span>';
						var actionCol =  '<button id="cfg-'+fg_id+'-view-button" class="btn btn-default viewCampaignFilterGroup" type="button" data-id="'+fg_id+'"><span class="glyphicon glyphicon-eye-open"></span></button>';
							actionCol += '<button id="cfg-'+fg_id+'-edit-button" class="btn btn-default editCampaignFilterGroup" type="button" data-id="'+fg_id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
							actionCol += '<button id="cfg-'+fg_id+'-delete-button" class="btn btn-default deleteCampaignFilterGroup" type="button" data-id="'+fg_id+'"><span class="glyphicon glyphicon-trash"></span></button>';
						
						var dataRow = [
							nameCol,
							descCol,
							statCol,
							actionCol
						];
						//add the row data
						var rowNode = filter_group_table.row.add(dataRow).draw().node();
					});	
					$('#addCmpFilter').removeAttr('disabled');
				}else {
					$('#addCmpFilter').attr('disabled',true);
				}

				$("#addCmpFilter").click(function() {
					$(".filterGroupList").prop("selectedIndex", 0);
						//console.log("selected");
				});
				/* FILTER GROUP END */

				/* AFFILIATE START */
				var affiliate_data = data.affiliates;
				// $('select[name="affiliates[]"] option').remove();
				// var aff_select = $('select[name="affiliates[]"]');
				// $.each(affiliate_data['available'], function(index,value){
				// 	aff_select.append('<option value="'+index+'">'+value+'</option>');
				// });		
				// var affiliate_count = $('select[name="affiliates[]"] option').length;
				// if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);
				// else $('select[name="affiliates[]"]').attr('size', 10);

				var isEIQIframe = false;
				if(eiqFrameID != '' && eiqFrameID > 0 && eiqFrameID == id) isEIQIframe = true;

				// var affiliate_table = $('#campaign-affiliate-table').DataTable();
				affiliate_table.rows().remove().draw();		

				if(affiliate_data['affiliated'] != undefined) 
				{
					// console.log(affiliate_data['affiliated']);
					$.each(affiliate_data['affiliated'], function(index,affiliate){

						var id = affiliate['id'];
						var idCol = '<input name="select_affiliate[]" class="selectCampaignAffiliate" value="'+id+'" data-name="'+affiliate['affiliate_name']+'" type="checkbox"> ';
						var leadTypeCol = '<span id="ca-'+id+'-type" data-id="'+affiliate['cap_type']+'">'+affiliate['cap_type_name']+'</span>';
						var leadValCol = '<span id="ca-'+id+'-value">'+affiliate['cap_value']+'</span>';
						var actionCol =  '<button id="ca-'+id+'-edit-button" class="btn btn-default editCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
						if(!isEIQIframe) {
							actionCol += ' <button id="ca-'+id+'-delete-button" class="btn btn-default deleteCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
						}else {
							var affStatIcon = 'ok',
								affStatBtn = 'success';
							if(affiliate['status'] == 0) {
								affStatIcon = 'remove';
								affStatBtn = 'danger';
							}
							actionCol += ' <button id="ca-'+affiliate['affiliate_id']+'-status-button" class="btn btn-'+affStatBtn+' eiqFrameStatus" type="button" data-id="'+affiliate['affiliate_id']+'" data-status="'+affiliate['status']+'"><span class="glyphicon glyphicon-'+affStatIcon+'-circle"></span></button>';
							actionCol += ' <button id="ca-'+affiliate['affiliate_id']+'-iframe-button" class="btn btn-default eiqFrameTraffic" type="button" data-id="'+affiliate['affiliate_id']+'">Iframe Traffic</button>';
						}

						var dataRow = [
							idCol,
							affiliate['affiliate_name'],
							leadTypeCol,
							leadValCol,
							actionCol
						];

						//add the row data
						var rowNode = affiliate_table.row.add(dataRow).draw().node();
						var affRow = $(rowNode);

					});	
					// refreshTable($('#campaign-affiliate-table'));	
				}	
				/* AFFILIATE END */

				/* PAYOUT START */
				var payout_data = data.payouts;
				$('select[name="payout[]"] option').remove();
				var aff_select = $('select[name="payout[]"]');
				$.each(payout_data['available'], function(index,value){
					aff_select.append('<option value="'+index+'">'+value+'</option>');
				});		
				var affiliate_count = $('select[name="payout[]"] option').length;
				if(affiliate_count <= 10) $('select[name="payout[]"]').attr('size', affiliate_count);

				payout_table.rows().remove().draw();	

				if(payout_data['affiliate'] != undefined) 
				{
					$.each(payout_data['affiliate'], function(index,payout){

						var id = payout['id'];
						var aff_name = payout['affiliate_name'];
						// var idCol = '<input name="select_payout[]" class="selectCampaignPayout" value="'+id+'" data-name="'+aff_name+'" type="checkbox"> ' + id;
						var idCol = '<input name="select_payout[]" class="selectCampaignPayout" value="'+id+'" data-name="'+aff_name+'" type="checkbox">';
						var receiveCol = '<span id="cp-'+id+'-receivable">'+ payout['received'] +'</span>'
						var payoutCol = '<span id="cp-'+id+'-payable">'+ payout['payout'] +'</span>'

						var actionCol =  '<button id="cp-'+id+'-edit-button" class="btn btn-default editCampaignPayout" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
							actionCol += '<button id="cp-'+id+'-delete-button" class="btn btn-default deleteCampaignPayout" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
						var dataRow = [
							idCol,
							aff_name,
							receiveCol,
							payoutCol,
							actionCol
						];

						//add the row data
						var rowNode = payout_table.row.add(dataRow).draw().node();
						var affRow = $(rowNode);
					});		
				}	
				/* PAYOUT END */

				/* CONFIG START */
				var config_data = data.config;
				$('#cmpCfg-url').html(config_data.post_url);
				$('#cmpCfg-url-txt input').val(config_data.post_url);
				$('#cmpCfg-hdr').text(config_data.post_header).html();
				$('#cmpCfg-hdr-txt textarea').val(config_data.post_header);
				$('#cmpCfg-dta').text(config_data.post_data).html();
				$('#cmpCfg-dta-txt textarea').val(config_data.post_data);
				$('#cmpCfg-dta-fv').text(config_data.post_data_fixed_value).html();
				$('#cmpCfg-dta-fv-txt textarea').val(config_data.post_data_fixed_value);
				$('#cmpCfg-map').text(config_data.post_data_map).html();
				$('#cmpCfg-map-txt textarea').val(config_data.post_data_map);
				$('#cmpCfg-mtd').html(config_data.post_method);
				$('#cmpCfg-mtd-txt select').val(config_data.post_method);
				$('#cmpCfg-scs').text(config_data.post_success).html();
				$('#cmpCfg-scs-txt input').val(config_data.post_success);
				$('#cmpCfg-purl').text(config_data.ping_url).html();
				$('#cmpCfg-purl-txt input').val(config_data.ping_url);
				$('#cmpCfg-pscs').text(config_data.ping_success).html();
				$('#cmpCfg-pscs-txt input').val(config_data.ping_success);

				var ftp_sent = 'off',
					ftp_sent_display = 'NO';
					ftp_protocol = '';
				if(config_data.ftp_sent == 1) {
					ftp_sent = 'on';
					ftp_sent_display = 'YES';
				}

				if(config_data.ftp_protocol !== null) {
					ftp_protocol = $('#cmpCfg-ftpp-txt select option[value="'+config_data.ftp_protocol+'"]').html();
				}

				$('#cmpCfg-ftps').html(ftp_sent_display);
				$('#if_ftp_sent').bootstrapToggle(ftp_sent);
				$('#cmpCfg-ftpp').html(ftp_protocol);
				$('#cmpCfg-ftpp-txt select').val(config_data.ftp_protocol);
				$('#cmpCfg-ftpu').html(config_data.ftp_username);
				$('#cmpCfg-ftpu-txt input').val(config_data.ftp_username);
				$('#cmpCfg-ftppw').html(config_data.ftp_password);
				$('#cmpCfg-ftppw-txt input').val(config_data.ftp_password);
				$('#cmpCfg-ftph').html(config_data.ftp_host);
				$('#cmpCfg-ftph-txt input').val(config_data.ftp_host);
				$('#cmpCfg-ftppt').html(config_data.ftp_port);
				$('#cmpCfg-ftppt-txt input').val(config_data.ftp_port);
				$('#cmpCfg-ftpto').html(config_data.ftp_timeout);
				$('#cmpCfg-ftpto-txt input').val(config_data.ftp_timeout);
                $('#cmpCfg-ftpdirectory').html(config_data.ftp_directory);
                $('#cmpCfg-ftpdirectory-txt input').val(config_data.ftp_directory);

                $('#cmpCfg-email').html(config_data.email_sent == 1 ? 'YES' : 'No');
                $('#if_email_sent').bootstrapToggle(config_data.email_sent == 1 ? 'on' : 'off');
                $('#cmpCfg-emailTo-txt input').val(config_data.email_to);
				$('#cmpCfg-emailTo').html(config_data.email_to);
				$('#cmpCfg-emailTitle-txt input').val(config_data.email_title);
				$('#cmpCfg-emailTitle').html(config_data.email_title);
				$('#cmpCfg-emailBody-txt textarea').val(config_data.email_body);
				$('#cmpCfg-emailBody').html(config_data.email_body);

				//Automation
				// console.log(config_data);
				// if(config_data.post_data != null || config_data.post_data_fixed_value != null || config_data.post_data_map != null
				// 	|| config_data.post_header != null || config_data.post_method != null || config_data.post_success != null) {
				// 	// $('#cmpConfigAutomationTable').show();
				// 	$('#cmpConfigAutomationSubmit').removeClass('disabled');
				// }else {
				// 	$('#cmpConfigAutomationSubmit').addClass('disabled');
				// }
				$('#campaign_config_interface_form').find('.this_campaign').val(id);

				/* CONFIG END*/

				/* CONTENT START */
				content_data = data.content;

				if(content_data.long == null) long_content = '';
				else long_content = content_data.long;
				$('#cmpCnt-long-actual').val(long_content);
				$('#cmpCnt-long-content').val(long_content);

				if(content_data.stack == null) stack_content = '';
				else stack_content = content_data.stack;
				$('#cmpCnt-stack-actual').val(stack_content);
				$('#cmpCnt-stack-content').val(stack_content);
				stackCodeMirror.getDoc().setValue(stack_content);

				if(content_data.high_paying == null) high_paying_content = '';
				else high_paying_content = content_data.high_paying;
				$('#cmpCnt-hp-actual').val(high_paying_content);
				$('#cmpCnt-hp-content').val(high_paying_content);

				var stack_lock = 'off';
				if(content_data.stack_lock == 1) stack_lock = 'on';
				$('#stack_form_lock').bootstrapToggle(stack_lock);
				/* CONTENT END*/

				/* POSTING INSTRUCTION START */
				if(data.posting_instruction == null) posting_instruction = '';
				else posting_instruction = data.posting_instruction;
				$('#cmp-posting-instruction').val(posting_instruction);
				$('#cmp-posting-instruction-actual').val(posting_instruction);
				// $('#cmp-posting-instruction').ckeditorGet().setReadOnly(true);

				if(data.sample_code == null) sample_code = '';
				else sample_code = data.sample_code;
				$('#cmp-sample-code').val(sample_code);
				$('#cmp-sample-code-actual').val(sample_code);

				/* POSTING INSTRUCTION END */

				// console.log(data);

				this_modal.modal('show');
				this_button.find('span').removeClass().addClass('glyphicon glyphicon-pencil');

				$('.editCampaign').removeAttr('disabled');
			},
			error: function (jqXHR, text, errorThrown) {
			    console.log(jqXHR + " " + text + " " + errorThrown);
			    setTimeout(function() 
				{
				    window.location.href = '';
				}, 5000);
			}
		});

		//Campaign Payout History
		campaign_payout_history_table.ajax.reload();
		
		/* END*/
	});
	
	/* Hide Add Campaign Modal */
	$('#addCmpFormModal').on('hide.bs.modal', function (event) 
	{
		var form = $(this).find('.form_with_file');

		$('.this_modal_submit').html('Save');

		$('.imgPreview').hide();
		$('.imgPreview img').attr('src','');

		form.find('.this_field').each(function() 
		{
			if($(this).attr('type') == 'radio') {
				$('input[name="'+$(this).attr('name')+'"][value="0"]').prop('checked', true);
			}else {
				$(this).val('');
			}
		});

		form.find('#default_payout').removeAttr('disabled');
		form.find('#default_received').removeAttr('disabled');

		//clear the advertiser dropdown
		$('.advertiser-select-add').trigger('change');

		//Lead Cap Type & Value
		$('#leadCapType_add').removeClass('col-md-6').addClass('col-md-12');
		$('#leadCapVal_add').hide();

		//Link Out Offer ID
		$('#linkoutOfferID_add').hide();

		form.find('.this_modal_submit').html('Save');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

		$('.campaign_img').removeClass('error error_field');

	});

	/* Hide Edit Campaign Modal */
	$('#editCmpFormModal').on('hide.bs.modal', function (event) 
	{
		var form = $(this).find('.form_with_file');

		$('.this_modal_submit').html('Save');

		$('.imgPreview').hide();
		$('.imgPreview img').attr('src','');

		form.find('.this_field').each(function() 
		{
			if($(this).attr('name') == 'img_type') {
				$('input[name="img_type"][value="1"]').prop('checked', true);
			}else if($(this).attr('type') == 'radio') {
				$('input[name="'+$(this).attr('name')+'"][value="0"]').prop('checked', true);
			}else {
				$(this).val('');
			}
		});

		form.find('#default_payout').removeAttr('disabled');
		form.find('#default_received').removeAttr('disabled');

	  	form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

		//Lead Cap Type & Value
		$('#leadCapType_edit').removeClass('col-md-6').addClass('col-md-12');
		$('#leadCapVal_edit').hide();
		$('#leadCapVal_edit').find('#lead_value').removeAttr('required');

		//Link Out Offer ID
		$('#linkoutOfferID_edit').hide();
		$('#linkoutOfferID_edit').find('#linkout_offer_id').removeAttr('required');

		$(form).find('.this_error_wrapper .this_errors').hide();
		$(form).find('.this_error_wrapper .this_errors ul').remove();

		form.find('.this_modal_submit').html('Save');

		/* Affiliates */
		$('#editAffilatesBtn').attr('disabled',true);
		$('#deleteAffiliatesBtn').attr('disabled', true);
		campaign_available_affiliates = null;

		$('.campaign_img').removeClass('error error_field');
		//$('a[href="#info_tab"]').tab('show');

		/* Content */
		if($('#editCampaignLongContent').data('config') == 'hide') $('#editCampaignLongContent').click();
		if($('#editCampaignStackContent').data('config') == 'hide') $('#editCampaignStackContent').click();
		if($('#editCampaignHighPayingContent').data('config') == 'hide') $('#editCampaignHighPayingContent').click();
		
		if($('#addCmpFilter').data('collapse') == 'hide') $('#addCmpFilter').click();
		if(! $('#addCampAff').hasClass('collapsed')) $('#addCampAff').click();
		if(! $('#addPytAff').hasClass('collapsed')){
			console.log("NO COLLAPSE");
			$('#addPytAff').click();
		}
		if($('#editCmpConfig').data('config') == 'hide') $('#editCmpConfig').click();
		$('.selectAllCampaignAffiliate').prop('checked', false);
		$('#campaignAffiliateForm').collapse('hide');
		
		/* Payouts */
		$('.capPytSelectAllAff').prop('checked', false);
		$('#campaignPayoutForm').collapse('hide');
		campaign_payout_available_affiliates = null;

		/* Configs */
		$('.cmpCfg-form input').val('');
		$('.cmpCfg-form select').val('');
		$('.cmpCfg-dsply').html('');
		$('#if_ftp_sent').bootstrapToggle('off');
		$('#if_email_sent').bootstrapToggle('off');
		
		/* Filters */
		$('#campaignFilterInfo').collapse('hide');
		$('#campaignFilterForm').collapse('hide');
		$('#campaignFilterGroupForm').collapse('hide');
		/* Posting Instruction */
		if($('#editPostingInstruction').data('config') == 'hide') $('#editPostingInstruction').click();
		
		/* Stack Creative */
		// campaignCreativeID = 0;
		// $('[href="#stackContentTab"]').trigger('click');
		$('#stack_content_tab .this_error_wrapper').hide();
		$('#stack_content_tab .this_error_wrapper .this_errors ul').remove();
		stackCodeMirror.getDoc().setValue('');
		setTimeout(function() {
			stackCodeMirror.refresh();
		},100);

		//remove the inactive advertisers that was added during editing
		for(var n=0;n<inactiveCategories.length;n++)
		{
			var inactiveCategory = inactiveCategories[n];
			$("#category option[value='"+inactiveCategory.value+"']").remove();
		}

		//clear the inactiveCategories array
		inactiveCategories = [];

		for(var n=0;n<inactiveAdvertisers.length;n++)
		{
			var inactiveAdvertiser = inactiveAdvertisers[n];
			$("#advertiser option[value='"+inactiveAdvertiser.value+"']").remove();
		}

		//clear the inactiveCategories array
		inactiveAdvertisers = [];

		/* Affiliate Websites */
		if($('#affiliateWebsiteDiv').is(":visible")) {
			console.log('visible');
			$('#backToCampaignAffiliateBtn').trigger('click');
		}

		console.log('edit modal closed and removed all inactive items!');

		// Go back to Info tab
		$('[href="#info_tab"]').trigger('click');
		
	});

	$(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#stack_content_tab"]', function() {
	    this.refresh();
	}.bind(stackCodeMirror));

	/***
	 * onclick listener for editing campaign
	 */
	$(document).on('click','.deleteCampaign',function() 
	{
		var this_campaign = $(this);
		var id = $(this).data('id');

		var confirmation = confirm('Are you sure you want to delete this campaign?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_campaign';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : {
					'id' : id
				}, 
				success : function(data) {
					console.log(data);
					var table = $('#campaign-table').DataTable();
					table.row(this_campaign.parents('tr')).remove().draw();

					//PRIORITY CHANGES
					if(data) {
						$.each(data, function(priority, campaign) {
							table.cell( $('#cmp-'+campaign+'-prio').parent('td') ).data('<span id="cmp-'+campaign+'prio">'+priority+'</span>').draw();
							// $('#cmp-' + campaign + '-prio').html(priority);
						});
					}

					//Remove 1 priority option in edit campaign
					$('#priority option:last').remove();

				}
			});
		}
	});
	
	/* ADD Campaign Affiliate Collapse */
	$('#campaignAffiliateForm').on('hide.bs.collapse', function () {
		var this_button = $('#addCampAff');
		this_button.html('<span class="glyphicon glyphicon-plus"></span>');
		$('#addCmpAffiliateBtn').addClass('disabled');
		$('select[name="affiliates[]"] option').prop('selected',false);
	});

	$('#campaignAffiliateForm').on('show.bs.collapse', function () {
		var this_button = $('#addCampAff');
		this_button.html('<span class="glyphicon glyphicon-remove"></span>')
	});

	$('.closeAffiliateCollapse').on('click', function () {
		$('#campaignAffiliateForm').collapse('hide');
	});

	$('select[name="affiliates[]"]').change(function() {
		var selectedAffiliates = $('select[name="affiliates[]"] option:selected');
		if(selectedAffiliates.length > 0) $('#addCmpAffiliateBtn').removeClass('disabled');
		else $('#addCmpAffiliateBtn').addClass('disabled');
	});

	$('.selectAllCampaignAffiliate').on('change', function (event) 
	{
		$('.selectAllCampaignAffiliate').prop('checked', $(this).prop("checked"));
		$(".selectCampaignAffiliate").prop('checked', $(this).prop("checked"));

		if($('.selectCampaignAffiliate:checked').length > 0) {
			$('#editAffilatesBtn').removeAttr('disabled');
			if(currentCampaign != eiqFrameID) $('#deleteAffiliatesBtn').removeAttr('disabled');
		}else {
			$('#editAffilatesBtn').attr('disabled',true);
			$('#deleteAffiliatesBtn').attr('disabled',true);
		}
	});

	$(document).on('change','.selectCampaignAffiliate', function(e) 
	{
		var checkIfSelectAll = $('input[name="select_affiliate[]"]:not(:checked)').length == 0;
		if(checkIfSelectAll == false) $('.selectAllCampaignAffiliate').prop('checked', false);
		else $('.selectAllCampaignAffiliate').prop('checked', true);

		if($('.selectCampaignAffiliate:checked').length > 0) {
			$('#editAffilatesBtn').removeAttr('disabled');
			if(currentCampaign != eiqFrameID) $('#deleteAffiliatesBtn').removeAttr('disabled');
		}else {
			$('#editAffilatesBtn').attr('disabled',true);
			$('#deleteAffiliatesBtn').attr('disabled',true);
		}
	});

	/* EDIT Campaign Affiliate Collapse */
	$('#editCampaignAffiliateForm').on('show.bs.collapse', function () {
		var this_button = $('#editAffilatesBtn');
		this_button.html('<span class="glyphicon glyphicon-remove"></span>');	

		$('.editCampaignAffiliate').addClass('disabled');

		var checkedAffiliates = $('input[name="select_affiliate[]"]:checked');
		if(checkedAffiliates.length == 1) var edit_info = '<strong>You are about to edit the affiliate:</strong><br> ';
		else var edit_info = '<strong>You are about to edit the following affiliates:</strong><br> ';
		
		$('#selectedAffiliateDiv').html('');
		checkedAffiliates.each(function() {
			edit_info += $(this).data('name') + ', ';
			$('#selectedAffiliateDiv').append('<input name="selected_affiliate[]" type="hidden" value="'+$(this).val()+'"/>');
		});

		// $('#selected_affiliate').val(checkedAffiliates.serialize());
		// $(checkedAffiliates).attr('disabled',true);
		$('.selectCampaignAffiliate').attr('disabled',true);
		$('.selectAllCampaignAffiliate').attr('disabled',true);

		edit_info = edit_info.substring(0,edit_info.length - 2);
		edit_info += '.';
		$('.listOfCampaignAffiliatesEdit').removeClass('hidden');
		$('.listOfCampaignAffiliatesEdit .panel-body').html(edit_info).show();

		//get value of first select
		var first_id = $('input[name="select_affiliate[]"]:checked').val();
		var first_type = $('#ca-'+first_id+'-type').data('id');
		var first_value = $('#ca-'+first_id+'-value').html();
		$('#edit_lead_cap_type').val(first_type);
		$('#edit_lead_cap_value').val(first_value);
	})

	$('#editCampaignAffiliateForm').on('hide.bs.collapse', function () {
		var this_button = $('#editAffilatesBtn');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		// $('select[name="affiliates[]"] option').prop('selected',false);
		$('.editCampaignAffiliate').removeClass('disabled');

		$('.listOfCampaignAffiliatesEdit').addClass('hidden');

		$('#selectedAffiliateDiv').html('');
		$('.selectCampaignAffiliate').removeAttr('disabled')
		$('.selectAllCampaignAffiliate').removeAttr('disabled')


		// $('#editAffilatesBtn').addClass('disabled');
		// $('#deleteAffiliatesBtn').addClass('disabled');
	});

	$(document).on('click','.closeEditAffiliateCollapse', function(e) 
	{
		$('#editCampaignAffiliateForm').collapse('hide');
	});

	$(document).on('click','.editCampaignAffiliate',function(e) 
    {
		e.preventDefault();
		var id = $(this).data('id');

		$('.selectCampaignAffiliate').prop('checked',false);
		$( '.selectCampaignAffiliate[value="'+id+'"]' ).trigger( "click" );

		$('.editCampaignAffiliate').addClass('disabled');
		$('#editCampaignAffiliateForm').collapse('show');
	});

	/***
	*	Add Private Affiliate or Affiliate with cap
	*/
	$('#addPvtAff').click(function() {
		var selectedAffiliates = $('select[name="affiliates[]"] option:selected');
		var ifConf;
		var this_button = $('#addPvtAff');
		if(selectedAffiliates.length > 0) {
			if(selectedAffiliates.length == 1) ifConf = 'new affiliate';
			else ifConf = 'following affiliates';
			var confirmation = confirm('Are you sure you want to add the '+ ifConf +' ?');
			if(confirmation === true) {
				this_button.addClass('disabled');
				//this_button.html('<span class="glyphicon glyphicon-refresh gly-spin"></span>');

				var the_url = $('#baseUrl').html() + '/add_affiliate_for_campaign';
				$.ajax({
					type: 'POST',
					data: {
						'id'		:	$('#editCmpFormModal').find('#this_id.this_campaign').val(),
						'affiliate' : 	$('select[name="affiliates[]"]').val()
					},
					url: the_url,
					success: function(data){
						var dCounter = 0;
						var table = $('#campaign-affiliate-table').DataTable();
						//var lead_cap_select = $('#addCmpFormModal').find('#lead_type');
						//var lead_cap_select = $('#campaign_affiliate_lead_type_select');
						//var lead_cap_select ---> GLOBAL
						$.each(selectedAffiliates, function() {
							var aff_id = $(this).val();
							// var aff_name = $(this).html().split(' - ');
							var aff_name = $(this).html();
							var id = data[dCounter];
							$(this).remove();
							
							var leadTypeCol = '<span id="ca-'+id+'-type" data-id="0">Unlimited</span>';
								leadTypeCol += '<span id="ca-'+id+'-type-select" class="hidden"><span>';
							var leadValCol = '<span id="ca-'+id+'-value">0</span>';
								leadValCol += '<span id="ca-'+id+'-value-input" class="hidden">';
								leadValCol += '<input type="text" class="form-control full_width_form_field"/>';
								leadValCol += '</span>';
							var actionCol =  '<button id="ca-'+id+'-edit-button" class="btn btn-default editCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
								actionCol += '<button id="ca-'+id+'-update-button" class="btn btn-primary updateCampaignAffiliate hidden" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-floppy-disk"></span></button>';
								actionCol += '<button id="ca-'+id+'-cancel-button" class="btn btn-default cancelCampaignAffiliate hidden" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-circle-arrow-left"></span></button>';
								actionCol += '<button id="ca-'+id+'-delete-button" class="btn btn-default deleteCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';

							var dataRow = [
								id,
								aff_name,
								leadTypeCol,
								leadValCol,
								actionCol
							];

							//add the row data
							var rowNode = table.row.add(dataRow).draw().node();
							var affRow = $(rowNode);

							// console.log(id);
							leadType_select = $('#ca-'+id+'-type-select').html(lead_cap_select);
							// console.log(leadType_select);
							leadType_select.find('select')
								.addClass('full_width_form_field')
								.val(0)
								.removeClass('this_field')
								.removeClass('hidden')
								.removeAttr('required')
								.removeAttr('name')
								.removeAttr('id');
							dCounter++;
						});
						// this_button.html('Add');
						//this_button.removeClass('disabled');

						$('#campaignAffiliateForm').collapse('hide');

						var affiliate_count = $('select[name="affiliates[]"] option').length;
						if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);
					}
				});
			}
		}
	});
	
	$(document).on('click','.deleteCampaignAffiliate', function(e) 
	{
		e.preventDefault();
		var this_affiliate = $(this);
		var id = $(this).data('id');

		$('input[name="select_affiliate[]"]').prop('checked', false);
		$('input[name="select_affiliate[]"][value="'+id+'"]').prop('checked',true);

		console.log( $('input[name="select_affiliate[]"]').serialize() );

		$('input[name="select_affiliate[]"][value="'+id+'"]').parents('tr').css('background-color','#ebccd1');

		var confirmation = confirm('Are you sure you want to delete this Affiliate?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_campaign_affiliate';
			$.ajax({
				type : 'POST',
				url  : the_url,
 				data : $('input[name="select_affiliate[]"]').serialize(), 
				success : function(data) {
					// console.log(data);
					var table = $('#campaign-affiliate-table').DataTable();
					table.row(this_affiliate.parents('tr')).remove().draw();

					$('select[name="affiliates[]"] option').remove();
					var aff_select = $('select[name="affiliates[]"]');
					$.each(data, function(index,value){
						aff_select.append('<option value="'+index+'">'+value+'</option>');
					});		
					var affiliate_count = $('select[name="affiliates[]"] option').length;
					if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);
				}
			});
		}else {
			$('input[name="select_affiliate[]"][value="'+id+'"]').parents('tr').css('background-color','');
		}
	});

	$(document).on('click','#deleteAffiliatesBtn', function() 
	{	
		var selectedAffiliates = $('input[name="select_affiliate[]"]:checked');

		selectedAffiliates.each(function() {
			var id = $(this).val();
			$('input[name="select_affiliate[]"][value="'+id+'"]').parents('tr').css('background-color','#ebccd1');
		});

		var confirmation = confirm('Are you sure you want to delete the ff. campaign affiliates?');
		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_campaign_affiliate';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : selectedAffiliates.serialize(), 
				success : function(data) {
					var table = $('#campaign-affiliate-table').DataTable();
					selectedAffiliates.each(function() {
						var id = $(this).val();
						table.row($('input[name="select_affiliate[]"][value="'+id+'"]').parents('tr')).remove().draw();
					});

					// console.log(data);
					$('select[name="affiliates[]"] option').remove();
					var aff_select = $('select[name="affiliates[]"]');
					$.each(data, function(index,value){
						aff_select.append('<option value="'+index+'">'+value+'</option>');
					});		
					var affiliate_count = $('select[name="affiliates[]"] option').length;
					if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);

					$('.selectAllCampaignAffiliate').prop('checked', false);
					$('.selectAllCampaignAffiliate').trigger('change');
				}
			});
		}else {
			selectedAffiliates.each(function() {
				var id = $(this).val();
				$('input[name="select_affiliate[]"][value="'+id+'"]').parents('tr').css('background-color','');
			});
		}
	});

	$(document).on('click','.deleteCampaignPayout', function(e) 
	{	
		e.preventDefault();
		var this_filter = $(this);
		var id = $(this).data('id');

		$('input[name="select_payout[]"]').prop('checked', false);
		$('input[name="select_payout[]"][value="'+id+'"]').prop('checked',true);

		// console.log( $('input[name="select_payout[]"]').serialize() );

		$('input[name="select_payout[]"][value="'+id+'"]').parents('tr').css('background-color','#ebccd1');

		var confirmation = confirm('Are you sure you want to delete this campaign payout?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_campaign_payout';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : $('input[name="select_payout[]"]').serialize(), 
				success : function(data) {
					var table = $('#campaign-payout-table').DataTable();
					table.row(this_filter.parents('tr')).remove().draw();
					$('#editPytAff').attr('disabled',true);
					$('#deletePytAff').attr('disabled',true);
				}
			});
		}else {
			$('input[name="select_payout[]"][value="'+id+'"]').parents('tr').css('background-color','');
		}
	});	

	/***
	*	Update Value based on Value Type
	*/
	$('.filter_value_type').on('change', function() {
		var value = $(this).val();
		
		var text01 = $('#val-1-text-wrapper');
		var select01 = $('#val-1-select-wrapper');
		var input01 = $('#val-1-input-wrapper');
		var date01 = $('#val-1-date-wrapper');
		var array01 = $('#val-1-array-wrapper');
		var time01 = $('#val-1-time-wrapper');
		var input02 = $('#val-2-input-wrapper');
		var date02 = $('#val-2-date-wrapper');
		var time02 = $('#val-2-time-wrapper');
		var wrapper02 = $('#filter_value_02_wrapper');
		var label01 = $('label[for="filter_value_01"]');
		var label02 = $('label[for="filter_value_02"]');

		//Clear Values
		input01.find('input').val('');
		select01.find('select').val('');
		date01.find('input').val('');
		time01.find('input').val('');
		input02.find('input').val('');
		date02.find('input').val('');
		array01.find('textarea').val('');
		time02.find('input').val('');

		//Clear Required
		text01.find('input').removeAttr('required');
		input01.find('input').removeAttr('required');
		select01.find('select').removeAttr('required');
		date01.find('input').removeAttr('required');
		time01.find('input').removeAttr('required');
		input02.find('input').removeAttr('required');
		date02.find('input').removeAttr('required');
		array01.find('textarea').removeAttr('required');
		time02.find('input').removeAttr('required')

		if(value == 6) {
			//Time
			text01.addClass('hidden');
			select01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			date01.addClass('hidden');
			time01.removeClass('hidden');
			time01.find('input').attr('required',true);
			
			input02.addClass('hidden');
			date02.addClass('hidden');
			time02.removeClass('hidden');
			time02.find('input').attr('required',true);

			wrapper02.removeClass('hidden');

			label01.html('Minimum Time');
			label02.html('Maximum Time');
		}
		else if(value == 5) {
			//Array
			select01.addClass('hidden');
			date01.addClass('hidden');
			input01.addClass('hidden');
			text01.addClass('hidden');
			time01.addClass('hidden');
			array01.removeClass('hidden');
			array01.find('textarea').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('Array');
		}else if(value == 4) {
			//Number
			text01.addClass('hidden');
			select01.addClass('hidden');
			date01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			input01.removeClass('hidden');
			input01.find('input').attr('required',true);

			date02.addClass('hidden');
			time02.addClass('hidden');
			input02.removeClass('hidden');
			input02.find('input').attr('required',true);

			wrapper02.removeClass('hidden');

			label01.html('Minimum Number');
			label02.html('Maximum Number');
		}else if(value == 3) {
			//Date
			text01.addClass('hidden');
			select01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			date01.removeClass('hidden');
			date01.find('input').attr('required',true);
			
			input02.addClass('hidden');
			time02.addClass('hidden');
			date02.removeClass('hidden');
			date02.find('input').attr('required',true);

			wrapper02.removeClass('hidden');

			label01.html('Minimum Date');
			label02.html('Maximum Date');
		}else if(value == 2) {
			//Boolean
			text01.addClass('hidden');
			input01.addClass('hidden');
			date01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			select01.removeClass('hidden');
			select01.find('select').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('Value');
		}else {
			//Text
			select01.addClass('hidden');
			date01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			text01.removeClass('hidden');
			text01.find('input').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('Value');
		}
	});

	/** 
	* Close Filter Form Collapse
	*/
	$('.closeFilterCollapse').click(function() {
		$('#campaignFilterForm').collapse('hide');
		var this_button = $('#addCmpFilter');
		this_button.html('<span class="glyphicon glyphicon-plus"></span>');
		this_button.attr('data-collapse','show');
		this_button.data('collapse','show');
	});

	/**
	* Display data of campaign filter for editing
	*/
	$(document).on('click','.editCampaignFilter', function() 
	{
		var this_modal = $('#campaignFilterForm');
		var url = $('#baseUrl').html() + '/edit_campaign_filter';

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','edit_campaign_filter');
		this_modal.find('.this_form').attr('data-process', 'edit_campaign_filter');
		this_modal.find('.this_form').data('confirmation','Are you sure you want to edit this filter?');
		this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to edit this filter?');
		$('#for_filter_type').html('Edit Filter');

		var this_filter = $(this);
		var id = this_filter.data('id');
		var value_type = this_filter.data('vtype');
		var value01 = this_filter.data('value01');
		var value02 = this_filter.data('value02');
		var form_button = $('#addCmpFilter');
		var form = $('#campaignFilterForm');

		// console.log(value01 + ' - ' + value02);
		
		form_button.html('<span class="glyphicon glyphicon-remove"></span>');
		form_button.attr('data-collapse','hide');
		form_button.data('collapse','hide');
		form.collapse('show');
		form.find('#this_id').val(id);

		form.find('#filter_type').val(this_filter.data('ftype'));
		form.find('input[name="value_type"][value="'+value_type+'"]').prop('checked', true);
		$('input[name="value_type"]:checked').trigger('change');

		switch(parseInt(value_type)) {
			case 1:
				form.find('input[name="filter_value_01_text"]').val(value01);
				break;
			case 2:
				form.find('select[name="filter_value_01_select"]').val(value01);
				break;
			case 3:
				form.find('input[name="filter_value_01_date"]').val(value01);
				form.find('input[name="filter_value_02_date"]').val(value02);
				break;
			case 4:
				form.find('input[name="filter_value_01_input"]').val(value01);
				form.find('input[name="filter_value_02_input"]').val(value02);
				break;
			case 5:
				form.find('textarea[name="filter_value_01_array"]').val(value01);
				break;
			case 6:
				form.find('input[name="filter_value_01_time"]').val(value01);
				form.find('input[name="filter_value_02_time"]').val(value02);
				break;
		}

		$('#filter_type').focus();
	});
	
	/**
	* Close Filter Collapse
	*/
	$('#campaignFilterForm').on('hidden.bs.collapse', function (event) 
	{
		// console.log('wat');
		var form = $(this).find('.this_form');

		form.find('.this_field').each(function() 
		{
			if($(this).is(':radio')) {
				//check datepicker
				$('input[name="value_type"][value="1"]').prop('checked',true);
				$('input[name="value_type"]:checked').trigger('change');
			}
			// else {
			// 	$(this).val('');
			// }
		});

		form.find('#filter_type').val('');
		form.find('#filter_value_01_text').val('');
		form.find('#filter_value_01_select').val('');
		form.find('#filter_value_01_date').val('');
		form.find('#filter_value_02_date').val('');
		form.find('#filter_value_01_input').val('');
		form.find('#filter_value_02_input').val('');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});
		form.find('.this_error_wrapper').hide();
		$('label[for="for_filter_type"]').html('Add Filter');
	});

	/* PAYOUT */

	$('.capPytSelectAllAff').on('change', function (event) 
	{
		$('.capPytSelectAllAff').prop('checked', $(this).prop("checked"));
		$(".selectCampaignPayout").prop('checked', $(this).prop("checked"));
		
		if($('.selectCampaignPayout:checked').length > 0) {
			$('#editPytAff').removeAttr('disabled');
			$('#deletePytAff').removeAttr('disabled');
		}else {
			$('#editPytAff').attr('disabled',true);
			$('#deletePytAff').attr('disabled',true);
		}
	});

	/** 
	* Open Add Payout Form Collapse
	*/
	$('#campaignPayoutForm').on('show.bs.collapse', function () {
		var this_button = $('#addPytAff');
		this_button.html('<span class="glyphicon glyphicon-remove"></span>');
		$('#editCampaignPayoutForm').collapse('hide');
		$('label[for="payout_title"]').html('Add Affiliate Payout');
	});

	/** 
	* Close Add Payout Form Collapse
	*/
	$('#campaignPayoutForm').on('hide.bs.collapse', function () {
		$('#payout_receivable').val('');
		$('#payout_payable').val('');
		var this_button = $('#addPytAff');
		this_button.html('<span class="glyphicon glyphicon-plus"></span>');
	});

	$('.closePayoutCollapse').click(function() {
		$('#campaignPayoutForm').collapse('hide');
	});

	$(document).on('change','.selectCampaignPayout', function(e) 
	{
		var checkIfSelectAll = $('input[name="select_payout[]"]:not(:checked)').length == 0;
		if(checkIfSelectAll == false) $('.capPytSelectAllAff').prop('checked', false);
		else $('.capPytSelectAllAff').prop('checked', true);

		//console.log($('.selectCampaignPayout:checked').length);
		$('#editCampaignPayoutForm').collapse('hide');
		if($('.selectCampaignPayout:checked').length > 0) {
			$('#editPytAff').removeAttr('disabled');
			$('#deletePytAff').removeAttr('disabled');
		}else {
			$('#editPytAff').attr('disabled',true);
			$('#deletePytAff').attr('disabled',true);
		}
	});

	$('#editCampaignPayoutForm').on('show.bs.collapse', function () {
		var this_button = $('#editPytAff');
		this_button.html('<span class="glyphicon glyphicon-remove"></span>');
		$('#campaignPayoutForm').collapse('hide');
		$('label[for="payout_title"]').html('Edit Affiliate Payout');

		var checkedAffiliates = $('input[name="select_payout[]"]:checked');
		if(checkedAffiliates.length == 1) var edit_info = '<strong>You are about to edit the affiliate:</strong><br> ';
		else var edit_info = '<strong>You are about to edit the following affiliates:</strong><br> ';
		
		$('#selectedPayoutDiv').html('');
		checkedAffiliates.each(function() {
			edit_info += $(this).data('name') + ', ';
			$('#selectedPayoutDiv').append('<input name="selected_payout[]" type="hidden" value="'+$(this).val()+'"/>');
		});

		edit_info = edit_info.substring(0,edit_info.length - 2);
		edit_info += '.';
		$('.listOfCampaignAffiliates').removeClass('hidden');
		$('.listOfCampaignAffiliates .panel-body').html(edit_info).show();

		//get value of first select
		var first_id = $('input[name="select_payout[]"]:checked').val();
		var receivable = $('#cp-'+first_id+'-receivable').html();
		var payable = $('#cp-'+first_id+'-payable').html();
		$('#edit_payout_receivable').val(receivable);
		$('#edit_payout_payable').val(payable);

		$('.selectCampaignPayout').attr('disabled',true);
		$('.capPytSelectAllAff').attr('disabled',true);		
		$('.editCampaignPayout').attr('disabled',true);

	});

	$('#editCampaignPayoutForm').on('hide.bs.collapse', function () {
		var this_button = $('#editPytAff');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		$('label[for="payout_title"]').html('Add Affiliate Payout');
		$('.editCampaignPayout').removeClass('disabled');
		$('.listOfCampaignAffiliates').addClass('hidden');

		$('.selectCampaignPayout').removeAttr('disabled');
		$('.capPytSelectAllAff').removeAttr('disabled');
		$('.editCampaignPayout').removeAttr('disabled');
		$('#selectedPayoutDiv').html('');
	});

	$('.closeEditPayoutCollapse').click(function() {
		$('#editCampaignPayoutForm').collapse('hide');
	});

	$(document).on('click','.editCampaignPayout', function(e) 
	{	
		$('.editCampaignPayout').addClass('disabled');
		$('#editCampaignPayoutForm').collapse('hide');
		var id = $(this).data('id');
		$('.selectCampaignPayout').prop('checked',false);
		// $('.selectCampaignPayout[value="'+id+'"]').prop('checked',true);
		$( '.selectCampaignPayout[value="'+id+'"]' ).trigger( "click" );
		$('#editCampaignPayoutForm').collapse('show');
	});

	$(document).on('click','#deletePytAff', function() 
	{	
		var selectedAffiliates = $('input[name="select_payout[]"]:checked');

		selectedAffiliates.each(function() {
			var id = $(this).val();
			$('input[name="select_payout[]"][value="'+id+'"]').parents('tr').css('background-color','#ebccd1');
		});

		var confirmation = confirm('Are you sure you want to delete the ff. campaign payouts?');
		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_campaign_payout';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : selectedAffiliates.serialize(), 
				success : function(data) {
					var table = $('#campaign-payout-table').DataTable();
					selectedAffiliates.each(function() {
						var id = $(this).val();
						table.row($('input[name="select_payout[]"][value="'+id+'"]').parents('tr')).remove().draw();
						$('#editPytAff').attr('disabled',true);
						$('#deletePytAff').attr('disabled',true);
					});
				}
			});
		}else {
			selectedAffiliates.each(function() {
				var id = $(this).val();
				$('input[name="select_payout[]"][value="'+id+'"]').parents('tr').css('background-color','');
			});
		}
	});

	/* CONFIG */
	
	$(document).on('click','#editCmpConfig', function() 
	{
		var this_button = $(this);
		var display = $(this).data('config');

		if(display == 'show') {
			this_button.html('<span class="glyphicon glyphicon-remove"></span>');
			this_button.attr('data-config','hide').data('config','hide');
			$('#campConfigDiv').removeClass('hidden');
			$('.cmpCfg-form').removeClass('hidden');
			$('.cmpCfg-dsply').addClass('hidden');
		}else {
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			$('#campConfigDiv').addClass('hidden');
			$('.cmpCfg-form').addClass('hidden');
			$('.cmpCfg-dsply').removeClass('hidden');
		}
	});

	$('.cancelCmpConfigEdit').click(function() {
		var this_button = $('#editCmpConfig');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		$('#campConfigDiv').addClass('hidden');
		$('.cmpCfg-form').addClass('hidden');
		$('.cmpCfg-dsply').removeClass('hidden');
	});

	$('body').on('hidden.bs.modal', '#cmpConfigAutomationModal', function (e) 
	// $('#cmpConfigAutomationModal').on('hide.bs.modal', function (event) 
	{
		var form = $(this).find('form');
		form.find('.this_field').val('');
		$('.postFieldTr').remove();
		$('#cmpConfigAutomationTable').hide();
		$('#cmpConfigAutomationSubmit').removeClass('disabled');
	});

	function addConfigField(field, value, count, ifFixed) {
		var fixed_selected = '',
			fixed_displayed = 'style="display: none"';

		if(ifFixed) {
			fixed_selected = 'selected';
			fixed_displayed = '';
		}
		return '<tr class="postFieldTr"><th>' + field + '</th>' +
            '<td>' +  
            '<select id="field['+ field +']" name="field['+ field +']" class="fieldSelect form-control this_field" data-value="'+count+'" required>' +
                '<option value="">Select Fields name</option>' +
                '<option value="first_name">first_name</option>' +
                '<option value="last_name">last_name</option>' +
                '<option value="zip">zip</option>' +
                '<option value="city">city</option>' +
                '<option value="state">state</option>' +
                '<option value="ip">ip</option>' +
                '<option value="age">age</option>' +
                '<option value="rev_tracker">rev_tracker</option>' +
                '<option value="eiq_email">eiq_email</option>' +
                '<option value="dob">dob</option>' +
                '<option value="dobmonth">dobmonth</option>' +
                '<option value="dobday">dobday</option>' +
                '<option value="dobyear">dobyear</option>' +
                '<option value="gender">gender</option>' +
             	'<option value="phone">phone</option>' +
             	'<option value="address">address</option>' +
             	'<option value="address1">address1</option>' +
                '<option value="datetime">datetime</option>' +
                '<option value="q1">q1</option>' +
                '<option value="q2">q2</option>' +
                '<option value="q3">q3</option>' +
                '<option value="q4">q4</option>' +
                '<option value="q5">q5</option>' +
                '<option value="q6">q6</option>' +
                '<option value="q7">q7</option>' +
                '<option value="q8">q8</option>' +
                '<option value="q9">q9</option>' +
                '<option value="q10">q10</option>' +
                '<option value="q11">q11</option>' +
                '<option value="q12">q12</option>' +
                '<option value="q13">q13</option>' +
                '<option value="q14">q14</option>' +
                '<option value="q15">q15</option>' +
                '<option value="q16">q16</option>' +
                '<option value="q17">q17</option>' +
                '<option value="q18">q18</option>' +
                '<option value="q19">q19</option>' +
                '<option value="q20">q20</option>' +
                '<option value="q21">q21</option>' +
                '<option value="q22">q22</option>' +
                '<option value="q23">q23</option>' +
                '<option value="q24">q24</option>' +
                '<option value="q25">q25</option>' +
                '<option value="q26">q26</option>' +
                '<option value="q27">q27</option>' +
                '<option value="q28">q28</option>' +
                '<option value="q29">q29</option>' +
                '<option value="q30">q30</option>' +
                '<option value="comments">comments</option>' +
                '<option value="comment2">comment2</option>' +
                '<option value="xxTrustedFormCertUrl">xxTrustedFormCertUrl</option>' +
                '<option value="fixed_data" '+fixed_selected+'>Fixed Data</option>' +
            '</select>'+
            '<span id="fieldInput-'+ count +'" class="fieldInput" '+fixed_displayed+'><input  type="text" name="fixed['+ field +']" id="fixed['+ field +']" placeholder="Enter Fix Value" class="form-control this_field" value="'+value+'"/>'+
            '</span>'+
            '</td></tr>';
	}

	$(document).on('click','#cmpConfigAutomationBtn', function() 
	{
		var post_url = $('#cmpCfg-url-txt input').val(),
			post_header = $('#cmpCfg-hdr-txt textarea').val(),
			post_data = $('#cmpCfg-dta-txt textarea').val(),
			post_data_fixed = $('#cmpCfg-dta-fv-txt textarea').val(),
			post_data_map = $('#cmpCfg-map-txt textarea').val(),
			post_method = $('#cmpCfg-mtd-txt select').val(),
			post_success = $('#cmpCfg-scs-txt input').val(),
			form = $('#campaign_config_interface_form'),
			campaign_id = $('#this_id.this_campaign').val();

		form.find('.this_campaign').val(campaign_id);

		if(post_url != '' || post_header != '' || post_data != '' || post_data_fixed != '' || post_data_map != '' 
			|| post_method != null || post_success != '' ) {
			$('#cmpConfigAutomationTable').show();
			$('#cmpConfigAutomationSubmit').removeClass('disabled');

			form.find('#post_url').val(post_url);
			form.find('#post_header').val(post_header);
			form.find('#post_data_map').val(post_data_map);
			form.find('#post_method').val(post_method);
			form.find('#post_success').val(post_success);

			var posting_url = post_url + '?';
			var post_data_counter = 0;

			if(post_data_fixed != '' && post_data_fixed.includes('{') && post_data_fixed.includes('}')) {
				var post_data_fixed_json = jQuery.parseJSON(post_data_fixed);
				
				var post_data_fixed_counter = 0;
				$.each(post_data_fixed_json, function( field, value ) {

					posting_url += field + '=' + value + '&';
				  	var fieldData = addConfigField(field, value, post_data_counter, true);
		    		$('#cmpConfigAutomationTable tbody #postDataMapDiv').before(fieldData);
		    		post_data_fixed_counter++;
		    		post_data_counter++;
				});
			}

			if(post_data != '' && post_data.includes('{') && post_data.includes('}')) {
				var post_data_json = jQuery.parseJSON(post_data);

				// var post_data_counter = 0;
				$.each(post_data_json, function( our, adv ) {
					posting_url += adv + '=' + our + '&';
				  	var fieldData = addConfigField(adv, '', post_data_counter, false);
				  	
		    		$('#cmpConfigAutomationTable tbody #postDataMapDiv').before(fieldData);
		    		if($('select[name="field['+ adv +']"] option[value="'+our+'"]').length > 0) {
		    			$('select[name="field['+ adv +']"]').val(our);
		    		}else {
		    			$('select[name="field['+ adv +']"]').prepend('<option value="'+our+'" selected>'+our+'</option>');
		    		}
		    		post_data_counter++;
				});
			}

			if(posting_url.substring(posting_url.length-1) == "&") {
				posting_url = posting_url.substring(0, posting_url.length-1);
			}

			form.find('#posting_url').val(posting_url);
		}else {
			$('#cmpConfigAutomationSubmit').addClass('disabled');
		}

		$('#cmpConfigAutomationModal').modal('show');
	});

	$(document).on('click','#cmpConfigAutomationGenerate', function() 
	{
		var form = $('#campaign_config_interface_form'),
			posting_url = form.find('#posting_url').val();

		$('.postFieldTr').remove();
		if(posting_url.length != '') {
			var getUrl = posting_url.substring(0,posting_url.indexOf('?')),
		   		hashes = posting_url.substring( posting_url.indexOf('?') + 1 ).split('&'),
		   		vars = [],
		   		hash;
		   
		    // for (var i = 0; i < hashes.length; i++) {
		    for (var i = (hashes.length - 1); i >= 0; i--) {
		        var hash = hashes[i].split('='),
		        	fieldName = hash[0];
		        vars.push(hash[0]);
		        // vars[hash[0]] = hash[1] + " ";

		        var fieldData = addConfigField(fieldName, '', i, false);
                        
            	$('#cmpConfigAutomationTable tbody #postHeaderDiv').after(fieldData);
		    } 
			// console.log(getUrl);    
	  //   	console.log(vars);

	    	form.find('#post_url').val(getUrl);

	    	$('#cmpConfigAutomationTable').show();

	    	$('#cmpConfigAutomationSubmit').removeClass('disabled');
		}else {
			alert("Please enter a posting url!");
		}
		   	
	});

	var field_selected = [],
		field_choices = [
			'first_name',
			'last_name',
			'zip',
			'city',
			'state',
			'ip',
			'age',
			'rev_tracker',
			'eiq_email',
			'dob',
			'gender',
			'phone',
			'address',
			'datetime',
			'q1',
			'q2',
			'q3',
			'q4',
			'q5',
			'q6',
			'q7',
			'q8',
			'q9',
			'q10',
			'fixed_data'
		];

	$(document).on('change','.fieldSelect',function() 
	{
	    var forField = $(this).data('value'),
	    	value = $(this).val();
	    if( value =="fixed_data"){
	        $('#fieldInput-'+forField+'.fieldInput').show();
	        $('#fieldInput-'+forField+'.fieldInput').find('input').attr('required', true);
	    }
	    else{
	        $('#fieldInput-'+forField+'.fieldInput').hide();
	        $('#fieldInput-'+forField+'.fieldInput').find('input').removeAttr('required');
	    }

	    var field_selected = $.map($('.fieldSelect'), function (el) { 
        	if(el.value != '' && el.value != 'fixed_data') {
        		return el.value;
        	}
        });
        console.log(field_selected);

        $('.fieldSelect').each(function(){
        	var this_value = $(this).val();
        	console.log(this_value);
        	$(this).find('option').each(function() {
    			if(this_value != $(this).val() && field_selected.indexOf($(this).val()) >= 0) {
    				$(this).addClass('hidden');
    			}else{
    				$(this).removeClass('hidden');
    			}
        	});
	    });
	});


	/* CONTENT */
	
	$(document).on('submit','.noSendForm', function(e) 
	{
		e.preventDefault();
	});

	$(document).on('click','#editCampaignLongContent', function() 
	{

		var this_button = $(this);
		var display = $(this).data('config');

		if(display == 'show') {
			this_button.html('<span class="glyphicon glyphicon-remove"></span>');
			this_button.attr('data-config','hide').data('config','hide');
			$('.cmpLngCnt-form-wrapper').removeClass('hidden');
			$('#cmpCnt-long-content').removeAttr('disabled');
			
		}else {
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			$('.cmpLngCnt-form-wrapper').addClass('hidden');
			$('#cmpCnt-long-content').attr('disabled',true);
			$('#cmpCnt-long-content').val($('#cmpCnt-long-actual').val());
		}
	});

	$('.cancelCampaignLongContentEdit').click(function() {
		var this_button = $('#editCampaignLongContent');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpLngCnt-form-wrapper').addClass('hidden');
		$('#cmpCnt-long-content').attr('disabled',true);
		$('#cmpCnt-long-content').val($('#cmpCnt-long-actual').val());
	});

	$(document).on('click','#editCampaignStackContent', function() 
	{
		var this_button = $(this);
		var display = $(this).data('config');

		if(display == 'show') {
			this_button.html('<span class="glyphicon glyphicon-remove"></span>');
			this_button.attr('data-config','hide').data('config','hide');
			//$('.cmpCnt-dsply-wrapper').addClass('hidden');
			$('.cmpStkCnt-form-wrapper').removeClass('hidden');
			$('#cmpCnt-stack-content').removeAttr('disabled');
			stackCodeMirror.setOption("readOnly", false);
		}else {
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			$('.cmpStkCnt-form-wrapper').addClass('hidden');
			$('#cmpCnt-stack-content').attr('disabled',true);
			$('#cmpCnt-stack-content').val($('#cmpCnt-stack-actual').val());
			stackCodeMirror.getDoc().setValue($('#cmpCnt-stack-actual').val());
			setTimeout(function() {
				stackCodeMirror.refresh();
				stackCodeMirror.setOption("readOnly", true);
			},100);
		}

		$('#stack_content_tab .this_error_wrapper').hide();
		$('#stack_content_tab .this_error_wrapper .this_errors ul').remove();
	});

	$('.cancelCampaignStackContentEdit').click(function() {
		var this_button = $('#editCampaignStackContent');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpStkCnt-form-wrapper').addClass('hidden');
		$('#cmpCnt-stack-content').attr('disabled',true);
		$('#cmpCnt-stack-content').val($('#cmpCnt-stack-actual').val());
		stackCodeMirror.getDoc().setValue($('#cmpCnt-stack-actual').val());
		setTimeout(function() {
			stackCodeMirror.refresh();
			stackCodeMirror.setOption("readOnly", true);
		},100);

		$('#stack_content_tab .this_error_wrapper').hide();
		$('#stack_content_tab .this_error_wrapper .this_errors ul').remove();
	});

	$(document).on('click','#editCampaignHighPayingContent', function() 
	{

		var this_button = $(this);
		var display = $(this).data('config');

		if(display == 'show') {
			this_button.html('<span class="glyphicon glyphicon-remove"></span>');
			this_button.attr('data-config','hide').data('config','hide');
			//$('.cmpCnt-dsply-wrapper').addClass('hidden');
			$('.cmpHPCnt-form-wrapper').removeClass('hidden');
			$('#cmpCnt-hp-content').removeAttr('disabled');
			
		}else {
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			$('.cmpHPCnt-form-wrapper').addClass('hidden');
			$('#cmpCnt-hp-content').attr('disabled',true);
			$('#cmpCnt-hp-content').val($('#cmpCnt-hp-actual').val());
		}
	});

	$('.cancelCampaignHighPayingContentEdit').click(function() {
		var this_button = $('#editCampaignHighPayingContent');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpHPCnt-form-wrapper').addClass('hidden');
		$('#cmpCnt-hp-content').attr('disabled',true);
		$('#cmpCnt-hp-content').val($('#cmpCnt-hp-actual').val());
	});
	
	$(document).on('click','.prvCampaignContent', function(e) 
	{	
		e.preventDefault();
		var type = $(this).data('type');
		var url = $(this).attr('href');
		var newForm = $('<form>', {
	        'action': url,
	        'target': '_blank',
	        'method' : 'POST'
	    }).append($('<input>', {
	        'name': 'content',
	        'value': $('#cmpCnt-content').val(),
	        'type': 'hidden'
	    })).append($('<input>',{
	    	'name': '_token',
	    	'value': $('meta[name="_token"]').attr('content'),
	    	'type': 'hidden'
	    })).append($('<input>',{
	    	'name': 'type',
	    	'value': type,
	    	'type': 'hidden'
	    }));
	  
	    newForm.submit();
	});

	$(document).on('click','.prvCmpCnt', function(e) {
		e.preventDefault();
		var id = $('#this_campaign').val();
		var url = $(this).attr('href') + id;
		var win = window.open(url, '_blank');
 		win.focus();
	});

	//SORT CAMPAIGNS
	var sorted_campaigns = [];
	var sorted = $( "#campaign_sortable" ).sortable({
		update: function (event, ui) {
			sorted_campaigns = $(this).sortable('serialize');
	        $('.saveSortedCampaignsBtn').show();
    	},
    	change: function (event, ui) {
			$(ui.item).addClass('selected');
    	}
	});	

	$(document).on('click','#sortCmpBtn', function(e) 
	{	
		e.preventDefault();

		var this_button = $(this);
		this_button.find('span').removeClass().addClass('fa fa-spin fa-spinner');
		$('#sortByPriorityBtn').addClass('active');
		$('#sortByRevenueBtn').removeClass('active');

		var the_url = $('#baseUrl').html() + '/get_campaigns_for_sort';

		$('#campaign_sortable tr').remove();

		$.ajax({
			type : 'POST',
			url  : the_url,
			success : function(campaigns) {
				this_button.find('span').removeClass().addClass('glyphicon glyphicon-sort-by-attributes');
				$('#current_sorting').html();
				$.each(campaigns, function(i, campaign) {
					$('#campaign-priority-sort').append('<tr id="cmpprt-'+campaign.id+'"><td>'+campaign.priority+'</td><td>'+campaign.name+'</td><td>'+campaign.campaign_type+'</td><td>'+campaign.status+'</td></tr>');
			    	$('#current_sorting').append('<input type="hidden" name="old_prio[]" value="'+campaign.id+'">');
			    });
			    $('#sortCmpModal').modal('show');
			}
		});
	});
	
	/* SORT BY PRIORITY */
	$(document).on('click','#sortByPriorityBtn', function(e) 
	{	
		e.preventDefault();

		var this_button = $(this);
		this_button.find('span').removeClass().addClass('fa fa-spin fa-spinner');

		var the_url = $('#baseUrl').html() + '/get_campaigns_for_sort';
		
		$('#campaign_sortable tr').remove();

		$.ajax({
			type : 'POST',
			url  : the_url,
			success : function(campaigns) {
				$('#sortByRevenueBtn').removeClass('active');
				this_button.addClass('active');
				this_button.find('span').removeClass().addClass('glyphicon glyphicon-sort-by-attributes');
				$('#current_sorting').html();
				$.each(campaigns, function(i, campaign) {
					$('#campaign-priority-sort').append('<tr id="cmpprt-'+campaign.id+'"><td>'+campaign.priority+'</td><td>'+campaign.name+'</td><td>'+campaign.campaign_type+'</td><td>'+campaign.status+'</td></tr>');
			    	$('#current_sorting').append('<input type="hidden" name="old_prio[]" value="'+campaign.id+'">');
			    });
			    sorted_campaigns = $( "#campaign_sortable" ).sortable('serialize');
			    $('.saveSortedCampaignsBtn').show();
			}
		});
	});

	/* SORT BY REVENUE */
	$(document).on('click','#sortByRevenueBtn', function(e) 
	{	
		e.preventDefault();

		var this_button = $(this);
		this_button.find('span').removeClass().addClass('fa fa-spin fa-spinner');

		var the_url = $('#baseUrl').html() + '/get_campaigns_by_revenue_for_sort';
		
		$('#campaign_sortable tr').remove();

		$.ajax({
			type : 'POST',
			url  : the_url,
			success : function(campaigns) {
				$('#sortByPriorityBtn').removeClass('active');
				this_button.addClass('active');
				this_button.find('span').removeClass().addClass('glyphicon glyphicon-sort-by-attributes');
				$('#current_sorting').html();
				$.each(campaigns, function(i, campaign) {
					$('#campaign-priority-sort').append('<tr id="cmpprt-'+campaign.id+'"><td>'+campaign.priority+'</td><td>'+campaign.name+'</td><td>'+campaign.campaign_type+'</td><td>'+campaign.status+'</td></tr>');
			    	$('#current_sorting').append('<input type="hidden" name="old_prio[]" value="'+campaign.id+'">');
			    });
			    sorted_campaigns = $( "#campaign_sortable" ).sortable('serialize');
			    $('.saveSortedCampaignsBtn').show();
			}
		});
	});

	$(document).on('click','.saveSortedCampaignsBtn', function(e) 
	{	
		e.preventDefault();

		var this_button = $('.saveSortedCampaignsBtn');
		this_button.html('<span class="fa fa-spin fa-spinner"></span>');

		var the_url = $('#baseUrl').html() + '/update_campaigns_priority';

		// console.log(sorted_campaigns);
		// console.log($('input[name="old_prio[]"]').serialize());
		$.ajax({
			type : 'POST',
			url  : the_url,
			data : {
				campaigns : sorted_campaigns,
				current_sort : $('input[name="old_prio[]"]').serialize()
			},
			success : function(campaign_priority) {
				$('#campaign_sortable tr').remove();
				$('#current_sorting').empty();
				$.each(campaign_priority, function(i, new_sort) {
					$('#campaign-priority-sort').append('<tr id="cmpprt-'+new_sort.id+'"><td>'+new_sort.priority+'</td><td>'+new_sort.name+'</td><td>'+new_sort.campaign_type+'</td><td>'+new_sort.status+'</td></tr>');
			    	$('#current_sorting').append('<input type="hidden" name="old_prio[]" value="'+new_sort.id+'">');
			    });
				this_button.html('Update');
				//this_button.hide();
				$('#campaign_sortable tr').removeClass('selected');
				campaign_datatable.ajax.reload(); 
			}
		});
	});
	
	/* FILTER TYPE */
	/***
	*	Show filter form
	*/
	$('#addCmpFilter').click(function() {

		var this_modal = $('#campaignFilterForm');
		var url = $('#baseUrl').html() + '/add_campaign_filter';

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','add_campaign_filter');
		this_modal.find('.this_form').attr('data-process', 'add_campaign_filter');
		this_modal.find('.this_form').data('confirmation','');
		this_modal.find('.this_form').attr('data-confirmation', '');
		$('#for_filter_type').html('Add Filter');
		$('label[for="filter_group"]').html('Add to Filter Group');
		$('.edit-filter-filter-group-help-block').addClass('hidden');
		this_modal.find('#this_id').val('');
	});

	/**
	* Close Filter Collapse
	*/
	$('#campaignFilterForm').on('hide.bs.collapse', function (event) 
	{
		// console.log('wat');
		var form = $(this).find('.this_form');

		form.find('.this_field').each(function() 
		{
			if($(this).is(':radio')) {
				$('input[name="value_type"][value="1"]').prop('checked',true);
				$('input[name="value_type"]:checked').trigger('change');
			}
		});

		form.find('#filter_type').val('');
		form.find('#filter_value_01_text').val('');
		form.find('#filter_value_01_select').val('');
		form.find('#filter_value_01_date').val('');
		form.find('#filter_value_02_date').val('');
		form.find('#filter_value_01_input').val('');
		form.find('#filter_value_02_input').val('');
		form.find('.filterGroupList').val('');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});
		form.find('.this_error_wrapper').hide();
		$('label[for="for_filter_type"]').html('Add Filter');

		var this_button = $('#addCmpFilter');
		this_button.html('<span class="glyphicon glyphicon-plus"></span> Filter');

	});
	
	/**
	* Show Filter Collapse
	*/
	$('#campaignFilterForm').on('show.bs.collapse', function (event) 
	{
		$('#campaignFilterGroupForm').collapse('hide');

		var this_button = $('#addCmpFilter');
		this_button.html('<span class="glyphicon glyphicon-remove"></span> Filter');
	});

	/** 
	* Close Filter Form Collapse
	*/
	$('.closeFilterCollapse').click(function() {
		$('#campaignFilterForm').collapse('hide');
	});

	/***
	*	Update Value based on Value Type
	*/
	$('.filter_value_type').on('change', function() {
		var value = $(this).val();
		
		var text01 = $('#val-1-text-wrapper');
		var select01 = $('#val-1-select-wrapper');
		var input01 = $('#val-1-input-wrapper');
		var date01 = $('#val-1-date-wrapper');
		var array01 = $('#val-1-array-wrapper');
		var time01 = $('#val-1-time-wrapper');
		// var file01 = $('#val-1-file-wrapper');
		var input02 = $('#val-2-input-wrapper');
		var date02 = $('#val-2-date-wrapper');
		var time02 = $('#val-2-time-wrapper');
		var wrapper02 = $('#filter_value_02_wrapper');
		var label01 = $('label[for="filter_value_01"]');
		var label02 = $('label[for="filter_value_02"]');

		//Clear Values
		input01.find('input').val('');
		select01.find('select').val('');
		date01.find('input').val('');
		time01.find('input').val('');
		// file01.find('input').val('');
		input02.find('input').val('');
		date02.find('input').val('');
		array01.find('textarea').val('');
		time02.find('input').val('');

		//Clear Required
		text01.find('input').removeAttr('required');
		input01.find('input').removeAttr('required');
		select01.find('select').removeAttr('required');
		date01.find('input').removeAttr('required');
		time01.find('input').removeAttr('required');
		// file01.find('input').removeAttr('required');
		input02.find('input').removeAttr('required');
		date02.find('input').removeAttr('required');
		array01.find('textarea').removeAttr('required');
		time02.find('input').removeAttr('required')

		if(value == 6) {
			//Time
			text01.addClass('hidden');
			select01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			date01.addClass('hidden');
			// file01.addClass('hidden');
			time01.removeClass('hidden');
			time01.find('input').attr('required',true);
			
			input02.addClass('hidden');
			date02.addClass('hidden');
			time02.removeClass('hidden');
			time02.find('input').attr('required',true);

			wrapper02.removeClass('hidden');

			label01.html('Minimum Time');
			label02.html('Maximum Time');
		}
		else if(value == 5) {
			//Array
			select01.addClass('hidden');
			date01.addClass('hidden');
			input01.addClass('hidden');
			text01.addClass('hidden');
			time01.addClass('hidden');
			// file01.addClass('hidden');
			array01.removeClass('hidden');
			array01.find('textarea').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('Array');
		}else if(value == 4) {
			//Number
			text01.addClass('hidden');
			select01.addClass('hidden');
			date01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			// file01.addClass('hidden');
			input01.removeClass('hidden');
			input01.find('input').attr('required',true);

			date02.addClass('hidden');
			time02.addClass('hidden');
			input02.removeClass('hidden');
			input02.find('input').attr('required',true);

			wrapper02.removeClass('hidden');

			label01.html('Minimum Number');
			label02.html('Maximum Number');
		}else if(value == 3) {
			//Date
			text01.addClass('hidden');
			select01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			// file01.addClass('hidden');
			date01.removeClass('hidden');
			date01.find('input').attr('required',true);
			
			input02.addClass('hidden');
			time02.addClass('hidden');
			date02.removeClass('hidden');
			date02.find('input').attr('required',true);

			wrapper02.removeClass('hidden');

			label01.html('Minimum Date');
			label02.html('Maximum Date');
		}else if(value == 2) {
			//Boolean
			text01.addClass('hidden');
			input01.addClass('hidden');
			date01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			// file01.addClass('hidden');
			select01.removeClass('hidden');
			select01.find('select').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('Value');
		}else {
			//Text
			select01.addClass('hidden');
			date01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			// file01.addClass('hidden');
			text01.removeClass('hidden');
			text01.find('input').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('Value');
		}
	});

	/**
	* Display data of campaign filter for editing
	*/
	$(document).on('click','.editCampaignFilter', function() 
	{
		var this_modal = $('#campaignFilterForm');
		var url = $('#baseUrl').html() + '/edit_campaign_filter';

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','edit_campaign_filter');
		this_modal.find('.this_form').attr('data-process', 'edit_campaign_filter');
		this_modal.find('.this_form').data('confirmation','Are you sure you want to edit this filter?');
		this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to edit this filter?');
		$('#for_filter_type').html('Edit Filter');

		var this_filter = $(this);
		var id = this_filter.data('id');
		var value_type = this_filter.data('vtype');
		var value01 = this_filter.data('value01');
		var value02 = this_filter.data('value02');
		var filter_group_id = this_filter.data('group');
		var form_button = $('#addCmpFilter');
		var form = $('#campaignFilterForm');
		
		form.find('#this_id').val(id);

		form.find('#filter_type').val(this_filter.data('ftype'));
		form.find('input[name="value_type"][value="'+value_type+'"]').prop('checked', true);
		$('input[name="value_type"]:checked').trigger('change');

		switch(parseInt(value_type)) {
			case 1:
				form.find('input[name="filter_value_01_text"]').val(value01);
				break;
			case 2:
				form.find('select[name="filter_value_01_select"]').val(value01);
				break;
			case 3:
				form.find('input[name="filter_value_01_date"]').val(value01);
				form.find('input[name="filter_value_02_date"]').val(value02);
				break;
			case 4:
				form.find('input[name="filter_value_01_input"]').val(value01);
				form.find('input[name="filter_value_02_input"]').val(value02);
				break;
			case 5:
				form.find('textarea[name="filter_value_01_array"]').val(value01);
				break;
			case 6:
				form.find('input[name="filter_value_01_time"]').val(value01);
				form.find('input[name="filter_value_02_time"]').val(value02);
				break;
		}

		$('label[for="filter_group"]').html('Update Filter in Filter Group');
		$('.edit-filter-filter-group-help-block').removeClass('hidden');
		$('.filterGroupList').val(filter_group_id);//.attr('disabled','true');

		form.collapse('show');
	});

	/**
	* Delete data of campaign filter for editing
	*/
	$(document).on('click','.deleteCampaignFilter', function(e) 
	{
		e.preventDefault();
		var this_filter = $(this);
		var id = $(this).data('id');
		var filter_group_id = $(this).data('group');

		var confirmation = confirm('Are you sure you want to delete this filter?');

		if(confirmation === true) {
			// console.log(id);

			var the_url = $('#baseUrl').html() + '/delete_campaign_filter';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : {
					'id'	: 	id
 				}, 
				success : function(data) {

					filter_group_table = $('#cfg-'+filter_group_id+'-table');

					if( filter_group_table.length == 1) {
						//Filter Group Filter is Opened and Has Filterss
						$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Close
						$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
					}else {
						//Filter Group Filter is Closed
						$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
					}
				}
			});
		}
	});
	
	/* FILTER GROUP */
	/***
	*	Show Add filter group form
	*/
	$('#addCmpFilterGroup').click(function() {

		var this_modal = $('#campaignFilterGroupForm');
		var url = $('#baseUrl').html() + '/add_campaign_filter_group';

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','add_campaign_filter_group');
		this_modal.find('.this_form').attr('data-process', 'add_campaign_filter_group');
		this_modal.find('.this_form').data('confirmation','');
		this_modal.find('.this_form').attr('data-confirmation', '');
		$('#for_filter_type').html('Add Filter Group');
		this_modal.find('#this_id').val('');
	});

	/***
	*	Show Edit filter group form
	*/
	$(document).on('click','.editCampaignFilterGroup', function(e) {
		var this_modal = $('#campaignFilterGroupForm');
		var id = $(this).data('id');
		var cfg = '#cfg-'+id+'-';
		var url = $('#baseUrl').html() + '/edit_campaign_filter_group';
		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','edit_campaign_filter_group');
		this_modal.find('.this_form').attr('data-process', 'edit_campaign_filter_group');
		this_modal.find('.this_form').data('confirmation','Are you sure you want to edit this filter group?');
		this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to edit this filter group?');
		$('#for_filter_type').html('Edit Filter Group');

		this_modal.find('#this_id').val(id);
		$('#filter_group_name').val($(cfg + 'name').html());
		$('#filter_group_description').val($(cfg + 'desc').html());
		$('input[type="radio"][name="filter_group_status"][value="'+$(cfg + 'stat').data('status')+'"]').prop('checked',true);
		$('#campaignFilterGroupForm').collapse('show');
		$('#filter_group_name').focus();
	});

	/***
	*	Delete filter group form
	*/
	$(document).on('click','.deleteCampaignFilterGroup', function(e) 
	{
		e.preventDefault();
		var this_filter_group = $(this);
		var id = $(this).data('id');

		var confirmation = confirm('Are you sure you want to delete this filter group?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_campaign_filter_group';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : {
					'id'	: 	id
 				}, 
				success : function(data) {
					var table = $('#campaign-filter-group-table').DataTable();
					table.row(this_filter_group.parents('tr')).remove().draw();
					$('.filterGroupList option[value="'+id+'"]').remove();
					var length = $('.viewCampaignFilterGroup').length;
				    if(length > 0) {
				    	$('.filterGroupList').attr('size',length);
				    	$('#addCmpFilter').removeAttr('disabled');
				    }else {
				    	$('#addCmpFilter').attr('disabled',true);
				    }
				}
			});
		}
	});

	// Show Filter Group Filters
    $('#campaign-filter-group-table tbody').on('click', '.viewCampaignFilterGroup', function () {
        var button = $(this);
        var filter_group_id = button.data('id');
        var table = $('#campaign-filter-group-table').DataTable();
        var tr = $(this).closest('tr');
        var row = table.row( tr );

 		var the_url = $('#baseUrl').html() + '/get_campaign_filter_group_filters';
 		var more_info = '';

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            button.html('<span class="glyphicon glyphicon-eye-open"></span>');
        }
        else {
            // Open this row
            $.ajax({
				type : 'POST',
				url  : the_url,
				data : {
					id : filter_group_id
				},
				success : function(filters) {
					// console.log(filters);
					// console.log(filters.length);
					if(filters.length > 0) {
					    $('.filter-group-filters-table').DataTable();

					    var table_id = 'cfg-'+filter_group_id+'-table';
					    var table_info = '<table id="'+table_id+'" cellpadding="5" cellspacing="0" border="0" class="table table-bordered filter-group-filters-table" data-id="'+filter_group_id+'">'+
					    	'<thead>'+
					    		'<tr>'+
					    			'<th>Filter</th>'+
					    			'<th>Value</th>'+
					    			'<th></th>'+
					    		'</tr>'+
					    	'</thead>'+
					   	'</table>';
					   	
					   	var counter = 0;
					   	var filter_group_set = [];
					   	$.each(filters, function(index,filter){
					   		var filter_id = filter['id'];
							var filter_value_type = filter['value_type'];
							var filter_value01,filter_value02;

							filter_group_set[counter] = filter_value_type;
							
							switch(filter_value_type) {
								case 1 :
									filter_value01 = filter['value_text'];
									filter_value02 = '';
									break;
								case 2 :
									filter_value01 = filter['value_boolean'];
									filter_value02 = '';
									break;
								case 3 :
									filter_value01 = filter['value_min_date'];
									filter_value02 = filter['value_max_date'];
									break;
								case 4 :
									filter_value01 = filter['value_min_integer'];
									filter_value02 = filter['value_max_integer'];
									break;
								case 5 :
									filter_value01 = filter['value_array'];
									filter_value02 = '';
									break;
								case 6 :
									filter_value01 = filter['value_min_time'];
									filter_value02 = filter['value_max_time'];
									break;
								default:
									filter_value01 = '';
									filter_value02 = '';
									break;
							}

							var nameCol = '<span id="cf-'+filter_id+'-filter">'+filter.filter_name+'</span>';
							var valueCol = '<span id="cf-'+filter_id+'-value">'+filter.value_value+'</span>';
							var actionCol = '<button id="cf-'+filter_id+'-edit-button" class="btn btn-default editCampaignFilter" type="button" data-id="'+filter_id+'" data-group="'+filter_group_id+'" data-ftype="'+filter['filter_type_id']+'" data-vtype="'+filter_value_type+'" data-value01="'+filter_value01+'" data-value02="'+filter_value02+'"><span class="glyphicon glyphicon-pencil"></span></button>';
								actionCol += '<button id="cf-'+filter_id+'-delete-button" class="btn btn-default deleteCampaignFilter" type="button" data-id="'+filter_id+'" data-group="'+filter_group_id+'"><span class="glyphicon glyphicon-trash"></span></button>';
							filter_group_set[counter] = [nameCol,valueCol,actionCol];
							counter++;
						});
						row.child(table_info).show();

						$('#'+table_id).DataTable( {
					        data: filter_group_set,
					        bFilter: false, 
					        bInfo: false,
					        "sDom": 'rt'
					    } );
					}else {
						more_info = '<span id="cfg-'+filter_group_id+'-no-table">No Filters.</span>'
						row.child(more_info).show();
					}
					
	            	tr.addClass('shown');
	            	button.html('<span class="glyphicon glyphicon-remove"></span>');
				}
			});
        }
    });

	$('#campaignFilterGroupForm').on('hide.bs.collapse', function () {
		var form = $(this).find('form');
		var this_button = $('#addCmpFilterGroup');
		this_button.html('<span class="glyphicon glyphicon-plus"></span> Group');
		$('#for_filter_type').html('Add Filter');

		$('#filter_group_name').val('');
		$('#filter_group_description').val('');
		$('input[type="radio"][name="filter_group_status"][value="0"]').prop('checked',true);

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});
		form.find('.this_error_wrapper').hide();
	});

	$('#campaignFilterGroupForm').on('show.bs.collapse', function () {

		$('#campaignFilterForm').collapse('hide');

		var this_button = $('#addCmpFilterGroup');
		this_button.html('<span class="glyphicon glyphicon-remove"></span> Group');
	});
	
	$('.closeFilterGroupCollapse').click(function() {
		$('#campaignFilterGroupForm').collapse('hide');
	});

	/* Open Filter Tab */
	$('[href="#filter_tab"]').click(function(){
		if($('#campaign-filter-group-table tbody tr.shown').length == 0) {
			$('.viewCampaignFilterGroup').trigger('click'); //Open Filter Groups
		}
	});

	/* Posting Instructions */
	$(document).on('click','#editPostingInstruction', function() 
	{
		var this_button = $(this);
		var display = $(this).data('config');

		if(display == 'show') {
			this_button.html('<span class="glyphicon glyphicon-remove"></span>');
			this_button.attr('data-config','hide').data('config','hide');
			$('.cmpPI-form-wrapper').removeClass('hidden');
			// $('#cmp-posting-instruction').removeAttr('disabled');
			$('#cmp-sample-code').removeAttr('disabled');
			$('#cmp-posting-instruction').ckeditorGet().setReadOnly(false);
		}else {
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			$('.cmpPI-form-wrapper').addClass('hidden');
			// $('#cmp-posting-instruction').attr('disabled',true);
			$('#cmp-posting-instruction').val($('#cmp-posting-instruction-actual').val());
			$('#cmp-sample-code').attr('disabled',true);
			$('#cmp-sample-code').val($('#cmp-sample-code-actual').val());
			$('#cmp-posting-instruction').ckeditorGet().setReadOnly();
		}
	});

	$('.cancelCampaignPostingInstructionEdit').click(function() {
		var this_button = $('#editPostingInstruction');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		$('.cmpPI-form-wrapper').addClass('hidden');
		$('#cmp-posting-instruction').attr('disabled',true);
		$('#cmp-posting-instruction').val($('#cmp-posting-instruction-actual').val());
		$('#cmp-sample-code').attr('disabled',true);
		$('#cmp-sample-code').val($('#cmp-sample-code-actual').val());
	});

	$(document).on('click','#previewPostingInstruction', function() 
	{
		var posting_instruction = $('#cmp-posting-instruction-actual').val();
		var sample_code = $('#cmp-sample-code-actual').val();
		$('#posting-tab.previewPostingInstruction').html(posting_instruction);
		$('.previewSampleCode').html(sample_code);
		$('#previewCampaignPostingInstructionModal').modal('show');
	});

	$('#previewCampaignPostingInstructionModal').on('hide.bs.modal', function (event) {
		$('#posting-tab.previewPostingInstruction').html('');
		$('.previewSampleCode').html('');
	});

	/**
	* Close SORT Campaigns Modal
	*/
	$('#sortCmpModal').on('hide.bs.modal', function (event) 
	{
		$('.saveSortedCampaignsBtn').hide();
	});

	/* Stack Creative Tab */

	$.widget( "ui.spinner", $.ui.spinner, {
        _buttonHtml: function() {
            return "" +
            	'<a class="ui-spinner-button ui-spinner-up ui-corner-tr ui-button ui-widget ui-state-default ui-button-text-only" tabindex="-1" role="button"><i class="fa fa-caret-up fa-sm" aria-hidden="true"></i></a>' +
                '<a class="ui-spinner-button ui-spinner-down ui-corner-br ui-button ui-widget ui-state-default ui-button-text-only" tabindex="-1" role="button"><i class="fa fa-caret-down fa-sm" aria-hidden="true"></i></a>';
        }
    });

	$( ".spinner" ).spinner({
      step: 0.01,
      numberFormat: "n",
      min: 0,
      max: 1
    });

    var campaignCreativeID = 0, canEditCampaignCreative = true, canDeleteCampaignCreative = true;

	var campaignCreativeTable = $('#campaign-stack-creative-table').DataTable({
        responsive: true,
        autoWidth: false,
        'processing': true,
    	'serverSide': true,
        "order": [[ 0, "desc" ]],
        'columns': [
			null,
			{ 'orderable': false },
			{ 'orderable': false },
			{ 'orderable': false }
		],
        columnDefs: [
        	{ width: '10%', targets: 0 },	                
            { width: '55%', targets: 1 },
            { width: '30%', targets: 2 },
            { width: '5%', targets: 3 },
        ],
        "bFilter" : false,
        "bLengthChange": false,
        "bInfo": false,
        "bPaginate": false,
        'ajax':{
			url: $('#baseUrl').html() + '/get_campaign_creative',
			type: 'post', 
			'data': function(d)
            {
            	// console.log('Campaign ID: ' + $('.this_campaign').val());
                // d.campaign_id = $('.this_campaign').val();
                d.campaign_id = $('#campaign_form_builder').find('.this_campaign').val();
            },
			error: function(data) //error handling
			{
				console.log(data);
			},
			"dataSrc": function ( json ) {
        		console.log(json);
        		canEditCampaignCreative = json.canEdit;
				canDeleteCampaignCreative = json.canDelete;

				if(! json.canAdd) {
					$('#addCampCreative').hide();
				}else {
					$('#addCampCreative').show();
				}
        		return json.data;
        	}
		},
		"sDom": 'lf<"campaign-stack-creative-table-add-toolbar">rtip',
        "fnDrawCallback": function( oSettings ) {
            // console.log(oSettings);
            campaignCreativeID =  $('.this_campaign').val();
        	$('.stackCreativeDesc').ckeditor({
		    	toolbarGroups : [
					{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
					{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
					{ name: 'forms', groups: [ 'forms' ] },
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
					{ name: 'links', groups: [ 'links' ] },
					{ name: 'insert', groups: [ 'insert' ] },
					{ name: 'styles', groups: [ 'styles' ] },
					{ name: 'colors', groups: [ 'colors' ] },
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
					{ name: 'tools', groups: [ 'tools' ] },
					{ name: 'others', groups: [ 'others' ] },
					{ name: 'about', groups: [ 'about' ] }
				],
		    	removeButtons : 'Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Redo,Undo,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,RemoveFormat,Language,BidiRtl,BidiLtr,Link,Unlink,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,ShowBlocks,About'
		    });
        	
		    $( ".spinner" ).spinner({
		      step: 0.01,
		      numberFormat: "n",
		      min: 0,
		      max: 1,
		      change: function( event, ui ) {
		      	$('#'+event.currentTarget.id).parent('span.ui-spinner').removeClass('error_element');
		      	if(! $(this).spinner('isValid')) {
		      		$('#'+event.currentTarget.id).parent('span.ui-spinner').addClass('error_element');
		      	}
		      	// var value = parseInt(event.currentTarget.value);
		      }
		    });
		    $( ".spinner" ).spinner("disable");

		    if(canEditCampaignCreative == false) {
		    	$('.editCampaignCreative').hide();
		    }

		    if(canDeleteCampaignCreative == false) {
		    	$('.deleteCampaignCreative').hide();
		    }
        }
    });
    $("div.campaign-stack-creative-table-add-toolbar").html('<button id="addCampCreative" class="btn btn-default pull-right" type="button"><span class="glyphicon glyphicon-plus"></span></button>');
    
	// $(document).on('click','[href="#stack_content_tab"]', function() 
	// {
	// 	if($('.this_campaign').val() != campaignCreativeID) {
	// 		campaignCreativeTable.ajax.reload();
	// 	}
	// });

	$(document).on('click','.editCampaignCreative', function() 
	{
		var id = $(this).data('id');
		var type = $(this).data('type');
		if(type == 'edit') {
			$('#cmpCrtv-'+id+'-desc').ckeditorGet().setReadOnly(false);
			$('#cmpCrtv-'+id+'-weight').spinner('enable');
			$('#cmpCrtv-'+id+'-imgLinkDiv').show();
			$(this).html('<span class="glyphicon glyphicon-remove"></span>');
			$('#cmpCrtv-'+id+'-save-button').show();
			$(this).data('type','cancel');
		}else {
			$('#cmpCrtv-'+id+'-desc').ckeditorGet().setReadOnly(true);
			$('#cmpCrtv-'+id+'-desc').val($('#cmpCrtv-'+id+'-desc-original').val());
			$('#cke_cmpCrtv-'+id+'-desc').removeClass('error_element');
			$('#cmpCrtv-'+id+'-weight').spinner('disable').val($('#cmpCrtv-'+id+'-weight-original').val());
			$('#cmpCrtv-'+id+'-weight').parent('span.ui-spinner').removeClass('error_element');
			var image_link = $('#cmpCrtv-'+id+'-img-original').val();
			$('#cmpCrtv-'+id+'-imgLinkDiv').hide();
			$('#cmpCrtv-'+id+'-img-preview').attr('src',image_link);
			$('#cmpCrtv-'+id+'-img').val(image_link).removeClass('error_field error error_element');
			$('#cmpCrtv-'+id+'-imgGalleryAlert').remove();
			$(this).html('<span class="glyphicon glyphicon-pencil"></span>');
			$('#cmpCrtv-'+id+'-save-button').hide();
			$(this).data('type','edit');
		}
	});

	$(document).on('change','.campaignCreativeImageLink', function() 
	{
		var this_input = $(this);
		var id = $(this).data('id');
		var has_error = false;

		this_input.removeClass('error_field error');

		$('#cmpCrtv-'+id+'-img-preview')
		    .on('load', function() { console.log("image loaded correctly"); })
		    .on('error', function() { 
		    	if(this_input.val() != '') {
		    		console.log(this_input.val());
		    		this_input.addClass('error_field error').val('');
		    		console.log("error loading image"); 
		    		has_error = true;
		    	}
		    })
		    .attr("src", $(this).val());
		;

		var gallery_alert = '<div id="cmpCrtv-'+id+'-imgGalleryAlert" class="col-md-12 alert alert-info" role="alert" style="margin-top: 10px;"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>It is recommended that the image comes from our very own <a href="'+$('#baseUrl').html()+'/admin/gallery" class="alert-link" target="_blank">gallery</a></div>';
		if ($(this).val().indexOf($('#baseUrl').html()+'/images/gallery/') < 0 && $('#cmpCrtv-'+id+'-imgGalleryAlert').length == 0) {
			console.log('not from gallery');
			$('#cmpCrtv-'+id+'-imgLinkDiv').after(gallery_alert);
		}
		else console.log('from gallery');
    });

	$(document).on('click','.saveCampaignCreative', function() 
	{
		var id = $(this).data('id'),
			weight = $('#cmpCrtv-'+id+'-weight').val(),
			image = $('#cmpCrtv-'+id+'-img').val(),
			description = $.trim(CKEDITOR.instances['cmpCrtv-'+id+'-desc'].getData()),
			org_weight = $('#cmpCrtv-'+id+'-weight-original').val(),
			org_desc = $('#cmpCrtv-'+id+'-desc-original').val(),
			org_img = $('#cmpCrtv-'+id+'-img-original').val();

		if( description == '' ) {
			$('#cke_cmpCrtv-'+id+'-desc').addClass('error_element');
		}else {
			$('#cke_cmpCrtv-'+id+'-desc').removeClass('error_element');
		}

		if( image == '' ) {
			$('#cmpCrtv-'+id+'-img').addClass('error_element');
		}else {
			$('#cmpCrtv-'+id+'-img').removeClass('error_element');
		}

		if($('#cmpCrtv-'+id+'-weight').spinner('isValid') && ! $('#cmpCrtv-'+id+'-img').hasClass('error') && image != '' && description != '' ) { //check if no errors
			if(weight !=  org_weight || image != org_img || description != org_desc) { //check if has changes
				var confirmation = confirm('Are you sure you want to edit this creative?');
				if(confirmation === true) {
					$.ajax({
						type: 'POST',
						url: $('#baseUrl').val() + '/edit_campaign_creative',
						data: {
							id : id,
							weight: weight,
							image : image,
							description : description
						},
						success: function(data)
						{
							console.log(data);
							$('#cmpCrtv-'+id+'-weight-original').val(weight);
							$('#cmpCrtv-'+id+'-desc-original').val(description);
							$('#cmpCrtv-'+id+'-img-original').val(image);
							$('#cmpCrtv-'+id+'-edit-button').trigger('click');

							alert('Campaign Creative updated!');
						}
					});
				}
			}else {
				console.log('no changes');
				$('#cmpCrtv-'+id+'-edit-button').trigger('click');
			}
		}else console.log('NOT A CHANCE');
	});

	$(document).on('click','#addCampCreative', function() 
	{
		var id = $('#campaign_form_builder').find('.this_campaign').val();
		$.ajax({
			type: 'POST',
			url: $('#baseUrl').val() + '/add_campaign_creative',
			data: {
				id : id
			},
			success: function(data)
			{
				campaignCreativeTable.order([]);
				campaignCreativeTable.ajax.reload();
			}
		});
	});

	$(document).on('click','.deleteCampaignCreative', function() 
	{
		console.log('Delete');
		var id = $(this).data('id'),
			this_button = $(this);

		var confirmation = confirm('Are you sure you want to delete this creative?');
		if(confirmation === true) {
			$.ajax({
				type: 'POST',
				url: $('#baseUrl').val() + '/delete_campaign_creative',
				data: {
					id : id
				},
				success: function(data)
				{
					this_button.parents('tr').remove();
					if($('#campaign-stack-creative-table tbody tr').length < 4) {
						$('#addCampCreative').show();
					}else {
						$('#addCampCreative').hide();
					}
				}
			});
		}
	});

    /**
     * campaign_checkeditor.js section
     */
    $('#cmp-posting-instruction').ckeditor({
        // uiColor: '#9AB8F3'
        removeButtons : 'Underline,Subscript,Superscript,Scayt,RemoveFormat,About,Copy,Paste,PasteText,PasteFromWord,Cut,Anchor'
    });

    /* EIQ Iframe Traffic */
    var websiteAffiliateId = 0;

	var affiliateWebsiteTable = $('#affiliate-website-table').DataTable({
        responsive: true,
        autoWidth: false,
        'processing': true,
    	'serverSide': true,
        "order": [[ 1, "desc" ]],
        'columns': [
        	{ 'orderable': false },
			null,
			null,
			null,
			null,
			null,
			null,
			{ 'orderable': false },
		],
        'ajax':{
			url: $('#baseUrl').html() + '/get_affiliate_website',
			type: 'post', 
			'data': function(d)
            {
                d.affiliate_id = websiteAffiliateId;
            },
			error: function(data) //error handling
			{
				console.log(data);
			},
			"dataSrc": function ( json ) {
        		// console.log(json);
    //     		canEditCampaignCreative = json.canEdit;
				// canDeleteCampaignCreative = json.canDelete;

				// if(! json.canAdd) {
				// 	$('#addCampCreative').hide();
				// }else {
				// 	$('#addCampCreative').show();
				// }
        		return json.data;
        	}
		}
    });

    $(document).on('click','.eiqFrameTraffic', function() 
	{
		var id = $(this).data('id'),
			this_button = $(this);

		affiliateWebsiteTable.clear().draw();

		websiteAffiliateId = id;
		affiliateWebsiteTable.ajax.reload(); 
		$('#affiliate_for_website_title').html(id);
		$('#website_affiliate').val(id);
		$('#affiliateWebsiteDiv').show();
		$('#campaignAffiliateDiv').hide();
		$('#campaignAffiliateForm').collapse('hide');
		$('#editCampaignAffiliateForm').collapse('hide');
		$('.selectCampaignAffiliate').prop('checked', false).trigger('change');
		$('.selectAllCampaignAffiliate').prop('checked', false);
		$('.selectAllAffiliateWebsite').prop('checked', false);
		$('.selectAffiliateWebsite').prop('checked', false);

		$('.editCampaignAffiliate').attr('disabled',true);
		$('.deleteCampaignAffiliate').attr('disabled', true);
		$('.eiqFrameTraffic').attr('disabled', true);
		$('.selectAllCampaignAffiliate').attr('disabled',true);
		$('.selectCampaignAffiliate').attr('disabled',true);
		
		// console.log(id);
	});

	$(document).on('click','#backToCampaignAffiliateBtn', function() 
	{
		$('#affiliateWebsiteDiv').hide();
		$('#campaignAffiliateDiv').show();
		$('#affiliateWebsiteForm').collapse('hide');

		$('.editCampaignAffiliate').removeAttr('disabled');
		$('.deleteCampaignAffiliate').removeAttr('disabled');
		$('.eiqFrameTraffic').removeAttr('disabled');
		$('.selectAllCampaignAffiliate').removeAttr('disabled');
		$('.selectCampaignAffiliate').removeAttr('disabled');
	});


	$(document).on('click','#addAffiliateWebsite', function() 
	{
		$('.selectCampaignAffiliate').prop('checked', false).trigger('change');
		$('.selectAllCampaignAffiliate').prop('checked', false);
		$('.selectAllAffiliateWebsite').prop('checked', false);
		$('.selectAffiliateWebsite').prop('checked', false);
		// console.log(id);
	});
	
	$(document).on('click','.closeAffiliateWebsiteCollapse', function() 
	{
		$('#affiliateWebsiteForm').collapse('hide');
	});

	$(document).on('change','.selectAffiliateWebsite', function()
	{
		var checkIfSelectAll = $('input[name="website_id[]"]:not(:checked)').length == 0;
		if(checkIfSelectAll == false) $('.selectAllAffiliateWebsite').prop('checked', false);
		else $('.selectAllAffiliateWebsite').prop('checked', true);


		if($('[name="website_id[]"]:checked').length > 1) {
			$('#editAffilateWebsitesBtn').removeAttr('disabled');
			$('#deleteAffiliateWebsitesBtn').removeAttr('disabled');
		}else {
			$('#editAffilateWebsitesBtn').attr('disabled',true);
			$('#deleteAffiliateWebsitesBtn').attr('disabled',true);
		}
	});

	$(document).on('change','.selectAllAffiliateWebsite', function()
	{
		$('[name="website_id[]"]').prop('checked', $(this).prop("checked"));
		$('.selectAllAffiliateWebsite').prop('checked', $(this).prop("checked"));

		if($('[name="website_id[]"]:checked').length > 1) {
			$('#editAffilateWebsitesBtn').removeAttr('disabled');
			$('#deleteAffiliateWebsitesBtn').removeAttr('disabled');
		}else {
			$('#editAffilateWebsitesBtn').attr('disabled',true);
			$('#deleteAffiliateWebsitesBtn').attr('disabled',true);
		}
	});

	$('#affiliateWebsiteForm').on('show.bs.collapse', function (event) 
	{
		// var addIcon = $('#addAffiliateWebsite span'),
		// 	editIcon = $('#editAffilateWebsitesBtn span'),
		// 	form = $('#affiliate_website_form');

		// if(form.find('#website_id').val() == '') {
		// 	addIcon.removeClass('glyphicon-plus').addClass('glyphicon-remove');
		// }else {
		// 	editIcon.removeClass('glyphicon-pencil').addClass('glyphicon-remove');
		// }

		var addIcon = $('#addAffiliateWebsite span');
		addIcon.removeClass('glyphicon-plus').addClass('glyphicon-remove');

		//disabled
		$('.selectAffiliateWebsite').attr('disabled',true);
		$('.selectAllAffiliateWebsite').attr('disabled',true);
		$('.editAffiliateWebsite').attr('disabled',true);
		$('.deleteAffiliateWebsite').attr('disabled',true);

		$('#affiliateWebsitePayoutForm').collapse('hide');
	});

	$('#affiliateWebsiteForm').on('hide.bs.collapse', function (event) 
	{
		var form = $('#affiliate_website_form'),
			addIcon = $('#addAffiliateWebsite span'),
			url = $('#baseUrl').html() + '/add_affiliate_website';

		//Icons
		addIcon.removeClass('glyphicon-remove').addClass('glyphicon-plus');

		//Form
		form.attr('action', url);
		form.data('confirmation','').attr('data-confirmation', '');

		//Input Fields
		form.find('#website_name').val('');
		form.find('#website_payout').val('');
		form.find('#website_description').val('');
		form.find('#website_id').val('');
		form.find('#revenue_tracker_id').val('');
		form.find('[name="allow_datafeed"][value="0"]').prop('check');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});
		form.find('.this_error_wrapper').hide();

		//remove disable
		$('.selectAffiliateWebsite').removeAttr('disabled');
		$('.selectAllAffiliateWebsite').removeAttr('disabled');
		$('.editAffiliateWebsite').removeAttr('disabled');
		$('.deleteAffiliateWebsite').removeAttr('disabled');
	});

	$('#affiliateWebsitePayoutForm').on('show.bs.collapse', function (event) 
	{
		var editIcon = $('#editAffilateWebsitesBtn span');

		editIcon.removeClass('glyphicon-pencil').addClass('glyphicon-remove');

		//disabled
		$('.selectAffiliateWebsite').attr('disabled',true);
		$('.selectAllAffiliateWebsite').attr('disabled',true);
		$('.editAffiliateWebsite').attr('disabled',true);
		$('.deleteAffiliateWebsite').attr('disabled',true);

		$('#affiliateWebsiteForm').collapse('hide');
	});

	$('#affiliateWebsitePayoutForm').on('hide.bs.collapse', function (event) 
	{
		var form = $('#affiliate_website_payout_form'),
			editIcon = $('#editAffilateWebsitesBtn span');

		//Icons
		editIcon.removeClass('glyphicon-remove').addClass('glyphicon-pencil');

		//Input Fields
		form.find('#website_payout').val('');

		//remove disable
		$('.selectAffiliateWebsite').removeAttr('disabled');
		$('.selectAllAffiliateWebsite').removeAttr('disabled');
		$('.editAffiliateWebsite').removeAttr('disabled');
		$('.deleteAffiliateWebsite').removeAttr('disabled');

		$('.listOfAffiliateWebsitesEdit').addClass('hidden');
		$('.putListOfWebsitesHere').html('');
	});

	$(document).on('click','.closeAffiliateWebsitePayoutCollapse', function() 
	{
		$('#affiliateWebsitePayoutForm').collapse('hide');
	});

	$(document).on('click','.editAffiliateWebsite', function() 
	{
		var id = $(this).data('id'),
			form = $('#affiliate_website_form'),
			url = $('#baseUrl').html() + '/edit_affiliate_website';

		$('.selectAffiliateWebsite').prop('checked',false);
		$('[name="website_id[]"][value="'+id+'"]').prop('checked',true).trigger('change');

		form.attr('action', url);
		form.data('confirmation','Are you sure you want to edit this?').attr('data-confirmation', 'Are you sure you want to edit this?');

		form.find('#website_name').val($('#afWeb-'+id+'-name').html());
		form.find('#website_payout').val($('#afWeb-'+id+'-pay').html());
		form.find('#website_description').val($('#afWeb-'+id+'-desc').html());
		form.find('#website_id').val(id);
		form.find('#revenue_tracker_id').val($('#afWeb-'+id+'-revTracker').html());
		form.find('[name="allow_datafeed"][value="'+$('#afWeb-'+id+'-pay').val()+'"]').prop('check');

		$('#affiliateWebsiteForm').collapse('show');
	});

	$(document).on('click','#editAffilateWebsitesBtn', function() 
	{
		var collapse = $('#affiliateWebsitePayoutForm');

		if (collapse.attr('aria-expanded') == "true") {
			collapse.collapse('hide');
		}else {
			var form = $('#affiliate_website_form'),
				ids = $('[name="website_id[]"]:checked'),
				id = ids[0]["value"];
			if(ids.length == 1) {
				console.log('hello');
				$('.editAffiliateWebsite[data-id="'+id+'"]').trigger('click');
			}else {
				var payout = $('#afWeb-'+id+'-pay').html(),
					form = $('#affiliate_website_payout_form'),
					edit_info = '';

					form.find('#website_payout').val(payout);
					$('#affiliateWebsitePayoutForm').collapse('show');

					$('#selectedAffiliateWebsiteDiv').html('');
					ids.each(function() {
						edit_info += $('#afWeb-'+$(this).val()+'-name').html() + ', ';
						$('#selectedAffiliateWebsiteDiv').append('<input name="selected_websites[]" type="hidden" value="'+$(this).val()+'"/>');
					});

					edit_info = edit_info.substring(0,edit_info.length - 2);
					edit_info += '.';
					$('.listOfAffiliateWebsitesEdit').removeClass('hidden');
					$('.putListOfWebsitesHere').html(edit_info).show();
			}
		}
	});

	$(document).on('click','.deleteAffiliateWebsite', function(e) 
	{
		e.preventDefault();
		var this_website = $(this);
			id = $(this).data('id');

		this_website.parents('tr').css('background-color','#ebccd1');

		var confirmation = confirm('Are you sure you want to delete this website?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_affiliate_website/'+id;
			$.ajax({
				type : 'POST',
				url  : the_url,
 				data : {
 					id: id
 				},
				success : function(data) {

					var table = $('#affiliate-website-table').DataTable();
					table.row(this_website.parents('tr')).remove().draw();;
				}
			});
		}else {
			this_website.parents('tr').css('background-color','');
		}
	});

	$(document).on('click','#deleteAffiliateWebsitesBtn', function(e) 
	{
		e.preventDefault();
		var this_website = $(this);
			ids = $('[name="website_id[]"]:checked'),
			websites = [];

		ids.parents('tr').css('background-color','#ebccd1');

		ids.each(function() {
			websites.push($(this).val());
		});

		var confirmation = confirm('Are you sure you want to delete these websites?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_multiple_affiliate_website';
			$.ajax({
				type : 'POST',
				url  : the_url,
 				data : {
 					id: websites
 				},
				success : function(data) {
					var table = $('#affiliate-website-table').DataTable();
					table.draw();
				}
			});
		}else {
			ids.parents('tr').css('background-color','');
		}
	});

	$(document).on('click','.eiqFrameStatus', function(e) 
	{
		e.preventDefault();
		var this_button = $(this),
			affiliate_id = $(this).data('id'),
			status = $(this).data('status');

		if(status == 1) confirm_stat = 'DEACTIVATE';
		else confirm_stat = 'ACTIVATE'

		var confirmation = confirm("You are about to "+confirm_stat+" this affiliate from running our Iframe. Click 'OK' to confirm.");

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/update_affiliate_website_status';
			$.ajax({
				type : 'POST',
				url  : the_url,
 				data : {
 					affiliate_id : affiliate_id,
 					status : status
 				}, 
				success : function(data) {
					new_status = data.new_status,
					update_count = data.update_count;

					if(update_count > 0) {
						if(new_status == 1) {
							curIcon = 'glyphicon-remove-circle';
							newIcon = 'glyphicon-ok-circle';
							curBtn = 'btn-danger';
							newBtn = 'btn-success';
						}else {
							curIcon = 'glyphicon-ok-circle';
							newIcon = 'glyphicon-remove-circle';
							curBtn = 'btn-success';
							newBtn = 'btn-danger';
						}

						this_button.attr('data-status',new_status).data('status',new_status);
						this_button.removeClass(curBtn).addClass(newBtn);
						this_button.find('span').removeClass(curIcon).addClass(newIcon);
					}else {
						alert('Affiliate does not have any websites.');
					}
					
				}
			});
		}
	});


	/* CREATIVE */
    CKEDITOR.config.readOnly = true;
    CKEDITOR.config.allowedContent = true;
    CKEDITOR.config.contentsCss = 'http://path6.paidforresearch.com/dynamic_live/css/style.css';
    CKEDITOR.config.contentsCss = 'http://path6.paidforresearch.com/dynamic_live/css/mobile.css';
    CKEDITOR.config.contentsCss = 'http://path6.paidforresearch.com/dynamic_live/css/stack.css';

    $.fn.modal.Constructor.prototype.enforceFocus = function () {
        modal_this = this
        $(document).on('focusin.modal', function (e) {
            if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
                    // add whatever conditions you need here:
                &&
                !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
                modal_this.$element.focus()
            }
        })
    };

    /* Form Builder */

    function addFormField(index) {
    	var field_table = $('#formFieldsTable tbody'),
    		field = form_builder_fields[index],
    		type = field.type,
    		value = field.value,
    		name = field.name;

    	if(field.value.indexOf('"') < 0) {
	    	if($('select[name="value_select"] option[value="'+field.value+'"]').length > 0) {
				value = $('select[name="value_select"] option[value="'+field.value+'"]').html();
			}
		}

    	//check if standard field
    	if(! field.standard) {
			sort = '<span class="sortFieldHandle glyphicon glyphicon-resize-vertical"></span>';
			remove = '';
			sortable = '';
		}else {
			remove = 'disabled';
			sort = '';
			sortable = 'class="sortDisabled"';
		}

		//get value
		if(value != '') {
			if(type == 'article'){
				prev_val = value;
				prev_val = prev_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
				prev_val = prev_val.replace(/<\/?[^>]+(>|$)/g, "");
				prev_val = prev_val.substr(0,150);
				if(value.length > 150) prev_val += '...';
				field_value = '<small><em>'+prev_val+'</em></small>';
			}else {
				def_val = value;
				if(type == 'dropdown' || type == 'checkbox' || type == 'radio') {
					def_val_indx = field.options.values.indexOf(field.value);
					if(field.value != field.options.displays[def_val_indx]) {
						def_val += ' - ' + field.options.displays[def_val_indx];
					}
				}
				def_val = def_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
				def_val_prev = def_val.substr(0,74);
				if(def_val.length > 74) def_val_prev += '...';
				field_value = '<span class="label label-primary">'+def_val_prev+'</span>';
				// console.log(def_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""));
			}
		}else field_value = '';
		
		//get some options if exists
		if(type == 'dropdown' || type == 'checkbox' || type == 'radio') {
			if(typeof field.options != undefined) {
				fieldCounter = 0;
				$.each(field.options.values, function(i, oVal) {
					if(oVal != '') {
						if(fieldCounter <= 3) {
							if(oVal != field.value) {
								if(oVal == field.options.displays[i]) opt_val = oVal;
								else opt_val = oVal + ' - ' + field.options.displays[i];
								opt_val = opt_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
								opt_val_prev = opt_val.substr(0,74);
								if(opt_val.length > 74) opt_val_prev += '...';
								field_value += ' <span class="label label-info">'+ opt_val_prev +'</span>';
								fieldCounter++;
							}
						}else {
							field_value += ' <span class="label label-info">...</span>';
							return false;
						}
					}
				});
			}
		}

		//get name/label
		var name_display = name;
		if(field.label != '') {
			name_display += '<br><small><em>' + field.label.substr(0,74);
			if(field.label.length > 74) name_display += '...';
			name_display += '</em></small>';
		}

    	field_content = '<tr '+sortable+' data-id="'+index+'"><th>'+sort+'</th>' +
			'<th id="fbf-name-'+index+'">'+ name_display +'</th>' +
          	'<td><span id="fbf-type-'+index+'" class="label label-default">'+ type +'</span></td>' +
          	'<td id="fbf-value-'+index+'">'+ field_value +'</td>' +
          	'<td><button type="button" class="removeFieldBtn btn btn-default pull-right" style="margin-left: 5px" data-id="'+index+'" '+remove+'><span class="glyphicon glyphicon-trash"></span></button>' +
          	'<button type="button" class="viewFieldModalBtn btn btn-default pull-right" data-id="'+index+'"><span class="glyphicon glyphicon-pencil"></span></button></td>' +
            '</tr>';

        field_table.append(field_content);
    }

    //SORT FIELDS
	// var sorted_fields = [];
	var sorted = $('#formFieldsTable tbody').sortable({
		handle: '.sortFieldHandle',
		items: "tr:not(.sortDisabled)"
	});	

    var form_builder_fields = [],
    	field_names = [],
    	standard_fields = $.parseJSON($('#standardRequiredFields').val());
    	standard_lf_fields = $.parseJSON($('#standardLFRequiredFields').val());

    var cssCodeMirror = CodeMirror.fromTextArea($('#custom_css')[0], {
	  	theme: 'default',
	    lineNumbers: true,
        matchBrackets: true,
        // mode: "application/x-httpd-php",
        // mode: 'javascript',
        mode: "css",
        indentUnit: 4,
        indentWithTabs: true
	});

	var jsCodeMirror = CodeMirror.fromTextArea($('#custom_js')[0], {
	  	theme: 'default',
	    lineNumbers: true,
        matchBrackets: true,
        // mode: "application/x-httpd-php",
        // mode: 'javascript',
        mode: 'text/html',
        indentUnit: 4,
        indentWithTabs: true
	});

	var formBuilderCodeMirror = CodeMirror.fromTextArea(document.getElementById('stack_code'), {
	  	theme: 'default',
	    lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true,
        readOnly: true
	});

    $(document).on('click','.campaignFormBuilder', function() 
	{
		var id = $(this).data('id'),
			campaign_type = $(this).data('type'),
			form_builder = $('#campaign_form_builder'),
			the_url = $('#baseUrl').html() + '/get_campaign_content';

		form_builder.find('#this_campaign').val(id);
		$('.this_campaign').val(id);
		$('#form-builder-campaign').html(id + ' - ' + $('#cmp-'+id+'-name').html());
		$('#formBuilderCampaignType').val(campaign_type);

		//campaign type
		if(campaign_type == 5 || campaign_type == 6) { //link out
			$('.formBuilderCoregElement').hide();
			$('.formBuilderCoregElement').find('[name="send_lead_to"]').removeAttr('required');
			$('.formBuilderLinkoutElement').show();
			$('.formBuilderLinkoutElement').find('[name="redirect_link"]').attr('required', true);
		}else {
			$('.formBuilderCoregElement').show();
			$('.formBuilderCoregElement').find('[name="send_lead_to"]').attr('required', true);
			$('.formBuilderLinkoutElement').hide();
			$('.formBuilderLinkoutElement').find('[name="redirect_link"]').removeAttr('required');
		}

		$.ajax({
			type: 'POST',
			data: {
				'id'	:	id,
				'type'	:   campaign_type
			},
			url: the_url,
			success: function(data) {
				console.log(data);

				if(typeof data.auto_lock !== 'undefined' && data.auto_lock == true) {
					autoLockAlert = '<div id="autoLockAlert" class="alert alert-warning alert-dismissible" role="alert">' +
  						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
  						'<strong>Warning!</strong> Content was automatically locked since it contained php codes.' +
						'</div>';
					$('#cmpFormBuilderModal').find('.modal-body').prepend(autoLockAlert);
				}

				if(data.content != null) {
					var form = data.form,
						field_table = $('#formFieldsTable tbody');

					form_builder.find('#stack_code').val(data.content);
					formBuilderCodeMirror.getDoc().setValue(data.content);

					//lock
					if(data.lock == 1) {
						$('.form_builder_btn').attr('disabled',true);
					}

					if(campaign_type == 5) {
						form_builder.find('#redirect_link').val(form.redirect_link);
					}else {
						if(form.action == 'lead_reactor' || form.action == 'lead_filter') {
							$('#formBuilderSendLeadSelect').val(form.action);
						}else {
							$('#formBuilderSendLeadSelect').val('custom').change();
							$('#formBuilderCustomUrlInput').val(form.action);
						}
					}

					//form
					form_builder.find('#form_id').val(form.id);
					form_builder.find('#form_class').val(form.class);

					//fields
					form_builder_fields = data.fields;
					// console.log(form_builder_fields);
					$.each(data.fields, function(index, field)
					{
						// console.log(field);
						// var type,
						// 	sort = '',
						// 	remove = 'disabled',
						// 	value = field.value;

						field_names.push(field.name);
						// field_names[index] = field.name;

						// if($('select[name="value_select"] option[value="'+field.value+'"]').length > 0) {
						// 	value = $('select[name="value_select"] option[value="'+field.value+'"]').html();
						// }

						var field_content = addFormField(index);
					});

					//custom
					custom = data.custom;
					form_builder.find('#custom_css').val(custom.css);
					cssCodeMirror.getDoc().setValue(custom.css);
					form_builder.find('#custom_js').val(custom.js);
					jsCodeMirror.getDoc().setValue(custom.js);
				}else {
					form_builder_fields = [];
					field_names = [];
					formBuilderCodeMirror.getDoc().setValue('');
				}
				
				campaignCreativeTable.ajax.reload();
				$('#cmpFormBuilderModal').modal('show');
			}
		});
	});

	$(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#customs_tab"]', function() {
	    this.refresh();
	}.bind(cssCodeMirror));

	$(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#customs_tab"]', function() {
	    this.refresh();
	}.bind(jsCodeMirror));

	$(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#code_tab"]', function() {
	    this.refresh();
	}.bind(formBuilderCodeMirror));

    $(document).on('click','.removeFieldBtn', function() 
	{
		// console.log('Submit');
		confirmation = confirm('Are you sure you want to delete this field?');

		if(confirmation) {
			var the_index = $(this).data('id');
			delete form_builder_fields[the_index];
			delete field_names[the_index];
			// form_builder_fields.splice(the_index, 1);
			// field_names.splice(the_index, 1);
			$(this).parents('tr').remove();
			// console.log('Removing');
			// console.log(the_index);
			// console.log(form_builder_fields);
			// console.log(field_names);
		}
	});

	$(document).on('change','#formBuilderFieldTypeSelect', function() 
	{
		var value = $(this).val();

		if(value == 'article'){
			$('.input_field_group').hide();
			$('.article_field_group').show();
			$('#form_builder_field #name').removeAttr('required');
		}else if(value == 'dropdown' || value == 'radio' || value == 'checkbox') {
			$('.input_field_group').show();
			$('.article_field_group').hide();
			$('#form_builder_field #name').attr('required', true);

			$('.value_group_two').show();
			$('.value_group_one').hide();
		}else {
			$('.input_field_group').show();
			$('.article_field_group').hide();
			$('#form_builder_field #name').attr('required', true);

			$('.value_group_two').hide();
			$('.value_group_one').show();
		}
	});

	$(document).on('click','.viewFieldModalBtn', function() 
	{
		var id = $(this).data('id'),
			field = form_builder_fields[id],
			modal = $('#cmpFormFieldModal');
			
		// console.log(field);

		modal.find('.modal-title').html('Edit Field');
		modal.find('#label').val(field.label);
		modal.find('#name').val(field.name);
		modal.find('#id').val(field.id);
		modal.find('#class').val(field.class);
		modal.find('#field_id').val(id);
		modal.find('#formBuilderFieldTypeSelect').val(field.type).trigger('change');
		
		if(field.type == 'hidden' || field.type == 'text' || field.type == 'textbox') {
			if($('select[name="value_select"] option[value="'+field.value+'"]').length > 0) {
				$('select[name="value_select"]').val(field.value);
				$('input[name="value_input"]').val('');
			}else {
				$('select[name="value_select"]').val('');
				$('input[name="value_input"]').val(field.value);
			}
		}else if(field.type == 'article'){
			$('#article').val(field.value);
		}else {
			$.each(field.options.values, function(index, values)
			{
				var isDefault = false,
					isAccepted = false,
					display = field.options.displays;
				
				if(field.value == values) isDefault = true;

				if(field.has_accepts) {
					$key = field.accepted_options.indexOf(values);
					if($key >= 0) isAccepted = true;
				}
				// console.log(index + ' - ' + values + ' - ' + isDefault + ' - ' + isAccepted);
				// addFieldRow(value, display, isDefault, isAccepted)
				if(values != '') {
					addFieldRow(values, display[index], isDefault, isAccepted);
				}
			});
		}
			

		//required validtion
		if(field.standard) required = true;
		else {
			required = false;
			if(field.hasOwnProperty("validation")) {
				if(field.validation.hasOwnProperty("required")) {
					required = field.validation.required;
				}
			}
			// if('validation.required' in field) {
			// 	console.log(field.validation.required);
			// 	required = field.validation.required;
			// }else required = false;
		}
		$('[name="validation[]"][value="required"]').prop( "checked" , required);

		//validation

		if(field.hasOwnProperty('validation')) {
			if(typeof field.validation.alphaNumeric != "undefined") 
				$('[name="validation[]"][value="alphaNumeric"]').prop( "checked" , field.validation.alphaNumeric);

			if(typeof field.validation.alphaSpace != "undefined") 
				$('[name="validation[]"][value="alphaSpace"]').prop( "checked" , field.validation.alphaSpace);

			if(typeof field.validation.date != "undefined") 
				$('[name="validation[]"][value="date"]').prop( "checked" , field.validation.date);

			if(typeof field.validation.digits != "undefined") 
				$('[name="validation[]"][value="digits"]').prop( "checked" , field.validation.digits);

			if(typeof field.validation.email != "undefined")
				$('[name="validation[]"][value="email"]').prop( "checked" , field.validation.email);

			if(typeof field.validation.equalTo != "undefined") {
				$('[name="validation[]"][value="equalTo"]').prop( "checked" , true).trigger('change');
				$('input[name="equal_to_value"]').val(field.validation.equalTo);
			}
				
			if(typeof field.validation.max != "undefined") {
				$('[name="validation[]"][value="max"]').prop( "checked" , true).trigger('change');
				$('input[name="max_value"]').val(field.validation.max);
			}

			if(typeof field.validation.min != "undefined") {
				$('[name="validation[]"][value="min"]').prop( "checked" , true).trigger('change');
				$('input[name="min_value"]').val(field.validation.min);
			}

			if(typeof field.validation.number != "undefined") 
				$('[name="validation[]"][value="number"]').prop( "checked" , field.validation.number);

			if(typeof field.validation.phoneUS != "undefined") 
				$('[name="validation[]"][value="phoneUS"]').prop( "checked" , field.validation.phoneUS);

			if(typeof field.validation.range != "undefined") {
				$('[name="validation[]"][value="range"]').prop( "checked" , true).trigger('change');
				range = $.parseJSON(field.validation.range);
				$('input[name="range_min_value"]').val(range[0]);
				$('input[name="range_max_value"]').val(range[1]);
			}

			if(typeof field.validation.url != "undefined") 
				$('[name="validation[]"][value="url"]').prop( "checked" , field.validation.url);
			
			if(typeof field.validation.zip != "undefined")
				$('[name="validation[]"][value="zip"]').prop( "checked" , field.validation.zip);

			if(typeof field.validation.zip != "undefined")
				$('[name="validation[]"][value="zip"]').prop( "checked" , field.validation.zip);

			if(typeof field.validation.minWordCount != "undefined") {
				$('[name="validation[]"][value="minWordCount"]').prop( "checked" , true).trigger('change');
				$('input[name="min_word_count_value"]').val(field.validation.minWordCount);
			}
		}

		modal.modal('show');
	});


	$(document).on('click','#addFormField', function() 
	{
		$('#cmpFormFieldModal').modal('show');
	});

	$(document).on('change','#formBuilderSendLeadSelect', function() 
	{
		var value = $(this).val(),
			custom_url = $('#formBuilderCustomUrlInput'),
			form = $('#campaign_form_builder');

		if(value == 'custom') {
			custom_url.show();
		}else {
			custom_url.hide();

			if(value != '') {
				$.each(standard_fields, function(index, value) {
					
					if(field_names.indexOf(index) < 0) {
						var new_id = $('#formFieldsTable tbody tr').length,
							the_value = value;
						if(index == 'eiq_campaign_id') the_value = form.find('#this_campaign').val();

						field = { class: '', id: '', label: '', name: index, standard: true, type: 'hidden',  
							value : the_value};
						form_builder_fields[form_builder_fields.length] = field;
						field_names[field_names.length] = index;
						// form_builder_fields.push(field);
						// field_names.push(index);
						field_content = addFormField(new_id);
					}
				});

				if(value == 'lead_filter') {
					$.each(standard_lf_fields, function(index, value) {
					
						if(field_names.indexOf(index) < 0) {
							var new_id = $('#formFieldsTable tbody tr').length,
								the_value = value;
							if(index == 'program_id') the_value = form.find('#this_campaign').val();
							else if(index == 'program_name') the_value = $('#cmp-'+form.find('#this_campaign').val()+'-name').html();
							
							field = { class: '', id: '', label: '', name: index, standard: false, type: 'hidden',  
								value : the_value};
							form_builder_fields[form_builder_fields.length] = field;
							field_names[field_names.length] = index;
							// form_builder_fields.push(field);
							// field_names.push(index);
							field_content = addFormField(new_id);
						}
					});
				}
			}
		}
		// console.log(field_names);
		// console.log(form_builder_fields);
	});
	
	//SORT FIELD OPTIONS
	// var sorted_fields = [];
	var sorted = $('#fieldValueTable tbody').sortable({
		handle: '.sortValueHandle',
	});	

	$(document).on('click','.removeRowFielValue', function() 
	{
		$(this).parents('tr').remove();
	});
	

	$(document).on('click','.clearFieldValueTable', function() 
	{
		$('#fieldValueTable tbody').html('');
	});

	$(document).on('click','#addFieldValueBtn', function() 
	{
		addFieldRow('', '', false, false);
		$('#fieldValueTable tbody tr:last [name="field_value[]"]').focus();
	});

	$(document).on('click','#addRangeValuesBtn', function() 
	{
		var table = $('#fieldValueTable tbody'),
			min = $('#min_range_value').val(),
			max = $('#max_range_value').val();
			

		if(min > max) {
			for(var i = min; i >= max; i--) {
				addFieldRow(i,i, false, false);
			}
		}else {
			for(var i = min; i <= max; i++) {
				addFieldRow(i,i, false, false);
			}
		}

		$('#rangeValueCollapse').collapse('hide');

		// if(min.val() < max.val()) {
		// 	for(var i = min.val(); i <= max.val(); i++) {
		// 		addFieldRow(i,i, false, false);
		// 	}
		// 	$('#rangeValueCollapse').collapse('hide');
		// }
	});

	//Close Form Builder Modal
	$('#cmpFormBuilderModal').on('hide.bs.modal', function (e) {
		if($('.editCampaignCreative .glyphicon-remove').length > 0) {
			alert('Save unfinished campaign creatives before closing the form builder.');
			$('#cmpFormBuilderModal [href="#creatives_tab"]').trigger('click');
			e.preventDefault();
		    e.stopImmediatePropagation();
		    return false; 
		}
		
		$('#form-builder-campaign').html('');
		$('.form_builder_btn').removeAttr('disabled');
		$('#formBuilderSendLeadSelect').val('').trigger('change');
		$('#campaign_form_builder').find('.this_field').val('');
		$('#formFieldsTable tbody').html('');
		$('#autoLockAlert').remove();
		$('#cmpFormBuilderModal [href="#form_tab"]').trigger('click');
		cssCodeMirror.getDoc().setValue('');
		jsCodeMirror.getDoc().setValue('');
		formBuilderCodeMirror.getDoc().setValue('');
		setTimeout(function() {
			cssCodeMirror.refresh();
			jsCodeMirror.refresh();
			formBuilderCodeMirror.refresh();
		},100);
	});

	//Close Edit Field Modal
	$('#cmpFormFieldModal').on('hide.bs.modal', function () {
		$('#cmpFormFieldModal .modal-title').html('Add Field');
		$('#cmpFormFieldModal .this_field').val('');
		$('#formBuilderFieldTypeSelect').trigger('change');
		$('[name="validation[]"]').prop('checked', false);
		$('[name="validation[]"][value="required"]').prop('checked', true);
		$('#fieldValueTable tbody').html('');

		//remove required of validation fields
		$('[name="equal_to_value"]').removeAttr('required');
		$('[name="range_min_value"]').removeAttr('required');
		$('[name="range_max_value"]').removeAttr('required');
		$('[name="min_value"]').removeAttr('required');
		$('[name="max_value"]').removeAttr('required');
		$('[name="min_word_count_value"]').removeAttr('required');
		$('#uploadValueCollapse').collapse('hide');

		$('.selectAcceptedValues').prop('checked',false);
	});

	$('#rangeValueCollapse').on('hidden.bs.collapse', function () {
	 	$('#min_range_value').val('');
	 	$('#max_range_value').val('');
	});

	$('#rangeValueCollapse').on('show.bs.collapse', function () {
	 	$('#uploadValueCollapse').collapse('hide');
	});

	$('#uploadValueCollapse').on('show.bs.collapse', function () {
	 	$('#rangeValueCollapse').collapse('hide');
	});

	// $(document).on('click','#uploadFileValuesBtn', function() 
	// {
	// 	var table = $('#fieldValueTable tbody'),
	// 		min = $('#min_range_value'),
	// 		max = $('#max_range_value');

	// 	if(min.val() < max.val()) {
	// 		for(var i = min.val(); i <= max.val(); i++) {
	// 			addFieldRow(i,i, false, false);
	// 		}

	// 		$('#rangeValueCollapse').collapse('hide');
	// 	}
	// });

	function addFieldRow(value, display, isDefault, isAccepted){
		var table = $('#fieldValueTable tbody'),
			ifDefault = '',
			ifAccepted ='',
			tr_id = $('#fieldValueTable tbody tr').length;

			if(isDefault) ifDefault = 'checked';
			if(isAccepted) ifAccepted = 'checked';
		var row = '<tr><th><span class="sortValueHandle glyphicon glyphicon-resize-vertical"></span></th>' +
				'<th><input name="default_value" type="radio" value="'+tr_id+'" '+ifDefault+'></th>' +
				'<th><input name="field_value[]" value="'+value+'" type="text" class="form-control" required></th>' +
				'<td><textarea name="field_display[]" class="form-control cmpFB_item" rows="2" required>'+display+'</textarea></td>' +
				'<th><input name="accepted[]" type="checkbox" value="'+tr_id+'" '+ifAccepted+'></th>' +
                '<td><button class="removeRowFielValue btn btn-default pull-right"><span class="glyphicon glyphicon-remove"></span>' + 
                '</button></td></tr>';
        table.append(row);
	}

	$(document).on('submit','#form_builder_field', function(e) 
	{
		e.preventDefault();

		var form = $(this),
			id = $('#field_id').val(),
			this_field = new Object(),
			isInShortCodeDropdown = false;

		// console.log(id);

		this_field.name = form.find('#name').val();
		this_field.type = $('#formBuilderFieldTypeSelect').val();
		this_field.id = form.find('#id').val();
		this_field.class = form.find('#class').val();
		this_field.label = form.find('#label').val();

		if(this_field.type == 'article') {
			this_field.value = form.find('#article').val();
			prev_val = this_field.value;
			prev_val = prev_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
			prev_val = prev_val.replace(/<\/?[^>]+(>|$)/g, "");
			prev_val = prev_val.substr(0,150);
			if(this_field.value.length > 150) prev_val += '...';
			field_value = '<small><em>'+prev_val+'</em></small>';
			
			if(id > 0 || id != '') { //edit existing article field row
				form_builder_fields[id] = this_field;
				$('#fbf-name-'+id).html('');
				$('#fbf-type-'+id).html(this_field.type);
				$('#fbf-value-'+id).html(field_value);
			}else{ //add
				var new_id = form_builder_fields.length;
				form_builder_fields[new_id] = this_field;
				field_names[field_names.length] = this_field.name;
				field_content = addFormField(new_id);
			}
		}else {

			if(this_field.type == 'dropdown' || this_field.type == 'radio' || this_field.type == 'checkbox') {
				var option_values = new Array(),
					option_displays = new Array(),
					this_displays = $('[name="field_value[]"]'),
					hasAccepts = false,
					option_accepts = new Array();
					
				
				$('[name="field_display[]"]').each(function(i, obj) {
					option_values.push($(this_displays[i]).val());
					option_displays.push($(obj).val());
				});
				options = { values: option_values, displays : option_displays};
				this_field.options= options;

				$('[name="accepted[]"]').each(function(i, obj) {
					if($(this).prop('checked')) {
						hasAccepts = true;
						value = $('#fieldValueTable tbody tr:nth('+i+')').find('[name="field_value[]"]').val();
						option_accepts.push(value);
					}
				});

				this_field.has_accepts = hasAccepts;
				this_field.accepted_options = option_accepts;
				

				this_field.value = '';
				if($('[name="default_value"]:checked').length > 0) {
					this_index = $('[name="default_value"]:checked').val();
					this_field.value = $('#fieldValueTable tbody tr:nth('+this_index+')').find('[name="field_value[]"]').val();
				}
				// console.log('Default: '+ this_field.value);
				// this_field['options']['values'] = option_values;
				// this_field['options']['displays'] = option_displays;
			}else {
				if($('[name="value_select"]').val() != '') {
					this_field.value = $('[name="value_select"]').val();
					isInShortCodeDropdown = true;
				}
				else if($('[name="value_input"]').val() != '') this_field.value = $('[name="value_input"]').val();
				else this_field.value = '';
			}

			var validations = {};
			$('[name="validation[]"]').each(function(i, obj) {
				var valid_value = $(this).val();
				if(valid_value == 'equalTo'){
					if($(this).prop('checked')) validations[valid_value] = $('[name="equal_to_value"]').val();
				}else if(valid_value == 'min'){
					if($(this).prop('checked')) validations[valid_value] = $('[name="min_value"]').val();
				}else if(valid_value == 'max'){
					if($(this).prop('checked')) validations[valid_value] = $('[name="max_value"]').val();
				}else if(valid_value == 'range'){
					if($(this).prop('checked')) validations[valid_value] = '[' + $('[name="range_min_value"]').val() + ',' + $('[name="range_max_value"]').val() + ']';
				}else if(valid_value == 'minWordCount'){
					if($(this).prop('checked')) validations[valid_value] = $('[name="min_word_count_value"]').val();
				}else {
					validations[valid_value] = $(this).prop('checked');
				}
			});
			this_field.validation = validations;

			if(id > 0 || id != '') {
				//edit
				form_builder_fields[id] = this_field;
				field_names[id] = this_field.name;
				value_display = this_field.value;
				if(isInShortCodeDropdown == true) value_display = $('[name="value_select"] option[value="'+this_field.value+'"]').html();
				
				//get value
				if(value_display != '') {
					if(this_field.type == 'dropdown' || this_field.type == 'checkbox' || this_field.type == 'radio') {
						def_val_indx = this_field.options.values.indexOf(this_field.value);
						def_val = value_display;
						if(def_val != this_field.options.displays[def_val_indx]) {
							def_val += ' - ' + this_field.options.displays[def_val_indx];
						}
					}else {
						def_val = value_display;
					}

					def_val_prev = def_val.substr(0,74);
					if(def_val.length > 74) def_val_prev += '...';
					field_value = '<span class="label label-primary">'+def_val_prev+'</span>';
				}else field_value = '';
				
				//get some options if exists
				if(this_field.type == 'dropdown' || this_field.type == 'checkbox' || this_field.type == 'radio') {
					if(typeof this_field.options != undefined) {
						fieldCounter = 0;
						$.each(this_field.options.values, function(i, oVal) {
							if(oVal != '') {
								if(fieldCounter <= 3) {
									if(oVal != this_field.value) {
										if(oVal == this_field.options.displays[i]) opt_val = oVal;
										else opt_val = oVal + ' - ' + this_field.options.displays[i];
										
										opt_val_prev = opt_val.substr(0,74);
										if(opt_val.length > 74) opt_val_prev += '...';
										field_value += ' <span class="label label-info">'+ opt_val_prev +'</span>';
										fieldCounter++;
									}
								}else {
									field_value += ' <span class="label label-info">...</span>';
									return false;
								}
							}
						});
					}
				}

				//get name/label
				var name_display = this_field.name;
				if(this_field.label != '') {
					name_display += '<br><small><em>' + this_field.label.substr(0,74);
					if(this_field.label.length > 74) name_display += '...';
					name_display += '</em></small>';
				}

				// console.log(form_builder_fields[id]);
				$('#fbf-name-'+id).html(name_display);
				$('#fbf-type-'+id).html(this_field.type);
				$('#fbf-value-'+id).html(field_value);
			}else {
				//add
				// form_builder_fields.push(this_field);
				// field_names.push(this_field.name);
				// console.log('Add');
				var new_id = form_builder_fields.length;
				form_builder_fields[new_id] = this_field;
				field_names[field_names.length] = this_field.name;
				// value_display = this_field.value;
				// if(isInShortCodeDropdown == true) value_display = $('[name="value_select"] option[value="'+this_field.value+'"]').html();
				// var new_id = $('#formFieldsTable tbody tr').length,
					field_content = addFormField(new_id);

	    		// console.log(this_field);
	    		// console.log(form_builder_fields);
			}
		}

		$('#cmpFormFieldModal').modal('hide');
		// console.log(this_field);
	});

	$(document).on('change','[name="validation[]"]', function() 
	{
		var type = $(this).val(),
			isChecked = $(this).prop('checked');

		if(type == 'equalTo') {
			if(isChecked) $('[name="equal_to_value"]').attr('required',true);
			else $('[name="equal_to_value"]').removeAttr('required');
		}else if(type == 'range') {
			if(isChecked) {
				$('[name="range_min_value"]').attr('required',true);
				$('[name="range_max_value"]').attr('required',true);
			}else {
				$('[name="range_min_value"]').removeAttr('required');
				$('[name="range_max_value"]').removeAttr('required');
			}
		}else if(type == 'min') {
			if(isChecked) $('[name="min_value"]').attr('required',true);
			else $('[name="min_value"]').removeAttr('required');
		}else if(type == 'max') {
			if(isChecked) $('[name="max_value"]').attr('required',true);
			else $('[name="max_value"]').removeAttr('required');
		}else if(type == 'minWordCount') {
			if(isChecked) $('[name="min_word_count_value"]').attr('required',true);
			else $('[name="min_word_count_value"]').removeAttr('required');
		}
	});

	$(document).on('submit','#campaign_form_builder', function(e) 
	{
		e.preventDefault();

		var form =  $('#campaign_form_builder'),
			final_form_builder_fields = [],
			submitBtn = $('#cmpConfigAutomationSubmit');

		//check for unsaved campaign creative
		if($('.editCampaignCreative .glyphicon-remove').length > 0) {
			alert('Campaign Form Builder NOT SAVED YET. Save unfinished campaign creatives first.');
			$('[href="#creatives_tab"]').trigger('click');
			return;
		}

		// console.log('Submit');
		confirmation = confirm('Are you sure you want to update this campaign content?');

		if(confirmation) {
			$('#formFieldsTable tbody tr').each(function(index, object) {
				final_form_builder_fields.push(form_builder_fields[$(this).data('id')]);
			});

			submitBtn.attr('disabled','true');
            submitBtn.html('<i class="fa fa-spin fa-spinner"></i>');
            // console.log(final_form_builder_fields);
			$.ajax({
				type: 'POST',
				data: {
					'id'		: form.find('.this_campaign').val(),
					'type'		: $('#formBuilderCampaignType').val(),
					'form[url]'  : $('#formBuilderSendLeadSelect').val(),
					'form[custom_url]'  : form.find('[name="custom_url"]').val(),
					'form[id]'  : form.find('[name="form_id"]').val(),
					'form[class]'  : form.find('[name="form_class"]').val(),
					'fields'	: final_form_builder_fields,
					'custom[css]'  : form.find('[name="custom_css"]').val(),
					'custom[js]'  : form.find('[name="custom_js"]').val(),
					'form[linkout_url]'  : form.find('[name="redirect_link"]').val(),
				},
				url: $('#baseUrl').html() + '/campaign_form_builder',
				success: function(data) {
					// console.log(data);

					form.find('#stack_code').val(data.stack);
					formBuilderCodeMirror.getDoc().setValue(data.stack);
					form.find('#form_id').val(data.form_id);
					form.find('#custom_js').val(data.js);
					jsCodeMirror.getDoc().setValue(data.js);
					
					// $('#cmpFormBuilderModal').modal('show');
					submitBtn.html('Save');
                    submitBtn.removeAttr('disabled');
					alert('Campaign Form Builder Modification Saved!');
				}
			});
		}
	});

	//Update Campaign Content Form Builder Lock
	$('#stack_form_lock').change(function() {

        // console.log('reorder toggle: ' + $(this).prop('checked'));
        if($(this).prop('checked'))
        {
            $(this).val(1);
           	confirm_stat = 'DISABLED';
           	stack_lock = 1;
        }
        else
        {
            $(this).val(0);
            confirm_stat = 'ENABLED';
            stack_lock = 0;
        }

        if ($('#editCampaignStackContent').is(':visible')) {
        	// var confirmation = confirm('You are about to '+ confirm_stat + ' this campaign from updating campaign content through the form builder');
	        // if(confirmation) {
	        	$.ajax({
					type: 'POST',
					data: {
						'id' : $('#editCmpFormModal').find('#this_id').val(),
						'lock' : stack_lock
					},
					url: $('#baseUrl').html() + '/update_campaign_stack_lock',
					success: function(data) {
						alert('This campaign is now '+ confirm_stat + ' from being updated through the content form builder')
						console.log(data);
					}
				});
	        // }
        }  
    });

    $('.selectAcceptedValues').change(function() {
        $('[name="accepted[]"').prop('checked',$(this).prop('checked'));
    });

    $('#fieldValueTable').on('change','[name="accepted[]"]', function()  {
    	var ifChecked = false;
    	if($('#fieldValueTable').find('[name="accepted[]"]:checked').length == $('#fieldValueTable').find('[name="accepted[]"]').length) {
    		ifChecked = true;
    	}
    	$('.selectAcceptedValues').prop('checked',ifChecked);
    });

    $('.clearDefaultValRadio').click(function() {
        $('[name="default_value"').prop('checked',false);
    });

    $(document).on('click','#uploadFileValuesBtn', function() 
	{
		var file = $('#upload_file_value').val(),
			label = $('[for="upload_file_value"]');

		label.removeClass('error_label');
		if(file != '') {
			var file_data = $('#upload_file_value').prop('files')[0];   
	    	var formData = new FormData();
	    	formData.append('file', file_data);
	    	// console.log(file_data);
	    	$.ajax({
	            url: $('#baseUrl').html() + '/campaign_upload_field_options',
	            dataType: 'text', 
	            cache: false,
	            contentType: false,
	            processData: false,
	            data: formData,                         
	            type: 'post',
	            success: function(data){
	                options = $.parseJSON(data);
	                displays = options.displays;
	                $.each(options.values, function(i, value) {
	                	addFieldRow(value, displays[i], false, false);
	                });
	                // console.log(options);
	                $('#uploadValueCollapse').collapse('hide');
	            }
	     	});
		}else {
			label.addClass('error_label');
		}
    });

    $('#uploadValueCollapse').on('hide.bs.collapse', function () {
	 	$('#upload_file_value').val('');
	 	$('[for="upload_file_value"]').removeClass('error_label');
	});

	$(document).on('change','[name="value_select"]', function(e) 
	{
		$('[name="value_input"]').val('');
	});

	$(document).on('change','[name="value_input"]', function(e) 
	{
		$('[name="value_select"]').val('');
	});

	/* Campaign Affiliate Management */

    var affiliateSelect = $('#searchCmpAffMgmt').select2({
        //tags: true,
        // placeholder: 'Select the id or name of the affiliate.',
        // minimumInputLength: 2,
        dropdownParent: $("#affCampMgmtModal"),
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
                // console.log(params);
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

  	var campaignAffiliateManagementTable = $('#cmpAffMgmt-table')
	.on( 'page.dt',   function () {
    	$('#allCAMcampaigns-chkbx').prop('checked', false).trigger('change');
    	$('#allCAMTblcampaigns-chkbx').prop('checked', false).trigger('change');
    })
	.DataTable({
		'processing': true,
		'serverSide': true,
		'columns': [
			{ 'orderable': false },
			null,
			null,
			null,
			null,
			null,
		],
		"order": [[ 1, "asc" ]],
		'ajax':{
			url:$('#baseUrl').html() + '/campaigns/datatable/affiliate_managament', // json datasource
			type: 'post',  // method
			'data': function(d)
            {
                d.operation = $('#cam_op_chkbx').val();
                d.affiliate = $('#searchCmpAffMgmt').val();
            },
            "dataSrc": function ( json ) {
                // $('#downloadSurveyTakers').removeAttr('disabled');
                return json.data;
            },
		},
		"createdRow": function( row, data, dataIndex ) {
            if(data[5] != 'Default' && data[5] != '') {
            	$(row).addClass('cam_active_row');
            }
            // console.log(row);
            // console.log(data);
            // console.log(dataIndex);
        }
	});
  	$('#cam_op_chkbx').bootstrapToggle('off');

  	function checkValidCamAffMgmt() {
  		var submitBtn = $('#campAffMgmtSubmitBtn'),
  			aff = $('#searchCmpAffMgmt').val(),
  			checkAll = $('#allCAMTblcampaigns-chkbx').prop('checked'),
  			allCamp = $('#allCAMcampaigns-chkbx').prop('checked'),
  			camp = $('[name="cam_campaign[]"]:checked').length,
  			cap_type = $('.camAddAff-div #lead_cap_type').val(),
  			cap_val = Math.floor(Number($('.camAddAff-div #lead_cap_value').val())),
  			op = $('#cam_op_chkbx').val(),
  			errorNum = 0;

  		if(op == 1) {
  			if(cap_type > 0 && !(cap_val > 0)) errorNum++;
  		}

  		if(aff === null) errorNum++;

  		if(!checkAll && !allCamp && camp == 0) errorNum++;

  		if(errorNum == 0) submitBtn.show();
  		else submitBtn.hide();
  	}

    $('#affCampMgmtModal').on('change','#allCAMcampaigns-chkbx', function(e) 
    {
        if($(this).prop('checked')){
        	$('[name="cam_campaign[]"]').prop('checked',false).attr('disabled', true);
        	$('#allCAMTblcampaigns-chkbx').prop('checked', false).attr('disabled', true);
        }else
        {
            $('[name="cam_campaign[]"]').prop('checked',false).removeAttr('disabled');
        	$('#allCAMTblcampaigns-chkbx').prop('checked',false).removeAttr('disabled');
        }

        $('.cam_cmp_type').prop('checked', false);

        checkValidCamAffMgmt();
    });

    $('#affCampMgmtModal').on('change','#allCAMTblcampaigns-chkbx', function(e) 
    {
    	$('[name="cam_campaign[]"]').prop('checked',$(this).prop('checked'));
    	$('.cam_cmp_type').prop('checked', $(this).prop('checked'));

        if($(this).prop('checked')){
        	$('#allCAMcampaigns-chkbx').prop('checked', false).attr('disabled', true);
        }else
        {
        	$('#allCAMcampaigns-chkbx').prop('checked',false).removeAttr('disabled');
        }

        checkValidCamAffMgmt();
    });

    $('#cmpAffMgmt-table').on('change','[name="cam_campaign[]"]', function()  {

    	$('[name="cam_campaign_type['+$(this).val()+']"]').prop('checked', $(this).prop('checked'));

    	var ifChecked = false;
    	if($('#cmpAffMgmt-table').find('[name="cam_campaign[]"]:checked').length == $('#cmpAffMgmt-table').find('[name="cam_campaign[]"]').length) {
    		ifChecked = true;
    	}
    	$('#allCAMTblcampaigns-chkbx').prop('checked',ifChecked);

    	checkValidCamAffMgmt();
    });

    $('.camAddAff-div').on('change','[name="lead_cap_type"]', function(e) 
    {
    	var $value = $('.camAddAff-div').find('[name="lead_cap_value"]');
    	if($(this).val() > 0) {
    		$value.removeAttr('disabled');
    	}else {
    		$value.attr('disabled', true);
    	}
    	$value.val('');

    	checkValidCamAffMgmt();
    });

    $('.camAddAff-div').on('change','[name="lead_cap_value"]', function(e) 
    {
    	checkValidCamAffMgmt();
    });

	$(document).on('click','#affiliateCampaignMgmtBtn', function() 
	{
		// campaignAffiliateManagementTable.ajax.reload();
		$('#affCampMgmtModal').modal('show');
		checkValidCamAffMgmt();
    });

	// change event for operation
    $('#affCampMgmtModal').on('change','#cam_op_chkbx', function(e) 
    {
    	// $('#allCAMTblcampaigns-chkbx').prop('checked', false).trigger('change');
        if($(this).prop('checked')) {
        	//Add
        	$(this).val(1);

        	$('.camAddAff-div').show();

			// $('#affCampMgmtModal').modal('show');
        }
        else { 
        	//Remove
        	$(this).val(0);

        	$('.camAddAff-div').hide();
        	$('.camAddAff-div').find('[name="lead_cap_type"]').val(0).trigger('change');
        }

        if($('#searchCmpAffMgmt').val() != null) campaignAffiliateManagementTable.ajax.reload();
    	
    	checkValidCamAffMgmt();
    });

    $('#affCampMgmtModal').on('hidden.bs.modal', function (e) {
		$('#searchCmpAffMgmt').val(null).trigger('change');
		$('#cam_op_chkbx').bootstrapToggle('off');
		$('.camAddAff-div').hide();
		$('.camAddAff-div').find('[name="lead_cap_type"]').val(0).trigger('change');
		campaignAffiliateManagementTable.ajax.reload();
	});

    $('#affCampMgmtModal').on('change','#searchCmpAffMgmt', function(e) 
    {
    	campaignAffiliateManagementTable.ajax.reload();

    	checkValidCamAffMgmt();
    });

    //Get Available Affiliates
    var campaign_available_affiliates = null;
    $('[href="#affiliate_tab"]').click(function() {
		if(campaign_available_affiliates == null) {
			var addBtn = $('#addCampAff');
				addBtn.find('span').removeClass().addClass('fa fa-spin fa-spinner');
				addBtn.attr('disabled', true);
			$.ajax({
				type : 'POST',
				url  : $('#baseUrl').html() + '/get_available_affiliates_for_campaign',
				data : {
					'id' : $('.this_campaign').val()
				}, 
				success : function(affiliates) {
					// console.log(affiliates);
					campaign_available_affiliates = affiliates;
					$('select[name="affiliates[]"] option').remove();
					var aff_select = $('select[name="affiliates[]"]');
					$.each(campaign_available_affiliates, function(index,value){
						aff_select.append('<option value="'+index+'">'+value+'</option>');
					});		
					var affiliate_count = $('select[name="affiliates[]"] option').length;
					if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);
					else $('select[name="affiliates[]"]').attr('size', 10);

					addBtn.find('span').removeClass().addClass('glyphicon glyphicon-plus');
					addBtn.removeAttr('disabled');
				}
			});
		}
	});

    //Get Available Affiliates for Payout
    var campaign_payout_available_affiliates = null;
    $('[href="#payout_tab"]').click(function() {
		if(campaign_payout_available_affiliates == null) {
			var addBtn = $('#addPytAff');
				addBtn.find('span').removeClass().addClass('fa fa-spin fa-spinner');
				addBtn.attr('disabled', true);
			$.ajax({
				type : 'POST',
				url  : $('#baseUrl').html() + '/get_available_affiliates_for_campaign_payout',
				data : {
					'id' : $('.this_campaign').val()
				}, 
				success : function(affiliates) {
					// console.log(affiliates);
					campaign_payout_available_affiliates = affiliates;
					$('select[name="payout[]"] option').remove();
					var aff_select = $('select[name="payout[]"]');
					$.each(campaign_payout_available_affiliates, function(index,value){
						aff_select.append('<option value="'+index+'">'+value+'</option>');
					});		
					var affiliate_count = $('select[name="payout[]"] option').length;
					if(affiliate_count <= 10) $('select[name="payout[]"]').attr('size', affiliate_count);
					else $('select[name="payout[]"]').attr('size', 10);

					addBtn.find('span').removeClass().addClass('glyphicon glyphicon-plus');
					addBtn.removeAttr('disabled');
				}
			});
		}
	});

	// var campaign_affiliate_datatable = $('#campaign-affiliates-table').DataTable({
	// 	'processing': true,
	// 	'serverSide': true,
	// 	'columns': [
	// 		{ 'orderable': false },
	// 		null,
	// 		null,
	// 		null,
	// 		null,
	// 	],
	// 	"order": [[ 1, "asc" ]],
	// 	'ajax':{
	// 		url:$('#baseUrl').html() + '/campaign_affiliates_datatable',
	// 		type: 'post',
	// 		'data': function(d)
	//         {
	//             d.status = $('#campAffiliaStatus').val();
	//             d.campaign = $('.this_campaign').val();
	//         },
	// 		error: function(data) //error handling
	// 		{
	// 			console.log(data);
	// 		}
	// 	},
	// 	"createdRow": function( row, data, dataIndex ) {
 //            if(data[2] != 'Default' && data[2] != '') {
 //            	$(row).addClass('camAff_active_row');
 //            }
 //        },
	// 	lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
	// 	"sDom": 'lf<"camAffAddtnlToolbar">rtip'
	// });

	// $("div.camAffAddtnlToolbar").html('<label> Affiliate: <select id="campAffiliaStatus" class="form-control input-sm"><option value="1">Active</option><option value="0">Inactive</option><option value="">All</option></select></label>');

	// $(document).on('change','#campAffiliaStatus', function() 
	// {
	// 	campaign_affiliate_datatable.ajax.reload();
 //    });

	// var affiliate_table = $('#campaign-affiliate-table').DataTable({
	// 	'processing': true,
	// 	'serverSide': true,
	// 	"columns": [
	// 		{ "orderable": false },
	// 		null,
	// 		null,
	// 		null,
	// 		{ "orderable": false }
	// 	],
	// 	"order": [[ 1, "asc" ]],
	// 	'ajax':{
	// 		url: $('#baseUrl').html() + '/get_campaign_affiliates', // json datasource
	// 		type: 'post',  // method  , by default get
	// 		'data': function(d)
	//         {
	//             d.campaign = $('.this_campaign').val();
	//         },
	// 		error: function(){  // error handling

	// 		}
	// 	},
	// 	lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
	// });

	// $('.search-campaign-affiliate-select').select2({
 //        //tags: true,
 //        placeholder: 'Select the id or name of the affiliate.',
 //        minimumInputLength: 1,
 //        dropdownParent: $("#editCmpFormModal"),
 //        theme: 'bootstrap',
 //        language: {
 //            inputTooShort: function(args) {
 //                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
 //            }
 //        },
 //        ajax: {
 //            url: $('#baseUrl').html()+'/search/select/campaignAffiliate',
 //            dataType: "json",
 //            type: "POST",
 //            data: function (params) {

 //                var queryParameters = {
 //                    term: params.term,
 //                    campaign: $('.this_campaign').val()
 //                };
 //                return queryParameters;
 //            },
 //            processResults: function (data) {
 //                return {
 //                    results: $.map(data.items, function (item) {
 //                        return {
 //                            text: item.name,
 //                            id: item.id
 //                        }
 //                    })
 //                };
 //            }
 //        }
 //    });

 	//CAMPAIGN CONFIG FTP
 	//Update Campaign Content Form Builder Lock
	$('#if_ftp_sent').change(function() {
	    // console.log('reorder toggle: ' + $(this).prop('checked'));
	    if($(this).prop('checked'))
	    {
	        $(this).val(1);
	       	$('.cmpCfgFtp-fld').attr('required', true);

	       	if($('#cmpCfg-ftppt-txt input').val() == '') {
	       		$('#cmpCfg-ftppt-txt input').val(21);
	       	}

	       	if($('#cmpCfg-ftpto-txt input').val() == '') {
	       		$('#cmpCfg-ftpto-txt input').val(30);
	       	}
	    }
	    else
	    {
	        $(this).val(0);
	        $('.cmpCfgFtp-fld').removeAttr('required');
	    }
	});

	$('#if_email_sent').change(function() {
	    // console.log('reorder toggle: ' + $(this).prop('checked'));
	    if($(this).prop('checked'))
	    {
	        $(this).val(1);
	       	$('.cmpCfgSTE-fld').attr('required', true);
	    }
	    else
	    {
	        $(this).val(0);
	        $('.cmpCfgSTE-fld').removeAttr('required');
	    }
	});

	/* JSON FORM BUILDER */

	var json_form_builder_fields = [],
    	json_field_names = [];

    var json_sorted = $('#jsonFormFieldsTable tbody').sortable({
		handle: '.sortJsonFieldHandle',
		items: "tr:not(.sortDisabled)"
	});	

	var scriptCodeMirror = CodeMirror.fromTextArea($('#custom_script')[0], {
	  	theme: 'default',
	    lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        // mode: 'javascript',
        // mode: 'text/html',
        indentUnit: 4,
        indentWithTabs: true
	});

	function addJsonFieldRow(value, display, image_url,  isDefault, isAccepted){
		var table = $('#jsonFieldValueTable tbody'),
			ifDefault = '',
			ifAccepted ='',
			tr_id = $('#jsonFieldValueTable tbody tr').length;

			if(isDefault) ifDefault = 'checked';
			if(isAccepted) ifAccepted = 'checked';
		var row = '<tr><th><span class="sortValueHandle glyphicon glyphicon-resize-vertical"></span></th>' +
				'<th><input name="default_value" type="radio" value="'+tr_id+'" '+ifDefault+'></th>' +
				'<th valign="middle"><input name="field_value[]" value="'+value+'" type="text" class="form-control" required></th>' +
				'<td><div class="row"><div class="col-md-12"><img src="'+image_url+'"></div><div class="col-md-12 form_div" style=""><label>Image URL:</label><input class="form-control this_field" name="field_img_value[]" type="text" value="'+image_url+'"></div><div class="col-md-12 form_div mt-1" style=""><label>Description</label><textarea name="field_display[]" class="form-control cmpFB_item" rows="2" required>'+display+'</textarea></div></div></td>' +
				'<th><input name="accepted[]" type="checkbox" value="'+tr_id+'" '+ifAccepted+'></th>' +
                '<td><button class="removeRowFielValue btn btn-default pull-right"><span class="glyphicon glyphicon-remove"></span>' + 
                '</button></td></tr>';
        table.append(row);
	}

	function addJsonFormField(index) {
    	var field_table = $('#jsonFormFieldsTable tbody'),
    		field = json_form_builder_fields[index],
    		type = field.type,
    		value = field.value,
    		name = field.name;

    	// console.log(field);

    	if(field.value.indexOf('"') < 0) {
	    	if($('select[name="value_select"] option[value="'+field.value+'"]').length > 0) {
				value = $('select[name="value_select"] option[value="'+field.value+'"]').html();
			}
		}

    	//check if standard field
    	if(field.standard == 'true') {
			remove = 'disabled';
			sort = '';
			sortable = 'class="sortDisabled"';
		}else {
			sort = '<span class="sortJsonFieldHandle glyphicon glyphicon-resize-vertical"></span>';
			remove = '';
			sortable = '';
		}

		//get value
		if(value != '') {
			if(type == 'article'){
				prev_val = value;
				prev_val = prev_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
				prev_val = prev_val.replace(/<\/?[^>]+(>|$)/g, "");
				prev_val = prev_val.substr(0,150);
				if(value.length > 150) prev_val += '...';
				field_value = '<small><em>'+prev_val+'</em></small>';
			}else {
				def_val = value;
				if(type == 'dropdown' || type == 'checkbox' || type == 'radio') {
					def_val_indx = field.options.values.indexOf(field.value);
					if(field.value != field.options.displays[def_val_indx]) {
						def_val += ' - ' + field.options.displays[def_val_indx];
					}
				}
				def_val = def_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
				def_val_prev = def_val.substr(0,74);
				if(def_val.length > 74) def_val_prev += '...';
				field_value = '<span class="label label-primary">'+def_val_prev+'</span>';
				// console.log(def_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""));
			}
		}else field_value = '';
		
		//get some options if exists
		if(type == 'dropdown' || type == 'checkbox' || type == 'radio') {
			if(typeof field.options != undefined) {
				fieldCounter = 0;
				$.each(field.options.values, function(i, oVal) {
					if(oVal != '') {
						if(fieldCounter <= 3) {
							if(oVal != field.value) {
								if(oVal == field.options.displays[i]) opt_val = oVal;
								else opt_val = oVal + ' - ' + field.options.displays[i];
								opt_val = opt_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
								opt_val_prev = opt_val.substr(0,74);
								if(opt_val.length > 74) opt_val_prev += '...';
								field_value += ' <span class="label label-info">'+ opt_val_prev +'</span>';
								fieldCounter++;
							}
						}else {
							field_value += ' <span class="label label-info">...</span>';
							return false;
						}
					}
				});
			}
		}

		//get name/label
		var name_display = name;
		if(field.label != '') {
			name_display += '<br><small><em>' + field.label.substr(0,74);
			if(field.label.length > 74) name_display += '...';
			name_display += '</em></small>';
		}

    	field_content = '<tr '+sortable+' data-id="'+index+'"><th>'+sort+'</th>' +
			'<th id="jfbf-name-'+index+'">'+ name_display +'</th>' +
          	'<td><span id="jfbf-type-'+index+'" class="label label-default">'+ type +'</span></td>' +
          	'<td id="jfbf-value-'+index+'">'+ field_value +'</td>' +
          	'<td><button type="button" class="removeJsonFieldBtn btn btn-default pull-right" style="margin-left: 5px" data-id="'+index+'" '+remove+'><span class="glyphicon glyphicon-trash"></span></button>' +
          	'<button type="button" class="viewJsonFieldModalBtn btn btn-default pull-right" data-id="'+index+'"><span class="glyphicon glyphicon-pencil"></span></button></td>' +
            '</tr>';

        field_table.append(field_content);
    }

	$(document).on('click','.campaignJsonFormBuilder', function() 
	{
		var id = $(this).data('id'),
			campaign_type = $(this).data('type'),
			form_builder = $('#campaign_json_form_builder'),
			the_url = $('#baseUrl').html() + '/get_campaign_json_content';

		form_builder.find('#this_campaign').val(id);
		$('.this_campaign').val(id);
		$('#cmpJsonFormBuilderModal #cmpName').html(id + ' - ' + $('#cmp-'+id+'-name').html());
		$('#cmpJsonFormBuilderModal #jsonFormBuilderCampaignType').val(campaign_type);

		//campaign type
		if(campaign_type == 5 || campaign_type == 6) { //link out
			$('.jsonFormBuilderCoregElement').hide();
			$('.jsonFormBuilderCoregElement').find('[name="send_lead_to"]').removeAttr('required');
			$('.jsonFormBuilderLinkoutElement').show();
			$('.jsonFormBuilderLinkoutElement').find('[name="redirect_link"]').attr('required', true);
		}else {
			$('.jsonFormBuilderCoregElement').show();
			$('.jsonFormBuilderCoregElement').find('[name="send_lead_to"]').attr('required', true);
			$('.jsonFormBuilderLinkoutElement').hide();
			$('.jsonFormBuilderLinkoutElement').find('[name="redirect_link"]').removeAttr('required');
		}

		$.ajax({
			type: 'POST',
			data: {
				'id'	:	id,
				'type'	:   campaign_type
			},
			url: the_url,
			success: function(data) {
				console.log(data);

				if(data.json != null) {
					var json = data.json,
						form = json.form,
						field_table = $('#jsonFormFieldsTable tbody');
					// console.log(json);
					// //lock
					// if(data.lock == 1) {
					// 	$('.form_builder_btn').attr('disabled',true);
					// }

					if(campaign_type == 5) {
						form_builder.find('#redirect_link').val(form.redirect_link);
					}else {
						if(form.url == 'lead_reactor' || form.url == 'lead_filter') {
							$('#jsonFormBuilderSendLeadSelect').val(form.url);
						}else {
							$('#jsonFormBuilderSendLeadSelect').val('custom').change();
							$('#jsonFormBuilderCustomUrlInput').val(form.custom_url);
						}
					}

					//form
					form_builder.find('#form_id').val(form.id);
					form_builder.find('#form_class').val(form.class);
					form_builder.find('#redirect_link').val(form.linkout_url);

					json_form_builder_fields = [];
					json_field_names = [];
					//fields
					$.each(json.fields, function(index, field)
					{
						var this_field = field;
						this_field.has_accepts = field.has_accepts == 'true' ? true : false;
						
						if(field.hasOwnProperty("validation")) {
							$.each(field.validation, function(name, val)
							{
								var evalue = val;
								if(val == 'false') evalue = false;
								else if(val == 'true') evalue = true; 
								this_field.validation[name] = evalue;
							});
						}

						json_form_builder_fields.push(this_field);
					});

					// console.log(json_form_builder_fields);
					$.each(json.fields, function(index, field)
					{
						json_field_names.push(field.name);
						var field_content = addJsonFormField(index);
					});

					form_builder.find('#custom_script').val(data.script);
					scriptCodeMirror.getDoc().setValue(data.script);
				}else {
					json_form_builder_fields = [];
					json_field_names = [];
					scriptCodeMirror.getDoc().setValue('');
				}
				
				campaignJsonCreativeTable.ajax.reload();
				$('#cmpJsonFormBuilderModal').modal('show');
			}
		});
	});

	$(document).on('click','#addFormFieldJson', function() 
	{
		$('#jsonFormFieldModal').modal('show');
	});

	$(document).on('click','#addJsonFieldValueBtn', function() 
	{
		addJsonFieldRow('', '', '', false, false);
		$('#jsonFieldValueTable tbody tr:last [name="field_value[]"]').focus();
	});
	
	$(document).on('change','#jsonFormBuilderFieldTypeSelect', function() 
	{
		var value = $(this).val();
		if(value == 'article'){
			$('.json_input_field_group').hide();
			$('.json_article_field_group').show();
			$('#json_form_builder_field #name').removeAttr('required');
		}else if(value == 'dropdown' || value == 'radio' || value == 'checkbox') {
			$('.json_input_field_group').show();
			$('.json_article_field_group').hide();
			$('#json_form_builder_field #name').attr('required', true);

			$('.json_value_group_two').show();
			$('.json_value_group_one').hide();
		}else {
			$('.json_input_field_group').show();
			$('.json_article_field_group').hide();
			$('#json_form_builder_field #name').attr('required', true);

			$('.json_value_group_two').hide();
			$('.json_value_group_one').show();
		}
	});

	$(document).on('click','.clearJsonFieldValueTable', function() 
	{
		$('#jsonFieldValueTable tbody').html('');
	});

	$(document).on('submit','#json_form_builder_field', function(e) 
	{
		e.preventDefault();

		var form = $(this),
			id = $('#jsonFormFieldModal #field_id').val(),
			this_field = new Object(),
			isInShortCodeDropdown = false;

		// console.log(id);

		this_field.name = form.find('#name').val();
		this_field.type = $('#jsonFormBuilderFieldTypeSelect').val();
		this_field.id = form.find('#id').val();
		this_field.class = form.find('#class').val();
		this_field.label = form.find('#label').val();

		if(this_field.type == 'article') {
			this_field.value = form.find('#article').val();
			prev_val = this_field.value;
			prev_val = prev_val.replace(/<a\b[^>]*>(.*?)<\/a>/i,""); //remove links
			prev_val = prev_val.replace(/<\/?[^>]+(>|$)/g, "");
			prev_val = prev_val.substr(0,150);
			if(this_field.value.length > 150) prev_val += '...';
			field_value = '<small><em>'+prev_val+'</em></small>';
			
			if(id > 0 || id != '') { //edit existing article field row
				json_form_builder_fields[id] = this_field;
				$('#jfbf-name-'+id).html('');
				$('#jfbf-type-'+id).html(this_field.type);
				$('#jfbf-value-'+id).html(field_value);
			}else{ //add
				var new_id = json_form_builder_fields.length;
				json_form_builder_fields[new_id] = this_field;
				json_field_names[json_field_names.length] = this_field.name;
				field_content = addJsonFormField(new_id);
			}
		}else {

			if(this_field.type == 'dropdown' || this_field.type == 'radio' || this_field.type == 'checkbox') {
				var option_values = new Array(),
					option_displays = new Array(),
					option_images = new Array(),
					this_displays = $('#json_form_builder_field [name="field_value[]"]'),
					this_images = $('#json_form_builder_field [name="field_img_value[]"]'),
					hasAccepts = false,
					option_accepts = new Array();
					
				
				$('#json_form_builder_field [name="field_display[]"]').each(function(i, obj) {
					option_values.push($(this_displays[i]).val());
					option_images.push($(this_images[i]).val());
					option_displays.push($(obj).val());
				});
				options = { values: option_values, displays : option_displays, images : option_images};
				this_field.options= options;

				$('#json_form_builder_field [name="accepted[]"]').each(function(i, obj) {
					if($(this).prop('checked')) {
						hasAccepts = true;
						value = $('#jsonFieldValueTable tbody tr:nth('+i+')').find('[name="field_value[]"]').val();
						option_accepts.push(value);
					}
				});

				this_field.has_accepts = hasAccepts;
				this_field.accepted_options = option_accepts;
				

				this_field.value = '';
				if($('#json_form_builder_field [name="default_value"]:checked').length > 0) {
					this_index = $('#json_form_builder_field [name="default_value"]:checked').val();
					this_field.value = $('#jsonFieldValueTable tbody tr:nth('+this_index+')').find('[name="field_value[]"]').val();
				}
				// console.log('Default: '+ this_field.value);
				// this_field['options']['values'] = option_values;
				// this_field['options']['displays'] = option_displays;
			}else {
				if($('#json_form_builder_field [name="value_select"]').val() != '') {
					this_field.value = $('#json_form_builder_field [name="value_select"]').val();
					isInShortCodeDropdown = true;
				}
				else if($('#json_form_builder_field [name="value_input"]').val() != '') this_field.value = $('#json_form_builder_field [name="value_input"]').val();
				else this_field.value = '';
			}

			var validations = {};
			$('#json_form_builder_field [name="validation[]"]').each(function(i, obj) {
				var valid_value = $(this).val();
				if(valid_value == 'equalTo'){
					if($(this).prop('checked')) validations[valid_value] = $('#json_form_builder_field [name="equal_to_value"]').val();
				}else if(valid_value == 'min'){
					if($(this).prop('checked')) validations[valid_value] = $('#json_form_builder_field [name="min_value"]').val();
				}else if(valid_value == 'max'){
					if($(this).prop('checked')) validations[valid_value] = $('#json_form_builder_field [name="max_value"]').val();
				}else if(valid_value == 'range'){
					if($(this).prop('checked')) validations[valid_value] = '[' + $('#json_form_builder_field [name="range_min_value"]').val() + ',' + $('#json_form_builder_field [name="range_max_value"]').val() + ']';
				}else if(valid_value == 'minWordCount'){
					if($(this).prop('checked')) validations[valid_value] = $('#json_form_builder_field [name="min_word_count_value"]').val();
				}else {
					validations[valid_value] = $(this).prop('checked');
				}
			});
			this_field.validation = validations;

			if(id > 0 || id != '') {
				//edit
				json_form_builder_fields[id] = this_field;
				json_field_names[id] = this_field.name;
				value_display = this_field.value;
				if(isInShortCodeDropdown == true) value_display = $('#json_form_builder_field [name="value_select"] option[value="'+this_field.value+'"]').html();
				
				//get value
				if(value_display != '') {
					if(this_field.type == 'dropdown' || this_field.type == 'checkbox' || this_field.type == 'radio') {
						def_val_indx = this_field.options.values.indexOf(this_field.value);
						def_val = value_display;
						if(def_val != this_field.options.displays[def_val_indx]) {
							def_val += ' - ' + this_field.options.displays[def_val_indx];
						}
					}else {
						def_val = value_display;
					}

					def_val_prev = def_val.substr(0,74);
					if(def_val.length > 74) def_val_prev += '...';
					field_value = '<span class="label label-primary">'+def_val_prev+'</span>';
				}else field_value = '';
				
				//get some options if exists
				if(this_field.type == 'dropdown' || this_field.type == 'checkbox' || this_field.type == 'radio') {
					if(typeof this_field.options != undefined) {
						fieldCounter = 0;
						$.each(this_field.options.values, function(i, oVal) {
							if(oVal != '') {
								if(fieldCounter <= 3) {
									if(oVal != this_field.value) {
										if(oVal == this_field.options.displays[i]) opt_val = oVal;
										else opt_val = oVal + ' - ' + this_field.options.displays[i];
										
										opt_val_prev = opt_val.substr(0,74);
										if(opt_val.length > 74) opt_val_prev += '...';
										field_value += ' <span class="label label-info">'+ opt_val_prev +'</span>';
										fieldCounter++;
									}
								}else {
									field_value += ' <span class="label label-info">...</span>';
									return false;
								}
							}
						});
					}
				}

				//get name/label
				var name_display = this_field.name;
				if(this_field.label != '') {
					name_display += '<br><small><em>' + this_field.label.substr(0,74);
					if(this_field.label.length > 74) name_display += '...';
					name_display += '</em></small>';
				}

				// console.log(json_form_builder_fields[id]);
				$('#jfbf-name-'+id).html(name_display);
				$('#jfbf-type-'+id).html(this_field.type);
				$('#jfbf-value-'+id).html(field_value);
			}else {
				//add
				// json_form_builder_fields.push(this_field);
				// json_field_names.push(this_field.name);
				// console.log('Add');
				var new_id = json_form_builder_fields.length;
				json_form_builder_fields[new_id] = this_field;
				json_field_names[json_field_names.length] = this_field.name;
				// value_display = this_field.value;
				// if(isInShortCodeDropdown == true) value_display = $('[name="value_select"] option[value="'+this_field.value+'"]').html();
				// var new_id = $('#formFieldsTable tbody tr').length,
					field_content = addJsonFormField(new_id);

	    		// console.log(this_field);
	    		// console.log(json_form_builder_fields);
			}
		}

		$('#jsonFormFieldModal').modal('hide');
		// console.log(this_field);
	});

	//Close Edit Field Modal
	$('#jsonFormFieldModal').on('hide.bs.modal', function () {
		$('#jsonFormFieldModal .modal-title').html('Add Field');
		$('#jsonFormFieldModal .this_field').val('');
		$('#jsonFormBuilderFieldTypeSelect').trigger('change');
		$('[name="validation[]"]').prop('checked', false);
		$('[name="validation[]"][value="required"]').prop('checked', true);
		$('#jsonFieldValueTable tbody').html('');

		//remove required of validation fields
		$('[name="equal_to_value"]').removeAttr('required');
		$('[name="range_min_value"]').removeAttr('required');
		$('[name="range_max_value"]').removeAttr('required');
		$('[name="min_value"]').removeAttr('required');
		$('[name="max_value"]').removeAttr('required');
		$('[name="min_word_count_value"]').removeAttr('required');
		$('#uploadValueCollapse').collapse('hide');

		$('.selectAcceptedValues').prop('checked',false);
	});

	$(document).on('click','.viewJsonFieldModalBtn', function() 
	{
		var id = $(this).data('id'),
			field = json_form_builder_fields[id],
			modal = $('#jsonFormFieldModal');
			
		console.log(field);

		modal.find('.modal-title').html('Edit Field');
		modal.find('#label').val(field.label);
		modal.find('#name').val(field.name);
		modal.find('#id').val(field.id);
		modal.find('#class').val(field.class);
		modal.find('#field_id').val(id);
		modal.find('#jsonFormBuilderFieldTypeSelect').val(field.type).trigger('change');
		
		if(field.type == 'hidden' || field.type == 'text' || field.type == 'textbox') {
			if($('select[name="value_select"] option[value="'+field.value+'"]').length > 0) {
				$('select[name="value_select"]').val(field.value);
				$('input[name="value_input"]').val('');
			}else {
				$('select[name="value_select"]').val('');
				$('input[name="value_input"]').val(field.value);
			}
		}else if(field.type == 'article'){
			modal.find('#article').val(field.value);
		}else {
			$.each(field.options.values, function(index, values)
			{
				var isDefault = false,
					isAccepted = false,
					display = field.options.displays;
				
				if(field.value == values) isDefault = true;

				if(field.has_accepts) {
					console.log('Has Accepts');
					$key = field.accepted_options.indexOf(values);
					if($key >= 0) isAccepted = true;
					console.log($key)
				}

				var image = '';
				if(field.options.images !== undefined) {
					images = field.options.images;
					image = images[index];
				}

				if(values != '') {
					addJsonFieldRow(values, display[index], image, isDefault, isAccepted);
				}
			});
		}
			

		//required validtion
		if(field.standard) required = true;
		else {
			required = false;
			if(field.hasOwnProperty("validation")) {
				if(field.validation.hasOwnProperty("required")) {
					required = field.validation.required;
				}
			}
			// if('validation.required' in field) {
			// 	console.log(field.validation.required);
			// 	required = field.validation.required;
			// }else required = false;
		}
		modal.find('[name="validation[]"][value="required"]').prop( "checked" , required);

		//validation

		if(field.hasOwnProperty('validation')) {
			if(typeof field.validation.alphaNumeric != "undefined") 
				$('[name="validation[]"][value="alphaNumeric"]').prop( "checked" , field.validation.alphaNumeric);

			if(typeof field.validation.alphaSpace != "undefined") 
				$('[name="validation[]"][value="alphaSpace"]').prop( "checked" , field.validation.alphaSpace);

			if(typeof field.validation.date != "undefined") 
				$('[name="validation[]"][value="date"]').prop( "checked" , field.validation.date);

			if(typeof field.validation.digits != "undefined") 
				$('[name="validation[]"][value="digits"]').prop( "checked" , field.validation.digits);

			if(typeof field.validation.email != "undefined")
				$('[name="validation[]"][value="email"]').prop( "checked" , field.validation.email);

			if(typeof field.validation.equalTo != "undefined") {
				$('[name="validation[]"][value="equalTo"]').prop( "checked" , true).trigger('change');
				$('input[name="equal_to_value"]').val(field.validation.equalTo);
			}
				
			if(typeof field.validation.max != "undefined") {
				$('[name="validation[]"][value="max"]').prop( "checked" , true).trigger('change');
				$('input[name="max_value"]').val(field.validation.max);
			}

			if(typeof field.validation.min != "undefined") {
				$('[name="validation[]"][value="min"]').prop( "checked" , true).trigger('change');
				$('input[name="min_value"]').val(field.validation.min);
			}

			if(typeof field.validation.number != "undefined") 
				$('[name="validation[]"][value="number"]').prop( "checked" , field.validation.number);

			if(typeof field.validation.phoneUS != "undefined") 
				$('[name="validation[]"][value="phoneUS"]').prop( "checked" , field.validation.phoneUS);

			if(typeof field.validation.range != "undefined") {
				$('[name="validation[]"][value="range"]').prop( "checked" , true).trigger('change');
				range = $.parseJSON(field.validation.range);
				$('input[name="range_min_value"]').val(range[0]);
				$('input[name="range_max_value"]').val(range[1]);
			}

			if(typeof field.validation.url != "undefined") 
				$('[name="validation[]"][value="url"]').prop( "checked" , field.validation.url);
			
			if(typeof field.validation.zip != "undefined")
				$('[name="validation[]"][value="zip"]').prop( "checked" , field.validation.zip);

			if(typeof field.validation.zip != "undefined")
				$('[name="validation[]"][value="zip"]').prop( "checked" , field.validation.zip);

			if(typeof field.validation.minWordCount != "undefined") {
				$('[name="validation[]"][value="minWordCount"]').prop( "checked" , true).trigger('change');
				$('input[name="min_word_count_value"]').val(field.validation.minWordCount);
			}
		}

		modal.modal('show');
	});

	$(document).on('change','#jsonFormBuilderSendLeadSelect', function() 
	{
		var value = $(this).val(),
			custom_url = $('#jsonFormBuilderCustomUrlInput'),
			form = $('#campaign_json_form_builder');

		if(value == 'custom') {
			custom_url.show();
		}else {
			custom_url.hide();

			if(value != '') {
				$.each(standard_fields, function(index, value) {
					
					if(json_field_names.indexOf(index) < 0) {
						var new_id = $('#jsonFormFieldsTable tbody tr').length,
							the_value = value;
						if(index == 'eiq_campaign_id') the_value = form.find('#this_campaign').val();

						field = { class: '', id: '', label: '', name: index, standard: true, type: 'hidden',  
							value : the_value};
						json_form_builder_fields[json_form_builder_fields.length] = field;
						json_field_names[json_field_names.length] = index;
						// json_form_builder_fields.push(field);
						// json_field_names.push(index);
						field_content = addJsonFormField(new_id);
					}
				});

				if(value == 'lead_filter') {
					$.each(standard_lf_fields, function(index, value) {
					
						if(json_field_names.indexOf(index) < 0) {
							var new_id = $('#jsonFormFieldsTable tbody tr').length,
								the_value = value;
							if(index == 'program_id') the_value = form.find('#this_campaign').val();
							else if(index == 'program_name') the_value = $('#cmp-'+form.find('#this_campaign').val()+'-name').html();
							
							field = { class: '', id: '', label: '', name: index, standard: false, type: 'hidden',  
								value : the_value};
							json_form_builder_fields[json_form_builder_fields.length] = field;
							json_field_names[json_field_names.length] = index;
							// json_form_builder_fields.push(field);
							// json_field_names.push(index);
							field_content = addJsonFormField(new_id);
						}
					});
				}
			}
		}
		// console.log(field_names);
		// console.log(form_builder_fields);
	});

	$(document).on('click','.removeJsonFieldBtn', function() 
	{
		// console.log('Submit');
		confirmation = confirm('Are you sure you want to delete this field?');

		if(confirmation) {
			var the_index = $(this).data('id');
			delete json_form_builder_fields[the_index];
			delete json_field_names[the_index];
			// form_builder_fields.splice(the_index, 1);
			// field_names.splice(the_index, 1);
			$(this).parents('tr').remove();
			// console.log('Removing');
			// console.log(the_index);
			// console.log(form_builder_fields);
			// console.log(field_names);
		}
	});

	$(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#json_customs_tab"]', function() {
	    this.refresh();
	}.bind(scriptCodeMirror));

	$(document).on('submit','#campaign_json_form_builder', function(e) 
	{
		e.preventDefault();

		var form =  $('#campaign_json_form_builder'),
			final_form_builder_fields = [],
			submitBtn = $('#jsonConfigAutomationSubmit');

		//check for unsaved campaign creative
		if($('.editCampaignCreative .glyphicon-remove').length > 0) {
			alert('Campaign Form Builder NOT SAVED YET. Save unfinished campaign creatives first.');
			$('[href="#creatives_tab"]').trigger('click');
			return;
		}

		// console.log('Submit');
		confirmation = confirm('Are you sure you want to update this campaign content?');

		if(confirmation) {
			$('#jsonFormFieldsTable tbody tr').each(function(index, object) {
				final_form_builder_fields.push(json_form_builder_fields[$(this).data('id')]);
			});

			submitBtn.attr('disabled','true');
            submitBtn.html('<i class="fa fa-spin fa-spinner"></i>');
            // console.log(final_form_builder_fields);
			$.ajax({
				type: 'POST',
				data: {
					'id'		: form.find('.this_campaign').val(),
					'type'		: $('#jsonFormBuilderCampaignType').val(),
					'form[url]'  : $('#jsonFormBuilderSendLeadSelect').val(),
					'form[custom_url]'  : form.find('[name="custom_url"]').val(),
					'form[id]'  : form.find('[name="form_id"]').val(),
					'form[class]'  : form.find('[name="form_class"]').val(),
					'fields'	: final_form_builder_fields,
					'script'  : form.find('[name="custom_script"]').val(),
					'form[linkout_url]'  : form.find('[name="redirect_link"]').val(),
				},
				url: $('#baseUrl').html() + '/campaign_json_form_builder',
				success: function(data) {
					console.log(data);
					submitBtn.html('Save');
                    submitBtn.removeAttr('disabled');
					alert('Campaign Form Builder Modification Saved!');
				}
			});
		}
	});

	//Close Form Builder Modal
	$('#cmpJsonFormBuilderModal').on('hide.bs.modal', function (e) {
		if($('.editCampaignCreative .glyphicon-remove').length > 0) {
			alert('Save unfinished campaign creatives before closing the form builder.');
			$('#cmpFormBuilderModal [href="#creatives_tab"]').trigger('click');
			e.preventDefault();
		    e.stopImmediatePropagation();
		    return false; 
		}
		
		$('#cmpJsonFormBuilderModal #cmpName').html('');
		$('.form_builder_btn').removeAttr('disabled');
		$('#jsonFormBuilderSendLeadSelect').val('').trigger('change');
		$('#campaign_json_form_builder').find('.this_field').val('');
		$('#jsonFormFieldsTable tbody').html('');
		// $('#autoLockAlert').remove();
		$('#cmpJsonFormBuilderModal [href="#json_form_tab"]').trigger('click');
		scriptCodeMirror.getDoc().setValue('');
		setTimeout(function() {
			scriptCodeMirror.refresh();
		},100);
	});

	var canEditJsonCampaignCreative = true, canDeleteJsonCampaignCreative = true;

	var campaignJsonCreativeTable = $('#json-campaign-stack-creative-table').DataTable({
        responsive: true,
        autoWidth: false,
        'processing': true,
    	'serverSide': true,
        "order": [[ 0, "desc" ]],
        'columns': [
			null,
			{ 'orderable': false },
			{ 'orderable': false },
			{ 'orderable': false }
		],
        columnDefs: [
        	{ width: '10%', targets: 0 },	                
            { width: '55%', targets: 1 },
            { width: '30%', targets: 2 },
            { width: '5%', targets: 3 },
        ],
        "bFilter" : false,
        "bLengthChange": false,
        "bInfo": false,
        "bPaginate": false,
        'ajax':{
			url: $('#baseUrl').html() + '/get_campaign_json_creative',
			type: 'post', 
			'data': function(d)
            {
            	// console.log('Campaign ID: ' + $('.this_campaign').val());
                // d.campaign_id = $('.this_campaign').val();
                d.campaign_id = $('#campaign_json_form_builder').find('.this_campaign').val();
            },
			error: function(data) //error handling
			{
				console.log(data);
			},
			"dataSrc": function ( json ) {
        		// console.log(json);
        		canEditJsonCampaignCreative = json.canEdit;
				canDeleteJsonCampaignCreative = json.canDelete;

				if(! json.canAdd) {
					$('#addCampJsonCreative').hide();
				}else {
					$('#addCampJsonCreative').show();
				}
        		return json.data;
        	}
		},
		"sDom": 'lf<"json-campaign-stack-creative-table-add-toolbar">rtip',
        "fnDrawCallback": function( oSettings ) {
            // console.log(oSettings);
            campaignCreativeID =  $('.this_campaign').val();
        	$('.stackCreativeDesc').ckeditor({
		    	toolbarGroups : [
					{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
					{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
					{ name: 'forms', groups: [ 'forms' ] },
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
					{ name: 'links', groups: [ 'links' ] },
					{ name: 'insert', groups: [ 'insert' ] },
					{ name: 'styles', groups: [ 'styles' ] },
					{ name: 'colors', groups: [ 'colors' ] },
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
					{ name: 'tools', groups: [ 'tools' ] },
					{ name: 'others', groups: [ 'others' ] },
					{ name: 'about', groups: [ 'about' ] }
				],
		    	removeButtons : 'Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Redo,Undo,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,RemoveFormat,Language,BidiRtl,BidiLtr,Link,Unlink,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,ShowBlocks,About'
		    });
        	
		    $( ".spinner" ).spinner({
		      step: 0.01,
		      numberFormat: "n",
		      min: 0,
		      max: 1,
		      change: function( event, ui ) {
		      	$('#'+event.currentTarget.id).parent('span.ui-spinner').removeClass('error_element');
		      	if(! $(this).spinner('isValid')) {
		      		$('#'+event.currentTarget.id).parent('span.ui-spinner').addClass('error_element');
		      	}
		      	// var value = parseInt(event.currentTarget.value);
		      }
		    });
		    $( ".spinner" ).spinner("disable");

		    if(canEditJsonCampaignCreative == false) {
		    	$('.editCampaignCreative').hide();
		    }

		    if(canDeleteJsonCampaignCreative == false) {
		    	$('.deleteCampaignCreative').hide();
		    }
        }
    });
    $("div.json-campaign-stack-creative-table-add-toolbar").html('<button id="addCampJsonCreative" class="btn btn-default pull-right" type="button"><span class="glyphicon glyphicon-plus"></span></button>');

    $(document).on('click','#addCampJsonCreative', function() 
	{
		var id = $('#campaign_json_form_builder').find('.this_campaign').val();
		$.ajax({
			type: 'POST',
			url: $('#baseUrl').val() + '/add_campaign_creative',
			data: {
				id : id
			},
			success: function(data)
			{
				campaignJsonCreativeTable.order([]);
				campaignJsonCreativeTable.ajax.reload();
			}
		});
	});

	$(document).on('change','[name="field_img_value[]"]', function() 
	{
		var this_input = $(this);
		var has_error = false;

		var container = $(this).closest('.row'),
			image = container.find('img');

		this_input.removeClass('error_field error');

		$(image)
		    .on('load', function() { console.log("image loaded correctly"); })
		    .on('error', function() { 
		    	if(this_input.val() != '') {
		    		console.log(this_input.val());
		    		this_input.addClass('error_field error').val('');
		    		console.log("error loading image"); 
		    		has_error = true;
		    	}
		    })
		    .attr("src", $(this).val());
		;

		// var gallery_alert = '<div id="cmpCrtv-'+id+'-imgGalleryAlert" class="col-md-12 alert alert-info" role="alert" style="margin-top: 10px;"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>It is recommended that the image comes from our very own <a href="'+$('#baseUrl').html()+'/admin/gallery" class="alert-link" target="_blank">gallery</a></div>';
		// if ($(this).val().indexOf($('#baseUrl').html()+'/images/gallery/') < 0 && $('#cmpCrtv-'+id+'-imgGalleryAlert').length == 0) {
		// 	console.log('not from gallery');
		// 	$('#cmpCrtv-'+id+'-imgLinkDiv').after(gallery_alert);
		// }
		// else console.log('from gallery');
    });

    $(document).on('click','#jsonAddRangeValuesBtn', function() 
	{
		var table = $('#jsonFieldValueTable tbody'),
			min = $('#json_min_range_value').val(),
			max = $('#json_max_range_value').val();
			

		if(min > max) {
			for(var i = min; i >= max; i--) {
				addJsonFieldRow(i,i, '', false, false);
			}
		}else {
			for(var i = min; i <= max; i++) {
				addJsonFieldRow(i,i, '', false, false);
			}
		}

		$('#jsonRangeValueCollapse').collapse('hide');
	});

	$(document).on('click','#jsonUploadFileValuesBtn', function() 
	{
		var file = $('#json_upload_file_value').val(),
			label = $('[for="json_upload_file_value"]');

		label.removeClass('error_label');
		if(file != '') {
			var file_data = $('#json_upload_file_value').prop('files')[0];   
	    	var formData = new FormData();
	    	formData.append('file', file_data);
	    	// console.log(file_data);
	    	$.ajax({
	            url: $('#baseUrl').html() + '/campaign_upload_field_options',
	            dataType: 'text', 
	            cache: false,
	            contentType: false,
	            processData: false,
	            data: formData,                         
	            type: 'post',
	            success: function(data){
	                options = $.parseJSON(data);
	                displays = options.displays;
	                $.each(options.values, function(i, value) {
	                	addJsonFieldRow(value, displays[i], '', false, false);
	                });
	                // console.log(options);
	                $('#jsonUploadValueCollapse').collapse('hide');
	            }
	     	});
		}else {
			label.addClass('error_label');
		}
    });

    $(document).on('click','.prvJsonCmpCnt', function(e) {
		e.preventDefault();
		var id = $('#this_campaign').val();
		var url = $(this).attr('href') + id + '&type=' + $('#jsonFormBuilderCampaignType').val();
		var win = window.open(url, '_blank');
 		win.focus();
	});

	function convertLinkOutLink(the_url) {
		$('#loa-modal #linkout_url').val('');
		$('.loa-rows').remove();

		var isValidUrl = true;

		try {
		    var url = new URL(the_url);

		    $('#loa-modal #linkout_url').val(url.origin);
		    var searchParams = new URLSearchParams(url.search);
		    searchParams.forEach(function(value, key) {
			  addLinkOutParamRow(key, value)
			});
	  	} catch (_) {
	    	isValidUrl = false;
	  	}
	  	return isValidUrl;
    }

	function addLinkOutParamRow(key, val) {
		var table = $('#loa-table tbody');

    	var row = '<tr class="loa-rows"><td><input class="form-control this_field" required="true" name="loa_key[]" type="text" value="'+key+'"></td>' +
					'<td><input class="form-control this_field" required="true" name="loa_value[]" type="text" value="'+val+'"></td>' + 
					'<td class="text-center"><button id="loa-deleteMe" class="btn btn-default" type="button">' +
						'<span class="glyphicon glyphicon-trash"></span></button></td></tr>';

        table.append(row);
    }

	$(document).on('click','#loa-btn', function() 
	{
		var redirect_link = $('.formBuilderLinkoutElement #redirect_link').val(),
			isValidUrl = true;

		convertLinkOutLink(redirect_link);
		$('#loa-modal #parse_linkout_url').val(redirect_link);
		$('#loa-modal').modal('show');
	});

	$(document).on('click','#loa-split', function() 
	{
		convertLinkOutLink($('#loa-modal #parse_linkout_url').val());
	});

	$(document).on('click','#loa-addRow', function() 
	{
		addLinkOutParamRow('', '')
	});

	$(document).on('click','#loa-deleteMe', function() 
	{
		$(this).closest('tr').remove();
	});

	$(document).on('click','#loa-submit', function() 
	{
		var url = $('#loa-modal #linkout_url').val().trim() + '?';
		$('[name="loa_key[]"]').each(function(i, j) {
			// console.log(i)
			console.log($(this).val() + ' = ' + $('[name="loa_value[]"]').eq(i).val());
			key = $(this).val()
			value = $('[name="loa_value[]"]').eq(i).val()
			url += key + '=' + value + '&';
		});
		url = url.substring(0, url.length - 1);
		console.log(url);
		$('.formBuilderLinkoutElement #redirect_link').val(url)
		$('#loa-modal').modal('hide')
	});

	

    $('#loa-modal').on('hide.bs.modal', function (e) {
		$('.loa-rows').remove();
		$('#loa-modal #linkout_url').val('');
		$('#loa-modal #parse_linkout_url').val('')
	});
});
