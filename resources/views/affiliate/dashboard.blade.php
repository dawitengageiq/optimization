@extends('affiliate.master')

@section('header')
<style>
.special_hide {
	display:none !important;
}

.navbar-nav-dashboard {
	height: 50px;
}
</style>
@stop

@section('content')
<div class="jumbotron publisher-dashboard">
    <div class="right-dashboard">
      <h1>Welcome, {{ auth()->user()->first_name }}</h1> 
      <p class="text-dashboard text-right">What would you like to do today?</p>
      <p class="text-right">
        <a href="{{ url('affiliate/statistics') }}" class="btn btn-lg btn-default btn-coreg">COREG DASHBOARD</a>
        <a href="#" class="btn btn-lg btn-default btn-link-out">LINK OUT</a>
      </p>
    </div>
</div>
@stop