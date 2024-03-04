@extends('app')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
@stop

@section('title')
    Cake Conversions
@stop

@section('content')

<div id="conversion-details-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Conversion Details</h4>
            </div>
            <div class="modal-body">
                <div id="modal-content-container"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <table id="cakeConversionsTable" class="table table-bordered table-striped table-hover table-heading table-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Offer ID</th>
                    <th>Offer Name</th>
                    <th>Campaign ID</th>
                    <th>S5</th>
                    <th>Conversion Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Offer ID</th>
                    <th>Offer Name</th>
                    <th>Campaign ID</th>
                    <th>S5</th>
                    <th>Conversion Date</th>
                    <th>Actions</th>
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
<script src="{{ asset('js/admin/cake_conversions.min.js') }}"></script>
@stop