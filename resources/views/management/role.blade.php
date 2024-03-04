@extends('app')

@section('title')
Role Management
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<style>
    .permission-label{
        font-size: 14px;
    }

    .panel-default > .panel-heading {
        color: #fff;
        background-color: #286090;
        border-color: #ddd;
    }
</style>

@stop

@section('content')

<button id="addRole" class="btn btn-primary addBtn" type="button">Add Role</button>

<div id="roleModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalLabel">Add Role</h4>
            </div>

            <div class="modal-body">

                <!-- Role info -->
                <div class="row">

                    <input type="hidden" id="roleID" value="">

                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label for="roleName">Role Name</label>
                            <input type="text" class="form-control permission-input-text" id="roleName" placeholder="Role Name">
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control permission-input-text" id="description" placeholder="Description">
                        </div>
                    </div>

                </div>

                <!-- section access permissions -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Main Section Access Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">

                        @if(isset($actionsData['access_dashboard']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_dashboard']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_dashboard']->code }}"><span class="permission-label"> {{ $actionsData['access_dashboard']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_contacts']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_contacts']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_contacts']->code }}"><span class="permission-label"> {{ $actionsData['access_contacts']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_affiliates']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_affiliates']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_affiliates']->code }}"><span class="permission-label"> {{ $actionsData['access_affiliates']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_campaigns']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_campaigns']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_campaigns']->code }}"><span class="permission-label"> {{ $actionsData['access_campaigns']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_advertisers']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_advertisers']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_advertisers']->code }}"><span class="permission-label"> {{ $actionsData['access_advertisers']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_filter_types']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_filter_types']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_filter_types']->code }}"><span class="permission-label"> {{ $actionsData['access_filter_types']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_reports_and_statistics']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_reports_and_statistics']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_reports_and_statistics']->code }}"><span class="permission-label"> {{ $actionsData['access_reports_and_statistics']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_search_leads']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_search_leads']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_search_leads']->code }}"><span class="permission-label"> {{ $actionsData['access_search_leads']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_affiliate_reports']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_affiliate_reports']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_affiliate_reports']->code }}"><span class="permission-label"> {{ $actionsData['access_affiliate_reports']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_revenue_statistics']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_revenue_statistics']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_revenue_statistics']->code }}"><span class="permission-label"> {{ $actionsData['access_revenue_statistics']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_duplicate_leads']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_duplicate_leads']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_duplicate_leads']->code }}"><span class="permission-label"> {{ $actionsData['access_duplicate_leads']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_creative_revenue_reports']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_creative_revenue_reports']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_creative_revenue_reports']->code }}"><span class="permission-label"> {{ $actionsData['access_creative_revenue_reports']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_coreg_reports']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_coreg_reports']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_coreg_reports']->code }}"><span class="permission-label"> {{ $actionsData['access_coreg_reports']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_prepop_statistics']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_prepop_statistics']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_prepop_statistics']->code }}"><span class="permission-label"> {{ $actionsData['access_prepop_statistics']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_revenue_trackers']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_revenue_trackers']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_revenue_trackers']->code }}"><span class="permission-label"> {{ $actionsData['access_revenue_trackers']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_gallery']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_gallery']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_gallery']->code }}"><span class="permission-label"> {{ $actionsData['access_gallery']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_zip_master']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_zip_master']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_zip_master']->code }}"><span class="permission-label"> {{ $actionsData['access_zip_master']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_survey_takers']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_survey_takers']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_survey_takers']->code }}"><span class="permission-label"> {{ $actionsData['access_survey_takers']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_cake_conversions']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_cake_conversions']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_cake_conversions']->code }}"><span class="permission-label"> {{ $actionsData['access_cake_conversions']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_users_and_roles']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_users_and_roles']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_users_and_roles']->code }}"><span class="permission-label"> {{ $actionsData['access_users_and_roles']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_apply_to_run_request']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_apply_to_run_request']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_apply_to_run_request']->code }}"><span class="permission-label"> {{ $actionsData['access_apply_to_run_request']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_categories']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_categories']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_categories']->code }}"><span class="permission-label"> {{ $actionsData['access_categories']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_cron_job']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_cron_job']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_cron_job']->code }}"><span class="permission-label"> {{ $actionsData['access_cron_job']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_survey_paths']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_survey_paths']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_survey_paths']->code }}"><span class="permission-label"> {{ $actionsData['access_survey_paths']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_settings']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_settings']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_settings']->code }}"><span class="permission-label"> {{ $actionsData['access_settings']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['access_user_action_history']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['access_user_action_history']->id}}" class="main-permission permission-input-checkbox" id="{{ $actionsData['access_user_action_history']->code }}"><span class="permission-label"> {{ $actionsData['access_user_action_history']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for campaigns-->
                <div class="panel panel-default sub-section-permissions" id="campaignPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Campaign Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_campaign']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_campaign']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_campaign']->code }}"><span class="permission-label"> {{ $actionsData['use_add_campaign']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_campaign']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_campaign']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_campaign']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_campaign']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- campaign info tabs permissions-->
                <div class="panel panel-default sub-section-permissions" id="campaignInfoPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Campaign Info Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_edit_campaign_info_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_info_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_info_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_info_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_filters_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_filters_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_filters_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_filters_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_affiliates_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_affiliates_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_affiliates_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_affiliates_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_payouts_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_payouts_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_payouts_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_payouts_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_config_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_config_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_config_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_config_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_long_content_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_long_content_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_long_content_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_long_content_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_stack_content_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_stack_content_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_stack_content_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_stack_content_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_campaign_high_paying_content_tab']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_campaign_high_paying_content_tab']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_campaign_high_paying_content_tab']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_campaign_high_paying_content_tab']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_force_edit_default_received_payout']))
                            <div class="col-md-6">
                                <input type="checkbox" data-action_id="{{$actionsData['use_force_edit_default_received_payout']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_force_edit_default_received_payout']->code }}"><span class="permission-label"> {{ $actionsData['use_force_edit_default_received_payout']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for contacts-->
                <div class="panel panel-default sub-section-permissions" id="contactPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Contacts Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_contact']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_contact']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_contact']->code }}"><span class="permission-label"> {{ $actionsData['use_add_contact']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_contact']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_contact']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_contact']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_contact']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_contact']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_contact']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_contact']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_contact']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for affiliates-->
                <div class="panel panel-default sub-section-permissions" id="affiliatesPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Affiliates Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_affiliate']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_affiliate']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_affiliate']->code }}"><span class="permission-label"> {{ $actionsData['use_add_affiliate']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_affiliate']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_affiliate']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_affiliate']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_affiliate']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_affiliate']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_affiliate']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_affiliate']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_affiliate']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for advertisers-->
                <div class="panel panel-default sub-section-permissions" id="advertisersPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Advertisers Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_advertiser']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_advertiser']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_advertiser']->code }}"><span class="permission-label"> {{ $actionsData['use_add_advertiser']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_advertiser']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_advertiser']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_advertiser']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_advertiser']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_advertiser']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_advertiser']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_advertiser']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_advertiser']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for filter types-->
                <div class="panel panel-default sub-section-permissions" id="filterTypesPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Filter Types Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_filter_type']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_filter_type']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_filter_type']->code }}"><span class="permission-label"> {{ $actionsData['use_add_filter_type']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_filter_type']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_filter_type']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_filter_type']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_filter_type']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_filter_type']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_filter_type']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_filter_type']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_filter_type']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for revenue trackers-->
                <div class="panel panel-default sub-section-permissions" id="revenueTrackersPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Revenue Trackers Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_revenue_trackers']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_revenue_trackers']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_revenue_trackers']->code }}"><span class="permission-label"> {{ $actionsData['use_add_revenue_trackers']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_edit_revenue_trackers']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_revenue_trackers']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_revenue_trackers']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_revenue_trackers']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_revenue_trackers']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_revenue_trackers']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_revenue_trackers']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_revenue_trackers']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- modification use permissions for gallery-->
                <div class="panel panel-default sub-section-permissions" id="galleryPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Gallery Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_add_gallery_image']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_add_gallery_image']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_add_gallery_image']->code }}"><span class="permission-label"> {{ $actionsData['use_add_gallery_image']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_gallery_image']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_gallery_image']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_gallery_image']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_gallery_image']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- API permissions-->
                <div class="panel panel-default" id="apiPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">API Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">
                        @if(isset($actionsData['use_api_access']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_api_access']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_api_access']->code }}"><span class="permission-label"> {{ $actionsData['use_api_access']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- use permissions for categories-->
                <div class="panel panel-default sub-section-permissions" id="categoriesPermissions">
                    <div class="panel-heading">
                        <h3 class="panel-title">Categories Permissions</h3>
                    </div>
                    <div class="panel-body permission-group">

                        @if(isset($actionsData['use_edit_category']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_edit_category']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_edit_category']->code }}"><span class="permission-label"> {{ $actionsData['use_edit_category']->name }}</span>
                            </div>
                        @endif

                        @if(isset($actionsData['use_delete_category']))
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <input type="checkbox" data-action_id="{{$actionsData['use_delete_category']->id}}" class="permission-input-checkbox" id="{{ $actionsData['use_delete_category']->code }}"><span class="permission-label"> {{ $actionsData['use_delete_category']->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-group this_error_wrapper">
                    <div class="alert alert-danger this_errors"></div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="saveRole" type="button" class="btn btn-primary">Save</button>
                <button id="updateRole" type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<br><br>
<table id="roles-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th class="col-actions">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </tfoot>
</table>
@stop

@section('footer')
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/admin/roles.min.js') }}"></script>
@stop