@extends('app')

@section('title')
User Management
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<style>
    .permission-label{
        font-size: 14px;
    }

    .panel-default > .panel-heading {
        color: #fff;
        background-color: #286090;
        border-color: #ddd;
    }
</style>

@stop

@section('content')

<?php
    $attributes = [
            'url' 		=> url('admin/user/save'),
            'class'			=> 'this_form',
            'data-confirmation' => '',
            'data-process' 	=> 'add_user'
    ];
?>

<button id="addUser" class="btn btn-primary addBtn" type="button">Add User</button>

<div id="userModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalLabel">Add Role</h4>
            </div>

            {!! Form::open($attributes) !!}
                <div class="modal-body">
                    <div class="row">
                        {!! Form::hidden('id', '',array('id' => 'id')) !!}

                        <div class="col-md-2 col-lg-2">
                            <div class="form-group">
                                {!! Form::label('title','Title') !!}
                                {!! Form::select('title',config('constants.USER_TITLES'),'Mr.',['class' => 'this_field form-control','id' => 'title']) !!}
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-4">
                            <div class="form-group">
                                {!! Form::label('first_name','First name') !!}
                                {!! Form::text('first_name',null,array('class' => 'this_field form-control', 'id' => 'first_name', 'required' => 'true')) !!}
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <div class="form-group">
                                {!! Form::label('middle_name','Middle name') !!}
                                {!! Form::text('middle_name',null,array('class' => 'this_field form-control', 'id' => 'middle_name', 'required' => 'true')) !!}
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <div class="form-group">
                                {!! Form::label('last_name','Last name') !!}
                                {!! Form::text('last_name',null,array('class' => 'this_field form-control', 'id' => 'last_name', 'required' => 'true')) !!}
                            </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                            <div class="form-group">
                                {!! Form::label('gender','Gender') !!}
                                {!! Form::select('gender',['M'=>'Male','F'=>'Female'],'M',['class' => 'this_field form-control','id' => 'gender']) !!}
                            </div>
                        </div>

                        <div class="col-md-7 col-lg-7">
                            <div class="form-group">
                                {!! Form::label('position','Position') !!}
                                {!! Form::text('position',null,array('class' => 'this_field form-control', 'id' => 'position')) !!}
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-3">
                            <div class="form-group">
                                {!! Form::label('role_id','Role') !!}
                                {!! Form::select('role_id',$roles,null,['class' => 'this_field form-control','id' => 'role_id', 'required' => 'true']) !!}
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-12">
                            <div class="form-group">
                                {!! Form::label('address','Address') !!}
                                {!! Form::text('address',null,array('class' => 'this_field form-control', 'id' => 'address')) !!}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6">
                            <div class="form-group">
                                {!! Form::label('email','Email') !!}
                                {!! Form::text('email',null,array('class' => 'this_field form-control', 'id' => 'email', 'required' => 'true')) !!}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6">
                            <div class="form-group">
                                {!! Form::label('mobile_number','Mobile number') !!}
                                {!! Form::text('mobile_number',null,array('class' => 'this_field form-control', 'id' => 'mobile_number')) !!}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6">
                            <div class="form-group">
                                {!! Form::label('phone_number','Phone number') !!}
                                {!! Form::text('phone_number',null,array('class' => 'this_field form-control', 'id' => 'phone_number')) !!}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6">
                            <div class="form-group">
                                {!! Form::label('instant_messaging','Instant messaging') !!}
                                {!! Form::text('instant_messaging',null,array('class' => 'this_field form-control', 'id' => 'instant_messaging')) !!}
                            </div>
                        </div>

                        <div class="password-fields-container form-group col-md-6 col-lg-6">
                            {!! Form::label('password','Password') !!}
                            {!! Form::password('password',array('class' => 'this_field form-control password-fields', 'id' => 'password', 'required' => 'true')) !!}
                        </div>

                        <div class="password-fields-container form-group col-md-6 col-lg-6">
                            {!! Form::label('confirm_password','Confirm password') !!}
                            {!! Form::password('password_confirmation',array('class' => 'this_field form-control password-fields', 'id' => 'password_confirmation', 'required' => 'true')) !!}
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

            {!! Form::close() !!}

        </div>
    </div>
</div>

<br><br>
<table id="users-table" class="table table-bordered table-striped table-hover table-heading table-datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Phone</th>
            <th>Role</th>
            <th class="col-actions">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </tfoot>
</table>

@stop

@section('footer')
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/admin/users.min.js') }}"></script>
@stop