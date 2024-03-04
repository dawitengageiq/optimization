<div role="tabpanel" class="tab-pane {{$configActive}}" id="config_tab">

	{!! Form::label('config_title','Campaign Configuration', array('style' => 'padding-top:7px')) !!}

	<button id="editCmpConfig" class="btn btn-default pull-right" type="button" style="margin:0px 2px 10px;" data-config="show">
		<span class="glyphicon glyphicon-pencil"></span>
	</button>

	<button id="cmpConfigAutomationBtn" class="btn btn-default pull-right" type="button">
		<span class="glyphicon glyphicon-cog"></span>
	</button>

	<?php
  		$attributes = [
  			'url' 					=> 'edit_campaign_config',
  			'class'					=> 'this_form',
  			'data-confirmation' 	=> '',
  			'data-process' 			=> 'edit_campaign_config'
  		];
  	?>
	{!! Form::open($attributes) !!}
	{!! Form::hidden('this_campaign', '',array('id' => 'this_campaign','class' => 'this_field this_campaign')) !!}
	<div class="row">
		<div class="col-md-12">
			<table class="table table-bordered table-wrap">
			  <tr>
			  	<th style="width: 20%;">
			  		{!! Form::label('post_url','Post Url') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-url" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-url-txt" class="hidden cmpCfg-form">
			  			{!! Form::text('post_url','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
			  		</span>
			  	</td>
			  </tr>
			   <tr>
			  	<th>
			  		{!! Form::label('post_header','Post Header') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-hdr" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-hdr-txt" class="cmpCfg-form hidden">
			  			{!! Form::textarea('post_header','',
							array('class' => 'form-control this_field', 'required' => 'true','rows' => '3')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('post_data','Post Data') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-dta" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-dta-txt" class="cmpCfg-form hidden">
			  			{!! Form::textarea('post_data','',
							array('class' => 'form-control this_field ', 'required' => 'true','rows' => '3')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('post_data_fixed_value','Post Data Fixed Value') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-dta-fv" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-dta-fv-txt" class="cmpCfg-form hidden">
			  			{!! Form::textarea('post_data_fixed_value','',
							array('class' => 'form-control this_field ', 'required' => 'true','rows' => '3')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('post_data_map','Post Data Map') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-map" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-map-txt" class="cmpCfg-form hidden">
			  			{!! Form::textarea('post_data_map','',
							array('class' => 'form-control this_field', 'required' => 'true','rows' => '3')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>{!! Form::label('post_method','Post Method') !!}</th>
			  	<td>
			  		<span id="cmpCfg-mtd" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-mtd-txt" class="cmpCfg-form hidden">
			  			{!! Form::select('post_method', array('POST' => 'POST','GET' => 'GET'), 'key', array('class' => 'form-control this_field', 'required' => 'true')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('post_success','Post Success') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-scs" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-scs-txt" class="cmpCfg-form hidden">
			  			{!! Form::text('post_success','',
							array('class' => 'form-control this_field', 'required' => 'true')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('ping_url','Ping Url') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-purl" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-purl-txt" class="cmpCfg-form hidden">
			  			{!! Form::text('ping_url','',
							array('class' => 'form-control this_field')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('ping_success','Ping Success') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-pscs" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-pscs-txt" class="cmpCfg-form hidden">
			  			{!! Form::text('ping_success','',
							array('class' => 'form-control this_field')) !!}
			  		</span>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('ftp_sent','Send Through FTP') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-ftps" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-ftps-txt" class="cmpCfg-form hidden">
			  			{!! Form::checkbox('ftp_sent',1, false, ['id' => 'if_ftp_sent','data-toggle' => 'toggle', 'data-off' => 'NO', 'data-on' => 'YES']) !!}
			  		</span>
			  		<br>
			  		<table class="table table-bordered table-wrap" style="margin-top:15px">
			  			<tr class="ftpSentTrueClass">
						  	<th>{!! Form::label('ftp_protocol','FTP Protocol') !!}</th>
						  	<td>
						  		<span id="cmpCfg-ftpp" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-ftpp-txt" class="cmpCfg-form hidden">
						  			{!! Form::select('ftp_protocol', [null => ''] + config('constants.FTP_PROTOCOL_TYPES'), '', array('class' => 'form-control this_field cmpCfgFtp-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('ftp_host','FTP Host') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-ftph" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-ftph-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('ftp_host','',
										array('class' => 'form-control this_field cmpCfgFtp-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('ftp_port','FTP Port') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-ftppt" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-ftppt-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('ftp_port','',
										array('class' => 'form-control this_field cmpCfgFtp-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('ftp_username','FTP Username') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-ftpu" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-ftpu-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('ftp_username','',
										array('class' => 'form-control this_field cmpCfgFtp-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('ftp_password','FTP Password') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-ftppw" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-ftppw-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('ftp_password','',
										array('class' => 'form-control this_field cmpCfgFtp-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('ftp_timeout','FTP Timeout') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-ftpto" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-ftpto-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('ftp_timeout','',
										array('class' => 'form-control this_field cmpCfgFtp-fld', 'placeholder' => 'in seconds')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
							<th>
								{!! Form::label('ftp_directory','FTP Directory') !!}
							</th>
							<td>
								<span id="cmpCfg-ftpdirectory" class="cmpCfg-dsply"></span>
								<span id="cmpCfg-ftpdirectory-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('ftp_directory','', ['class' => 'form-control this_field cmpCfgFtp-fld', 'placeholder' => 'directory']) !!}
						  		</span>
							</td>
						</tr>
			  		</table>
			  	</td>
			  </tr>
			  <tr>
			  	<th>
			  		{!! Form::label('email_sent','Send Through Email') !!}
			  	</th>
			  	<td>
			  		<span id="cmpCfg-email" class="cmpCfg-dsply"></span>
			  		<span id="cmpCfg-email-txt" class="cmpCfg-form hidden">
			  			{!! Form::checkbox('email_sent',1, false, ['id' => 'if_email_sent','data-toggle' => 'toggle', 'data-off' => 'NO', 'data-on' => 'YES']) !!}
			  		</span>
			  		<br>
			  		<table class="table table-bordered table-wrap" style="margin-top:15px">
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('email_to','Email To') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-emailTo" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-emailTo-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('email_to','',
										array('class' => 'form-control this_field cmpCfgSTE-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('email_title','Email Subject') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-emailTitle" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-emailTitle-txt" class="cmpCfg-form hidden">
						  			{!! Form::text('email_title','',
										array('class' => 'form-control this_field cmpCfgSTE-fld')) !!}
						  		</span>
						  	</td>
						</tr>
						<tr class="ftpSentTrueClass">
						  	<th>
						  		{!! Form::label('email_body','Email Body') !!}
						  	</th>
						  	<td>
						  		<span id="cmpCfg-emailBody" class="cmpCfg-dsply"></span>
						  		<span id="cmpCfg-emailBody-txt" class="cmpCfg-form hidden">
						  			{!! Form::textarea('email_body','',
										array('class' => 'form-control this_field cmpCfgSTE-fld')) !!}
						  		</span>
						  	</td>
						</tr>
			  		</table>
			  	</td>
			  </tr>
			</table>
		</div>
		<div class="col-md-12 form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
                
            </div>
		</div>
		<div id="campConfigDiv" class="col-md-12 hidden">
			<button type="submit" class="btn btn-primary this_modal_submit pull-right">Save</button>
			<button type="button" class="btn btn-default pull-right cancelCmpConfigEdit" style="margin-right: 5px;">Cancel</button>
		</div>
	</div>
	{!! Form::close() !!}

</div>