<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="EngageIQ - Lead Reactor">
    <meta name="_token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" type="image/png" href="/images/favicon.ico"/>

    <title>Engage IQ - Lead Reactor</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="{{ asset('bower_components/metisMenu/dist/metisMenu.min.css') }}" rel="stylesheet">

    <!-- App CSS -->
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet">

    <!-- Timeline CSS
    <link href="{{ asset('css/timeline.css') }}" rel="stylesheet"> -->

    <!-- Custom CSS
    <link href="{{ asset('css/sb-admin-2.css') }}" rel="stylesheet"> -->

    <!-- EIQ CSS
    <link href="{{ asset('css/style.css') }}" rel="stylesheet"> -->

    <!-- Morris Charts CSS -->
    <link href="{{ asset('bower_components/morrisjs/morris.css') }}" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    @yield('header')
</head>

<body>

<?php
$advertiser = auth()->user()->advertiser;
$affiliate = auth()->user()->affiliate;
?>

<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;background-color:#286090;">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            @if(Auth::check() && Auth::user()->isAdministrator())
                <a style="color:#fff" class="navbar-brand" href="{{ url('admin') }}">Lead Reactor</a>
            @else(Auth::check() && Auth::user()->isUser())
                <a style="color:#fff" class="navbar-brand" href="{{ url('') }}">Lead Reactor</a>
            @endif

        </div>
        <!-- /.navbar-header -->

        <!-- BUG REPORT START-->
        <ul class="nav navbar-top-links navbar-right pull-right">
            <li id="bugReportDropdown" class="dropdown" >
                <a class="dropdown-toggle this_is_white for_pop_over" data-toggle="dropdown" href="#">
                    <i class="fa fa-exclamation for_pop_over" aria-hidden="true"></i>  <i class="fa fa-caret-down for_pop_over"></i>
                </a>
                <ul id="bugReportDiv" class="dropdown-menu dropdown-user">
                    <li>
                        <?php
                            $attributes = [
                                'id'                    => 'report_bug_form',
                                'url'                   => 'report_bug',
                                'class'                 => 'form_with_file',
                                'data-confirmation'     => '',
                                'data-process'          => 'report_bug',
                                'files'                 => true
                            ];
                        ?>
                        {!! Form::open($attributes) !!}
                        <div class="row">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <div class="row">
                                            <div class="col-md-2 col-sm-1 col-xs-1">
                                                <i class="fa fa-exclamation" aria-hidden="true" style="padding: 6px 16px !important;"></i>
                                            </div>
                                            <div class="col-md-10 col-sm-11 col-xs-11">
                                            {!! Form::text('bug_summary','',
                                                array('id' => 'bug_summary','class' => 'form-control this_field', 'required' => 'true', 'placeholder' => 'Summary')) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <div class="row">
                                            <div class="col-md-2 col-sm-1 col-xs-1">
                                                <button id="attachBugFilesBtn" class="btn btn-default" type="button">
                                                    <i class="fa fa-paperclip" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div class="col-md-10 col-sm-11 col-xs-11">
                                                <ul id="bugFileList" class="list-group" style="width: 100%;margin-bottom:0px">
                                                  <!-- <li class="list-group-item">
                                                    <span class="bug-file-name">sample.txt</span> <span class="bug-file-size">(42K)</span>
                                                    <button type="button" class="close removeBugFileBtn"><span aria-hidden="true">&times;</span></button>
                                                  </li> -->
                                                  <li class="list-group-item" style="height:34px"></li>
                                                </ul>
                                            </div>
                                        </div>
                                        {!! Form::file('bug_evidence_files[]', array('id' => 'bug_evidence_files','class' => 'hidden','multiple' => 'true','accept'=> "image/*")) !!}
                                        {!! Form::hidden('list_of_files','',array('id' => 'final_list_of_files')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-12">
                                        {!! Form::textarea('bug_description','',
                                        array('id' => 'bug_description','class' => 'form-control this_field', 'rows' => '4', 'placeholder' => 'Bug Description')) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-md-12 text-center" style="margin-top:5px;font-size:16px;margin-bottom:13px;">
                                        <span id="bugReportDate">{!! Carbon::now()->format('m/d/Y') !!}</span>
                                    </div>
                                    <div class="col-md-12">
                                        <button id="bugReportSubmitBtn" type="button" class="btn btn-primary this_modal_submit" style="width: 100%;">Submit</button>
                                        <!--{!! Form::submit('Submit', array('class' => 'btn btn-primary this_modal_submit')) !!} -->
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-12">
                                <div class="form-group this_error_wrapper" style="margin-top: 15px;">
                                    <div class="alert alert-danger this_errors">

                                    </div>
                                </div>
                            </div> -->
                        </div>
                        {!! Form::close() !!}
                    </li>
                </ul>
            </li>
            <!-- /.dropdown -->

            <!-- /.dropdown -->
            <li class="dropdown">
                <a class="dropdown-toggle this_is_white" data-toggle="dropdown" href="#">
                    <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-user">
                    <li>
                        <a href="{{ url('users/'.auth()->user()->id) }}"><i class="fa fa-user fa-fw"></i> User Profile</a>
                    </li>
                    @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_user_action_history')))
                    <li>
                        <a href="{{ url('admin/userActionHistory') }}"><i class="fa fa-history fa-fw"></i> User Action History</a>
                    </li>
                    @endif
                    @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_users_and_roles')))
                        <li>
                            <a href="{{ url('admin/role_management') }}"><i class="fa fa-hand-o-right fa-fw"></i> Role Management</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/user_management') }}"><i class="fa fa-hand-o-right fa-fw"></i> User Management</a>
                        </li>
                    @endif
                    @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_cron_job')))
                        <li>
                            <a href="{{ url('admin/cron_job') }}"><i class="fa fa-clock-o fa-fw"></i> Cron Job Today</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/cron_history') }}"><i class="fa fa-clock-o fa-fw"></i> Cron Job History</a>
                        </li>
                    @endif
                    <li>
                        <!-- only authenticated and admin type of user and super user or has permission to access the settings page -->
                        @if( auth()->check() &&
                             (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_settings'))))

                            <a href="{{ url('admin/settings') }}"><i class="fa fa-gear fa-fw"></i> Settings</a>

                        @endif
                    </li>
                    <li>
                        <!-- only authenticated and admin type of user and super user or has permission to access the settings page -->
                        @if( auth()->check() &&
                             (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_settings'))))

                            <a href="{{ url('admin/banned/leads') }}"><i class="fa fa-times"></i> Banned Leads</a>

                        @endif
                    </li>
                    <li>
                        <!-- only authenticated and admin type of user and super user or has permission to access the settings page -->
                        @if( auth()->check() &&
                             (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_settings'))))

                            <a href="{{ url('admin/banned/attempts') }}"><i class="fa fa-times"></i> Banned Attempts</a>

                        @endif
                    </li>
                    <li class="divider"></li>
                    <li><a href="{{url('auth/logout')}}"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                    </li>
                </ul>
                <!-- /.dropdown-user -->
            </li>
            <!-- /.dropdown -->

        </ul>
        <!-- BUG REPORT END -->

        <!-- /.navbar-top-links -->
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    @if( auth()->check() &&
                         (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_dashboard')) ||
                         $affiliate || $advertiser ))
                        <li>
                            @if($advertiser)
                                <a href="{{ url('advertiser/dashboard') }}"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                            @elseif($affiliate)
                                <a href="{{ url('affiliate/dashboard') }}"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                            @else
                                <a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                            @endif
                        </li>
                    @endif
                    @if( auth()->check() &&
                        (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_affiliate_reports'))))
                        <li>
                            <a href="{{ url('admin/affiliateReports') }}"><i class="fa fa-area-chart fa-fw"></i> Affiliate Reports</a>
                        </li>
                    @endif

                    <li><a href="{{ url('admin/consolidatedGraph') }}"><i class="glyphicon glyphicon-filter"></i> Revenue Funnel</a></li>

                    @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_apply_to_run_request')))

                         <li>
                             <a href="{{ url('admin/affiliate_requests') }}"><i class="fa fa-check-square-o"></i> Apply to Run Request</a>
                         </li>

                    @endif

                    @if( auth()->check() &&
                         (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_reports_and_statistics')) ||
                         $affiliate || $advertiser ))
                         <li>
                             <a href="{{ url('admin/campaign/rejection') }}"><i class="glyphicon glyphicon-exclamation-sign"></i> Campaign Rejection Charts</a>
                         </li>
                    @endif

                    @if( auth()->check() &&
                         (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_search_leads')) ||
                         $affiliate || $advertiser))

                        <li>
                            @if($advertiser)
                                <a href="{{ url('advertiser/searchLeads') }}"><i class="fa fa-search"></i> Search Leads</a>
                            @elseif($affiliate)
                                <a href="{{ url('affiliate/searchLeads') }}"><i class="fa fa-search"></i> Search Leads</a>
                            @else
                                <a href="{{ url('admin/searchLeads') }}"><i class="fa fa-search"></i> Search Leads</a>
                            @endif
                        </li>

                    @endif

                    @if( auth()->check() &&
                         (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_reports_and_statistics')) ||
                         $affiliate || $advertiser ))

                        <li>
                            <a href="#"><i class="fa fa-bar-chart"></i> Page Statistics<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">

                                <li><a href="{{ url('admin/clicksVsRegsStats') }}">Clicks Vs. Regs Stats</a></li>
                                <li><a href="{{ url('admin/pageViewStats') }}">Page View Stats</a></li>
                                <li><a href="{{ url('admin/pageOptinRateStats') }}">Page Optin Rate Stats</a></li>
                                <li><a href="{{ url('admin/clickLogTracer') }}">Click Log Tracer</a></li>

                                @if( auth()->check() &&
                                     (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_revenue_statistics')) || $affiliate || $advertiser))
                                    <li>
                                        @if($advertiser)
                                            <a href="{{ url('advertiser/revenueStatistics') }}">Revenue Statistics</a>
                                        @elseif($affiliate)
                                            <a href="{{ url('affiliate/revenueStatistics') }}">Revenue Statistics</a>
                                        @else
                                            <a href="{{ url('admin/revenueStatistics') }}">Revenue Statistics</a>
                                        @endif
                                    </li>
                                @endif

                                @if( auth()->check() &&
                                    (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_creative_revenue_reports'))))
                                    <li>
                                        <a href="{{ url('admin/creativeReports') }}">Creative Revenue Reports </a>
                                    </li>
                                @endif

                                @if( auth()->check() &&
                                     (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_coreg_reports'))))
                                    <li>
                                        <a href="{{ url('admin/coregReports') }}">Coreg Reports</a>
                                    </li>
                                @endif

                                @if( auth()->check() &&
                                     Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_survey_takers')))

                                    <li>
                                        <a href="{{ url('admin/survey_takers') }}">Survey Takers</a>
                                    </li>
                                @endif

                                <li>
                                     <a href="{{ url('admin/activeCampaigns') }}">Active Campaign</a>
                                 </li>

                                @if( auth()->check() &&
                                     (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_prepop_statistics'))))
                                     <li>
                                         <a href="{{ url('admin/prepopStatistics') }}">Prepop Statistics</a>
                                     </li>
                                @endif

                                {{-- @if( auth()->check() &&
                                    (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_duplicate_leads'))))
                                    <li>
                                        <a href="{{ url('admin/duplicateLeads') }}">Duplicate Leads</a>
                                    </li>
                                @endif --}}
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                    @endif

                    <li>
                        <a href="#"><i class="fa fa-users fa-fw"></i> Contacts<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_affiliates')))

                                <li>
                                    <a href="{{ url('admin/affiliates') }}">Affiliates</a>
                                </li>

                            @endif

                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_advertisers')))

                                <li>
                                    <a href="{{ url('admin/advertisers') }}">Advertisers</a>
                                </li>

                            @endif
                            @if( auth()->check() &&
                                 (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_contacts')) ||
                                 $affiliate || $advertiser ))

                                <li>
                                    @if($advertiser)
                                        <a href="{{ url('advertiser/contacts')}}">Contacts</a>
                                    @elseif($affiliate)
                                        <a href="{{ url('affiliate/contacts')}}">Contacts</a>
                                    @else
                                        <a href="{{ url('admin/contacts')}}">Contacts</a>
                                    @endif
                                </li>

                            @endif
                        </ul>
                    </li>

                    <li>
                        <a href="#"><i class="fa fa-bullhorn fa-fw"></i> Campaign Setup<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            @if( auth()->check() &&
                                 (Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_campaigns')) ||
                                 $affiliate || $advertiser ))

                                <li>
                                    @if($advertiser)
                                        <a href="{{ url('advertiser/campaigns') }}">Campaigns</a>
                                    @elseif($affiliate)
                                        <a href="{{ url('affiliate/campaigns') }}">Campaigns</a>
                                    @else
                                        <a href="{{ url('admin/campaigns') }}">Campaigns</a>
                                    @endif
                                </li>

                            @endif
                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_filter_types')))

                                <li>
                                    <a href="{{ url('admin/filtertypes') }}">Filter Types</a>
                                </li>

                            @endif
                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_revenue_trackers')))

                                <li>
                                    <a href="{{ url('admin/revenuetrackers') }}">Revenue Trackers</a>
                                </li>

                            @endif

                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_gallery')))

                                <li>
                                    <a href="{{ url('admin/gallery') }}">Gallery</a>
                                </li>

                            @endif

                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_zip_master')))
                                <!-- <li>
                                    <a href="{{ url('admin/zip_master') }}"><i class="fa fa-location-arrow fa-fw"></i> Zip Master</a>
                                </li> -->
                                <li>
                                    <a href="{{ url('admin/zip_codes') }}">Zip Codes</a>
                                </li>
                            @endif
                            @if( auth()->check() &&
                                Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_categories')))

                                <li>
                                    <a href="{{ url('admin/categories') }}">Categories</a>
                                </li>
                            @endif
                            @if( auth()->check() &&
                                 Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_settings')))
                                    <li>
                                        <a href="{{ url('admin/settings') }}">Settings</a>
                                    </li>
                            @endif
                        </ul>
                    </li>       

                    {{-- @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_cake_conversions')))

                         <li>
                            <a href="{{ url('admin/cake_conversions') }}"><i class="fa fa-exchange fa-fw"></i> Cake Conversions</a>
                         </li>
                    @endif --}}

                    <!-- only super user administrator can access this section -->

                    {{-- @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_survey_paths')))
                         <li>
                            <a href="{{ url('admin/survey_paths') }}"><span class="glyphicon glyphicon-road" aria-hidden="true"></span> Survey Paths</a>
                         </li>
                    @endif 

                        <li>
                            <a href="{{ url('users/'.auth()->user()->id) }}"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                    --}}
                    
                    {{-- @if( auth()->check() &&
                         Bus::dispatch(new GetUserActionPermission(auth()->user(),'access_user_action_history')))
                            <li>
                                <a href="{{ url('admin/userActionHistory') }}"><i class="fa fa-history fa-fw"></i> User Action History</a>
                            </li>
                    @endif --}}
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>

    <div id="page-wrapper" style="/*margin-top: 25px;*/">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">@yield('title')</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        @yield('content')
    </div>

    <div id="notificationModal" class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #337ab7;color: white;">
            <h4 class="modal-title">Notification</h4>
          </div>
          <div class="modal-body text-center">
            <p id="notification_text">Bug Reported Successfully!</p>
            <button type="button" class="btn btn-primary" data-dismiss="modal" style="margin: 0 auto;display: block;">OK</button>
          </div>
        </div>
      </div>
    </div>

</div>

@yield('modals')

<!-- /#wrapper -->

<span id="baseUrl" hidden>{{ 'https://leadreactor.ourcutebaby.com' }}</span> 

<span id="reportsUrl" hidden>{{ config('app.reports_url') }}</span>
<!-- jQuery -->
<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>

<!-- Bootstrap Core JavaScript -->
<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="{{ asset('bower_components/metisMenu/dist/metisMenu.min.js') }}"></script>

<!-- Custom Theme JavaScript
<script src="{{ asset('js/sb-admin-2.js') }}"></script> -->

<!-- EIQ JavaScript
<script src="{{ asset('js/commons.js') }}"></script> -->

<!-- App JavaScript-->
<script src="{{ asset('js/app.min.js') }}"></script>

@yield('footer')
</body>
</html>
