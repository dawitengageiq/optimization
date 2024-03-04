@extends('app')

@section('title')
    Hi {{ $user->first_name }}
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin/user_profile.min.css') }}" rel="stylesheet">
@stop

@section('content')

<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel">
    <div class="modal-dialog modal-md" role="document">

        <?php
            $attributes = [
                    'url' 		=> url('user/changePassword'),
                    'class'			=> 'this_form',
                    'data-confirmation' => '',
                    'data-process' 	=> 'change_user_password'
            ];
        ?>

        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Change Password</h4>
                </div>

                <div class="modal-body">

                    {!! Form::hidden('id', auth()->user()->id , ['id' => 'id']) !!}

                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('old_password','Old password') !!}
                                {!! Form::password('old_password',array('class' => 'this_field form-control', 'id' => 'old_password', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('password','Password') !!}
                                {!! Form::password('password',array('class' => 'this_field form-control', 'id' => 'password', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('confirm_password','Confirm password') !!}
                                {!! Form::password('password_confirmation',array('class' => 'this_field form-control', 'id' => 'password_confirmation', 'required' => 'true')) !!}
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

<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">

        <?php
            $attributes = [
                'url' 		=> url('user/updateProfile'),
                'class'			=> 'this_form',
                'data-confirmation' => '',
                'data-process' 	=> 'update_user_profile'
            ];
        ?>

        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit Profile</h4>
                </div>

                <div class="modal-body">

                    {!! Form::hidden('id', auth()->user()->id , ['id' => 'id']) !!}

                    <div class="container-fluid">
                        <div class="row">
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
                                {!! Form::text('middle_name',null,array('class' => 'this_field form-control', 'id' => 'middle_name', 'required' => 'true')) !!}
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

<div class="container-fluid">
    <div class="row">

        <!-- User Profile section -->
        <div class="col-lg-3 col-md-3">

            <?php
                $attributes = [
                        'url' 		=> url('user/profile_image_upload'),
                        'class'			=> 'text-center',
                        'data-confirmation' => '',
                        'data-process' 	=> 'upload_profile_image',
                        'method' => 'post',
                        'enctype' => 'multipart/form-data',
                        'id' => 'profileImageForm'
                ];
            ?>

            <!-- the avatar markup -->
            <div id="profileImageErrorContainer" class="center-block" style="margin-bottom: 10px;"></div>

            {!! Form::open($attributes) !!}
                <div class="kv-avatar center-block">
                    {!! Form::hidden('current_profile_image',auth()->user()->profile_image,array('id' => 'current_profile_image')) !!}
                    <input id="profileImageInput" name="profileImageInput" type="file" accept="image/*">
                </div>
            {!! Form::close() !!}

        </div>
        <!-- USER INFORMATION -->
        <div class="col-lg-7 col-md-7">
            <form class="form-horizontal">
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Name:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_full_name">{{$user->title.' '.$user->first_name.' '.$user->middle_name.' '.$user->last_name}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Address:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_address">{{$user->address}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Gender:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_gender">{{$user->gender}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Email:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_email">{{$user->email}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Phone Number:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_phone_number">{{$user->phone_number}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Position:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_position">{{$user->position}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Mobile Number</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_mobile_number">{{$user->mobile_number}}</p>
                    </div>
                </div>
                <div class="form-group form-group-profile">
                    <label class="col-sm-3 control-label">Instant Messaging:</label>
                    <div class="col-sm-9">
                        <p class="form-control-static" id="current_im">{{$user->instant_messaging}}</p>
                    </div>
                </div>
            </form>
            <!-- <div class="row">
                <div class="col-lg-12 col-md-12">
                    <span class="field-label">Name: </span>
                    <span class="field-value" id="current_full_name">{{$user->title.' '.$user->first_name.' '.$user->middle_name.' '.$user->last_name}}</span>
                </div>
                <div class="col-lg-12 col-md-12">
                    <span class="field-label">Address: </span>
                    <span class="field-value" id="current_address">{{$user->address}}</span>
                </div>
                <div class="col-lg-6 col-md-6">
                    <span class="field-label">Gender: </span>
                    <span class="field-value" id="current_gender">{{$user->gender}}</span>
                </div>
                <div class="col-lg-6 col-md-6">
                    <span class="field-label">Position: </span>
                    <span class="field-value" id="current_position">{{$user->position}}</span>
                </div>
                <div class="col-lg-6 col-md-6">
                    <span class="field-label">Email: </span>
                    <span class="field-value" id="current_email">{{$user->email}}</span>
                </div>
                <div class="col-lg-6 col-md-6">
                    <span class="field-label">Mobile Number: </span>
                    <span class="field-value" id="current_mobile_number">{{$user->mobile_number}}</span>
                </div>
                <div class="col-lg-6 col-md-6">
                    <span class="field-label">Phone Number: </span>
                    <span class="field-value" id="current_phone_number">{{$user->phone_number}}</span>
                </div>
                <div class="col-lg-6 col-md-6">
                    <span class="field-label">IM: </span>
                    <span class="field-value" id="current_im">{{$user->instant_messaging}}</span>
                </div>
            </div> -->
        </div>
        <div class="col-lg-2 col-md-2">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button id="editProfile" class="btn btn-primary user-button">Edit Profile</button>
                </div>
                <br><br>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button id="changePassword" class="btn btn-success user-button">Change Password</button>
                </div>
            </div>
        </div>
    </div>
    <br><br>
    @if($user->affiliate || $user->advertiser)
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Company Details</h3>
                </div>
                <div class="panel-body">
                    <div class="col-lg-4 col-md-4">
                        <span class="field-label">ID: </span>
                        @if($user->affiliate)
                            <span class="field-value">{{$user->affiliate->id}}</span>
                        @elseif($user->advertiser)
                            <span class="field-value">{{$user->advertiser->id}}</span>
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <span class="field-label">Name: </span>
                        @if($user->affiliate)
                            <span class="field-value">{{$user->affiliate->company}}</span>
                        @elseif($user->advertiser)
                            <span class="field-value">{{$user->advertiser->company}}</span>
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <span class="field-label">Website: </span>
                        @if($user->affiliate)
                            <span class="field-value">{{$user->affiliate->website_url}}</span>
                        @elseif($user->advertiser)
                            <span class="field-value">{{$user->advertiser->website_url}}</span>
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <span class="field-label">Address: </span>
                        @if($user->affiliate)
                            <span class="field-value">{{$user->affiliate->address.' '.$user->affiliate->city.' '.$user->affiliate->state}}</span>
                        @elseif($user->advertiser)
                            <span class="field-value">{{$user->advertiser->address.' '.$user->advertiser->city.' '.$user->advertiser->state}}</span>
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <span class="field-label">Phone: </span>
                        @if($user->affiliate)
                            <span class="field-value">{{$user->affiliate->phone}}</span>
                        @elseif($user->advertiser)
                            <span class="field-value">{{$user->advertiser->phone}}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@stop

@section('footer')
<script src="{{ asset('bower_components/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap-fileinput/js/fileinput.min.js') }}"></script>
<script src="{{ asset('js/admin/user_profile.min.js') }}"></script>
@stop