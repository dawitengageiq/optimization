@extends('app')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
@stop

@section('title')
    Zip Codes
@stop

@section('content')
<div class="row">
    <div class="col-xs-12">
        <table id="zipmaster-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
            <thead>
                <tr>
                    <th>ZIP</th>
                    <th>City</th>
                    <th>State</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>ZIP</th>
                    <th>City</th>
                    <th>State</th>
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
<script src="{{ asset('js/admin/zip_codes.min.js') }}"></script>
@stop