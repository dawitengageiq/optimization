<div id="graph_tab">
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <!-- <li class="{!! ($inputs['chart_type'] == '#date_range') ? 'active' : '' !!}">
            <a href="#date_range" data-toggle="tab">Specific Affiliates In Date Range</a>
        </li>
        <li class="{!! ($inputs['chart_type'] == '#all_affiliate') ? 'active' : '' !!}">
            <a href="#all_affiliate" data-toggle="tab">All Affiliates In Specific Date</a>
        </li>
        <li class="{!! ($inputs['chart_type'] == '#all_inbox') ? 'active' : '' !!}">
            <a href="#all_inbox" data-toggle="tab">Add All Inbox Manually</a>
        </li> -->
        <li class="{!! ($inputs['chart_type'] == '#date_range_multiple') ? 'active' : '' !!}">
            <a href="#date_range_multiple" data-toggle="tab">Multiple Affiliates In Date Range</a>
        </li>
    </ul>
    <div class="tab-content clearfix">
        <div class="{!! ($inputs['chart_type'] == '#date_range') ? 'active ' : '' !!}tab-pane col-lg-12" id="date_range">
            <span>Create graph/chart of a specific affiliates in reference to revenue tracker id by date range. </span>
        </div>
        <div class="{!! ($inputs['chart_type'] == '#all_affiliate') ? 'active ' : '' !!}tab-pane col-lg-12" id="all_affiliate">
            <span>Create graph/chart of all affiliates that have record on a selected date. </span>
        </div>
        <div class="{!! ($inputs['chart_type'] == '#all_inbox') ? 'active ' : '' !!}tab-pane col-lg-12" id="all_inbox">
            <span>Manually add all inbox revenue data on a specified date and generate graph.
            <span class="text-danger" style="">Note:</span>
            Empty value won't update and no records on database won't insert a new row. </span>
        </div>
        <div class="{!! ($inputs['chart_type'] == '#date_range_multiple') ? 'active ' : '' !!}tab-pane col-lg-12" id="date_range_multiple">
            Records of affiliates by date range.
            <span class="text-danger" style="">Note:</span>
            No chart/graph where generated. </span>
        </div>
    </div>
</div>
