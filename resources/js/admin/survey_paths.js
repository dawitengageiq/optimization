$(document).ready(function() {
    var paths_table = $('#paths-table').DataTable({
        'aoColumnDefs': [{
            'bSortable': false,
            'aTargets': [-1]
        }],
        "columns": [
            { "width": "5%" },
            { "width": "40%" },
            { "width": "40%" },
            { "width": "15%" }
        ]
    });

    var the_modal = $('#path_form_modal');
    var the_form = the_modal.find('form.this_form');

    $('#add_path_button').click(function() {
        the_modal.modal('show');
    });

    $(document).on('click','.editPath',function()
    {
        var url = $('#baseUrl').html() + '/edit_path';
        var id = $(this).data('id');
        var sp_name = '#sp-' + id + '-';

        the_modal.find('.modal-title').html('Edit Path');
        the_form.attr('action',url);
        the_form.data('process','edit_path');
        the_form.attr('data-process', 'edit_path');
        the_form.data('confirmation','Are you sure you want to edit this path?');
        the_form.attr('data-confirmation', 'Are you sure you want to edit this path?');
        the_form.find('#this_id').val(id);
        the_form.find('#id').val(id);
        the_form.find('#name').val( $(sp_name+'name').html() );
        the_form.find('#url').val( $(sp_name+'url').html() );

        $('#pathIdDiv').show();

        the_modal.modal('show');
    });

    /* Close Confirmation Modal */
    the_modal.on('hidden.bs.modal', function (e) {
        var url = $('#baseUrl').html() + '/add_path';

        the_form.attr('action',url);
        the_form.data('process','add_path');
        the_form.attr('data-process', 'add_path');
        the_form.data('confirmation','');
        the_form.attr('data-confirmation', '');
        the_form.find('#this_id').val('');
        the_form.find('#id').val('');
        the_form.find('#name').val('');
        the_form.find('#url').val('');
        the_modal.find('.modal-title').html('Add Path');

        the_form.find('.error').each(function(){
            $(this).removeClass('error');
            $(this).removeClass('error_field');
            $(this).removeClass('error_label');
        });
        $('.this_error_wrapper').hide();

        $('#pathIdDiv').hide();
    });

    $(document).on('click','.deletePath',function()
    {
        var this_path = $(this);
        var id = $(this).data('id');

        var confirmation = confirm('Are you sure you want to delete this path?');

        if(confirmation === true) {
            var the_url = $('#baseUrl').html() + '/delete_path';
            $.ajax({
                type : 'POST',
                url  : the_url,
                data : {
                    'id' : id
                },
                success : function(data) {
                    var table = $('#paths-table').DataTable();
                    table.row(this_path.parents('tr')).remove().draw();
                }
            });
        }
    });
});