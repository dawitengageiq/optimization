@extends('app')

@section('title')
	Campaigns
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ URL::asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ URL::asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<!-- <link href="{{ URL::asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet"> -->
<link href="{{ URL::asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">

<link href="{{ URL::asset('css/jquery-ui.min.css') }}" rel="stylesheet">

<style>
#campaign_sortable .selected {
	background-color: #A9A9A9;
    color: white;
}
</style>
@stop

@section('content')

<div class="row">
	<div class="col-lg-6 col-md-6">
		@if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_campaign')))
			<button id="addCmpBtn" class="btn btn-primary addBtn pull-left" type="button">Add Campaign</button>
		@endif
	</div>

	<div class="col-lg-6 col-md-6">
		<button id="sortCmpBtn" type="button" class="btn btn-default pull-right" data-toggle="modal">
			<span class="glyphicon glyphicon-sort-by-attributes"></span>
		</button>
	</div>
</div>
<br>

<!-- SORT Campaigns -->
<div id="sortCmpModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div>
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Sort Campaigns</h4>
      </div>
      <div class="modal-body">
        <table class="table table-striped" id="campaign-priority-sort">
			<thead>
				<tr>
					<th>Priority</th>
					<th>Name</th>
					<th>Type</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody id="campaign_sortable">
			</tbody>
		</table>
		<div id="current_sorting"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="saveSortedCampaignsBtn" style="display:none">Update</button>
      </div>
    </div>
  </div>
</div>
</div>

<!-- ADD Campaign Modal -->
<div class="modal fade" id="addCmpFormModal" tabindex="-1" role="dialog" aria-labelledby="addCmpFormModal">
<?php
	$attributes = [
		'url' 					=> 'add_campaign',
		'class'					=> 'form_with_file',
		'data-confirmation' 	=> '',
		'data-process' 			=> 'add_campaign',
		'files'					=> true
	];
?>
{!! Form::open($attributes) !!}
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add Campaign</h4>
      </div>
      <div class="modal-body">
      	{!! Form::hidden('this_id', '',array('id' => 'this_id','class' => 'this_field')) !!}
		<div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-12 form-div">
						{!! Form::label('name','Name') !!}
						{!! Form::text('name','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('campaign_type','Campaign Type') !!}
						{!! Form::select('campaign_type', [null=>''] + $campaign_types, '', 
							array('class' => 'form-control this_field', 'required' => 'true') ) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('advertiser','Advertiser') !!}
						{!! Form::select('advertiser', $advertisers, '', 
							array('class' => 'form-control this_field', 'required' => 'true') ) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('lead_type','Lead Cap Type') !!}
						{!! Form::select('lead_type', [null=>''] + $lead_types, '', 
							array('class' => 'form-control this_field leadCapType', 'required' => 'true','data-form' => 'add') ) !!}
					</div>
					<div id="leadCapVal_add" class="col-md-12 form-div">
						{!! Form::label('lead_value','Lead Cap Value') !!}
						{!! Form::text('lead_value','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</div>
					<div id="leadCapVal_add" class="col-md-12 form-div">
						{!! Form::label('default_payout','Default Payout') !!}
						{!! Form::text('default_payout','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</div>
					<div id="leadCapVal_add" class="col-md-12 form-div">
						{!! Form::label('default_received','Default Received') !!}
						{!! Form::text('default_received','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</div>
				</div>
			</div>
			<div class="col-md-6 image_wrapper" data-type="add">
				<div class="row">
					<div class="col-md-12 form-div">
						{!! Form::label('img_type','Image Source') !!}
						<div>
							<div class="radio-inline">
								<label>
									{!! Form::radio('img_type', '1', true,array('class' => 'img_type')) !!}
									Image Upload
								</label>
							</div>
							<div class="radio-inline">
								<label>
									{!! Form::radio('img_type', '2', false,array('class' => 'img_type')) !!}
									Image Url
								</label>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="imgPreview">
							<img src=""/>
						</div>
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('image','Image') !!}
						{!! Form::file('image', array('class' => 'form-control this_field campaign_img','accept' => 'image/*')) !!}
					</div>
					<div class="col-md-12 form-div">
    					{!! Form::label('description','Description') !!}
						{!! Form::textarea('description','',
							array('id' => 'description','class' => 'form-control this_field', 'rows' => '2')) !!}
					</div>
					<div class="col-md-12 form-div">
    					{!! Form::label('notes','Notes') !!}
						{!! Form::textarea('notes','',
							array('id' => 'notes','class' => 'form-control this_field', 'rows' => '2')) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('status','Status') !!}
						<div>
							<div class="radio-inline">
								<label>
									{!! Form::radio('status', 0, true, array('data-label' => 'Inactive','class' => 'this_field')) !!}
									Inactive
								</label>
							</div>
							<div class="radio-inline">
								<label>
									{!! Form::radio('status', 1, false, array('data-label' => 'Private','class' => 'this_field')) !!}
									Private
								</label>
							</div>
							<div class="radio-inline">
								<label>
									{!! Form::radio('status', 2, false, array('data-label' => 'Public','class' => 'this_field')) !!}
									Public
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
                
            </div>
		</div>	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
        
        <button type="submit" class="btn btn-primary this_modal_submit">Save</button>
      </div>
    </div>
  </div>
{!! Form::close() !!}
</div>

<?php

	$canAccessInfo = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_info_tab'));
	$canAccessFilters = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_filters_tab'));
	$canAccessAffiliates = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_affiliates_tab'));
	$canAccessPayouts = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_payouts_tab'));
	$canAccessConfig = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_config_tab'));
	$canAccessLongContent = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_long_content_tab'));
	$canAccessStackContent = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_stack_content_tab'));
	$canAccessHighPayingContent = Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_edit_campaign_high_paying_content_tab'));

	$infoActive = '';
	$filterActive = '';
	$affiliateActive = '';
	$payoutActive = '';
	$configActive = '';
	$longContentActive = '';
	$stackContentActive = '';
	$highPayingContentActive = '';

	//determine the first tab to be active
	if($canAccessInfo)
	{
		$infoActive = 'active';
	}
	else if($canAccessFilters)
	{
		$filterActive = 'active';
	}
	else if($canAccessAffiliates)
	{
		$affiliateActive = 'active';
	}
	else if($canAccessPayouts)
	{
		$payoutActive = 'active';
	}
	else if($canAccessConfig)
	{
		$configActive = 'active';
	}
	else if($canAccessLongContent)
	{
		$longContentActive = 'active';
	}
	else if($canAccessStackContent)
	{
		$stackContentActive = 'active';
	}
	else if($canAccessHighPayingContent)
	{
		$highPayingContentActive = 'active';
	}
?>

<!-- EDIT Campaign Modal -->
<div class="modal fade" id="editCmpFormModal" tabindex="-1" role="dialog" aria-labelledby="editCmpFormModal">
<div>
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Edit Campaign: <strong id="modal-campaign-title"></strong></h4>
      </div>
      <div class="modal-body">
      	<ul class="nav nav-tabs" role="tablist">

			@if($canAccessInfo)
				<li role="presentation" class="{{$infoActive}}">
					<a href="#info_tab" aria-controls="info" role="tab" data-toggle="tab">Info</a>
				</li>
			@endif

			@if($canAccessFilters)
				<li role="presentation" class="{{$filterActive}}">
					<a href="#filter_tab" aria-controls="info" role="tab" data-toggle="tab">Filters</a>
				</li>
			@endif

			@if($canAccessAffiliates)
				<li role="presentation" class="{{$affiliateActive}}">
					<a href="#affiliate_tab" aria-controls="affiliate" role="tab" data-toggle="tab">Affiliates</a>
				</li>
			@endif

			@if($canAccessPayouts)
				<li role="presentation" class="{{$payoutActive}}">
					<a href="#payout_tab" aria-controls="payout" role="tab" data-toggle="tab">Payouts</a>
				</li>
			@endif

			@if($canAccessConfig)
				<li role="presentation" class="{{$configActive}}">
					<a href="#config_tab" aria-controls="config" role="tab" data-toggle="tab">Config</a>
				</li>
			@endif

			@if($canAccessLongContent)
				<li role="presentation" class="{{$longContentActive}}">
					<a href="#long_content_tab" aria-controls="content" role="tab" data-toggle="tab">Long Content</a>
				</li>
			@endif

			@if($canAccessStackContent)
				<li role="presentation" class="{{$stackContentActive}}">
					<a href="#stack_content_tab" aria-controls="content" role="tab" data-toggle="tab">Stack Content</a>
				</li>
			@endif

			@if($canAccessHighPayingContent)
				<li role="presentation" class="{{$highPayingContentActive}}">
					<a href="#high_paying_content_tab" aria-controls="content" role="tab" data-toggle="tab">High Paying Content</a>
				</li>
			@endif
		</ul>

		<div class="tab-content" style="margin-top: 10px;">

			<!-- INFO TAB Start -->
			@if($canAccessInfo)
				@include('campaign.info')
			@endif
			<!-- INFO TAB End -->

			<!-- FILTER TAB Start -->
			@if($canAccessFilters)
				@include('campaign.filter')
			@endif
			<!-- FILTER TAB End -->

			<!-- AFFILIATE TAB Start -->
			@if($canAccessAffiliates)
				@include('campaign.affiliate')
			@endif
			<!-- AFFILIATE TAB End -->

			<!-- PAYOUT TAB Start -->
			@if($canAccessPayouts)
				@include('campaign.payout')
			@endif
			<!-- PAYOUT TAB End -->

			<!-- CONFIG TAB Start -->
			@if($canAccessConfig)
				@include('campaign.config')
			@endif
			<!-- CONFIG TAB End -->

			<!-- LONG CONTENT TAB Start -->
			@if($canAccessLongContent)
				@include('campaign.long_content')
			@endif
			<!-- LONG CONTENT TAB End -->

			<!-- STACK CONTENT TAB Start -->
			@if($canAccessStackContent)
				@include('campaign.stack_content')
			@endif
			<!-- STACK CONTENT TAB End -->

			<!-- HIGH PAYING CONTENT TAB Start -->
			@if($canAccessHighPayingContent)
				@include('campaign.high_paying_content')
			@endif
			<!-- HIGH PAYING CONTENT TAB End -->

		</div>
      </div>
    </div> 
  </div>
</div>
</div>

<div class="row">
	<div class="col-xs-12">
		<table id="campaign-table" data-advertiser="{{ auth()->user()->advertiser->id }}" class="table table-bordered table-striped table-hover table-heading table-datatable">
			<thead>
				<tr>
					<th>Priority</th>
					<th>ID</th>
					<th>Image</th>
					<th>Name</th>
					<th>Advertiser</th>
					<th>Lead Cap Type</th>
					<th>Lead Cap Value</th>
					<th>Notes</th>
					<th>Type</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				@if(isset($campaigns))
					@foreach($campaigns as $campaign)
						<tr>
							<td>
								<span id="cmp-{{ $campaign['id']}}-prio">{{ $campaign->priority }}</span>
							</td>
							<td>{{ $campaign->id }}</td>
							<?php
								$img = '';
								if(is_null($campaign->image) || $campaign->image == '') {
									$url = url('images/img_unavailable.jpg');
									$img = 'none';
								} else $url = $campaign->image;
							?>
							<td>
								<span class="imgPreTbl" id="cmp-{{ $campaign['id']}}-img" data-img="{{ $img }}">
									<img src="{{ $url }}"/>
								</span>
							</td>
							<td>
								<span id="cmp-{{ $campaign['id']}}-name">{{$campaign->name}}</span>
							</td>
							<td>
								<span id="cmp-{{ $campaign['id']}}-adv" data-adv="{{ $campaign->advertiser_id }}">{{ $campaign->advertiser->company }}</span>
							</td>
							<td>
								<span id="cmp-{{ $campaign['id']}}-lct" data-type="{{ $campaign->lead_cap_type }}">{{ $lead_types[$campaign->lead_cap_type] }}</span>
							</td>
							<td>
								<span id="cmp-{{ $campaign['id']}}-lcv">{{ $campaign->lead_cap_value }}</span>
							</td>
							<td>
								<span id="cmp-{{ $campaign['id']}}-desc">{{ $campaign->description }}</span>
							</td>
							<td>
								<span id="cmp-{{ $campaign['id']}}-stat" data-status="{{$campaign['status']}}">
									@if($campaign->status == 0) Inactive
									@elseif($campaign->status == 1) Private
									@elseif($campaign->status == 2) Public
									@endif
								</span>
							</td>
							<td>
								<textarea id="cmp-{{ $campaign['id']}}-notes" class="hidden" disabled>{{ $campaign->notes }}</textarea>
								<input type="hidden" id="cmp-{{ $campaign['id']}}-dpyt" value="{{ $campaign->default_payout }}"/>
								<input type="hidden" id="cmp-{{ $campaign['id']}}-drcv" value="{{ $campaign->default_received }}"/>
								<button class="editCampaign btn btn-default" data-id="{{ $campaign->id }}">
									<span class="glyphicon glyphicon-pencil"></span>
								</button>
								<button class="deleteCampaign btn btn-default" data-id="{{ $campaign->id }}">
									<span class="glyphicon glyphicon-trash"></span>
								</button>
							</td>
						</tr>
					@endforeach
				@endif
			</tbody>
			<tfoot>
				<tr>
					<th>Priority</th>
					<th>ID</th>
					<th>Image</th>
					<th>Name</th>
					<th>Advertiser</th>
					<th>Lead Cap Type</th>
					<th>Lead Cap Value</th>
					<th>Notes</th>
					<th>Type</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
@stop

@section('footer')
<script src="{{ URL::asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('js/moment.js') }}"></script>
<script src="{{ URL::asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ URL::asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ URL::asset('js/campaigns.js') }}"></script>
@stop