@extends('app')

@section('title')
    Page View Statistics
@stop

@section('header')
    <!-- DataTables CSS -->
    <link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

    <!-- DataTables Responsive CSS -->
    <link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin/page-view-statistics.min.css') }}" rel="stylesheet">
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-body">

            {!! Form::open(['url' => url('admin/getClicksVsRegsStats'),'class'=> '', 'id' => 'page-view-stats-form']) !!}

            @include('partials.flash')
            @include('partials.error')

            <div class="row">
                <div class="col-md-4 col-lg-4 col-sm-12">
                    {!! Form::label('affiliate_id', 'Affiliate / Revenue Tracker Traffic Source') !!}
                    <button id="remove_affiliate_id_selections" type="button" class="btn btn-primary btn-xs pull-right">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                    {!! Form::select('affiliate_id', [], null, ['class' => 'form-control search-affiliate-select', 'id' => 'affiliate_id', 'name' => 'affiliateIDs[]', 'multiple' => 'multiple', 'style' => 'width: 100%']) !!}
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
                <div class="form-group col-md-1 col-lg-1">
                    {!! Form::label('s4','S4') !!}
                    {!! Form::text('s4','',['class' => 'this_field form-control', 'id' => 's4']) !!}
                </div>
                <div class="form-group col-md-1 col-lg-1">
                    {!! Form::label('s5','S5') !!}
                    {!! Form::text('s5','',['class' => 'this_field form-control', 'id' => 's5']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3 col-sm-12">
                    {!! Form::label('date_from','Date From') !!}
                    <div class="input-group date">
                        <input name="date_from" id="date_from" value="{{ isset($inputs['date_from']) ? $inputs['date_from'] : '' }}" type="text" class="date-field form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3 col-sm-12">
                    {!! Form::label('date_to','Date To') !!}
                    <div class="input-group date">
                        <input name="date_to" id="date_to" value="{{ isset($inputs['date_to']) ? $inputs['date_to'] : '' }}" type="text" class="date-field form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3 col-sm-12">
                    {!! Form::label('date_range','Predefined Date Range') !!}
                    {!! Form::select('date_range',['' => '','yesterday' => 'Yesterday','week' => 'Week to date', 'month' => 'Month to date','last_month' => 'Last Month'],'',['class' => 'this_field form-control','id' => 'date_range']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3 col-sm-12">
                    {!! Form::label('group_by', 'Group By') !!}
                    {!! Form::select('group_by', ['created_at' => 'Day Per SubID', 'custom' => 'Day Per Revenue Tracker','revenue_tracker_id' => 'Revenue Tracker'], isset($inputs['group_by']) ? $inputs['group_by'] : '',['class' => 'this_field form-control','id' => 'group_by']) !!}
                </div>
                <div class="form-group col-md-12">
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
                    {!! Html::link(url('admin/downLoadPageViewStatisticsReport'),'Download Report',['class' =>'disabled btn btn-primary', 'id' => 'download-page-view-statistics-report']) !!}
                </div>
            </div>
            <hr>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="row">
        <div class="container-fluid">
            <table id="page-view-statistics-table" class="publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Affiliate Name</th>
                        <th>Affiliate ID</th>
                        <th>RevenueTracker ID</th>
                        <th>s1</th>
                        <th>s2</th>
                        <th>s3</th>
                        <th>s4</th>
                        <th>s5</th>
                        <th>LP</th>
                        <th>RP</th>
                        <th>TO1</th>
                        <th>TO2</th>
                        <th>MO1</th>
                        <th>MO2</th>
                        <th>MO3</th>
                        <th>MO4</th>
                        <th>LFC1</th>
                        <th>LFC2</th>
                        <th>TBR1</th>
                        <th>PD</th>
                        <th>TBR2</th>
                        <th>IFF</th>
                        <th>REX</th>
                        <th>ADS</th>
                        <th>CPAWALL</th>
                        <th>EXITPAGE</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>Total</th>
                        <th>LP</th>
                        <th>RP</th>
                        <th>TO1</th>
                        <th>TO2</th>
                        <th>MO1</th>
                        <th>MO2</th>
                        <th>MO3</th>
                        <th>MO4</th>
                        <th>LFC1</th>
                        <th>LFC2</th>
                        <th>TBR1</th>
                        <th>PD</th>
                        <th>TBR2</th>
                        <th>IFF</th>
                        <th>REX</th>
                        <th>ADS</th>
                        <th>CPAWALL</th>
                        <th>EXITPAGE</th>
                        <th>%</th>
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
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/admin/page-view-statistics.min.js') }}"></script>
@stop