@extends('app')

@section('title')
	Banned Leads
@stop

@section('header')
<!-- DataTables CSS -->
<link href="{{ asset('bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css') }}" rel="stylesheet">

<!-- DataTables Responsive CSS -->
<link href="{{ asset('bower_components/datatables-responsive/css/dataTables.responsive.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="row container-fluid">
    <button id="add_banned_button" class="btn btn-primary" type="button">Add Banned Lead</button>
</div>

<div id="banned_form_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php
            $attributes = [
                'url' 				=> url('add_banned_lead'),
                'class'				=> 'this_form',
                'data-confirmation' => '',
                'data-process' 		=> 'add_banned_lead'
            ];
        ?>
        {!! Form::open($attributes) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Banned Lead</h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '',array('id' => 'this_id')) !!}
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('first_name','First Name') !!}
                                {!! Form::text('first_name',null,array('class' => 'this_field form-control', 'id' => 'first_name', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('last_name','Last Name') !!}
                                {!! Form::text('last_name',null,array('class' => 'this_field form-control', 'id' => 'last_name', 'required' => 'true')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('email','Email') !!}
                                {!! Form::text('email',null,array('class' => 'this_field form-control', 'id' => 'email')) !!}
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                {!! Form::label('phone','Phone') !!}
                                <input class="this_field form-control" id="phone" name="phone" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
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
        <table id="leads-table" class="table table-bordered table-striped table-hover table-heading table-datatable responsive-data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
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
<script type="text/javascript">
    $(document).ready(function() {
    $('#leads-table').DataTable({
        'processing': true,
        'serverSide': true,
        'columns': [
            null,
            null,
            null,
            null,
            null,
            { 'orderable': false }
        ],
        // columnDefs: [
        //     // { width: '3%', targets: 0 },
        //     // // { width: '10%', targets: 2 },
        //     // { width: '15%', targets: [1,3] },
        //     // { width: '20%', targets: 4 },
        //     // { width: '7%', targets: 5 },
        //     { width: '15%', targets: 11 },
        // ],
        "order": [[ 0, "asc" ]],
        'ajax':{
            url:$('#baseUrl').html() + '/banned/leads', // json datasource
            type: 'post',  // method
            error: function(){  // error handling

            },
            "dataSrc": function ( json ) {
                return json.data;
            },
        },
        //lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
    });

    $('#add_banned_button').click(function() {
        $('#banned_form_modal').modal('show');
    });

    $(document).on('click','.editBanned',function()
    {
        var the_modal = $('#banned_form_modal');
        var the_form = the_modal.find('form.this_form');
        var url = $('#baseUrl').html() + '/edit_banned_lead';
        var id = $(this).data('id'),
            details = $.parseJSON($('#bnd-'+id+'-details').val());
        // console.log(details);

        the_modal.find('.modal-title').html('Edit Banned Lead');
        the_form.attr('action',url);
        the_form.data('process','edit_banned_lead');
        the_form.attr('data-process', 'edit_banned_lead');
        the_form.data('confirmation','Are you sure you want to edit this lead?');
        the_form.attr('data-confirmation', 'Are you sure you want to edit this lead?');
        the_form.find('#this_id').val(id);
        the_form.find('#first_name').val(details.first_name);
        the_form.find('#last_name').val(details.last_name);
        the_form.find('#email').val(details.email);
        the_form.find('#phone').val(details.phone);
        the_modal.modal('show');
    });

    /* Close Confirmation Modal */
    $('#banned_form_modal').on('hidden.bs.modal', function (e) {
        var the_modal = $('#banned_form_modal');
        var the_form = the_modal.find('form.this_form');
        var url = $('#baseUrl').html() + '/add_banned_lead';

        the_form.attr('action',url);
        the_form.data('process','add_banned_lead');
        the_form.attr('data-process', 'add_banned_lead');
        the_form.data('confirmation','');
        the_form.attr('data-confirmation', '');
        the_form.find('#this_id').val('');
        the_form.find('.this_field').val('');
        the_modal.find('.modal-title').html('Add Banned Lead');

        $('.error').removeClass('error');
        $('.error_field').removeClass('error_field');
        $('.error_label').removeClass('error_label');
        $('.this_error_wrapper .this_errors').empty()
        $('.this_error_wrapper').hide()
    });

    $(document).on('click','.deleteBanned',function()
    {
        var this_lead = $(this);
        var id = $(this).data('id');

        var confirmation = confirm('Are you sure you want to delete this lead?');

        if(confirmation === true) {
            var the_url = $('#baseUrl').html() + '/delete_banned_lead';
            $.ajax({
                type : 'POST',
                url  : the_url,
                data : {
                    'id' : id
                },
                success : function(data) {
                    var table = $('#leads-table').DataTable();
                    table.row(this_lead.parents('tr')).remove().draw();
                }
            });
        }
    });
});
</script>
@stop