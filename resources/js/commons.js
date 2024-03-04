var bugFiles = [];
var bugFileSizeChecker = [];


/***
 * Generic function after ajax call
 *
 * @param process
 * @param data
 */
function processIdentifier(process, data)
{
	switch(process)
	{
		case 'add_affiliate':
			addAffiliateProcess(data);
			break;

		case 'edit_affiliate':
			editAffiliateProcess(data);
			break;

        case 'add_contact':
			addContactProcess(data);
			break;

        case 'edit_contact':
			editContactProcess(data);
			break;

        case 'add_advertiser':
			addAdvertiserProcess(data);
			break;

        case 'edit_advertiser':
			editAdvertiserProcess(data);
			break;

        case 'add_filter_type':
			addFilterTypeProcess(data);
			break;
	
	    case 'edit_filter_type':
			editFilterTypeProcess(data);
			break;

		case 'add_campaign':
			addCampaignProcess(data);
			break;

		case 'edit_campaign':
			editCampaignProcess(data);
			break;

		case 'update_lead_details':
			updateLeadDetails(data);
			break;

		case 'add_campaign_payout':
			addCampaignPayoutProcess(data);
			break;

		case 'edit_campaign_payout':
			editCampaignPayoutProcess(data);
			break;

		case 'edit_campaign_config':
			editCampaignConfigProcess(data);
			break;

		case 'edit_campaign_long_content':
			editCampaignLongContentProcess(data);
			break;

		case 'edit_campaign_stack_content':
			editCampaignStackContentProcess(data);
			break;

		case 'edit_campaign_high_paying_content':
			editCampaignHighPayingContentProcess(data);
			break;

		case 'edit_campaign_posting_instruction':
			editCampaignPostingInstructionProcess(data);
			break;
			
		case 'add_revenue_tracker':
			addRevenueTrackerProcess(data);
			break;

		case 'edit_revenue_tracker':
			editRevenueTrackerProcess(data);
			break;

		case 'update_revenue_tracker_campaign_order':
			updateRevenueTrackerCampaignOrderProcess(data);
			break;

        case 'update_revenue_tracker_mixed_coreg_campaign_order':
            updateRevenueTrackerMixedCoregCampaignOrderProcess(data);
            break;

		case 'add_gallery_image':
			addGalleryImageProcess(data);
			break;

		case 'add_user':
			addUserProcess(data);
			break;

		case 'edit_user':
			editUserProcess(data);
			break;

		case 'add_campaign_affiliate':
			addCampaignAffiliateProcess(data);
			break;

		case 'edit_campaign_affiliate':
			editCampaignAffiliateProcess(data);
			break;

		case 'add_campaign_filter_group':
			addCampaignFilterGroupProcess(data);
			break;

		case 'edit_campaign_filter_group':
			editCampaignFilterGroupProcess(data);
			break;

		case 'add_campaign_filter':
			addCampaignFilterProcess(data);
			break;

		case 'edit_campaign_filter':
			editCampaignFilterProcess(data);
			break;

		case 'update_user_profile':
			updateUserProfileProcess(data);
			break;

		case 'change_user_password':
			changePasswordProcess(data);
			break;

		case 'add_category':
			addCategoryProcess(data);
			break;

		case 'edit_category':
			editCategoryProcess(data);
			break;

		case 'edit_account_contact_info':
			editAccountContactInfo(data);
			break;

		case 'change_password_contact_info':
		    changePasswordContactInfo(data);
		    break;

		case 'report_bug':
			reportBugProcess(data);
			break;

		case 'update_settings':
			updateSettingsProcess(data);
			break;

        case 'upload_affiliate_reports':
            uploadAffiliateReportsProcess(data);
            break;

        case 'add_path':
			addPathProcess(data);
			break;

		case 'edit_path':
			editPathProcess(data);
			break;

		case 'add_affiliate_website':
			addAffiliateWebsiteProcess(data);
			break;

		case 'affiliate_website_payout':
			affiliateWebsitePayoutProcess(data);
			break;

        case 'upload_campaign_payout':
            uploadCampaignPayoutProcess(data);
            break;

		case 'campaign_config_interface':
			campaignConfigInterface(data);
			break;

		case 'campaign_affiliate_management':
			campaignAffiliateManagement(data);
			break;

		case 'rev_tracker_to_exit_page_list':
			revTrackerToExitPageList(data);
			break;

		case 'add_notes_category':
			addNotesCategoryProcess(data);
			break;

		case 'edit_notes_category':
			editNotesCategoryProcess(data);
			break;

		case 'add_note':
			addNoteProcess(data);
			break;

		case 'edit_note':
			editNoteProcess(data);
			break;

		case 'add_banned_lead':
			addBannedLeadProcess(data);
			break;

		case 'edit_banned_lead':
			editBannedLeadProcess(data);
			break;

		case 'update_lead_rejection_rate_settings':
			alert('Settings Updated');
			break;

        default:
			alert('unknown process!');
			break;
	}
}

function uploadCampaignPayoutProcess(data)
{
    $('.this_error_wrapper').show();
    console.log(data);
    // console.log(data.status);
    // console.log(data.message);

    var alertDangerWrapper = $('.alert-danger-wrapper');
    var alertWarningWrapper = $('.alert-warning-wrapper');
    var alertSuccessWrapper = $('.alert-success-wrapper');
    var alertDangerContent = $('.alert-danger-content');
    var alertWarningContent =  $('.alert-warning-content');
    var alertSuccessContent = $('.alert-success-content');

    alertDangerWrapper.hide();
    alertWarningWrapper.hide();
    alertSuccessWrapper.hide();

    switch(data.status)
    {
        case 'file_problem':

            alertDangerWrapper.show();
            alertDangerContent.html('');
            alertDangerContent.append(data.message);

            break;

        case 'upload_warning':

            alertWarningWrapper.show();
            alertWarningContent.html('');
            alertWarningContent.append(data.message);

            break;

        case 'upload_fail':

            alertDangerWrapper.show();
            alertDangerContent.html('');
            alertDangerContent.append(data.message);
            break;

        default:

            alertSuccessWrapper.show();
            alertSuccessContent.html('');
            alertSuccessContent.append(data.message);

            var table = $('#campaign-table').DataTable();
            table.ajax.reload();

            break;
    }
}

function uploadAffiliateReportsProcess(data)
{
    $('.this_error_wrapper').show();
    console.log(data.status);
    console.log(data.message);

    var alertDangerWrapper = $('.alert-danger-wrapper');
    var alertWarningWrapper = $('.alert-warning-wrapper');
    var alertSuccessWrapper = $('.alert-success-wrapper');
    var alertDangerContent = $('.alert-danger-content');
    var alertWarningContent = $('.alert-warning-content');
    var alertSuccessContent = $('.alert-success-content');

    alertDangerWrapper.hide();
    alertWarningWrapper.hide();
    alertSuccessWrapper.hide();

    switch(data.status)
    {
        case 'file_problem':

            alertDangerWrapper.show();
            alertDangerContent.html('');
            alertDangerContent.append(data.message);

            break;

        case 'upload_warning':

            alertWarningWrapper.show();
            alertWarningContent.html('');
            alertWarningContent.append(data.message);

            break;

        case 'upload_fail':

            alertDangerWrapper.show();
            alertDangerContent.html('');
            alertDangerContent.append(data.message);
            break;

        default:

            alertSuccessWrapper.show();
            alertSuccessContent.html('');
            alertSuccessContent.append(data.message);

            break;
    }
}

/**
 * Change Password process
 *
 * @param data
 */
function changePasswordProcess(data)
{
	console.log(data);
	$('#changePasswordModal').modal('hide');

    alert(data.message);

    //redirect user
    window.location.href = $('#baseUrl').val()+'/auth/logout';
    //window.location.reload(true);
    //history.go(-1);
}

/**
 * Update User Profile process
 *
 * @param data
 */
function updateUserProfileProcess(data)
{
	console.log(data);

	//set the modal fields
	var newTitle = $('#title').val();
	var newFirstName = $('#first_name').val();
	var newMiddleName = $('#middle_name').val();
	var newLastName = $('#last_name').val();
	var newGender = $('#gender').val();
	var newAddress = $('#address').val();
	var newPosition = $('#position').val();
	var newEmail = $('#email').val();
	var newMobileNumber = $('#mobile_number').val();
	var newPhoneNumber = $('#phone_number').val();
	var newIM = $('#instant_messaging').val();

	$('#current_full_name').html(newTitle+' '+newFirstName+' '+newMiddleName+' '+newLastName);
	$('#current_gender').html(newGender);
	$('#current_address').html(newAddress);
	$('#current_position').html(newPosition);
	$('#current_email').html(newEmail);
	$('#current_mobile_number').html(newMobileNumber);
	$('#current_phone_number').html(newPhoneNumber);
	$('#current_im').html(newIM);

	$('#editProfileModal').modal('hide');
}

/**
 * Add User process
 *
 * @param data
 */
function addUserProcess(data)
{
	console.log(data);
	var id = data.id;
	var this_user = 'user-'+id+'-';

	var full_name = $('#title').val()+' '+$('#first_name').val()+' '+$('#middle_name').val()+' '+$('#last_name').val();

	var actionsColumn ='<input id="'+this_user+'id" name="user_id" type="hidden" value="'+id+'">';

	actionsColumn += '<input id="'+this_user+'gender" name="gender" type="hidden" value="'+$('#gender').val()+'">';
	actionsColumn += '<input id="'+this_user+'address" name="address" type="hidden" value="'+$('#address').val()+'">';
	actionsColumn += '<input id="'+this_user+'instant_messaging" name="instant_messaging" type="hidden" value="'+$('#instant_messaging').val()+'">';
	actionsColumn += '<button class="editUser btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
	actionsColumn += '<button class="deleteRole btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-remove"></span></button>';

	var dataRow = [id,full_name,$('#email').val(),$('#mobile_number').val(),$('#phone_number').val(),$('#role_name').text(),actionsColumn];

	var table = $('#users-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();
	var userRow = $(rowNode);

	//assign id to the row
	userRow.attr('id','row-user-'+id);

	//assign id to each td
	userRow.find('td:nth-child(0)').attr('id',this_user+'id');
	userRow.find('td:nth-child(1)').attr('id',this_user+'full_name');
	userRow.find('td:nth-child(2)').attr('id',this_user+'email');
	userRow.find('td:nth-child(3)').attr('id',this_user+'mobile_number');
	userRow.find('td:nth-child(4)').attr('id',this_user+'phone_number');
	userRow.find('td:nth-child(5)').attr('id',this_user+'role_name');

	//clear all fields and hide the error wrapper
	$('.this_field').val('');
	$('#userModal').modal('hide');
}

/**
 * Edit User process
 *
 * @param data
 */
function editUserProcess(data)
{
	var id = data.id;
	var this_user = '#user-'+id+'-';

	var full_name = $('#title').val()+' '+$('#first_name').val()+' '+$('#middle_name').val()+' '+$('#last_name').val();
	$(this_user+'full_name').html(full_name);
	$(this_user+'gender').val($('#gender').val());
	$(this_user+'position').val($('#position').val());
	$(this_user+'address').val($('#address').val());

	var roleID = $('#role_id').val();
	var roleName = $('#role_id option:selected').text();

	console.log(roleID);
	console.log(roleName);

	$(this_user+'role_id').val(roleID);
	$(this_user+'role_name').html(roleName);

	$(this_user+'instant_messaging').val($('#instant_messaging').val());
	$(this_user+'email').html($('#email').val());
	$(this_user+'mobile_number').html($('#mobile_number').val());
	$(this_user+'phone_number').html($('#phone_number').val());

	$('.this_field').val('');
	$('#userModal').modal('hide');
}

/***
 * get all cities by state
 *
 * @param state
 * @param callback
 */
function getCitiesByState(state,callback) 
{
	var the_url = $('#baseUrl').val() + '/get_cities_for_state';
	$.ajax({
		type: 'POST',
		data: {
			'name'	:	state
		},
		url: the_url,
		success: function(data){
			callback(data);
		}
	});
}

/***
 * previewing of selected and uploaded image
 *
 * @param input
 * @param preview
 */
function imgPreview(input,preview) 
{
    var reader = new FileReader();
    reader.onload = function (e) {
        preview.attr("src", e.target.result);
    }
    reader.readAsDataURL(input[0].files[0]);
}

/**
 * Refresh DataTable to include updated data
 *
 * @param table
 */
function refreshTable(table)
{
	var oTable =  table.dataTable({
						"order": [[ 0, "desc" ]],
						"bFilter": false,
                       	"bSort": true,
                       	"bInfo": false,
                       	"bPaginate": false,
                       	"bDestroy": true,
                       	// "bStateSave": true,
                       	responsive: true
                    });
    oTable.fnDraw();
}

/**
 * Add affiliate process. This will append new affiliates and dismisses the modal form.
 *
 * @param data
 */
function addAffiliateProcess(data)
{
	var id = data.id;

	var stat = $('input[name="status"]:checked');
	var typeColumn = '<span id="aff-'+id+'-type" data-type="'+$('#type').val()+'">'+$('#type option:selected').html()+'</span>';
	var webColumn = '<a href="'+$('#website').val()+'" target="_blank"><span id="aff-'+id+'-web">'+ $('#website').val() +'</span></td>';
	var contColumn = '<span class="glyphicon glyphicon-envelope"></span> ';
		contColumn += '<span id="aff-'+id+'-add">'+ $('#address').val() +'</span><br>';
		contColumn += '<span id="aff-'+id+'-cty">'+ $('#city :selected').val() +'</span>, <span id="aff-'+id+'-ste">'+ $('#state :selected').val() +'</span>';
		contColumn += ' <span id="aff-'+id+'-zip">'+ $('#zip').val() +'</span><br>';
		contColumn += '<span class="glyphicon glyphicon-phone-alt"></span>';
		contColumn += ' <span id="aff-'+id+'-phn">'+ $('#phone').val() +'</span><br>';
	var actionsColumn = '<input type="hidden" id="aff-'+id+'-desc" value="'+ $('#description').val() +'" />';

		//determine if the user can edit or delete contact
		if(data.canEdit)
		{
			actionsColumn += '<button class="editAffiliate btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
		}

		if(data.canDelete)
		{
			actionsColumn += '<button class="deleteAffiliate btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
		}

	var dataRow = [
		id,
		$('#company').val(),
		'',
		typeColumn,
		webColumn,
		contColumn,
		stat.data('label'),
		actionsColumn
	];

	var table = $('#affiliate-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();
	var affRow = $(rowNode);

	//assign id and data to each td
	// var this_aff = 'aff-'+id+'-';
	// affRow.find('td:nth-child(2)').attr('id',this_aff+'comp');
	// affRow.find('td:nth-child(3)').attr('id',this_aff+'user');
	// affRow.find('td:nth-child(6)').attr('id',this_aff+'stat');
	// affRow.find('td:nth-child(6)').attr('data-status', stat.val());

	$('#AffFormModal').modal('hide');
}

/**
 * edit affiliate process. This will append new affiliates and dismisses the modal form.
 *
 * @param data
 */
function editAffiliateProcess(data)
{
	var id = data.id;
	$('#aff-'+id+'-comp').html($('#company').val());
	$('#aff-'+id+'-user').html(data.user);
	$('#aff-'+id+'-type').html($('#type :selected').html()).attr('data-type',$('#type').val()).data('type',$('#type').val());
	$('#aff-'+id+'-web').html($('#website').val());
	$('#aff-'+id+'-web').parent('a').attr('href',$('#website').val());
	$('#aff-'+id+'-phn').html($('#phone').val());
	$('#aff-'+id+'-add').html($('#address').val());
	$('#aff-'+id+'-cty').html($('#city :selected').val());
	$('#aff-'+id+'-ste').html($('#state :selected').val());
	$('#aff-'+id+'-zip').html($('#zip').val());
	$('#aff-'+id+'-desc').val($('#description').val());
	var stat = $('input[name="status"]:checked');
	$('#aff-'+id+'-stat').html(stat.data('label'));
	$('#aff-'+id+'-stat').data('status',stat.val());
	$('#aff-'+id+'-stat').attr('data-status',stat.val());

	//refreshTable($('#affiliate-table'));

	$('#AffFormModal').modal('hide');
}

/**
 * Add Advertiser process. This will append new advertiser and dismisses the modal form.
 *
 * @param data
 */
function addAdvertiserProcess(data)
{
	var id = data.id;
    var websiteURL = $('#website_url').val();

	var stat = $('input[name="status"]:checked');
	var webColumn = '<a href="'+websiteURL+'" target="_blank"><span id="adv-'+id+'-web">'+websiteURL+'</span></td>';

	var contColumn = '<span class="glyphicon glyphicon-envelope"></span> ';
		contColumn += '<span id="adv-'+id+'-add">'+ $('#address').val() +'</span><br>';
		contColumn += '<span id="adv-'+id+'-cty">'+ $('#city :selected').val() +'</span>, <span id="adv-'+id+'-ste">'+ $('#state :selected').val() +'</span>';
		contColumn += ' <span id="adv-'+id+'-zip">'+ $('#zip').val() +'</span><br>';
		contColumn += '<span class="glyphicon glyphicon-phone-alt"></span>';
		contColumn += ' <span id="adv-'+id+'-phn">'+ $('#phone').val() +'</span><br>';
	var actionsColumn = '<input type="hidden" id="adv-'+id+'-desc" value="'+ $('#description').val() +'" />';

	//determine if the user can edit or delete contact
	if(data.canEdit)
	{
		actionsColumn += '<button class="editAdvertiser btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
	}

	if(data.canDelete)
	{
		actionsColumn += '<button class="deleteAdvertiser btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
	}

	var dataRow = [
		id,
		$('#company').val(),
		'',
		webColumn,
		contColumn,
		stat.data('label'),
		actionsColumn
	];

	var table = $('#advertiser-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();
	var advRow = $(rowNode);

	//assign id and data to each td
	var this_adv = 'adv-'+id+'-';
	advRow.find('td:nth-child(2)').attr('id',this_adv+'comp');
	advRow.find('td:nth-child(3)').attr('id',this_adv+'user');
	advRow.find('td:nth-child(6)').attr('id',this_adv+'stat');
	advRow.find('td:nth-child(6)').attr('data-status', stat.val());

	$('#AdvFormModal').modal('hide');
}

/**
 * This routine updates the datatable with the edited values from the advFormModal the value of the id is based on the returned value from the advertiser model after the updates was made to the database.
 *
 * @param data
 */
function editAdvertiserProcess(data)
{
	var id = data.id;
    var websiteURL = $('#website_url').val();
    var advIDWebContainer = $('#adv-'+id+'-web');
    var advIDStatContainer = $('#adv-'+id+'-stat');

	$('#adv-'+id+'-comp').html($('#company').val());
    advIDWebContainer.html(websiteURL);
    advIDWebContainer.parent('a').attr('html',websiteURL);
    advIDWebContainer.parent('a').attr('href',websiteURL);
	$('#adv-'+id+'-phn').html($('#phone').val());
	$('#adv-'+id+'-add').html($('#address').val());
	$('#adv-'+id+'-cty').html($('#city :selected').val());
	$('#adv-'+id+'-ste').html($('#state :selected').val());
	$('#adv-'+id+'-zip').html($('#zip').val());
	$('#adv-'+id+'-desc').html($('#description').val());
	var stat = $('input[name="status"]:checked');
    advIDStatContainer.html(stat.data('label'));
    advIDStatContainer.data('status',stat.val());
    advIDStatContainer.attr('data-status',stat.val());

	$('#AdvFormModal').modal('hide');

	console.log('Advertiser Record has been updated');
}


/***
 * add contact process. This will append new contacts and dismisses the modal form.
 *
 * @param data
 */
function addContactProcess(data)
{
	console.log(data);
	var id = data.id;
	var this_contact = 'contact-'+id+'-';

	var full_name = $('#title').val()+' '+$('#first_name').val()+' '+$('#middle_name').val()+' '+$('#last_name').val();

	var actionsColumn ='<input id="'+this_contact+'affiliate_id" name="affiliate_id" type="hidden" value="'+$('#affiliate_id').val()+'">';
	actionsColumn += '<input id="'+this_contact+'advertiser_id" name="advertiser_id" type="hidden" value="'+$('#advertiser_id').val()+'">';
	actionsColumn +='<input id="'+this_contact+'gender" name="gender" type="hidden" value="'+$('#gender').val()+'">';
	actionsColumn += '<input id="'+this_contact+'address" name="address" type="hidden" value="'+$('#address').val()+'">';
	actionsColumn += '<input id="'+this_contact+'instant_messaging" name="instant_messaging" type="hidden" value="'+$('#instant_messaging').val()+'">';

	//determine if the user can edit or delete contact
	if(data.canEdit)
	{
		actionsColumn += '<button class="edit-contact btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
	}

	if(data.canDelete)
	{
		actionsColumn += '<button class="delete-contact btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
	}

    //get the name of affiliate or advertiser and place it as company
    var companyName = '';
    var affiliateName = $('#affiliate_id option:selected').text();
    var advertiserName = $('#advertiser_id option:selected').text();

    if(affiliateName==null || affiliateName=='')
    {
        companyName = affiliateName;
    }
    else if(advertiserName==null || advertiserName=='')
    {
        companyName = advertiserName;
    }
    else
    {
        companyName = affiliateName+', '+advertiserName;
    }

	var dataRow = [full_name,$('#position').val(),companyName,$('#email').val(),$('#mobile_number').val(),$('#phone_number').val(),actionsColumn];
	var table = $('#contacts-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();
	var contactRow = $(rowNode);
	//assign id to the row
	contactRow.attr('id','row-contact-'+id);
	//assign id to each td
	contactRow.find('td:nth-child(1)').attr('id',this_contact+'full_name');
	contactRow.find('td:nth-child(2)').attr('id',this_contact+'position');
    contactRow.find('td:nth-child(3)').attr('id',this_contact+'company');
	contactRow.find('td:nth-child(4)').attr('id',this_contact+'email');
	contactRow.find('td:nth-child(5)').attr('id',this_contact+'mobile_number');
	contactRow.find('td:nth-child(6)').attr('id',this_contact+'phone_number');

	//clear all fields and hide the error wrapper
	$('.this_field').val('');
	$('#contacts_form_modal').modal('hide');

	alert('Contact was added successfully!');
}

/**
 * edit contact process
 *
 * @param data
 */
function editContactProcess(data)
{
	var id = data.id;
	var this_contact = '#contact-'+id+'-';

	var affiliateDropdown = $('#affiliate_id');
	var advertiserDropdown = $('#advertiser_id');
	var affiliateDropdownDisabled = affiliateDropdown.prop('disabled');
	var advertiserDropdownDisabled = advertiserDropdown.prop('disabled');

    //get the selected affiliate or advertiser IDs
    var affiliateText = $("#affiliate_id option:selected").text();
    var advertiserText = $("#advertiser_id option:selected").text();

	if(affiliateDropdownDisabled)
	{
		$(this_contact+'affiliate_id').val('');
        affiliateText = '';
	}
	else
	{
		$(this_contact+'affiliate_id').val(affiliateDropdown.val());
	}

	if(advertiserDropdownDisabled)
	{
		$(this_contact+'advertiser_id').val('');
        advertiserText = '';
	}
	else
	{
		$(this_contact+'advertiser_id').val(advertiserDropdown.val());
	}

	$(this_contact+'gender').val($('#gender').val());
	$(this_contact+'address').val($('#address').val());
	$(this_contact+'instant_messaging').val($('#instant_messaging').val());
	var full_name = $('#title').val()+' '+$('#first_name').val()+' '+$('#middle_name').val()+' '+$('#last_name').val();
	$(this_contact+'full_name').html(full_name);
	$(this_contact+'position').html($('#position').val());

    if(affiliateText!='' && advertiserText!='')
    {
        $(this_contact+'company').html(affiliateText+', '+advertiserText);
    }
    else if(affiliateText!='' && advertiserText=='')
    {
        $(this_contact+'company').html(affiliateText);
    }
    else
    {
        $(this_contact+'company').html(advertiserText);
    }


	$(this_contact+'email').html($('#email').val());
	$(this_contact+'mobile_number').html($('#mobile_number').val());
	$(this_contact+'phone_number').html($('#phone_number').val());

	$('.this_field').val('');

	$('.password-fields-container').show();
	$('.password-fields').prop('required',true);

	$('#contacts_form_modal').modal('hide');
}

/**
 * add filter type process
 *
 * @param data
 */
function addFilterTypeProcess(data)
{
	var id = data.id;
	var this_filtertype = 'filtertype-'+id+'-';

	var stat = $('input[name="status"]:checked');
	var actionColumn = '';

	//determine if the user can edit or delete contact
	if(data.canEdit)
	{
		actionColumn = '<button class="edit-filtertype btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
	}

	if(data.canDelete)
	{
		actionColumn += '<button class="delete-filtertype btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
	}

	var dataRow = [$('#type').val(),$('#name').val(),stat.data('label'),actionColumn];
	var table = $('#filtertypes-table').DataTable();

	console.log(dataRow);

	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();
	$('#filtertype_form_modal').modal('hide');
}

/**
 * Edit FilterType Process
 *
 * @param data
 */
function editFilterTypeProcess(data)
{
	var id = data.id;
	var this_filtertype = '#filtertype-'+id+'-';

	var stat = $('input[name="status"]:checked');

	$(this_filtertype+'type').html($('#type').val());
	$(this_filtertype+'name').html($('#name').val());
	$(this_filtertype+'status').html(stat.data('label')).data('status',stat.val());
	$(this_filtertype+'image').html(data.image);

	$('#name.this_field').val('');
	$('#filtertype_form_modal').modal('hide');
}

/** add affiliate process. This will append new affiliates and dismisses the modal form.
 *
 * @param data
 */
function addCampaignProcess(data)
{	
	// var data = $.parseJSON(data);
	// console.log(data);
	var id = data.id;
	var dataIMG = '';

	if(data.img == '' || data.img == null)
	{
		dataIMG = 'none';
		var the_image = $('#baseUrl').html() + '/images/img_unavailable.jpg';
	}
	else
	{
		var the_image = data.img;
	}

	// if($('#notes').val().length > 75) notes = $('#notes').val().substr(0, 75) + '...';
	// else notes = $('#notes').val();

	var prioColumn = '<span id="cmp-'+id+'-prio">'+data.priority+'</span>';
	var imgColumn = '<span class="imgPreTbl" id="cmp-'+id+'-img" data-img="'+dataIMG+'"><img src="'+ the_image +'"><span>';
	var nameColumn = '<span id="cmp-'+id+'-name">'+$('#name').val()+'</span>';
	var advColumn = '<span id="cmp-'+id+'-adv" data-adv="'+$('#advertiser').val()+'">'+$('#advertiser :selected').html()+'</span>';
	var lctColumn = '<span id="cmp-'+id+'-lct" data-type="'+$('#lead_type').val()+'">'+$('#lead_type :selected').html()+'</span>';
	var lcvColumn = '<span id="cmp-'+id+'-lcv">'+$('#lead_value').val()+'</span>';
	var dRcvColumn = '<span id="cmp-'+id+'-drcv">'+$('#default_received').val()+'</span>';
	var typeColumn = '<span id="cmp-'+id+'-type" data-type=">'+$('#campaign_type').val()+'">'+$('#campaign_type :selected').html()+'</span>';
	var statColumn = '<span id="cmp-'+id+'-stat" data-status="'+$('input[name="status"]:checked').val()+'">'+$('input[name="status"]:checked').data('label')+'</span>';
	var rateColumn = '<span id="cmp-'+id+'-rate">'+$('#rate').val()+'</span>';

	var actionsColumn = '<textarea id="cmp-'+id+'-notes" class="hidden" disabled="">'+$('#notes').val()+'</textarea>';
		actionsColumn += '<textarea id="cmp-'+id+'-desc" class="hidden" disabled>'+$('#description').val()+'</textarea>';
		actionsColumn += '<input type="hidden" id="cmp-'+id+'-dpyt" value="'+$('#default_payout').val()+'"/>';
		// actionsColumn += '<input type="hidden" id="cmp-'+id+'-drcv" value="'+$('#default_received').val()+'"/>';
		actionsColumn += '<input type="hidden" id="cmp-'+id+'-ctgry" value="'+$('#category').val()+'"/>';
		actionsColumn += '<input type="hidden" id="cmp-'+id+'-lnkOutOffer" value="'+$('#linkout_offer_id').val()+'"/>';
		actionsColumn += '<input type="hidden" id="cmp-'+id+'-prgId" value="'+$('#program_id').val()+'"/>';
		actionsColumn += '<input type="hidden" id="cmp-'+id+'-advEmail" value="'+$('#advertiser_email').val()+'"/>';

	//determine if the user can edit or delete campaigns
	if(data.canEdit)
	{
		actionsColumn += '<button class="editCampaign btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
	}

	if(data.canDelete)
	{
		actionsColumn += '<button class="deleteCampaign btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button></td>';
	}

	var dataRow = [
		prioColumn,
		id,
		imgColumn,
		nameColumn,
		advColumn,
		typeColumn,
		lctColumn,
		lcvColumn,
		dRcvColumn,
		rateColumn,
		statColumn,
		actionsColumn
	];

	var table = $('#campaign-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();
	var affRow = $(rowNode);
	affRow.click();

	//add priority to form
	$('#info_tab form').find('#priority').append('<option value="'+data.priority+'">'+data.priority+'</option>');

	$('#addCmpFormModal').modal('hide');
}

/**
 * edit affiliate process. This will append new affiliates and dismisses the modal form.
 *
 * @param id
 */
function editCampaignProcess(data)
{
	console.log(data);
	var id = data.id;
	var form = $('#editCmpFormModal div.modal-dialog div.modal-content div.modal-body div.tab-content div#info_tab');
	if(data.status) {
		var prioCol = '<span id="cmp-'+id+'-prio">'+form.find('#priority').val()+'</span>';

		if(data.image == null)
		{
			img_link = $('#baseUrl').html() + '/images/img_unavailable.jpg';
			img_exists = 'none';
		}
		else
		{
			img_link = data.image;
			img_exists = '';
		}
		var imgCol = '<span class="imgPreTbl" id="cmp-'+id+'-img" data-img="'+img_exists+'"><img src="'+img_link+'"></span>';
		
		var nameCol = '<span id="cmp-'+id+'-name">'+form.find('#name').val()+'</span>';

		var advCol = '<span id="cmp-'+id+'-adv" data-adv="'+form.find('#advertiser').val()+'">'+form.find('#advertiser :selected').html()+'</span>';

		var typeCol = '<span id="cmp-'+id+'-type" data-type="'+form.find('#campaign_type').val()+'">'+form.find('#campaign_type :selected').html()+'</span>';
		
		var lctCol = '<span id="cmp-'+id+'-lct" data-type="'+form.find('#lead_type').val()+'">'+form.find('#lead_type :selected').html()+'</span>';

		var drcvCol = '<span id="cmp-'+id+'-drcv">'+form.find('#default_received').val()+'</span>';

		var rateCol = '<span id="cmp-'+id+'-rate">'+form.find('#rate').val()+'</span>';

		//checks if lead type is unlimited
		if(form.find('#lead_type').val() == 0 ) lead_value = 0;
		else lead_value = form.find('#lead_value').val();
		var lcvCol = '<span id="cmp-'+id+'-lcv">'+lead_value+'</span>';

		// if(form.find('#notes').val().length > 75) notes = form.find('#notes').val().substr(0, 75) + '...';
		// else notes = form.find('#notes').val();
		// var notesCol = '<span id="cmp-'+id+'-notes-preview">'+notes+'</span>';
		
		var statCol = '<span id="cmp-'+id+'-stat" data-status="'+form.find('input[name="status"]:checked').val()+'">'+form.find('input[name="status"]:checked').data('label')+'</span>';

		//ACTION & HIDDEN AREA
		$('#cmp-'+id+'-notes').html(form.find('#notes').val());
		$('#cmp-'+id+'-desc').html(form.find('#description').val());
		$('#cmp-'+id+'-dpyt').val(form.find('#default_payout').val());
		// $('#cmp-'+id+'-drcv').val(form.find('#default_received').val());
		$('#cmp-'+id+'-ctgry').val(form.find('#category').val());
		$('#cmp-'+id+'-lnkOutOffer').val(form.find('#linkout_offer_id').val());
		$('#cmp-'+id+'-prgId').val(form.find('#program_id').val());
		$('#cmp-'+id+'-rate').val(form.find('#rate').val());
		$('#cmp-'+id+'-advEmail').val(form.find('#advertiser_email').val());

		$('#modal-campaign-title').html(id + ' - '+ form.find('#name').val());	
		
		alert('Campaign modification saved!');
		form.find('.this_modal_submit').html('Save');	

		var table = $('#campaign-table').DataTable();
		try {
		    table.cell( $('#cmp-'+id+'-prio').parent('td') ).data(prioCol);
			table.cell( $('#cmp-'+id+'-img').parent('td') ).data(imgCol);
			table.cell( $('#cmp-'+id+'-name').parent('td') ).data(nameCol);
			table.cell( $('#cmp-'+id+'-adv').parent('td') ).data(advCol);
			table.cell( $('#cmp-'+id+'-type').parent('td') ).data(typeCol);
			table.cell( $('#cmp-'+id+'-lct').parent('td') ).data(lctCol);
			table.cell( $('#cmp-'+id+'-lcv').parent('td') ).data(lcvCol);
			table.cell( $('#cmp-'+id+'-drcv').parent('td') ).data(drcvCol);
			table.cell( $('#cmp-'+id+'-stat').parent('td') ).data(statCol);
			table.cell( $('#cmp-'+id+'-rate').parent('td') ).data(rateCol);
		}
		catch(err) {
		   console.log('Not Existing 1');
		}
		

		//PRIORITY CHANGES
		var affected_campaign = data.affected;
		if(affected_campaign)
		{
			$.each(affected_campaign, function(priority, campaign)
			{
				// console.log(campaign + ' - ' + $('#cmp-'+campaign+'-prio').length);
				if( $('#cmp-'+campaign+'-prio').length != 0 ) {
					var prioCol = '<span id="cmp-'+campaign+'-prio">'+priority+'</span>';
					table.cell( $('#cmp-'+campaign+'-prio').parent('td') ).data(prioCol).draw();
				}
			});
		}

		//campaign payout history
		var campaign_payout_history_table = $('#campaignPayoutsHistory-table').DataTable();
		campaign_payout_history_table.ajax.reload();
	}else {
		form.find('.this_modal_submit').html('Not Saved');	
		//alert('Errors encountered on ');
		var msg = '<div class="alert alert-danger" role="alert"><b>Error!</b> Campaign changes not saved due to error encountered in campaign stack. Please review code.</div>';
			msg += '<table class="table table-bordered"><tr><th>Errors</th></tr>';

		$.each(data.errors, function(i, error) {
			if(typeof error === 'object') {
				error = '<textarea class="form-control" rows="3">'+JSON.stringify(error)+'</textarea>';
			}
			msg += '<tr><td>'+error+'</td></tr>';
		});
			msg += '</table>';

		$('#campaignErrorModal .modal-body').html(msg);
		$('#campaignErrorModal').modal('show');
	}
		
}

/**
 * Update lead details
 *
 * @param data
 */
function updateLeadDetails(data)
{
	$('#lead_details_modal').modal('hide');
}

/**
 * Add Campaign Payout Process. This will append new payout/s and dismisses the form.
 *
 * @param data
 */
function addCampaignPayoutProcess(data) 
{
	console.log(data);

	var form = $('#payout_tab form');

	var selectedAffiliates = $('select[name="payout[]"] option:selected');
	var receivable = $('#payout_receivable').val();
	var payable = $('#payout_payable').val();

	var dCounter = 0;
	var table = $('#campaign-payout-table').DataTable();

	$.each(selectedAffiliates, function() {
		var aff_id = $(this).val();
		var aff_name = $(this).html();
		var id = data[dCounter];
		$(this).remove();
		var idCol = '<input name="select_payout[]" class="selectCampaignPayout" value="'+id+'" data-name="'+aff_name+'" type="checkbox">';
		var receiveCol = '<span id="cp-'+id+'-receivable">'+ receivable +'</span>'
		var payoutCol = '<span id="cp-'+id+'-payable">'+ payable +'</span>'
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
		var rowNode = table.row.add(dataRow).draw().node();
		var affRow = $(rowNode);

		dCounter++;
	});
	
	$('#campaignPayoutForm').collapse('hide');
}

/**
 * Edit campaign payout process
 *
 * @param data
 */
function editCampaignPayoutProcess(data)
{
	console.log(data);
	var receivable  = $('#edit_payout_receivable').val();
	var payable  = $('#edit_payout_payable').val();
	$.each(data, function(index,id){
		$('#cp-'+ id +'-receivable').html(receivable);
		$('#cp-'+ id +'-payable').html(payable);
	});		
	// var table = $('#campaign-payout-table');
	// refreshTable(table);
	$('.selectCampaignPayout').prop('checked', false);
	$('#editCampaignPayoutForm').collapse('hide');

	$('#editPytAff').attr('disabled',true);
	$('#deletePytAff').attr('disabled',true);
}

/**
 * Edit Campaign config process
 *
 * @param data
 */
function editCampaignConfigProcess(data)
{
	$('#cmpCfg-url').html( $('#cmpCfg-url-txt input').val() );
	$('#cmpCfg-hdr').text( $('#cmpCfg-hdr-txt textarea').val() ).html();
	$('#cmpCfg-dta').text( $('#cmpCfg-dta-txt textarea').val() ).html();
	$('#cmpCfg-dta-fv').text( $('#cmpCfg-dta-fv-txt textarea').val() ).html();
	$('#cmpCfg-map').text( $('#cmpCfg-map-txt textarea').val() ).html();
	$('#cmpCfg-mtd').html( $('#cmpCfg-mtd-txt select').val() );
	$('#cmpCfg-scs').text( $('#cmpCfg-scs-txt input').val() ).html();
	$('#cmpCfg-purl').html( $('#cmpCfg-purl-txt input').val() );
	$('#cmpCfg-pscs').text( $('#cmpCfg-pscs-txt input').val() ).html();

	var ftp_sent = 'NO';
	if($('#if_ftp_sent').val() == 1) ftp_sent = 'YES';
	$('#cmpCfg-ftps').html(ftp_sent);
	$('#cmpCfg-ftpp').html($('#cmpCfg-ftpp-txt select option[value="'+$('#cmpCfg-ftpp-txt select').val()+'"]').html());
	$('#cmpCfg-ftpu').html($('#cmpCfg-ftpu-txt input').val());
	$('#cmpCfg-ftppw').html($('#cmpCfg-ftppw-txt input').val());
	$('#cmpCfg-ftph').html($('#cmpCfg-ftph-txt input').val());
	$('#cmpCfg-ftppt').html($('#cmpCfg-ftppt-txt input').val());
	$('#cmpCfg-ftpto').html($('#cmpCfg-ftpto-txt input').val());
    $('#cmpCfg-ftpdirectory').html($('#cmpCfg-ftpdirectory-txt input').val());

    $('#cmpCfg-email').html($('#if_email_sent').val() == 1 ? 'YES' : 'NO');
    $('#cmpCfg-emailTo').html($('#cmpCfg-emailTo-txt input').val());
    $('#cmpCfg-emailTitle').html($('#cmpCfg-emailTitle-txt input').val());
    $('#cmpCfg-emailBody').html($('#cmpCfg-emailBody-txt textarea').val());
	
	var this_button = $('#editCmpConfig');
	this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
	this_button.attr('data-config','show').data('config','show');
	$('#campConfigDiv').addClass('hidden');
	$('.cmpCfg-form').addClass('hidden');
	$('.cmpCfg-dsply').removeClass('hidden');
}

/**
 * Edit Campaign Long Content process
 *
 * @param data
 */
function editCampaignLongContentProcess(data)
{
	var this_button = $('#editCampaignLongContent');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpLngCnt-form-wrapper').addClass('hidden');
		$('#cmpCnt-long-content').attr('disabled',true);
		$('#cmpCnt-long-actual').val($('#cmpCnt-long-content').val());
}

/**
 * Edit campaign stack content process
 *
 * @param data
 */
function editCampaignStackContentProcess(data)
{
	console.log(data);
	if(data.status) {
		var this_button = $('#editCampaignStackContent');
			this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
			this_button.attr('data-config','show').data('config','show');
			//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
			$('.cmpStkCnt-form-wrapper').addClass('hidden');
			$('#cmpCnt-stack-content').attr('disabled',true);
			$('#cmpCnt-stack-actual').val($('#cmpCnt-stack-content').val());
		
		var stackCodeMirror = $('.CodeMirror')[0].CodeMirror;
		stackCodeMirror.getDoc().setValue($('#cmpCnt-stack-actual').val());
		setTimeout(function() {
			stackCodeMirror.refresh();
			stackCodeMirror.setOption("readOnly", true);
		},100);
	}else {
		$('#campaignStackForm [type="submit"]').removeAttr('disabled');
		var msg = '<div class="alert alert-danger" role="alert"><b>Error!</b> Campaign changes not saved due to error encountered in campaign stack. Please review code.</div>';
			msg += '<table class="table table-bordered"><tr><th>Errors</th></tr>';

		$.each(data.errors, function(i, error) {
			if(typeof error === 'object') {
				error = '<textarea class="form-control" rows="3">'+JSON.stringify(error)+'</textarea>';
			}
			msg += '<tr><td>'+error+'</td></tr>';
		});
			msg += '</table>';

		$('#campaignErrorModal .modal-body').html(msg);
		$('#campaignErrorModal').modal('show');
	}
		
}

/**
 * Edit High Paying Content process
 *
 * @param data
 */
function editCampaignHighPayingContentProcess(data)
{
	var this_button = $('#editCampaignHighPayingContent');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpHPCnt-form-wrapper').addClass('hidden');
		$('#cmpCnt-hp-content').attr('disabled',true);
		$('#cmpCnt-hp-actual').val($('#cmpCnt-hp-content').val());
}

function editCampaignPostingInstructionProcess(data)
{
	var this_button = $('#editPostingInstruction');
		this_button.html('<span class="glyphicon glyphicon-pencil"></span>');
		this_button.attr('data-config','show').data('config','show');
		//$('.cmpCnt-dsply-wrapper').removeClass('hidden');
		$('.cmpPI-form-wrapper').addClass('hidden');
		// $('#cmp-posting-instruction').attr('disabled',true);
		$('#cmp-posting-instruction-actual').val($('#cmp-posting-instruction').val());
		$('#cmp-sample-code').attr('disabled',true);
		$('#cmp-sample-code-actual').val($('#cmp-sample-code').val());
		$('#cmp-posting-instruction').ckeditorGet().setReadOnly();
}

/**
 * Add revenue tracker process
 *
 * @param data
 */
function addRevenueTrackerProcess(data)
{
	var id = data.id;

	var webCol = '<span id="trk-'+id+'-web">'+$('#website').val()+'</span>';
	var affCol = '<span id="trk-'+id+'-aff" data-affiliate="'+$('#affiliate').val()+'">'+ $('#affiliate :selected').html()+'</span>';
	var ofrCol = '<span id="trk-'+id+'-ofr">'+$('#offer').val()+'</span>';
	var cmpCol = '<span id="trk-'+id+'-cmp">'+$('#campaign').val()+'</span>';
	var rtiCol = '<span id="trk-'+id+'-rti">'+$('#revenue_tracker').val()+'</span>';
	var s1Col = '<span id="trk-'+id+'-s1">'+$('#s1').val()+'</span>';
	var s2Col = '<span id="trk-'+id+'-s2">'+$('#s2').val()+'</span>';
	var s3Col = '<span id="trk-'+id+'-s3">'+$('#s3').val()+'</span>';
	var s4Col = '<span id="trk-'+id+'-s4">'+$('#s4').val()+'</span>';
	var s5Col = '<span id="trk-'+id+'-s5">'+$('#s5').val()+'</span>';
	var actionCol = '<input type="hidden" id="trk-'+id+'-crglmt" value="'+$('#crg_limit').val()+'"/>';
		actionCol += '<input type="hidden" id="trk-'+id+'-extlmt" value="'+$('#ext_limit').val()+'"/>';
		actionCol += '<input type="hidden" id="trk-'+id+'-lnklmt" value="'+$('#lnk_limit').val()+'"/>';
		actionCol += '<input type="hidden" id="trk-'+id+'-lnk" value="'+$('#link').val()+'"/>';
		actionCol += '<input type="hidden" id="trk-'+id+'-nts" value="'+$('#notes').val()+'"/>';
		actionCol += '<input type="hidden" id="trk-'+id+'-type" value="'+$('#type').val()+'"/>';

	//determine if the user can edit or delete campaigns
	if(data.canEdit)
	{
		actionCol += '<button class="editTracker btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
	}

	if(data.canDelete)
	{
		actionCol += '<button class="deleteTracker btn btn-default" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
	}

	var dataRow = [
		webCol,
		affCol,
		cmpCol,
		ofrCol,
		rtiCol,
		s1Col,
		s2Col,
		s3Col,
		s4Col,
		s5Col,
		data.subid_breakdown == 0 ? 'Disabled' : 'Enabled',
		actionCol
	];

	var table = $('#tracker-table').DataTable();
	var rowNode = table.row.add(dataRow).draw().node();

	$('#TrkFormModal').modal('hide');
}

/**
 * Edit revenue tracker process
 *
 * @param id
 */
function editRevenueTrackerProcess(id)
{
	console.log(id);

	$('#trk-'+id+'-web').html($('#website').val());
	$('#trk-'+id+'-aff').attr('data-affiliate',$('#affiliate').val()).data('affiliate',$('#affiliate').val());
	$('#trk-'+id+'-aff').html($('#affiliate option:selected').html());
	$('#trk-'+id+'-ofr').html($('#offer').val());
	$('#trk-'+id+'-cmp').html($('#campaign').val());
	$('#trk-'+id+'-rti').html($('#revenue_tracker').val());
	$('#trk-'+id+'-s1').html($('#s1').val());
	$('#trk-'+id+'-s2').html($('#s2').val());
	$('#trk-'+id+'-s3').html($('#s3').val());
	$('#trk-'+id+'-s4').html($('#s4').val());
	$('#trk-'+id+'-s5').html($('#s5').val());
	$('#trk-'+id+'-lnk').val($('#link').val());
	$('#trk-'+id+'-crglmt').val($('#crg_limit').val());
	$('#trk-'+id+'-extlmt').val($('#ext_limit').val());
	$('#trk-'+id+'-lnklmt').val($('#lnk_limit').val());
	$('#trk-'+id+'-nts').val($('#notes').val());
	$('#trk-'+id+'-type').val($('#type').val());
	$('#trk-'+id+'-fire').val($('#fire').val());
	$('#trk-'+id+'-pixel').text($('#pixel').val());

	// refreshTable($('#tracker-table'));
	var table = $('#tracker-table').DataTable();
	table.draw();

	$('#TrkFormModal').modal('hide');
}

function updateRevenueTrackerMixedCoregCampaignOrderProcess(id)
{
    console.log(id);
    var form = $('#mixed_coreg_campaign_order_form');

    $('#trk-'+id+'-mixed_coreg_order_by').val(form.find('#mixed_coreg_order_by').val());
    $('#trk-'+id+'-mixed_coreg_order_status').val(form.find('#mixed_coreg_reorder').val());
    $('#trk-'+id+'-mixed_coreg_campaign_views').val(form.find('#mixed_coreg_views').val());
    $('#trk-'+id+'-default_order').val(form.find('#default_order').val());
    $('#trk-'+id+'-mixed_coreg_campaign_limit').val(form.find('#mixed_coreg_limit').val());
}

function updateRevenueTrackerCampaignOrderProcess(data) {

    // console.log(data);
    var form = $('#campaign_order_form'),
        id = data.id;

	// $('#trk-'+id+'-order_by').val(form.find('#order_by').val());
	// $('#trk-'+id+'-order_status').val(form.find('#reorder').val());
	// $('#trk-'+id+'-views').val(form.find('#views').val());
	// $('#trk-'+id+'-default_order').val(form.find('#default_order').val());

	$('#trk-'+id+'-details').html(JSON.stringify(data.tracker));

	if(data.change_ref_date) {
		$('#CmpOrdrFormModal').modal('hide');
	}
}

/**
 * Add gallery process
 *
 * @param gallery
 */
function addGalleryImageProcess(gallery)
{
	refreshGallery(gallery);
	$('#addGalImgModal').modal('hide');
}

/**
 * Referesh Gallery
 *
 * @param gallery
 */
function refreshGallery(gallery) 
{
	var base_url = $('#baseUrl').html();
	var table = $('#gallery-table').DataTable(); 
	table.clear().draw();
	$.each(gallery, function(index,row){
		var dataRow = [];
		$.each(row, function(index,image){
			if(image != '') {
				var img_url = base_url +'/'+image;
				var img_name = image.split('/').pop();
				var tdCol = '<div><div class="gal-wrap">';
					tdCol += '<div class="gal-img-wrp">';
					tdCol += '<img src="'+img_url+'" class="gal-img"/>';
					tdCol += '</div>';
					tdCol += '<div class="gal-img-name">'+img_name+'</div>';
					tdCol += '<div class="gal-img-actn btn-group" role="group" aria-label="...">';
					tdCol += '<button type="button" class="btn btn-default copyUrlToClipboard" data-clipboard-text="'+img_url+'" data-toggle="tooltip" data-placement="bottom" title="copied"><span class="glyphicon glyphicon-duplicate"></span></button>';
					tdCol += '<button type="button" class="btn btn-default viewGalImg" data-url="'+img_url+'"><span class="glyphicon glyphicon-eye-open"></span></button>';
					tdCol += '<button type="button" class="btn btn-default deleteGalImg" data-img = "'+image+'"><span class="glyphicon glyphicon-trash"></span></button>';
					tdCol += '</div>';
					tdCol += '</div></div>';
			}else {
				var tdCol = '<td class="no-content"></td>';
			}
			dataRow.push(tdCol);
			// console.log(image);
		});	
		var rowNode = table.row.add(dataRow).draw().node();
	});	
}

/**
 * Add campaign affiliate process
 *
 * @param data
 */
function addCampaignAffiliateProcess(data)
{
	console.log(data);

	var selectedAffiliates = $('select[name="affiliates[]"] option:selected');
	var dCounter = 0;
	var table = $('#campaign-affiliate-table').DataTable();
	var eiqFrameID = $('#eiq_iframe_id').val(),
		isEIQIframe = false,
		cID = $('#this_id.this_campaign').val();

	if(eiqFrameID != '' && eiqFrameID > 0 && eiqFrameID == cID) isEIQIframe = true;

	$.each(selectedAffiliates, function() {
		var aff_id = $(this).val();
		var aff_name = $(this).html();
		var id = data[dCounter];
		var lead_cap_type_id = $('#lead_cap_type').val();
		var lead_cap_type_name = $('#lead_cap_type :selected').html();
		var lead_cap_value = $('#lead_cap_value').val();

		if(lead_cap_type_id == 0) lead_cap_value == 0;
		$(this).remove();
		
		var idCol = '<input name="select_affiliate[]" class="selectCampaignAffiliate" value="'+id+'" data-name="'+aff_name+'" type="checkbox"> ';
		var leadTypeCol = '<span id="ca-'+id+'-type" data-id="'+lead_cap_type_id+'">'+lead_cap_type_name+'</span>';
		var leadValCol = '<span id="ca-'+id+'-value">'+lead_cap_value+'</span>';
		var actionCol =  '<button id="ca-'+id+'-edit-button" class="btn btn-default editCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
			
		
		if(isEIQIframe) {
			actionCol += ' <button id="ca-'+aff_id+'-status-button" class="btn btn-danger eiqFrameStatus" type="button" data-id="'+aff_id+'" data-status="0"><span class="glyphicon glyphicon-remove-circle"></span></button>';
			actionCol += ' <button id="ca-'+aff_id+'-iframe-button" class="btn btn-default eiqFrameTraffic" type="button" data-id="'+aff_id+'">Iframe Traffic</button>';
		}else {
			actionCol += ' <button id="ca-'+id+'-delete-button" class="btn btn-default deleteCampaignAffiliate" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
		}

		var dataRow = [
			idCol,
			aff_name,
			leadTypeCol,
			leadValCol,
			actionCol
		];
		
		//add the row data
		var rowNode = table.row.add(dataRow).draw().node();
		var affRow = $(rowNode);

		dCounter++;
	});

	$('#campaignAffiliateForm').collapse('hide');
	$('#lead_cap_type').val(0);
	$('#lead_cap_value').val('');
	
	var affiliate_count = $('select[name="affiliates[]"] option').length;
	if(affiliate_count <= 10) $('select[name="affiliates[]"]').attr('size', affiliate_count);
}

/**
 * Edit campaign affiliate process
 *
 * @param data
 */
function editCampaignAffiliateProcess(data)
{
	// console.log(data);
	var type  = $('#edit_lead_cap_type').val();
	var value  = $('#edit_lead_cap_value').val();
	$.each(data, function(index,id){
		if(type == 0) value = 0;
		if(type != 0 && value == '') value = 0;
		$('#ca-'+ id +'-type').attr('data-id',type).data('id',type).html($('#edit_lead_cap_type :selected').html());
		$('#ca-'+ id +'-value').html(value);
	});		
	var table = $('#campaign-affiliate-table');
	refreshTable(table);
	$('.selectCampaignAffiliate').prop('checked', false);
	$('#editCampaignAffiliateForm').collapse('hide');

	$('#editAffilatesBtn').attr('disabled',true);
	$('#deleteAffiliatesBtn').attr('disabled',true);
}

/**
 * Add campaign filter group process
 *
 * @param id
 */
function addCampaignFilterGroupProcess(id)
{

	var name = $('#filter_group_name').val();
	var desc = $('#filter_group_description').val();
	var stat = $('input[type="radio"][name="filter_group_status"]:checked');
	var nameCol = '<span id="cfg-'+id+'-name">'+name+'</span>';
	var descCol = '<span id="cfg-'+id+'-desc">'+desc+'</span>';
	var statusCol = '<span id="cfg-'+id+'-stat" data-status="'+stat.val()+'">'+stat.data('label')+'</span>';
	var actionCol = '<button id="cfg-'+id+'-view-button" class="btn btn-default viewCampaignFilterGroup" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-eye-open"></span></button>';
		actionCol += '<button id="cfg-'+id+'-edit-button" class="btn btn-default editCampaignFilterGroup" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
		actionCol += '<button id="cfg-'+id+'-delete-button" class="btn btn-default deleteCampaignFilterGroup" type="button" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';
	
	var dataRow = [
		nameCol,
		descCol,
		statusCol,
		actionCol
	];

	var table = $('#campaign-filter-group-table').DataTable();
	var rowNode = table.row.add(dataRow).draw().node();
	$('.filterGroupList').append('<option value="'+id+'">'+name+'</option>');
	var length = $('.viewCampaignFilterGroup').length;
    if(length > 0) {
    	$('.filterGroupList').attr('size',length);
    	$('#addCmpFilter').removeAttr('disabled');
    }else {
    	$('#addCmpFilter').attr('disabled',true);
    }

	$('#campaignFilterGroupForm').collapse('hide');
}

/**
 * Edit campaign filter group process
 *
 * @param data
 */
function editCampaignFilterGroupProcess(data)
{

	var the_collapse = $('#campaignFilterGroupForm');
	var the_form = the_collapse.find('form');
	var id = the_form.find('#this_id').val();
	var cfg = '#cfg-'+id+'-';
	var name = $('#filter_group_name').val();
	var desc = $('#filter_group_description').val();
	var stat = $('input[type="radio"][name="filter_group_status"]:checked');

	var table = $('#campaign-filter-group-table').DataTable();

	var nameCol = '<span id="cfg-'+id+'-name">'+name+'</span>';
	var descCol = '<span id="cfg-'+id+'-desc">'+desc+'</span>';
	var statCol = '<span id="cfg-'+id+'-stat" data-status="'+stat.val()+'">'+stat.data('label')+'</span>';
	table.cell( $('#cfg-'+id+'-name').parent('td') ).data(nameCol).draw();
	table.cell( $('#cfg-'+id+'-desc').parent('td') ).data(descCol).draw();
	table.cell( $('#cfg-'+id+'-stat').parent('td') ).data(statCol).draw();

	$('.filterGroupList [value="'+id+'"]').html(name);

	the_collapse.collapse('hide');
}

/**
 * Add Campaign Filter Process. This will append new filter and dismisses the form.
 *
 * @param data
 */
function addCampaignFilterProcess(data)
{	
	var filter_groups = $('[name="filter_group[]"]').val();
	var filter_ids = data;
	var counter = 0;

	$.each(filter_groups, function(index,filter_group_id){

		console.log(filter_group_id);

		var filter_group_table = $('#cfg-'+filter_group_id+'-table');
		var filter_group_table_dt = filter_group_table.DataTable();

		if($('#cfg-'+filter_group_id+'-no-table').length == 1) { 
			//Filter Group Filter is Opened but No Filters
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Close
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
		} 
		if( filter_group_table.length == 1) {
			//Filter Group Filter is Opened and Has Filters
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Close
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
		}else {
			//Filter Group Filter is Closed
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
		}

		$('#campaignFilterForm').collapse('hide');
	});
}

/**
 * Edit Campaign Filter Process. This will update existing filter and dismisses the form.
 *
 * @param data
 */
function editCampaignFilterProcess(data)
{

	console.log(data);

	var form = $('#campaignFilterForm form');
	var filter_groups = $('[name="filter_group[]"]').val();
	var filter_ids = data;
	var counter = 0;
	$.each(filter_groups, function(index,filter_group_id) {
		filter_group_table = $('#cfg-'+filter_group_id+'-table');
		filter_group_table_dt = $('#cfg-'+filter_group_id+'-table').DataTable();

		if($('#cfg-'+filter_group_id+'-no-table').length == 1) { 
			//Filter Group Filter is Opened but No Filters
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Close
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
		} 

		if( filter_group_table.length == 1) {
			//Filter Group Filter is Opened and Has Filterss
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Close
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
		}else {
			//Filter Group Filter is Closed
			$('#cfg-'+filter_group_id+'-view-button').trigger('click'); //Open
		}
	});

	$('#campaignFilterForm').collapse('hide');
}

function addCategoryProcess(id)
{
	var stat = $('input[name="status"]:checked');
	var desc = $('#description').val();
	if(desc.length > 75) preview = desc.substr(0, 75) + '...';
	else preview = desc;
	var nameColumn = '<span id="cat-'+id+'-name">'+$('#name').val()+'</span>';
	var descColumn = '<span id="cat-'+id+'-desc-preview">'+preview+'</span>';
	var statColumn = '<span id="cat-'+id+'-status" data-id="'+stat.val()+'">'+stat.data('label')+'</span>';
	var actionColumn = '<textarea id="cat-'+id+'-desc" class="hidden" disabled>'+desc+'</textarea>';
		actionColumn += '<button class="editCategory btn btn-primary" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
		actionColumn += ' <button class="deleteCategory btn btn-danger" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';

	var dataRow = [
		nameColumn,
		descColumn,
		statColumn,
		actionColumn
	];

	var table = $('#categories-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();

	$('#category_form_modal').modal('hide');
}

function editCategoryProcess(id)
{
	// console.log(id);
	var name = $('#name').val();
	var stat = $('input[name="status"]:checked');
	var desc = $('#description').val();
	if(desc.length > 75) preview = desc.substr(0, 75) + '...';
	else preview = desc;

	var table = $('#categories-table').DataTable();

	var nameCol = '<span id="cat-'+id+'-name">'+name+'</span>';
	var descCol = '<span id="cat-'+id+'-desc-preview">'+preview+'</span>';
	var statCol = '<span id="cat-'+id+'-status" data-status="'+stat.val()+'">'+stat.data('label')+'</span>';
	$('#cat-'+id+'-desc').html(desc);

	table.cell( $('#cat-'+id+'-name').parent('td') ).data(nameCol).draw();
	table.cell( $('#cat-'+id+'-desc-preview').parent('td') ).data(descCol).draw();
	table.cell( $('#cat-'+id+'-status').parent('td') ).data(statCol).draw();

	$('#category_form_modal').modal('hide');
}

function addNotesCategoryProcess(id)
{
	var form = $('#notes_category_form');
	var name = $(form).find('[name="name"]').val();
	var editBtn = '<button data-id="'+id+'" class="editNoteCategoryBtn btn btn-primary btn-xs" type="button" style="margin-right:5px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>';
    var deleteBtn = '<button data-id="'+id+'" class="deleteNoteCategoryBtn btn btn-danger btn-xs" type="button"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
                        
	var dataRow = [
		id,
		name,
		editBtn + deleteBtn,
	];

	var table = $('#notes_category-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();

	$('#noteCategoryCollapse').collapse('hide');

	$('#notesCategoryList').append('<a href="#" data-id="'+id+'" class="list-group-item">'+name+' <span class="badge"></span></a>');
}

function editNotesCategoryProcess(id)
{
	console.log(id);

	var form = $('#notes_category_form');
	var name = $(form).find('[name="name"]').val();

	$('.editNoteCategoryBtn[data-id="'+id+'"]').closest('tr').find('td:nth-child(2)').html(name);

	var table = $('#notes_category-table').DataTable();
	table.draw();

	$('#notesCategoryList a[data-id="'+id+'"]').html(name);

	$('#noteCategoryCollapse').collapse('hide');
}

function addNoteProcess(note) {
	console.log(note)

	$('#notesList a.isEmpty').remove();

	$('#notesList').prepend('<a href="#" data-id="'+note.id+'" data-cat="'+note.category_id+'" class="list-group-item">'+note.subject+'</a>');
	theNotes[note.category_id][note.id] = note;

	$('#notesList a[data-id="'+note.id+'"]').trigger('click');

}

function editNoteProcess(note) {
	console.log(note)
	theNotes[note.category_id][note.id] = note;
	$('#notesList a[data-id="'+note.id+'"]').html(note.subject);
}

/**
* Edit Account Contact Info for Affiliate and Advertiser Portal By Burt 07/25/2016
*
*/

function editAccountContactInfo(data)
{
  console.log(data);

  $('#updateContactBtn').val('Done!!!');

  setTimeout(function(){
  $('#updateContactBtn').val("Update");
  }, 1000);

  alert('Contact Info has been successfully updated...');
    
}

/**
* Change Contact Password for Affiliate and Advertiser Portal By Burt 07/25/2016
*
*/

function changePasswordContactInfo(data)
{
   console.log(data);

  $('#changePasswordBtn').val('Done!!!');

  setTimeout(function(){
  $('#changePasswordBtn').val("Update");
  }, 1000);

  alert('Password has been successfully updated...');
  
}

function reportBugProcess(data) 
{
	// console.log(data);
	
	$('#notificationModal').modal('show');

	$('#bugReportDropdown').removeClass('open');
	$('#report_bug_form').find('#bug_summary').val('');
	$('#report_bug_form').find('#bug_description').val('');
	$('#final_list_of_files').val('');
	$('[name="bug_evidence_files[]"]').val('');
	$('[name="bug_evidence_files[]"]:not([id])').remove();
	$('#bugFileList').empty().append('<li class="list-group-item" style="height:34px"></li>');
	$('#bugReportSubmitBtn').html('Submit');
	bugFiles = [];
		
	setTimeout(function(){ 
		$('#notificationModal').modal('hide');
	}, 5000); 
}

function updateSettingsProcess(data) 
{
	alert('Settings Successfully Updated!');
	//Reset Stack Page Order
	$('#stack_page_order').val('');

	//Reset Lead Rejection Rate
	if(typeof data.new_rates != 'undefined') {
		var new_rates = data.new_rates;
		$('#update_rejection_rate').val(0);
		$('#min_high_reject_rate_hidden').val(new_rates[0]);
		$('#max_high_reject_rate_hidden').val(new_rates[1]);
	}
}

function addPathProcess(id)
{
	var nameColumn = '<span id="sp-'+id+'-name">'+$('#name').val()+'</span>';
	var urlColumn = '<a id="sp-'+id+'-url" href="'+$('#url').val()+'" target="_blank">'+$('#url').val()+'</a>';
	var	actionColumn = '<button class="editPath btn btn-primary" data-id="'+id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
		actionColumn += ' <button class="deletePath btn btn-danger" data-id="'+id+'"><span class="glyphicon glyphicon-trash"></span></button>';

	var dataRow = [
		nameColumn,
		urlColumn,
		actionColumn
	];

	var table = $('#paths-table').DataTable();
	//add the row data
	var rowNode = table.row.add(dataRow).draw().node();

	$('#path_form_modal').modal('hide');
}

function editPathProcess(id)
{
	var name = $('#name').val();
	var url = $('#url').val();

	var table = $('#paths-table').DataTable();

	var nameCol = '<span id="sp-'+id+'-name">'+name+'</span>';
	var urlCol = '<a id="sp-'+id+'-url" target="_blank" href="'+url+'">'+url+'</a>';

	table.cell( $('#sp-'+id+'-name').parent('td') ).data(nameCol).draw();
	table.cell( $('#sp-'+id+'-url').parent('td') ).data(urlCol).draw();

	$('#path_form_modal').modal('hide');
}

function addAffiliateWebsiteProcess(data) {
	// console.log(data);

	var table = $('#affiliate-website-table').DataTable();
	table.draw();

	$('#editAffilateWebsitesBtn').attr('disabled',true);
	$('#deleteAffiliateWebsitesBtn').attr('disabled',true);

	$('#affiliateWebsiteForm').collapse('hide');
}

function affiliateWebsitePayoutProcess(data) {
	var table = $('#affiliate-website-table').DataTable();
	table.draw();

	$('.selectAllAffiliateWebsite').prop('checked', false);
	$('#affiliateWebsitePayoutForm').collapse('hide');
}

function campaignConfigInterface(config) {
	console.log(config);

	$('#cmpCfg-url').html(config.post_url);
	$('#cmpCfg-url-txt input').val(config.post_url);
	$('#cmpCfg-hdr').text(config.post_header).html();
	$('#cmpCfg-hdr-txt textarea').val(config.post_header);
	$('#cmpCfg-dta').text(config.post_data).html();
	$('#cmpCfg-dta-txt textarea').val(config.post_data);
	$('#cmpCfg-dta-fv').text(config.post_data_fixed_value).html();
	$('#cmpCfg-dta-fv-txt textarea').val(config.post_data_fixed_value);
	$('#cmpCfg-map').text(config.post_data_map).html();
	$('#cmpCfg-map-txt textarea').val(config.post_data_map);
	$('#cmpCfg-mtd').html(config.post_method);
	$('#cmpCfg-mtd-txt select').val(config.post_method);
	$('#cmpCfg-scs').text(config.post_success).html();
	$('#cmpCfg-scs-txt input').val(config.post_success);
	$('#cmpCfg-purl').text(config.ping_url).html();
	$('#cmpCfg-purl-txt input').val(config.ping_url);
	$('#cmpCfg-pscs').text(config.ping_success).html();
	$('#cmpCfg-pscs-txt input').val(config.ping_success);

	$('#cmpConfigAutomationModal').modal('hide');

	$('#cmpConfigAutomationTable').show();
}

function campaignAffiliateManagement(data) {
	console.log(data);
	var table = $('#cmpAffMgmt-table').DataTable();
	table.draw();
	$('.camAddAff-div').find('[name="lead_cap_type"]').val(0).trigger('change');
	$('#allCAMTblcampaigns-chkbx').prop('checked', false).trigger('change');
	$('#allCAMcampaigns-chkbx').prop('checked', false).trigger('change');
}

function revTrackerToExitPageList(data) {
	$('#exit_page_rev_tracker_selection').val(null).trigger('change');
	var table = $('#exitPageTracker-table').DataTable();
	table.search('').draw();
}

function addBannedLeadProcess(data)
{
	console.log(data)
	var table = $('#leads-table').DataTable();
	table.draw();

	$('#banned_form_modal').modal('hide');
}

function editBannedLeadProcess(data)
{
	console.log(data)
	var table = $('#leads-table').DataTable();
	table.draw();

	$('#banned_form_modal').modal('hide');
}

/**
 * escape HTML
 *
 * @param html
 * @returns {XML|string}
 */

function escapeHTML(html) {
    return html.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
 }

$(document).ready(function() 
{
    $('#side-menu').metisMenu({
        toggle: false // disable the auto collapse. Default: true.
    });

	$('div.alert').not('.alert-important').delay(3000).slideUp();

    $.ajaxSetup({
       headers: {
           'X-CSRF-Token': $('meta[name="_token"]').attr('content')
       }
    });

   	$(document).on('submit','.this_form',function(e)
   	{
		e.preventDefault();
		var form = $(this);
		var the_data = form.serialize();
    	var url = form.attr('action');

    	var confirmation = true;

    	if($(this).data('confirmation') != '')
		{
			confirmation = confirm(form.data('confirmation'));
		}

		if(confirmation === true) 
		{
			form.find('.this_modal_submit').attr('disabled','true');
			form.find('[type="submit"]').attr('disabled','true');

			$.ajax({
				type: 'POST',
				data: the_data,
				url: url,
				error: function(data)
				{
					
					var errors = data.responseJSON;

					var process = form.data('process');

					if(process=='add_contact' || process=='edit_contact')
					{
						//remove the duplicate error message during adding contact with unassigned affiliate or advertiser
						//errors.advertiser_id[0] = '';
						if(errors.advertiser_id!=undefined)
						{
							errors.advertiser_id[0] = '';
						}
					}

					console.log(errors);

					form.find('.error').each(function(){
						$(this).removeClass('error');
						$(this).removeClass('error_field');
						$(this).removeClass('error_label');
					});

					form.find('.this_errors').html('');
					form.find('.this_error_wrapper').show();
					form.find('.this_errors').show();

					// $('.this_errors').html('');
					// $('.this_error_wrapper').show();

					var errorsHtml = '<ul>';
					$.each( errors, function( key, value )
					{
						if(value!='')
						{
							errorsHtml += '<li> <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> ' + value[0] + '</li>'; //showing only the first error.
						}

						$('label[for="'+key+'"]').addClass('error_label error');
						$('#'+key).addClass('error_field error');
			        });

			        errorsHtml += '</ul>';

			        console.log(errorsHtml);

			       	form.find('.this_errors').append(errorsHtml);
			        form.find('.this_modal_submit').removeAttr('disabled');
			        form.find('[type="submit"]').removeAttr('disabled');
			        // $('.this_errors').append(errorsHtml);
			        // $('.this_modal_submit').removeAttr('disabled');
				},
				success: function(data)
				{
					//form.find('input[type="submit"]').prop('disabled',true);
					form.find('.error').each(function(){
						$(this).removeClass('error');
						$(this).removeClass('error_field');
						$(this).removeClass('error_label');
					});

					$('.this_error_wrapper').hide();
					processIdentifier(form.data('process'), data);
					form.find('.this_modal_submit').removeAttr('disabled');
					form.find('[type="submit"]').removeAttr('disabled');
				}
			});
		}
   	});

	//exclusive for profile image upload
	$(document).on('submit','#profileImageForm', function(e)
	{
		e.preventDefault();

		var uploadButtonIcon = $('#uploadImageIcon');

		var form = $(this);
		var url = form.attr('action');
		var formData = new FormData(this);

		uploadButtonIcon.attr('disabled','true');
		uploadButtonIcon.removeClass().addClass('fa fa-spin fa-spinner');

		$.ajax({
			type: 'POST',
			data: formData,
			url: url,
			cache:false,
			contentType: false,
			processData: false,
			error: function(data)
			{

				var errors = data.responseJSON;
				console.log(errors);

				var errorContainer = $('#profileImageErrorContainer');

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

				uploadButtonIcon.attr('disabled','false');
				uploadButtonIcon.removeClass().addClass('fa fa-save fa-fw fa-lg');

				alert(data.message);
			},
			success: function(data)
			{
				//remove all content of the error container
				$('#profileImageErrorContainer').empty();

				uploadButtonIcon.attr('disabled','false');
				uploadButtonIcon.removeClass().addClass('fa fa-save fa-fw fa-lg');

				alert(data.message);
			}
		});
	});

	$(document).on('submit','.form_with_file',function(e)
    {
		e.preventDefault();
		var form = $(this);
    	var url = form.attr('action');
    	var formData = new FormData(this);
    	var confirmation = true;

    	var submitBtn = $('.form_with_file').find('.this_modal_submit');

        console.log(submitBtn.html());

    	if($(this).data('confirmation') != '')
		{
			confirmation = confirm(form.data('confirmation'));
		}
		
		if(confirmation === true) 
		{
			submitBtn.attr('disabled','true');
			submitBtn.html('<i class="fa fa-spin fa-spinner"></i>');
			//submitBtn.html('<span class="glyphicon glyphicon-refresh gly-spin"></span>');
			$.ajax({
				type: 'POST',
				data: formData,
				url: url,
				cache:false,
	            contentType: false,
	            processData: false,
				error: function(data)
				{

					var errors = data.responseJSON;

					console.log(errors);

					$('.this_errors').html('');
					$('.this_error_wrapper').show();
					$('.this_errors').show();

					var errorsHtml = '<ul>';

					$.each( errors, function( key, value ) {
			            errorsHtml += '<li> <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> ' + value[0] + '</li>'; //showing only the first error.
			            form.find('label[for="'+key+'"]').addClass('error_label error');
			            form.find('#'+key).addClass('error_field error');
			        });

			        errorsHtml += '</ul>';

			        $('.this_errors').append(errorsHtml);

			        submitBtn.html('Save');
			        submitBtn.removeAttr('disabled');
				},
				success: function(data)
				{
					//console.log(data);
					form.find('.error').each(function(){
						$(this).removeClass('error');
						$(this).removeClass('error_field');
						$(this).removeClass('error_label');
					});

					$('.this_error_wrapper').hide();
					submitBtn.html('Saved!');
					submitBtn.removeAttr('disabled');
					processIdentifier(form.data('process'), data);
				}
			});
		}
	});

    $(document).on('submit','.form_with_file_not_modal',function(e)
    {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = new FormData(this);
        var confirmation = true;

        var submitBtn = $('.form_with_file_not_modal').find('.this_modal_submit');
        var originalSubmitButtonText = submitBtn.html();

        if($(this).data('confirmation') != '')
        {
            confirmation = confirm(form.data('confirmation'));
        }

        if(confirmation === true)
        {
            submitBtn.attr('disabled','true');
            submitBtn.html('<i class="fa fa-spin fa-spinner"></i>');
            //submitBtn.html('<span class="glyphicon glyphicon-refresh gly-spin"></span>');
            $.ajax({
                type: 'POST',
                data: formData,
                url: url,
                cache:false,
                contentType: false,
                processData: false,
                error: function(data)
                {

                    var errors = data.responseJSON;

                    console.log(data);

                    $('.this_error_wrapper').show();
                    $('.this_errors').html('');
                    $('.this_errors').show();
                    var errorsHtml = '<ul>';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li> <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> ' + value[0] + '</li>'; //showing only the first error.
                        form.find('label[for="'+key+'"]').addClass('error_label error');
                        form.find('#'+key).addClass('error_field error');
                    });

                    errorsHtml += '</ul>';
                    $('.this_errors').append(errorsHtml);

                    submitBtn.html(originalSubmitButtonText);
                    submitBtn.removeAttr('disabled');
                },
                success: function(data)
                {
                    //console.log(data);
                    /*
                    form.find('.error').each(function(){
                        $(this).removeClass('error');
                        $(this).removeClass('error_field');
                        $(this).removeClass('error_label');
                    });
                    */

                    //clear the file field
                    form.find('#file').val('');

                    $('.this_error_wrapper').hide();
                    submitBtn.html(originalSubmitButtonText);
                    submitBtn.removeAttr('disabled');
                    processIdentifier(form.data('process'), data);
                }
            });
        }
    });

	// /* For Bug Report */
	// $('#bugReportDropdown a').hover(function() {
	// 	if($('#bugReportDropdown').hasClass('open') == false) {
	// 		// console.log('Hover & Not');
	// 		$('[data-toggle="popover"]#bugReportDropdown').popover('show'); 
	// 	}else {
	// 		// console.log('Hover & Visible');
	// 		$('[data-toggle="popover"]#bugReportDropdown').popover('hide'); 
	// 	}
	// });

	// $( "#bugReportDropdown a" ).mouseleave(function() {
	// 	// console.log('Leave');
	//   $('[data-toggle="popover"]#bugReportDropdown').popover('hide'); 
	// });

	// $('#bugReportDropdown').on('show.bs.dropdown', function () {
	// 	// console.log('Open');
	// 	$('[data-toggle="popover"]#bugReportDropdown').popover('destroy');
	// })

	$('#attachBugFilesBtn').click(function() {
		$('#bug_evidence_files').trigger('click');
	});

	$('#bugReportSubmitBtn').click(function() {
		var form = $('#report_bug_form');
		var summary = form.find('#bug_summary');
		var description = form.find('#bug_description');
		var error_counter = 0;

		if(summary.val() == '') {
			summary.addClass('error_field error');
			error_counter++;
		}else {
			summary.removeClass('error_field error');
		}

		if(description.val() == '') {
			description.addClass('error_field error');
			error_counter++;
		}else {
			description.removeClass('error_field error');
		}

		$('.bug-file-name').parent('li').css('background-color','#fff');

		if(bugFiles.length > 0) {
			$(bugFiles).each(function(i, file) {
				if(bugFileSizeChecker[i] > 250000) {
					if(file.length > 20) name = file.substring(0,20) + '...';
		  			else name = file;
		  			$('.bug-file-name:contains("'+name+'")').parent('li').css('background-color','#dfb8b8');
					error_counter++;
				}				
			});
		}

		if(error_counter == 0) {
			$('#report_bug_form').submit();
		}
	});

	$(document).on('change','#bug_evidence_files',function()
   	{	
		// $(this)[0].files[0].name; 
		if($('#bugFileList li').length == 1 && $('#bugFileList li').is(':empty')) {
			$('#bugFileList').empty();
		}

		var files = $(this)[0].files;
		$(files).each(function(i, file) {
			// console.log(file.size);
		  	var size = file.size * .001;
		  	if(file.name.length > 20) name = file.name.substring(0,20) + '...';
		  	else name = file.name;
		  	file_size = '('+size.toFixed(2)+'K)';
		  	var file_item = '<li class="list-group-item">';
		  		file_item += '<span class="bug-file-name">'+name+'</span>';
		  		if(file_size.length + 1 + name.length > 20) file_item += '<br>';
		  		file_item += ' <span class="bug-file-size">'+file_size+'</span>';
		  		file_item += '<button type="button" class="close removeBugFileBtn" data-name="'+file.name+'"><span aria-hidden="true">&times;</span></button></li>';
		  	$('#bugFileList').append(file_item);

		  	bugFiles.push(file.name);
		  	bugFileSizeChecker.push(file.size);
		});
		// console.log(bugFiles);
		// console.log(JSON.stringify( bugFiles ));
		$('#final_list_of_files').val(JSON.stringify( bugFiles ));
		//add another file input for another list of added
		$(this).removeAttr('id');
		$(this).parent('div').append('<input id="bug_evidence_files" class="hidden" multiple="true" name="bug_evidence_files[]" type="file" accept="image/*">');
		
		// console.log($(this).val());
	});

	$(document).on('click','.removeBugFileBtn',function()
   	{
		$(this).parent('li').remove();

		if($('#bugFileList li').length == 0 && $('#bugFileList').is(':empty')) {
			$('#bugFileList').append('<li class="list-group-item" style="height:34px"></li>');
		}

		var index = bugFiles.indexOf($(this).data('name'));
		if (index > -1) {
		    bugFiles.splice(index, 1);
		    bugFileSizeChecker.splice(index,1);
		}
		$('#final_list_of_files').val(JSON.stringify( bugFiles ));
   	});

   	$('#bugReportDropdown').on('hide.bs.dropdown', function () {
		var form = $('#report_bug_form');
		var summary = form.find('#bug_summary');
		var description = form.find('#bug_description');
		var final_list_of_files = form.find('#final_list_of_files');

		if(summary.val() == '') {
			summary.removeClass('error_field error');
		}

		if(description.val() == '') {
			description.removeClass('error_field error');
		}

		if(final_list_of_files.val() == '[]') {
			final_list_of_files.val('');
			$('[name="bug_evidence_files[]"]').val('');
			$('[name="bug_evidence_files[]"]:not([id])').remove();
		}
	});

	$('.modal').on('hidden.bs.modal', function (e) {
	    if($('.modal').hasClass('in')) {
	    $('body').addClass('modal-open');
	    }    
	});
});

// $(document).ajaxError(function(event, jqxhr, settings, exception) {

//     // if (exception == 'Unauthorized') {
//     //     // Prompt user if they'd like to be redirected to the login page
//     //     bootbox.confirm("Your session has expired. Would you like to be redirected to the login page?", function(result) {
//     //         if (result) {
//     //             window.location = '/login';
//     //         }
//     //     });
//     // }
//     console.log('KARLA');
//     console.log(event);
//     console.log(jqxhr);
//     console.log(settings);
//     console.log(exception);
//     var return_response = jqxhr.responseText;
//     if(return_response.search('TokenMismatchException') >= 0) {
//     	// location.reload();
//     }
// });