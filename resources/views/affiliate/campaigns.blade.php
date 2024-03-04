@extends('affiliate.master')

@section('campaigns-active') active @stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">

<link href="{{ asset('bower_components/highlight/styles/default.css') }}" rel="stylesheet">
@stop

@section('content')
{!! Form::hidden('eiq_iframe_id', env('EIQ_IFRAME_ID',0),array('id' => 'eiq_iframe_id')) !!}

<!-- MORE DETAILS MODAL -->
<div id="campaignMoreDetailsModal" class="modal fade" role="dialog">
  	<div class="modal-dialog modal-lg">
	  <div class="modal-content campaign-modal-content">
	    <div class="modal-body">
	      <button type="button" class="close" data-dismiss="modal" title="Close">&times;</button>
	      <div class="row">
	        <div class="col-md-4">
	          <div class="campaign-col-name">
	            <h4 class="campaign-title">Campaign Name:</h4>
	            <p id="campaign-name" class="campaign-desc">AccountNow Coreg</p>
	            <h4 class="campaign-title">Advertiser Name:</h4>
	            <p id="advertiser-name" class="campaign-desc">Sample Advertiser Name</p>
	            <div class="campaign-image">
	              <img id="campaign-image" width="115px" class="campaign-desc" src="{{ URL::asset('images/logos/engageiq-logo.png') }}" />
	            </div>
	            <strong style="color: #2f89bb;">Campaign Image</strong>
	          </div>
	        </div>
	        <div class="col-md-8">
	          <div class="row">
	            <div class="col-md-12">
	              <h4 class="campaign-title">Campaign Description</h4>
	              <div id="campaign-full-description" class="campaign-desc campaign-description">
	                <p>
	                  Lorem ipsum dolor sit amet, augue vidisse ea eam. Inani nostrum necessitatibus ut quo, no integre eruditi sea. Nostro ceteros intellegat sea an. Has ex legere graeci voluptatibus, eam delicata senserit no. Mea ex altera option deseruisse, quo dicam commune petentium an.
	                </p>
	              </div>
	            </div>
	          </div>
	        </div>
	      </div>
	    </div>
	  </div>
  	</div>
</div>
<!-- END MORE DETAILS MODAL -->

<div id="applyToRunMessageModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #0159A3;color: #fff;text-align: center;">
        <h4 class="modal-title">THANK YOU FOR APPLYING</h4>
      </div>
      <div class="modal-body">
        <h5>We will notify you within 24 hours about the status of your application VIA voice call or email.</h5>
      </div>
    </div>
  </div>
</div>

<!-- GET CODE MODAL -->
<div id="campaignGetCodeModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="close" data-dismiss="modal" title="Close">&times;</button>
          <ul id="getACodeTabList" class="nav nav-tabs">
            <li><a data-toggle="tab" href="#posting-tab">Posting Instructions</a></li>
            <li class="active"><a data-toggle="tab" href="#get-tab">Get a Code</a></li>
          </ul>
          <div class="tab-content">
            <div id="posting-tab" class="tab-pane fade">
	            <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" data-interval="false">
				  <!-- Wrapper for slides -->
				  <div class="carousel-inner" role="listbox">
				    <div class="item active">
				      	<div class="posting-instructions-div1">
			              <h3>Campaign Setup Instructions</h3>
			              <p>1) Campaign Description:</p><br />
			              <p>Be an Instant Winner with the Toluna opinion panel community and earn $1 to $5 each time you qualify and complete a survey! Also, each survey you attempt or complete (after registration) will enter you to win $5,000 cash! All information you provide will be kept strictly confidential and will only be used for research purposes, never for sales or marketing. Join now and make your opinion count!</p>
			              <p>2) Campaign Creatives:</p><br />
			              <p>
			              234x60 Creative: http://affiliates.engageiq.com/42/35/0/<br />
			              768x90 Creative: http://affiliates.engageiq.com/42/36/0/
			              </p><br /><br />
			              <p>3) POST URL Example:</p><br />
			              <p>
			              Method: GET or POST <br /><br />
			              URL: http://204.232.224.98:9280/sendLead/sendLead <br /><br />
			              URL example:<br />
			              http://engageiq.com/sendLead/sendLead?sub_id=EID1&program_id=95&
			              </p><br />

			              <h2>IMPORTANT:</h2>
			              <p>
			              <ol>
			                <li>Please remember to insert correct CD# which is unique to you. Ex. CD1</li>
			                <li>When sending test leads, please use the email test_CD#_test#@yourdomain.com, where # is the test iteration e.e.: the first test would be test_CD1@yourdomain.com, the second test_CD1_test2@yourdomain.com and so on.</li>
			              </ol>
			              </p>
			            </div>

			            <button class="btn btn-primary pull-right" href="#carousel-example-generic" role="button" data-slide="next">
			            	<i class="fa fa-arrow-right" aria-hidden="true" ></i> NEXT
			            </button>
				    </div>
				    <div class="item">
				    	<!-- STANDARD FIELDS -->
				    	<h3>Standard Fields</h3>
	                  	<div>
		                    <table class="table">
		                      <thead>
		                        <tr>
		                          <th class="column-header">Field Description</th>
		                          <th class="column-header">Field Name & Accepted Value Format</th>
		                          <th class="column-header">Additional Info</th>
		                        </tr>
		                      </thead>
		                      <tbody>
		                        <tr>
		                          <td>Consumer Identifier Fields</td>
		                          <td>
		                          Lorem ipsum dolor sit amet, qui te legimus iracundia molestiae, ex sed debet inermis, vitae gloriatur tincidunt per ne. Graeco reformidans consequuntur ad sed. Autem eripuit propriae usu eu, eu sonet semper vix. Ne doming nusquam nam, ad delenit praesent voluptatum qui. Quot ubique argumentum ei pri, eum id ignota volumus minimum. Audiam animal no duo, qui ut illud ullum dicant.</td>
		                          <td>All fields are required.</td>
		                        </tr>
		                        <tr>
		                          <td>Consumer Identifier Fields</td>
		                          <td>
		                          Lorem ipsum dolor sit amet, qui te legimus iracundia molestiae, ex sed debet inermis, vitae gloriatur tincidunt per ne. Graeco reformidans consequuntur ad sed. Autem eripuit propriae usu eu, eu sonet semper vix. Ne doming nusquam nam, ad delenit praesent voluptatum qui. Quot ubique argumentum ei pri, eum id ignota volumus minimum. Audiam animal no duo, qui ut illud ullum dicant.</td>
		                          <td>Required Field.</td>
		                        </tr>
		                        <tr>
		                          <td>Response</td>
		                          <td>Lead Successfully Received / Lead Received Successfully.</td>
		                          <td>Lead received and accepted in system.</td>
		                        </tr>
		                      </tbody>
		                    </table>
	                  	</div>

				    	<button class="btn btn-primary" href="#carousel-example-generic" role="button" data-slide="prev">
			            	<i class="fa fa-arrow-left" aria-hidden="true" ></i> PREVIOUS
			            </button>

			            <button class="btn btn-primary pull-right" href="#carousel-example-generic" role="button" data-slide="next">
			            	<i class="fa fa-arrow-right" aria-hidden="true" ></i> NEXT
			            </button>
				    </div>
				    <div class="item">
				    	<!-- FOR MORE INFORMATION -->
	                  <div class="row">
	                    <div class="col-sm-4 col-md-4">
	                      <div class="profile-picture">
	                        <!-- <img src="./images/image-profile.png" /> -->
	                      </div>
	                    </div>
	                    <div class="col-sm-8 col-md-8 profile-about">
	                      <h4>DEAL: Toluna-coreg</h4>
	                      <p>Setup Help Contact</p>
	                      <p>Ashton Kutcher: joval@engageiq.com</p>
	                      <p>Skype: engageiqjoval</p>
	                    </div>
	                  </div>
				    	<button class="btn btn-primary" href="#carousel-example-generic" role="button" data-slide="prev">
			            	<i class="fa fa-arrow-left" aria-hidden="true" ></i> PREVIOUS
			            </button>
				    </div>
				  </div>

				  <!-- Controls -->
				  <!-- <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
				    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				    <span class="sr-only">Previous</span>
				  </a>
				  <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
				    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				    <span class="sr-only">Next</span>
				  </a> -->
				</div>
            </div>
            <div id="get-tab" class="tab-pane fade in active">
			    <button id="iframeCustomCodeBtn" class="btn btn-primary" type="button" data-toggle="collapse" data-target="#customCodeCollapse" aria-expanded="false" aria-controls="customCodeCollapse" style="display:none">Customize Code</button>
            	<div class="collapse" id="customCodeCollapse">
            		<hr>
            		<div class="row">
                        <div class="col-md-6 form-div">
                            {!! Form::label('website','Website (Required)') !!}
                            {!! Form::select('website',[null=>''] + $websites,null,['class' => 'form-control customizeCode','id' => 'website']) !!}
                        </div>
                        <div class="col-md-6 form-div">
                            <label for="append_to" data-toggle="tooltip" data-placement="right" title="Where to append the iframe. It could be a class name or id of HTML element or raw HTML element.">Append To (Optional)</label>
                            {!! Form::text('append_to','',
                            array('class' => 'form-control customizeCode', 'id' => 'append_to')) !!}
                        </div>
                        <div class="col-md-6 form-div">
                        	<label for="redirect_url" data-toggle="tooltip" data-placement="left" title="The next page url. After submission the page will redirect to next page.">Redirect URL (Optional)</label>
                            {!! Form::text('redirect_url','',
                            array('class' => 'form-control customizeCode', 'id' => 'redirect_url')) !!}
                        </div>
                        <div class="col-md-6 form-div">
                        	<label for="timeout" data-toggle="tooltip" data-placement="right" title="This option is a time delay when to show the skip button. See Posting Instructions for more details.">Skip Timeout (Optional)</label>
                            {!! Form::text('timeout','',
                            array('class' => 'form-control customizeCode', 'id' => 'timeout')) !!}
                        </div>
                        <div class="col-md-6 form-div">
                        	<label for="submit_btn" data-toggle="tooltip" data-placement="left" title="Text for submit button.">Submit Button Text (Optional)</label>
                            {!! Form::text('submit_btn','',
                            array('class' => 'form-control customizeCode', 'id' => 'submit_btn')) !!}
                        </div>
                        <div class="col-md-6 form-div">
                        	<label for="loader_text" data-toggle="tooltip" data-placement="right" title="TThe text message when fetching offers.">Loading Text (Optional)</label>
                            {!! Form::text('loader_text','',
                            array('class' => 'form-control customizeCode', 'id' => 'loader_text')) !!}
                        </div>
                        <div class="col-md-12 form-div text-right">
                        	<button id="updateIframeCodeBtn" type="button" class="btn btn-primary">Update Code</button>
                        </div>
                    </div>
                    <hr>
				</div>
				<textarea id="sampleCode4Custom" class="hidden"></textarea>
            	<div class="code-sample-div">
	              <!-- SAMPLE GET A CODE -->
	              <pre><code></code></pre>
	              <!-- END SAMPLE CODE -->
	          	</div>
              	<!-- <a class="btn btn-default btn-submit text-center" data-dismiss="modal">OK</a> -->
            </div>
          </div>
        </div>
        <!-- <div class="modal-footer">
          <a class="btn btn-default btn-submit" data-dismiss="modal">OK</a>
        </div> -->
      </div>
    </div>
</div>
<!-- END GET CODE MODAL -->

<div class="content-form">
    <!-- TABLE CAMPAIGN -->
    <table id="campaigns-table" class="table">
      <thead>
        <tr>
          <th width="15%">Image</th>
          <th>Name</th>
          <th>Category</th>
          <th width="25%">Short Description</th>
          <th>Payouts</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th width="15%">Image</th>
          <th>Name</th>
          <th>Category</th>
          <th width="25%">Short Description</th>
          <th>Payouts</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </tfoot>
      <tbody>
      </tbody>
    </table>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/highlight/highlight.pack.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/affiliate/campaigns.min.js') }}"></script>
@stop