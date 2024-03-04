@extends('advertiser.master')

@section('content')
<div class="edit-account-info">
    <h2>Edit Account</h2>
    <form class="form-horizontal" role="form">
      <div class="form-group">
        <label class="control-label col-sm-3" for="password">New Password</label>
        <div class="col-sm-9">
          <input type="password" class="form-control" id="password">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-3" for="confirm-password">Re-enter Password</label>
        <div class="col-sm-9">
          <input type="password" class="form-control" id="confirm-password">
        </div>
      </div>
      <div class="form-group"> 
        <div class="col-sm-offset-2 col-sm-10 offset-update-button">
          <button type="submit" class="btn btn-default btn-submit">Update</button>
        </div>
      </div>
    </form>
</div>
@stop