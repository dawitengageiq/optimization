@extends('affiliate.master')

@section('accounts-active') active @stop

@section('content')
<div class="edit-account-info">

    <h2>Edit Account</h2>
    <br>

      <?php
                  $attributes = [
                  'url'                => url('affiliate/change_password_contact_info'),
                  'class'              => 'this_form form-horizontal',
                  'data-confirmation'  => '',
                  'data-process'       => 'change_password_contact_info'
                ];
            ?>
    {!! Form::open($attributes) !!}

      <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">    

      <div class="form-group">
        <label class="control-label col-sm-3" for="old_password">Existing Password</label>
        <div class="col-sm-9">
          <input type="password" class="form-control" id="old_password" name="old_password" required>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-sm-3" for="password">New Password</label>
        <div class="col-sm-9">
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-sm-3" for="password_confirmation">Re-enter Password</label>
        <div class="col-sm-9">
          <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
      </div>

      
      <div class="form-group this_error_wrapper" style="display:none;">
        <label class="control-label col-sm-3" for=""></label>
        <div class="col-sm-9">
          <div class="alert alert-danger this_errors" id="changepasswordErrorHandler" >            
          </div>
        </div>
      </div>
         

      <div class="form-group"> 
        <div class="col-sm-offset-2 col-sm-10 offset-update-button">
          <!--<button type="submit" class="btn btn-default btn-submit">Update</button> -->
          {!! Form::submit('Change', array('class' => 'btn btn-primary', 'id' => 'changePasswordBtn')) !!}
          <a href="{{ url('affiliate/account') }}" class="btn btn-default btn-submit">Cancel</a>
        </div>
      </div>
    {!! Form::close() !!}
</div>
@stop

@section('footer')

<!-- This script checks if an error is return that the user have attempted to change the password 3 times-->
<script>
var myVar = setInterval(function(){ errorTimer() }, 1000);

function errorTimer()
{
  var errMessage = $('#attempt').html();   

   if(errMessage==2)
   {    
     clearInterval(myVar);
     alert("You have made 3 attempts to change your password but your old password do not match in our record. You will be log out.");
     $(location).attr('href', '{{ url('auth/logout') }}')
   }
}
</script>


<!-- EIQ JavaScript-->
<script src="{{ asset('js/affiliate/commons.min.js') }}"></script>
@stop
