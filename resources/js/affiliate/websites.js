$(document).ready(function() {

    $.ajaxSetup({
       headers: {
           'X-CSRF-Token': $('meta[name="_token"]').attr('content')
       }
    });

    var websiteID = null;

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
					
					websiteID = data.id;
					console.log('SUCCESS ' + websiteID);
					var affiliateWebsiteTable = $('#websites-table').DataTable();
					affiliateWebsiteTable.draw();

					$('#websiteModal').modal('hide');

					$('.this_modal_submit').removeAttr('disabled');
				}
			});
		}
   	});

	var affiliateWebsiteTable = $('#websites-table').DataTable({
	    responsive: true,
	    autoWidth: false,
	    'processing': true,
		'serverSide': true,
	    "order": [[ 0, "desc" ]],
	    'columns': [
			null,
			null,
			{ 'orderable': false },
			null,
			null,
			{ 'orderable': false },
		],
	    'ajax':{
			url: $('#baseUrl').html() + '/affiliate/get_websites',
			type: 'post', 
			error: function(data) //error handling
			{
				console.log(data);
			},
			"dataSrc": function ( json ) {
	    		return json.data;
	    	}
		},
		"drawCallback": function( settings ) {
			console.log(websiteID);
	       if(websiteID != null) {
	       	$('#afWeb-'+websiteID+'-name').parent().parent().css('background-color','rgba(0, 188, 212, 0.16)');
	       	setTimeout(function(){ 
	       		$('#afWeb-'+websiteID+'-name').parent().parent().css('background-color','');
	       		websiteID = null;
	       	}, 2000);

	       }
	    }
	});

	$('#websiteModal').on('hide.bs.modal', function () {
	  var modal = $('#websiteModal'),
		form = modal.find('form'),
		pre_btn = $('#websiteModalPreSubmit'),
		submit_btn = $('#websiteModalSubmit');

		// websiteID = null;

		$('#this_id').val('');
		$('#website_id').val('');
		$('#name').val('');
		$('#description').val('');
		$('#status').val('');

		pre_btn.show();
		submit_btn.hide();
		form.find('.this_modal_close').html('Close');
		$('#websiteModalConfirm').hide();
	});
});

$(document).on('click','.editAffiliateWebsite', function() 
{
	var id = $(this).data('id'),
		this_button = $(this),
		modal = $('#websiteModal'),
		form = modal.find('form');

		$('#this_id').val(id);
		$('#website_id').val(id);
		$('#name').val($('#afWeb-'+id+'-name').html());
		$('#description').val($('#afWeb-'+id+'-desc').html());
		$('#status').val($('#afWeb-'+id+'-status').html());

	modal.modal('show');
});

$(document).on('click','#websiteModalPreSubmit', function() 
{
	var modal = $('#websiteModal'),
		form = modal.find('form'),
		this_btn = $(this),
		submit_btn = $('#websiteModalSubmit');

	this_btn.hide();
	submit_btn.show();
	form.find('.this_modal_close').html('Cancel');
	$('#websiteModalConfirm').show();
	
});
