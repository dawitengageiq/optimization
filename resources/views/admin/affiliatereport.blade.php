@extends('app')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
<!--<link href="https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.min.css" rel="stylesheet">-->
<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin/affiliatereports.min.css') }}" rel="stylesheet">
@stop

@section('title')
    Affiliate Reports
@stop

@section('content')
<div id="affReportPage-wrapper">
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => '','class'=> 'form-inline','id'=>'updatePublisherReportsTable']) !!}
            {!! Form::hidden('affiliate_type', 1, array('id' => 'affiliate_type')) !!}
            <div class="form-group">
                {!! Form::label('period','Snapshot Period: ',array('style' => 'padding-top: 8px;')) !!}
                <?php
                    $periods = [
                        'none'              =>  'Select Period',
                        // 'today'         =>  'TODAY',
                            'yesterday'     =>  'YESTERDAY',
                            'last_week'     =>  'LAST WEEK',
                            'last_month'    =>  'LAST MONTH'
                    ];
                ?>
                {!! Form::select('period',$periods, 'yesterday',['class' => 'this_field form-control','id' => 'snapshot_period']) !!}
            </div>

            <!-- DATE RANGE -->
            <div class="form-group">

                <label for="email"> Date Range: </label>

                <div class="input-group date">
                    <input type="text" class="form-control date-range-picker" placeholder="Start Date" id="start_date" name="start_date">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="input-group date">
                    <input type="text" class="form-control date-range-picker" placeholder="End Date" id="end_date" name="end_date">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                    </div>
                </div>

            </div>

            {!! Form::submit('View Report', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
</div>
<div>
    <!-- Nav tabs -->
    <ul id="publisher_reports_tab_list" class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#internal_tab" aria-controls="home" role="tab" data-toggle="tab">INTERNAL</a></li>
        <li role="presentation"><a href="#handp_tab" aria-controls="profile" role="tab" data-toggle="tab">H & P</a></li>
        <li role="presentation"><a href="#internal_iframe_tab" aria-controls="profile" role="tab" data-toggle="tab">INTERNAL IFRAME</a></li>
        <!--
        <li role="presentation"><a href="#both_tab" aria-controls="messages" role="tab" data-toggle="tab">BOTH</a></li>
        -->
    </ul>

    {!! Form::hidden('internal_tab_snap_shot', 'yesterday', array('id' => 'internal_tab_snap_shot')) !!}
    {!! Form::hidden('internal_tab_start_date', '', array('id' => 'internal_tab_start_date')) !!}
    {!! Form::hidden('internal_tab_end_date', '', array('id' => 'internal_tab_end_date')) !!}

    {!! Form::hidden('handp_tab_snap_shot', 'yesterday', array('id' => 'handp_tab_snap_shot')) !!}
    {!! Form::hidden('hand_tab_start_date', '', array('id' => 'hand_tab_start_date')) !!}
    {!! Form::hidden('handp_tab_end_date', '', array('id' => 'handp_tab_end_date')) !!}

    {!! Form::hidden('internal_iframe_tab_snap_shot', 'yesterday', array('id' => 'internal_iframe_tab_snap_shot')) !!}
    {!! Form::hidden('internal_iframe_tab_start_date', '', array('id' => 'internal_iframe_tab_start_date')) !!}
    {!! Form::hidden('internal_iframe_tab_end_date', '', array('id' => 'internal_iframe_tab_end_date')) !!}

    <div class="publisher_reports_tab_title panel-body">
        Lead Statistics Report for <span class="pr_sp_periord">yesterday</span>
    </div>

    <!-- Tab panes -->
    <div id="publisher_reports_tab_content" class="tab-content">

        <div id="internal_tab" role="tabpanel" class="tab-pane active">
            <div class="row">

                <?php
                    $attributes = [
                            'url'       => url('uploadReports'),
                            'class'         => 'form-inline form_with_file_not_modal',
                            'data-confirmation' => '',
                            'data-process'  => 'upload_affiliate_reports',
                            'id' => 'uploadReportForm',
                            'enctype' => 'multipart/form-data'
                    ];
                ?>

                <div class="col-md-12">
                    {!! Form::open($attributes) !!}
                    <div class="form-group">
                        <!--{!! Form::submit('Upload', ['class' =>'btn btn-primary this_modal_submit', 'id' => 'upload']) !!}-->
                        <button type="submit" class="btn btn-primary this_modal_submit">Upload</button>
                        {!! Form::file('file', ['id' => 'file','class' => 'file form-control', 'place']) !!}
                        {!! Form::hidden('affiliate_type', 1,array('id' => 'affiliate_type')) !!}
                        <span data-affiliate_type="1" data-download_link_id="#downloadXLSInternal" class="btn btn-primary generate-excel-report">Generate XLS Report</span>
                        {!! Html::link(url('downloadAffiliateReportXLS'),'Download XLS',['class' =>'btn btn-primary download-report-btn', 'id' => 'downloadXLSInternal', 'style' =>'display:none']) !!}

                        {!! Html::link(url('downloadAffiliateReportXLSNSB'),'Download No SubID Breakdown XLS',['class' =>'btn btn-primary download-report-btn', 'id' => 'downloadXLSInternalNSB' , 'style' =>'display:none']) !!}

                        <!--{!! Html::link(url('testing/none/1?start_date=2016-01-01&end_date=2016-01-04'),'TEST',['class' =>'btn btn-primary', 'id' => 'testButton']) !!}-->
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>

            <div class="row this_error_wrapper internal-error-wrapper">
                <!--
                <div class="form-group">
                    <div class="alert alert-danger this_errors"></div>
                </div>
                -->
                <br/>

                <div class="container-fluid">
                    <div class="alert alert-danger alert-dismissible alert-danger-wrapper">
                        <button type="button" class="close close-internal-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-danger-content this_errors"></div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-dismissible alert-warning-wrapper" role="alert">
                        <button type="button" class="close close-internal-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-warning-content"></div>
                        </div>
                    </div>

                    <div class="alert alert-success alert-dismissible alert-success-wrapper" role="alert">
                        <button type="button" class="close close-internal-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-success-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <hr/>

            <div class="row">
                <div class="col-xs-12">
                    <table id="internal-table" class="publisher_reports_table publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                        <thead>
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Clicks/Views</th>
                                <th>Payout</th>
                                <th>Leads</th>
                                <th>Revenue</th>
                                <th>We Get</th>
                                <th>Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Totals</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div id="handp_tab" role="tabpanel" class="tab-pane">

            <div class="row">
                <div class="col-md-12">
                    <span data-affiliate_type="2" data-download_link_id="#downloadXLSHandP" class="btn btn-primary generate-handp-excel-report">Generate XLS Report</span>
                    {!! Html::link(url('downloadAffiliateReportXLS'),'Download XLS',['class' =>'btn btn-primary download-report-btn', 'id' => 'downloadXLSHandP']) !!}
                </div>
            </div>

            <div class="row this_error_wrapper handp-error-wrapper">
                <!--
                <div class="form-group">
                    <div class="alert alert-danger this_errors"></div>
                </div>
                -->

                <br/>
                <div class="container-fluid">
                    <div class="alert alert-danger alert-dismissible alert-danger-wrapper">
                        <button type="button" class="close close-handp-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-danger-content this_errors"></div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-dismissible alert-warning-wrapper" role="alert">
                        <button type="button" class="close close-handp-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-warning-content"></div>
                        </div>
                    </div>

                    <div class="alert alert-success alert-dismissible alert-success-wrapper" role="alert">
                        <button type="button" class="close close-handp-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-success-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>

            <div class="row">
                <div class="col-xs-12">
                    <table id="handp-table" class="publisher_reports_table publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                        <thead>
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Leads</th>
                                <th>Payout</th>
                                <th>Revenue</th>
                                <th>We Get</th>
                                <th>Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Totals</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div id="internal_iframe_tab" role="tabpanel" class="tab-pane">

            <div class="row">
                <div class="col-md-12">
                    <span data-affiliate_type="2" data-download_link_id="#downloadXLSIframe" class="btn btn-primary generate-iframe-excel-report">Generate XLS Report</span>
                    {!! Html::link(url('downloadAffiliateReportXLS'),'Download XLS',['class' =>'btn btn-primary download-report-btn', 'id' => 'downloadXLSIframe']) !!}
                </div>
            </div>

            <div class="row this_error_wrapper internal-iframe-error-wrapper">
                <br/>
                <div class="container-fluid">
                    <div class="alert alert-danger alert-dismissible alert-danger-wrapper">
                        <button type="button" class="close close-internal-iframe-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-danger-content this_errors"></div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-dismissible alert-warning-wrapper" role="alert">
                        <button type="button" class="close close-internal-iframe-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-warning-content"></div>
                        </div>
                    </div>

                    <div class="alert alert-success alert-dismissible alert-success-wrapper" role="alert">
                        <button type="button" class="close close-internal-iframe-report-error" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="form-group">
                            <div class="alert-success-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <hr/>

            <div class="row">
                <div class="col-xs-12">
                    <table id="internal-iframe-table" class="publisher_reports_table publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                        <thead>
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Passovers</th>
                                <th>Payout</th>
                                <th>Leads</th>
                                <th>Revenue</th>
                                <th>We Get</th>
                                <th>Margin</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th>Totals</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        <div id="both_tab" role="tabpanel" class="tab-pane">
            <div class="row">
                <div class="col-xs-12">
                    <table id="both-table" class="publisher_reports_table publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                        <thead>
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Clicks/Views</th>
                                <th>Payout</th>
                                <th>Leads</th>
                                <th>Revenue</th>
                                <th>We Get</th>
                                <th>Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Totals</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 small">
            <span class="label" id="cakeColorIndicator">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            <span>From CAKE</span>
            <br>
            <span class="label" id="nlrColorIndicator">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            <span>From NLR</span>
        </div>
    </div>
</div>
</div>
<div id="prAffiliateWebsiteModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span id="pr_aff_cmp"></span> Lead Statistics for <span class="pr_sp_periord">yesterday</span>
                </h4>
            </div>

            <div class="modal-body">

                <table id="affiliate_website-table" class="publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>Clicks/Views</th>
                            <th>Payout</th>
                            <th>Leads</th>
                            <th>Revenue</th>
                            <th>We Get</th>
                            <th>Margin</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>Totals</th>
                            <td></td>
                            <td></td>
                            <td></td>
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

<div id="handpSubIDModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span id="handp_aff_cmp"></span> Lead Statistics for <span class="pr_sp_periord">yesterday</span>
                </h4>
            </div>
            <div class="modal-body">

                <table id="handp-subid-table" class="publisher_reports_aff_subid_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                    <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>S1</th>
                            <th>S2</th>
                            <th>S3</th>
                            <th>S4</th>
                            <th>S5</th>
                            <th>Leads</th>
                            <th>Payout</th>
                            <th>Revenue</th>
                            <th>We Get</th>
                            <th>Margin</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>Totals</th>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
</div>

<div id="prIframeAffiliateWebsiteModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span id="pr_aff_cmp"></span> Lead Statistics for <span class="pr_sp_periord">yesterday</span>
                </h4>
            </div>

            <div class="modal-body">

                <table id="iframe_affiliate_website-table" class="publisher_reports_table_design table table-bordered table-striped table-hover table-heading table-datatable">
                    <thead>
                    <tr>
                        <th>Website</th>
                        <th>Passovers</th>
                        <th>Payout</th>
                        <th>Leads</th>
                        <th>Revenue</th>
                        <th>We Get</th>
                        <th>Margin</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    <tr>
                        <th>Totals</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="iframeRevenueTrackerDetailsModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span id="iframeRevenueTrackerWebsite"></span>
                </h4>
            </div>
            <div class="modal-body">
                <table id="iframeRevenueTrackersDetailsTable" class="publisher_reports_table_design table table-striped table-hover table-heading table-datatable">
                    <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Leads</th>
                        <th>Revenue</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Totals</th>
                        <td></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="subIDDetailsModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span id="AffRevTitle"></span>
                </h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['url' => url('admin/getClicksVsRegsStats'),'class'=> '', 'id' => 'filter-subid-form']) !!}
                <div class="row">
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
                        <input class="btn btn-primary" type="submit" value="Generate Report" style="margin-left:10px">
                        <a href="#" class="btn btn-primary disabled" id="downloadSubReportBtn">Download Report</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <table id="subIDBreakdownTable" class="publisher_reports_subid_table_design table table-striped table-hover table-heading table-datatable">
                    <thead>
                    <tr>
                        <th>S1</th>
                        <th>S2</th>
                        <th>S3</th>
                        <th>S4</th>
                        <th>S5</th>
                        <th>Clicks/Views</th>
                        <th>Payout</th>
                        <th>Leads</th>
                        <th>Revenue</th>
                        <th>We Get</th>
                        <th>Margin</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <td></td>
                        <td></td>
                        <th></th>
                        <td></td>
                        <td></td>
                        <th></th>
                        <td></td>
                        <td></td>
                        <th></th>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="subIDCampaignDetailsModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span id="subID"></span>
                </h4>
            </div>
            <div class="modal-body">
                <table id="subIDCampaignDetailsTable" class="publisher_reports_table_design table table-striped table-hover table-heading table-datatable">
                    <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Leads</th>
                        <th>Revenue</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Totals</th>
                        <td></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="revenueTrackerDetailsModal" class="modal fade draggable-drilldown-modal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <!--<span id="revenueTrackerWebsite"></span> Lead Statistics for <span class="pr_sp_periord">yesterday</span>-->
                    <span id="revenueTrackerWebsite"></span>
                </h4>
            </div>
            <div class="modal-body">
                <table id="revenueTrackersDetailsTable" class="publisher_reports_table_design table table-striped table-hover table-heading table-datatable">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Leads</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Totals</th>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row hidden">
    <div id="subIDAdditionalForm" class="col-md-12 small">
        <div class="row">
            <div class="col-md-2 col-lg-2">
                {!! Form::text('s1','',['class' => 'this_field form-control', 'id' => 's1', 'placeholder' => 'S1']) !!}
            </div>
            <div class="col-md-2 col-lg-2">
                {!! Form::text('s2','',['class' => 'this_field form-control', 'id' => 's2', 'id' => 's1', 'placeholder' => 'S2']) !!}
            </div>
            <div class="col-md-2 col-lg-2">
                {!! Form::text('s3','',['class' => 'this_field form-control', 'id' => 's3', 'id' => 's1', 'placeholder' => 'S3']) !!}
            </div>
            <div class="col-md-1 col-lg-1">
                {!! Form::text('s4','',['class' => 'this_field form-control', 'id' => 's4', 'id' => 's1', 'placeholder' => 'S4']) !!}
            </div>
            <div class="col-md-1 col-lg-1">
                {!! Form::text('s5','',['class' => 'this_field form-control', 'id' => 's5', 'id' => 's1', 'placeholder' => 'S5']) !!}
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/admin/affiliate_reports.min.js') }}"></script>
@stop