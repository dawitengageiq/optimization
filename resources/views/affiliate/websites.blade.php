@extends('affiliate.master')

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('websites-active') active @stop

@section('content')

<div class="modal fade" id="websiteModal" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <?php
        $attributes = [
            'url'                   => 'affiliate/update_website',
            'class'                 => 'this_form',
            'data-confirmation'     => '',
            'data-process'          => 'update_affiliat_website'
        ];
    ?>
    {!! Form::open($attributes) !!}
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Website</h4>
          </div>

          <div class="modal-body">
            {!! Form::hidden('this_id', '',array('id' => 'this_id')) !!}
                <div class="row">
                    <div class="col-md-12 form-div">
                        {!! Form::label('website_id','Website ID') !!}
                        {!! Form::text('website_id','',
                            array('class' => 'form-control this_field', 'required' => 'true', 'readonly' => 'true')) !!}
                    </div>
                    <div class="col-md-12 form-div">
                        {!! Form::label('name','Website Name') !!}
                        {!! Form::text('name','',
                            array('class' => 'form-control this_field', 'required' => 'true')) !!}
                    </div>
                    <div class="col-md-12 form-div">
                        {!! Form::label('description','Description') !!}
                        {!! Form::textarea('description','',array('class' => 'form-control this_field', 'rows' => '5')) !!}
                    </div>
                     <div class="col-md-12 form-div">
                        {!! Form::label('status','Status') !!}
                        {!! Form::text('status','',
                            array('class' => 'form-control this_field', 'required' => 'true', 'readonly' => 'true')) !!}
                    </div>
                </div>
          </div>
          <div class="modal-footer">
            <div id="websiteModalConfirm" class="alert alert-info" role="alert" style="display:none">Are you sure you want to update this website?</div>
            <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
            <button id="websiteModalPreSubmit" type="button" class="btn btn-primary">Save</button>
            <button id="websiteModalSubmit" type="submit" class="btn btn-primary this_modal_submit" style="display:none">Yes!</button>

          </div>
        </div>
    {!! Form::close() !!}
  </div>
</div>

<div class="content-form">
    <!-- TABLE CAMPAIGN -->
    <table id="websites-table" class="table">
      <thead>
        <tr>
          <th>Website ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Payout</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th>Website ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Payout</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </tfoot>
      <tbody>
      </tbody>
    </table>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/affiliate/websites.min.js') }}"></script>
@endsection
