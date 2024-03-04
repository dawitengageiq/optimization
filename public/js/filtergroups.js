$(document).ready(function()
{
    $('.table-datatable').DataTable({
        responsive: true,
        "order": [[ 0, "desc" ]],
        lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
    });

    $('#addFilterGroupModal').on('hidden.bs.modal', function (e) {
        var this_modal = $(this);
        this_modal.find('#name.this_field').val('');
        this_modal.find('input[name="status"][value="1"]').prop('checked', true);
        this_modal.find('#description.this_field').val('');

        /* Error Handling */
        this_modal.find('.this_error_wrapper').hide();
        this_modal.find('.error').each(function(){
            $(this).removeClass('error');
            $(this).removeClass('error_field');
            $(this).removeClass('error_label');
        });
    })

    $(document).on('click','.editFilterGroupBtn',function() 
    {
        var the_button = $(this);
        var the_id = the_button.data('id');
        var the_modal = $('#editFilterGroupModal');

        var filter_group = '#filtergroup-'+ the_id +'-';
        var name = $(filter_group+'name').html();
        var desc = $(filter_group+'description').html();
        var stat = $(filter_group+'status').data('status');

        $('#editFilterType-Name').html(name);

        /* INFO TAB */
        
        the_modal.modal('show');
    });
    
});