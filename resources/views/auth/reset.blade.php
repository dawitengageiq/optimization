@extends('auth.template')

@section('header')
    <link href="{{ URL::asset('css/auth/login.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class="login">
        <div class="container">
            <form class="form-signin" role="form" method="POST" action="{{ url('/password/reset') }}">
                <div class="form-login">

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

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- EMAIL -->
                    <label for="inputEmail" class="sr-only">Email address</label>
                    <input type="email" name="email" id="inputEmail" class="form-control login-control" placeholder="Email address" required="" autofocus="">

                    <!-- PASSWORD -->
                    <label for="inputPassword" class="sr-only">Password</label>
                    <input type="password" id="inputPassword" name="password" class="form-control login-control" placeholder="Password" required="">

                    <!-- CONFIRM PASSWORD -->
                    <label for="inputPassword" class="sr-only">Confirm Password</label>
                    <input type="password" id="inputConfirmPassword" name="password_confirmation" class="form-control login-control" placeholder="Confirm Password" required="">

                    <!-- RESET PASSWORD BUTTON -->
                    <button class="btn btn-lg btn-primary btn-block btn-login" type="submit">Reset Password</button><br><br>

                </div>
            </form>
        </div>
    </div>

@endsection