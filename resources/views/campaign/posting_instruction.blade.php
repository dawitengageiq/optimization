<div role="tabpanel" class="tab-pane" id="posting_instructions_tab">
	<div class="row">
		<div class="col-md-12">
			<button id="editPostingInstruction" class="btn btn-default pull-right" style="margin:0px 2px 10px;" data-config="show">
			  <span class="glyphicon glyphicon-pencil"></span>
			</button>

			<button id="previewPostingInstruction" class="btn btn-default pull-right" style="margin:0px 2px 10px;">
			  <span class="glyphicon glyphicon-eye-open"></span>
			</button>
		</div>
	
		<?php
	  		$attributes = [
	  			'url' 					=> 'edit_campaign_posting_instruction',
	  			'class'					=> 'this_form',
	  			'data-confirmation' 	=> 'Are you sure you want to edit the campaign\'s posting instruction?',
	  			'data-process' 			=> 'edit_campaign_posting_instruction'
	  		];
	  	?>
	  	{!! Form::open($attributes) !!}
  	
  		<div class="col-md-12 col-sm-12 form-group">
  			{!! Form::label('posting_instruction','Posting Instruction') !!}
			<textarea id="cmp-posting-instruction-actual" class="hidden"></textarea>
			<span id="cmpCnt-form">
				{!! Form::textarea('posting_instruction','',
					array('id' => 'cmp-posting-instruction', 'class' => 'form-control this_field', 'required' => 'true','rows' => '15')) !!}
			</span>
  		</div>
  		<div class="col-md-12 col-sm-12 form-group">
  			{!! Form::label('sample_code','Sample Code') !!}
  			<textarea id="cmp-sample-code-actual" class="hidden"></textarea>
			<span id="cmpCnt-form">
				{!! Form::textarea('sample_code','',
					array('id' => 'cmp-sample-code', 'class' => 'form-control this_field', 'required' => 'true','rows' => '5', 'disabled' => 'true')) !!}
			</span>
  		</div>
		<div class="col-md-12 form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
	            
	        </div>
		</div>
		<div class="col-md-12 form-group cmpPI-form-wrapper hidden">
			{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
			<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
			<button type="button" class="btn btn-default pull-right cancelCampaignPostingInstructionEdit" style="margin-right: 5px;">Cancel</button>
		</div>
		{!! Form::close() !!}

	</div>
</div>