@extends('auth.template')

@section('header')
    <link href="{{ URL::asset('css/auth/login.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class="login">
        <div class="container">
            <form class="form-signin" role="form" method="POST" action="{{ url('/password/email') }}">
                <div class="form-login">

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

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

                    <!-- EMAIL -->
                    <label for="inputEmail" class="sr-only">Email address</label>
                    <input type="email" name="email" id="inputEmail" class="form-control login-control" placeholder="Email address" required autofocus="">

                    <!-- SUBMIT BUTTON -->
                    <button class="btn btn-lg btn-primary btn-block btn-login" type="submit">SUBMIT</button>

                </div>
            </form>
        </div>
    </div>

@endsection
