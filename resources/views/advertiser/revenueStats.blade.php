@extends('app')

@section('title')
    Revenue Statistics -
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
        {!! Form::open(['url' => url('admin/revenueStatistics'),'class'=> '', 'id' => 'search-leads']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
                <!--
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_id','Lead ID') !!}
                    {!! Form::text('lead_id',isset($inputs['lead_id']) ? $inputs['lead_id'] : '',['class' => 'this_field form-control', 'id' => 'lead_id']) !!}
                </div>
                -->
                <div class="form-group col-md-5 col-lg-5">
                    {!! Form::label('campaign_id','Campaign') !!}
                    {!! Form::select('campaign_id',Bus::dispatch(new GetCampaignListAndIDsPair()),isset($inputs['campaign_id']) ? $inputs['campaign_id']: '',['class' => 'this_field form-control','id' => 'campaign_id']) !!}
                </div>
                <div class="form-group col-md-4 col-lg-4">
                    {!! Form::label('affiliate_id','Affiliate ID') !!}
                    {!! Form::text('affiliate_id',isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '',['class' => 'this_field form-control', 'id' => 'affiliate_id']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_status','Status') !!}
                    {!! Form::select('lead_status',config('constants.LEAD_STATUS'),isset($inputs['lead_status']) ? $inputs['lead_status'] : '',['class' => 'this_field form-control','id' => 'lead_status']) !!}
                </div>
                <div class="form-group col-md-6 col-lg-6">
                    {!! Form::label('containing_data','Containing Data') !!}
                    {!! Form::text('containing_data',isset($inputs['containing_data']) ? $inputs['containing_data'] : '',['class' => 'this_field form-control', 'id' => 'containing_data']) !!}
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
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('limit_rows','Limit Rows') !!}
                    {!! Form::select('limit_rows',config('constants.LIMIT_ROWS'),isset($inputs['limit_rows']) ? $inputs['limit_rows'] : '',['class' => 'this_field form-control','id' => 'limit_rows']) !!}
                </div>
            </div>
            <div class="row container-fluid">
                <div class="text-center">
                <!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-lg-offset-4 col-md-offset-3 col-sm-offset-4 col-xs-offset-4"> -->
                    {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                    {!! Form::submit('Generate Report', ['class' => 'btn btn-primary']) !!}
                    @if(session()->has('searched_revenue_leads'))
                        {!! Html::link(url('admin/downloadRevenueReport'),'Download Report',['class' =>'btn btn-primary', 'id' => 'downloadRevenueReport']) !!}
                    @endif
                </div>
            </div>
            <hr>
        {!! Form::close() !!}
    </div>
</div>

<div class="row">
    <div class="container-fluid">
        <table id="leads-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>Lead Date</th>
                    <th>Campaign</th>
                    <th>Affiliate</th>
                    <th>S1</th>
                    <th>S2</th>
                    <th>S3</th>
                    <th>S4</th>
                    <th>S5</th>
                    <th>Status</th>
                    <th>Lead Count</th>
                    <th>Cost</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leads as $lead)

                    <?php
                        $leadStatus = '';

                        //get the campaign payout
                        $campaignPayout = \App\CampaignPayout::getCampaignAffiliatePayout($lead->campaign->id,$lead->affiliate->id)->first();
                        $receive = 0;
                        $payout = 0;

                        if(isset($campaignPayout->received))
                        {
                            $receive = $campaignPayout->received;
                        }

                        if(isset($campaignPayout->payout))
                        {
                            $payout = $campaignPayout->payout;
                        }

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
                        <td>{{ $lead->created_at->toDateString() }}</td>
                        <td>{{ $lead->campaign->name }}</td>
                        <td>{{ $lead->affiliate->id }}</td>
                        <td>{{ $lead->s1 }}</td>
                        <td>{{ $lead->s2 }}</td>
                        <td>{{ $lead->s3 }}</td>
                        <td>{{ $lead->s4 }}</td>
                        <td>{{ $lead->s5 }}</td>
                        <td>{{ $leadStatus }}</td>
                        <td>{{ $lead->lead_count }}</td>
                        <td>{{ $lead->lead_status==1 ? $lead->cost : 0 }}</td>
                        <td>{{ $lead->lead_status==1 ? $lead->revenue : 0 }}</td>
                        <td>{{ $lead->lead_status==1 ? $lead->revenue - $lead->cost : 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Lead Date</th>
                    <th>Campaign</th>
                    <th>Affiliate</th>
                    <th>S1</th>
                    <th>S2</th>
                    <th>S3</th>
                    <th>S4</th>
                    <th>S5</th>
                    <th>Status</th>
                    <th>Lead Count</th>
                    <th>Cost</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                </tr>
            </tfoot>
        </table>
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
        $('.table-datatable').DataTable({
            responsive: true,
            "order": [[ 0, "desc" ]],
            lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
        });
        
        $('#clear').click(function()
        {
            var form = $('#search-leads');

            form.find('input:text, input:password, input:file, select, textarea').val('');
            form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
        });

        $('.show-details').click(function()
        {
            var leadID = $(this).data('lead_id');
            var getDetailsURL = $('#baseUrl').val()+'/admin/leads/'+leadID+'/getLeadDetails';

            console.log(leadID);

            $('#id').val(leadID);

            $.ajax({
                type: 'POST',
                url: getDetailsURL,
                success: function(data)
                {
                    console.log(data);

                    if(data.leadDataCSV!==undefined || data.leadDataCSV!==null)
                    {
                        $('#lead_csv').val(data.leadDataCSV.value)
                    }

                    if(data.leadMessage!==undefined || data.leadMessage!==null)
                    {
                        $('#message').val(data.leadMessage.value)
                    }

                    if(data.leadDataADV!==undefined || data.leadDataADV!==null)
                    {
                        $('#advertiser_data').val(data.leadDataADV.value)
                    }

                    if(data.leadSentResult!==undefined || data.leadSentResult!==null)
                    {
                        $('#sent_result').val(data.leadSentResult.value)
                    }
                },
                error: function(data)
                {
                    console.log(data);
                }
            });

            $('#lead_details_modal').modal('show');
        });

        $('.input-group.date').datepicker({
            format: "yyyy-mm-dd",
            clearBtn: true,
            autoclose: true,
            todayHighlight: true
        });
    });
</script>
@stop