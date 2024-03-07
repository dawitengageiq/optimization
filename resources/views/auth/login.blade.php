@extends('auth.template')

@section('header')
    <link href="{{ URL::asset('css/auth/login.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class="login">
        <div class="row">
            <div class="col-md-12">
                <div id="login-wrap" class="col-md-4 center_me">
                    <img id="login-logo" src="{{ URL::asset('images/logos/EngageIQ.png')}}">
                    <form class="" role="form" method="POST" action="{{ url('/auth/login') }}">
                        <div class="">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger login-error">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <strong>Login Error!</strong><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @csrf

                            <!-- EMAIL -->
                            <label for="inputEmail" class="sr-only">Email address</label>
                            <input type="email" name="email" id="inputEmail" class="form-control login-control" placeholder="Email address" required autofocus="">

                            <!-- PASSWORD -->
                            <label for="inputPassword" class="sr-only">Password</label>
                            <input type="password" name="password" id="inputPassword" class="form-control login-control" placeholder="Password" required>
                                
                            <input type="checkbox" name="remember"> Remember Me

                            <!-- LOGIN BUTTON -->
                            <button class="btn btn-lg btn-primary btn-block btn-login" type="submit">Sign in</button><br>

                            <div class="forgot-details">
                                <p style="margin-bottom: 0;">Forgot Password?<a class="text-bold" href="{{ url('/password/email') }}" style="color: #ed1b24; "> Click Here</a></p>
                                <p><a class="text-bold" href="#" style="color: #5b0003;">Sign Up For New Account</a></p>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
