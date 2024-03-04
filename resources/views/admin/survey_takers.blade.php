@extends('app')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin/survey_takers.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('title')
    Survey Takers
@stop

@section('content')

<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/surveyTakers'),'class'=> '', 'id' => 'surveyTakers-form']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('id','ID') !!}
                    {!! Form::text('id','',['class' => 'this_field form-control', 'id' => 'id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('email','Email') !!}
                    {!! Form::text('email','',['class' => 'this_field form-control', 'id' => 'email']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('gender','Gender') !!}
                    {!! Form::select('gender',['' => '','F' => 'Female','M' => 'Male'],'',['class' => 'this_field form-control','id' => 'gender']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('zip','Zip') !!}
                    {!! Form::text('zip','',['class' => 'this_field form-control', 'id' => 'zip']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('affiliate_id','Affiliate ID') !!}
                    {!! Form::text('affiliate_id','',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('first_name','First Name') !!}
                    {!! Form::text('first_name','',['class' => 'this_field form-control', 'id' => 'first_name']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('city','City') !!}
                    {!! Form::text('city','',['class' => 'this_field form-control', 'id' => 'city']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('source_url','Source URL') !!}
                    {!! Form::text('source_url','',['class' => 'this_field form-control', 'id' => 'source_url']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('revenue_tracker','Revenue Tracker') !!}
                    {!! Form::text('revenue_tracker','',['class' => 'this_field form-control', 'id' => 'revenue_tracker']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('last_name','Last Name') !!}
                    {!! Form::text('last_name','',['class' => 'this_field form-control', 'id' => 'last_name']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('state','State') !!}
                    {!! Form::select('state',config('constants.US_STATES_ABBR'),'',['class' => 'this_field form-control','id' => 'state']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('phone','Phone') !!}
                    {!! Form::text('phone','',['class' => 'this_field form-control', 'id' => 'phone']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s1','S1') !!}
                    {!! Form::text('s1','',['class' => 'this_field form-control', 'id' => 's1']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s2','S2') !!}
                    {!! Form::text('s2','',['class' => 'this_field form-control', 'id' => 's2']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s3','S3') !!}
                    {!! Form::text('s3','',['class' => 'this_field form-control', 'id' => 's3']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s4','S4') !!}
                    {!! Form::text('s4','',['class' => 'this_field form-control', 'id' => 's4']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s5','S5') !!}
                    {!! Form::text('s5','',['class' => 'this_field form-control', 'id' => 's5']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('ip','IP Address') !!}
                    {!! Form::text('ip','',['class' => 'this_field form-control', 'id' => 'ip']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('date_from','Date From') !!}
                    <div class="input-group date">
                        <input name="date_from" id="date_from" value="" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('date_to','Date To') !!}
                    <div class="input-group date">
                        <input name="date_to" id="date_to" value="" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Search Survey Takers', ['id' => 'getSurveyTakers','class' => 'btn btn-primary']) !!}
                    {!! Html::link(url('admin/downloadSurveyTakers'),'Download',['class' =>'btn btn-primary', 'id' => 'downloadSurveyTakers', 'disabled' => 'true']) !!}
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <table id="survey-takers-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Affiliate ID</th>
                    <th>Revenue Tracker</th>
                    <th>s1</th>
                    <th>s2</th>
                    <th>s3</th>
                    <th>s4</th>
                    <th>s5</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Zip</th>
                    <th>State</th>
                    <th>Source URL</th>
                    <th>Created At</th>
                    <th>More Details</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Affiliate ID</th>
                    <th>Revenue Tracker</th>
                    <th>s1</th>
                    <th>s2</th>
                    <th>s3</th>
                    <th>s4</th>
                    <th>s5</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Zip</th>
                    <th>State</th>
                    <th>Source URL</th>
                    <th>Created At</th>
                    <th>More Details</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="more-details-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="moreDetailsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="moreDetailsModalLabel">More Details</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <table id="surveyTakers_moreDetails-table" class="table table-striped table-bordered" style="table-layout: fixed;">
                            <tr>
                                <th width="30%">ID</th>
                                <td id="id-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Affiliate ID</th>
                                <td id="affiliate-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Revenue Tracker</th>
                                <td id="rev_tracker-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">First Name</th>
                                <td id="fname-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Last Name</th>
                                <td id="lname-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Email</th>
                                <td id="email-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Zip</th>
                                <td id="zip-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">City</th>
                                <td id="city-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">State</th>
                                <td id="state-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Birthdate</th>
                                <td id="birthdate-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Gender</th>
                                <td id="gender-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Address1</th>
                                <td id="address1-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Address2</th>
                                <td id="address2-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Ethnicity</th>
                                <td id="ethnicity-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Phone</th>
                                <td id="phone-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">IP</th>
                                <td id="ip-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Mobile</th>
                                <td id="mobile-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Source Url</th>
                                <td id="source_url-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Status</th>
                                <td id="status-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Response</th>
                                <td id="response-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Created At</th>
                                <td id="created_at-value" class="md-deets" width="70%"></td>
                            </tr>
                            <tr>
                                <th width="30%">Updated At</th>
                                <td id="updated_at-value" class="md-deets" width="70%"></td>
                            </tr>
                        </table>
                    </div>
                    {{-- <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Birthdate: </span><span id="birthdate-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Gender: </span><span id="gender-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">City: </span><span id="city-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Address1: </span><span id="address1-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Address2: </span><span id="address2-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Ethnicity: </span><span id="ethnicity-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Phone: </span><span id="phone-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">IP: </span><span id="ip-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Mobile: </span><span id="mobile-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Status: </span><span id="status-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Response: </span><span id="response-value"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Updated At: </span><span id="updated_at-value"></span>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/admin/survey_takers.min.js') }}"></script>
@stop