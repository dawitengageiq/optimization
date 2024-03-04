<style>
#campaign-filter-group-table > tbody > tr.odd > td > span, #campaign-filter-group-table > tbody > tr.even > td > span{
	font-weight: 800;
}

.filter-group-filters-table tbody tr td:nth-child(2), .filter-group-filters-table tbody tr td:nth-child(1) {
	word-wrap: break-word;
    word-break: break-all;
}

</style>
<div role="tabpanel" class="tab-pane {{$filterActive}}" id="filter_tab">
	{!! Form::label('for_filter_type','Add Filter', array('style' => 'padding-top:7px','id'=> 'for_filter_type')) !!}

	<i class="fa fa-question-circle" tabindex="0" data-toggle="collapse" href="#campaignFilterInfo" aria-expanded="false" aria-controls="collapseExample"></i>
	<button id="addCmpFilter" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#campaignFilterForm" data-collapse="show" aria-expanded="false" aria-controls="campaignFilterForm">
	  <span class="glyphicon glyphicon-plus"></span> Filter
	</button>
	<button id="addCmpFilterGroup" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#campaignFilterGroupForm" data-collapse="show" aria-expanded="false" aria-controls="campaignFilterGroupForm" style="margin-right: 5px;">
	  <span class="glyphicon glyphicon-plus"></span> Group
	</button>
	<div class="collapse" id="campaignFilterInfo" style="margin: 10px 0px;">
	  	<table class="table table-bordered">
	  		<tr>
	  			<th colspan="3" class="text-center">Filter Types</th>
	  		</tr>
	  		<tr>
	  			<th colspan="3" class="text-center">Custom</th>
	  		</tr>
			<tr>
				<td>
					<strong>desktop_view</strong> - checks if user is viewing in desktop. Value type is <strong>boolean</strong>.
				</td>
				<td>
					<strong>mobile_view</strong> - checks if user is viewing in mobile. Value type is <strong>boolean</strong>.
				</td>
				<td>
					<strong>tablet_view</strong> - checks if user is viewing in tablet. Value type is <strong>boolean</strong>.
				</td>
			</tr>
			<tr>
				<td>
					<strong>show_date</strong> - gets the day today and check if matches with filter value.
					<br>
					Ex. Sunday,Monday
				</td>
				<td>
					<strong>show_time</strong> - gets the current time and check it's between minimum maximum time. Value Type is <strong>Time</strong>.
				</td>
				<td>
					<strong>check_ping</strong> - checks if ping url returns ping success. Value type is <strong>boolean</strong>.
				</td>
			</tr>
			<tr>
	  			<th colspan="3" class="text-center">Profile</th>
	  		</tr>
	  		<tr>
				<td>
					<strong>age</strong> - gets user's age and checks if it matches filter value.
					<br>
					Ex. 19,21
				</td>
				<td>
					<strong>email</strong> - gets user's email and checks if its email domain matches filter value.
					<br>
					Ex. gmail,yahoo
				</td>
				<td>
					<strong>gender</strong> - gets user's gender and checks if it matches filter value. 
					<br>
					Ex. Female/Male
				</td>
			</tr>
			<tr>
				<td>
					<strong>state</strong> - gets user's state and checks if it matches filter value.
					<br>
					Ex. CA,NY
				</td>
				<td>
					<strong>zip</strong> - gets user's zip and checks if it matches filter value.
					<br>
					Ex. 10001, 10002
				</td>
				<td>
					<strong>ethnicity</strong> - gets user's ethnicity and checks if it matches filter value.
					<br>
					Ex. hispanic, caucasian etc.
				</td>
			</tr>
			<tr>
	  			<th colspan="3" class="text-center">Question</th>
	  		</tr>
	  		<tr>
				<td colspan="3">
					gets user's answer to question and checks if it matches filter value. Question type filters' value type is only <strong>boolean</strong>.
				</td>
			</tr>
			<tr>
	  			<th colspan="3" class="text-center">Extras</th>
	  		</tr>
	  		<tr>
				<td colspan="3">
					<strong>[NOT]</strong> - add to filter value if you want to check if user's value not matches filter value.
				</td>
			</tr>
		</table>
	</div>
	<div class="collapse" id="campaignFilterForm" style="margin: 10px 0px;">
		<div class="well">
		  	<?php
		  		$attributes = [
		  			'url' 					=> 'add_campaign_filter',
		  			'class'					=> 'this_form',
		  			'data-confirmation' 	=> '',
		  			'data-process' 			=> 'add_campaign_filter'
		  		];
		  	?>
		  	{!! Form::open($attributes) !!}
		  	<div class="row">
		  		{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
				{!! Form::hidden('this_id', '',array('id' => 'this_id','class' => 'this_field')) !!}
				<div class="col-md-5 form-group">
					{!! Form::label('filter_type','Filter Type') !!}
					{!! Form::select('filter_type', [null=>''] + $filter_types, '', 
						array('class' => 'form-control this_field', 'required' => 'true') ) !!}
				</div>
				<div class="col-md-7 form-group">
					{!! Form::label('value_type','Value Type') !!}
					<div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('value_type', 1, true, array('data-label' => 'Text','class' => 'this_field filter_value_type')) !!}
								Text
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('value_type', 2, false, array('data-label' => 'Boolean','class' => 'this_field filter_value_type')) !!}
								Boolean
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('value_type', 3, false, array('data-label' => 'Date','class' => 'this_field filter_value_type')) !!}
								Date
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('value_type', 6, false, array('data-label' => 'Time','class' => 'this_field filter_value_type')) !!}
								Time
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('value_type', 4, false, array('data-label' => 'Number','class' => 'this_field filter_value_type')) !!}
								Number
							</label>
						</div>
						<div class="radio-inline">
							<label>
								{!! Form::radio('value_type', 5, false, array('data-label' => 'Array','class' => 'this_field filter_value_type')) !!}
								Array
							</label>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 form-group">
					{!! Form::label('filter_value_01','Value') !!}
					<div id="val-1-text-wrapper">
						{!! Form::text('filter_value_01_text','',
						array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'filter_value_01_text')) !!}
					</div>
					<div id="val-1-input-wrapper" class="hidden">
						{!! Form::text('filter_value_01_input','',
						array('class' => 'form-control this_field', 'id' => 'filter_value_01_input')) !!}
					</div>
					<div id="val-1-date-wrapper" class="input-group date-wrapper hidden">
						{!! Form::text('filter_value_01_date','',
						array('class' => 'form-control this_field', 'id' => 'filter_value_01_date')) !!}
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
					</div>
					<div id="val-1-select-wrapper" class="hidden">
						{!! Form::select('filter_value_01_select', array('' => '','1' => 'True', '0' => 'False'), '', array('class' => 'form-control this_field disabled','id' => 'filter_value_01_select')) !!}
					</div>
					<div id="val-1-array-wrapper" class="hidden">
						<p id="val-1-array-helper" class="help-block">Comma-separated array.</p>
						{!! Form::textarea('filter_value_01_array','',
						array('class' => 'form-control this_field', 'id' => 'filter_value_01_array', 'rows' => '3')) !!}
					</div>
					<div id="val-1-time-wrapper" class="input-group time-wrapper hidden">
						{!! Form::text('filter_value_01_time','',
						array('class' => 'form-control this_field', 'id' => 'filter_value_01_time')) !!}
						<span class="input-group-addon glyphicon glyphicon-time"></span>
					</div>
				</div>
				<div id="filter_value_02_wrapper" class="col-md-6 form-group hidden">
					{!! Form::label('filter_value_02','Value') !!}
					<div id="val-2-input-wrapper">
						{!! Form::text('filter_value_02_input','',
							array('class' => 'form-control this_field', 'id' => 'filter_value_02_input')) !!}
					</div>
					<div id="val-2-date-wrapper" class="input-group date-wrapper hidden">
						{!! Form::text('filter_value_02_date','',
						array('class' => 'form-control this_field', 'id' => 'filter_value_02_date')) !!}
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
					</div>
					<div id="val-2-time-wrapper" class="input-group time-wrapper hidden">
						{!! Form::text('filter_value_02_time','',
						array('class' => 'form-control this_field', 'id' => 'filter_value_02_time')) !!}
						<span class="input-group-addon glyphicon glyphicon-time"></span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 form-group filter-group-div">
					{!! Form::label('filter_group','Add to Filter Group') !!}
					<p class="help-block hidden edit-filter-filter-group-help-block" style="margin-top: -7px;"><em>* this will only update if filter is found in the filter group.</em></p>
					{!! Form::select('filter_group[]', [null=>''], '', 
						array('class' => 'form-control this_field filterGroupList', 'required' => 'true', 'multiple' => 'true') ) !!}
				</div>
			</div>
			<div class="form-group this_error_wrapper">
				<div class="alert alert-danger this_errors">
	                HI
	            </div>
			</div>
			<div class="row">
				<div class="col-md-12 form-group">
					<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
					<button type="button" class="btn btn-default pull-right closeFilterCollapse" style="margin-right: 5px;">Cancel</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
	<div class="collapse" id="campaignFilterGroupForm" style="margin: 10px 0px;">
		<div class="well">
		  	<?php
		  		$attributes = [
		  			'url' 					=> 'add_campaign_filter_group',
		  			'class'					=> 'this_form',
		  			'data-confirmation' 	=> '',
		  			'data-process' 			=> 'add_campaign_filter_group'
		  		];
		  	?>
		  	{!! Form::open($attributes) !!}
		  	<div class="row">
		  		{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
				{!! Form::hidden('this_id', '',array('id' => 'this_id','class' => 'this_field')) !!}
				<div class="col-md-12 form-group">
					{!! Form::label('filter_group_name','Name') !!}
					{!! Form::text('filter_group_name','',
						array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'filter_group_name')) !!}
				</div>
				<div class="col-md-12 form-group">
					{!! Form::label('filter_group_description','Description') !!}
					{!! Form::textarea('filter_group_description','',
						array('class' => 'form-control this_field', 'id' => 'filter_group_description', 'rows' => '3')) !!}
				</div>
				<div class="col-md-12 form-group">
					{!! Form::label('filter_group_status','Status') !!}
                    <div>
                        <div class="radio-inline">
                            <label>
                                {!! Form::radio('filter_group_status', 0, true, array('data-label' => 'Inactive','class' => 'this_field')) !!}
                                Inactive
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                {!! Form::radio('filter_group_status', 1, false, array('data-label' => 'Active','class' => 'this_field')) !!}
                                Active
                            </label>
                        </div>
                    </div>
				</div>
			</div>
			<div class="form-group this_error_wrapper">
				<div class="alert alert-danger this_errors">
	                HI
	            </div>
			</div>
			<div class="row">
				<div class="col-md-12 form-group">
					<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
					<button type="button" class="btn btn-default pull-right closeFilterGroupCollapse" style="margin-right: 5px;">Cancel</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>

	<div style="margin-bottom: 10px;"></div>
	<table id="campaign-filter-group-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table table-wrap">
		<thead>
			<tr>
				<th>Name</th>
				<th>Description</th>
				<th>Status</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><span id="cfg-1-name">Filter - Name</span></td>
				<td><span id="cfg-1-desc">Filter - Description</span></td>
				<td>
					<span id="cf-1-stat" data-status="1">Active</span>
				</td>
				<td>
					<button id="cfg-1-view-button" class="btn btn-default viewCampaignFilterGroup" type="button" data-id="1"><span class="glyphicon glyphicon-eye-open"></span></button>
					<button id="cfg-1-edit-button" class="btn btn-default editCampaignFilterGroup" type="button" data-id="1"><span class="glyphicon glyphicon-pencil"></span></button>
					<button id="cfg-1-delete-button" class="btn btn-default deleteCampaignFilterGroup" type="button" data-id="1"><span class="glyphicon glyphicon-trash"></span></button>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th>Name</th>
				<th>Description</th>
				<th>Status</th>
				<th></th>
			</tr>
		</tfoot>
	</table>
</div>