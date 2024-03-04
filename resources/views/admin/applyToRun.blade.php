@extends('app')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<!-- Special CSS -->
<link href="{{ asset('css/admin/affiliaterequest.min.css') }}" rel="stylesheet">
@stop

@section('title') 
    Apply to Run Request
@stop

@section('content')

<div class="row">
	<br>
	<div class="col-xs-12 container-fluid">
		<div id="confirmationModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
		  <div style="width:100%;height:100%;padding-top: 50px;">
		  <div class="modal-dialog modal-sm">
		    <div class="modal-content">
		    	<?php
			  		$attributes = [
			  			'url' 					=> 'approve_affiliate_campaign_request',
			  			'class'					=> 'confirmation-form',
			  			'data-process' 			=> 'approve_affiliate_campaign_request'
			  		];
			  	?>
				{!! Form::open($attributes) !!}
				{!! Form::hidden('id', '',array('id' => 'affiliate_campaign_request_id','class' => 'this_field')) !!}
				<div id="confirmationHeader" class="modal-header">
					<!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
					<i class="fa fa-exclamation-triangle pull-right" aria-hidden="true"></i>
					<!-- <span class="glyphicon glyphicon-exclamation-sign text-right" aria-hidden="true"></span> -->
					<h4 class="modal-title">Confirmation</h4>

				</div>
				<div class="modal-body">
					<p id="confirmation-description"></p>
					<em>Click 'Confirm' to continue.</em>
				</div>
				<div class="modal-footer">
					<button id="confirmationButton" type="submit" class="btn btn-primary">Confirm</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
		    	{!! Form::close() !!}
		    	
		    </div>
		  </div>
		</div>
		</div>

		<div>
		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" class="active"><a href="#pending-tab" aria-controls="pending-tab" role="tab" data-toggle="tab">Pending</a></li>
		    <li role="presentation"><a href="#active-tab" aria-controls="active" role="tab" data-toggle="tab">Active</a></li>
		    <li role="presentation"><a href="#rejected-tab" aria-controls="rejected" role="tab" data-toggle="tab">Rejected</a></li>
		    <li role="presentation"><a href="#deactivated-tab" aria-controls="deactivated" role="tab" data-toggle="tab">Deactivated</a></li>
		  </ul>

		  <!-- Tab panes -->
		  <div class="tab-content" style="margin-top: 10px;">
		    <div role="tabpanel" class="tab-pane active" id="pending-tab">
		    	<table id="pendingAffiliateRequests-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table" style="width:100%">
					<thead>
						<tr>
							<th>Date Updated</th>
							<th>Date Applied</th>
							<th>Affiliate</th>
							<th>Campaign</th>
							<th style="width: 200px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<!-- <tr>
							<td>Jun-30-2016</td>
							<td>ID - Affiliate Name</td>
							<td>Toluna (Pending)</td>
							<td>
								<button class="approveRequestBtn btn btn-success" type="button" data-id="">
									<i class="fa fa-check-circle" aria-hidden="true"></i> Approve
								</button>

								<button class="rejectRequestBtn btn btn-danger" type="button" data-id="">
									<i class="fa fa-times-circle" aria-hidden="true"></i> Reject
								</button>
							</td>
						</tr>
						<tr>
							<td>Jun-30-2016</td>
							<td>ID - Affiliate Name</td>
							<td>Toluna - Active</td>
							<td>
							</td>
						</tr>
						<tr>
							<td>Jun-30-2016</td>
							<td>ID - Affiliate Name</td>
							<td>Toluna - Rejected</td>
							<td>
								<button class="revertRequestBtn btn btn-danger" type="button" data-id="">
									<i class="fa fa-arrow-circle-left" aria-hidden="true"></i> Revert
								</button>
							</td>
						</tr> -->
					</tbody>
					<tfoot>
						<tr>
							<th>Date Updated</th>
							<th>Date Applied</th>
							<th>Affiliate</th>
							<th>Campaign</th>
							<th>Action</th>
						</tr>
					</tfoot>
				</table>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="active-tab">
		    	<div id="active-loading" style="text-align: center; float: none;">
			    	<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
					<span class="sr-only">Loading...</span>
				</div>
				<div id="active-data" style="display:none">
					<table id="activeAffiliateRequests-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table" style="width:100%">
						<thead>
							<tr>
								<th>Date Updated</th>
								<th>Date Applied</th>
								<th>Affiliate</th>
								<th>Campaign</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<th>Date Updated</th>
								<th>Date Applied</th>
								<th>Affiliate</th>
								<th>Campaign</th>
							</tr>
						</tfoot>
					</table>
				</div>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="rejected-tab">
		    	<div id="rejected-loading"  style="text-align: center; float: none;">
			    	<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
					<span class="sr-only">Loading...</span>
				</div>
				<div id="rejected-data" style="display:none">
					<table id="rejectedAffiliateRequests-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table" style="width:100%">
						<thead>
							<tr>
								<th>Date Updated</th>
								<th>Date Applied</th>
								<th>Affiliate</th>
								<th>Campaign</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<th>Date Updated</th>
								<th>Date Applied</th>
								<th>Affiliate</th>
								<th>Campaign</th>
								<th>Action</th>
							</tr>
						</tfoot>
					</table>
				</div>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="deactivated-tab">
		    	<div id="deactivated-loading" style="text-align: center; float: none;">
			    	<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
					<span class="sr-only">Loading...</span>
				</div>
				<div id="deactivated-data" style="display:none">
					<table id="deactivatedAffiliateRequests-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table" style="width:100%">
						<thead>
							<tr>
								<th>Date Updated</th>
								<th>Date Applied</th>
								<th>Affiliate</th>
								<th>Campaign</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<th>Date Updated</th>
								<th>Date Applied</th>
								<th>Affiliate</th>
								<th>Campaign</th>
								<th>Action</th>
							</tr>
						</tfoot>
					</table>
				</div>
		    </div>
		  </div>
		</div>
	</div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/admin/affiliaterequest.min.js') }}"></script>
@stop