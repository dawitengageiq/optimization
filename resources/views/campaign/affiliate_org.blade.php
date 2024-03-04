<div role="tabpanel" class="tab-pane" id="affiliate_tab">
	{!! Form::label('affiliate_title','Add Affiliates', array('style' => 'padding-top:7px')) !!}

	<button id="addCampAff" class="btn btn-default pull-right " type="button" data-toggle="collapse" data-target="#campaignAffiliateForm" aria-expanded="false" aria-controls="campaignAffiliateForm" style="margin-bottom: 10px;">
	  <span class="glyphicon glyphicon-plus"></span>
	</button>

	<div class="collapse" id="campaignAffiliateForm" style="margin: 10px 0px;">
		<div class="well">
			{!! Form::label('affiliates[]','Select Affiliates: ', array('style' => 'padding-top:7px')) !!}
			<div class="row">
				<div class="col-md-12 form-div">
					{!! Form::select('affiliates[]', $affiliates, '', 
						array('class' => 'form-control this_field', 'multiple' => 'true', 'size' => '10') ) !!}
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 form-group">
					<button id="addPvtAff" class="btn btn-primary pull-right disabled" type="button">Add</button>
					<button type="button" class="btn btn-default pull-right closeAffiliateCollapse" style="margin-right: 5px;">Cancel</button>
				</div>
			</div>
		</div>
	</div>

	<!-- <button id="addPvtAff" class="btn btn-default pull-right disabled" type="button" style="margin-bottom: 10px;"><span class="glyphicon glyphicon-plus"></span></button> -->
	<!-- <div class="row">
		<div class="col-md-12 form-div">
			{!! Form::select('affiliates[]', $affiliates, '', 
				array('class' => 'form-control this_field', 'multiple' => 'true', 'size' => '10') ) !!}
		</div>
	</div> -->
	<table id="campaign-affiliate-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
		<thead>
			<tr>
				<th>ID</th>
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
				<th>ID</th>
				<th>Company</th>
				<th>Lead Cap Type</th>
				<th>Lead Cap Value</th>
				<th></th>
			</tr>
		</tfoot>
	</table>
</div>