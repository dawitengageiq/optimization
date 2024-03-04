@extends('app')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
<style>
  .detail-item-title {
      font-size: 16px;
      font-weight: bold;
  }
</style>
@stop

@section('title')
    Cron Jobs
@stop

@section('content')
<div id="more-details-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="moreDetailsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="moreDetailsModalLabel">More Details</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Cron Job ID: </span><span id="cj-id"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <span class="detail-item-title">Status: </span><span id="cj-s"></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <table class="table table-bordered table-striped table-hover table-heading table-datatable">
                                <tr>
                                    <th>Leads Queued</th>
                                    <th>Leads Processed</th>
                                    <th>Leads Waiting</th>
                                </tr>
                                <tr>
                                    <td id="cj-q"></td>
                                    <td id="cj-p"></td>
                                    <td id="cj-w"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <table class="table table-bordered table-striped table-hover table-heading table-datatable">
                                <tr>
                                    <th>Time Started</th>
                                    <th>Time Ended</th>
                                    <th>Time Interval</th>
                                </tr>
                                <tr>
                                    <td id="cj-ts"></td>
                                    <td id="cj-te"></td>
                                    <td id="cj-ti"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="container-fluid">
                            <table id="leads-id-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
                                <thead>
                                    <tr>
                                        <th colspan="10" style="text-align:center;">Lead IDs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <table id="cron-job-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Leads Queued</th>
                    <th>Leads Processed</th>
                    <th>Leads Waiting</th>
                    <th>Time Started</th>
                    <th>Time Ended</th>
                    <th>Time Interval</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Leads Queued</th>
                    <th>Leads Processed</th>
                    <th>Leads Waiting</th>
                    <th>Time Started</th>
                    <th>Time Ended</th>
                    <th>Time Interval</th>
                    <th>Status</th>
                    <th>Details</th>
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
<script src="{{ asset('js/admin/cron_jobs.min.js') }}"></script>
@stop