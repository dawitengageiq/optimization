@extends('app')

@section('title')
    Click Log Tracer
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin/clicks-vs-registration.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">

        {!! Form::open(['url' => url('admin/getClicksVsRegsStats'), 'class'=> '', 'id' => 'clicks-vs-registration-form']) !!}

        @include('partials.flash')
        @include('partials.error')

        <div class="row">
            <div class="col-sm-9">
                {!! Form::label('affiliate_id', 'Affiliate / Revenue Tracker Traffic Source') !!}
                <button id="remove_affiliate_id_selections" type="button" class="btn btn-primary btn-xs pull-right">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
                {!! Form::select('affiliate_id', [], null, ['class' => 'form-control search-affiliate-select', 'id' => 'affiliate_id', 'name' => 'affiliateIDs[]', 'multiple' => 'multiple', 'style' => 'width: 100%']) !!}
            </div>
        </div>
        <br>
        <div class="row">
            <div class="form-group col-md-3">
                {!! Form::label('date_range','Predefined Date Range') !!}
                {!! Form::select('date_range',['' => '','yesterday' => 'Yesterday','week' => 'Week to date', 'month' => 'Month to date','last_month' => 'Last Month'],'',['class' => 'this_field form-control','id' => 'date_range']) !!}
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('date_from','Date From') !!}
                <div class="input-group date">
                    <input name="date_from" id="date_from" value="{{ isset($inputs['date_from']) ? $inputs['date_from'] : '' }}" type="text" class="date-field form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('date_to','Date To') !!}
                <div class="input-group date">
                    <input name="date_to" id="date_to" value="{{ isset($inputs['date_to']) ? $inputs['date_to'] : '' }}" type="text" class="date-field form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="checkbox-inline">
                  <input type="checkbox" name="hide_duplicate_email" id="hide_duplicate_email" value="1" class="this_field sibs"> Hide Duplicate Email
                </label>
            </div>
        </div>

        <div class="row container-fluid">
            <div class="text-center">
                {!! Form::button('Clear', ['class' => 'btn btn-default','id' => 'clear']) !!}
                {!! Form::submit('Generate Report', ['id' => 'generateReportBtn','class' => 'btn btn-primary']) !!}
                {!! Html::link(url('admin/clickLogTracer/download'),'Export to Excel',['class' =>'disabled btn btn-primary', 'id' => 'downloadReport']) !!}
            </div>
        </div>
        <hr>
        {!! Form::close() !!}
    </div>
</div>
<div class="row">
    <div class="col-xs-12 container-fluid">
        <table id="leads-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Click Date</th>
                    <th>Click ID</th>
                    <th>Email</th>
                    <th>Affiliate ID</th>
                    <th>RevTracker</th>
                    <th>IP Address</th>
                    <th>DB Prepoped</th>
                    <th>No. of Registration</th>
                    <th>1st Entry</th>
                    <th>Last Entry</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <th>No.</th>
                    <th>Click Date</th>
                    <th>Click ID</th>
                    <th>Email</th>
                    <th>Affiliate ID</th>
                    <th>RevTracker</th>
                    <th>IP Address</th>
                    <th>DB Prepoped</th>
                    <th>No. of Registration</th>
                    <th>1st Entry</th>
                    <th>Last Entry</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
    var leadsTable = $('#leads-table').DataTable({
        'processing': true,
        'serverSide': true,
        "searching": false,
        "deferLoading": 0,
        // 'columns': [
        //     null,
        //     null,
        //     null,
        //     null,
        //     null,
        //     { 'orderable': false }
        // ],
        columnDefs: [
            { width: '2%', targets: 0 },
            { width: '10%', targets: 3 },
            { width: '5%', targets: 8 },
        ],
        "order": [[ 0, "asc" ]],
        'ajax':{
            url:$('#baseUrl').html() + '/click_log_info',
            type: 'post',
            'data': function(d)
            {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.affiliate_id = $('#affiliate_id').val();
                d.date_range = $('#date_range').val();
                d.hide_duplicate_email = $('#hide_duplicate_email').prop('checked') ? 1 : 0;
            },
            "dataSrc": function ( json ) {
                console.log(json)
                var data = [], length = Number(json.length), offset = Number(json.offset);

                var counter = offset;

                $(json.data).each(function(e, item){
                    data.push([
                        ++counter,
                        item.click_date,
                        item.click_id,
                        item.email,
                        item.affiliate_id,
                        item.revenue_tracker_id,
                        item.ip,
                        item.is_dbprepoped == 1 ? 'Yes' : 'No',
                        item.reg_count,
                        item.first_entry_rev_id == 0 ? '' : item.first_entry_rev_id + ' - ' + item.first_entry_timestamp,
                        item.last_entry_rev_id == 0 ? '' : item.last_entry_rev_id + ' - ' + item.last_entry_timestamp,
                    ]);
                });
                console.log(json.recordsFiltered)
                if(json.recordsFiltered > 0) {
                    $('#downloadReport').removeClass('disabled');
                }
                return data;
            },
        },
        //lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
    });

    var baseURL = $('#baseUrl');

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('#affiliate_id').select2({
        placeholder: 'Select the id or name of the affiliate.',
        minimumInputLength: 1,
        maximumSelectionLength: 0,
        theme: 'bootstrap',
        language: {
            inputTooShort: function() {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            },
            searching: function() {
                return "Searching...";
            }
        },
        ajax: {
            url: baseURL.html()+'/search/select/activeAffiliatesIDName',
            dataType: "json",
            type: "POST",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                };

                return queryParameters;
            },
            processResults: function (data) {
                console.log(data);
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    $('#remove_affiliate_id_selections').click(function(){
        $('#affiliate_id').val(null).trigger('change');
    });


    $(document).on('click','#generateReportBtn', function(e)
    {
        e.preventDefault();
        $('#downloadReport').addClass('disabled');
        leadsTable.ajax.reload();

    });

    $('#clear').click(function(){

        $('.date-field').val('');
        $('#affiliate_id').val('').trigger('change');
        $('#hide_duplicate_email').prop('checked', false)
    });

    $('#date_range').change(function()
    {
        if($(this).val() != '')
        {
            $('#date_from').val('');
            $('#date_to').val('');
        }
    });

    $('.date-field').change(function()
    {
        if($(this).val() != '')
        {
            $('#date_range').val('');
        }
    });

    $('#addAffiliateID').click(function(){
        // Get the selected affiliate and add it on the list.
        var affiliateID = $('#affiliate_id').val();
        console.log(affiliateID);
    });
});
</script>
@stop