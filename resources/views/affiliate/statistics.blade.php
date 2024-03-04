@extends('affiliate.master')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('statistics-active') active @stop

@section('content')

<!-- REVENUE -->
<div class="container">

    <!-- REVENUE MODAL -->
    <form id="website_sub_revenue" class="modal fade" role="dialog">

        <div class="modal-dialog modal-lg" style="max-width: 1000px; width: 100%;">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 id="campaign_name"></h4>
                    <br>

                    <div class="form-inline" id="modal_predefined_form">

                        <!-- campaign ID -->
                        <input type="hidden" name="campaign_id" id="campaign_id" value="">

                        <div class="form-group">

                            <label for="email">DATE RANGE</label>

                            <div class="input-group date">
                                <input type="text" class="form-control date-range-picker modal-date-picker" id="modal_start_date" placeholder="Start Date" name="modal_start_date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                </div>
                            </div>

                            <div class="input-group date">
                                <input type="text" class="form-control date-range-picker modal-date-picker" id="modal_end_date" placeholder="End Date" name="modal_end_date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                </div>
                            </div>

                        </div>

                        <!-- FILTER -->
                        <div class="form-group">

                            <label for="filter">PREDEFINED FILTERS</label>

                            <select class="form-control" id="modal_filter" name="modal_filter">
                                <option value="">Select Period</option>
                                <option value="today" selected>Today</option>
                                <option value="week_to_date">Week to date</option>
                                <option value="month_to_date">Month to date</option>
                                <option value="year_to_date">Year to date</option>
                            </select>

                        </div>

                        <!-- SUBMIT -->
                        <button type="submit" class="btn btn-default btn-submit">Submit</button>

                    </div>
                </div>

                <div class="modal-body" id="website_modal">
                    <table class="table table-hover" id="website_statistics">
                        <thead class="sub-revenue">
                            <tr>
                                <th class="column-header">Website</th>
                                <th class="column-header">Leads</th>
                                <th class="column-header">Revenue</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>0</th>
                                <th>$0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-submit" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- HOST AND POSTED DEALS AND REG PATH REVENUE-->
<div class="container">

    <form class="form-inline" role="form" id="predefined_form">

        <!-- DATE RANGE -->
        <div class="form-group">

            <label for="email">DATE RANGE</label>

            <div class="input-group date">
                <input type="text" class="form-control date-range-picker hosted-date-picker" placeholder="Start Date" id="hosted_start_date" name="hosted_start_date">
                <div class="input-group-addon">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
            </div>

            <div class="input-group date">
                <input type="text" class="form-control date-range-picker hosted-date-picker" placeholder="End Date" id="hosted_end_date" name="hosted_end_date">
                <div class="input-group-addon">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
            </div>

        </div>

        <!-- FILTER -->
        <div class="form-group">
            <label for="hosted_filter">PREDEFINED FILTERS</label>
            <select class="form-control" id="hosted_filter" name="hosted_filter">
                <option value="">Select Period</option>
                <option value="today" selected>Today</option>
                <option value="week_to_date">Week to date</option>
                <option value="month_to_date">Month to date</option>
                <option value="year_to_date">Year to date</option>
            </select>
        </div>

        <!-- SUBMIT -->
        <button type="submit" class="btn btn-default btn-submit">Submit</button>
    </form>

    <br>
    <h1>External Path Revenue</h1>
    <br>

    <table class="table table-hover" id="external_path_revenue">
        <thead>
            <tr>
                <th class="column-header deals">DATE</th>
                {{-- <th class="column-header deals">LEADS</th> --}}
                <th class="column-header deals">GROSS REVENUE</th>
                <th class="column-header deals">PUBLISHER REVENUE (<span id="publisherRevenueSharePerc"></span>%)</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                {{-- <th>0</th> --}}
                <th>$0.00</th>
                <th>$0.00</th>
            </tr>
        </tfoot>
    </table>

    <br>
    <h1>REG Path Revenue</h1>
    <br>
    <input type="hidden" id="reg_path_rev_email_value" value="{{ $reg_report_email ? $reg_report_email->value : 0}}"/>
    <table class="table table-hover" id="reg_path_revenue">
        <thead>
            <tr>
                <th class="column-header deals">SITES</th>
                <th class="column-header deals">
                    UNIQUE EMAIL COUNT
                </th>
                <th class="column-header deals">REVENUE</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>0</th>
                <th>$0.00</th>
            </tr>
        </tfoot>
    </table>

    <br>
    <h1>Host and Posted Deals</h1>
    <br>

    <table class="table table-hover" id="host_posted_deals">
        <thead>
            <tr>
                <th class="column-header deals">CAMPAIGN</th>
                <th class="column-header deals">
                    <span data-toggle="tooltip" data-placement="top" title="Approved Leads">LEADS</span>
                </th>
                <th class="column-header deals">TOTAL</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>0</th>
                <th>$0.00</th>
            </tr>
        </tfoot>
    </table>
</div>

<div id="regPathRevenueModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    Reg Path Breakdown Report
                </h4>
            </div>

            <div class="modal-body">

                <table id="reg_path_breakdown-table" class="publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Unique Email Count</th>
                            <th>Rate</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>Totals</th>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="row">
                    <div class="col-md-12 small">
                        <span class="label" style="background-color: rgba(211,213,235,1);margin-right: 10px;color: rgba(211,213,235,1);">BLANK</span>
                        <span>From CAKE</span>
                        <br>
                        <span class="label" style="background-color: rgba(231,220,237,1);margin-right: 10px;color: rgba(231,220,237,1);">BLANK</span>
                        <span>From NLR</span>
                    </div>
                </div>
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
<script src="{{ asset('js/affiliate/statistics.min.js') }}"></script>
@endsection
