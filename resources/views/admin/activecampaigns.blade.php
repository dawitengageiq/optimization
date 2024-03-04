@extends('app')

@section('title')
    Active Campaigns
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

        <!--Campaigns-->
        <div class="container-fluid col-md-12 col-lg-12 col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading dashboard-item-heading">
                    <h3 class="panel-title">Active Campaigns</h3>
                </div>
                <div class="panel-body">
                    <br>
                    <div class="container-fluid">
                        <table class="table table-striped table-hover table-heading table-datatable responsive-data-table" id="dashboard-campaigns">
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
<script>
    $(document).ready(function() {
        var the_url = $('#baseUrl').val()+'/admin/activeCampaignsServerSide';
        $('#dashboard-campaigns').DataTable({
            'processing': true,
            'serverSide': true,
            'order': [[ 0, "desc" ]],
            'searching': false,
            'ajax':{
                url: the_url,
                type: 'post',
                error: function(error){  // error handling
                    console.log(error);
                }
            },
            'columns':[
                {'data':'id'},
                {'data':'name'},
                {'data':'cap_type'},
                {'data':'cap'}
            ],
            'ordering': false,
            lengthMenu: [[50,100,250],[50,100,250]]
        });
    });
</script>
@stop