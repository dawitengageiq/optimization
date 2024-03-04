<div id="affiliateWebsiteDiv" style="display:none">

	<button id="backToCampaignAffiliateBtn" class="btn btn-default" type="button"><span class="glyphicon glyphicon-arrow-left"></span></button>

	<label for="website_title" style="padding-top:7px">Add Affiliate: <span id="affiliate_for_website_title"></span> Website</label>

	<button id="deleteAffiliateWebsitesBtn" class="btn btn-default pull-right" type="button" disabled style="margin:0px 2px 10px;"><span class="glyphicon glyphicon-trash"></span></button>

	<button id="editAffilateWebsitesBtn" class="btn btn-default pull-right" type="button" style="margin:0px 2px 10px;" disabled>
		<span class="glyphicon glyphicon-pencil"></span>
	</button>

	<button id="addAffiliateWebsite" class="btn btn-default pull-right collapsed" type="button" data-toggle="collapse" data-target="#affiliateWebsiteForm" aria-expanded="false" aria-controls="affiliateWebsiteForm" style="margin-bottom: 10px;">
	  <span class="glyphicon glyphicon-plus"></span>
	</button>

	<!-- Add AFFILIATE WEBSITE FORM Start -->
	<?php
		$attributes = [
			'url' 					=> 'add_affiliate_website',
			'class'					=> 'this_form',
			'data-confirmation' 	=> '',
			'data-process' 			=> 'add_affiliate_website',
			'id'					=> 'affiliate_website_form'
		];
	?>
	{!! Form::open($attributes) !!}
		{!! Form::hidden('website_affiliate', '',array('id' => 'website_affiliate','class' => 'this_field website_affiliate')) !!}
		{!! Form::hidden('website_id', '',array('id' => 'website_id','class' => 'this_field')) !!}
		<div class="collapse" id="affiliateWebsiteForm" style="margin: 10px 0px;">
			<div class="well">
				<div class="row">
					<div class="col-md-6 form-div">
						{!! Form::label('website_name','Name') !!}
						{!! Form::text('website_name','',
							array('class' => 'form-control this_field', 'id' => 'website_name')) !!}
					</div>
					<div class="col-md-6 form-div">
						{!! Form::label('website_payout','Payout') !!}
						{!! Form::text('website_payout','',
							array('class' => 'form-control this_field', 'id' => 'website_payout')) !!}
					</div>
					<div class="col-md-12 form-div">
						{!! Form::label('revenue_tracker_id','Revenue Tracker ID') !!}
						{!! Form::text('revenue_tracker_id','',
							array('class' => 'form-control this_field', 'id' => 'revenue_tracker_id')) !!}
					</div>
					<div class="col-md-6 form-div">
						<div class="radio-inline">
							<label>
								{!! Form::radio('allow_datafeed', '1', false,array('class' => 'allow_datafeed this_field')) !!}
								Allow this tracker to send datafeed
							</label>
						</div>
					</div>
					<div class="col-md-6 form-div">
						<div class="radio-inline">
							<label>
								{!! Form::radio('allow_datafeed', '0', true,array('class' => 'allow_datafeed this_field')) !!}
								DO NOT Allow this tracker to send datafeed
							</label>
						</div>
					</div>
					<div class="col-md-12 form-div">
    					{!! Form::label('website_description','Description') !!}
						{!! Form::textarea('website_description','',
							array('id' => 'website_description','class' => 'form-control this_field', 'rows' => '2')) !!}
					</div>
					<!-- <div class="col-md-12 form-div">
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
					</div> -->
				</div>
				<div class="row">
					<div class="col-md-12 form-group">
						<button id="addCmpAffiliateBtn" class="btn btn-primary pull-right" type="submit">Save</button>
						<button type="button" class="btn btn-default pull-right closeAffiliateWebsiteCollapse" style="margin-right: 5px;">Cancel</button>
					</div>
				</div>
			</div>
		</div>
	{!! Form::close() !!}

	<div class="row" style="margin-top:20px">
		<div class="listOfAffiliateWebsitesEdit panel panel-default col-md-11 center_div hidden">
		  <div class="panel-body">
		    <strong>You are about to edit the following websites: <span class="putListOfWebsitesHere"></span></strong>
		  </div>
		</div>
	</div>

	<!-- Edit Campaign Affiliate FORM Start -->
	<?php
  		$attributes = [
  			'url' 					=> 'affiliate_website_payout',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> 'Are you sure you want to edit this?',
  			'data-process' 			=> 'affiliate_website_payout',
  			'id'					=> 'affiliate_website_payout_form'
  		];
  	?>
	{!! Form::open($attributes) !!}
		{!! Form::hidden('website_affiliate', '',array('id' => 'website_affiliate','class' => 'this_field website_affiliate')) !!}
		<div id="selectedAffiliateWebsiteDiv" class="hidden"></div>
		<!-- <textarea name="selected_affiliate" id="selected_affiliate" hidden></textarea> -->
		<div class="collapse" id="affiliateWebsitePayoutForm" style="margin: 10px 0px;">
			<div class="well">
				<div class="row">
					<div class="col-md-12 form-div">
						{!! Form::label('website_payout','Payout') !!}
						{!! Form::text('website_payout','',
							array('class' => 'form-control this_field', 'id' => 'website_payout')) !!}
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 form-group">
						<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
						<button type="button" class="btn btn-default pull-right closeAffiliateWebsitePayoutCollapse" style="margin-right: 5px;">Cancel</button>
					</div>
				</div>
			</div>
		</div>

		<table id="affiliate-website-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
			<thead>
				<tr>
					<th>
						<input name="selectAllAffiliateWebsite" class="selectAllAffiliateWebsite" type="checkbox">
					</th>
					<th>ID</th>
					<th>Revenue Tracker</th>
					<th>Name</th>
					<th>Description</th>
					<th>Payout</th>
					<th>Allow Datafeed</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
				<tr>
					<th>
						<input name="selectAllAffiliateWebsite" class="selectAllAffiliateWebsite" type="checkbox">
					</th>
					<th>ID</th>
					<th>Revenue Tracker</th>
					<th>Name</th>
					<th>Description</th>
					<th>Payout</th>
					<th>Allow Datafeed</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	{!! Form::close() !!}
</div>