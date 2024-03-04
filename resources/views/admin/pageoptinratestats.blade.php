@extends('app')

@section('title')
    Page OptIn Rate Stats
@stop

@section('header')
    <!-- DataTables CSS -->
    <link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('content')
    <input type="hidden" id="totalCampaignTypeCountCol" value="{!! count(config('constants.CAMPAIGN_TYPES')) !!}"/>
    <div class="panel panel-default">
        <div class="panel-body">
            {!! Form::open(['url' => url('admin/pageOptinRateStats'),'class'=> '', 'id' => 'pageOptInRate-form']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
                <div class="form-group col-md-4 col-lg-4">
                    {!! Form::label('affiliate_id','Affiliate ID') !!}
                    <button id="remove_affiliate_id_selections" type="button" class="btn btn-primary btn-xs pull-right">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                    {!! Form::select('affiliate_id', [], null, ['class' => 'form-control search-affiliate-select', 'id' => 'affiliate_id', 'style' => 'width: 100%', 'multiple' => 'multiple']) !!}
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="form-group col-md-6 col-lg-6">
                            {!! Form::label('date_from','Date From') !!}
                            <div class="input-group date">
                                <input name="date_from" id="date_from" value="" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {!! Form::label('date_to','Date To') !!}
                            <div class="input-group date">
                                <input name="date_to" id="date_to" value="" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {!! Form::label('date_range','Predefined Date Range') !!}
                            {!! Form::select('date_range',['' => '','yesterday' => 'Yesterday','week' => 'Week to date', 'month' => 'Month to date','last_month' => 'Last Month'],'',['class' => 'this_field form-control','id' => 'date_range']) !!}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {!! Form::label('group_by','Group By') !!}
                            {!! Form::select('group_by',['day' => 'Day','rev_tracker' => 'Revenue Tracker'],'day',['class' => 'this_field form-control','id' => 'group_by']) !!}
                        </div>
                    </div>
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
                    {!! Html::link(url('admin/downloadPageOptInRateReport'),'Download Report',['class' =>'btn btn-primary', 'id' => 'downloadPageOptInRateReport', 'disabled' => 'true']) !!}
                </div>
            </div>
            <hr>
            {!! Form::close() !!}
        </div>
    </div>
    <?php
        $campaign_types = config('constants.CAMPAIGN_TYPES');
    ?>

    {!! Form::hidden('campaignLinkoutIds', json_encode($linkout_campaigns),array('id' => 'campaignLinkoutIds')) !!}

    <div class="row">
        <div class="container-fluid">
            <table id="pageOptInRateStats-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
                <thead>
                    <tr>
                        <th rowspan="2">Date</th>
                        <th rowspan="2">Name</th>
                        <th rowspan="2">Rev Tracker [Publisher]</th>
                        <th rowspan="2">S1</th>
                        <th rowspan="2">S2</th>
                        <th rowspan="2">S3</th>
                        <th rowspan="2">S4</th>
                        <th rowspan="2">S5</th>
                        @foreach($order_type as $type)
                            <th>
                                {!! $campaign_types[$type] !!}
                            </th>  
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($order_type as $type)
                          <th class="col-ct-{!! $type !!} campaign_type_column" width="10%">
                            @if(isset($campaigns[$type]))
                                <?php 
                                    $def = isset($benchmarks[$type]) ? $benchmarks[$type] : null;
                                    if($type == 4) $choices = config('constants.EXTERNAL_CAMPAIGN_AFFILIATE_REPORT_ID');
                                    else $choices = $campaigns[$type];

                                    $label = strlen($choices[$def]) > 10 ? substr($choices[$def], 0, 10) . '...' : $choices[$def];
                                ?>
                                <span class="label label-default bch-label benchmark-label" data-toggle="tooltip" data-placement="bottom" title="{!! $choices[$def] !!}">{!! $label !!}</span>
                            {{-- {!! Form::select('benchmark['.$type.'][]', $choices, $def, 
                                ['data-type' => $type,'class' => 'form-control campaign_type_benchmark', 'style' => 'width: 100%', 'multiple' => 'multiple']) !!} --}}
                            @endif
                          </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- <tr>
                        <td>3432</td>
                        <td>SBG</td>
                        <td>100</td>
                        <td>50</td>
                        <td>150</td>
                        <td>100%</td>
                        <td>100</td>
                        <td>50</td>
                        <td>150</td>
                        <td>100%</td>
                    </tr> --}}
                </tbody>
                <tfoot>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Rev Tracker [Publisher]</th>
                        <th>S1</th>
                        <th>S2</th>
                        <th>S3</th>
                        <th>S4</th>
                        <th>S5</th>
                        @foreach($order_type as $type)
                          <th>{!! $campaign_types[$type] !!}</th>  
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div id="benchmarkModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="benchmarkModal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Campaign Benchmarks</h4>
          </div>
          <div class="modal-body">
            <button id="selectAllBenchmarkBtn" type="button" class="btn btn-default btn-xs pull-right">Select All</button>
            <div class="row">
                @foreach($order_type as $type)
                    @if(isset($campaigns[$type]))
                    <?php 
                        $def = isset($benchmarks[$type]) ? $benchmarks[$type] : null;
                        if($type == 4) $choices = config('constants.EXTERNAL_CAMPAIGN_AFFILIATE_REPORT_ID');
                        else $choices = $campaigns[$type];
                    ?>
                    <div class="col-md-12">
                        {!! Form::label('campaign_type-'.$type,$campaign_types[$type]) !!}
                        {!! Form::select('benchmark['.$type.'][]', ['all' => 'All'] + $choices, $def, 
                            ['data-type' => $type,'class' => 'form-control campaign_type_benchmark', 'style' => 'width: 100%', 'multiple' => 'multiple']) !!}
                    </div>
                    @endif  
                @endforeach

                {!! Form::hidden('benchmark', json_encode($benchmarks),array('id' => 'current_benchmarks')) !!}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button id="updateBenchmarkBtn" type="button" class="btn btn-primary">Update</button>
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
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/admin/pageoptinratestats.min.js') }}"></script>
@stop