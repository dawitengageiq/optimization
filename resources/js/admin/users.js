/**
 * Created by magbanua-ariel on 03/05/2016.
 */

$(document).ready(function()
{
    var usersURL = $('#baseUrl').html() + '/admin/users';

    $('#users-table').DataTable({
        'processing': true,
        'serverSide': true,
        'columns': [
            null,
            null,
            null,
            null,
            null,
            null,
            { "orderable": false }
        ],
        'ajax':{
            url: usersURL, // json datasource
            type: 'get',  //LIVE
            // type: 'post',  // KARLA ver
            error: function(){  // error handling
            }
        },
        //lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });

    $('#addUser').click(function()
    {
        var userModal = $('#userModal');
        var formActionURL = $('#baseUrl').html() + '/admin/user/save';

        userModal.find('.this_form').attr('action',formActionURL);
        userModal.find('.this_form').attr('data-process', 'add_user');
        userModal.find('.this_form').attr('data-confirmation', '');
        userModal.find('.modal-title').html('Add User');

        //reset all needed fields in the modal
        $('.this_field').val('');
        $('#title').val('Mr.');
        $('#gender').val('M');

        $('.password-fields-container').show();
        $('.password-fields').prop('required',true);

        userModal.modal('show');
        //hide the error block
        $('.this_error_wrapper').hide();
    });

    /***
     * onclick listener for deleting affiliate
     */
    $(document).on('click','.deleteUser',function()
    {
        if(!confirm('Are you sure you want to delete this user?'))
        {
            return;
        }

        var id = $(this).data('id');
        var url = $('#baseUrl').val() + '/admin/user/'+id+'/delete';

        $.ajax({
            type: 'POST',
            url: url,
            success: function(data)
            {
                if(data.delete_status)
                {
                    var table = $('#users-table').DataTable();
                    table.row($('#row-user-'+id)).remove();
                    table.draw();
                }
                else
                {
                    alert(data.message);
                }
            }
        });
    });

    /***
     * onclick listener for editing contact
     */
    $(document).on('click','.editUser',function()
    {
        var this_modal = $('#userModal');
        var id = $(this).data('id');

        var formActionURL = $('#baseUrl').html() + '/admin/user/'+id+'/update';

        this_modal.find('.this_form').attr('action',formActionURL);
        this_modal.find('.this_form').data('process','edit_user');
        this_modal.find('.this_form').attr('data-process', 'edit_user');
        this_modal.find('.this_form').data('confirmation','Are you sure you want to update this user?');
        this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to update this user?')
        this_modal.find('.modal-title').html('Edit User');

        this_modal.find('#id').val(id);

        var this_contact = '#user-'+id+'-';

        //fill all the fields
        var full_name = $(this_contact+'full_name').html().trim();
        var nameArray = full_name.split(' ');
        var title = nameArray[0];
        var first_name = nameArray[1];
        var middle_name = nameArray[2];
        var last_name = nameArray[3];
        var gender = $(this_contact+'gender').val();
        var position = $(this_contact+'position').val();
        var email = $(this_contact+'email').html();
        var address = $(this_contact+'address').val();
        var mobile_number = $(this_contact+'mobile_number').html();
        var phone_number = $(this_contact+'phone_number').html();
        var role_id = $(this_contact+'role_id').val();
        var instant_messaging = $(this_contact+'instant_messaging').val();

        $('#title').val(title);
        $('#first_name').val(first_name);
        $('#middle_name').val(middle_name);
        $('#last_name').val(last_name);
        $('#gender').val(gender);
        $('#position').val(position);
        $('#address').val(address);
        $('#email').val(email);
        $('#mobile_number').val(mobile_number);
        $('#phone_number').val(phone_number);
        $('#role_id').val(role_id);
        $('#instant_messaging').val(instant_messaging);

        $('.password-fields-container').hide();
        $('.password-fields').prop('required',false);

        //hide the error block
        $('.this_error_wrapper').hide();

        this_modal.modal('show');
    });
});

