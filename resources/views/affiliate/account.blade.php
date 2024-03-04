@extends('affiliate.master')

@section('accounts-active') active @stop

@section('content')
<div class="account-info">
	<div class="row">
	  <div class="col-md-4">
	    <div class="profile-pic">
	      <a href=""><i class="fa fa-user" style="font-size: 200px;"></i></a>
	    </div>
	  </div>
	  <div class="col-md-8">
	    <div class="row">
	      <div class="col-md-12">
	        <div class="edit-account">
	          <div class="icon-wrapper">
	            <i class="fa fa-pencil-square-o fa-5x" aria-hidden="true"></i>
	          </div>
	          <div class="details-account">
	            <a href="{{ url('affiliate/edit_account') }}"><h3>Edit Account</h3></a>
	            <p>Edit your account information including address, email, and payment threshold.</p>
	          </div>
	        </div>
	      </div>
	    </div>
	    <div class="row">
	      <div class="col-md-12">
	        <div class="edit-account">
	          <div class="icon-wrapper">
	            <i class="fa fa-lock fa-5x" aria-hidden="true"></i>
	          </div>
	          <div class="details-account">
	            <a href="{{ url('affiliate/change_password') }}"><h3>Change Password</h3></a>
	            <p>Update the password you use to login to the system.</p>
	          </div>
	        </div>
	      </div>
	    </div>
	  </div>
	</div>
</div>
@stop