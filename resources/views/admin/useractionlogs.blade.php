@extends('app')

@section('title')
    User Action History
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">

<!-- Select2 CSS -->
<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">

<link href="{{ asset('css/admin/diffview.min.css') }}" rel="stylesheet">

<!-- Style alteration for Select2 -->
<style>
    ul.select2-results__options--nested li.select2-results__option {
        margin-left: 20px;
    }

    strong.select2-results__group{
        color: #000;
    }

    .modal.modal-wide .modal-dialog {
        width: 90%;
    }
    .modal-wide .modal-body {
        overflow-y: auto;
    }

    .info-label {
        font-weight: bold;
        font-size: 16px;
    }

    #tallModal .modal-body p { margin-bottom: 900px }

    .table-no-grid>tbody>tr>td,
    .table-no-grid>tbody>tr>th,
    .table-no-grid>tfoot>tr>td,
    .table-no-grid>tfoot>tr>th,
    .table-no-grid>thead>tr>td,
    .table-no-grid>thead>tr>th {
        border-top: 0;
    }

    #diffoutput {
        width: 100%;
    }

    .textarea-value-container {
        width: 100%;
    }

    th.author {
        display: none;
    }

    table.diff{
        width: 100%;
    }
</style>
@stop

@section('content')
<div id="details-modal" class="modal modal-wide fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <table id="user-action-log-details-table" class="table table-striped table-no-grid" style="table-layout: fixed;">
                            <tr>
                                <td width="30%" align="right"><span class="info-label">Log ID: </span></td>
                                <td class="md-deets" width="70%"><span id="id-value"></span></td>
                                <td width="30%" align="right"><span class="info-label">Section: </span></td>
                                <td class="md-deets" width="70%"><span id="section-value"></span></td>
                                <td width="30%" align="right"><span class="info-label">Sub Section: </span></td>
                                <td class="md-deets" width="70%"><span id="sub-section-value"></span></td>
                                <td width="30%" align="right"><span class="info-label">Reference ID: </span></td>
                                <td class="md-deets" width="70%"><span id="reference-id-value"></span></td>
                            </tr>
                            <tr>
                                <td width="30%" align="right"><span class="info-label">User: </span></td>
                                <td class="md-deets" width="70%"><span id="user-value"></span></td>
                                <td width="30%" align="right"><span class="info-label">Severity: </span></td>
                                <td class="md-deets" width="70%"><span id="severity-value"></span></td>
                                <td width="30%" align="right"><span class="info-label">Summary: </span></td>
                                <td class="md-deets" width="70%" colspan="3"><span id="summary-value"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <textarea id="old-value-textarea" class="textarea-value-container" hidden></textarea>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <textarea id="new-value-textarea" class="textarea-value-container" hidden></textarea>
                    </div>
                </div>
                <div id="output-container" class="row">
                    <div class="container-fluid">
                        <div id="diffoutput"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="panel panel-default">
    <div class="panel-body">
        <div class="container-fluid">
            @include('partials.flash')
            @include('partials.error')
        </div>

        <div class="row">
            <div class="form-group col-md-4 col-lg-4">
                {!! Form::label('user','User') !!}
                {!! Form::select('user', $users+['' => 'All'],null,['class' => 'form-control','id' => 'user_id', 'style' => 'width: 100%']) !!}
            </div>

            <div class="form-group col-md-5 col-lg-5">
                {!! Form::label('section','Section') !!}
                {!! Form::select('section', $finalSections+['' => 'All'], null, ['class' => 'form-control', 'id' => 'section', 'style' => 'width: 100%']) !!}
            </div>

            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('severity','Severity') !!}
                {!! Form::select('severity', $finalSeverities+['' => 'All'], null, ['class' => 'form-control', 'id' => 'change_severity', 'style' => 'width: 100%']) !!}
            </div>

            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('date_from','Date From') !!}
                <div class="input-group date">
                    <input name="date_from" id="date_from" value="" type="text" class="form-control date-picker"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-3 col-lg-3">
                {!! Form::label('date_to','Date To') !!}
                <div class="input-group date">
                    <input name="date_to" id="date_to" value="" type="text" class="form-control date-picker"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
        </div>

        <div class="row container-fluid">
            <div class="text-center form-group">
            {!! Form::button('Clear', ['class' => 'btn btn-default', 'id' => 'clear']) !!}
            {!! Form::button('View Logs', ['class' => 'btn btn-primary', 'id' => 'view_logs']) !!}
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="container-fluid row">
            <table id="history-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Section</th>
                        <th>Sub Section</th>
                        <th>Reference ID</th>
                        <th>Summary</th>
                        <th>Severity</th>
                        <th>Datetime</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Section</th>
                        <th>Sub Section</th>
                        <th>Reference ID</th>
                        <th>Summary</th>
                        <th>Severity</th>
                        <th>Datetime</th>
                        <th>Details</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@stop

@section('footer')
    <!-- DataTables JavaScript -->
    <script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/admin/diffview.min.js') }}"></script>
    <script src="{{ asset('js/admin/difflib.min.js') }}"></script>
    <script src="{{ asset('js/admin/user-action-history.min.js') }}"></script>
@stop