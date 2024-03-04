@extends('app')

@section('title')
    Revenue Statistics
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">

<!-- Select2 CSS -->
<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/revenueStatistics'),'class'=> '', 'id' => 'revStats-form']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
                <div class="form-group col-md-5 col-lg-5">
                    {!! Form::label('campaign_id','Campaign') !!}
                    <label class="checkbox-inline" style="margin-left: 10px;">
                        <input type="checkbox" name="show_inactive_campaign" id="show_inactive_campaign" value="1" class="this_field" {!! isset($inputs['show_inactive_campaign']) ? 'checked' : ''!!}> Show Inactive Campaign
                    </label>
                    <input type="hidden" name="campaign_id" id="realCampaignId" value="{!! isset($inputs['campaign_id']) ? $inputs['campaign_id']: '' !!}" />
                    <div id="showAllCampaigns-container" style="{!! isset($inputs['show_inactive_campaign']) ? '' : 'display:none' !!}">
                        {!! Form::select('fake_campaign_id_all',Bus::dispatch(new GetCampaignListAndIDsPair()),isset($inputs['campaign_id']) ? $inputs['campaign_id']: '',['class' => 'this_field form-control','id' => 'campaign_id', 'class' => 'campaignField', 'style'=>'width:100%;']) !!}
                    </div>
                    <div id="showActiveCampaigns-container" style="{!! isset($inputs['show_inactive_campaign']) ? 'display:none' : '' !!}">
                    {!! Form::select('fake_campaign_id_active',Bus::dispatch(new GetCampaignListAndIDsPair(1)),isset($inputs['campaign_id']) ? $inputs['campaign_id']: '',['class' => 'this_field form-control','id' => 'campaign_id', 'class' => 'campaignField', 'style'=>'width:100%']) !!}
                    </div>
                </div>
                <div class="form-group col-md-4 col-lg-4">
                    {!! Form::label('affiliate_id','Affiliate ID') !!}
                    {!! Form::text('affiliate_id',isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_status','Status') !!}
                    {!! Form::select('lead_status',config('constants.LEAD_STATUS'),isset($inputs['lead_status']) ? $inputs['lead_status'] : '',['class' => 'this_field form-control','id' => 'lead_status']) !!}
                </div>
                <!-- <div class="form-group col-md-5 col-lg-5">
                    {!! Form::label('containing_data','Containing Data') !!}
                    {!! Form::text('containing_data',isset($inputs['containing_data']) ? $inputs['containing_data'] : '',['class' => 'this_field form-control', 'id' => 'containing_data']) !!}
                </div> -->
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s1','S1') !!}
                    {!! Form::text('s1',isset($inputs['s1']) ? $inputs['s1'] : '',['class' => 'this_field form-control', 'id' => 's1']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s2','S2') !!}
                    {!! Form::text('s2',isset($inputs['s2']) ? $inputs['s2'] : '',['class' => 'this_field form-control', 'id' => 's2']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s3','S3') !!}
                    {!! Form::text('s3',isset($inputs['s3']) ? $inputs['s3'] : '',['class' => 'this_field form-control', 'id' => 's3']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('s4','S4') !!}
                    {!! Form::text('s4',isset($inputs['s4']) ? $inputs['s4'] : '',['class' => 'this_field form-control', 'id' => 's4']) !!}
                </div>
                <div class="form-group col-md-1 col-lg-1">
                    {!! Form::label('s5','S5') !!}
                    {!! Form::text('s5',isset($inputs['s5']) ? $inputs['s5'] : '',['class' => 'this_field form-control', 'id' => 's5']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('date_range','Predefined Date Range') !!}
                    {!! Form::select('date_range',['' => '','today' => 'Today','yesterday' => 'Yesterday','week' => 'Week to date', 'month' => 'Month to date','last_month' => 'Last Month', 'year' => 'Year to date'],'today',['class' => 'this_field form-control','id' => 'date_range']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_from','Revenue Date From') !!}
                    <div class="input-group date">
                        <input name="lead_date_from" id="lead_date_from" value="{{ isset($inputs['lead_date_from']) ? $inputs['lead_date_from'] : '' }}" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_to','Revenue Date To') !!}
                    <div class="input-group date">
                        <input name="lead_date_to" id="lead_date_to" value="{{ isset($inputs['lead_date_to']) ? $inputs['lead_date_to'] : '' }}" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('group_by','Date Group By') !!}
                    {!! Form::select('group_by',['date' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'],isset($inputs['group_by']) ? $inputs['group_by'] : '',['class' => 'this_field form-control','id' => 'group_by']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('group_by_column','Additional Group By') !!}
                    {!! Form::select('group_by_column',['' => '', 'campaign' => 'Campaign', 'affiliate' => 'Affiliate', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3', 's4' => 'S4', 's5' => 'S5','creative_id' => 'Creative ID'],'',['class' => 'this_field form-control','id' => 'group_by_column']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('table','Table') !!}
                    {!! Form::select('table',['leads' => 'leads','leads_archive' => 'leads_archive'],isset($inputs['table']) ? $inputs['table'] : 'leads',['class' => 'this_field form-control','id' => 'table']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('affiliate_type','Affiliate Type') !!}
                    {!! Form::select('affiliate_type',[null=>''] + config('constants.AFFILIATE_TYPE'),'',['class' => 'this_field form-control','id' => 'affiliate_type']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('include_subids','Include SubIDs in the report:') !!}
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s1" id="sib_s1" value="1" class="this_field sibs"> S1
                    </label>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s2" id="sib_s2" value="1" class="this_field sibs"> S2
                    </label>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s3" id="sib_s3" value="1" class="this_field sibs"> S3
                    </label>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s4" id="sib_s4" value="1" class="this_field sibs"> S4
                    </label>
                </div>
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Generate Report', ['id' => 'generateReportBtn','class' => 'btn btn-primary']) !!}
                    {!! Html::link(url('admin/downloadRevenueReport'),'Download Report',['class' =>'disabled btn btn-primary', 'id' => 'downloadRevenueReport']) !!}
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="row">
    <div class="container-fluid">
        <table id="revStats-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>Lead Date</th>
                    <th>Campaign</th>
                    <th>Campaign Type</th>
                    <th>Creative ID</th>
                    <th>Affiliate</th>
                    <th>Company</th>
                    <th>Rate</th>
                    <th>Advertiser</th>
                    <th>S1</th>
                    <th>S2</th>
                    <th>S3</th>
                    <th>S4</th>
                    <th>S5</th>
                    <th>Status</th>
                    <th>Lead Count</th>
                    <th>Cost</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
            <tfoot>
                <tr>
                    <th>Lead Date</th>
                    <th>Campaign</th>
                    <th>Campaign Type</th>
                    <th>Creative ID</th>
                    <th>Affiliate</th>
                    <th>Company</th>
                    <th>Rate</th>
                    <th>Advertiser</th>
                    <th>S1</th>
                    <th>S2</th>
                    <th>S3</th>
                    <th>S4</th>
                    <th>S5</th>
                    <th>Status</th>
                    <th id="totalLeadCount">Lead Count</th>
                    <th id="totalCost">Cost</th>
                    <th id="totalRevenue">Revenue</th>
                    <th id="totalPost">Profit</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{asset('bower_components/select2/dist/js/select2.min.js')}}"></script>
<script src="{{ asset('js/admin/revenue_statistics.min.js') }}"></script>
@stop