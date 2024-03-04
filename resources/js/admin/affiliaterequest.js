var updatedActiveTable = 0, updatedRejectTable = 0, updatedDeactivatedTable = 0;

function populateAffiliateRequestDataTable(table_name, status, remove_column) {
	var dataApplyToRunURL = $('#baseUrl').html() + '/apply_to_run';
	return $(table_name).DataTable({
		'processing': true,
		'serverSide': true,
		"columns": [
			null,
			null,
			null,
			null,
			{ "orderable": false }
		],
		'ajax':{
			url:dataApplyToRunURL, // json datasource
			type: 'post',  // method  , by default get
			data: {
				status : status
			},
			error: function(){  // error handling

			}
		},
		"columnDefs": [
		{
            "targets": [ remove_column ],
            "visible": false
        }],
		lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
	});
}

function processIdentifier(process, data)
{
	switch(process)
	{
		case 'approve_affiliate_campaign_request':
			approveAffiliateApplyToRunRequest(data);
			break;
		case 'reject_affiliate_campaign_request':
			rejectAffiliateApplyToRunRequest(data);
			break;
		case 'revert_affiliate_campaign_request':
			revertAffiliateApplyToRunRequest(data);
			break;
        default:
			alert('unknown process!');
			break;
	}
}

function approveAffiliateApplyToRunRequest(request_id) {
	var pendingTable = $('#pendingAffiliateRequests-table').DataTable();
	pendingTable.row($('.approveRequestBtn[data-id="'+request_id+'"]').parents('tr')).remove().draw();
	updatedActiveTable = 1;
	$('#confirmationModal').modal('hide');
}

function rejectAffiliateApplyToRunRequest(request_id) {
	var pendingTable = $('#pendingAffiliateRequests-table').DataTable();
	pendingTable.row($('.rejectRequestBtn[data-id="'+request_id+'"]').parents('tr')).remove().draw();
	updatedRejectTable = 1;
	$('#confirmationModal').modal('hide');
}

function revertAffiliateApplyToRunRequest(request_id) {
	console.log(request_id);
	var rejectedTable = $('#rejectedAffiliateRequests-table').DataTable();
	rejectedTable.row($('.revertRequestBtn[data-id="'+request_id+'"]').parents('tr')).remove().draw();
	var pendingTable = $('#pendingAffiliateRequests-table').DataTable();
	pendingTable.ajax.reload();
	$('#confirmationModal').modal('hide');
}

$(document).ready(function() {

	// var dataApplyToRunURL = $('#baseUrl').html() + '/apply_to_run';
	var pendingTable = populateAffiliateRequestDataTable('#pendingAffiliateRequests-table', 2, 0);
	var activeTable, rejectedTable, deactivatedTable;
	var baseURL = $('#baseUrl').html();

	/* Open Active Tab */
	$('[href="#active-tab"]').click(function(){
		var activeTableName = '#activeAffiliateRequests-table';
		if ( ! $.fn.DataTable.isDataTable( activeTableName ) ) {
			$('#active-loading').hide();
			$('#active-data').show();
		  	activeTable = populateAffiliateRequestDataTable(activeTableName, 1, -1);
		}

		if(updatedActiveTable != 0) {
			console.log('Active');
			updatedActiveTable = 0;
			activeTable.ajax.reload();
			// activeTable.fnDraw();
		}
	});

	/* Open Rejected Tab */
	$('[href="#rejected-tab"]').click(function(){
		var rejectedTableName = '#rejectedAffiliateRequests-table';
		if ( ! $.fn.DataTable.isDataTable( rejectedTableName ) ) {
			$('#rejected-loading').hide();
			$('#rejected-data').show();
		  	rejectedTable = populateAffiliateRequestDataTable(rejectedTableName, 3, null);
		}

		if(updatedRejectTable != 0) {
			console.log('Reject');
			updatedRejectTable = 0;
			rejectedTable.ajax.reload();
			// activeTable.fnDraw();
		}
	});

	/* Open Deactivated Tab */
	$('[href="#deactivated-tab"]').click(function(){
		var deactivatedTableName = '#deactivatedAffiliateRequests-table';
		if ( ! $.fn.DataTable.isDataTable( deactivatedTableName ) ) {
			$('#deactivated-loading').hide();
			$('#deactivated-data').show();
		  	deactivatedTable = populateAffiliateRequestDataTable(deactivatedTableName, 0, -1);
		}else {
			console.log('Rejected');
			// rejectedTable.ajax.reload();
			// activeTable.fnDraw();
			// var column = pendingTable.column( 0 );
			// column.visible( ! column.visible() );
		}
	});

	/* Submit Confirmation Modal */
	$(document).on('submit','.confirmation-form',function(e) 
	{
		e.preventDefault();
		var form = $(this);
		var the_data = form.serialize();
    	var url = form.attr('action');

		$.ajax({
			type: 'POST',
			data: the_data,
			url: url,
			success: function(data)
			{
				console.log('Success');
				processIdentifier(form.data('process'), data);
			}
		});
	});

	/* Close Confirmation Modal */
	$('#confirmationModal').on('hidden.bs.modal', function (e) {
	  $('#confirmationHeader').removeClass('success-modal-header primary-modal-header danger-modal-header');
	  $('#confirmationButton').removeClass('btn-success btn-danger');
	})

	/* Approve Pending Request Button */
	$(document).on('click','.approveRequestBtn',function() 
	{
		// console.log($('#baseUrl').html())
		var id = $(this).data('id');
		var form = $('.confirmation-form');
		form.attr('action', baseURL+'/approve_affiliate_campaign_request');
		form.attr('data-process', 'approve_affiliate_campaign_request').data('process','approve_affiliate_campaign_request');
		$('#affiliate_campaign_request_id').val(id);
		var description = "You are about to approve <strong>"+ $('#rqst-'+id+'-aff').html() +"'s</strong> request to run <strong>"+ $('#rqst-'+id+'-cmp').html() +".</strong>";
		$('#confirmation-description').html(description);
		$('#confirmationHeader').addClass('success-modal-header');
		$('#confirmationButton').addClass('btn-success');
		$('#confirmationModal').modal('show');
	});

	/* Reject Pending Request Button */
	$(document).on('click','.rejectRequestBtn',function() 
	{
		var id = $(this).data('id');
		var form = $('.confirmation-form');
		form.attr('action', baseURL+'/reject_affiliate_campaign_request');
		form.attr('data-process', 'reject_affiliate_campaign_request').data('process','reject_affiliate_campaign_request');
		$('#affiliate_campaign_request_id').val(id);
		var description = "You are about to reject <strong>"+ $('#rqst-'+id+'-aff').html() +"'s</strong> request to run <strong>"+ $('#rqst-'+id+'-cmp').html() +".</strong>";
		$('#confirmation-description').html(description);
		$('#confirmationHeader').addClass('danger-modal-header');
		$('#confirmationButton').addClass('btn-danger');
		$('#confirmationModal').modal('show');
	});

	/* Revert Rejected Request Button */
	$(document).on('click','.revertRequestBtn',function() 
	{
		// console.log($('#baseUrl').html())
		var id = $(this).data('id');
		var form = $('.confirmation-form');
		form.attr('action', baseURL+'/revert_affiliate_campaign_request');
		form.attr('data-process', 'revert_affiliate_campaign_request').data('process','revert_affiliate_campaign_request');
		$('#affiliate_campaign_request_id').val(id);
		var description = "You are about to revert <strong>"+ $('#rqst-'+id+'-aff').html() +"'s</strong> request to run <strong>"+ $('#rqst-'+id+'-cmp').html() +"</strong> to pending.";
		$('#confirmation-description').html(description);
		$('#confirmationHeader').addClass('danger-modal-header');
		$('#confirmationButton').addClass('btn-danger');
		$('#confirmationModal').modal('show');
	});

	
});