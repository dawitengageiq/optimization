@extends('app')

@section('title')
	Categories
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="row container-fluid">
    <button id="add_category_button" class="btn btn-primary" type="button">Add Category</button>
</div>

<div id="category_form_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php
            $attributes = [
                'url' => url('add_category'),
                'class'	=> 'this_form',
                'data-confirmation' => '',
                'data-process' => 'add_category'
            ];

			$user = auth()->user();
			$canEdit = Bus::dispatch(new GetUserActionPermission($user,'use_edit_category'));
			$canDelete = Bus::dispatch(new GetUserActionPermission($user,'use_delete_category'));
        ?>
        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Category</h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'this_id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('name','Name') !!}
                                {!! Form::text('name',null,array('class' => 'this_field form-control', 'id' => 'name', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('description','Description') !!}
								{!! Form::textarea('description','',
									array('id' => 'description','class' => 'form-control this_field', 'rows' => '5')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('status','Status') !!}
                                <div>
                                    <div class="radio-inline">
                                        <label>
                                            {!! Form::radio('status', 0, false, array('data-label' => 'Inactive','class' => 'this_field')) !!}
                                            Inactive
                                        </label>
                                    </div>
                                    <div class="radio-inline">
                                        <label>
                                            {!! Form::radio('status', 1, true, array('data-label' => 'Active','class' => 'this_field')) !!}
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group this_error_wrapper">
                        <div class="alert alert-danger this_errors"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    {!! Form::button('Close', array('class' => 'btn btn-default','data-dismiss' => 'modal')) !!}
                    {!! Form::submit('Save', array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>
<br>
<div class="row">
    <div class="col-xs-12 container-fluid">
        <table id="categories-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            	@foreach($categories as $category)
            		<?php $id = $category['id']; ?>
            		<tr>
	            		<td>
	            			<span id="cat-{{$id}}-name">{{$category['name']}}</span>
	            		</td>
	            		<td>
	            			<?php 
	            				$preview = strlen($category['description']) > 75 ? substr($category['description'], 0, 75).'...' : $category['description']; 
	            			?>
	            			<span id="cat-{{$id}}-desc-preview">{{$preview}}</span>
	            		</td>
	            		<td>
	            			<span id="cat-{{$id}}-status" data-status="{{$category['status']}}">{{$statuses[$category['status']]}}</span>
	            		</td>
	            		<td>
	            			<textarea id="cat-{{$id}}-desc" class="hidden" disabled>{{$category['description']}}</textarea>

							@if($canEdit)
								<button class="editCategory btn btn-primary" title="Edit" data-id="{{$id}}">
									<span class="glyphicon glyphicon-pencil"></span>
								</button>
							@endif

							@if($canDelete)
								<button class="deleteCategory btn btn-danger" title="Delete" data-id="{{$id}}">
									<span class="glyphicon glyphicon-trash"></span>
								</button>
							@endif

	            		</td>
	            	</tr>
            	@endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@stop

@section('footer')
<!-- DataTables JavaScript -->
<script src="{{ asset('bower_components/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/admin/campaign_categories.min.js') }}"></script>
@stop
