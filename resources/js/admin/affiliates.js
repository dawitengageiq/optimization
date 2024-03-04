/***
 * Get all unassigned or available users
 *
 * @param id
 * @param callback
 */
function getAvailableUsers(id,callback)
{
	var the_url = $('#baseUrl').val() + '/get_available_users';
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
		dropdownParent: $("#AffFormModal")
	});

	$('#website').change(function()
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

	var dataAffiliatesURL = $('#baseUrl').html() + '/affiliates';
	$('#affiliate-table').DataTable({
		'processing': true,
		'serverSide': true,
		"columns": [
			null,
			null,
			null,
			null,
			{ "orderable": false },
			null,
			{ "orderable": false }
		],
		"order": [[ 0, "desc" ]],
		columnDefs: [
            { width: '3%', targets: 0 },
            // { width: '10%', targets: 2 },
            { width: '15%', targets: [1,3] },
            { width: '20%', targets: 4 },
            { width: '7%', targets: 5 },
            { width: '10%', targets: 6 },
        ],
		'ajax':{
			url:dataAffiliatesURL, // json datasource
			type: 'post',  // method  , by default get
			error: function(){  // error handling

			}
		},
		lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
	});

	$('#addAffBtn').click(function()
	{
		var this_modal = $('#AffFormModal');
		var url = $('#baseUrl').html() + '/add_affiliate';

		$('#tab-with-history').hide();

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','add_affiliate');
		this_modal.find('.this_form').attr('data-process', 'add_affiliate');
		this_modal.find('.this_form').data('confirmation','');
		this_modal.find('.this_form').attr('data-confirmation', '');
		this_modal.find('.modal-title').html('Add Affiliate');
		this_modal.find('#this_id').val('');

		$('#user').attr('required','required');
		$('#userForAffiliate').show();
		$('#website').val('http://');
		$('#state').val('').trigger('change');

		getAvailableUsers(null,function(data) {
			$('#user option').remove();
			$('#user').append('<option></option>');
			$.each(data,function(key, value)
			{
			    $('#user').append('<option value="' + key + '">' + value + '</option>');
			});
		})

		this_modal.modal('show');
	});

	/***
	 * onclick listener for editing affiliate
	 */
	$(document).on('click','.editAffiliate',function()
	{
		var this_modal = $('#AffFormModal');
		var id = $(this).data('id');
		var url = $('#baseUrl').html() + '/edit_affiliate';

		$('#tab-with-history').show();

		this_modal.find('.this_form').attr('action',url);
		this_modal.find('.this_form').data('process','edit_affiliate');
		this_modal.find('.this_form').attr('data-process', 'edit_affiliate');
		this_modal.find('.this_form').data('confirmation','Are you sure you want to edit this attribute?');
		this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to edit this attribute?');
		this_modal.find('.modal-title').html('Edit Affiliate');
		this_modal.find('#this_id').val(id);		
		this_modal.find('.table-history #reference_id').val(id);

		var this_aff = '#aff-' + id + '-';
		$('#company').val( $(this_aff+'comp').html());
		$('#type').val( $(this_aff+'type').data('type'));
		$('#website').val( $(this_aff+'web').html());
		$('#phone').val( $(this_aff+'phn').html());
		$('#address').val( $(this_aff+'add').html());
		$('.state_zip_wrapper').show();

        var currentCity = $(this_aff+'cty').html();
        var currentZip = $(this_aff+'zip').html();

        //load the current city as data so that when trigger is executed append and select the current city after filling up of city
        $('#city').data('current_city',currentCity);
        $('#state').val($(this_aff+'ste').html()).trigger('change');
        $('#zip').val(currentZip);

        //clear the city
        //$('#city option').remove();
        console.log(currentCity);
        console.log(currentZip);

        /*
		getCitiesByState(

			$('#state :selected').html(), function(data) {

				$('#city').attr('required','true');
				$('#zip').attr('required','true');

				//$('#city').append('<option value=></option>');
                //append the current city immediately
                //$('#city').append('<option value="'+currentCity+'" selected="selected">'+currentCity+'</option>');

				$.each(data,function(key, value)
				{
					$('#city').append('<option value="' + value + '">' + value + '</option>');
				});

				$('.state_zip_wrapper').show();
				$('#address').attr('rows',2);

                //$('#city').val(currentCity).trigger('change');
				//$('#zip').val(currentZip).trigger('change');
			}
		);
        */

		$('#description').val($(this_aff+'desc').val());
		$('input[name="status"][value="'+$(this_aff+'stat').data('status')+'"]').prop('checked', true);
		this_modal.modal('show');

	});

	/***
	 * onclick listener for deleting affiliate
	 */
	$(document).on('click','.deleteAffiliate',function()
	{
		var this_affiliate = $(this);
		var id = $(this).data('id');
		var the_url = $('#baseUrl').val() + '/delete_affiliate';
		var confirmation = true;

		confirmation = confirm('Are you sure you want to delete '+ $('#aff-'+id+'-comp').html() + '?');

		if(confirmation === true)
		{
			$.ajax({
				type: 'POST',
				data: {
					'id':id
				},
				url: the_url,
				success: function(){
					//$('#aff-'+id+'-comp').parent().remove();
					var table = $('#affiliate-table').DataTable();
					table.row(this_affiliate.parents('tr')).remove().draw();
				}
			});
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

	    form.find('.error').each(function()
        {
            $(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

	    $('.state_zip_wrapper').hide();
	    $('.this_error_wrapper').hide();
	});

	$('#close-AddAffCol').click(function()
	{
		$('#AffFormCol').collapse('hide');
		$('.this_field').val('');
		$('#addAffColBtn').fadeIn( "slow");
	});

    /***
     * select listener for editing affiliate
     */
    $(document).on('change','.state_select',function(e)
    {
        //$('#city').val('').trigger('change');
        //$('#zip').val('').trigger('change');

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
                $('#address').attr('rows',2);

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

});
