@extends('app')

@section('header')
    <!-- DataTables CSS -->
    <link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

    <!-- DataTables Responsive CSS -->
    <link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/bootstrap-timepicker/css/timepicker.min.css') }}" rel="stylesheet">

    <link href="{{ asset('https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
    <style>
        .btn-group-sm>.btn, .btn-sm {
            font-size: 15px;
        }
        @media (min-width: 992px) {
          .modal-lg {
            width: 1200px;
          }
        }
    </style>
@stop

@section('title')
    Revenue Trackers
@stop

@section('content')

    @if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_revenue_trackers')))
        <button id="addTrkBtn" class="btn btn-primary addBtn" type="button">Add Revenue Tracker</button>
    @endif

    <button id="setExitPageBtn" class="btn btn-primary addBtn" type="button">Set Exit Page</button>

    <textarea id="default_campaign_order" class="hidden" name="default_campaign_order">{!! json_encode($default_order) !!}</textarea>
    <textarea id="default_mixed_coreg_campaign_order" class="hidden" name="default_mixed_coreg_campaign_order">{!! json_encode($default_mixed_coreg_campaign_order) !!}</textarea>
    <textarea id="campaign_names" class="hidden" name="campaign_names">{!! json_encode($campaign_names) !!}</textarea>
    <textarea id="campaign_statuses" class="hidden" name="campaign_statuses">{!! json_encode($campaign_statuses) !!}</textarea>

    <div class="modal fade" id="TrkFormModal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">

            <?php
            $attributes = [
                    'url' 					=> 'add_revenue_tracker',
                    'class'					=> 'this_form',
                    'data-confirmation' 	=> '',
                    'data-process' 			=> 'add_revenue_tracker'
            ];
            ?>

            {!! Form::open($attributes) !!}
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Revenue Tracker</h4>
                </div>

                <div class="modal-body">
                    {!! Form::hidden('this_id', '',array('id' => 'this_id')) !!}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 form-div">
                                    {!! Form::label('website','Website Name') !!}
                                    {!! Form::text('website','', ['class' => 'form-control this_field', 'required' => 'true']) !!}
                                </div>
                                <div class="col-md-6 form-div">
                                    {!! Form::label('link','Tracking Link') !!}
                                    {!! Form::text('link','', ['class' => 'form-control this_field', 'required' => 'true']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-div">
                                    {!! Form::label('affiliate','Affiliate') !!}
                                    {!! Form::select('affiliate',[],null,['class' => 'form-control search-affiliate-select','id' => 'affiliate', 'style' => 'width: 100%']) !!}
                                </div>
                                <div class="col-md-6 form-div">
                                    {!! Form::label('campaign','Campaign ID') !!}
                                    {!! Form::text('campaign','', ['class' => 'form-control this_field', 'required' => 'true']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-div">
                                    {!! Form::label('offer','Offer ID') !!}
                                    {!! Form::text('offer','', ['class' => 'form-control this_field', 'required' => 'true']) !!}
                                </div>
                                <div class="col-md-6 form-div">
                                    {!! Form::label('revenue_tracker','Revenue Tracker ID') !!}
                                    {!! Form::select('revenue_tracker',[],null,['class' => 'form-control search-revenueTracker-select','id' => 'revenue_tracker', 'style' => 'width: 100%']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-div">
                                    {!! Form::label('s1','S1') !!}
                                    {!! Form::text('s1','', ['class' => 'form-control this_field']) !!}
                                </div>
                                <div class="col-md-6 form-div">
                                    {!! Form::label('s2','S2') !!}
                                    {!! Form::text('s2','', ['class' => 'form-control this_field']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-div">
                                    {!! Form::label('s3','S3') !!}
                                    {!! Form::text('s3','', ['class' => 'form-control this_field']) !!}
                                </div>
                                <div class="col-md-6 form-div">
                                    {!! Form::label('s4','S4') !!}
                                    {!! Form::text('s4','', ['class' => 'form-control this_field']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-div">
                                    {!! Form::label('s5','S5') !!}
                                    {!! Form::text('s5','', ['class' => 'form-control this_field']) !!}
                                </div>
                                <div class="col-md-3 form-div">
                                    {!! Form::label('type','Path Type') !!}
                                    {!! Form::select('type', [null=>''] + $path_types, '', ['class' => 'form-control this_field', 'required' => 'true']) !!}
                                </div>
                                 <div class="col-md-3 form-div">
                                    {!! Form::label('order_type','Order Type') !!}
                                    {!! Form::select('order_type',config('constants.PATH_ORDER_TYPE'),null,['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-4 form-div">
                                                    {!! Form::label('crg_limit','Co-reg Limit') !!}
                                                    {!! Form::text('crg_limit','',
                                                    array('class' => 'form-control this_field')) !!}
                                                </div>
                                                <div class="col-md-4 form-div">
                                                    {!! Form::label('ext_limit','External Limit') !!}
                                                    {!! Form::text('ext_limit','',
                                                    array('class' => 'form-control this_field')) !!}
                                                </div>
                                                <div class="col-md-4 form-div">
                                                    {!! Form::label('lnk_limit','Link Out Limit') !!}
                                                    {!! Form::text('lnk_limit','',
                                                    array('class' => 'form-control this_field')) !!}
                                                </div>
                                                <div class="col-md-12">
                                                    <p class="help-block" style="margin-top: -10px;"><em>* leave limit blank for unlimited.</em></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="col-md-6 form-div">
                                    {!! Form::label('exit_page','Exit Page') !!}
                                    {!! Form::select('exit_page',[null=>''] + $exit_page_campaigns,null,['class' => 'form-control this_field']) !!}
                                </div> --}}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-12 form-div">
                                    {!! Form::label('pixel','Affiliate Pixel') !!}
                                    {!! Form::textarea('pixel','',
                                    array('id' => 'pixel','class' => 'form-control this_field', 'rows' => '2', 'overflow' => 'hidden')) !!}
                                </div>
                                <div class="col-md-12 form-div">
                                    {!! Form::label('fire','Affiliate Pixel Fires at') !!}
                                    {!! Form::select('fire', [null=>''] + config('constants.PAGE_FIRE_PIXEL'), '', ['class' => 'form-control this_field']) !!}
                                </div>
                                <div class="col-md-12">
                                    <hr style="margin-top: 0px !important;margin-bottom: 15px !important;">
                                </div>
                                <div class="col-md-12 form-div">
                                    {!! Form::label('pixel_header','Header Pixel') !!}
                                    {!! Form::textarea('pixel_header','',
                                    array('id' => 'pixel_header','class' => 'form-control this_field', 'rows' => '2', 'overflow' => 'hidden')) !!}
                                </div>
                                <div class="col-md-12 form-div">
                                    {!! Form::label('pixel_body','Body Pixel') !!}
                                    {!! Form::textarea('pixel_body','',
                                    array('id' => 'pixel_body','class' => 'form-control this_field', 'rows' => '2', 'overflow' => 'hidden')) !!}
                                </div>
                                <div class="col-md-12 form-div">
                                    {!! Form::label('pixel_footer','Footer Pixel') !!}
                                    {!! Form::textarea('pixel_footer','',
                                    array('id' => 'pixel_footer','class' => 'form-control this_field', 'rows' => '2', 'overflow' => 'hidden')) !!}
                                </div>
                                <div class="col-md-12 form-div">
                                    {!! Form::label('notes','Notes') !!}
                                    {!! Form::textarea('notes','',
                                    array('id' => 'notes','class' => 'form-control this_field', 'rows' => '2', 'overflow' => 'hidden')) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12 form-div">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading">
                                            <strong>Heads Up!</strong><br>
                                            SubID Breakdown Status will only update at midnight
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    {!! Form::label('subid_breakdown','Current Sub ID Breakdown Status') !!}
                                                    {!! Form::select('subid_breakdown',config('constants.UNI_STATUS2'),null,['class' => 'form-control', 'disabled' => true]) !!}
                                                    <div id="currentSIBSetting-div" style="display:none">
                                                        <label class="checkbox-inline">
                                                          <input type="checkbox" name="sib_s1" id="sib_s1" value="1" class="this_field" disabled> S1
                                                        </label>
                                                        <label class="checkbox-inline">
                                                          <input type="checkbox" name="sib_s2" id="sib_s2" value="1" class="this_field"disabled> S2
                                                        </label>
                                                        <label class="checkbox-inline">
                                                          <input type="checkbox" name="sib_s3" id="sib_s3" value="1" class="this_field"disabled> S3
                                                        </label>
                                                        <label class="checkbox-inline">
                                                          <input type="checkbox" name="sib_s4" id="sib_s4" value="1" class="this_field"disabled> S4
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    {!! Form::label('new_subid_breakdown_status','New Sub ID Breakdown Status') !!}
                                                    {!! Form::select('new_subid_breakdown_status',['null'=> ''] + config('constants.UNI_STATUS2'),null,['class' => 'form-control']) !!}

                                                    <div id="newSIBSetting-div" style="display:none">
                                                        <label class="checkbox-inline">
                                                            <input type="hidden" name="nsib_s1" value=""/>
                                                          <input type="checkbox" name="nsib_s1" id="nsib_s1" value="1"> S1
                                                        </label>
                                                        <label class="checkbox-inline">
                                                            <input type="hidden" name="nsib_s2" value=""/>
                                                          <input type="checkbox" name="nsib_s2" id="nsib_s2" value="1"> S2
                                                        </label>
                                                        <label class="checkbox-inline">
                                                            <input type="hidden" name="nsib_s3" value=""/>
                                                          <input type="checkbox" name="nsib_s3" id="nsib_s3" value="1" > S3
                                                        </label>
                                                        <label class="checkbox-inline">
                                                            <input type="hidden" name="nsib_s4" value=""/>
                                                          <input type="checkbox" name="nsib_s4" id="nsib_s4" value="1" > S4
                                                        </label>
                                                    </div>
                                                </div>
                                                    
                                                    
                                            </div>
                                        </div>
                                    </div>
                                       
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group this_error_wrapper">
                                <div class="alert alert-danger this_errors"></div>
                            </div>
                        </div>
                    </div>
                            
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
                    {!! Form::submit('Save', array('class' => 'btn btn-primary this_modal_submit')) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <table id="tracker-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
                <thead>
                <tr>
                    <th>Website</th>
                    <th>Affiliate</th>
                    <th>Campaign ID</th>
                    <th>Offer ID</th>
                    <th>Revenue Tracker ID</th>
                    <th>S1</th>
                    <th>S2</th>
                    <th>S3</th>
                    <th>S4</th>
                    <th>S5</th>
                    <th>Sub ID Breakdown</th>
                    <th class="col-actions">Actions</th>
                </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                <tr>
                    <th>Website</th>
                    <th>Affiliate</th>
                    <th>Campaign ID</th>
                    <th>Offer ID</th>
                    <th>Revenue Tracker ID</th>
                    <th>S1</th>
                    <th>S2</th>
                    <th>S3</th>
                    <th>S4</th>
                    <th>S5</th>
                    <th>Sub ID Breakdown</th>
                    <th>Actions</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="modal fade" id="CmpOrdrFormModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">

            <?php
            $attributes = [
                    'url'                   => 'update_revenue_tracker_campaign_order',
                    'class'                 => 'this_form',
                    'data-confirmation'     => '',
                    'data-process'          => 'update_revenue_tracker_campaign_order',
                    'id'                    => 'campaign_order_form'
            ];
            ?>

            {!! Form::open($attributes) !!}
            {!! Form::hidden('this_id', '',array('id' => 'this_id', 'class' => 'this_field')) !!}
            {!! Form::hidden('rev_tracker_id', '',array('id' => 'rev_tracker_id', 'class' => 'this_field')) !!}
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Revenue Tracker Campaign Order</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-2 form-div">
                            {!! Form::label('rev_tracker_display','Revenue Tracker') !!}
                            {!! Form::text('rev_tracker_display','', ['class' => 'form-control this_field', 'readonly' => 'true']) !!}
                        </div>
                        <div class="col-md-2 form-div">
                            {!! Form::label('reorder','Reorder') !!}
                            <div>
                                {!! Form::checkbox('reorder', 0, false, ['data-toggle' => 'toggle', 'data-on' => 'Enabled', 'data-off' => 'Disabled', 'data-size' => 'small', 'data-width' => '100%', 'data-height' => '34px', 'style' => 'font-size: 30px']) !!}
                            </div>
                        </div>
                        <div class="col-md-4 form-div">
                            {!! Form::label('order_by','Order By') !!}
                            {!! Form::select('order_by',$campaignOrderDirection,null,['class' => 'form-control','id' => 'order_by']) !!}
                        </div>
                        <div class="col-md-4 form-div">
                            {!! Form::label('views','Views') !!}
                            {!! Form::text('views', 0, ['class' => 'form-control this_field']) !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 form-div">
                            {!! Form::label('campaign_type_constants','Campaign Type Order') !!}
                            {!! Form::select('campaign_type_constants',$campaignTypes,null,['class' => 'form-control','id' => 'campaign_type_constants']) !!}
                        </div>
                        <div class="col-md-3 form-div">
                            {!! Form::label('reference_date','Reference Date') !!}
                            {!! Form::text('reference_date','', ['class' => 'form-control this_field', 'readonly' => 'true']) !!}
                        </div>
                        <div class="col-md-12 form-div">
                            <div id="sortCampaignOrderDiv" class="row">
                                @foreach($campaignTypes as $key => $type)
                                    <div class="col-md-12 form-div campaignOrderContainer" id="{!! 'campOrderContainer_'.$key !!}">
                                        {!! Form::hidden("campaign_type_order[$key]", '', ['class' => "campaign_type_order_hidden this_field"]) !!}
                                        {!! Form::label('campaign_order', $type) !!}
                                        <ul id="{!! 'campTypeOrderList_'.$key !!}" class="campOrderGrpList list-group" data-campaign_type="{!! $key !!}">
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group this_error_wrapper">
                        <div class="alert alert-danger this_errors"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
                    {!! Form::submit('Save', array('class' => 'btn btn-primary this_modal_submit')) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="modal fade" id="mixedCoregCampaignOrderFormModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">

            <?php
            $attributes = [
                    'url'                   => 'update_revenue_tracker_mixed_coreg_campaign_order',
                    'class'                 => 'this_form',
                    'data-confirmation'     => '',
                    'data-process'          => 'update_revenue_tracker_mixed_coreg_campaign_order',
                    'id'                    => 'mixed_coreg_campaign_order_form'
            ];
            ?>

            {!! Form::open($attributes) !!}
            {!! Form::hidden('this_id', '',array('id' => 'this_id', 'class' => 'this_field')) !!}
            {!! Form::hidden('mixed_coreg_rev_tracker_id', '',array('id' => 'mixed_coreg_rev_tracker_id', 'class' => 'this_field')) !!}
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Revenue Tracker Mixed Coreg Campaign Order</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-2 form-div">
                            {!! Form::label('mixed_coreg_rev_tracker_display','Revenue&nbsp;Tracker') !!}
                            {!! Form::text('mixed_coreg_rev_tracker_display','', ['class' => 'form-control this_field', 'readonly' => 'true']) !!}
                        </div>
                        <div class="col-md-2 form-div">
                            {!! Form::label('mixed_coreg_reorder','Reorder') !!}
                            <div>
                                {!! Form::checkbox('mixed_coreg_reorder', 0, false, ['data-toggle' => 'toggle', 'data-on' => 'Enabled', 'data-off' => 'Disabled', 'data-size' => 'small', 'data-width' => '100%', 'data-height' => '34px', 'style' => 'font-size: 30px']) !!}
                            </div>
                        </div>
                        <div class="col-md-3 form-div">
                            {!! Form::label('mixed_coreg_order_by','Order By') !!}
                            {!! Form::select('mixed_coreg_order_by',$campaignOrderDirection,null,['class' => 'form-control','id' => 'mixed_coreg_order_by']) !!}
                        </div>
                        <div class="col-md-2 form-div">
                            {!! Form::label('mixed_coreg_limit','Limit') !!}
                            {!! Form::text('mixed_coreg_limit', null, ['class' => 'form-control this_field', 'placeholder' => 'Unlimited']) !!}
                        </div>
                        <div class="col-md-3 form-div">
                            {!! Form::label('mixed_coreg_reference_date','Reference Date') !!}
                            {!! Form::text('mixed_coreg_reference_date','', ['class' => 'form-control this_field', 'readonly' => 'true']) !!}
                        </div>
                        <div class="col-md-4">
                            <div id="mix_coreg_recurrence" class="row">
                                <div class="col-md-12 form-div">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            {!! Form::radio('mixed_coreg_recurrence', 'views', true, ['id' => 'recurrence_views', 'style' => 'display: block; margin-bottom: 0;']) !!}
                                        </span>
                                        <span class="input-group-addon bold" style="width: 67px">
                                            {!! Form::label('mixed_coreg_views','Views', ['style' => 'display: block; margin-bottom: 0;']) !!}
                                        </span>
                                        {!! Form::text('mixed_coreg_views', 0, ['class' => 'form-control this_field text-right']) !!}
                                    </div>
                                </div>
                                <div class="col-md-12 form-div">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            {!! Form::radio('mixed_coreg_recurrence', 'daily', false, ['id' => 'recurrence_daily', 'style' => 'display: block; margin-bottom: 0;']) !!}
                                        </span>
                                        <span class="input-group-addon bold" style="width: 67px">
                                            {!! Form::label('mixed_coreg_daily','Daily', ['style' => 'display: block; margin-bottom: 0;']) !!}
                                        </span>
                                        {!! Form::text('mixed_coreg_daily', 0, ['id' => 'timepicker', 'class' => 'form-control this_field text-right']) !!}
                                        <span class="input-group-addon bold" style="width: 67px">
                                            PST
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-div">
                            <div id="mixedCoregSortCampaignOrderDiv" class="row">
                                <div class="col-md-12 form-div mixedCoregCampaignOrderContainer" id="mixedCoregCampaignOrderContainer">
                                    {!! Form::hidden('mixed_coreg_campaign_order', '', ['class' => 'mixed_coreg_campaign_order_hidden this_field']) !!}
                                    {!! Form::label('campaign_order', 'Mixed Coreg Campaigns') !!}
                                    <ul id="mixedCoregCampaignOrderList" class="mixedCoregCampaignOrderList list-group">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group this_error_wrapper">
                        <div class="alert alert-danger this_errors"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
                    {!! Form::submit('Save', array('class' => 'btn btn-primary this_modal_submit')) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="modal fade" id="setExitPageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Set Exit Page</h4>
                </div>
                <div class="modal-body">
                    <?php
                    $attributes = [
                            'url'                   => 'update_rev_tracker_to_exit_page_list',
                            'class'                 => 'this_form',
                            'data-confirmation'     => '',
                            'data-process'          => 'rev_tracker_to_exit_page_list'
                    ];
                    ?>
                    {!! Form::open($attributes) !!}
                    <div class="row">
                        <div class="col-md-6 form-div">
                            {!! Form::label('exit_page_id','Exit Page') !!}
                            {!! Form::select('exit_page_id',[null=>'DEFAULT EXIT PAGE'] + $exit_page_campaigns,null,['class' => 'form-control this_field', 'id' => 'exit_page_selected']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('revenue_tracker_id','Revenue Tracker') !!}
                            <button id="remove_revenue_tracker_id_selections" type="button" class="btn btn-primary btn-xs pull-right" style="display:none">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                            {!! Form::select('revenue_tracker_id[]', [], null, ['class' => 'form-control search-affiliate-select', 'id' => 'exit_page_rev_tracker_selection', 'style' => 'width: auto !important', 'multiple' => 'multiple']) !!}
                        </div>
                        <div class="col-md-12 form-div">
                            <input class="setExitPageBtn btn btn-primary pull-right btn-xs" type="submit" value="Set Exit Page" disabled="disabled">
                        </div>
                    </div>
                    {!! Form::close() !!}

                    <?php
                    $attributes = [
                            'url'                   => 'update_rev_tracker_to_exit_page_list',
                            'class'                 => 'this_form',
                            'data-confirmation'     => '',
                            'data-process'          => 'rev_tracker_to_exit_page_list'
                    ];
                    ?>
                    {!! Form::open($attributes) !!}
                    {!! Form::hidden('exit_page_id', '',array('id' => 'default_exit_page', 'class' => 'this_field')) !!}
                    <div class="row">
                        <div class="col-md-12 form-div">
                            <hr>
                            <table id="exitPageTracker-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
                                <thead>
                                    <tr>
                                        <th><input id="selectAllEPRT" type="checkbox"></th>
                                        <th>Revenue Tracker</th>
                                        <th>Affiliate</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    {!! Form::close() !!}
                    <div class="form-group this_error_wrapper">
                        <div class="alert alert-danger this_errors"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('footer')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js') }}"></script>
    <script src="{{ asset('js/admin/revenue_trackers.min.js') }}"></script>
@stop
