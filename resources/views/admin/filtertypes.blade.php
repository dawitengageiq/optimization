@extends('app')

@section('title')
    Filter Types
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ URL::asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ URL::asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ URL::asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
@stop

@section('content')
@if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_filter_type')))
    <div class="row container-fluid">
        <button id="add_filtertype_button" class="btn btn-primary" type="button">Add Filter Type</button>        
    </div>
@endif

<div class="modal fade" id="filtertype_form_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">

        <?php
            $attributes = [
                'url'       => url('admin/filter/store'),
                'class'         => 'form_with_file',
                'data-confirmation' => '',
                'data-process'  => 'add_filter_type',
                'files'                 => true
            ];
        ?>

        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Filter Type</h4>
                </div>

                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('type','Type') !!}
                                {!! Form::select('type',config('constants.FILTER_TYPES'),'profile',['class' => 'this_field form-control','id' => 'type']) !!}
                            </div>

                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('name','Filter name/question') !!}
                                {!! Form::text('name',null,array('class' => 'this_field form-control', 'id' => 'name', 'required' => 'true')) !!}
                            </div>
                            <div class="forPrflIconDiv col-md-12 form-div" style="display:none">
                                {!! Form::label('img_type','Icon Source') !!}
                                <div>
                                    <div class="radio-inline">
                                        <label>
                                            {!! Form::radio('img_type', '1', true,array('class' => 'img_type')) !!}
                                            Image Upload
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            {!! Form::radio('img_type', '2', false,array('class' => 'img_type')) !!}
                                            Image Url
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="forPrflIconDiv col-md-12" style="display:none">
                                <div class="imgPreview">
                                    <img src=""/>
                                </div>
                            </div>
                            <div class="forPrflIconDiv col-md-12 form-div" style="display:none">
                                {!! Form::label('icon','Image') !!}
                                {!! Form::file('icon', array('class' => 'form-control this_field','accept' => 'image/*')) !!}
                            </div>

                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('status','Status') !!}
                                <div>
                                    <div class="radio-inline">
                                        <label>
                                            {!! Form::radio('status', 0, false, array('data-label' => 'Inactive','class' => 'this_field')) !!}
                                            Inactive
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            {!! Form::radio('status', 1, true, array('data-label' => 'Active','class' => 'this_field')) !!}
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group this_error_wrapper">
                        <div class="alert alert-danger this_errors"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    {!! Form::button('Close', array('class' => 'btn btn-default','data-dismiss' => 'modal')) !!}
                    {!! Form::submit('Save', array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>

<div class="row">
    <br>
    <div class="col-xs-12 container-fluid">
        <table id="filtertypes-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name/Question</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>Type</th>
                    <th>Name/Question</th>
                    <th>Status</th>
                    <th>Actions</th>
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
<script src="{{ URL::asset('js/moment.js') }}"></script>
<script src="{{ URL::asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ URL::asset('js/admin/filtertypes.min.js') }}"></script>
@stop