@extends('app')

@section('title')
    Survey Paths
@stop

@section('header')

<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

@stop

@section('content')
<div class="row container-fluid">
    <button id="add_path_button" class="btn btn-primary" type="button">Add Survey Path</button>
</div>

<div id="path_form_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php
            $attributes = [
                'url'               => url('add_path'),
                'class'             => 'this_form',
                'data-confirmation' => '',
                'data-process'      => 'add_path'
            ];
        ?>
        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Path</h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'this_id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div id="pathIdDiv" class="form-group col-md-12 col-lg-12" style="display:none">
                                {!! Form::label('id','ID') !!}
                                {!! Form::text('id',null,array('class' => 'this_field form-control', 'id' => 'id', 'readonly' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('name','Name') !!}
                                {!! Form::text('name',null,array('class' => 'this_field form-control', 'id' => 'name', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('url','URL') !!}
                                {!! Form::text('url',null,array('class' => 'this_field form-control', 'id' => 'url', 'required' => 'true')) !!}
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
<br>
<div class="row">
    <div class="col-xs-12 container-fluid">
        <table id="paths-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paths as $path)
                    <?php $id = $path['id']; ?>
                    <tr>
                        <td>
                            {{$path['id']}}
                        </td>
                        <td>
                            <span id="sp-{{$id}}-name">{{$path['name']}}</span>
                        </td>
                        <td>
                            <a id="sp-{{$id}}-url" href="{{ $path['url'] }}" target="_blank">{{ $path['url'] }}</a>
                        </td>
                        <td>
                            <button class="editPath btn btn-primary" title="Edit" data-id="{{$id}}">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button class="deletePath btn btn-danger" title="Delete" data-id="{{$id}}">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>URL</th>
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
<script src="{{ asset('js/admin/survey_paths.min.js') }}"></script>
@stop