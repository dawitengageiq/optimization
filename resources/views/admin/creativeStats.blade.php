
@extends('app')

@section('title')
    Creative  Revenue Reports
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

<style>
    /*#creativeStats-table_filter {display:none;}*/
</style>
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/creativeReports'),'class'=> '', 'id' => 'revStats-form']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
               <!--  
               <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('table','Table') !!}
                    {!! Form::select('table',['leads' => 'leads','leads_archive' => 'leads_archive'],isset($inputs['table']) ? $inputs['table'] : 'leads',['class' => 'this_field form-control','id' => 'table']) !!}
                </div>
                <div class="form-group col-md-4 col-lg-4">
                    {!! Form::label('affiliate_id','Affiliate ID') !!}
                    {!! Form::text('affiliate_id',isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <div class="">
                    <div  style="display: none;">
                    {!! Form::label('lead_status','Status') !!}
                    {!! Form::select('lead_status',config('constants.LEAD_STATUS'),isset($inputs['lead_status']) ? $inputs['lead_status'] : '',['class' => 'this_field form-control','id' => 'lead_status']) !!}
                </div> 
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('date_range','Predefined Date Range') !!}
                    {!! Form::select('date_range',['' => '','today' => 'Today','yesterday' => 'Yesterday','week' => 'Week to date', 'month' => 'Month to date','last_month' => 'Last Month', 'year' => 'Year to date'],'today',['class' => 'this_field form-control','id' => 'date_range']) !!}
                </div>
                -->
                <div class="form-group col-md-6 col-lg-6">
                    {!! Form::label('lead_date_from','Revenue Date From') !!}
                    <div class="input-group date">
                        <input name="lead_date_from" id="lead_date_from" value="{{ isset($inputs['lead_date_from']) ? $inputs['lead_date_from'] : '' }}" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-6 col-lg-6">
                    {!! Form::label('lead_date_to','Revenue Date To') !!}
                    <div class="input-group date">
                        <input name="lead_date_to" id="lead_date_to" value="{{ isset($inputs['lead_date_to']) ? $inputs['lead_date_to'] : '' }}" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-6 col-lg-6">
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
                <div class="form-group col-md-6 col-lg-6">
                    {!! Form::label('affiliate_id','Revenue Tracker') !!}
                    {!! Form::text('affiliate_id',isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <!-- <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('group_by','Date Group By') !!}
                    {!! Form::select('group_by',['date' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'],isset($inputs['group_by']) ? $inputs['group_by'] : '',['class' => 'this_field form-control','id' => 'group_by']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('group_by_column','Additional Group By') !!}
                    {!! Form::select('group_by_column',['' => '', 'campaign' => 'Campaign', 'affiliate' => 'Affiliate', 'creative_id' => 'Creative ID'],'',['class' => 'this_field form-control','id' => 'group_by_column']) !!}
                </div> -->
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Generate Report', ['id' => 'generateReportBtn','class' => 'btn btn-primary']) !!}
                    @if(isset($summary) && count($summary) > 0)
                    {!! Html::link(url('admin/downloadCreativeRevenueReport'),'Download Report',['class' =>'btn btn-primary', 'id' => 'downloadCreativeReport']) !!}
                    @endif
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<?php
$date = isset($inputs['lead_date_today']) ? $inputs['lead_date_today'] : Carbon::now()->toDateString();
if(isset($inputs['lead_date_from'], $inputs['lead_date_to'])) {
    if($inputs['lead_date_from'] != '' && $inputs['lead_date_to'] != '') {
        $date = $inputs['lead_date_from'];
        if($inputs['lead_date_from'] != $inputs['lead_date_to']) {
            $date .= ' to '.$inputs['lead_date_to'];
        }
    }
}
?>
@if(isset($summary))
<h4><strong>Report for <span id="crr-date">{!! $date !!}</span></strong></h4>
@endif

<div class="row">
    <div class="container-fluid">
        <table id="creativeStats-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>Affiliate [Revenue Tracker]</th>
                    <th id="creativeId_thead">Creative ID</th>
                    <th>Views</th>
                    <th>Lead Count</th>
                    <th>Revenue</th>
                    <th>Revenue/View</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($summary))
                @foreach($summary as $data) 
                    <tr>
                        <td>
                            <?php 
                                if(isset($affiliates[$data['affiliate_id']])) {
                                    echo $affiliates[$data['affiliate_id']].'<br>['.$data['affiliate_id'].']';
                                }else {
                                    echo $data['affiliate_id'];
                                }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $creative = $data['creative_id'];
                            if($data['creative_id'] == null) $creative = 'No Creative ID';
                            echo $campaigns[$data['campaign_id']].' - '.$creative;
                            ?>
                        </td>
                        <td>{!! $data['views'] !!}</td>
                        <td>{!! $data['lead_count'] !!}</td>
                        <td>{!! sprintf("%.2f", $data['revenue']) !!}</td>
                        <td>{!! sprintf("%.2f", $data['revView']) !!}</td>
                    </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th>Path [Revenue Tracker]</th>
                    <th>Creative ID</th>
                    <th>Views</th>
                    <th>Lead Count</th>
                    <th>Revenue</th>
                    <th>Revenue/View</th>
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
<script src="/bower_components/select2/dist/js/select2.min.js"></script>
<script src="{{ asset('js/admin/creative_stats.min.js') }}"></script>
@stop
