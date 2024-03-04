@extends('app')

@section('title')
	Affiliates
@stop

@section('header')

<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<!-- Select2 CSS -->
<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/admin/history.min.css') }}" rel="stylesheet">
@stop

@section('content')
@if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_affiliate')))
	<button id="addAffBtn" class="btn btn-primary addBtn" type="button">Add Affiliate</button>
@endif

<div class="modal fade" id="AffFormModal" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
  	<?php
  		$attributes = [
  			'url' 					=> 'add_affiliate',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> '',
  			'data-process' 			=> 'add_affiliate'
  		];
  	?>
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">Add Affiliate</h4>
		</div>

		<div class="modal-body">
			<ul id="tab-with-history" class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#tab-info" id="info-tab">Info</a></li>
				<li><a data-toggle="tab" href="#aff-history">History</a></li>
			</ul>
			<div class="tab-content" style="padding-top: 15px;">
				<div id="tab-info" class="tab-pane fade in active">
			  	{!! Form::open($attributes) !!}
					{!! Form::hidden('this_id', '',array('id' => 'this_id')) !!}
					<div class="row">
						<div class="col-md-12 form-div">
							{!! Form::label('company','Company Name') !!}
							{!! Form::text('company','',
								array('class' => 'form-control this_field', 'required' => 'true')) !!}
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 form-div">
							{!! Form::label('website','Website URL') !!}
							{!! Form::text('website','',
								array('class' => 'form-control this_field', 'required' => 'true')) !!}
						</div>
						<div class="col-md-6 form-div">
							{!! Form::label('type','Type') !!}
							{!! Form::select('type', [null=>''] + $types, '',
								array('class' => 'form-control this_field', 'required' => 'true') ) !!}
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12 form-div">
									{!! Form::label('phone','Phone #') !!}
									{!! Form::text('phone','',
										array('class' => 'form-control this_field', 'required' => 'true')) !!}
								</div>
								<div class="col-md-12 form-div">
									{!! Form::label('state','State') !!}
									{!! Form::select('state', $states, '',
										array('class' => 'form-control state_select this_field search-select', 'required' => 'true', 'style' => 'width: 100%')) !!}
								</div>
								<div class="col-md-12 state_zip_wrapper">
									<div class="row">
										<div class="col-md-12 form-div">
											{!! Form::label('city','City') !!}
											{!! Form::select('city', $states, '',
												array('class' => 'form-control this_field search-select', 'style' => 'width: 100%')) !!}
										</div>
										<div class="col-md-12 form-div">
											{!! Form::label('zip','ZIP') !!}
											{!! Form::text('zip','',
												array('id' => 'zip','class' => 'form-control this_field')) !!}
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12 form-div">
									{!! Form::label('address','Address') !!}
									{!! Form::textarea('address','',array('class' => 'form-control this_field', 'rows' => '1')) !!}
								</div>
								<div class="col-md-12 form-div">
									{!! Form::label('description','Description') !!}
									{!! Form::textarea('description','',array('class' => 'form-control this_field ggwp', 'rows' => '2')) !!}
								</div>
								<div class="col-md-12 form-div">
									{!! Form::label('status','Status') !!}
									<div>
										<div class="radio-inline">
											<label>
												{!! Form::radio('status', 0, false, array('data-label' => 'Inactive','class' => 'this_field')) !!}
												Inactive
											</label>
										</div>
										<div class="radio-inline">
											<label>
												{!! Form::radio('status', 1, true, array('data-label' => 'Active','class' => 'this_field')) !!}
												Active
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- <label for="checkbox[]" class="col-sm-3 control-label">Check</label>
					<input type="checkbox" class="this_field" id="checkbox" name="checkbox[]" data-label="Active" value="1" checked> Active
					<input type="checkbox" class="this_field" id="checkbox" name="checkbox[]" data-label="Inactive" value="0"> Inactive -->
					<div class="form-group this_error_wrapper">
						<div class="alert alert-danger this_errors">

						</div>
					</div>

					<div class="modal-footer row" style="padding-bottom: 0;">
						<button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
						{!! Form::submit('Save', array('class' => 'btn btn-primary this_modal_submit')) !!}
					</div>
      			{!! Form::close() !!}
				</div>

				<div id="aff-history" class="table-history tab-pane fade" style="position: relative">
					<input type="hidden" id="section_id" value="2">
					<input type="hidden" id="reference_id" value="">
					@include('partials.userActionHistory')
					<div class="modal-footer row" style="padding-bottom: 0;">
						<button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
	  	</div>
	</div>
  </div>
</div>
<div class="row">
	<br>
	<div class="col-xs-12 container-fluid">
		<table id="affiliate-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive responsive-data-table">
			<thead>
				<tr>
					<th>ID</th>
					<th>Company</th>
					<th>Type</th>
					<th>Website Url</th>
					<th>Contact Details</th>
					<th>Status</th>
					<th class="col-actions">Actions</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
				<tr>
					<th>ID</th>
					<th>Company</th>
					<th>Type</th>
					<th>Website Url</th>
					<th>Contact Details</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('js/admin/affiliates.min.js') }}"></script>
<script src="{{ asset('js/admin/history.min.js') }}"></script>
@stop
