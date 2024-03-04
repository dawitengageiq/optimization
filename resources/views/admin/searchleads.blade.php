@extends('app')

@section('title')
    Search Leads
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
table.dataTable tbody tr td, table.dataTable thead tr th {
    word-wrap: break-word;
    word-break: break-all;
}
</style>
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/searchLeads'),'class'=> '','id'=>'search-leads',]) !!}
            <div class="container-fluid">
                @include('partials.flash')
                @include('partials.error')
            </div>
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
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('lead_id','Lead ID') !!}
                    {!! Form::text('lead_id',isset($inputs['lead_id']) ? $inputs['lead_id'] : '',['class' => 'this_field form-control', 'id' => 'lead_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_email','Lead Email') !!}
                    {!! Form::text('lead_email',isset($inputs['lead_email']) ? $inputs['lead_email'] : '',['class' => 'this_field form-control', 'id' => 'lead_email']) !!}
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
                <div class="form-group col-md-1 col-lg-1">
                    {!! Form::label('s3','S3') !!}
                    {!! Form::text('s3',isset($inputs['s3']) ? $inputs['s3'] : '',['class' => 'this_field form-control', 'id' => 's3']) !!}
                </div>
                <div class="form-group col-md-1 col-lg-1">
                    {!! Form::label('s4','S4') !!}
                    {!! Form::text('s4',isset($inputs['s4']) ? $inputs['s4'] : '',['class' => 'this_field form-control', 'id' => 's4']) !!}
                </div>
                <div class="form-group col-md-1 col-lg-1">
                    {!! Form::label('s5','S5') !!}
                    {!! Form::text('s5',isset($inputs['s5']) ? $inputs['s5'] : '',['class' => 'this_field form-control', 'id' => 's5']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_from','Lead Date From') !!}
                    <div class="input-group date">
                        <input name="lead_date_from" id="lead_date_from" value="{{ isset($inputs['lead_date_from']) ? $inputs['lead_date_from'] : '' }}" type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_to','Lead Date To') !!}
                    <div class="input-group date">
                        <input name="lead_date_to" id="lead_date_to" value="{{ isset($inputs['lead_date_to']) ? $inputs['lead_date_to'] : '' }}" type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('limit_rows','Limit Rows') !!}
                    {!! Form::select('limit_rows',config('constants.LIMIT_ROWS'),isset($inputs['limit_rows']) ? $inputs['limit_rows'] : '',['class' => 'this_field form-control','id' => 'limit_rows']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('affiliate_type','Affiliate Type') !!}
                    {!! Form::select('affiliate_type',[null=>''] + config('constants.AFFILIATE_TYPE'),isset($inputs['affiliate_type']) ? $inputs['affiliate_type'] : '',['class' => 'this_field form-control','id' => 'affiliate_type']) !!}
                </div>
                <div class="form-group col-md-2 col-lg-2">
                    {!! Form::label('table','Table') !!}
                    {!! Form::select('table',['leads' => 'leads','leads_archive' => 'leads_archive'],isset($inputs['table']) ? $inputs['table'] : 'leads',['class' => 'this_field form-control','id' => 'table']) !!}
                </div>
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Search Leads', ['id' => 'searchedLeadsBtn','class' => 'btn btn-primary']) !!}
                    @if(session()->has('has_search_leads'))
                        {!! Html::link(url('admin/downloadSearchedLeads'),'Download Leads',['class' =>'btn btn-primary', 'id' => 'downloadSearchedLeads']) !!}
                    @endif
                    @if(session()->has('has_search_leads') && isset($inputs['campaign_id']) && $inputs['campaign_id'] != '')
                        <?php 
                            $params = $inputs;
                            unset($params['_token']);
                            unset($params['limit_rows']);
                            $params = http_build_query($params);
                        ?>
                        {!! Html::link(config('app.reports_url').'downloadSearchedLeadsAdvertiserData?'.$params,'Download Leads Advertiser Data ',['class' =>'btn btn-primary', 'id' => 'downloadSearchedLeadsAdvertiserData']) !!}
                    @endif
                    <!-- {!! Html::link(url('admin/downloadSearchedLeads'),'Download Leads',['class' =>'btn btn-primary', 'id' => 'downloadSearchedLeads', 'disabled' => 'true']) !!} -->
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="modal fade" id="lead_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">

        <?php
            $attributes = [
                    'url' 		=> url('admin/updateLeadDetails'),
                    'class'			=> 'this_form',
                    'data-confirmation' => 'Are you sure you want to update the details of this lead?',
                    'data-process' 	=> 'update_lead_details'
            ];
        ?>

        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Leads Detail</h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('lead_csv','Lead CSV') !!}
                                {!! Form::textarea('lead_csv',null,array('class' => 'this_field form-control', 'id' => 'lead_csv', 'required' => 'true', 'rows' => 3)) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('message','Message') !!}
                                {!! Form::textarea('message',null,array('class' => 'this_field form-control', 'id' => 'message', 'rows' => 3)) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('advertiser_data','Advertiser Data') !!}
                                {!! Form::textarea('advertiser_data',null,array('class' => 'this_field form-control', 'id' => 'advertiser_data', 'required' => 'true', 'rows' => 3)) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('sent_result','Sent Result') !!}
                                {!! Form::textarea('sent_result',null,array('class' => 'this_field form-control', 'id' => 'sent_result', 'required' => 'true', 'rows' => 2)) !!}
                                <button type="button" class="openSettingBtn btn btn-primary btn-sm pull-right" data-toggle="modal" style="margin-top: 10px;">Update Rejected Config</button>
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::label('retry_count','Retry Count') !!}
                                {!! Form::text('retry_count', '' , array('class' => 'this_field form-control')); !!}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::label('retry_date','Last Retry Date') !!}
                                {!! Form::text('retry_date', '' , array('class' => 'this_field form-control')); !!}
                            </div>
                        </div>
                    </div>
                    @include('partials.error')
                </div>
                <div class="modal-footer">
                    {!! Form::button('Close', array('class' => 'btn btn-default','data-dismiss' => 'modal')) !!}
                    {!! Form::submit('Update', array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">

        <div class="container-fluid row">
            <table id="leads-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Campaign</th>
                        <th>Creative</th>
                        <th>Affiliate</th>
                        <th>Email</th>
                        <th>S1</th>
                        <th>S2</th>
                        <th>S3</th>
                        <th>S4</th>
                        <th>S5</th>
                        <th>Received</th>
                        <th>Payout</th>
                        <th>Lead Date</th>
                        <th>Lead Updated</th>
                        <th>Time Interval</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $leadStatuses = config('constants.LEAD_STATUS')?>
                    @foreach($leads as $lead)
                        <tr>
                            <td>
                                {!! Form::checkbox('lead-'.$lead->id,0,false,['class' => 'checkbox', 'id' => 'lead-'.$lead->id, 'data-lead_id' => $lead->id]) !!}
                                <br>
                                {{ $lead->id }}
                            </td>
                            <td>{{ $lead->name }}</td>
                            <td>{{ $lead->creative_id }}</td>
                            <td>{{ $lead->affiliate_id }}</td>
                            <td>{{ $lead->lead_email }}</td>
                            <td>{{ $lead->s1 }}</td>
                            <td>{{ $lead->s2 }}</td>
                            <td>{{ $lead->s3 }}</td>
                            <td>{{ $lead->s4 }}</td>
                            <td>{{ $lead->s5 }}</td>
                            <td>{{ $lead->received }}</td>
                            <td>{{ $lead->payout }}</td>
                            <td>{{ $lead->created_at }}</td>
                            <td>{{ $lead->updated_at }}</td>
                            <td>{{ $lead->time_interval }}</td>
                            <td>{{ $leadStatuses[$lead->lead_status] }}</td>
                            <td>
                            {!! Form::button('Show', ['class' => 'btn btn-default show-details','data-lead_id' => $lead->id,'data-name' => $lead->name, 'data-rCount' => $lead->retry_count, 'data-rDate' => $lead->last_retry_date, 'data-source' => $lead->lead_source]) !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Lead ID</th>
                        <th>Campaign</th>
                        <th>Creative</th>
                        <th>Affiliate</th>
                        <th>Email</th>
                        <th>S1</th>
                        <th>S2</th>
                        <th>S3</th>
                        <th>S4</th>
                        <th>S5</th>
                        <th>Received</th>
                        <th>Payout</th>
                        <th>Lead Date</th>
                        <th>Lead Updated</th>
                        <th>Time Interval</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="container-fluid row">
            {!! Form::button('Select All', ['class' => 'btn btn-default','id' => 'select-all','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only select the leads currently in the page']) !!}
            {!! Form::button('De-Select All', ['class' => 'btn btn-default','id' => 'de-select-all','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only de-select the leads currently in the page']) !!}

            @if(isset($inputs['table']))
                {!! Form::button('Re-send leads', ['class' => 'btn btn-default','id' => 'resend-leads','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only resend the leads currently in the page', 'data-table' => $inputs['table']]) !!}
            @else
                {!! Form::button('Re-send leads', ['style' => 'display:none', 'class' => 'btn btn-default','id' => 'resend-leads','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only resend the leads currently in the page']) !!}
            @endif

            {{--@if(isset($inputs['table']) && $inputs['table']=='leads')--}}
                {{--{!! Form::button('Re-send leads', ['class' => 'btn btn-default','id' => 'resend-leads','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only resend the leads currently in the page']) !!}--}}
            {{--@else--}}
                {{--{!! Form::button('Re-send leads', ['style' => 'display:none', 'class' => 'btn btn-default','id' => 'resend-leads','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only resend the leads currently in the page']) !!}--}}
            {{--@endif--}}
        </div>

    </div>
</div>
@stop

@section('modals')
<div class="modal fade draggable-drilldown-modal" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">

    <?php
        $attributes = [
                'url'       => url('updateLeadRejectionRateSettings'),
                'class'         => 'this_form',
                'data-confirmation' => 'Are you sure you want to update?',
                'data-process'  => 'update_lead_rejection_rate_settings'
        ];
    ?>

    {!! Form::open($attributes) !!}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Lead Rejection Rate Settings</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="appendHere row">
                        <div class="col-md-12">
                            {!! Form::label('lead_rejection_rate','High Rejection Rate:') !!}
                        </div>
                        <div class="col-md-6">
                            <div class="form-inline">
                              <div class="form-group">
                                <div class="input-group">
                                  <label class="input-group-addon" for="min_high_reject_rate">Min</label>
                                  {!! Form::text('min_high_reject_rate', 0 ,
                                    array('class' => 'form-control this_field rejection_rate_field', 'id' => 'min_high_reject_rate', 'required' => true)) !!}
                                  <div class="input-group-addon">%</div>
                                </div>
                              </div>
                            </div>
                            
                        </div>
                        <div class="col-md-6">
                            <div class="form-inline">
                              <div class="form-group">
                                <div class="input-group">
                                  <div class="input-group-addon" for="max_high_reject_rate">Max</div>
                                  {!! Form::text('max_high_reject_rate', 0 ,
                                    array('class' => 'form-control this_field rejection_rate_field', 'id' => 'max_high_reject_rate', 'required' => true)) !!}
                                  <div class="input-group-addon">%</div>
                                </div>
                              </div>
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-top:5px">
                            {!! Form::label('lead_rejection_rate','Critical Rejection Rate:') !!}
                        </div>
                        <div class="col-md-6">
                            <div class="form-inline">
                              <div class="form-group">
                                <div class="input-group">
                                  <div class="input-group-addon" for="min_critical_reject_rate">Min</div>
                                  {!! Form::text('min_critical_reject_rate', 0 ,
                                    array('class' => 'form-control this_field', 'id' => 'min_critical_reject_rate', 'disabled' => 'true')) !!}
                                  <div class="input-group-addon">%</div>
                                </div>
                              </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inline">
                              <div class="form-group">
                                <div class="input-group">
                                  <div class="input-group-addon">Max</div>
                                  {!! Form::text('max_critical_reject_rate', 100 ,
                                    array('class' => 'form-control this_field', 'id' => 'max_critical_reject_rate', 'disabled' => 'true')) !!}
                                  <div class="input-group-addon">%</div>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('partials.error')
            </div>
            <div class="modal-footer">
                {!! Form::button('Close', array('class' => 'btn btn-default','data-dismiss' => 'modal')) !!}
                {!! Form::submit('Update', array('class' => 'btn btn-primary')) !!}
            </div>
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="/bower_components/select2/dist/js/select2.min.js"></script>

<script src="{{ asset('js/admin/search_leads.min.js') }}"></script>
@stop
