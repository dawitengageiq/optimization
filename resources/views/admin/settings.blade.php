@extends('app')

@section('title')
    Settings
@stop

@section('header')
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<link href="{{ asset('https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/codemirror-5.32.0/lib/codemirror.css') }}" rel="stylesheet">
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
    input[type="checkbox"] {
        -ms-transform: scale(1.5);
        -moz-transform: scale(1.5);
        -webkit-transform: scale(1.5);
        -o-transform: scale(1.5);
        transform: scale(1.5);
        padding: 10px;
        margin-right: 5px;
    }
</style>
@stop

@section('content')

<div class="row">
    <?php
        $attributes = [
            'url'                   => 'admin/updateSettings',
            'class'                 => 'this_form',
            'data-confirmation'     => 'are you sure you want to update the settings?',
            'data-process'          => 'update_settings',
            'id'                    => 'save-settings'
        ];
    ?>
    {!! Form::open($attributes) !!}
    <div class="form-group this_error_wrapper">
        <div class="alert alert-danger this_errors">

        </div>
    </div>

    <div class="col-md-6">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Campaign Filter Settings</h3>
                </div>
                <div class="panel-body">
                    @if(isset($settings['campaign_filter_process_status']))
                        {!! Form::hidden('campaign_filter_process_status_id', $settings['campaign_filter_process_status']->id , ['id' => 'campaign_filter_process_status_id']) !!}
                        {!! Form::hidden('campaign_filter_process_status_update', 0 , ['id' => 'campaign_filter_process_status_update']) !!}
                        {!! Form::label('campaign_filter_process_status',$settings['campaign_filter_process_status']->name) !!}
                        {!! Form::select('campaign_filter_process_status',$statuses, $settings['campaign_filter_process_status']->integer_value,array('class' => 'form-control this_field','required' => 'true'));!!}
                    @endif
                    <br>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Cake Conversions Settings</h3>
                </div>
                <div class="panel-body">
                    @if(isset($settings['cake_conversions_archiving_age_in_days']))
                        {!! Form::hidden('cake_conversions_archiving_age_in_days_update', 0 , ['id' => 'cake_conversions_archiving_age_in_days_update']) !!}
                        {!! Form::hidden('cake_conversions_archiving_age_in_days_id', $settings['cake_conversions_archiving_age_in_days']->id , ['id' => 'cake_conversions_archiving_age_in_days_id']) !!}
                        {!! Form::label('cake_conversions_archiving_age_in_days',$settings['cake_conversions_archiving_age_in_days']->name) !!}
                        {!! Form::text('cake_conversions_archiving_age_in_days',
                        $settings['cake_conversions_archiving_age_in_days']->integer_value != null ? $settings['cake_conversions_archiving_age_in_days']->integer_value : 0,
                        ['required','class' => 'this_field form-control', 'id' => 'cake_conversions_archiving_age_in_days','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['cake_conversions_archiving_age_in_days']->description != null ? $settings['cake_conversions_archiving_age_in_days']->description : '']) !!}
                    @endif
                    <br>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Campaign Type Settings</h3>
                </div>
                <div class="panel-body">
                    @if(isset($settings['campaign_reordering_status']))
                        {!! Form::hidden('campaign_reordering_status', $settings['campaign_reordering_status']->integer_value , ['id' => 'campaign_reordering_status']) !!}
                        {!! Form::hidden('campaign_reordering_status_id', $settings['campaign_reordering_status']->id , ['id' => 'campaign_reordering_status_id']) !!}
                        {!! Form::label('campaign_reordering',$settings['campaign_reordering_status']->name) !!}
                        {!! Form::checkbox('campaign_reordering',$settings['campaign_reordering_status']->integer_value, $settings['campaign_reordering_status']->integer_value == 1, ['data-toggle' => 'toggle', 'data-on' => 'Enabled', 'data-off' => 'Disabled', 'data-size' => 'small']) !!}
                    @endif

                    @if(isset($settings['mixed_coreg_campaign_reordering_status']))
                        <br><br>
                        {!! Form::hidden('mixed_coreg_campaign_reordering_status', $settings['mixed_coreg_campaign_reordering_status']->integer_value , ['id' => 'mixed_coreg_campaign_reordering_status']) !!}
                        {!! Form::hidden('mixed_coreg_campaign_reordering_status_id', $settings['mixed_coreg_campaign_reordering_status']->id , ['id' => 'mixed_coreg_campaign_reordering_status_id']) !!}
                        {!! Form::label('mixed_coreg_campaign_reordering',$settings['mixed_coreg_campaign_reordering_status']->name) !!}
                        {!! Form::checkbox('mixed_coreg_campaign_reordering',$settings['mixed_coreg_campaign_reordering_status']->integer_value, $settings['mixed_coreg_campaign_reordering_status']->integer_value == 1, ['data-toggle' => 'toggle', 'data-on' => 'Enabled', 'data-off' => 'Disabled', 'data-size' => 'small']) !!}
                    @endif

                    @if(isset($settings['campaign_type_view_count']))
                        <br><br>
                        {!! Form::hidden('campaign_type_view_count_update', 0 , ['id' => 'campaign_type_view_count_update']) !!}
                        {!! Form::hidden('campaign_type_view_count_id', $settings['campaign_type_view_count']->id , ['id' => 'campaign_type_view_count_id']) !!}
                        {!! Form::label('campaign_type_view_count',$settings['campaign_type_view_count']->name) !!}
                        {!! Form::text('campaign_type_view_count',$settings['campaign_type_view_count']->integer_value != null ? $settings['campaign_type_view_count']->integer_value : 0, ['required','class' => 'this_field form-control', 'id' => 'campaign_type_view_count','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['campaign_type_view_count']->description != null ? $settings['campaign_type_view_count']->description : '']) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Campaign Tracking Settings</h3>
                </div>
                <div class="panel-body">
                    @if(isset($settings['user_nos_before_not_displaying_campaign']))
                        {!! Form::hidden('user_nos_before_not_displaying_campaign_update', 0 , ['id' => 'user_nos_before_not_displaying_campaign_update']) !!}
                        {!! Form::hidden('user_nos_before_not_displaying_campaign_id', $settings['user_nos_before_not_displaying_campaign']->id , ['id' => 'user_nos_before_not_displaying_campaign_id']) !!}
                        {!! Form::label('user_nos_before_not_displaying_campaign',$settings['user_nos_before_not_displaying_campaign']->name) !!}
                        {!! Form::text('user_nos_before_not_displaying_campaign',
                            $settings['user_nos_before_not_displaying_campaign']->integer_value != null ? $settings['user_nos_before_not_displaying_campaign']->integer_value : 0,
                            ['required','class' => 'this_field form-control', 'id' => 'user_nos_before_not_displaying_campaign','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['user_nos_before_not_displaying_campaign']->description != null ? $settings['user_nos_before_not_displaying_campaign']->description : '']) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">CPL Tracking</h3>
                </div>
                <div class="panel-body">
                    @if(isset($settings['cplchecker_excluded_campaigns']))
                        {!! Form::hidden('cplchecker_excluded_campaigns_update', 0 , ['id' => 'cplchecker_excluded_campaigns_update']) !!}
                        {!! Form::hidden('cplchecker_excluded_campaigns_id', $settings['cplchecker_excluded_campaigns']->id , ['id' => 'cplchecker_excluded_campaigns_id']) !!}
                        {!! Form::label('cplchecker_excluded_campaigns',$settings['cplchecker_excluded_campaigns']->name) !!}
                        {!! Form::textarea('cplchecker_excluded_campaigns',$settings['cplchecker_excluded_campaigns']->description,
                            ['id' => 'cplchecker_excluded_campaigns','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'comma separate', 'style' => 'margin-bottom:5px']) !!}
                    @endif
                    @if(isset($settings['nocpl_recipient']))
                        {!! Form::hidden('nocpl_recipient_update', 0 , ['id' => 'nocpl_recipient_update']) !!}
                        {!! Form::hidden('nocpl_recipient_id', $settings['nocpl_recipient']->id , ['id' => 'nocpl_recipient_id']) !!}
                        {!! Form::label('nocpl_recipient',$settings['nocpl_recipient']->name) !!}
                        {!! Form::textarea('nocpl_recipient',$settings['nocpl_recipient']->description,
                            ['id' => 'nocpl_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate']) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Opt Out Settings</h3>
                </div>
                <div class="panel-body">
                    @if(isset($settings['optoutreport_recipient']))
                        {!! Form::hidden('optoutreport_recipient_update', 0 , ['id' => 'optoutreport_recipient_update']) !!}
                        {!! Form::hidden('optoutreport_recipient_id', $settings['optoutreport_recipient']->id , ['id' => 'optoutreport_recipient_id']) !!}
                        {!! Form::label('optoutreport_recipient',$settings['optoutreport_recipient']->name) !!}
                        {!! Form::textarea('optoutreport_recipient',$settings['optoutreport_recipient']->description,
                            ['id' => 'optoutreport_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate', 'style' => 'margin-bottom:5px']) !!}
                    @endif
                    @if(isset($settings['ccpaadminemail_recipient']))
                        {!! Form::hidden('ccpaadminemail_recipient_update', 0 , ['id' => 'ccpaadminemail_recipient_update']) !!}
                        {!! Form::hidden('ccpaadminemail_recipient_id', $settings['ccpaadminemail_recipient']->id , ['id' => 'ccpaadminemail_recipient_id']) !!}
                        {!! Form::label('ccpaadminemail_recipient',$settings['ccpaadminemail_recipient']->name) !!}
                        {!! Form::textarea('ccpaadminemail_recipient',$settings['ccpaadminemail_recipient']->description,
                            ['id' => 'ccpaadminemail_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate', 'style' => 'margin-bottom:5px']) !!}
                    @endif
                    @if(isset($settings['optoutexternal_recipient']))
                        {!! Form::hidden('optoutexternal_recipient_update', 0 , ['id' => 'optoutexternal_recipient_update']) !!}
                        {!! Form::hidden('optoutexternal_recipient_id', $settings['optoutexternal_recipient']->id , ['id' => 'optoutexternal_recipient_id']) !!}
                        {!! Form::label('optoutexternal_recipient',$settings['optoutexternal_recipient']->name) !!}
                        {!! Form::textarea('optoutexternal_recipient',$settings['optoutexternal_recipient']->description,
                            ['id' => 'optoutexternal_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate', 'style' => 'margin-bottom:5px']) !!}
                    @endif
                    @if(isset($settings['optoutemail_replyto']))
                        {!! Form::hidden('optoutemail_replyto_update', 0 , ['id' => 'optoutemail_replyto_update']) !!}
                        {!! Form::hidden('optoutemail_replyto_id', $settings['optoutemail_replyto']->id , ['id' => 'optoutemail_replyto_id']) !!}
                        {!! Form::label('optoutemail_replyto',$settings['optoutemail_replyto']->name) !!}
                        {!! Form::text('optoutemail_replyto',$settings['optoutemail_replyto']->description,
                            ['id' => 'optoutemail_replyto','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate']) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Enable / Disable 100% Rejection Report?</h3>
                </div>
                <div class="panel-body">
                    {!! Form::hidden('full_rejection_alert_status_update', 0 , ['id' => 'full_rejection_alert_status_update']) !!}
                    @if(isset($settings['full_rejection_alert_status']))
                        {!! Form::hidden('full_rejection_alert_status', $settings['full_rejection_alert_status']->integer_value , ['id' => 'full_rejection_alert_status']) !!}
                        {!! Form::hidden('full_rejection_alert_status_id', $settings['full_rejection_alert_status']->id , ['id' => 'full_rejection_alert_status_id']) !!}
                        {!! Form::label('full_rejection_alert_status','Status') !!}
                        {!! Form::checkbox('full_rejection_alert_status_checkbox',$settings['full_rejection_alert_status']->integer_value, $settings['full_rejection_alert_status']->integer_value == 1, ['data-toggle' => 'toggle', 'data-on' => 'Enabled', 'data-off' => 'Disabled', 'data-size' => 'small', 'id' => 'full_rejection_alert_status_checkbox']) !!}
                    @endif
                    @if(isset($settings['full_rejection_alert_min_leads']))
                        <br><br>
                        {!! Form::hidden('full_rejection_alert_min_leads_id', $settings['full_rejection_alert_min_leads']->id , ['id' => 'full_rejection_alert_min_leads_id']) !!}
                        {!! Form::label('full_rejection_alert_min_leads',$settings['full_rejection_alert_min_leads']->name) !!}
                        {!! Form::text('full_rejection_alert_min_leads',$settings['full_rejection_alert_min_leads']->description != null ? $settings['full_rejection_alert_min_leads']->description : 0, ['required','class' => 'this_field form-control', 'id' => 'full_rejection_alert_min_leads','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'no. of leads']) !!}
                    @endif
                    @if(isset($settings['full_rejection_alert_check_days']))
                        <br>
                        {!! Form::hidden('full_rejection_alert_check_days_id', $settings['full_rejection_alert_check_days']->id , ['id' => 'full_rejection_alert_check_days_id']) !!}
                        {!! Form::label('full_rejection_alert_check_days',$settings['full_rejection_alert_check_days']->name) !!}
                        {!! Form::text('full_rejection_alert_check_days',$settings['full_rejection_alert_check_days']->description != null ? $settings['full_rejection_alert_check_days']->description : 0, ['required','class' => 'this_field form-control', 'id' => 'full_rejection_alert_check_days','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'no. of days']) !!}
                    @endif
                    @if(isset($settings['full_rejection_advertiser_email_status']))
                        <br>
                        {!! Form::hidden('full_rejection_advertiser_email_status_id', $settings['full_rejection_advertiser_email_status']->id , ['id' => 'full_rejection_advertiser_email_status_id']) !!}
                        <input type="checkbox" name="full_rejection_advertiser_email_status" 
                        id="full_rejection_advertiser_email_status"
                        value="1" {!! $settings['full_rejection_advertiser_email_status']->integer_value == 1 ? 'checked' : '' !!}>
                        {!! Form::label('full_rejection_advertiser_email_status',$settings['full_rejection_advertiser_email_status']->name) !!}
                    @endif
                    @if(isset($settings['full_rejection_deactivate_campaign_status']))
                        <br>
                        {!! Form::hidden('full_rejection_deactivate_campaign_status_id', $settings['full_rejection_deactivate_campaign_status']->id , ['id' => 'full_rejection_deactivate_campaign_status_id']) !!}
                        <input type="checkbox" name="full_rejection_deactivate_campaign_status" 
                        id="full_rejection_deactivate_campaign_status"
                        value="1" {!! $settings['full_rejection_deactivate_campaign_status']->integer_value == 1 ? 'checked' : '' !!}>
                        {!! Form::label('full_rejection_deactivate_campaign_status',$settings['full_rejection_deactivate_campaign_status']->name) !!}
                    @endif
                    @if(isset($settings['full_rejection_excluded_campaigns']))
                        {!! Form::hidden('full_rejection_excluded_campaigns_id', $settings['full_rejection_excluded_campaigns']->id , ['id' => 'full_rejection_excluded_campaigns_id']) !!}
                        {!! Form::label('full_rejection_excluded_campaigns',$settings['full_rejection_excluded_campaigns']->name) !!}
                        {!! Form::textarea('full_rejection_excluded_campaigns',$settings['full_rejection_excluded_campaigns']->description,
                            ['id' => 'full_rejection_excluded_campaigns','class' => 'form-control this_field', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'comma separate', 'style' => 'margin-bottom:5px']) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Click Log Tracing</h3>
                </div>
                <div class="panel-body">
                    {!! Form::hidden('click_log_tracing_update', 0 , ['id' => 'click_log_tracing_update']) !!}
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::label('affiliate_id', 'Select Traffic') !!}
                            <button id="clearClickLogTrafficSourceSelect" type="button" class="btn btn-danger btn-xs pull-right">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                            <button id="addClickLogTrafficBtn" type="button" class="btn btn-success btn-xs pull-right" style="margin-right:5px">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                            {!! Form::select('affiliate_id', [], null, ['class' => 'form-control search-affiliate-select', 'id' => 'clickLogTrafficSourceSelect', 'name' => 'affiliate_id', 'style' => 'width: 100%']) !!}
                        </div>
                        <!-- <div class="col-md-5">
                            @if(isset($settings['click_log_num_days']))
                                {!! Form::hidden('click_log_num_days_id', $settings['click_log_num_days']->id , ['id' => 'click_log_num_days_id']) !!}
                                {!! Form::label('click_log_num_days', $settings['click_log_num_days']->name) !!}
                                {!! Form::select('click_log_num_days', ['' => ''] + range(0, 100), $settings['click_log_num_days']->integer_value, ['id' => 'click_log_num_days','class' => 'form-control', 'style' => 'width: 100%', 'required' => true]) !!}
                            @endif
                        </div> -->
                        <div class="col-md-12">
                            <br>
                            <table id="clicks-log-source-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table text-center">
                                <thead>
                                    <tr>
                                        <th>Affiliate/Revenue Tracker</th>
                                        <th>Date Added</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th>Affiliate/Revenue Tracker</th>
                                        <th>Date Added</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-12">
                            @if(isset($settings['clic_logs_apply_all_affiliates']))
                                {!! Form::hidden('clic_logs_apply_all_affiliates_id', $settings['clic_logs_apply_all_affiliates']->id , ['id' => 'clic_logs_apply_all_affiliates_id']) !!}
                                <input type="checkbox" name="clic_logs_apply_all_affiliates" 
                                id="clic_logs_apply_all_affiliates"
                                value="1" {!! $settings['clic_logs_apply_all_affiliates']->integer_value == 1 ? 'checked' : '' !!}>
                                {!! Form::label('clic_logs_apply_all_affiliates', $settings['clic_logs_apply_all_affiliates']->name) !!}
                                
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Leads Settings</h3>
                </div>
                <div class="panel-body">

                    @if(isset($settings['send_pending_lead_cron_expiration']))
                        {!! Form::hidden('send_pending_lead_cron_expiration_id', $settings['send_pending_lead_cron_expiration']->id , ['id' => 'send_pending_lead_cron_expiration_id']) !!}
                        {!! Form::hidden('send_pending_lead_cron_expiration_update', 0 , ['id' => 'send_pending_lead_cron_expiration_update']) !!}
                        {!! Form::label('send_pending_lead_cron_expiration',$settings['send_pending_lead_cron_expiration']->name) !!}
                        {!! Form::text('send_pending_lead_cron_expiration',
                            $settings['send_pending_lead_cron_expiration']->integer_value != null ? $settings['send_pending_lead_cron_expiration']->integer_value : 0,
                            ['required','class' => 'this_field form-control', 'id' => 'send_pending_lead_cron_expiration','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['send_pending_lead_cron_expiration']->description != null ? $settings['send_pending_lead_cron_expiration']->description : '']) !!}
                    @endif
                    <br>
                    @if(isset($settings['leads_archiving_age_in_days']))
                        {!! Form::hidden('leads_archiving_age_in_days_id', $settings['leads_archiving_age_in_days']->id , ['id' => 'leads_archiving_age_in_days_id']) !!}
                        {!! Form::hidden('leads_archiving_age_in_days_update', 0 , ['id' => 'leads_archiving_age_in_days_update']) !!}
                        {!! Form::label('leads_archiving_age_in_days',$settings['leads_archiving_age_in_days']->name) !!}
                        {!! Form::text('leads_archiving_age_in_days',
                            $settings['leads_archiving_age_in_days']->integer_value != null ? $settings['leads_archiving_age_in_days']->integer_value : 0,
                            ['required','class' => 'this_field form-control', 'id' => 'leads_archiving_age_in_days','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['leads_archiving_age_in_days']->description != null ? $settings['leads_archiving_age_in_days']->description : '']) !!}
                    @endif
                    <br>
                    @if(isset($settings['num_leads_to_process_for_send_pending_leads']))
                        {!! Form::hidden('num_leads_to_process_for_send_pending_leads_update', 0 , ['id' => 'num_leads_to_process_for_send_pending_leads_update']) !!}
                        {!! Form::hidden('num_leads_to_process_for_send_pending_leads_id', $settings['num_leads_to_process_for_send_pending_leads']->id , ['id' => 'num_leads_to_process_for_send_pending_leads_id']) !!}
                        {!! Form::label('num_leads_to_process_for_send_pending_leads',$settings['num_leads_to_process_for_send_pending_leads']->name) !!}
                        {!! Form::text('num_leads_to_process_for_send_pending_leads',
                            $settings['num_leads_to_process_for_send_pending_leads']->integer_value != null ? $settings['num_leads_to_process_for_send_pending_leads']->integer_value : 0,
                            ['required','class' => 'this_field form-control', 'id' => 'num_leads_to_process_for_send_pending_leads','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['num_leads_to_process_for_send_pending_leads']->description != null ? $settings['num_leads_to_process_for_send_pending_leads']->description : '']) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Lead Rejection Rate Settings</h3>
                    {!! Form::hidden('update_rejection_rate', 0 , ['id' => 'update_rejection_rate']) !!}
                    {!! Form::hidden('high_critical_rejection_rate_id', $settings['high_critical_rejection_rate']->id , ['id' => 'high_critical_rejection_rate_id']) !!}
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::label('lead_rejection_rate','High Rejection Rate:') !!}
                        </div>
                        <div class="col-md-6">
                            <div class="form-inline">
                              <div class="form-group">
                                <div class="input-group">
                                  <label class="input-group-addon" for="min_high_reject_rate">Min</label>
                                  {!! Form::hidden('min_high_reject_rate_hidden', $rejection_rates[0] , ['id' => 'min_high_reject_rate_hidden']) !!}
                                  {!! Form::text('min_high_reject_rate', $rejection_rates[0] ,
                                    array('class' => 'form-control this_field rejection_rate_field', 'id' => 'min_high_reject_rate')) !!}
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
                                  {!! Form::hidden('max_high_reject_rate_hidden', $rejection_rates[1] , ['id' => 'max_high_reject_rate_hidden']) !!}
                                  {!! Form::text('max_high_reject_rate', $rejection_rates[1] ,
                                    array('class' => 'form-control this_field rejection_rate_field', 'id' => 'max_high_reject_rate')) !!}
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
                                  {!! Form::text('min_critical_reject_rate', $rejection_rates[1] + .1 ,
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
                        @if(isset($settings['high_rejection_keywords']))
                        {!! Form::hidden('high_rejection_keywords_id', $settings['high_rejection_keywords']->id , ['id' => 'high_rejection_keywords_id']) !!}
                        {!! Form::hidden('high_rejection_keywords_update', 0 , ['id' => 'high_rejection_keywords_update']) !!}
                            <?php 
                                $keywords = json_decode($settings['high_rejection_keywords']->description, true);
                                foreach($keywords as $key => $word):
                            ?>
                            <div class="col-md-6" style="margin-top:5px">
                                <label>{!! $high_rejection_type_names[$key].' Keywords' !!}</label>
                                <textarea class="form-control this_field hrk_txtb" rows="2" name="{!! 'high_rejection_keywords['.$key.']' !!}">{!! $word !!}</textarea>
                            </div>
                            <?php endforeach ?>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Email Recipient Settings</h3>
                    {!! Form::hidden('recipient_change', 0 , ['id' => 'recipientChange']) !!}
                </div>
                <div class="panel-body">
                    @if(isset($settings['default_admin_email']))
                        {!! Form::hidden('default_admin_email_id', $settings['default_admin_email']->id , ['id' => 'default_admin_email_id']) !!}
                        {!! Form::label('default_admin_email',$settings['default_admin_email']->name) !!}
                        {!! Form::text('default_admin_email',
                            $settings['default_admin_email']->string_value != null ? $settings['default_admin_email']->string_value : 0,
                            ['required','class' => 'this_field form-control email_recipient', 'id' => 'default_admin_email','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => $settings['default_admin_email']->description != null ? $settings['default_admin_email']->description : '']) !!}
                    @endif
                    @if(isset($settings['report_recipient']))
                        <br>
                        {!! Form::hidden('report_recipient_id', $settings['report_recipient']->id , ['id' => 'report_recipient_id']) !!}
                        {!! Form::label('report_recipient',$settings['report_recipient']->name) !!}
                        {!! Form::textarea('report_recipient',$settings['report_recipient']->description,
                            ['id' => 'report_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate']) !!}
                    @endif
                    
                    @if(isset($settings['qa_recipient']))
                        <br>
                        {!! Form::hidden('qa_recipient_id', $settings['qa_recipient']->id , ['id' => 'qa_recipient_id']) !!}
                        {!! Form::label('qa_recipient',$settings['qa_recipient']->name) !!}
                        {!! Form::textarea('qa_recipient',$settings['qa_recipient']->description,
                            ['id' => 'qa_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate']) !!}
                    @endif
                    @if(isset($settings['error_email_recipient']))
                        <br>
                        {!! Form::hidden('error_email_recipient_id', $settings['error_email_recipient']->id , ['id' => 'error_email_recipient_id']) !!}
                        {!! Form::label('error_email_recipient',$settings['error_email_recipient']->name) !!}
                        {!! Form::textarea('error_email_recipient',$settings['error_email_recipient']->description,
                            ['id' => 'error_email_recipient','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate']) !!}
                    @endif
                    @if(isset($settings['js_midpath_email_report']))
                        <br>
                        {!! Form::hidden('js_midpath_email_report_id', $settings['js_midpath_email_report']->id , ['id' => 'js_midpath_email_report_id']) !!}
                        {!! Form::label('js_midpath_email_report',$settings['js_midpath_email_report']->name) !!}
                        {!! Form::textarea('js_midpath_email_report',$settings['js_midpath_email_report']->description,
                            ['id' => 'js_midpath_email_report','class' => 'form-control this_field email_recipient', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'semicolon separate']) !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">EIQ Javascript Path Revenue Share Percentage</h3>
                    {!! Form::hidden('publisher_percentage_revenue_update', 0 , ['id' => 'publisher_percentage_revenue_update']) !!}
                </div>
                <div class="panel-body">
                    @if(isset($settings['publisher_percentage_revenue']))
                        {!! Form::hidden('publisher_percentage_revenue_id', $settings['publisher_percentage_revenue']->id , ['id' => 'publisher_percentage_revenue_id']) !!}
                        {!! Form::label('publisher_percentage_revenue',$settings['publisher_percentage_revenue']->name) !!}
                        <div class="input-group">
                        {!! Form::select('publisher_percentage_revenue', ['' => ''] + range(0, 100), $settings['publisher_percentage_revenue']->description, ['id' => 'publisher_percentage_revenue','class' => 'form-control', 'style' => 'width: 100%', 'required' => true]) !!}
                        <div class="input-group-addon">%</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Excluded from Datafeed</h3>
                    {!! Form::hidden('data_feed_excluded_affiliates_update', 0 , ['id' => 'data_feed_excluded_affiliates_update']) !!}
                </div>
                <div class="panel-body">
                    @if(isset($settings['data_feed_excluded_affiliates']))
                        {!! Form::hidden('data_feed_excluded_affiliates_id', $settings['data_feed_excluded_affiliates']->id , ['id' => 'data_feed_excluded_affiliates_id']) !!}
                        {!! Form::label('data_feed_excluded_affiliates',$settings['data_feed_excluded_affiliates']->name) !!}
                        {!! Form::textarea('data_feed_excluded_affiliates',$settings['data_feed_excluded_affiliates']->description,
                            ['id' => 'data_feed_excluded_affiliates','class' => 'form-control this_field', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'comma separate']) !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Excessive Affiliate SubIDs</h3>
                    {!! Form::hidden('excessive_affiliate_subids_update', 0 , ['id' => 'excessive_affiliate_subids_update']) !!}
                </div>
                <div class="panel-body">
                    @if(isset($settings['excessive_affiliate_subids']))
                        {!! Form::hidden('excessive_affiliate_subids_id', $settings['excessive_affiliate_subids']->id , ['id' => 'excessive_affiliate_subids_id']) !!}
                        <small>These affiliates have excessive affiliate subID issue on cake and temporarily excluded from getting the clicks ang payout in cake in order not to disrupt the generation of the daily revenu report</small>
                        {!! Form::textarea('excessive_affiliate_subids',$settings['excessive_affiliate_subids']->description,
                            ['id' => 'excessive_affiliate_subids','class' => 'form-control this_field', 'rows' => '2','data-toggle' => 'tooltip','data-placement' => 'bottom','title' => 'comma separate']) !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Stack Settings</h3>
          </div>
          <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    {!! Form::label('stack_page_order', $settings['stack_path_campaign_type_order']->name) !!}
                    {!! Form::hidden('stack_page_order', '') !!}
                    {!! Form::hidden('stack_page_order_id', $settings['stack_path_campaign_type_order']->id , ['id' => 'stack_page_order_id']) !!}
                </div>
                <div class="col-md-4">
                    <label class="text-right">No. of Campaigns per Page</label>
                    {!! Form::hidden('campaign_type_path_limit_id', $settings['campaign_type_path_limit']->id , ['id' => 'campaign_type_path_limit_id']) !!}
                </div>
                <div class="col-md-4">
                    <label>Campaign Type Benchmark</label>
                    {!! Form::hidden('campaign_type_benchmarks_id', $settings['campaign_type_benchmarks']->id , ['id' => 'campaign_type_benchmarks_id']) !!}
                </div>
            </div>
            
            <?php 
                $benchmark = json_decode($settings['campaign_type_benchmarks']->description, true);
            ?>
            <ul id="stack_order_table" class="list-group">
                @foreach($path_order as $order => $type)
                    <li class="list-group-item" data-id="{!! $type !!}" >
                        <div class="row">
                            <div class="col-md-4" style="margin-top: 5px;">
                                {!! $campaign_types[$type] !!}
                            </div>
                            <div class="col-md-4">
                            @if($type != 4 && $type != 6)
                                {!! Form::text('page_limit['.$type.']', isset($campaign_type_limit[$type]) ? $campaign_type_limit[$type] : null, ['class' => 'form-control this_field']) !!}
                            @endif
                            </div>
                            <div class="col-md-4">
                                @if(isset($campaigns[$type]))
                                <?php 
                                    $val = isset($benchmark[$type]) ? $benchmark[$type] : null;
                                    if($type == 4) {
                                        $choices = config('constants.EXTERNAL_CAMPAIGN_AFFILIATE_REPORT_ID');
                                    }else {
                                        $choices = $campaigns[$type];
                                    }
                                ?>
                                {!! Form::select('campaign_type_benchmark['.$type.']', ['' => ''] + $choices, $val, ['class' => 'form-control search-affiliate-select', 'id' => 'affiliate_id', 'style' => 'width: 100%', 'required' => true]) !!}
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
          </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Pixel Settings</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    @if(isset($settings['header_pixel']))
                    <div class="col-md-12 form-group">
                        {!! Form::hidden('header_pixel_id', $settings['header_pixel']->id , ['id' => 'header_pixel_id']) !!}
                        {!! Form::hidden('header_pixel_status', 0 , ['id' => 'header_pixel_status']) !!}
                        {!! Form::label('header_pixel',$settings['header_pixel']->name) !!}
                        {!! Form::textarea('header_pixel',$settings['header_pixel']->description,
                            array('id' => 'uni_pixel_header', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif
                    @if(isset($settings['body_pixel']))
                    <div class="col-md-12 form-group">  
                        {!! Form::hidden('body_pixel_id', $settings['body_pixel']->id , ['id' => 'body_pixel_id']) !!}
                        {!! Form::hidden('body_pixel_status', 0 , ['id' => 'body_pixel_status']) !!}
                        {!! Form::label('body_pixel',$settings['body_pixel']->name) !!}
                        {!! Form::textarea('body_pixel',$settings['body_pixel']->description,
                            array('id' => 'uni_pixel_body', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif 
                    @if(isset($settings['footer_pixel']))
                    <div class="col-md-12 form-group">  
                        {!! Form::hidden('footer_pixel_id', $settings['footer_pixel']->id , ['id' => 'footer_pixel_id']) !!}
                        {!! Form::hidden('footer_pixel_status', 0 , ['id' => 'footer_pixel_status']) !!}
                        {!! Form::label('footer_pixel',$settings['footer_pixel']->name) !!}
                        {!! Form::textarea('footer_pixel',$settings['footer_pixel']->description,
                            array('id' => 'uni_pixel_footer', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif 
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">TCPA</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    @if(isset($settings['epicdemand_tcpa']))
                    <div class="col-md-12 form-group">
                        {!! Form::hidden('epicdemand_tcpa_id', $settings['epicdemand_tcpa']->id , ['id' => 'epicdemand_tcpa_id']) !!}
                        {!! Form::hidden('epicdemand_tcpa_status', 0 , ['id' => 'epicdemand_tcpa_status']) !!}
                        {!! Form::label('epicdemand_tcpa',$settings['epicdemand_tcpa']->name) !!}
                            {!! Form::textarea('epicdemand_tcpa',$settings['epicdemand_tcpa']->description,
                            array('id' => 'epicdemand_tcpa', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif
                    @if(isset($settings['pfr_tcpa']))
                    <div class="col-md-12 form-group">  
                        {!! Form::hidden('pfr_tcpa_id', $settings['pfr_tcpa']->id , ['id' => 'pfr_tcpa_id']) !!}
                        {!! Form::hidden('pfr_tcpa_status', 0 , ['id' => 'pfr_tcpa_status']) !!}
                        {!! Form::label('pfr_tcpa',$settings['pfr_tcpa']->name) !!}
                        {!! Form::textarea('pfr_tcpa',$settings['pfr_tcpa']->description,
                            array('id' => 'pfr_tcpa', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif 
                    @if(isset($settings['admired_tcpa']))
                    <div class="col-md-12 form-group">  
                        {!! Form::hidden('admired_tcpa_id', $settings['admired_tcpa']->id , ['id' => 'admired_tcpa_id']) !!}
                        {!! Form::hidden('admired_tcpa_status', 0 , ['id' => 'admired_tcpa_status']) !!}
                        {!! Form::label('admired_tcpa',$settings['admired_tcpa']->name) !!}
                        {!! Form::textarea('admired_tcpa',$settings['admired_tcpa']->description,
                            array('id' => 'admired_tcpa', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif 
                    @if(isset($settings['clinical_trial_tcpa']))
                    <div class="col-md-12 form-group">  
                        {!! Form::hidden('clinical_trial_tcpa_id', $settings['clinical_trial_tcpa']->id , ['id' => 'clinical_trial_tcpa_id']) !!}
                        {!! Form::hidden('clinical_trial_tcpa_status', 0 , ['id' => 'clinical_trial_tcpa_status']) !!}
                        {!! Form::label('clinical_trial_tcpa',$settings['clinical_trial_tcpa']->name) !!}
                        {!! Form::textarea('clinical_trial_tcpa',$settings['clinical_trial_tcpa']->description,
                            array('id' => 'clinical_trial_tcpa', 'class' => 'form-control this_field')) !!}
                    </div>
                    @endif 
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-12 col-lg-12" align="center">
        {!! Form::submit('Save', ['class' => 'btn btn-primary','id' => 'settingsSubmitBtn']) !!}
        {!! Html::link(url('admin/dashboard'),'Cancel',['class' =>'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
</div>
@stop

@section('footer')
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js') }}"></script>
<script src="{{ asset('bower_components/ckeditor/ckeditor.js')}}"></script>
<script src="{{ asset('bower_components/ckeditor/adapters/jquery.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/lib/codemirror.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/addon/edit/matchbrackets.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/htmlmixed/htmlmixed.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/xml/xml.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/javascript/javascript.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/css/css.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/clike/clike.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/mode/php/php.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/addon/search/search.js')}}"></script>
<script src="{{ asset('bower_components/codemirror-5.32.0/addon/search/searchcursor.js')}}"></script>
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('js/admin/settings.min.js?v=1.2') }}"></script>
@stop