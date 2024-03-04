@extends('app')

@section('title')
    Dashboard -
    <!-- when you are done remove this notice :) -->
    (Hi developer! Kindly customize the content, data source, data table server side, routes and etc. according to the Affiliate currently using the page!)
@stop

@section('header')
<!-- Morris Charts CSS -->
<link href="{{ URL::asset('bower_components/morrisjs/morris.css') }}" rel="stylesheet">

<link href="{{ URL::asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">

<link href="{{ URL::asset('css/dashboard.css') }}" rel="stylesheet">
@stop

@section('content')
<span id="dateYesterday" class="hidden">{{ Carbon::now()->subDay()->toDateString() }}</span>
<span id="date7DaysAgo" class="hidden">{{ Carbon::now()->subDay(7)->toDateString() }}</span>
    
    <!-- Received Revenue Statistics for Co-Reg Modal -->
    <div id="TotalRevenueDateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <?php
            $attributes = [
                'url'   => 'get_total_revenue_statistics_by_date',
                'id'    => 'totalRevenueStatisticsForm',
                'class' => 'form-inline this_form',
                'data-confirmation'     => '',
                'data-process'          => 'get_total_revenue_statistics'
            ];
            ?>
            {!! Form::open($attributes) !!}
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Received Revenue Statistics for Co-Reg</h4>
              </div>
              <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('','Select Dates:') !!}
                        <div class="input-group date">
                            <input name="total_revenue_from_date" id="total_revenue_from_date" value="" type="text" class="form-control" placeholder="From Date">
                            <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                        </div>
                        <div class="input-group date">
                            <input name="total_revenue_to_date" id="total_revenue_to_date" value="" type="text" class="form-control" placeholder="To Date">
                            <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                        </div>
                    </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Get Statistics</button>
              </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="container-fluid row">
        <div class="container-fluid col-md-12 col-lg-12 col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Received Revenue Statistics for Co-Reg
                    <div class="pull-right">
                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#TotalRevenueDateModal">
                            <i class="fa fa-calendar fa-sm"></i>
                        </button>
                        <button id="refreshTotalRevenueStatisticsChart" class="btn btn-primary btn-xs">
                            <i class="fa fa-refresh fa-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="totalRevenueStatisticsChart" style="height: 250px;"></div>
                </div>
                <!-- /.panel-body -->
            </div>
        </div>

        <!-- Received Revenue Statistics By Affiliates for Co-Reg Modal -->
        <div id="AffiliateRevenueDateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <?php
                $attributes = [
                    'url'   => 'get_affiliate_revenues_by_date',
                    'id'    => 'revenueByAffStatisticsForm',
                    'class' => 'form-inline this_form',
                    'data-confirmation'     => '',
                    'data-process'          => 'get_affiliate_revenues_by_date'
                ];
            ?>
            {!! Form::open($attributes) !!}
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Received Revenue Statistics By Affiliates for Co-Reg</h4>
              </div>
              <div class="modal-body">
                    <div class="form-group">
                    {!! Form::label('','Select Dates:') !!}
                    <div class="input-group date">
                        <input name="affiliate_revenues_from_date" id="affiliate_revenues_from_date" value="" type="text" class="form-control" placeholder="From Date">
                        <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                    </div>
                    <div class="input-group date">
                        <input name="affiliate_revenues_to_date" id="affiliate_revenues_to_date" value="" type="text" class="form-control" placeholder="To Date">
                        <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                    </div>
                    </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Get Statistics</button>
              </div>
            </div>
            {!! Form::close() !!}
          </div>
        </div>

        <div class="container-fluid col-md-12 col-lg-12 col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Received Revenue Statistics By Affiliates for Co-Reg
                    <div class="pull-right">
                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#AffiliateRevenueDateModal">
                            <i class="fa fa-calendar fa-sm"></i>
                        </button>
                        <button id="refreshRevenueByAffStatisticsChart" class="btn btn-primary btn-xs">
                            <i class="fa fa-refresh fa-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="receivedStatisticsByAffiliateChart" style="height: 250px;"></div>
                </div>
            </div>
        </div>

        <!-- Total Survey Takers Per Affiliate Modal -->
        <div id="AffiliateSurveyTakersDateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog" role="document">
            <?php
                $attributes = [
                    'url'   => 'get_affiliate_survey_takers_by_date',
                    'id'    => 'surveyTakersByAffStatisticsForm',
                    'class' => 'form-inline this_form',
                    'data-confirmation'     => '',
                    'data-process'          => 'get_affiliate_survey_takers_by_date'
                ];
            ?>
            {!! Form::open($attributes) !!}
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Total Survey Takers Per Affiliate</h4>
              </div>
              <div class="modal-body">
                    <div class="form-group">
                    {!! Form::label('','Select Dates:') !!}
                    <div class="input-group date">
                        <input name="affiliate_takers_from_date" id="affiliate_takers_from_date" value="" type="text" class="form-control" placeholder="From Date">
                        <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                    </div>
                    <div class="input-group date">
                        <input name="affiliate_takers_to_date" id="affiliate_takers_to_date" value="" type="text" class="form-control" placeholder="To Date">
                        <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                    </div>
                    </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Get Statistics</button>
              </div>
            </div>
            {!! Form::close() !!}
          </div>
        </div>

        <div class="container-fluid col-md-12 col-lg-12 col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Total Survey Takers Per Affiliate
                    <div class="pull-right">
                        <button class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="bottom" title="Disregards emails with 'engageiq' and source urls with 'localhost'.">
                            <i class="fa fa-info-circle fa-sm"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#AffiliateSurveyTakersDateModal">
                            <i class="fa fa-calendar fa-sm"></i>
                        </button>
                        <button id="refreshSurveyTakersByAffStatisticsChart" class="btn btn-primary btn-xs">
                            <i class="fa fa-refresh fa-sm"></i>
                        </button>
                        <!-- <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="Disregards emails with 'engageiq' and source urls with 'localhost'."></i>   -->
                    </div>
                </div>
                <div class="panel-body">
                    <div id="totalSurveyTakersPerAffiliateChart" style="height: 250px;"></div>
                    <!-- <div class="panel panel-default">
                      <div id="totalSurveyTakersPerAffiliateChart-info" class="panel-body">
                      </div>
                    </div> -->
                </div>
            </div>
        </div>

        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Top 5 Campaigns By Leads for <span id="topCampaignsByLeadsDate">{{ Carbon::now()->subDay()->toDateString() }}</span>
                </div>
                <!-- /.panel-heading -->
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="topCampaignsByLeadsBarChart" style="height: 250px;"></div>
                        </div>
                        <div class="col-md-12">
                            <form class="form-inline">
                              <div class="form-group">
                                {!! Form::label('date_top_campaigns_by_leads','Select Date:') !!}
                                <div class="input-group date">
                                    <input name="date_top_campaigns_by_leads" id="date_top_campaigns_by_leads" value="{{ isset($inputs['lead_date_from']) ? $inputs['lead_date_from'] : '' }}" type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                </div>
                              </div>
                              {!! Form::button('Get Campaigns', ['class' => 'btn btn-primary','id' => 'getTopCampaignsByDateSubmit']) !!}
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.panel-body -->
            </div>
        </div>


        <!-- Top 10 campaigns by revenue yesterday-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Campaigns by Revenue as of {{ Carbon::now()->subDay()->toDateString() }}</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="top-ten-revenue-yesterday-campaign">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Cost</th>
                                <th>Revenue</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 campaigns by revenue this week-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Campaigns by Revenue as of this Week</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="top-ten-revenue-week-campaign">
                        <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Cost</th>
                            <th>Revenue</th>
                            <th>Profit</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 campaigns by revenue this month-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Campaigns by Revenue as of this Month</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="top-ten-revenue-month-campaign">
                        <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Cost</th>
                            <th>Revenue</th>
                            <th>Profit</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Leads summary and statistics -->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Leads Summary and Statistics</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive">
                        <tr>
                            <td>Total Number of Success Leads:</td>
                            <td><span id="success-leads">0</span></td>
                        </tr>
                        <tr>
                            <td>Total Number of Rejected Leads:</td>
                            <td><span id="rejected-leads">0</span></td>
                        </tr>
                        <tr>
                            <td>Total Number of Failed Leads:</td>
                            <td><span id="failed-leads">0</span></td>
                        </tr>
                        <tr>
                            <td>Total Number of Pending Leads:</td>
                            <td><span id="pending-leads">0</span></td>
                        </tr>
                        <tr>
                            <td>Total of All Leads:</td>
                            <td><span id="total-leads">0</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!--Campaigns-->
        <div class="container-fluid col-md-12 col-lg-12 col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Active Campaigns</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="dashboard-campaigns">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Cap Type</th>
                            <th>Cap</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@stop

@section('footer')
<!-- Morris Charts JavaScript -->
<script src="{{ URL::asset('bower_components/raphael/raphael-min.js') }}"></script>
<script src="{{ URL::asset('bower_components/morrisjs/morris.min.js') }}"></script>
<script src="{{ URL::asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ URL::asset('js/affiliate/dashboard.js') }}"></script>
@stop