@extends('app')

@section('title')
    Prepop Statistics
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">

<!-- Select2 CSS -->
<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
<style>
    table.dataTable tbody tr td, table.dataTable thead tr th {
        word-wrap: break-word;
        word-break: break-all;
    }

    /*initially hide the button*/
    /*#download_report{
        display: none;
    }*/
</style>
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        <div class="container-fluid">
            @include('partials.flash')
            @include('partials.error')
        </div>
        <div class="row">
            <div class="form-group col-md-4 col-lg-4">
                {!! Form::label('affiliate','Affiliate / Rev Tracker') !!}
                {!! Form::select('affiliate_id',Bus::dispatch(new GetAffiliatesCompanyIDPair())+['' => 'All'],null,['class' => 'form-control','id' => 'affiliate_id', 'style' => 'width: 100%']) !!}
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
            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('date_from','Date From') !!}
                <div class="input-group date">
                    <input name="date_from" id="date_from" value="" type="text" class="form-control date-picker"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('date_to','Date To') !!}
                <div class="input-group date">
                    <input name="date_to" id="date_to" value="" type="text" class="form-control date-picker"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('predefined_date','Predefined Date') !!}
                {!! Form::select('predefined_date',['' => 'Select a Period', 'yesterday' => 'Yesterday', 'week' => 'Week', 'month' => 'Month'],'yesterday',['class' => 'this_field form-control','id' => 'predefined_date']) !!}
            </div>
            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('group_by','Group By') !!}
                {!! Form::select('group_by',['affiliate_id' => 'Affiliate', 'revenue_tracker_id' => 'Revenue Tracker', 'created_at' => 'Date'],'created_at',['class' => 'this_field form-control','id' => 'group_by']) !!}
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
            <div class="text-center form-group">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                {!! Form::button('Generate Report', ['class' => 'btn btn-primary','id' => 'generate_report']) !!}
                <!--{!! Form::button('Download Report', ['class' => 'btn btn-primary','id' => 'download_report']) !!}-->
                {!! Html::link(url('downloadPrepopStatisticsReport'),'Download Report',['class' =>'btn btn-primary disabled', 'id' => 'download_report']) !!}
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="container-fluid row">
            <table id="prepop-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Affiliate ID</th>
                        <th>Rev ID</th>
                        <th>s1</th>
                        <th>s2</th>
                        <th>s3</th>
                        <th>s4</th>
                        <th>s5</th>
                        <th>Total Clicks</th>
                        <th>Prepopped</th>
                        <th>Not Prepopped</th>
                        <th>Prepopped <br>with Errors</th>
                        <th>Percentage <br>Prepopped</th>
                        <th>Percentage <br>Not Prepopped</th>
                        <th>Percentage <br>Prepopped <br>With Errors</th>
                        {{-- <th>Profit Margin</th> --}}
                    </tr>
                </thead>
                <tbody></tbody>
               <tfoot>
                    <tr>
                        <th>Date</th>
                        <th>Affiliate ID</th>
                        <th>Rev ID</th>
                        <th>s1</th>
                        <th>s2</th>
                        <th>s3</th>
                        <th>s4</th>
                        <th>s5</th>
                        <th>Total Clicks</th>
                        <th>Prepopped</th>
                        <th>Not Prepopped</th>
                        <th>Prepopped <br>with Errors</th>
                        <th>Percentage <br>Prepopped</th>
                        <th>Percentage <br>Not Prepopped</th>
                        <th>Percentage <br>Prepopped <br>With Errors</th>
                        {{-- <th>Profit Margin</th> --}}
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('js/admin/prepopstatistics.min.js') }}"></script>
@stop