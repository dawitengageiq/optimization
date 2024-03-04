@extends('advertiser.master')

@section('content')
<div class="edit-account-info">
    <h2>Edit Account</h2>
    <form class="form-horizontal" role="form">
      <div class="form-group">
        <label class="control-label col-sm-2" for="company">Company</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="company">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="phone">Phone No.</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="phone">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="website">Website</label>
        <div class="col-sm-9"> 
          <input type="text" class="form-control" id="website" placeholder="www.example.com">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="phone">Email</label>
        <div class="col-sm-9">
          <input type="email" class="form-control" id="phone">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="address">Address</label>
        <div class="col-sm-9"> 
          <input type="text" class="form-control" id="address">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="city">City</label>
        <div class="col-sm-9"> 
          <input type="text" class="form-control" id="city">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="state">State</label>
        <div class="col-sm-9"> 
          <input type="text" class="form-control" id="state">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-sm-2" for="zip">Zip Code</label>
        <div class="col-sm-9"> 
          <input type="text" class="form-control" id="zip">
        </div>
      </div>
      <div class="form-group"> 
        <div class="col-sm-offset-2 col-sm-10">
          <button type="submit" class="btn btn-default btn-submit">Update</button>
        </div>
      </div>
    </form>
</div>
@stop