@extends('app')

@section('title')
    Contacts
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<!-- Select2 CSS -->
<link href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
@stop

@section('content')
@if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_contact')))
    <button id="add_contact_button" class="btn btn-primary" type="button">Add Contact</button>
@endif

<div class="modal fade" id="contacts_form_modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">

        <?php
            $attributes = [
                'url' 		=> url('admin/contact/store'),
                'class'			=> 'this_form',
                'data-confirmation' => '',
                'data-process' 	=> 'add_contact'
            ];
        ?>

        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Contact</h4>
                </div>

                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::checkbox('affiliate_checkbox',true,true,['class' => '', 'id' => 'affiliate_checkbox']) !!}
                                {!! Form::label('affiliate','Affiliate') !!}
                                {!! Form::select('affiliate_id',Bus::dispatch(new GetAffiliatesCompanyIDPair()),null,['class' => 'form-control','id' => 'affiliate_id', 'style' => 'width: 100%']) !!}
                             </div>
                             <div class="form-group col-md-6 col-lg-6">
                                {!! Form::checkbox('advertiser_checkbox',true,true,['class' => '', 'id' => 'advertiser_checkbox']) !!}
                                {!! Form::label('advertiser','Advertiser') !!}
                                {!! Form::select('advertiser_id',Bus::dispatch(new GetAdvertisersCompanyIDPair()),null,['class' => 'form-control','id' => 'advertiser_id', 'style' => 'width: 100%']) !!}
                            </div>
                            <div class="form-group col-md-2 col-lg-2">
                                {!! Form::label('title','Title') !!}
                                {!! Form::select('title',config('constants.USER_TITLES'),'Mr.',['class' => 'this_field form-control','id' => 'title']) !!}
                            </div>
                            <div class="form-group col-md-4 col-lg-4">
                                {!! Form::label('first_name','First name') !!}
                                {!! Form::text('first_name',null,array('class' => 'this_field form-control', 'id' => 'first_name', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-3 col-lg-3">
                                {!! Form::label('middle_name','Middle name') !!}
                                {!! Form::text('middle_name',null,array('class' => 'this_field form-control', 'id' => 'middle_name')) !!}
                            </div>
                            <div class="form-group col-md-3 col-lg-3">
                                {!! Form::label('last_name','Last name') !!}
                                {!! Form::text('last_name',null,array('class' => 'this_field form-control', 'id' => 'last_name', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-2 col-lg-2">
                                {!! Form::label('gender','Gender') !!}
                                {!! Form::select('gender',['M'=>'Male','F'=>'Female'],'M',['class' => 'this_field form-control','id' => 'gender']) !!}
                            </div>
                            <div class="form-group col-md-10 col-lg-10">
                                {!! Form::label('position','Position') !!}
                                {!! Form::text('position',null,array('class' => 'this_field form-control', 'id' => 'position')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('address','Address') !!}
                                {!! Form::text('address',null,array('class' => 'this_field form-control', 'id' => 'address')) !!}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::label('email','Email') !!}
                                {!! Form::text('email',null,array('class' => 'this_field form-control', 'id' => 'email', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::label('mobile_number','Mobile number') !!}
                                {!! Form::text('mobile_number',null,array('class' => 'this_field form-control', 'id' => 'mobile_number')) !!}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::label('phone_number','Phone number') !!}
                                {!! Form::text('phone_number',null,array('class' => 'this_field form-control', 'id' => 'phone_number')) !!}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {!! Form::label('instant_messaging','Instant messaging') !!}
                                {!! Form::text('instant_messaging',null,array('class' => 'this_field form-control', 'id' => 'instant_messaging')) !!}
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
        <table id="contacts-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Phone</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>

            <tbody></tbody>

            <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Phone</th>
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
<script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('js/admin/contacts.min.js') }}"></script>
@stop