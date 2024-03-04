@extends('affiliate.master')

@section('accounts-active') active @stop

@section('content')

    <!-- Begin page content 
    <div class="container"> -->
      <div class="edit-account-info">
        <div class="row">
          <!-- COMPANY INFO -->
          <div class="col-md-6">
            <h2>Company Info</h2>
            <form class="form-horizontal" role="form">
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-name">ID</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="company-id" readonly="readonly">{{$affiliate->id}}</textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-name">Company</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="company-name" readonly="readonly">{{$affiliate->company}}</textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-phone">Phone Number</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="company-phone" readonly="readonly" value="{{$affiliate->phone}}">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-website">Website</label>
                <div class="col-sm-9"> 
                  <textarea class="form-control" id="company-website" readonly="readonly">{{$affiliate->website_url}}</textarea>
                </div>
              </div>              
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-address">Address</label>
                <div class="col-sm-9"> 
                  <textarea class="form-control" id="company-address" readonly="readonly">{{$affiliate->address}}</textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-city">City</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="company-city" readonly="readonly" value="{{$affiliate->city}}">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-state">State</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="company-state" readonly="readonly" value="{{$affiliate->state}}">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="company-zip">Zip Code</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="company-zip" readonly="readonly" value="{{$affiliate->zip}}">
                </div>
              </div>
            </form>
          </div>
          <!-- CONTACT INFO -->
          <div class="col-md-6">
            <!-- TITLE, FIRST NAME & LAST NAME -->
            <h2>Contact Info</h2>
            <?php
                  $attributes = [
                  'url'                => url('affiliate/edit_account_contact_info'),
                  'class'              => 'this_form form-horizontal',
                  'data-confirmation'  => '',
                  'data-process'       => 'edit_account_contact_info'
                ];
            ?>
            {!! Form::open($attributes) !!}

            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

            <div class="form-group">
                <label class="control-label col-sm-3" for="contact-title">Title</label>
                <div class="col-sm-9">
                    <select id="contact-title" name="title" class="form-control" required="true">
                    <option value="">Select...</option>
                    <option value="Mr." {!! strtolower(auth()->user()->title) == 'mr.' ? 'selected' : '' !!} >Mr.</option>
                    <option value="Mrs." {!! strtolower(auth()->user()->title) == 'mrs.' ? 'selected' : '' !!} >Mrs.</option>
                    <option value="Ms." {!! strtolower(auth()->user()->title) == 'ms.' ? 'selected' : '' !!} >Ms.</option>
                    <option value="Engr." {!! strtolower(auth()->user()->title) == 'engr.' ? 'selected' : '' !!} >Engr.</option>
                    <option value="Architect" {!! strtolower(auth()->user()->title) == 'architect' ? 'selected' : '' !!} >Architect</option>
                    <option value="Dr." {!! strtolower(auth()->user()->title) == 'dr.' ? 'selected' : '' !!}>Dr.</option>
                    <option value="Sir" {!! strtolower(auth()->user()->title) == 'sir' ? 'selected' : '' !!}>Sir</option>
                    </select>  
                  
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-first-name">First Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="contact-first-name" name="first_name" value="{{ auth()->user()->first_name }}" required="true">
                </div>
              </div>

            <div class="form-group">
                <label class="control-label col-sm-3" for="contact-middle-name">Middle Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="contact-middle-name" name="middle_name"  value="{{ auth()->user()->middle_name }}">
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-last-name">Last Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="contact-last-name" name="last_name"  value="{{ auth()->user()->last_name }}" required="true">
                </div>
              </div>
              <!-- GENDER AND POSITION -->
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-gender">Gender</label>
                <div class="col-sm-9"> 
                  <select id="contact-gender" name="gender" class="form-control" required="true">
                    <option value="">Select...</option>
                    <option value="m" <?php if (strtoupper(auth()->user()->gender)=="M") echo "selected" ?>>Male</option>
                    <option value="f" <?php if (strtoupper(auth()->user()->gender)=="F") echo "selected" ?>>Female</option>
                    </select>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-position">Position</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="contact-position" name="position" value="{{ auth()->user()->position}}" required="true">
                </div>
              </div>
              <!-- ADDRESS, EMAIL, CONTACT NUMBERS & INSTANT MESSAGING -->
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-address">Street Address</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="contact-address" name="address" value="{{ auth()->user()->address}}" required="true">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-email">Email</label>
                <div class="col-sm-9"> 
                  <input type="email" class="form-control" id="contact-email" name="email" value="{{ auth()->user()->email}}" required="true">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-email">Co-workers Email</label>
                <div class="col-sm-9"> 
                  <textarea name="coworker_email" class="form-control this_field" rows="2">{{$coworker_email }}</textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-mobile">Mobile Number</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="contact-mobile" name="mobile_number" value="{{ auth()->user()->mobile_number}}" required="true">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-phone">Phone Number</label>
                <div class="col-sm-9"> 
                  <input type="text" class="form-control" id="contact-phone" name="phone_number" value="{{ auth()->user()->phone_number}}" required="true">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-3" for="contact-instant-messaging">Instant Messaging</label>
                <div class="col-sm-9"> 
                  <textarea class="form-control" id="contact-instant-messaging" name="instant_messaging">{{ auth()->user()->instant_messaging}}</textarea>
                </div>
              </div>
              
              <div class="form-group this_error_wrapper" style="display:none;">
                 <div class="alert alert-danger this_errors"></div>
              </div>

              <div class="form-group"> 
                <div class="col-sm-offset-3 col-sm-10">                                    
                  {!! Form::submit('Update', array('class' => 'btn btn-primary', 'id' => 'updateContactBtn')) !!}
                </div>
              </div>

            {!! Form::close() !!}
          </div>
        </div>
      </div>
    <!-- </div>  /container -->

@stop

@section('footer')

<!-- EIQ JavaScript-->
<script src="{{ asset('js/affiliate/commons.min.js') }}"></script>

@stop