@extends('app')

@section('title')
    Search Leads -
    <!-- when you are done remove this notice :) -->
    (Hi developer! Kindly customize the content, data source, data table server side, routes and etc. according to the Advertiser currently using the page!)

@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ URL::asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ URL::asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ URL::asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/searchLeads'),'class'=> '','id'=>'search-leads',]) !!}
            <div class="container-fluid">
                @include('partials.flash')
                @include('partials.error')
            </div>
            <div class="row">
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_id','Lead ID') !!}
                    {!! Form::text('lead_id',isset($inputs['lead_id']) ? $inputs['lead_id'] : '',['class' => 'this_field form-control', 'id' => 'lead_id']) !!}
                </div>
                <div class="form-group col-md-5 col-lg-5">
                    {!! Form::label('campaign_id','Campaign') !!}
                    {!! Form::select('campaign_id',Bus::dispatch(new GetCampaignListAndIDsPair()),isset($inputs['campaign_id']) ? $inputs['campaign_id']: '',['class' => 'this_field form-control','id' => 'campaign_id']) !!}
                </div>
                <div class="form-group col-md-4 col-lg-4">
                    {!! Form::label('affiliate_id','Affiliate ID') !!}
                    {!! Form::text('affiliate_id',isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <div class="form-group col-md-5 col-lg-6">
                    {!! Form::label('containing_data','Containing Data') !!}
                    {!! Form::text('containing_data',isset($inputs['containing_data']) ? $inputs['containing_data'] : '',['class' => 'this_field form-control', 'id' => 'containing_data']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_status','Status') !!}
                    {!! Form::select('lead_status',config('constants.LEAD_STATUS'),isset($inputs['lead_status']) ? $inputs['lead_status'] : '',['class' => 'this_field form-control','id' => 'lead_status']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('limit_rows','Limit Rows') !!}
                    {!! Form::select('limit_rows',config('constants.LIMIT_ROWS'),isset($inputs['limit_rows']) ? $inputs['limit_rows'] : '',['class' => 'this_field form-control','id' => 'limit_rows']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_from','Lead Date From') !!}
                    <div class="input-group date">
                        <input name="lead_date_from" id="lead_date_from" value="{{ isset($inputs['lead_date_from']) ? $inputs['lead_date_from'] : '' }}" type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_to','Lead Date To') !!}
                    <div class="input-group date">
                        <input name="lead_date_to" id="lead_date_to" value="{{ isset($inputs['lead_date_to']) ? $inputs['lead_date_to'] : '' }}" type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('s1','S1') !!}
                    {!! Form::text('s1',isset($inputs['s1']) ? $inputs['s1'] : '',['class' => 'this_field form-control', 'id' => 'sub_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('s2','S2') !!}
                    {!! Form::text('s2',isset($inputs['s2']) ? $inputs['s2'] : '',['class' => 'this_field form-control', 'id' => 'sub_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('s3','S3') !!}
                    {!! Form::text('s3',isset($inputs['s3']) ? $inputs['s3'] : '',['class' => 'this_field form-control', 'id' => 'sub_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('s4','S4') !!}
                    {!! Form::text('s4',isset($inputs['s4']) ? $inputs['s4'] : '',['class' => 'this_field form-control', 'id' => 'sub_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('s5','S5') !!}
                    {!! Form::text('s5',isset($inputs['s5']) ? $inputs['s5'] : '',['class' => 'this_field form-control', 'id' => 'sub_id']) !!}
                </div>
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Search Leads', ['class' => 'btn btn-primary']) !!}
                    @if(session()->has('searched_searched_leads'))
                        {!! Html::link(url('admin/downloadSearchedLeads'),'Download Leads',['class' =>'btn btn-primary', 'id' => 'downloadSearchedLeads']) !!}
                    @endif
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="modal fade" id="lead_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">

        <?php
            $attributes = [
                    'url' 		=> url('admin/updateLeadDetails'),
                    'class'			=> 'this_form',
                    'data-confirmation' => 'Are you sure you want to update the details of this lead?',
                    'data-process' 	=> 'update_lead_details'
            ];
        ?>

        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Leads Detail</h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('lead_csv','Lead CSV') !!}
                                {!! Form::textarea('lead_csv',null,array('class' => 'this_field form-control', 'id' => 'lead_csv', 'required' => 'true', 'rows' => 4)) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('message','Message') !!}
                                {!! Form::textarea('message',null,array('class' => 'this_field form-control', 'id' => 'message', 'rows' => 4)) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('advertiser_data','Advertiser Data') !!}
                                {!! Form::textarea('advertiser_data',null,array('class' => 'this_field form-control', 'id' => 'advertiser_data', 'required' => 'true', 'rows' => 4)) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('sent_result','Sent Result') !!}
                                {!! Form::textarea('sent_result',null,array('class' => 'this_field form-control', 'id' => 'sent_result', 'required' => 'true', 'rows' => 4)) !!}
                            </div>
                        </div>
                    </div>
                    @include('partials.error')
                </div>
                <div class="modal-footer">
                    {!! Form::button('Close', array('class' => 'btn btn-default','data-dismiss' => 'modal')) !!}
                    {!! Form::submit('Update', array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">

        <div class="container-fluid row">
            <table id="leads-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Campaign</th>
                        <th>Affiliate</th>
                        <th>S1</th>
                        <th>S2</th>
                        <th>S3</th>
                        <th>S4</th>
                        <th>S5</th>
                        <th>Received</th>
                        <th>Payout</th>
                        <th>Lead Date</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)

                        <?php
                        $leadStatus = '';
                        switch($lead->lead_status)
                        {
                            case 0:
                                $leadStatus='FAIL';
                                break;

                            case 1:
                                $leadStatus='SUCCESS';
                                break;

                            case 2:
                                $leadStatus='REJECTED';
                                break;
                            case 3:
                                $leadStatus='PENDING';
                                break;
                        }
                        ?>

                        <tr>
                            <td>
                                {!! Form::checkbox('lead-'.$lead->id,0,false,['class' => 'checkbox', 'id' => 'lead-'.$lead->id, 'data-lead_id' => $lead->id]) !!}
                                {{ $lead->id }}
                            </td>
                            <td>{{ $lead->campaign->name }}</td>
                            <td>{{ $lead->affiliate->id }}</td>
                            <th>{{ $lead->s1 }}</th>
                            <th>{{ $lead->s2 }}</th>
                            <th>{{ $lead->s3 }}</th>
                            <th>{{ $lead->s4 }}</th>
                            <th>{{ $lead->s5 }}</th>
                            <td>{{ $lead->received }}</td>
                            <td>{{ $lead->payout }}</td>
                            <td>{{ $lead->created_at }}</td>
                            <td>{{ $leadStatus }}</td>
                            <td>{!! Form::button('Show', ['class' => 'btn btn-default show-details','data-lead_id' => $lead->id,'data-name' => $lead->campaign->name]) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Lead ID</th>
                        <th>Campaign</th>
                        <th>Affiliate</th>
                        <th>S1</th>
                        <th>S2</th>
                        <th>S3</th>
                        <th>S4</th>
                        <th>S5</th>
                        <th>Received</th>
                        <th>Payout</th>
                        <th>Lead Date</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="container-fluid row">
            {!! Form::button('Select All', ['class' => 'btn btn-default','id' => 'select-all','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only select the leads currently in the page']) !!}
            {!! Form::button('De-Select All', ['class' => 'btn btn-default','id' => 'de-select-all','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only de-select the leads currently in the page']) !!}
            {!! Form::button('Re-send leads', ['class' => 'btn btn-default','id' => 'resend-leads','data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'This will only resend the leads currently in the page']) !!}
        </div>

    </div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ URL::asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('js/bootstrap-datepicker.min.js') }}"></script>
<script>

    $(document).ready(function()
    {
        var table = $('.table-datatable').DataTable({
            responsive: true,
            "order": [[ 0, "desc" ]],
            lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
        });

        $('#leads-table tbody').on( 'click', 'tr', function () {
            console.log('first this');
            if ( $(this).hasClass('selected') ) {
                $(this).removeClass('selected');
            }
            else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        $('#clear').click(function()
        {
            var form = $('#search-leads');

            form.find('input:text, input:password, input:file, select, textarea').val('');
            form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        });

        $('.show-details').click(function()
        {
            // setTimeout(function(){
            //     console.log('first that');
            //     $(this).closest('tr').addClass('selected');
            // },1000);

            var leadID = $(this).data('lead_id');
            var getDetailsURL = $('#baseUrl').val()+'/admin/leads/'+leadID+'/getLeadDetails';
            var leadName = $(this).data('name');

            /* Modal Name */
            $('.modal-title').html('Leads Detail: <b>' + leadID + ' - ' + leadName + '</b>');

            console.log(leadID);

            $('#id').val(leadID);

            $.ajax({
                type: 'POST',
                url: getDetailsURL,
                success: function(data)
                {
                    console.log(data);

                    // if(data.leadDataCSV!==undefined || data.leadDataCSV!==null)
                    if(data.leadDataCSV)
                    {
                        $('#lead_csv').val(data.leadDataCSV.value)
                    }

                    //if(data.leadMessage!==undefined || data.leadMessage!==null)
                    if(data.leadMessage)
                    {
                        $('#message').val(data.leadMessage.value)
                    }

                    // if(data.leadDataADV!==undefined || data.leadDataADV!==null)
                    if(data.leadDataADV)
                    {
                        $('#advertiser_data').val(data.leadDataADV.value)
                    }

                    //if(data.leadSentResult!==undefined || data.leadSentResult!==null)
                    if(data.leadSentResult)
                    {
                        $('#sent_result').val(data.leadSentResult.value)
                    }
                    $('#lead_details_modal').modal('show');
                },
                error: function(data)
                {
                    console.log(data);
                }
            });

            //$('#lead_details_modal').modal('show');
        });

        $('.input-group.date').datepicker({
            format: "yyyy-mm-dd",
            clearBtn: true,
            autoclose: true,
            todayHighlight: true
        });

        $('#select-all').click(function()
        {
            //$('#leads-table').closest('tr').find('.checkbox').prop('checked', this.checked);
            $('#leads-table tr').each(function(i,row){
                var tableRow = $(row);
                tableRow.find('.checkbox').prop('checked',true);
            });
        });

        $('#de-select-all').click(function()
        {
            $('#leads-table tr').each(function(i,row){
                var tableRow = $(row);
                tableRow.find('.checkbox').prop('checked',false);
            });
        });

        $('#resend-leads').click(function()
        {

            var url = $('#baseUrl').val() + '/updateLeadsToPendingStatus/';
            var leadIDs = [];

            //get all lead id
            $('#leads-table tr').each(function(i,row){
                var tableRow = $(row);
                var checkBox = tableRow.find('.checkbox');
                var leadID = checkBox.data('lead_id');

                if(checkBox.prop('checked') && leadID!==undefined)
                {
                    leadIDs.push(leadID);
                }
            });

            var strLeadIDs = '';
            //pass all ids via url
            for(var i=0;i<leadIDs.length;i++)
            {
                strLeadIDs += leadIDs[i]+',';
            }

            //remove the last comma
            strLeadIDs = strLeadIDs.substr(0,strLeadIDs.length-1);

            url += strLeadIDs;

            $.ajax({
                type: 'POST',
                url: url,
                success: function(data)
                {
                    console.log(data);
                    alert('Leads updated to pending!');
                    //submit the form to refresh the entire page
                    $('#search-leads').submit();
                }
            });
        });
    });
</script>
@stop