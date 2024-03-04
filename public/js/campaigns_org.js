$(document).ready(function() 
{
	/*
	var table = $('.table-datatable').DataTable({
		responsive: true,
		"order": [[ 0, "desc" ]],
		lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
	});
	*/

	var dataCampaignsURL = $('#baseUrl').html() + '/campaigns';
	var campaign_datatable = $('#campaign-table').DataTable({
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
			{ "orderable": false }
		],
		'ajax':{
			url:dataCampaignsURL, // json datasource
			// type: 'get',  //LIVE
			type: 'post',  // KARLA ver
			error: function(){  // error handling

			}
		},
		lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
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

    $('.leadCapType').on('change', function() {
    	var select = $(this);
    	var leadCapVal_div = '';
    	//console.log(select.data('form'));

    	if(select.data('form') == 'add') {
			leadCapVal_div = $('#leadCapVal_add');
		}else {
			leadCapVal_div = $('#leadCapVal_edit');
		}

    	if(select.val() == 0){
    		leadCapVal_div.find('input').removeAttr('required');
    		leadCapVal_div.hide();
    	}else {
    		leadCapVal_div.find('input').attr('required','true');
    		leadCapVal_div.show();
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
		
		$('.this_modal_submit').removeAttr('disabled');

		this_modal.modal('show');
	});

	// $('#editCampaignInfo').click(function() {
	// 	$('#info_tab form .this_field').removeAttr('disabled');
	// });

	/***
	 * onclick listener for editing campaign
	 */
	$(document).on('click','.editCampaign',function() 
	{
		$(this).closest('tr').addClass('selected');

		$('.editCampaign').find('span').removeClass().addClass('glyphicon glyphicon-pencil');
		
		$('.editCampaign').attr('disabled','true');

		var this_button = $(this);
		this_button.find('span').removeClass().addClass('fa fa-spin fa-spinner');

		/* Info */
		var this_modal = $('#editCmpFormModal');
		var id = $(this).data('id');

		//$('#info_tab form .this_field').attr('disabled',true);
		this_modal.find('.this_campaign').val(id);

		var this_cmp = '#cmp-' + id + '-';

		$('#modal-campaign-title').html(id + ' - '+ $(this_cmp+'name').html());
		
		if(typeof $(this_cmp+'img').data('img') == 'undefined' || $(this_cmp+'img').data('img') == '' ) {
			this_modal.find('.imgPreview').show();
			this_modal.find('.imgPreview img').attr('src', $(this_cmp+'img img').attr('src'));
		}

		//checks if lead cap type is universal
		var leadCapVal_div = $('#leadCapVal_edit');
		if($(this_cmp+'lct').data('type') == 0) {
			leadCapVal_div.find('input').removeAttr('required');
			leadCapVal_div.hide();
		}else {
			leadCapVal_div.find('input').attr('required','true');
    		leadCapVal_div.show();
		}

		this_modal.find('#name').val( $(this_cmp+'name').html() );
		this_modal.find('#priority').val( $(this_cmp+'prio').html() );
		this_modal.find('#campaign_type').val( $(this_cmp+'type').val() );
		this_modal.find('#advertiser').val( $(this_cmp+'adv').data('adv') );
		this_modal.find('#lead_type').val( $(this_cmp+'lct').data('type') );
		this_modal.find('#lead_value').val( $(this_cmp+'lcv').html() );
		this_modal.find('#default_payout').val( $(this_cmp+'dpyt').val() );
		this_modal.find('#default_received').val( $(this_cmp+'drcv').val() );
		this_modal.find('#description').val( $(this_cmp+'desc').html() );
		this_modal.find('#notes').val( $(this_cmp+'notes').val() );
		this_modal.find('input[name="status"][value="'+$(this_cmp+'stat').data('status')+'"]').prop('checked', true);
		
		
		/* UNIVERSAL */

		$('.this_campaign').val(id);
		var filter_table = $('#campaign-filter-table').DataTable();
		filter_table.rows().remove().draw();	

		var affiliate_table = $('#campaign-affiliate-table').DataTable();
		affiliate_table.rows().remove().draw();	

		var payout_table = $('#campaign-payout-table').DataTable();
		payout_table.rows().remove().draw();	

		var the_url = $('#baseUrl').html() + '/get_campaign_info';

		$.ajax({
			// type: 'POST', // LIVE Ver
			type: 'GET', //KARLA Ver
			data: {
				'id'	:	id
			},
			url: the_url,
			// error: function(xhr, status, error) {
  			// 	console.log(error);
			// },
			success: function(data) {
				/* FILTER START*/
				filter_data = data.filters;
				filter_table.rows().remove().draw();	
				if(filter_data != null) {
					$.each(filter_data, function(index,filter){
						//console.log(filter);
						var filter_id = filter['id'];
						var filter_value_type = filter['value_type'];
						var filter_value01,filter_value02;

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

						var filterCol = '<span id="cf-'+filter_id+'-filter">'+filter['filter_name']+'</span>';
						var valueCol = '<span id="cf-'+filter_id+'-value">'+filter['value_value']+'</span>';
						var actionCol =  '<button id="cf-'+filter_id+'-edit-button" class="btn btn-default editCampaignFilter" type="button" data-id="'+filter_id+'" data-ftype="'+filter['filter_type_id']+'" data-vtype="'+filter_value_type+'" data-value01="'+filter_value01+'" data-value02="'+filter_value02+'"><span class="glyphicon glyphicon-pencil"></span></button>';
							actionCol += '<button id="cf-'+filter_id+'-delete-button" class="btn btn-default deleteCampaignFilter" type="button" data-id="'+filter_id+'"><span class="glyphicon glyphicon-trash"></span></button>';
						
						var dataRow = [
							// filter_id,
							filterCol,
							valueCol,
							actionCol
						];
						//add the row data
						var rowNode = filter_table.row.add(dataRow).draw().node();
						// var affRow = $(rowNode);
						// affRow.find('td:nth-child(1)').css('width','10%');
					});	
				}
				/* FILTER END */

				/* AFFILIATE START */
				affiliate_data = data.affiliates;
				$('select[name="affiliates[]"] option').remove();
				var aff_select = $('select[name="affiliates[]"]');
				$.each(affiliate_data['available'], function(index,value){
					aff_select.append('<option value="'+index+'">'+value+'</option>');
				});		
				var affiliate_count = $('select[name="affiliates[]"] option').length;
				if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);

				// var affiliate_table = $('#campaign-affiliate-table').DataTable();
				affiliate_table.rows().remove().draw();		


				if(affiliate_data['affiliated'] != undefined) 
				{
					$.each(affiliate_data['affiliated'], function(index,affiliate){

						var id = affiliate['id'];
						// var idCol = '<input name="select_affiliate[]" class="selectCampaignAffiliate" value="'+id+'" data-name="'+affiliate['affiliate_name']+'" type="checkbox"> ' + id;
						var leadTypeCol = '<span id="ca-'+id+'-type" data-id="'+affiliate['cap_type']+'">None</span>';
							leadTypeCol += '<span id="ca-'+id+'-type-select" class="hidden">'+lead_cap_select+'<span>';
						var leadValCol = '<span id="ca-'+id+'-value">'+affiliate['lead_count']+'</span>';
							leadValCol += '<span id="ca-'+id+'-value-input" class="hidden">';
							leadValCol += '<input type="text" class="form-control full_width_form_field" value="'+affiliate['lead_count']+'"/>';
							leadValCol += '</span>';
						var actionCol =  '<button id="ca-'+id+'-edit-button" class="btn btn-default editCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
							actionCol += '<button id="ca-'+id+'-update-button" class="btn btn-primary updateCampaignAffiliate hidden" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-floppy-disk"></span></button>';
							actionCol += '<button id="ca-'+id+'-cancel-button" class="btn btn-default cancelCampaignAffiliate hidden" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-circle-arrow-left"></span></button>';
							actionCol += '<button id="ca-'+id+'-delete-button" class="btn btn-default deleteCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
						var dataRow = [
							id,
							affiliate['affiliate_name'],
							leadTypeCol,
							leadValCol,
							actionCol
						];

						//add the row data
						var rowNode = affiliate_table.row.add(dataRow).draw().node();
						var affRow = $(rowNode);
						
						//$('#ca-'+id+'-type-select').html(lead_cap_select);
						$('#ca-'+id+'-type-select select').val(affiliate['cap_type']);
						// if(affiliate['cap_type'] == 0) lead_cap_name = 'None';
						// else lead_cap_name = $('#ca-'+id+'-type-select select :selected').html();
						lead_cap_name = $('#ca-'+id+'-type-select select :selected').html();
						$('#ca-'+id+'-type').html(lead_cap_name);

					});	
					refreshTable($('#campaign-affiliate-table'));	
				}	
				/* AFFILIATE END */

				/* PAYOUT START */
				payout_data = data.payouts;
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
						var idCol = '<input name="select_payout[]" class="selectCampaignPayout" value="'+id+'" data-name="'+aff_name+'" type="checkbox"> ' + id;
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
				config_data = data.config;
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

				if(content_data.high_paying == null) high_paying_content = '';
				else high_paying_content = content_data.high_paying;
				$('#cmpCnt-hp-actual').val(high_paying_content);
				$('#cmpCnt-hp-content').val(high_paying_content);

				/* CONTENT END*/

				console.log(data);

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
		/* END*/
	});

	$('#addCmpFormModal').on('hide.bs.modal', function (event) 
	{
		var form = $(this).find('.form_with_file');

		$('.this_modal_submit').html('Save');

		$('.imgPreview').hide();
		$('.imgPreview img').attr('src','');

		form.find('.this_field').each(function() 
		{
			if($(this).attr('name') == 'status') {
				$('input[name="status"][value="0"]').prop('checked', true);
			}else {
				$(this).val('');
			}
		});

		form.find('.this_modal_submit').html('Save');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

		$('.campaign_img').removeClass('error error_field');

	});

	$('#editCmpFormModal').on('hide.bs.modal', function (event) 
	{
		var form = $(this).find('.form_with_file');

		$('.this_modal_submit').html('Save');

		$('.imgPreview').hide();
		$('.imgPreview img').attr('src','');

		form.find('.this_field').each(function() 
		{
			if($(this).attr('name') == 'status') {
				$('input[name="status"][value="0"]').prop('checked', true);
			}else if($(this).attr('name') == 'img_type') {
				$('input[name="img_type"][value="1"]').prop('checked', true);
			}else {
				$(this).val('');
			}
		});

	  	form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

		form.find('.this_modal_submit').html('Save');

		/* Affiliates */
		$('#addPvtAff').addClass('disabled');
		$('#addPvtAff').html('<span class="glyphicon glyphicon-plus"></span>');

		$('.campaign_img').removeClass('error error_field');
		//$('a[href="#info_tab"]').tab('show');

		if($('#editCampaignLongContent').data('config') == 'hide') $('#editCampaignLongContent').click();
		if($('#editCampaignStackContent').data('config') == 'hide') $('#editCampaignStackContent').click();
		if($('#addCmpFilter').data('collapse') == 'hide') $('#addCmpFilter').click();
		// if(! $('#addCampAff').hasClass('collapsed')) $('#addCampAff').click();
		// if(! $('#addPytAff').hasClass('collapsed')) $('#addPytAff').click();
		if($('#addCampAff').attr('aria-expanded') == true || !$('#addCampAff').hasClass('collapsed')) $('#addCampAff').click();
		if($('#addPytAff').attr('aria-expanded') == true || ! $('#addPytAff').hasClass('collapsed')) $('#addPytAff').click();
		if($('#editCmpConfig').data('config') == 'hide') $('#editCmpConfig').click();
	});

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
					//console.log(data);
					var table = $('#campaign-table').DataTable();
					table.row(this_campaign.parents('tr')).remove().draw();

					//PRIORITY CHANGES
					if(data) {
						$.each(data, function(priority, campaign) {
							$('#cmp-' + campaign + '-prio').html(priority);
						});
					}

				}
			});
		}
	});

	/* AFFILIATES */
	$('select[name="affiliates[]"]').change(function() {
		var selectedAffiliates = $('select[name="affiliates[]"] option:selected');
		if(selectedAffiliates.length > 0) $('#addPvtAff').removeClass('disabled');
		else $('#addPvtAff').addClass('disabled');
		//else $('select[name="affiliates[]"] option').attr('selected',false)
		//console.log(selectedAffiliates.length);
	});
	

	$('#campaignAffiliateForm').on('hidden.bs.collapse', function () {
		var this_button = $('#addCampAff');
		this_button.html('<span class="glyphicon glyphicon-plus"></span>');
		$('#addPvtAff').addClass('disabled');
		$('select[name="affiliates[]"] option').prop('selected',false);
		$('#addPvtAff').addClass('disabled');
	})

	$('#campaignAffiliateForm').on('shown.bs.collapse', function () {
		var this_button = $('#addCampAff');
		this_button.html('<span class="glyphicon glyphicon-remove"></span>');	
	})

	$('.closeAffiliateCollapse').on('click', function () {
		$('#campaignAffiliateForm').collapse('hide');
	})

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
	
	$(document).on('click','.editCampaignAffiliate',function(e) 
    {
		e.preventDefault();
		var id = $(this).data('id');
		$(this).addClass('hidden');
		$('#ca-'+id+'-update-button').removeClass('hidden');
		$('#ca-'+id+'-type').addClass('hidden');
		$('#ca-'+id+'-type-select').removeClass('hidden');
		$('#ca-'+id+'-type').addClass('hidden');
		$('#ca-'+id+'-type-select').removeClass('hidden');
		$('#ca-'+id+'-value').addClass('hidden');
		$('#ca-'+id+'-value-input').removeClass('hidden');
		$('#ca-'+id+'-cancel-button').removeClass('hidden');
		$('#ca-'+id+'-delete-button').addClass('hidden');

		//$('#ca-'+id+'-update-button').removeClass('hidden');
	});

	$(document).on('click','.updateCampaignAffiliate', function(e) 
	{
		e.preventDefault();

		var id = $(this).data('id');

		var cur_type = $('#ca-'+id+'-type').data('id');
		var new_type = $('#ca-'+id+'-type-select select').val();
		var cur_value = $('#ca-'+id+'-value').html();
		var new_value = $('#ca-'+id+'-value-input input').val();

		var error_counter = 0;
		if( cur_type != new_type || cur_value != new_value)
		{
			// console.log(new_value + ' - ' + new_type);
			var the_url = $('#baseUrl').html() + '/update_affiliate_for_campaign';

			if( Math.floor(new_value) != new_value && ! $.isNumeric(new_value) ) {
				$('#ca-'+id+'-value-input input').addClass('error_field ');
				error_counter++;
			}else $('#ca-'+id+'-value-input input').removeClass('error_field ');
			
			if(new_type == '') {
				$('#ca-'+id+'-type-select select').addClass('error_field ');
				error_counter++;
			}else $('#ca-'+id+'-type-select select').removeClass('error_field ');

			if(new_type != 0 && new_type != '') {
				if(new_value <= 0){
					$('#ca-'+id+'-value-input input').addClass('error_field ');
					error_counter++;
				}else $('#ca-'+id+'-value-input input').removeClass('error_field ');
			}

			if(new_type == 0 || new_type == '') new_value = 0;

			if(error_counter == 0) {
				$.ajax({
					type : 'POST',
					url  : the_url,
					data : {
						'id'	: 	id,
						'type'	: 	new_type,
						'value'	: 	new_value
	 				}, 
					success : function(data) {
						$('#ca-'+id+'-type')
							.attr('data-id',new_type)
							.data('id',new_type)
							.html($('#ca-'+id+'-type-select select :selected').html());
						$('#ca-'+id+'-value').html(new_value);
						$('#ca-'+id+'-value-input input').val(new_value);
						refreshTable($('#campaign-affiliate-table'));
					}
				});
			}
		}else {
			$('#ca-'+id+'-value-input input').removeClass('error_field');
			$('#ca-'+id+'-type-select select').removeClass('error_field');
		}
		
		if(error_counter == 0) {
			$(this).addClass('hidden');
			$('#ca-'+id+'-edit-button').removeClass('hidden');
			$('#ca-'+id+'-type').removeClass('hidden');
			$('#ca-'+id+'-type-select').addClass('hidden');
			$('#ca-'+id+'-type').removeClass('hidden');
			$('#ca-'+id+'-type-select').addClass('hidden');
			$('#ca-'+id+'-value').removeClass('hidden');
			$('#ca-'+id+'-value-input').addClass('hidden');
			$('#ca-'+id+'-cancel-button').addClass('hidden');
			$('#ca-'+id+'-delete-button').removeClass('hidden');
		}
	})
	
	$(document).on('click','.deleteCampaignAffiliate', function(e) 
	{
		e.preventDefault();
		var this_affiliate = $(this);
		var id = $(this).data('id');

		var confirmation = confirm('Are you sure you want to delete this Affiliate?');

		if(confirmation === true) {
			// console.log(id);

			var the_url = $('#baseUrl').html() + '/delete_affiliate_for_campaign';
			$.ajax({
				type : 'POST',
				url  : the_url,
				data : {
					'id'	: 	id,
					'campaign' : $('#editCmpFormModal form').find('#this_id').val()
 				}, 
				success : function(data) {
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
		}
	})

	$(document).on('click','.cancelCampaignAffiliate', function(e) 
	{
		var id = $(this).data('id');
		$(this).addClass('hidden');
		$('#ca-'+id+'-update-button').addClass('hidden');
		$('#ca-'+id+'-edit-button').removeClass('hidden');
		$('#ca-'+id+'-type').removeClass('hidden');
		$('#ca-'+id+'-type-select').addClass('hidden');
		$('#ca-'+id+'-type').removeClass('hidden');
		$('#ca-'+id+'-type-select').addClass('hidden');
		$('#ca-'+id+'-value').removeClass('hidden');
		$('#ca-'+id+'-value-input').addClass('hidden');
		$('#ca-'+id+'-cancel-button').addClass('hidden');
		$('#ca-'+id+'-delete-button').removeClass('hidden');
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
		this_modal.find('#this_id').val('');

		var this_button = $(this);
		// console.log(this_button.data('collapse'));
		if(this_button.data('collapse') == 'show') {
			this_button.html('<span class="glyphicon glyphicon-remove"></span>');
			this_button.attr('data-collapse','hide');
			this_button.data('collapse','hide');
			$('#campaignFilterForm').collapse('show');
		}else {
			this_button.html('<span class="glyphicon glyphicon-plus"></span>');
			this_button.attr('data-collapse','show');
			this_button.data('collapse','show');
			$('#campaignFilterForm').collapse('hide');
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
		var file01 = $('#val-1-file-wrapper');
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
		file01.find('input').val('');
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
		file01.find('input').removeAttr('required');
		input02.find('input').removeAttr('required');
		date02.find('input').removeAttr('required');
		array01.find('textarea').removeAttr('required');
		time02.find('input').removeAttr('required')

		if(value == 7) {
			//File
			select01.addClass('hidden');
			date01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			time01.addClass('hidden');
			text01.addClass('hidden');
			file01.removeClass('hidden');
			file01.find('input').attr('required',true);

			wrapper02.addClass('hidden');

			label01.html('File');
		}
		else if(value == 6) {
			//Time
			text01.addClass('hidden');
			select01.addClass('hidden');
			input01.addClass('hidden');
			array01.addClass('hidden');
			date01.addClass('hidden');
			file01.addClass('hidden');
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
			file01.addClass('hidden');
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
			file01.addClass('hidden');
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
			file01.addClass('hidden');
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
			file01.addClass('hidden');
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
			file01.addClass('hidden');
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

	/**
	* Delete data of campaign filter for editing
	*/
	$(document).on('click','.deleteCampaignFilter', function(e) 
	{
		e.preventDefault();
		var this_filter = $(this);
		var id = $(this).data('id');

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
					var table = $('#campaign-filter-table').DataTable();
					table.row(this_filter.parents('tr')).remove().draw();
				}
			});
		}
	});

	/* PAYOUT */

	/***
	*	Select All/None Affiliates for Payout
	*/
	$('#capPytSelectAllAff').on('change', function (event) 
	{
		if($(this).prop('checked') == true) {
			$('select[name="payout[]"] option').prop('selected', true);
			// $('#addPytAff').removeClass('disabled');
		}else {
			$('select[name="payout[]"] option').prop('selected', false);
			// $('#addPytAff').addClass('disabled');

			//var this_button = $('#addPytAff');
			// this_button.html('<span class="glyphicon glyphicon-plus"></span>');
			// this_button.attr('data-collapse','show').data('collapse','show');
			// $('#campaignPayoutForm').collapse('hide');
		}
	});

	/***
	*	Enable/Disable Add Button
	*/
	$('select[name="payout[]"]').on('change', function (event) {
		var checkIfSelectAll = $('select[name="payout[]"] :not(:selected)').length == 0;// && $('select[name="payout[]"] options').length > 0;
		var selectCount = $('select[name="payout[]"] :selected').length;
		// if(selectCount > 0) {
		// 	$('#addPytAff').removeClass('disabled');
		// }else $('#addPytAff').addClass('disabled');

		if(checkIfSelectAll == false) $('#capPytSelectAllAff').prop('checked', false);
		else $('#capPytSelectAllAff').prop('checked', true);
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
		if(checkIfSelectAll == false) $('#selectAllPayoutAffiliate').prop('checked', false);
		else $('#selectAllPayoutAffiliate').prop('checked', true);

		//console.log($('.selectCampaignPayout:checked').length);
		$('#editCampaignPayoutForm').collapse('hide');
		if($('.selectCampaignPayout:checked').length > 0) {
			$('#editPytAff').removeClass('disabled');
			$('#deletePytAff').removeClass('disabled');
		}else {
			$('#editPytAff').addClass('disabled');
			$('#deletePytAff').addClass('disabled');
		}
	});

	$('#selectAllPayoutAffiliate').click(function() {

		$('#editCampaignPayoutForm').collapse('hide');
		if($(this).prop('checked') == true) 
		{
			$('.selectCampaignPayout').prop('checked', true);
			$('#editPytAff').removeClass('disabled');
			$('#deletePytAff').removeClass('disabled');
		}
		else
		{
			$('.selectCampaignPayout').prop('checked', false);
			$('#editPytAff').addClass('disabled');
			$('#deletePytAff').addClass('disabled');
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
		
		checkedAffiliates.each(function() {
			edit_info += $(this).data('name') + ', ';
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

	});

	$('#editCampaignPayoutForm').on('hide.bs.collapse', function () {
		var this_button = $('#editPytAff');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		$('label[for="payout_title"]').html('Add Affiliate Payout');
		$('.editCampaignPayout').removeClass('disabled');
		$('.listOfCampaignAffiliates').addClass('hidden');
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

	$(document).on('click','.deleteCampaignPayout', function(e) 
	{	
		e.preventDefault();
		var this_filter = $(this);
		var id = $(this).data('id');

		$('input[name="select_payout[]"]').prop('checked', false);
		$('input[name="select_payout[]"][value="'+id+'"]').prop('checked',true);

		console.log( $('input[name="select_payout[]"]').serialize() );

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
				}
			});
		}else {
			$('input[name="select_payout[]"][value="'+id+'"]').parents('tr').css('background-color','');
		}
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
			
		}else {
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			$('.cmpStkCnt-form-wrapper').addClass('hidden');
			$('#cmpCnt-stack-content').attr('disabled',true);
			$('#cmpCnt-stack-content').val($('#cmpCnt-stack-actual').val());
		}
	});

	$('.cancelCampaignStackContentEdit').click(function() {
		var this_button = $('#editCampaignStackContent');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpStkCnt-form-wrapper').addClass('hidden');
		$('#cmpCnt-stack-content').attr('disabled',true);
		$('#cmpCnt-stack-content').val($('#cmpCnt-stack-actual').val());
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
	        'method' : 'POST',
	    }).append($('<input>', {
	        'name': 'content',
	        'value': $('#cmpCnt-content').val(),
	        'type': 'hidden'
	    })).append($('<input>',{
	    	'name': '_token',
	    	'value': $('meta[name="_token"]').attr('content'),
	    	'type': 'hidden',
	    })).append($('<input>',{
	    	'name': 'type',
	    	'value': type,
	    	'type': 'hidden',
	    }));
	  
	    newForm.submit();
	});

	$(document).on('click','.prvCmpCnt', function(e) {
		e.preventDefault();
		var id = $('#this_campaign').val()
		var url = $(this).attr('href') + id;
		var win = window.open(url, '_blank');
 		win.focus();
	});

	//SORT CAMPAIGNS
	var sorted_campaigns = [];
	var sorted = $( "#campaign_sortable" ).sortable({
		update: function (event, ui) {
			sorted_campaigns = $(this).sortable('serialize');
	        $('#saveSortedCampaignsBtn').show();
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

	$(document).on('click','#saveSortedCampaignsBtn', function(e) 
	{	
		e.preventDefault();

		var this_button = $(this);
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
				$('#current_sorting').html();
				$.each(campaign_priority, function(i, new_sort) {
					$('#campaign-priority-sort').append('<tr id="cmpprt-'+new_sort.id+'"><td>'+new_sort.priority+'</td><td>'+new_sort.name+'</td><td>'+new_sort.campaign_type+'</td><td>'+new_sort.status+'</td></tr>');
			    	$('#current_sorting').append('<input type="hidden" name="old_prio[]" value="'+new_sort.id+'">');
			    });
				this_button.html('Update');
				this_button.hide();
				$('#campaign_sortable tr').removeClass('selected');
				campaign_datatable.ajax.reload(); 
			}
		});
	});
	
});