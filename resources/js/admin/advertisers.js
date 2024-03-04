/***
 * Get all unassigned or available users
 *
 * @param id
 * @param callback
 */
function getAvailableUsers(id,callback)
{
	var the_url = $('#baseUrl').val() + '/get_available_users_as_advertiser';
	$.ajax({
		type: 'POST',
		data: {
			'id':id
		},
		url: the_url,
		success: function(data){
			callback(data);
		}
	});

}

$(document).ready(function()
{
	$('.search-select').select2({
		theme: 'bootstrap',
		dropdownParent: $("#AdvFormModal")
	});

	$('#website_url').change(function()
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

	var dataAdvertisersURL = $('#baseUrl').html() + '/advertisers';
	$('#advertiser-table').DataTable({
		'processing': true,
		'serverSide': true,
		"columns": [
			null,
			null,
			null,
			{ "orderable": false },
			null,
			{ "orderable": false }
		],
		"order": [[ 0, "desc" ]],
		'ajax':{
			url: dataAdvertisersURL, // json datasource
			type: 'post',  // method  , by default get
			error: function(){  // error handling

			}
		},
		lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
	});

	$('#addAdvBtn').click(function()
	{

        var this_modal = $('#AdvFormModal');
        var formActionURL = $('#baseUrl').html() + '/admin/advertiser/store';

		$('#tab-with-history').hide();

        this_modal.find('.this_form').attr('action',formActionURL);
		this_modal.find('.this_form').data('process','add_advertiser');
        this_modal.find('.this_form').attr('data-process', 'add_advertiser');
		this_modal.find('.this_form').data('confirmation','');
        this_modal.find('.this_form').attr('data-confirmation', '');
        this_modal.find('.modal-title').html('Add Advertiser');

        //clean all objects inside the form
        //$('.this_field').val(''); //clear all fields that has this_field class
        $('#adv_form input:text').val(''); //Clear all text field within the form
		$('#adv_form textarea').val(''); //Clear all text area fields within the form
        $('#adv_form select').prop('selectedIndex',0); //clear all value selected in all select list

		$('#state').val('').trigger('change');

        //show the form
        $('#AdvFormModal').modal('show');

		//reset userlist to only display the available users that can be set as advertiser
		getAvailableUsers(0,function(data) {
			//console.log(data);
			$('#id option').remove();
			$('#id').append('<option value=""> </option>');
			$.each(data,function(key, value)
			{
			    $('#id').append('<option value="' + key + '">' + value + '</option>');
			});
		})

        //hide the error block
        $('.this_error_wrapper').hide();

	});

	$(document).on('click','.editAdvertiser',function()
	{
		var this_modal = $('#AdvFormModal');
		var id = $(this).data('id');
		var url = $('#baseUrl').html() + '/admin/advertiser/update/'+id;

		$('#tab-with-history').show();

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','edit_advertiser');
		this_modal.find('.this_form').attr('data-process', 'edit_advertiser');
		this_modal.find('.this_form').data('confirmation','Are you sure you want to edit this attribute?');
		this_modal.find('.this_form').attr('data-confirmation', '')
		this_modal.find('.modal-title').html('Edit Advertiser');
		this_modal.find('#this_id').val(id);
		this_modal.find('.table-history #reference_id').val(id);

		var this_adv = '#adv-' + id + '-';

		$('#company').val( $(this_adv+'comp').html() );
		$('#website_url').val( $(this_adv+'web').html() );
		$('#phone').val( $(this_adv+'phn').html() );
		$('#address').val( $(this_adv+'add').html() );
		$('.state_zip_wrapper').show();

        var currentCity = $(this_adv+'cty').html();
        var currentZip = $(this_adv+'zip').html();

        $('#city').data('current_city',currentCity);
        $('#state').val($(this_adv+'ste').html()).trigger('change');
        $('#zip').val(currentZip);
		//$('#zip').val( $(this_adv+'zip').html() );

        console.log(currentCity);
        console.log(currentZip);

		/*
		getAvailableUsers($(this_adv+'user').data('id'),function(data) {
			$('#id option').remove();
			$('#id').append('<option value=""> </option>');
			$.each(data,function(key, value)
			{
			    $('#id').append('<option value="' + key + '">' + value + '</option>');
			});
			$('#id').val( $(this_adv+'user').data('id') );
		});
		*/
        /*
		getCitiesByState($('#state :selected').html(), function(data)
		{
			$('#city option').remove();
			$('#city').attr('required','true');
			$('#zip').attr('required','true');
			$('#city').append('<option value=></option>');

			$.each(data,function(key, value)
			{
			    $('#city').append('<option value="' + value + '">' + value + '</option>');
			});

			$('.state_zip_wrapper').show();
			$('#address').attr('rows',2);

			$('#city').val($(this_adv+'cty').html());
			$('#zip').val($(this_adv+'zip').html());
		});
        */
		$('#description').val($(this_adv+'desc').val());
		$('input[name="status"][value="'+$(this_adv+'stat').data('status')+'"]').prop('checked', true);
		this_modal.modal('show');
	});


	$(document).on('click','.deleteAdvertiser',function()
	{
		var this_advertiser = $(this);
		var id = this_advertiser.data('id');
		var the_url = $('#baseUrl').val() + '/admin/advertiser/destroy';
		var confirmation = true;

		confirmation = confirm('Are you sure you want to delete '+ $('#adv-'+id+'-comp').html() + '?');

		if(confirmation === true)
		{
			$.ajax({
				type: 'POST',
				data: {
					'id': id
				},
				url: the_url,
				success: function(){
					var table = $('#advertiser-table').DataTable();
					table.row(this_advertiser.parents('tr')).remove().draw();
					//$('#adv-'+id+'-comp').parent().fadeOut();
					//$('#adv-'+id+'-comp').parent().remove();
				}
			});
		}
	});

	$('#AdvFormModal').on('hide.bs.modal', function (event)
	{
	  var form = $(this).find('.this_form');
	  form.find('.this_field').each(function()
	  {
	  	if($(this).attr('name') == 'status') {
	  		$('input[name="status"][value="1"]').prop('checked', true);
	  	}else {
	  		$(this).val('');
	  	}
	  });

	  form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});
	  $('.state_zip_wrapper').hide();
	  $('.this_error_wrapper').hide();
	})

	$('#close-AddAffCol').click(function()
	{
		$('#AffFormCol').collapse('hide');
		$('.this_field').val('');
		$('#addAffColBtn').fadeIn( "slow");
	});

	$(document).on('change','.state_select',function(e)
	{
		//$('#city').val('');
		//$('#zip').val('');

		if($(this).val() != '')
		{
            //placeholder while ajax is processing cities to be added
            $('#city option').remove();
            $('#city').append('<option value=>Processing cities for the state...</option>');

			getCitiesByState($('#state option:selected').html(), function(data)
            {
				$('#city option').remove();

				$('#city').attr('required','true');
				$('#zip').attr('required','true');

				$('#city').append('<option value=></option>');

				$.each(data,function(key, value)
				{
				    $('#city').append('<option value="' + value + '">' + value + '</option>');
				});

				$('.state_zip_wrapper').fadeIn('slow');
				$('#address').attr('rows',4);

                //set if there is pre defined city
                var currentCity = $('#city').data('current_city');
                $('#city').val(currentCity).trigger('change');
			});

		}
		else
		{
			$('#city').attr('required','false');
			$('#zip').attr('required','false');
			$('.state_zip_wrapper').fadeOut('fast');
			$('#address').attr('rows',1);
		}
	});


	$('#AffFormModal').on('hide.bs.modal', function (event)
	{
	    var form = $(this).find('.this_form');

	    form.find('.this_field').each(function()
	    {
	  	    if($(this).attr('name') == 'status')
            {
	  		    $('input[name="status"][value="1"]').prop('checked', true);
	  	    }
            else
            {
	  		    $(this).val('');
	  	    }
	    });

	    form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

	    $('.state_zip_wrapper').hide();
	    $('.this_error_wrapper').hide();
	});
});
