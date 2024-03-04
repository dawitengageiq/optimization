{!! Form::hidden('standardRequiredFields', json_encode(config('constants.STANDARD_REQUIRED_FIELDS')) ,array('id' => 'standardRequiredFields')) !!}
{!! Form::hidden('standardLFRequiredFields', json_encode(config('constants.STANDARD_LF_REQUIRED_FIELDS')) ,array('id' => 'standardLFRequiredFields')) !!}
<div class="modal fade" id="cmpJsonFormBuilderModal" tabindex="-1" role="dialog" aria-labelledby="cmpJsonFormBuilderModal">
	<?php
  		$attributes = [
        'url'           => 'campaign_json_form_builder',
        'class'         => '',
        'data-confirmation'   => '',
        'data-process'      => 'campaign_json_form_builder',
        'id'          => 'campaign_json_form_builder'
      ];
  	?>
	{!! Form::open($attributes) !!}
	{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
  {!! Form::hidden('campaign_type', '',array('id' => 'jsonFormBuilderCampaignType','class' => 'this_field')) !!}
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Campaign JSON Form Builder: <strong id="cmpName"></strong></h4>
      </div>
      <div class="modal-body">
      	<div>
		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" class="active"><a href="#json_form_tab" aria-controls="json_form_tab" role="tab" data-toggle="tab">Form</a></li>
		    <li role="presentation" class="jsonFormBuilderCoregElement"><a href="#json_fields_tab" aria-controls="json_fields_tab" role="tab" data-toggle="tab">Fields</a></li>
		    <li role="presentation"><a href="#json_creatives_tab" aria-controls="json_creatives_tab" role="tab" data-toggle="tab">Creatives</a></li>
		    <li role="presentation"><a href="#json_customs_tab" aria-controls="json_customs_tab" role="tab" data-toggle="tab">Custom</a></li>
        
        <div class="dropdown pull-right">
          <button type="button" id="prvCampaignContent" class="btn btn-default" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-eye-open"></span><span class="caret"></span></button>
          <ul class="dropdown-menu" aria-labelledby="prvCampaignContent">
            <li><a href="{{ config('constants.JSON_PATH_URL') }}/preview_json.php?id=" class="prvJsonCmpCnt" data-type="falling-money">Falling Money</a></li>
            {{-- <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cdall/preview_stack.php?id=" class="prvCmpCnt" data-type="blue-path">Blue Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_election/preview_stack.php?id=" class="prvCmpCnt" data-type="election">Election</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live/preview_stack.php?id=" class="prvCmpCnt" data-type="falling-money">Falling Money</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd3432/preview_stack.php?id=" class="prvCmpCnt" data-type="gray-path">Gray Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd18478/preview_stack.php?id=" class="prvCmpCnt" data-type="lifescript-path">Life Script Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cdamx/preview_stack.php?id=" class="prvCmpCnt" data-type="red-path">Red Path</a></li>
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_sm/preview_stack.php?id=" class="prvCmpCnt" data-type="survey-monster">Survey Monster</a></li>     
            <li><a href="{{ config('constants.LEAD_REACTOR_PATH') }}dynamic_live_cd18463/preview_stack.php?id=" class="prvCmpCnt" data-type="zeeto-path">Zeeto Path</a></li> --}}
          </ul>
        </div>
		  </ul>

		  <!-- Tab panes -->
		  <div class="tab-content" style="margin-top: 10px;">
		    <div role="tabpanel" class="tab-pane active" id="json_form_tab">
		    	<div class="row">
		    		<div class="col-md-12 form-div jsonFormBuilderCoregElement">
		    			{!! Form::label('send_lead_to','Send Lead To') !!}
						{!! Form::select('send_lead_to', array(''=>'','lead_reactor' => 'Lead Reactor','lead_filter' => 'Lead Filter', 'custom' => 'Custom'), 'key', array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'jsonFormBuilderSendLeadSelect')) !!}
						{!! Form::text('custom_url','',
						array('class' => 'form-control this_field', 'placeholder' => 'Custom URL', 'style' => 'display:none', 'id' => 'jsonFormBuilderCustomUrlInput')) !!}
		    		</div>
            <div class="col-md-12 form-div jsonFormBuilderLinkoutElement">
              {!! Form::label('redirect_link','Redirect Link') !!}
              {!! Form::text('redirect_link','',
                array('class' => 'form-control this_field', 'required' => 'true')) !!}
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
		    <div role="tabpanel" class="tab-pane" id="json_fields_tab">
  				<button id="addFormFieldJson" class="btn btn-default pull-right form_builder_btn" type="button">
  				  <span class="glyphicon glyphicon-plus"></span>
  				</button>
  				<table id="jsonFormFieldsTable" class="table table-striped">
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
		    <div role="tabpanel" class="tab-pane" id="json_creatives_tab">
          <table id="json-campaign-stack-creative-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table table-wrap">
            <thead>
              <tr>
                <th><span style="display:block">ID</span> [Weight]</th>
                <th>Description</th>
                <th>Image</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
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
		    <div role="tabpanel" class="tab-pane" id="json_customs_tab">
          <div class="form-group">
            {!! Form::label('custom_script','Custom Script') !!}
					  {!! Form::textarea('custom_script','',
						  array('id' => 'custom_script','class' => 'form-control this_field', 'rows' => '4')) !!}
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
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button id="jsonConfigAutomationSubmit" type="submit" class="btn btn-primary form_builder_btn">Save</button>
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

<div id="jsonFormFieldModal" class="modal fade" tabindex="-1" role="dialog">
  <?php
    $attributes = [
      'url'           => 'json_form_builder_field',
      'class'         => '',
      'data-confirmation'   => '',
      'data-process'      => 'json_form_builder_field',
      'id'          => 'json_form_builder_field'
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
            {!! Form::select('field_type', [null=>''] + config('constants.FIELD_TYPES'), '', array('class' => 'form-control this_field', 'required' => 'true', 'id' => 'jsonFormBuilderFieldTypeSelect')) !!}
          </div>
    			<div class="col-md-12 form-div json_input_field_group">
    				{!! Form::label('label','Label') !!}
            {!! Form::textarea('label','',
              array('id' => 'label','class' => 'form-control this_field cmpFB_item', 'rows' => '1')) !!}
    			</div>
          <div class="col-md-12 form-div json_input_field_group">
            {!! Form::label('name','Name') !!}
            {!! Form::text('name','',
              array('class' => 'form-control this_field', 'required' => 'true')) !!}
          </div>
    			<div class="col-md-12 json_value_group_one json_input_field_group">
    				{!! Form::label('value','Value') !!}
            <a href="{{ url('admin/shortcodes') }}" target="_blank"><i class="fa fa-question-circle"></i></a>
    			</div>
    			<div class="col-md-5 form-div json_value_group_one json_input_field_group">
    				{!! Form::select('value_select', [null=>''] + $short_codes, '', array('class' => 'form-control this_field')) !!}
    			</div>
    			<div class="col-md-2 text-center json_value_group_one json_input_field_group">
              <strong>Or</strong>
          </div>
          <div class="col-md-5 form-div json_value_group_one json_input_field_group">
    				{!! Form::text('value_input','',
    					array('class' => 'form-control this_field', 'placeholder' => 'Value')) !!}
    			</div>
    			<div class="col-md-12 form-div json_value_group_two json_input_field_group" style="display:none">
    				<button id="jsonRangeValueBtn" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#jsonRangeValueCollapse" style="margin-right: 5px;">Range</button>
            <button id="jsonUploadFieldValuesBtn" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#jsonUploadValueCollapse" style="margin-right: 5px;"><span class="glyphicon glyphicon-cloud-upload"></span></button>
            <button id="addJsonFieldValueBtn" class="btn btn-default pull-right" type="button" style="margin-right: 5px;"><span class="glyphicon glyphicon-plus"></span></button>
            <br style="clear:both">
            <div class="row">
              <div class="col-md-6" style="float: right;margin-top: 5px;">
                <div class="collapse" id="jsonRangeValueCollapse">
                  <div class="well">
                    <div class="row">
                      <div class="col-md-6">
                        {!! Form::label('json_min_range_value','First Value') !!}
                        {!! Form::text('json_min_range_value','',
                          array('class' => 'form-control this_field')) !!}
                      </div>
                      <div class="col-md-6">
                        {!! Form::label('json_max_range_value','Second Value') !!}
                        {!! Form::text('json_max_range_value','',
                          array('class' => 'form-control this_field')) !!}
                      </div>
                      <div class="col-md-12">
                        <button id="jsonAddRangeValuesBtn" type="button" class="btn btn-primary pull-right" style="margin-top: 10px;">Add</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="collapse" id="jsonUploadValueCollapse">
                  <div class="well">
                    <div class="row">
                      <div class="col-md-12">
                        {!! Form::label('json_upload_file_value','File') !!}
                        {!! Form::file('json_upload_file_value', ['id' => 'json_upload_file_value', 'class' => 'form-control', 'accept' => 'text/plain']) !!}
                        <p class="help-block">Upload a text file.</p>
                      </div>
                      <div class="col-md-12">
                        <button id="jsonUploadFileValuesBtn"  type="button" class="btn btn-primary pull-right" style="margin-top: 10px;">Upload</button>
                      </div>
                    </div>
                  </div>
                </div>
                 
              </div>
            </div>
            <table id="jsonFieldValueTable" class="table table-striped" style="margin-bottom: 0px !important">
              <thead>
                  <tr>
                    <th></th>
                    <th><span class="glyphicon glyphicon-star" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Default Value"></th>
                    <th>Value</th>
                    <th>Display</th>
                    <th><span class="glyphicon glyphicon-asterisk" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Accepted Value"></span></th>
                    <th>
                      <button type="button" class="clearJsonFieldValueTable btn btn-default pull-right">
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
                            <button type="button" class="clearJsonFieldValueTable btn btn-default pull-right">
                              <span class="glyphicon glyphicon-remove"></span>
                            </button>
                      </th>
                  </tr>
              </tfoot>
            </table>
    			</div>
    			<div class="col-md-12 form-div json_input_field_group">
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
          <div class="col-md-12 form-div json_article_field_group" style="display:none">
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