<div role="tabpanel" class="tab-pane {{$payoutActive}}" id="payout_tab">
	
	{!! Form::label('payout_title','Add Affiliate Payout', array('style' => 'padding-top:7px')) !!}

	<button id="deletePytAff" class="btn btn-default pull-right" type="button" disabled><span class="glyphicon glyphicon-trash"></span></button>
	
	<button id="editPytAff" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#editCampaignPayoutForm" aria-expanded="false" aria-controls="editCampaignPayoutForm" style="margin:0px 2px 10px;" disabled>
		<span class="glyphicon glyphicon-pencil"></span>
	</button>

	<button id="addPytAff" class="btn btn-default pull-right collapsed" type="button" data-toggle="collapse" data-target="#campaignPayoutForm" aria-expanded="false" aria-controls="campaignPayoutForm" style="margin-bottom: 10px;">
	  <span class="glyphicon glyphicon-plus"></span>
	</button>

	<!-- Add PAYOUT FORM Start -->
	<?php
  		$attributes = [
  			'url' 					=> 'add_campaign_payout',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> '',
  			'data-process' 			=> 'add_campaign_payout'
  		];
  	?>
	{!! Form::open($attributes) !!}
	<div class="collapse" id="campaignPayoutForm" style="margin: 10px 0px;">
		<div class="well">
			{!! Form::label('payout[]','Select Affiliate Payout', array('style' => 'padding-top:7px')) !!}
			<div class="row">
				<div class="col-md-12 form-div">
					{!! Form::select('payout[]', $affiliates, '', 
						array('class' => 'form-control this_field', 'multiple' => 'true', 'size' => '10') ) !!}
				</div>
			</div>
		  	<div class="row">
		  		{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
				{!! Form::hidden('this_id', '',array('id' => 'this_id','class' => 'this_field')) !!}
				<div class="col-md-6 form-group">
					{!! Form::label('payout_receivable','Cash Receivable') !!}
					<div class="input-group">
				      <div class="input-group-addon">$</div>
				      {!! Form::text('payout_receivable','',
						array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'payout_receivable')) !!}
				    </div>
				</div>
				<div class="col-md-6 form-group">
					{!! Form::label('payout_payable','Cash Payable') !!}
					<div class="input-group">
				      <div class="input-group-addon">$</div>
				      {!! Form::text('payout_payable','',
						array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'payout_payable')) !!}
				    </div>
					
				</div>
			</div>
			<div class="form-group this_error_wrapper">
				<div class="alert alert-danger this_errors">
	                
	            </div>
			</div>
			<div class="row">
				<div class="col-md-12 form-group">
					<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
					<button type="button" class="btn btn-default pull-right closePayoutCollapse" style="margin-right: 5px;">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	{!! Form::close() !!}

	<div class="row" style="margin-top:20px">
		<div class="listOfCampaignAffiliates panel panel-default col-md-11 center_div hidden">
		  <div class="panel-body">
		    <strong>You are about to edit the following affiliates:</strong>
		  </div>
		</div>
	</div>
	<!-- Edit PAYOUT FORM Start -->
	<?php
  		$attributes = [
  			'url' 					=> 'edit_campaign_payout',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> 'Are you sure you want to edit this?',
  			'data-process' 			=> 'edit_campaign_payout'
  		];
  	?>
	{!! Form::open($attributes) !!}
	<div id="selectedPayoutDiv" class="hidden"></div>
	<div class="collapse" id="editCampaignPayoutForm" style="margin: 10px 0px;">
		<div class="well">
		  	<div class="row">
				<div class="col-md-6 form-group">
					{!! Form::label('edit_payout_receivable','Cash Receivable') !!}
					<div class="input-group">
				      <div class="input-group-addon">$</div>
				      {!! Form::text('edit_payout_receivable','',
						array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'edit_payout_receivable')) !!}
				    </div>
				</div>
				<div class="col-md-6 form-group">
					{!! Form::label('edit_payout_payable','Cash Payable') !!}
					<div class="input-group">
				      <div class="input-group-addon">$</div>
				      {!! Form::text('edit_payout_payable','',
						array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'edit_payout_payable')) !!}
				    </div>
					
				</div>
			</div>
			<div class="form-group this_error_wrapper">
				<div class="alert alert-danger this_errors">
	                
	            </div>
			</div>
			<div class="row">
				<div class="col-md-12 form-group">
					<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
					<button type="button" class="btn btn-default pull-right closeEditPayoutCollapse" style="margin-right: 5px;">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	

	<!-- <hr>

	<div class="row">
		<div class="col-md-12">
			<div class="checkbox pull-right" style="margin-right: 10px;">
			    <label>
			    	{!! Form::checkbox('selectAllPayoutAffiliate', '', false, array('id' => 'selectAllPayoutAffiliate')); !!}
			    	<strong>Select All</strong>
			    </label>
			</div>
		</div>
	</div> -->
	

	<table id="campaign-payout-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
		<thead>
			<tr>
				<th>{!! Form::checkbox('capPytSelectAllAff', '', false, array('class' => 'capPytSelectAllAff')); !!}</th>
				<th>Company</th>
				<th>Receivable</th>
				<th>Payable</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<input name="select_payout[]" class="selectCampaignPayout" value="1" type="checkbox">
					1
				</td>
				<td>Company</td>
				<td>
					<span id="cp-1-receivable">201.00</span>
				</td>
				<td>
					<span id="cp-1-payable">100</span>
				</td>
				<td>
					<button id="cp-1-edit-button" class="btn btn-default editCampaignAffiliate" type="button" data-id="1"><span class="glyphicon glyphicon-pencil"></span></button>
					<button id="cp-1-update-button" class="btn btn-primary updateCampaignAffiliate hidden" type="button" data-id="1"><span class="glyphicon glyphicon-floppy-disk"></span></button>
				</td>
			</tr>
			<tr>
				<td>
					<input name="select_payout[]" class="selectCampaignPayout" value="2" type="checkbox">
					2
				</td>
				<td>Company</td>
				<td>
					<span id="ca-2-type" data-id='1'>60.00</span>
				</td>
				<td>
					<span id="ca-2-value">50</span>
				</td>
				<td>
					<button id="ca-2-edit-button" class="btn btn-default editCampaignPayout" type="button" data-id="1"><span class="glyphicon glyphicon-pencil"></span></button>
					<button id="ca-2-update-button" class="btn btn-primary updateCampaignPayout hidden" type="button" data-id="1"><span class="glyphicon glyphicon-floppy-disk"></span></button>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th>{!! Form::checkbox('capPytSelectAllAff', '', false, array('class' => 'capPytSelectAllAff')); !!}</th>
				<th>Company</th>
				<th>Receivable</th>
				<th>Payable</th>
				<th></th>
			</tr>
		</tfoot>
	</table>

	{!! Form::close() !!}
</div>