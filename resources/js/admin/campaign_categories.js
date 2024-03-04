$(document).ready(function() {
    var category_table = $('#categories-table').DataTable({
        'aoColumnDefs': [{
            'bSortable': false,
            'aTargets': [-1]
        }],
    });

    $('#add_category_button').click(function() {
        $('#category_form_modal').modal('show');
    });

    $(document).on('click','.editCategory',function()
    {
        var the_modal = $('#category_form_modal');
        var the_form = the_modal.find('form.this_form');
        var url = $('#baseUrl').html() + '/edit_category';
        var id = $(this).data('id');
        var cat_name = '#cat-' + id + '-';

        the_modal.find('.modal-title').html('Edit Category');
        the_form.attr('action',url);
        the_form.data('process','edit_category');
        the_form.attr('data-process', 'edit_category');
        the_form.data('confirmation','Are you sure you want to edit this category?');
        the_form.attr('data-confirmation', 'Are you sure you want to edit this category?');
        the_form.find('#this_id').val(id);
        the_form.find('#name').val( $(cat_name+'name').html() );
        the_form.find('#description').val( $(cat_name+'desc').html() );
        the_form.find('[name="status"][value="'+ $(cat_name+'status').data('status') +'"]').prop('checked',true);
        the_modal.modal('show');
    });

    /* Close Confirmation Modal */
    $('#category_form_modal').on('hidden.bs.modal', function (e) {
        var the_modal = $('#category_form_modal');
        var the_form = the_modal.find('form.this_form');
        var url = $('#baseUrl').html() + '/add_category';

        the_form.attr('action',url);
        the_form.data('process','add_category');
        the_form.attr('data-process', 'add_category');
        the_form.data('confirmation','');
        the_form.attr('data-confirmation', '');
        the_form.find('#this_id').val('');
        the_form.find('#name').val('');
        the_form.find('#description').val('');
        the_form.find('[name="status"][value="1"]').prop('checked',true);
        the_modal.find('.modal-title').html('Add Category');
    });

    $(document).on('click','.deleteCategory',function()
    {
        var this_category = $(this);
        var id = $(this).data('id');

        var confirmation = confirm('Are you sure you want to delete this category?');

        if(confirmation === true) {
            var the_url = $('#baseUrl').html() + '/delete_category';
            $.ajax({
                type : 'POST',
                url  : the_url,
                data : {
                    'id' : id
                },
                success : function(data) {
                    var table = $('#categories-table').DataTable();
                    table.row(this_category.parents('tr')).remove().draw();
                }
            });
        }
    });
});