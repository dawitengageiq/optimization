<div id="graph_search" class="panel panel-default">
    <div class="panel-body" style="position: relative;">
        {!! Form::open(['id' => 'consolidated_search']) !!}
            {!! Form::hidden('chart_type', $inputs['chart_type'], ['id' => 'chart_type']) !!}
            <div class="row">
                <div class="col-md-12 col-lg-8">
                    {!! Form::label('revenue_tracker_id', 'Affiliate / Revenue Tracker Traffic Source', ['id' => 'revenue_tracker_id_label']) !!}
                    <div id="revenue_tracker_selection_wrap" class="input-group">
                        <select id="revenue_tracker_selection" class="form-control" name="revenue_tracker_id[]" required="required">
                            <option id="selection_all" value="">ALL</option>
                            @if(count($affiliates))
                                @foreach($affiliates as $affiliate)
                                       <option value="{!! $affiliate['id'] !!}"
                                           {!! ($inputs['revenue_tracker_id'] == $affiliate['id'] ) ? ' data-selected="true" selected': '' !!}
                                        >
                                            {!! $affiliate['id'] !!} - {!! $affiliate['company'] !!}
                                        </option>
                                @endforeach
                            @endif
                        </select>
                        <span
                            id="clear_affiliates"
                            class="input-group-addon"
                            style="color:#fff; background-color:#337ab7; border:1px solid #337ab7; cursor:pointer;z-index: 999999; position: relative;"
                        >
                            <i class="glyphicon glyphicon-remove"></i>
                        </span>
                    </div>
                </div>
                <div class="col-xs-12 col-md-12 col-lg-4">
                    <div class="row">
                        <div id="legends_wrap" class="form-group col-md-12 col-lg-12">
                            {!! Form::label('legends', 'Legends / Graphs Columns', ['id' => 'legends_label']) !!}
                            <select name="legends[]" id="legends" class="form-control">
                                @if(count($legends))
                                    @foreach($legends as $legend => $details)
                                        <option value="{!! $legend !!}"
                                        {!! (count($inputs['legends']) > 1) ?
                                            (in_array($legend, $inputs['legends'])) ? 'data-selected="true" selected': '' : 'data-selected="true"' !!}>
                                             {!! $details['alias'] !!}
                                         </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-8">
                    <div class="row">
                        <div class="col-md-9 col-lg-4">
                            {!! Form::label('predefine_dates', 'Predefine Dates', ['id' => 'predefine_dates_label']) !!}
                            <select id="predefine_dates" class="form-control" name="predefine_dates">
                                <option value=""{{ ($inputs['predefine_dates'] == '') ? ' selected' : ''}}>Use Date Picker</option>
                                <option value="yesterday"{{ ($inputs['predefine_dates'] == 'yesterday') ? ' selected' : ''}}>Yesterday</option>
                                <option value="week_to_date"{{ ($inputs['predefine_dates'] == 'week_to_date') ? ' selected' : ''}}>Week to date</option>
                                <option value="month_to_date"{{ ($inputs['predefine_dates'] == 'month_to_date') ? ' selected' : ''}}>Month to date</option>
                                <option value="last_month"{{ ($inputs['predefine_dates'] == 'last_month') ? ' selected' : ''}}>All of last Month</option>
                            </select>
                        </div>
                        <?php
                            $dateDisplay = $inputs['predefine_dates'] == '' ? '' : 'style="display:none"';
                        ?>
                        <div  id="date_from_wrap" class="date_picker_wrap form-group col-md-4 col-lg-4" {!! $dateDisplay !!}>
                            {!! Form::label('date_from', 'Date From', ['id' => 'date_from_label']) !!}
                            <div class="input-group date">
                                <input id="date_from" class="form-control" name="date_from" type="text" value="{!! $inputs['date_from'] !!}" {!! $inputs['predefine_dates'] == '' ? 'required' : '' !!}>
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-th"></i>
                                </span>
                            </div>
                        </div>
                        <div id="date_to_wrap" class="date_picker_wrap form-group col-md-4 col-lg-4" {!! $dateDisplay !!}>
                            {!! Form::label('date_to', 'Date To', ['id' => 'date_to_label']) !!}
                            <div class="input-group date">
                                <input id="date_to" class="form-control" name="date_to" type="text" value="{!! $inputs['date_to'] !!}"  {!! $inputs['predefine_dates'] == '' ? 'required' : '' !!}>
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-th"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12" style="margin-top:15px">
                    {!! Form::label('include_subids','Include SubIDs in the report:') !!}
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s1" id="sib_s1" value="1" class="this_field sibs" style="display:block !important" {!! $inputs['sib_s1'] ? 'checked' : '' !!}> S1
                    </label>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s2" id="sib_s2" value="1" class="this_field sibs" style="display:block !important" {!! $inputs['sib_s2'] ? 'checked' : '' !!}> S2
                    </label>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s3" id="sib_s3" value="1" class="this_field sibs" style="display:block !important" {!! $inputs['sib_s3'] ? 'checked' : '' !!}> S3
                    </label>
                    <label class="checkbox-inline">
                      <input type="checkbox" name="sib_s4" id="sib_s4" value="1" class="this_field sibs" style="display:block !important" {!! $inputs['sib_s4'] ? 'checked' : '' !!}> S4
                    </label>
                </div>
                <div class="col-xs-12 col-md-12 col-lg-12">
                    <div class="row">
                        <div id="all_inbox_wrap" class="form-group col-md-4 col-lg-4" style="display: none">
                            {!! Form::label('all_inbox_rev', 'All Inbox Revenue', ['id' => 'all_inbox_label']) !!}
                            <input id="all_inbox_rev" class="form-control" name="all_inbox_rev" type="text" value="{!! $inputs['all_inbox_rev'] !!}">
                        </div>
                        <div class="form-group col-md-8 col-lg-8  text-right pull-right" style="padding-top: 24px;">
                            {!! Form::button('Clear', ['id' => 'clear', 'class' => 'btn btn-default']) !!}
                            {!! Form::button('Draw', ['id' => 'draw', 'class' => 'btn btn-primary', 'type' => 'submit']) !!}
                            <a id="export_excel"
                        		onclick="COMMON.spinner(this.id)"
                        		href="/admin/consolidatedGraph/{!! $export_link !!}"
                        		class="btn btn-primary pull-right"
                                style="margin-left: 4px; margin-top: 0;"
                        	>
                        		Export to Excel
                        	</a>
                        </div>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}
        @include('admin.consolidated_graph.includes.spinner')
    </div>
</div>
