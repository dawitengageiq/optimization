
@extends('app')

@section('title')
    Co-Reg Report
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

<link href="{{ asset('css/admin/coregreports.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('getCoregReports'),'class'=> '', 'id' => 'coregReport-form']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
                <!-- <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_from','Lead Date') !!}
                    <div class="input-group date">
                        <input name="lead_date" id="lead_date" value="" type="text" class="lead_date form-control" value="{!! Carbon::yesterday()->toDateString() !!}"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div> -->
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
                    {!! Form::text('affiliate_id','',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('revenue_tracker_id','Revenue Tracker ID') !!}
                    {!! Form::text('revenue_tracker_id','',['class' => 'this_field form-control', 'id' => 'revenue_tracker_id']) !!}
                </div>
                <!-- <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('group_by_column','Group By') !!}
                    {!! Form::select('group_by_column',['' => '', 'campaign_id' => 'Campaign', 'affiliate_id' => 'Affiliate', 'revenue_tracker_id' => 'Revenue Tracker ID'],'',['class' => 'this_field form-control','id' => 'group_by_column']) !!}
                </div> -->
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Generate Report', ['id' => 'generateReportBtn','class' => 'btn btn-primary']) !!}
                    {!! Html::link(url('admin/downloadCoregReport'),'Download Report',['class' =>'disabled btn btn-primary', 'id' => 'downloadRevenueReport']) !!}
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="row">
    <div class="container-fluid">
        <table id="coregReport-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th colspan="2">
                        <span id="snapshot-date">{!! Carbon::yesterday()->toDateString() !!}</span><br>
                        <h4><strong>Net Profit</strong></h4>
                        $<span id="total-net-profit">100.00</span>
                    </th>
                    <th colspan="5">
                        <h4><strong>Lead Filter</strong></h4>
                        Total Cost: $<span id="total-cost">100.00</span>
                    </th>
                    <th colspan="6">
                        <h4><strong>Lead Reactor</strong></h4>
                        Total Revenue: $<span id="total-revenue">100.00</span>
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <th rowspan="2">Rev Tracker</th>
                    <th rowspan="2">Campaign</th>
                    <th rowspan="2">Leads Received</th>
                    <th colspan="3">Drop Off</th>
                    <th rowspan="2">Cost</th>
                    <th rowspan="2">Leads Received</th>
                    <th colspan="4">Drop Off</th>
                    <th rowspan="2">Revenue</th>
                    <th rowspan="2">We Get</th>
                </tr>
                <tr>
                    <th>Filter</th>
                    <th>Admin</th>
                    <th>NLR</th>
                    <th>Rejected</th>
                    <th>Failed</th>
                    <th>Pending/Queued</th>
                    <th>Cap Reached</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
            <tfoot>
                <tr>
                    <th>Rev Tracker</th>
                    <th>Campaign</th>
                    <th>Leads Received</th>
                    <th>Filter</th>
                    <th>Admin</th>
                    <th>NLR</th>
                    <th>Cost</th>
                    <th>Leads Received</th>
                    <th>Rejected</th>
                    <th>Failed</th>
                    <th>Pending/Queued</th>
                    <th>Cap Reached</th>
                    <th>Revenue</th>
                    <th>We Get</th>
                </tr>
            </tfoot>
        </table>
        <div class="row hidden">
            <div id="coregReportLegendDiv" class="col-md-12 small">
                <span class="label label-primary" style="background-color: rgba(242, 222, 222, 1);margin-right: 10px;color: rgba(242, 222, 222, 1);">BLANK</span>
                <span style="padding-right: 10px;">From NLR</span> 
                <span class="label label-success" style="background-color: rgba(223, 240, 216, 1);margin-right: 10px;color: rgba(223, 240, 216, 1);">BLANK</span>
                <span style="padding-right: 10px;">From OLR</span>
                <span class="label label-info" style="background-color: rgba(217, 237, 247, 1);margin-right: 10px;color: rgba(217, 237, 247, 1);">BLANK</span>
                <span style="padding-right: 10px;">From LF only</span>
                <!-- <span class="label label-warning" style="background-color: rgba(252, 248, 227, 1);margin-right: 10px;color: rgba(252, 248, 227, 1)">BLANK</span>
                <span style="padding-right: 10px;">Both from NLR and OLR</span> -->
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
<script src="{{asset('bower_components/select2/dist/js/select2.min.js')}}"></script>
<script src="{{ asset('js/admin/coreg_reports.min.js') }}"></script>
@stop