{!! Form::hidden('standardRequiredFields', json_encode(config('constants.STANDARD_REQUIRED_FIELDS')) ,array('id' => 'standardRequiredFields')) !!}
{!! Form::hidden('standardLFRequiredFields', json_encode(config('constants.STANDARD_LF_REQUIRED_FIELDS')) ,array('id' => 'standardLFRequiredFields')) !!}
<div class="modal fade" id="cmpFormBuilderModal" tabindex="-1" role="dialog" aria-labelledby="cmpFormBuilderModal">
	<?php
  		$attributes = [
  			'url' 					=> 'campaign_form_builder',
  			'class'					=> '',
  			'data-confirmation' 	=> '',
  			'data-process' 			=> 'campaign_form_builder',
  			'id'					=> 'campaign_form_builder'
  		];
  	?>
	{!! Form::open($attributes) !!}
	{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
  {!! Form::hidden('campaign_type', '',array('id' => 'formBuilderCampaignType','class' => 'this_field')) !!}
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Campaign Form Builder: <strong id="form-builder-campaign"></strong></h4>
      </div>
      <div class="modal-body">
      	<div>
		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" class="active"><a href="#form_tab" aria-controls="form_tab" role="tab" data-toggle="tab">Form</a></li>
		    <li role="presentation" class="formBuilderCoregElement"><a href="#fields_tab" aria-controls="fields_tab" role="tab" data-toggle="tab">Fields</a></li>
		    <li role="presentation"><a href="#creatives_tab" aria-controls="creatives_tab" role="tab" data-toggle="tab">Creatives</a></li>
		    <li role="presentation"><a href="#customs_tab" aria-controls="customs_tab" role="tab" data-toggle="tab">Custom</a></li>
		    <li role="presentation"><a href="#code_tab" aria-controls="code_tab" role="tab" data-toggle="tab">Code</a></li>
        
        <div class="dropdown pull-right">
          <button type="button" id="prvCampaignContent" class="btn btn-default" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-eye-open"></span><span class="caret"></span></button>
          <ul class="dropdown-menu" aria-labelledby="prvCampaignContent">
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cdall/preview_stack.php?id=" class="prvCmpCnt" data-type="blue-path">Blue Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_election/preview_stack.php?id=" class="prvCmpCnt" data-type="election">Election</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live/preview_stack.php?id=" class="prvCmpCnt" data-type="falling-money">Falling Money</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd3432/preview_stack.php?id=" class="prvCmpCnt" data-type="gray-path">Gray Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd18478/preview_stack.php?id=" class="prvCmpCnt" data-type="lifescript-path">Life Script Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cdamx/preview_stack.php?id=" class="prvCmpCnt" data-type="red-path">Red Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_sm/preview_stack.php?id=" class="prvCmpCnt" data-type="survey-monster">Survey Monster</a></li>     
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd18463/preview_stack.php?id=" class="prvCmpCnt" data-type="zeeto-path">Zeeto Path</a></li>
          </ul>
        </div>
		  </ul>

		  <!-- Tab panes -->
		  <div class="tab-content" style="margin-top: 10px;">
		    <div role="tabpanel" class="tab-pane active" id="form_tab">
		    	<div class="row">
		    		<div class="col-md-12 form-div formBuilderCoregElement">
		    			{!! Form::label('send_lead_to','Send Lead To') !!}
						{!! Form::select('send_lead_to', array(''=>'','lead_reactor' => 'Lead Reactor','lead_filter' => 'Lead Filter', 'custom' => 'Custom'), 'key', array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'formBuilderSendLeadSelect')) !!}
						{!! Form::text('custom_url','',
						array('class' => 'form-control this_field', 'placeholder' => 'Custom URL', 'style' => 'display:none', 'id' => 'formBuilderCustomUrlInput')) !!}
		    		</div>
            <div class="col-md-12 form-div formBuilderLinkoutElement">
              {!! Form::label('redirect_link','Redirect Link') !!}
              <div class="row">
                <div class="col-md-11">
                  {!! Form::text('redirect_link','',
                    array('class' => 'form-control this_field', 'required' => 'true')) !!}
                </div>
                <div class="col-md-1">
                  <button id="loa-btn" class="btn btn-default pull-right" type="button">
                    <span class="glyphicon glyphicon-cog"></span>
                  </button>
                </div>
              </div>
            </div>
		    		<div class="col-md-12 form-div">
		    			{!! Form::label('form_id','Form ID') !!}
  						{!! Form::text('form_id','',
  						array('class' => 'form-control this_field')) !!}
		    		</div>
		    		<div class="col-md-12 form-div">
		    			{!! Form::label('form_class','Form Class') !!}
  						{!! Form::text('form_class','',
  						array('class' => 'form-control this_field')) !!}
		    		</div>
		    	</div>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="fields_tab">
  				<button id="addFormField" class="btn btn-default pull-right form_builder_btn" type="button">
  				  <span class="glyphicon glyphicon-plus"></span>
  				</button>
  				<table id="formFieldsTable" class="table table-striped">
  					<thead>
  						<tr>
                <th></th>
  							<th>Name/Label</th>
  							<th>Type</th>
                <th>Value</th>
  							<th></th>
  						</tr>
  					</thead>
  					<tbody>
  					</tbody>
  					<tfoot>
  						<tr>
                <th></th>
  							<th>Name/Label</th>
  							<th>Type</th>
                <th>Value</th>
  							<th></th>
  						</tr>
  					</tfoot>
  				</table>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="creatives_tab">
          <table id="campaign-stack-creative-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table table-wrap">
            <thead>
              <tr>
                <th><span style="display:block">ID</span> [Weight]</th>
                <th>Description</th>
                <th>Image</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <input name="spinner" value="" class="form-control spinner">
                </td>
                <td>
                  <textarea id="stackCreativeDesc" class="stackCreativeDesc form-control this_field" required="true" rows="3" name="content" cols="5"></textarea>
                </td>
                <td>
                  <div class="cmpCrtv-img-wrp">
                    <img src="http://leadreactor.engageiq.com/images/gallery/giftboxyellow.png" class="gal-img"/>
                  </div>
                </td>
                <td>
                  <button id="cmpCrtv-1-edit-button" class="btn btn-default editCampaignFilterGroup hidden" type="button" data-id="1" disabled="true"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                  <button id="cmpCrtv-1-delete-button" class="btn btn-default deleteCampaignFilterGroup" type="button" data-id="1"><span class="glyphicon glyphicon-trash"></span></button>
                </td>
              </tr>
              <tr>
                <td>
                  <input name="spinner" value="" class="form-control spinner">
                </td>
                <td>
                  <textarea class="stackCreativeDesc form-control this_field" required="true" rows="3" name="content" cols="5"></textarea>
                </td>
                <td>
                  <div class="cmpCrtv-img-wrp">
                    <img src="http://localhost:1234/images/gallery/100guaranteed.png" class="gal-img"> 
                  </div>
                </td>
                <td>
                  <button id="cfg-1-edit-button" class="btn btn-default editCampaignFilterGroup" type="button" data-id="1"><span class="glyphicon glyphicon-pencil"></span></button>
                  <button id="cfg-1-delete-button" class="btn btn-default deleteCampaignFilterGroup" type="button" data-id="1"><span class="glyphicon glyphicon-trash"></span></button>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <th>ID [Weight]</th>
                <th>Description</th>
                <th>Image</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
		    <div role="tabpanel" class="tab-pane" id="customs_tab">
		    	<div class="form-group">
		    		{!! Form::label('custom_css','Custom CSS') !!}
					  {!! Form::textarea('custom_css','',
						  array('id' => 'custom_css','class' => 'form-control this_field', 'rows' => '4')) !!}
          </div>
          <div class="form-group">
            {!! Form::label('custom_js','Custom JS') !!}
					  {!! Form::textarea('custom_js','',
						  array('id' => 'custom_js','class' => 'form-control this_field', 'rows' => '4')) !!}
          </div>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="code_tab">
		    	{!! Form::label('stack_code','Code') !!}
				  {!! Form::textarea('stack_code','',
					array('id' => 'stack_code','class' => 'form-control this_field', 'rows' => '15', 'readonly')) !!}
		    </div>
		  </div>

		</div>

		<div class="form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
                
            </div>
		</div>	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button id="cmpConfigAutomationSubmit" type="submit" class="btn btn-primary form_builder_btn">Save</button>
      </div>
    </div>
  </div>
  {!! Form::close() !!}
</div>

<?php 
$short_codes = config('constants.SHORT_CODES');
// asort($short_codes);
// foreach($short_codes as $sc) {
// 	$short_code[$sc] = $sc;
// }
?>

<div id="cmpFormFieldModal" class="modal fade" tabindex="-1" role="dialog">
  <?php
    $attributes = [
      'url'           => 'form_builder_field',
      'class'         => '',
      'data-confirmation'   => '',
      'data-process'      => 'form_builder_field',
      'id'          => 'form_builder_field'
    ];
  ?>
  {!! Form::open($attributes) !!}
  {!! Form::hidden('field_id', '',array('id' => 'field_id','class' => 'this_field')) !!}
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add Field</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 form-div">
            {!! Form::label('field_type','Field Type') !!}
            {!! Form::select('field_type', [null=>''] + config('constants.FIELD_TYPES'), '', array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'formBuilderFieldTypeSelect')) !!}
          </div>
    			<div class="col-md-12 form-div input_field_group">
    				{!! Form::label('label','Label') !!}
            {!! Form::textarea('label','',
              array('id' => 'label','class' => 'form-control this_field cmpFB_item', 'rows' => '1')) !!}
    			</div>
          <div class="col-md-12 form-div input_field_group">
            {!! Form::label('name','Name') !!}
            {!! Form::text('name','',
              array('class' => 'form-control this_field', 'required' => 'true')) !!}
          </div>
    			<div class="col-md-12 value_group_one input_field_group">
    				{!! Form::label('value','Value') !!}
            <a href="{{ url('admin/shortcodes') }}" target="_blank"><i class="fa fa-question-circle"></i></a>
    			</div>
    			<div class="col-md-5 form-div value_group_one input_field_group">
    				{!! Form::select('value_select', [null=>''] + $short_codes, '', array('class' => 'form-control this_field')) !!}
    			</div>
    			<div class="col-md-2 text-center value_group_one input_field_group">
              <strong>Or</strong>
          </div>
          <div class="col-md-5 form-div value_group_one input_field_group">
    				{!! Form::text('value_input','',
    					array('class' => 'form-control this_field', 'placeholder' => 'Value')) !!}
    			</div>
    			<div class="col-md-12 form-div value_group_two input_field_group" style="display:none">
    				<button id="rangeValueBtn" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#rangeValueCollapse" style="margin-right: 5px;">Range</button>
            <button id="uploadFieldValuesBtn" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#uploadValueCollapse" style="margin-right: 5px;"><span class="glyphicon glyphicon-cloud-upload"></span></button>
            <button id="addFieldValueBtn" class="btn btn-default pull-right" type="button" style="margin-right: 5px;"><span class="glyphicon glyphicon-plus"></span></button>
            <br style="clear:both">
            <div class="row">
              <div class="col-md-6" style="float: right;margin-top: 5px;">
                <div class="collapse" id="rangeValueCollapse">
                  <div class="well">
                    <div class="row">
                      <div class="col-md-6">
                        {!! Form::label('min_range_value','First Value') !!}
                        {!! Form::text('min_range_value','',
                          array('class' => 'form-control this_field')) !!}
                      </div>
                      <div class="col-md-6">
                        {!! Form::label('max_range_value','Second Value') !!}
                        {!! Form::text('max_range_value','',
                          array('class' => 'form-control this_field')) !!}
                      </div>
                      <div class="col-md-12">
                        <button id="addRangeValuesBtn" type="button" class="btn btn-primary pull-right" style="margin-top: 10px;">Add</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="collapse" id="uploadValueCollapse">
                  <div class="well">
                    <div class="row">
                      <div class="col-md-12">
                        {!! Form::label('upload_file_value','File') !!}

                        {{-- {!! Form::file('upload_file_value', ['id' => 'upload_file_value', 'class' => 'form-control', 'accept' => 'text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']) !!} --}}
                        {!! Form::file('upload_file_value', ['id' => 'upload_file_value', 'class' => 'form-control', 'accept' => 'text/plain']) !!}
                        <p class="help-block">Upload a text file.</p>
                      </div>
                      <div class="col-md-12">
                        <button id="uploadFileValuesBtn"  type="button" class="btn btn-primary pull-right" style="margin-top: 10px;">Upload</button>
                      </div>
                    </div>
                  </div>
                </div>
                 
              </div>
            </div>
            <table id="fieldValueTable" class="table table-striped" style="margin-bottom: 0px !important">
              <thead>
                  <tr>
                    <th></th>
                    <th><span class="glyphicon glyphicon-star" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Default Value"></th>
                    <th>Value</th>
                    <th>Display</th>
                    <th><span class="glyphicon glyphicon-asterisk" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Accepted Value"></span></th>
                    <th>
                      <button type="button" class="clearFieldValueTable btn btn-default pull-right">
                        <span class="glyphicon glyphicon-remove"></span>
                      </button>
                    </th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
                   <tr>
                      <th></th>
                      <th>
                        <button type="button" class="clearDefaultValRadio btn btn-default btn-xs">
                          <i class="fa fa-circle-o" aria-hidden="true"></i>
                        </button>
                      </th>
                      <th>Value</th>
                      <th>Display</th>
                      <th><input type="checkbox" class="selectAcceptedValues" value="1"></td></th>
                      <th>
                            <button type="button" class="clearFieldValueTable btn btn-default pull-right">
                              <span class="glyphicon glyphicon-remove"></span>
                            </button>
                      </th>
                  </tr>
              </tfoot>
            </table>
    			</div>
    			<div class="col-md-12 form-div input_field_group">
            {!! Form::label('validation','Validation') !!}
            <div class="row">
              <div class="col-md-4">
                  <div class="checkbox">
                    <label>
                    	{!! Form::checkbox('validation[]', 'required', array('checked')); !!}
                      Required
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'email'); !!}
                      Email
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'phoneUS'); !!}
                      US Phone
                    </label>
                  </div>
                  <!-- <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'zip'); !!}
                      Zip Code
                    </label>
                  </div> -->
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'date'); !!}
                      Date
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'url'); !!}
                      URL
                    </label>
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'equalTo'); !!}
                      Equal To: 
                      {!! Form::text('equal_to_value','',
    		             array('class' => 'this_field', 'placeholder' => 'ID', 'style' => 'width: 100px;')) !!}
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'alphaSpace'); !!}
                      AlphaSpace only
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'number'); !!}
                      Numeric only
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'digits'); !!}
                      Digits only
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'minWordCount'); !!}
                      Minimum Word Count: 
                      {!! Form::text('min_word_count_value','',
                     array('class' => 'this_field', 'placeholder' => 'Min', 'style' => 'width: 50px;')) !!}
                    </label>
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'alphaNumeric'); !!}
                      AlphaNumeric only
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'range'); !!}
                      Range: 
                      {!! Form::text('range_min_value','',
        							array('class' => 'this_field', 'placeholder' => 'Min', 'style' => 'width: 50px;')) !!}
        							{!! Form::text('range_max_value','',
        							array('class' => 'this_field', 'placeholder' => 'Max', 'style' => 'width: 50px;')) !!}
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'min'); !!}
                      Minimum Value: 
                      {!! Form::text('min_value','',
    		             array('class' => 'this_field', 'placeholder' => 'Min', 'style' => 'width: 50px;')) !!}
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'max'); !!}
                      Maximum Value: 
                      {!! Form::text('max_value','',
    		             array('class' => 'this_field', 'placeholder' => 'Max', 'style' => 'width: 50px;')) !!}
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      {!! Form::checkbox('validation[]', 'zip'); !!}
                      U.S Zip
                    </label>
                  </div>
              </div>
            </div>
          </div>
          <div class="col-md-12 form-div article_field_group">
            {!! Form::label('article','Article') !!}
            {!! Form::textarea('article','',
              array('id' => 'article','class' => 'form-control this_field cmpFB_item', 'rows' => '3')) !!}
          </div>
          <div class="col-md-6 form-div">
            {!! Form::label('id','ID') !!}
    				{!! Form::text('id','',
    					array('class' => 'form-control this_field')) !!}
          </div>
          <div class="col-md-6 form-div">
            {!! Form::label('class','Class') !!}
    				{!! Form::text('class','',
    					array('class' => 'form-control this_field')) !!}
          </div>
		    </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary form_builder_btn">Save</button>
      </div>
    </div>
  </div>
  {!! Form::close() !!}
</div>