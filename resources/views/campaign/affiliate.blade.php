<div role="tabpanel" class="tab-pane {{$affiliateActive}}" id="affiliate_tab">
	<div id="campaignAffiliateDiv">
		{!! Form::label('affiliate_title','Add Affiliates', array('style' => 'padding-top:7px')) !!}

		<button id="deleteAffiliatesBtn" class="btn btn-default pull-right" type="button" disabled><span class="glyphicon glyphicon-trash"></span></button>
		
		<button id="editAffilatesBtn" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#editCampaignAffiliateForm" aria-expanded="false" aria-controls="editCampaignAffiliateForm" style="margin:0px 2px 10px;" disabled>
			<span class="glyphicon glyphicon-pencil"></span>
		</button>

		<button id="addCampAff" class="btn btn-default pull-right collapsed" type="button" data-toggle="collapse" data-target="#campaignAffiliateForm" aria-expanded="false" aria-controls="campaignAffiliateForm" style="margin-bottom: 10px;">
		  <span class="glyphicon glyphicon-plus"></span>
		</button>


		<!-- Add AFFILIATE FORM Start -->
		<?php
	  		$attributes = [
	  			'url' 					=> 'add_campaign_affiliate',
	  			'class'					=> 'this_form',
	  			'data-confirmation' 	=> '',
	  			'data-process' 			=> 'add_campaign_affiliate'
	  		];
	  	?>
		{!! Form::open($attributes) !!}
			{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
			<div class="collapse" id="campaignAffiliateForm" style="margin: 10px 0px;">
				<div class="well">
					{!! Form::label('affiliates[]','Select Affiliates: ', array('style' => 'padding-top:7px')) !!}
					<div class="row">
						<div class="col-md-12 form-div">
							{!! Form::select('affiliates[]', $affiliates, '',
								array('class' => 'form-control this_field', 'multiple' => 'true', 'size' => '10','required' => 'true') ) !!}
						</div>
						{{-- <div class="col-md-12 form-div">
                            {!! Form::label('affiliate','Affiliate') !!}
                            {!! Form::select('affiliate[]',[],null,['class' => 'form-control search-campaign-affiliate-select','id' => 'campaign-affiliate', 'style' => 'width: 100%','multiple' => 'multiple']) !!}
                        </div> --}}
						<div class="col-md-6 form-div">
							{!! Form::label('lead_cap_type','Lead Cap Type') !!}
							{!! Form::select('lead_cap_type', $lead_types, '',
								array('class' => 'form-control this_field') ) !!}
						</div>
						<div class="col-md-6 form-div">
							{!! Form::label('lead_cap_value','Lead Cap Value') !!}
							{!! Form::text('lead_cap_value','',
								array('class' => 'form-control this_field', 'id' => 'lead_cap_value')) !!}
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 form-group">
							<button id="addCmpAffiliateBtn" class="btn btn-primary pull-right disabled" type="submit">Add</button>
							<button type="button" class="btn btn-default pull-right closeAffiliateCollapse" style="margin-right: 5px;">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		{!! Form::close() !!}

		<div class="row" style="margin-top:20px">
			<div class="listOfCampaignAffiliatesEdit panel panel-default col-md-11 center_div hidden">
			  <div class="panel-body">
			    <strong>You are about to edit the following affiliates:</strong>
			  </div>
			</div>
		</div>

	</div>

	<!-- Affiliate Webstie -->
	@include('campaign.affiliate_websites')

	<!-- Edit Campaign Affiliate FORM Start -->
	<?php
  		$attributes = [
  			'url' 					=> 'edit_campaign_affiliate',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> 'Are you sure you want to edit this?',
  			'data-process' 			=> 'edit_campaign_affiliate'
  		];
  	?>
	{!! Form::open($attributes) !!}
		{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
		<div id="selectedAffiliateDiv" class="hidden"></div>
		<!-- <textarea name="selected_affiliate" id="selected_affiliate" hidden></textarea> -->
		<div class="collapse" id="editCampaignAffiliateForm" style="margin: 10px 0px;">
			<div class="well">
				<div class="row">
					<div class="col-md-6 form-div">
						{!! Form::label('edit_lead_cap_type','Lead Cap Type') !!}
						{!! Form::select('edit_lead_cap_type', $lead_types, '',
							array('class' => 'form-control this_field') ) !!}
					</div>
					<div class="col-md-6 form-div">
						{!! Form::label('edit_lead_cap_value','Lead Cap Value') !!}
						{!! Form::text('edit_lead_cap_value','',
							array('class' => 'form-control this_field', 'id' => 'edit_lead_cap_value')) !!}
					</div>
				</div>
				<div class="form-group this_error_wrapper">
					<div class="alert alert-danger this_errors">

					</div>
				</div>
				<div class="row">
					<div class="col-md-12 form-group">
						<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
						<button type="button" class="btn btn-default pull-right closeEditAffiliateCollapse" style="margin-right: 5px;">Cancel</button>
					</div>
				</div>
			</div>
		</div>

		<table id="campaign-affiliate-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
			<thead>
				<tr>
					<th>
						<input name="selectAllCampaignAffiliate" class="selectAllCampaignAffiliate" type="checkbox">
					</th>
					<th>Company</th>
					<th>Lead Cap Type</th>
					<th>Lead Cap Value</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>ID</td>
					<td>Company</td>
					<td>
						<span id="ca-1-type" data-id='1'>Daily</span>
						<span id="ca-1-type-select" class="hidden">
							<select class="form-control full_width_form_field">
								<option>Daily</option>
								<option>Monthly</option>
							</select>
						</span>
					</td>
					<td>
						<span id="ca-1-value">100</span>
						<span id="ca-1-value-input" class="hidden">
							<input type="text" class="form-control full_width_form_field" value="100"/>
						</span>
					</td>
					<td>
						<button id="ca-1-edit-button" class="btn btn-default editCampaignAffiliate" type="button" data-id="1"><span class="glyphicon glyphicon-pencil"></span></button>
						<button id="ca-1-update-button" class="btn btn-primary updateCampaignAffiliate hidden" type="button" data-id="1"><span class="glyphicon glyphicon-floppy-disk"></span></button>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th>
						<input name="selectAllCampaignAffiliate" class="selectAllCampaignAffiliate" type="checkbox">
					</th>
					<th>Company</th>
					<th>Lead Cap Type</th>
					<th>Lead Cap Value</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	{!! Form::close() !!}
</div>