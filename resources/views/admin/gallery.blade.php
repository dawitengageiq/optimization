@extends('app')

@section('title') 
Gallery
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="modal fade" id="viewGalImgModal" tabindex="-1" role="dialog" aria-labelledby="addCmpFormModal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 id="gal-modal-title" class="modal-title">Image</h4>
      </div>
      <div class="modal-body">
		<img src="" id="put-img-here">
      </div>
    </div>
  </div>
</div>

@if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_add_gallery_image')))
	<button id="addGalImgBtn" class="btn btn-primary addBtn" type="button" data-toggle="modal" data-target="#addGalImgModal">Add Image</button>
	<br><br>
@endif

<div class="modal fade" id="addGalImgModal" tabindex="-1" role="dialog" aria-labelledby="addGalImgModal">
  <div class="modal-dialog" role="document">
  	<?php
  		$attributes = [
  			'url' 					=> 'add_gallery_image',
  			'class'					=> 'form_with_file',
  			'data-confirmation' 	=> '',
  			'data-process' 			=> 'add_gallery_image'
  		];
  	?>
  	{!! Form::open($attributes) !!}
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add Image</h4>
      </div>
      <div class="modal-body">
		<div class="row">
			<div class="col-md-12 form-div">
				{!! Form::label('img_type','Image Source') !!}
				<div>
					<div class="radio-inline">
						<label>
							{!! Form::radio('img_type', '1', true,array('class' => 'img_type')) !!}
							Image Upload
						</label>
					</div>
					<div class="radio-inline">
						<label>
							{!! Form::radio('img_type', '2', false,array('class' => 'img_type')) !!}
							Image Url
						</label>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="imgPreview">
					<img src=""/>
				</div>
			</div>
			<div class="col-md-12 form-div">
				{!! Form::label('image','Image') !!}
				{!! Form::file('image', array('class' => 'form-control this_field gallery_img','accept' => 'image/*', 'required' => 'true'));!!}
			</div>
			<div class="col-md-12 form-div">
				{!! Form::label('name','Name') !!}
				{!! Form::text('name','',
					array('class' => 'form-control this_field', 'required' => 'true')) !!}

			</div>
		</div>
		<div class="form-group this_error_wrapper">
			<div class="alert alert-danger this_errors">
                
            </div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default this_modal_close" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary this_modal_submit">Add</button>
      </div>
    </div>
    {!! Form::close() !!}
  </div>
</div>

<table id="gallery-table" class="table table-bordered">
	<thead>
		<tr>
			<th colspan="4">Images</th>
		</tr>
		<tr>
			<th>Image</th>
			<th>Imag</th>
			<th>Ima</th>
			<th>Im</th>
		</tr>
	</thead>
	<tbody>
		@foreach($gallery as $row)
			<tr>
				@foreach($row as $image)
					@if($image != '')
					<td>
						<div>
							<div class="gal-wrap">
								<div class="gal-img-wrp">
									<img src="{{ url($image) }}" class="gal-img"/> 
								</div>
								<?php 
									$img = explode('/',$image);
								?>
								<div class="gal-img-name">{{ $img[2] }}</div>
								<div class="gal-img-actn btn-group" role="group" aria-label="...">
								  	<button type="button" class="btn btn-default copyUrlToClipboard" data-clipboard-text="{{ url($image) }}" data-toggle="tooltip" data-placement="bottom" title="copied"><span class="glyphicon glyphicon-duplicate"></span></button>
								  	<button type="button" class="btn btn-default viewGalImg" data-url="{{ url($image) }}"><span class="glyphicon glyphicon-eye-open"></span></button>

									@if(Bus::dispatch(new GetUserActionPermission(auth()->user(),'use_delete_gallery_image')))
										<button type="button" class="btn btn-default deleteGalImg" data-img = "{{ $image }}"><span class="glyphicon glyphicon-trash"></span></button>
									@endif

								</div>
							</div>
						</div>
					</td>
					@else
					<td class="no-content"></td>
					@endif
				@endforeach
			</tr>
		@endforeach
	</tbody>
</table>

@stop

@section('footer')
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/clipboard.min.js') }}"></script>
<script src="{{ asset('js/admin/gallery.min.js') }}"></script>
@stop