<div role="tabpanel" class="tab-pane {{$infoActive}}" id="info_tab">

	<?php

		$campaign_count = [];

		if(isset($totalCampaignCount))
		{
			for($c = 1; $c <= $totalCampaignCount; $c++)
			{
				$campaign_count[$c] = $c;
			}
		}

  		$attributes = [
  			'url' 					=> 'edit_campaign',
  			'class'					=> 'form_with_file',
  			'data-confirmation' 	=> 'Are you sure you want to edit this attribute?',
  			'data-process' 			=> 'edit_campaign',
  			'files'					=> true
  		];
  	?>
  	{!! Form::open($attributes) !!}
	{!! Form::hidden('this_id', '',array('id' => 'this_id','class' => 'this_campaign this_field')) !!}
	{!! Form::hidden('allowed_force_edit', $forceUserToUpdate,array('id' => 'allowed_force_edit')) !!}
	<div class="row">
		<!-- <div class="col-md-12">
			<button id="editCampaignInfo" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#campaignFilterForm" data-collapse="show" aria-expanded="false" aria-controls="campaignFilterForm">
			  <span class="glyphicon glyphicon-pencil"></span>
			</button>
		</div> -->
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<p><strong>Date Created: <span id="date-created-container"></span></strong></p>
				</div>
				<div class="col-md-12 form-div">
					{!! Form::label('name','Name') !!}
					{!! Form::text('name','',
						array('class' => 'form-control this_field', 'required' => 'true')) !!}
				</div>
				<div class="col-md-12 form-div">
					{!! Form::label('priority','Priority No.') !!}
					{!! Form::select('priority', [null=>''] + $campaign_count , '',
						array('id' => 'priority','class' => 'form-control this_field', 'required' => 'true') ) !!}
				</div>
				<div class="col-md-12 form-div">
					{!! Form::label('campaign_type','Campaign Type') !!}
					{!! Form::select('campaign_type', [null=>''] + $campaign_types , '',
						array('id' => 'campaign_type','class' => 'form-control this_field campaignType','required' => 'true','data-form' => 'edit') ) !!}
				</div>
				<div class="col-md-12 form-div">
					{!! Form::label('category','Category') !!}
					{!! Form::select('category', [null=>''] + $categories, '', 
						array('class' => 'form-control this_field', 'required' => 'true') ) !!}
				</div>
				<div id="editCampaignAdvertiserDiv" class="col-md-12 form-div">
					{!! Form::label('advertiser','Advertiser') !!}
					{!! Form::select('advertiser', $advertisers, '',
						array('class' => 'form-control this_field advertiser-select-edit', 'required' => 'true', 'style' => 'width: 100%') ) !!}
				</div>
				<div class="col-md-12 form-div">
					{!! Form::label('advertiser_email','CCPA Advertiser Email') !!}
					{!! Form::text('advertiser_email','',
						array('class' => 'form-control this_field', 'required' => 'true')) !!}
				</div>
				<div class="col-md-12">
					<div class="row">
						<div id="leadCapType_edit" class="col-md-12 form-div">
							{!! Form::label('lead_type','Lead Cap Type') !!}
							{!! Form::select('lead_type', [null=>''] + $lead_types, '', 
								array('class' => 'form-control this_field leadCapType', 'required' => 'true','data-form' => 'edit') ) !!}
						</div>
						<div id="leadCapVal_edit" class="col-md-6 form-div" style="display:none">
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
					<div class="row">
						<div class="col-md-6 form-div">
							{!! Form::label('effective_date','As of (Effective Date)') !!}
							{!! Form::text('effective_date','',
								array('class' => 'form-control this_field', 'placeholder' => 'MM/DD/YYYY')) !!}
						</div>
						<div class="col-md-6 form-div">
							{!! Form::label('rate','Rate') !!}
							{!! Form::text('rate','',
								array('class' => 'form-control this_field')) !!}
						</div>
					</div>
				</div>
				<div id="linkoutOfferID_edit" class="col-md-12 form-div" style="display:none">
					{!! Form::label('linkout_offer_id','Cake Offer ID') !!}
					{!! Form::text('linkout_offer_id','',
						array('class' => 'form-control this_field')) !!}
				</div>
				<div id="olrProgramID_edit" class="col-md-12 form-div" style="display:none">
					{!! Form::label('program_id','OLR Program ID') !!}
					{!! Form::text('program_id','',
						array('class' => 'form-control this_field')) !!}
				</div>
			</div>
		</div>
		<div class="col-md-6 image_wrapper" data-type="edit">
			<div class="row">
				<div class="col-md-12 form-div">
					{!! Form::label('img_type','Image Source') !!}
					<div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('img_type', '1', true,array('class' => 'img_type this_field')) !!}
								Image Upload
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('img_type', '2', false,array('class' => 'img_type this_field')) !!}
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
					{!! Form::file('image', array('class' => 'form-control this_field campaign_img','accept' => 'image/*'))!!}
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
									{!! Form::radio('status', $id, false, array('data-label' => $status,'class' => 'this_field')) !!}
									{!! $status !!}
								</label>
							</div>
						@endforeach
						<!-- <div class="radio-inline">
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
						</div> -->
					</div>
				</div>
				<div class="col-md-12 form-div">
					<hr>
					{!! Form::label('publisher_name','Name in Publisher Portal') !!}
					{!! Form::text('publisher_name','',
						array('class' => 'form-control this_field')) !!}
				</div>
				<div class="col-md-12 form-div">
					{!! Form::label('is_external','Display in Publisher Portal') !!}
					<div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('is_external', 0, true, array('data-label' => 'Disabled','class' => 'this_field')) !!}
								Disabled
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('is_external', 1, false, array('data-label' => 'Enabled','class' => 'this_field')) !!}
								Enabled
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
	<hr>
	<!-- <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button> -->
	<div class="row">
		<div class="container pull-right">
			<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
		</div>
	</div>
	{!! Form::close() !!}
</div>