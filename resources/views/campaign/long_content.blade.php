<div role="tabpanel" class="tab-pane {{$longContentActive}}" id="long_content_tab">
	{!! Form::label('for_content','Campaign Long Content', array('style' => 'padding-top:7px','id'=> 'for_filter_type')) !!}
	<a href="{{ url('admin/shortcodes') }}" target="_blank"><i class="fa fa-question-circle"></i></a>
	<button id="editCampaignLongContent" class="btn btn-default pull-right" style="margin:0px 2px 10px;" data-config="show">
	  <span class="glyphicon glyphicon-pencil"></span>
	</button>
	<div class="dropdown pull-right">
		<button type="button" id="prvCampaignContent" class="btn btn-default" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
	  		<span class="glyphicon glyphicon-eye-open"></span>
	  		<span class="caret"></span>
		</button>
	  	<ul class="dropdown-menu" aria-labelledby="prvCampaignContent">
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cdall/preview_survey.php?id=" class="prvCmpCnt" data-type="blue-path">Blue Path</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_election/preview_survey.php?id=" class="prvCmpCnt" data-type="election">Election</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live/preview_survey.php?id=" class="prvCmpCnt" data-type="falling-money">Falling Money</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd3432/preview_survey.php?id=" class="prvCmpCnt" data-type="gray-path">Gray Path</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd18478/preview_survey.php?id=" class="prvCmpCnt" data-type="lifescript-path">Life Script Path</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cdamx/preview_survey.php?id=" class="prvCmpCnt" data-type="red-path">Red Path</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_sm/preview_survey.php?id=" class="prvCmpCnt" data-type="survey-monster">Survey Monster</a></li>
	  		<li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd18463/preview_survey.php?id=" class="prvCmpCnt" data-type="zeeto-path">Zeeto Path</a></li>	  		
	  	</ul>
	</div>
	<?php
  		$attributes = [
  			'url' 					=> 'edit_campaign_long_content',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> 'Are you sure you want to edit the campaign\'s long content?',
  			'data-process' 			=> 'edit_campaign_long_content'
  		];
  	?>
  	<div class="row">
		{!! Form::open($attributes) !!}

		<div class="col-md-12 form-group">	
			<textarea id="cmpCnt-long-actual" class="hidden"></textarea>
			<span id="cmpCnt-form">
				{!! Form::textarea('content','',
					array('id' => 'cmpCnt-long-content', 'class' => 'form-control this_field', 'required' => 'true','rows' => '15', 'disabled' => 'true')) !!}
			</span>
		</div>	
		<div class="col-md-12 form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
	            
	        </div>
		</div>
		<div class="col-md-12 form-group cmpLngCnt-form-wrapper hidden">
			{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
			<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
			<button type="button" class="btn btn-default pull-right cancelCampaignLongContentEdit" style="margin-right: 5px;">Cancel</button>
		</div>
		{!! Form::close() !!}
	</div>
</div>