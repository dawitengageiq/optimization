@extends('app')

@section('title')
    Dashboard
@stop

@section('header')
<!-- Morris Charts CSS -->
<link href="{{ asset('bower_components/morrisjs/morris.css') }}" rel="stylesheet">

<!--<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">-->

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">

<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<!-- Theme included stylesheets -->
<link href="//cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<link href="{{ asset('css/admin/dashboard.min.css') }}" rel="stylesheet">
<style>
    .align-right {
        text-align: right;
    }

    .change-indicator-red {
        color: #FF0000;
    }

    .change-indicator-green {
        color: #00FF00;
    }
</style>
@stop

@section('content')
<span id="dateYesterday" class="hidden">{{ Carbon::now()->subDay()->toDateString() }}</span>
<span id="date7DaysAgo" class="hidden">{{ Carbon::now()->subDay(7)->toDateString() }}</span>
<span id="currentFromDate" class="hidden">{{ Carbon::now()->subDay()->toDateString() }}</span>
<span id="currentToDate" class="hidden">{{ Carbon::now()->subDay(7)->toDateString() }}</span>

<div class="row">
    <div class="col-md-12">
        <div class="container-fluid col-md-4 col-lg-4">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Offer Goes Down</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="offer-goes-down">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Offer Down</th>
                                <th>Publisher</th>
                                <th>7-Day Daily Revenue Avg.</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr><td colspan="4" class="text-center"><small><em>Loading...</em></small></td></tr> --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <input type="hidden" id="allInboxCampaignID" value="{{ env('ALL_INBOX_CAMPAIGN_ID', 286) }}"/>
        <div class="container-fluid col-md-4 col-lg-4">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">All Inbox</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="allInbox-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Revenue</th>
                                <th style="width: 25%;">Number of Records Accepted</th>
                                <th>30-Day Daily Revenue Avg.</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr><td colspan="4" class="text-center"><small><em>Loading...</em></small></td></tr> --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <input type="hidden" id="pushProCampaignID" value="{{ env('PUSH_PRO_CAMPAIGN_ID', 1672) }}"/>
        <div class="container-fluid col-md-4 col-lg-4">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Push Pros</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="pushPros-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Revenue</th>
                                <th style="width: 25%;">Number of Records Accepted</th>
                                <th>30-Day Daily Revenue Avg.</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr><td colspan="4" class="text-center"><small><em>Loading...</em></small></td></tr> --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="container-fluid col-md-3 col-lg-3">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Path6</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="path6-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Up Time</th>
                                <th>Sum Time (ms)</th>
                                <th>Average Time (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="container-fluid col-md-3 col-lg-3">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Path17</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="path17-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Up Time</th>
                                <th>Sum Time (ms)</th>
                                <th>Average Time (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="container-fluid col-md-3 col-lg-3">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Path18</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="path18-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Up Time</th>
                                <th>Sum Time (ms)</th>
                                <th>Average Time (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="container-fluid col-md-3 col-lg-3">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Path19</h3>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <br>
                    <table class="table table-responsive" id="path19-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Up Time</th>
                                <th>Sum Time (ms)</th>
                                <th>Average Time (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="container-fluid col-md-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <b>Notes</b>
                    <div class="pull-right">
                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#CategoryNoteModal">
                            <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="panel-body" style="font-size: 85%;">
                    <div class="row">
                        <div class="col-md-3">
                            <div id="notesCategoryList" class="nav-tabs list-group">
                                <a href="#" class="list-group-item text-center"><small><em>Loading...</em></small></a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="nCat-notes-div" style="display:none">
                                <button id="addNoteBtn" type="button" class="btn btn-primary btn-xs" style="margin-bottom: 5px">
                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </button>
                                <div id="notesList" class="nav-tabs list-group">
                                  <a href="#" class="list-group-item text-center"><small><em>Loading...</em></small></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="nCat-note-div" style="display:none">
                                <?php
                                    $attributes = [
                                            'url'       => url('add_note'),
                                            'class'         => 'this_form',
                                            'data-confirmation' => '',
                                            'data-process'  => 'add_note',
                                            'id' => 'notes_form',
                                    ];
                                ?>
                                {!! Form::open($attributes) !!}
                                {!! Form::hidden('id', '',array('id' => 'this_id','class' => 'this_field')) !!}
                                {!! Form::hidden('category_id', '',array('class' => 'this_field')) !!}
                                <div class="col-md-12 form-div">
                                    {!! Form::label('subject','Subject') !!}
                                    {!! Form::text('subject','',
                                        array('class' => 'form-control this_field', 'required' => 'true')) !!}
                                </div>
                                <div class="col-md-12">
                                    <textarea name="content" style="display:none"></textarea>
                                    <div id="noteEditorQuill">
                                      <p>Hello World!</p>
                                      <p>Some initial <strong>bold</strong> text</p>
                                      <p><br></p>
                                    </div>
                                </div>
                                <div class="col-md-12 noteInfo">
                                    <em>last updated at: <span id="updated_at"></span></em>
                                </div>
                                <div class="col-md-12 form-div" style="padding-top:10px">
                                    <button id="deleteNote" type="button" class="btn btn-sm btn-danger noteInfo">Delete</button>
                                    {!! Form::submit('Save', array('class' => 'btn btn-sm btn-primary this_modal_submit pull-right')) !!}
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="container-fluid col-md-12 col-lg-12col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Top 10 Campaigns By Leads for <span id="topCampaignsByLeadsDate">{{ Carbon::now()->subDay()->toDateString() }}</span>
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
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center"><small><em>Loading...</em></small></td></tr>
                        </tbody>
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
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center"><small><em>Loading...</em></small></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 campaigns by revenue this week-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Campaigns With The Most Changes</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <div class="container-fluid">
                        <div class="row">
                            <table class="table table-responsive" id="top-ten-campaigns-most-changes">
                                <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Revenue / View</th>
                                    <th>Change</th>
                                    <th>Net %</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4 col-lg-4">
                                {!! Form::label('predefined_date_range','Predefined Date Range') !!}
                                {!! Form::select('predefined_date_range',['yesterday' => 'Yesterday', 'last_week' => 'Last Week', 'last_month' => 'Last Month'], '',['class' => 'this_field form-control','id' => 'predefined_date_range']) !!}
                            </div>
                            <div class="form-group col-md-8 col-lg-8">
                                {!! Form::label('affiliate_ids_most_changes','Affiliate ID') !!}
                                <button id="remove_affiliate_id_selections" type="button" class="btn btn-primary btn-xs pull-right">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button>
                                {!! Form::select('affiliate_ids_most_changes', [], null, ['class' => 'form-control search-affiliate-select', 'id' => 'affiliate_ids_most_changes', 'style' => 'width: 100%', 'multiple' => 'multiple']) !!}
                            </div>
                            <div class="form-group col-md-4 col-lg-4">
                                {!! Form::label('term','Term') !!}
                                {!! Form::select('term',['revenue' => 'Revenue'],'term',['class' => 'this_field form-control','id' => 'term']) !!}
                            </div>
                            <div class="form-group col-md-8 col-lg-8 align-right">
                                {!! Form::button('Update Report', ['class' => 'btn btn-primary','id' => 'update-top-ten-most-changes-report']) !!}
                            </div>
                        </div>
                    </div>
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
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center"><small><em>Loading...</em></small></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--Top 10 affiliates by revenue yesterday-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Affiliates by Revenue as of {{ Carbon::now()->subDay()->toDateString() }}</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="top-ten-revenue-yesterday-affiliate">
                        <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>Revenue</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center"><small><em>Loading...</em></small></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--Top 10 affiliates by revenue by week-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Affiliates by Revenue as of this Week</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="top-ten-revenue-week-affiliate">
                        <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>Revenue</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center"><small><em>Loading...</em></small></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--Top 10 affiliates by revenue by month-->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Top 10 Affiliates by Revenue as of this Month</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <table class="table table-responsive" id="top-ten-revenue-month-affiliate">
                        <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>Revenue</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center"><small><em>Loading...</em></small></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Leads summary and statistics -->
        <div class="container-fluid col-md-6 col-lg-6 col-xs-12 col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Leads Summary and Statistics for the Day</h3>
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
    </div>
        
</div>

    <!-- Category Modal -->
    <div class="modal fade" id="CategoryNoteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Notes Category</h4>
          </div>
          <div class="modal-body">
            <button class="btn btn-primary btn-xs pull-right" type="button" data-toggle="collapse" data-target="#noteCategoryCollapse" aria-expanded="false" aria-controls="noteCategoryCollapse" style="margin-bottom: 5px"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            </button><br>
            <div class="collapse" id="noteCategoryCollapse">
              <?php
                $attributes = [
                        'url'       => url('add_notes_category'),
                        'class'         => 'this_form',
                        'data-confirmation' => '',
                        'data-process'  => 'add_notes_category',
                        'id' => 'notes_category_form',
                ];
            ?>
            {!! Form::open($attributes) !!}
            {!! Form::hidden('id', '',array('id' => 'this_id','class' => 'this_field')) !!}
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                    <div class="col-md-12 form-div">
                        {!! Form::label('name','Name') !!}
                        {!! Form::text('name','',
                            array('class' => 'form-control this_field', 'required' => 'true')) !!}
                    </div>
                    <div class="col-md-12 form-div">
                        {!! Form::submit('Save', array('class' => 'btn btn-sm btn-primary this_modal_submit pull-right')) !!}
                    </div>
                </div>
              </div>
            </div>
            {!! Form::close() !!}
            </div>
            
            <table id="notes_category-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="3" class="text-center"><small><em>Loading...</em></small></td></tr>
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

        
</div>

@stop

@section('footer')
<!-- Morris Charts JavaScript -->
<script src="{{ asset('bower_components/raphael/raphael-min.js') }}"></script>
<script src="{{ asset('bower_components/morrisjs/morris.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>

<!-- Main Quill library -->
<script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script src="{{ asset('js/admin/dashboard.min.js') }}"></script>
@stop