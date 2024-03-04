@extends('app')

@section('title') Campaigns 
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<!-- <link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet"> -->
<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">

<!-- Select2 CSS -->
<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">

<link href="{{ asset('https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css') }}" rel="stylesheet">

<link href="{{ asset('bower_components/codemirror-5.32.0/lib/codemirror.css') }}" rel="stylesheet">

<style>
	@media (min-width: 992px) {
	  .modal-lg {
		width: 1050px;
	  }
	}

	/*highpaying styles*/
	.item-fields{
		padding : 5px;
		cursor: pointer;
	}

	.item-fields:hover{
		background-color: whitesmoke;
		color: darkgreen;
	}

	#campaign-table_length {
	    width: 20%;
	    float: left;
	}

	.addToolbar {
	    float: right;
	    margin-right: 10px;
	}

	.addToolbar label {
	    float: right;
	}

	#campaign-table_filter {
	    float: right;
	}
	#show_inactive_campaigns {margin-right: 15px;margin-top: 4px;}
	#show_inactive_campaigns input[type="checkbox"] {
		-ms-transform: scale(1.5); /* IE */
	    -moz-transform: scale(1.5); /* FF */
	    -webkit-transform: scale(1.5); /* Safari and Chrome */
	    -o-transform: scale(1.5); /* Opera */
	    transform: scale(1.5);
	    padding: 10px;
	    margin-right: 5px;
	}
	#show_inactive_campaigns a {margin-left: 2px;}
</style>
@stop

@section('content')

{!! Form::hidden('eiq_iframe_id', env('EIQ_IFRAME_ID',0),array('id' => 'eiq_iframe_id')) !!}

<?php
    $attributes = [
            'url' => url('uploadCampaignPayout'),
            'class'=> 'form-inline form_with_file_not_modal',
            'data-confirmation' => '',
            'data-process' => 'upload_campaign_payout',
            'id' =>'upload_campaign_payout_form',
            'enctype' => 'multipart/form-data'
    ];
?>
<div class="row">
	<div class="col-lg-9 col-md-9">
        {!! Form::open($attributes) !!}
            @if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_campaign')))
                <div class="form-group">
                    <button id="addCmpBtn" class="btn btn-primary bottomMarginForTable pull-left" type="button">Add Campaign</button>
                </div>
            @endif
            <div class="form-group">
                {!! Form::file('file', ['id' => 'file', 'class' => 'file form-control bottomMarginForTable', 'place', 'required']) !!}
            </div>
            <button id="campaign_upload_payout_button" type="submit" class="btn btn-primary bottomMarginForTable this_modal_submit">Upload Payout</button>
        {!! Form::close() !!}
	</div>

	<div class="col-lg-3 col-md-3">
		<button id="sortCmpBtn" type="button" class="btn btn-default pull-right" data-toggle="modal">
			<span class="glyphicon glyphicon-sort-by-attributes"></span> Sort Campaigns
		</button>
		<button id="affiliateCampaignMgmtBtn" type="button" class="btn btn-default pull-right" data-toggle="modal">
			<i class="fa fa-users fa-fw"></i>
		</button>
	</div>
</div>
<div class="row">
    <div class="row this_error_wrapper internal-error-wrapper col-lg-12 col-md-12">
        <br/>
        <div class="container-fluid">
            <div class="alert alert-danger alert-dismissible alert-danger-wrapper">
                <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="form-group">
                    <div class="alert-danger-content this_errors"></div>
                </div>
            </div>

            <div class="alert alert-warning alert-dismissible alert-warning-wrapper" role="alert">
                <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="form-group">
                    <div class="alert-warning-content"></div>
                </div>
            </div>

            <div class="alert alert-success alert-dismissible alert-success-wrapper" role="alert">
                <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="form-group">
                    <div class="alert-success-content"></div>
                </div>
            </div>
        </div>
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
      	<button id="sortByPriorityBtn" type="button" class="btn btn-default active">
			<span class="glyphicon glyphicon-sort-by-attributes"></span> By Priority
		</button>

      	<button id="sortByRevenueBtn" type="button" class="btn btn-default">
			<span class="glyphicon glyphicon-sort-by-attributes"></span> By Revenue
		</button>
		
		<button type="button" class="btn btn-primary saveSortedCampaignsBtn pull-right" style="display:none">Update</button>

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
        <button type="button" class="btn btn-primary saveSortedCampaignsBtn" style="display:none">Update</button>
      </div>
    </div>
  </div>
</div>
</div>

<!-- ADD Campaign Modal -->
<div class="modal fade" id="addCmpFormModal" role="dialog" aria-labelledby="addCmpFormModal">
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
							array('class' => 'form-control this_field campaignType', 'required' => 'true','data-form' => 'add') ) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('category','Category') !!}
						{!! Form::select('category', [null=>''] + $categories, '', 
							array('class' => 'form-control this_field', 'required' => 'true') ) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('advertiser','Advertiser') !!}
						{!! Form::select('advertiser', $advertisers, '',
							array('class' => 'form-control this_field advertiser-select-add', 'required' => 'true', 'style' => 'width: 100%') ) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('advertiser_email','CCPA Advertiser Email') !!}
						{!! Form::text('advertiser_email','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</div>
					<div class="col-md-12">
						<div class="row">
							<div id="leadCapType_add" class="col-md-12 form-div">
								{!! Form::label('lead_type','Lead Cap Type') !!}
								{!! Form::select('lead_type', [null=>''] + $lead_types, '', 
									array('class' => 'form-control this_field leadCapType', 'required' => 'true','data-form' => 'add') ) !!}
							</div>
							<div id="leadCapVal_add" class="col-md-6 form-div" style="display:none">
								{!! Form::label('lead_value','Lead Cap Value') !!}
								{!! Form::text('lead_value','',
									array('class' => 'form-control this_field', 'required' => 'true')) !!}
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6 form-div">
								{!! Form::label('default_payout','Default Payout') !!}
								{!! Form::text('default_payout','',
									array('class' => 'form-control this_field', 'required' => 'true')) !!}
							</div>
							<div class="col-md-6 form-div">
								{!! Form::label('default_received','Default Received') !!}
								{!! Form::text('default_received','',
									array('class' => 'form-control this_field', 'required' => 'true')) !!}
							</div>
						</div>
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('rate','Rate') !!}
						{!! Form::text('rate','',
							array('class' => 'form-control this_field')) !!}
					</div>
					<div id="linkoutOfferID_add" class="col-md-12 form-div" style="display:none">
						{!! Form::label('linkout_offer_id','Cake Offer ID') !!}
						{!! Form::text('linkout_offer_id','',
							array('class' => 'form-control this_field')) !!}
					</div>
					<div id="olrProgramID_add" class="col-md-12 form-div" style="display:none">
						{!! Form::label('program_id','OLR Program ID') !!}
						{!! Form::text('program_id','',
							array('class' => 'form-control this_field')) !!}
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
							@foreach($campaign_statuses as $id => $status)
								<div class="radio-inline">
									<label>
										<?php 
											$this_inactive = $id == 0 ? true : false;
										?>
										{!! Form::radio('status', $id, $this_inactive, array('data-label' => $status,'class' => 'this_field')) !!}
										{!! $status !!}
									</label>
								</div>
							@endforeach
						</div>
					</div>
					<div class="col-md-12 form-div">
						<hr>
						{!! Form::label('publisher_name','Name in Publisher Portal') !!}
						{!! Form::text('publisher_name','',
							array('class' => 'form-control this_field')) !!}
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
<div class="modal fade" id="editCmpFormModal" role="dialog" aria-labelledby="editCmpFormModal">
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
					<a href="#stack_content_tab" aria-controls="content" role="tab" data-toggle="tab">Stack</a>
				</li>
			@endif

			@if($canAccessHighPayingContent)
				<li role="presentation" class="{{$highPayingContentActive}}">
					<a href="#high_paying_content_tab" aria-controls="content" role="tab" data-toggle="tab">High Paying Content</a>
				</li>
			@endif

			<li role="presentation">
				<a href="#posting_instructions_tab" aria-controls="content" role="tab" data-toggle="tab">Posting Instructions</a>
			</li>

			<li role="presentation">
				<a href="#payouts_history_tab" aria-controls="content" role="tab" data-toggle="tab">Payouts History</a>
			</li>
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

			<!-- POSTING INSTRUCTION TAB Start -->
			@include('campaign.posting_instruction')
			<!-- POSTING INSTRUCTION TAB End -->

			<!-- POSTING INSTRUCTION TAB Start -->
			@include('campaign.payouts_history')
			<!-- POSTING INSTRUCTION TAB End -->

		</div>
      </div>
    </div> 
  </div>
</div>
</div>

<div id="previewCampaignPostingInstructionModal" class="modal fade" role="dialog">
	<style scoped>
		.column-header {background-color: #0d446d;color: white;text-align: center;}
		.column-header.revenue {text-align: center;font-weight: bold;font-size: 20px;color: white;}
		.column-header.deals {text-align: center;font-weight: bold;font-size: 20px;color: white;}
		.btn-submit {background-color: #0d446d;border-color: #0d446d;color: white;}
		#previewCampaignPostingInstructionModal .table > tbody > tr > td, .table > thead > tr > th{ vertical-align: middle;}
	</style>
	<div>
	    <div class="modal-dialog">
	      <!-- Modal content-->
	      <div class="modal-content">
	        <div class="modal-body">
	          <button type="button" class="close" data-dismiss="modal" title="Close">&times;</button>
	          <ul class="nav nav-tabs">
	            <li><a data-toggle="tab" href="#posting-tab">Posting Instructions</a></li>
	            <li class="active"><a data-toggle="tab" href="#get-tab">Get a Code</a></li>
	          </ul>
	          <div class="tab-content">
	            <div id="posting-tab" class="previewPostingInstruction tab-pane fade">
		            
	            </div>
	            <div id="get-tab" class="tab-pane fade in active">
	            	<div class="code-sample-div">
		              <textarea class="previewSampleCode" style="margin:0px;width:571px;height:468px;">
		               
		              </textarea>
		              <!-- END SAMPLE CODE -->
		          	</div>
	              	<a class="btn btn-default btn-submit text-center" data-dismiss="modal" style="background-color:#0d446d;border-color:#0d446d;color: white;">OK</a>
	            </div>
	          </div>
	        </div>
	      </div>
	    </div>
	</div>
</div>
	
<!-- Config Automation -->
<div class="modal fade" id="cmpConfigAutomationModal" tabindex="-1" role="dialog" aria-labelledby="cmpConfigAutomationModal">
	<?php
  		$attributes = [
  			'url' 					=> 'campaign_config_interface',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> '',
  			'data-process' 			=> 'campaign_config_interface',
  			'id'					=> 'campaign_config_interface_form'
  		];
  	?>
	{!! Form::open($attributes) !!}
	{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Campaign Configuration Interface: <strong id="modal-campaign-title"></strong></h4>
      </div>
      <div class="modal-body">
      	<div id="cmpConfigAutomationDiv">
	      	{!! Form::label('posting_url','Posting URL') !!}
			{!! Form::textarea('posting_url','',
				array('class' => 'form-control this_field ', 'required' => 'true','rows' => '3')) !!}
			<p class="text-muted"><b>Note:</b> To post leads using XML or JSON format use the url: <em>http://postleads.engageiq.com/xmljson/eiq-http-request/awesome.php</em>
			<a href="{{ url('admin/eiq_http_request') }}" target="_blank"><i class="fa fa-question-circle"></i></a></p>
			<button id="cmpConfigAutomationGenerate" type="button" class="btn btn-primary pull-right" style="margin: 5px 0">Generate</button>
			<div style="clear:both"></div>
		</div>

		<table id="cmpConfigAutomationTable" class="table table-bordered" style="display:none">
			<thead>
				<tr>
					<th>Field name from Client</th>
					<th>Field from our End</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>{!! Form::label('post_url','Post URL') !!}</th>
					<th>
						{!! Form::text('post_url','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</th>
				</tr>
				<tr id="postHeaderDiv">
					<th>{!! Form::label('post_header','Post Header') !!}</th>
					<th>
						{!! Form::textarea('post_header','',
							array('class' => 'form-control this_field ', 'rows' => '2', 'placeholder' => 'no post header needed')) !!}
					</th>
				</tr>
				<tr id="postDataMapDiv">
					<th>{!! Form::label('post_data_map','Post Data Map') !!}</th>
					<th>
						{!! Form::textarea('post_data_map','',
							array('class' => 'form-control this_field ', 'rows' => '2', 'placeholder' => 'no post data map needed')) !!}
					</th>
				</tr>
				<tr>
					<th>{!! Form::label('post_method','Post Method') !!}</th>
					<th>
						{!! Form::select('post_method', array(''=>'','POST' => 'POST','GET' => 'GET'), 'key', array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</th>
				</tr>
				<tr>
					<th>{!! Form::label('post_success','Post Success') !!}</th>
					<th>
						{!! Form::text('post_success','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</th>
				</tr>
			</tbody>
		</table>

		<div class="form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
                
            </div>
		</div>	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button id="cmpConfigAutomationSubmit" type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
  {!! Form::close() !!}
</div>

<!-- Form Builder Start -->
@include('campaign.form_builder')
<!-- Form Builder End -->

<!-- Redirect Link Automation -->
<div id="loa-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="loa-modal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Link Out Builder</h4>
      </div>
      <div class="modal-body">
      	<div id="">
	      	{!! Form::label('parse_linkout_url','Link Out URL') !!}
			{!! Form::textarea('parse_linkout_url','',
				array('id' => 'parse_linkout_url', 'class' => 'form-control this_field ', 'required' => 'true','rows' => '3')) !!}
			<button id="loa-split" type="button" class="btn btn-primary pull-right" style="margin: 5px 0">Split</button>
			<div style="clear:both"></div>
		</div>

		<table id="loa-table" class="table table-bordered">
			<thead>
				<tr>
					<th>Field Name</th>
					<th>Field Values <a href="{{ url('admin/shortcodes') }}" target="_blank"><i class="fa fa-question-circle"></i></a></th>
					<th>
						<button id="loa-addRow" class="btn btn-default" type="button">
		  				  <span class="glyphicon glyphicon-plus"></span>
		  				</button>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>{!! Form::label('linkout_url','Link Out URL') !!}</th>
					<th>
						{!! Form::text('linkout_url','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
					</th>
					<th></th>
				</tr>
			</tbody>
		</table>

		<div class="form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
                
            </div>
		</div>	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button id="loa-submit" type="button" class="btn btn-primary">Generate</button>
      </div>
    </div>
  </div>
</div>

<!-- Affiliate Campaign Management -->
<div id="affCampMgmtModal" class="modal fade" role="dialog">
	<?php
		$attributes = [
			'url' 	=> 'campaign_affiliate_management',
			'class'	=> 'this_form',
			'data-confirmation'=> '',
			'data-process' 	=> 'campaign_affiliate_management'
		];
	?>
	{!! Form::open($attributes) !!}
	  <div class="modal-dialog modal-lg" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title">Campaign Affiliate Management</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row">
	        	{{-- <div class="col-md-12 form-div">
	        		{!! Form::label('affiliate_id','Affiliate/Revenue Tracker') !!}
	        		{!! Form::select('affiliate_id[]',[],null,['class' => 'form-control','id' => 'searchCmpAffMgmt', 'style' => 'width: 100%', 'multiple' => 'multiple']) !!}
	        	</div> --}}
	        	<div class="col-md-12 form-div">
	        		{!! Form::label('affiliate_id','Affiliate/Revenue Tracker') !!}
	        		{!! Form::select('affiliate_id',[],null,['class' => 'form-control','id' => 'searchCmpAffMgmt', 'style' => 'width: 100%']) !!}
	        	</div>
	        	<div class="col-md-12 form-div text-right">
	        		{!! Form::checkbox('cam_op',0, true, [
        					'id' => 'cam_op_chkbx',
        					'data-toggle' => 'toggle', 
        					'data-off' => 'Remove Affiliate',
        					'data-on' => 'Add/Edit Affiliate',
        					'data-width' => '150',
        					'data-onstyle' => 'primary',
        					'data-offstyle' => 'danger',
        				]) !!}
	        	</div>
	        	<div class="camAddAff-div col-md-6 form-div" style="display:none">
					{!! Form::label('lead_cap_type','Lead Cap Type') !!}
					{!! Form::select('lead_cap_type', $lead_types, '',
						array('class' => 'form-control this_field') ) !!}
				</div>
				<div class="camAddAff-div col-md-6 form-div" style="display:none">
					{!! Form::label('lead_cap_value','Lead Cap Value') !!}
					{!! Form::text('lead_cap_value','',
						array('class' => 'form-control this_field', 'disabled' => 'true')) !!}
				</div>
	        	<div class="col-md-12">
	        		<table id="cmpAffMgmt-table" class="table" style="    width: 100% !important;">
						<thead>
							<tr>
								<th>
									<input type="checkbox" id="allCAMTblcampaigns-chkbx" value="1" />
								</th>
								<th>ID</th>
								<th>Name</th>
								<th>Type</th>
								<th>Status</th>
								<th>Lead Cap</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
	        	</div>
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        {!! Form::submit('Save', array('class' => 'btn btn-primary', 'id' => 'campAffMgmtSubmitBtn', 'style' => 'display:none')) !!}
	      </div>
	    </div>
	  </div>
	{!! Form::close() !!}
</div>

<!-- JSON Form Builder Start -->
@include('campaign.json_form_builder')
<!-- JSON Builder End -->

<div class="row">
	<div class="col-xs-12">
		<table id="campaign-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
			<thead>
				<tr>
					<th>Priority</th>
					<th>ID</th>
					<th class="imgPreTbl">Image</th>
					<th>Name</th>
					<th>Advertiser</th>
					<th>Type</th>
					<th>Lead Cap Type</th>
					<th>Lead Cap Value</th>
					<th>Default Received</th>
					<th>Rate</th>
					<th>Status</th>
					<th class="col-actions" >Actions</th>
				</tr>
			</thead>
			<tbody>
			
			</tbody>
			<tfoot>
				<tr>
					<th>Priority</th>
					<th>ID</th>
					<th>Image</th>
					<th>Name</th>
					<th>Advertiser</th>
					<th>Type</th>
					<th>Lead Cap Type</th>
					<th>Lead Cap Value</th>
					<th>Default Received</th>
					<th>Rate</th>
					<th>Status</th>
					<th class="col-actions" >Actions</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<div id="campaignErrorModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Response</h4>
      </div>
      <div class="modal-body"></div>
    </div>
</div>


@stop

@section('footer')
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/moment.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('bower_components/ckeditor/ckeditor.js')}}"></script>
<script src="{{ asset('bower_components/ckeditor/adapters/jquery.js')}}"></script>
{{--<script src="{{ asset('js/vue.min.js') }}"></script>--}}
<script src="{{ asset('https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js') }}"></script>

<script src="{{ asset('bower_components/codemirror-5.32.0/lib/codemirror.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/addon/edit/matchbrackets.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/htmlmixed/htmlmixed.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/xml/xml.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/javascript/javascript.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/css/css.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/clike/clike.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/php/php.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/addon/search/search.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/addon/search/searchcursor.js')}}"></script>
<script src="{{ asset('js/admin/campaigns.min.js') }}"></script>

{{--<script>--}}
		{{--window.Vue = new Vue ({--}}
			{{--el : '#high_paying_content_tab',--}}
			{{--data : {--}}
				{{--contents : [],--}}
				{{--requiredField : 0,--}}
				{{--selectedRequiredField : [],--}}
				{{--customs : {--}}
						{{--field : '',--}}
						{{--value : ''--}}
				{{--},--}}
				{{--additionalField : {--}}
					{{--type : 0,--}}
					{{--field : '',--}}
					{{--question : '',--}}
					{{--options : []--}}
				{{--},--}}
				{{--selectedAdditionalFields : [],--}}
				{{--chooseSelections : {--}}
					{{--mirror : false,--}}
					{{--name : '',--}}
					{{--value : '',--}}
					{{--options : []--}}
				{{--},--}}
				{{--fields :--}}
						{{--[--}}
							{{--{name : 'User`s Personal Information', values : [--}}
								{{--{field:'first_name',val:'[VALUE_FIRST_NAME]', s : '0', format:null, fn:'getFirstName'},--}}
								{{--{field:'last_name',val:'[VALUE_LAST_NAME]', s : '0', format:null, fn:'getLastName'},--}}
								{{--{field:'gender',val:'[VALUE_GENDER]', s : '0', format:null, fn:'getGender'},--}}
								{{--{field:'gender',val:'[VALUE_GENDER_FULL]', s : '0', format:null, fn:'getGenderFull'},--}}
								{{--{field:'age',val:'[VALUE_AGE]', s : '0', format:null, fn:'getAge'},--}}
								{{--{field:'dob',val:'[VALUE_BIRTHDATE]', s : '0', format:'Y-m-d', fn:'getBirthDate'},--}}
								{{--{field:'dob',val:'[VALUE_BIRTHDATE]', s : '0', format:'m/d/Y', fn:'getBirthDate'},--}}
								{{--{field:'dob',val:'[VALUE_BIRTHDATE]', s : '0', format:'m-d-Y', fn:'getBirthDate'},--}}
								{{--{field:'dob_day',val:'[VALUE_DOBDAY]', s : '0', format:'d', fn:'getDobDay'},--}}
								{{--{field:'dob_month',val:'[VALUE_DOBMONTH]', s : '0', format:'m', fn:'getDobMonth'},--}}
								{{--{field:'dob_year',val:'[VALUE_DOBYEAR]', s : '0', format:'Y', fn:'getDobYear'},--}}
								{{--{field:'title',val:'[VALUE_TITLE]', s : '0', format:null, fn:'getTitle'}--}}
							{{--]},--}}
							{{--{name : 'User`s Address Information', values : [--}}
								{{--{field:'email',val:'[VALUE_EMAIL]', s : '0', format:null, fn:'getEmail'},--}}
								{{--{field:'ip',val:'[VALUE_IP]', s : '0', format:null, fn:'getIp'},--}}
								{{--{field:'zip',val:'[VALUE_ZIP]', s : '0', format:null, fn:'getZip'},--}}
								{{--{field:'state',val:'[VALUE_STATE]', s : '0', format:null, fn:'getState'},--}}
								{{--{field:'city',val:'[VALUE_CITY]', s : '0', format:null, fn:'getCity'}--}}
							{{--]},--}}

							{{--{name : 'Dates and Timestamps', values : [--}}
								{{--{field:'datetime',val:'[VALUE_DATE_TIME]', s : '0', format:'m/d/Y H:i:s', fn:'getDateTime'},--}}
								{{--{field:'datetime',val:'[VALUE_DATE_TIME]', s : '0', format:'m-d-Y H:i:s', fn:'getDateTime'},--}}
								{{--{field:'datetime',val:'[VALUE_DATE_TIME]', s : '0', format:'Y-m-d H:i:s', fn:'getDateTime'},--}}
								{{--{field:'datetime',val:'[VALUE_DATE_TIME]', s : '0', format:'m-d-Y H:i', fn:'getDateTime'},--}}
								{{--{field:'datetime',val:'[VALUE_DATE_TIME]', s : '0', format:'Y-m-d H:i', fn:'getDateTime'},--}}
								{{--{field:'datetime',val:'[VALUE_DATE_TIME]', s : '0', format:'m/d/Y H:i', fn:'getDateTime'},--}}
								{{--{field:'today',val:'[VALUE_TODAY]', s : '0', format:'Y-m-d', fn:'getToday'},--}}
								{{--{field:'today',val:'[VALUE_TODAY]', s : '0', format:'Y/m/d', fn:'getToday'},--}}
								{{--{field:'today',val:'[VALUE_TODAY]', s : '0', format:'m/d/Y', fn:'getToday'},--}}
								{{--{field:'today',val:'[VALUE_TODAY]', s : '0', format:'m-d-Y', fn:'getToday'}--}}
							{{--]},--}}

							{{--{name : 'OS, Browser & Device Detection', values : [--}}
								{{--{field:'os',val:'[DETECT_OS]', s : '0', format:null, fn:'getOs'},--}}
								{{--{field:'os_ver',val:'[DETECT_OS_VER]', s : '0', format:null, fn:'getOsVer'},--}}
								{{--{field:'browser',val:'[DETECT_BROWSER]', s : '0', format:null, fn:'getBrowser'},--}}
								{{--{field:'browser_ver',val:'[DETECT_BROWSER_VER]', s : '0', format:null, fn:'getBrowserVer'},--}}
								{{--{field:'user_agent',val:'[DETECT_USER_AGENT]', s : '0', format:null, fn:'getUserAgent'},--}}
								{{--{field:'device',val:'[DETECT_DEVICE]', s : '0', format:null, fn:'getDevice'}--}}
							{{--]},--}}

							{{--{name : 'Rev Trackers and Others', values : [--}}
								{{--{field:'rev_tracker',val:'[VALUE_REV_TRACKER]', s : '0', format:null, fn:'getRevTracker'},--}}
								{{--{field:'add_code',val:'CD[VALUE_REV_TRACKER]', s : '0', format:null, fn:'getCdRevTracker'}--}}
							{{--]},--}}

							{{--{name : 'Create a static field & value', values : [--}}
								{{--{field:'custom',val:'[STATIC FIELD + STATIC VALUE]', s : '1', format:null, fn:'getStatic'}--}}
							{{--]}--}}
						{{--],--}}

				{{--loading : false,--}}
				{{--type : 0--}}

			{{--},--}}

			{{--mounted()--}}
			{{--{--}}
			{{--},--}}
			{{--computed :--}}
			{{--{--}}


			{{--},--}}

			{{--methods : {--}}
				{{--setContentData : function(data){--}}
					{{--this.contents = data;--}}
					{{--this.selectedRequiredField = data.fields;--}}
					{{--this.selectedAdditionalFields = data.additional_fields;--}}
				{{--},--}}

				{{--addRequiredField : function()--}}
				{{--{--}}
					{{--if(!(this.requiredField[0]==5 && this.requiredField[1]==0))--}}
					{{--{--}}
						{{--this.selectedRequiredField.push(this.fields[this.requiredField[0]]['values'][this.requiredField[1]]);--}}
						{{--this.requiredField = 0;--}}
					{{--}--}}
				{{--},--}}

				{{--insertCustoms : function()--}}
				{{--{--}}
					{{--var customs = {--}}
						{{--field: this.customs.field,--}}
						{{--val:this.customs.value,--}}
						{{--s : '1',--}}
						{{--format:null,--}}
						{{--fn:'getStatic'--}}
					{{--};--}}
					{{--this.selectedRequiredField.push(customs);--}}
					{{--this.requiredField = 0;--}}
				{{--},--}}

				{{--addAdditonalField : function()--}}
				{{--{--}}
					{{--var additional_field = {--}}
						{{--type : this.additionalField.type,--}}
						{{--field : this.additionalField.field,--}}
						{{--question : this.additionalField.question,--}}
						{{--options: this.additionalGetOptions(this.additionalField.type),--}}
						{{--rules : {--}}
							{{--required : true--}}
						{{--}--}}
					{{--};--}}

					{{--this.selectedAdditionalFields.push(additional_field);--}}
					{{--this.resetAdditional();--}}
				{{--},--}}

				{{--additionalGetOptions  : function(type)--}}
				{{--{--}}
					{{--switch (type)--}}
					{{--{--}}
						{{--case 'yesOrNo' :--}}
							{{--return [--}}
								{{--{ value : 'yes', name : 'Yes' },--}}
								{{--{ value : 'no', name : 'No'}--}}
							{{--];--}}
						{{--break;--}}

						{{--case 'select' :--}}
							{{--return this.chooseSelections.options;--}}
							{{--break;--}}

						{{--default :--}}
							{{--return [];--}}
						{{--break;--}}
					{{--}--}}
				{{--},--}}

				{{--selectionSyncer : function(field)--}}
				{{--{--}}
					{{--if(this.chooseSelections.mirror)--}}
					{{--{--}}
						{{--if(field==1)--}}
						{{--{--}}
							{{--this.chooseSelections.value = this.chooseSelections.name;--}}
							{{--return true;--}}
						{{--}--}}
						{{--this.chooseSelections.name = this.chooseSelections.value;--}}
						{{--return true;--}}
					{{--}--}}
				{{--},--}}

				{{--addChooseSelections : function()--}}
				{{--{--}}
					{{--if(this.chooseSelections.name != '' || this.chooseSelections.value != '')--}}
					{{--{--}}
						{{--this.chooseSelections.options.push({ name: this.chooseSelections.name, value : this.chooseSelections.value });--}}
						{{--this.chooseSelections.name = '';--}}
						{{--this.chooseSelections.value = '';--}}
					{{--}--}}
				{{--},--}}

				{{--resetAdditional : function()--}}
				{{--{--}}
					{{--this.chooseSelections.options = [];--}}
					{{--this.chooseSelections.name = '';--}}
					{{--this.chooseSelections.value = '';--}}
					{{--this.chooseSelections.mirror = false;--}}
					{{--this.additionalField.type = '';--}}
					{{--this.additionalField.field = '';--}}
					{{--this.additionalField.question = '';--}}
					{{--this.additionalField.type = 0;--}}
				{{--},--}}


				{{--updateCampaignContents : function()--}}
				{{--{--}}
					{{--var data = {--}}
						{{--id : this.contents.id,--}}
						{{--name : this.contents.name,--}}
						{{--deal : this.contents.deal,--}}
						{{--description : this.contents.description,--}}
						{{--sticker : this.contents.sticker,--}}
						{{--cpa_creative_id : this.contents.cpa_creative_id,--}}
						{{--fields : this.selectedRequiredField,--}}
						{{--additional_fields : this.selectedAdditionalFields--}}
					{{--};--}}

					{{--if(confirm("Are you sure you want update High Paying Contents ?!"))--}}
					{{--{--}}
						{{--this.loading = true;--}}
						{{--$.post('/edit_campaign_high_paying_content',data,function(response)--}}
						{{--{--}}
							{{--alert('Campaign modification success');--}}
							{{--this.loading = false;--}}
						{{--}.bind(this));--}}
					{{--}--}}

				{{--}--}}
			{{--},--}}

			{{--filters : {--}}

			{{--},--}}

			{{--watch : {--}}
				{{--'contents.type' : function(){--}}
					{{--var coregs_types = [1, 2, 3, 7, 8, 9, 10, 11, 12, 13, 14];--}}
					{{--var externals = [4];--}}

					{{--console.log(this.contents.type);--}}

					{{--for (var i = 0; i < coregs_types.length; i++) {--}}
						{{--if (coregs_types[i] == this.contents.type) {--}}
							{{--this.type = 'coreg';--}}
							{{--return false;--}}
						{{--}--}}
					{{--}--}}
					{{--for (var i = 0; i < externals.length; i++) {--}}
						{{--if (externals[i] == this.contents.type) {--}}
							{{--this.type = 'external';--}}
							{{--return false;--}}
						{{--}--}}
					{{--}--}}
					{{--this.type = 'others';--}}
				{{--}--}}
			{{--}--}}
		{{--})--}}
{{--</script>--}}

@stop
