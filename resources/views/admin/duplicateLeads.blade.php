@extends('app')

@section('title')
    Duplicate Leads
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/duplicateLeads'),'class'=> '','id'=>'search-leads',]) !!}
            <div class="container-fluid">
                @include('partials.flash')
                @include('partials.error')
            </div>

            <div class="row">
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('campaign_id','Campaign') !!}
                    {!! Form::select('campaign_id',Bus::dispatch(new GetCampaignListAndIDsPair()),isset($inputs['campaign_id']) ? $inputs['campaign_id']: '',['class' => 'this_field form-control','id' => 'campaign_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('affiliate_id','Affiliate') !!}
                    {!! Form::select('affiliate_id', $affiliates, isset($inputs['affiliate_id']) ? $inputs['affiliate_id']: '',['class' => 'this_field form-control','id' => 'affiliate_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_from','Lead Date From') !!}
                    <div class="input-group date">
                        <input name="lead_date_from" id="lead_date_from" value="{{ isset($inputs['lead_date_from']) ? $inputs['lead_date_from'] : '' }}" type="text" class="this_field form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_to','Lead Date To') !!}
                    <div class="input-group date">
                        <input name="lead_date_to" id="lead_date_to" value="{{ isset($inputs['lead_date_to']) ? $inputs['lead_date_to'] : '' }}" type="text" class="this_field form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_status','Status') !!}
                    {!! Form::select('lead_status',config('constants.LEAD_STATUS'),isset($inputs['lead_status']) ? $inputs['lead_status'] : '',['class' => 'this_field form-control','id' => 'lead_status']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('limit_rows','Limit Rows') !!}
                    {!! Form::select('limit_rows',config('constants.LIMIT_ROWS'),isset($inputs['limit_rows']) ? $inputs['limit_rows'] : '',['class' => 'this_field form-control','id' => 'limit_rows']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('duplicate','Duplicate') !!}
                    {!! Form::select('duplicate',array(''=>'ALL','1' => 'No Duplicate', '2' => 'With Duplicates'),isset($inputs['duplicate']) ? $inputs['duplicate'] : '',['class' => 'this_field form-control','id' => 'duplicate']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('table','Table') !!}
                    {!! Form::select('table',array('1' => 'Leads','2' => 'Lead Duplicates'),isset($inputs['table']) ? $inputs['table'] : '',['class' => 'this_field form-control','id' => 'table']) !!}
                </div>
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Search Leads', ['class' => 'btn btn-primary','id' => 'duplicateLeadsSearchBtn']) !!}
                    @if(session()->has('has_duplicate_leads'))
                        {!! Html::link(url('admin/downloadSearchedDuplicateLeads'),'Download Leads',['class' =>'btn btn-primary', 'id' => 'downloadSearchedLeads']) !!}
                    @endif
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="container-fluid row">
            <table id="leads-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Campaign</th>
                        <th>Affiliate</th>
                        <th>Status</th>
                        <th>Payout/Lead</th>
                        <th>Count</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $lead_status = config('constants.LEAD_STATUS');
                    ?>
                    @foreach($leads as $lead)
                        <tr>
                            <td>{{ $lead->lead_email }}</td>
                            <td>{{ $lead->campaign->name }}</td>
                            <td>{{ $lead->affiliate->id }}</td>
                            <td>{{ $lead_status[$lead->lead_status] }}</td>
                            <td>{{ $lead->lead_status == 1 ? $lead->payout : 0 }}</td>
                            <td>{{ $lead->count }}</td>
                            <td>{{ $lead->date_created }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Email</th>
                        <th>Campaign</th>
                        <th>Affiliate</th>
                        <th>Status</th>
                        <th>Payout/Lead</th>
                        <th>Count</th>
                        <th>Date Created</th>
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
<script src="{{ asset('js/admin/duplicate_leads.min.js') }}"></script>
@stop