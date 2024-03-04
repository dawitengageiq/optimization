/**
 * Created by magbanua-ariel on 02/05/2016.
 */

$(document).ready(function()
{
    var rolesURL = $('#baseUrl').val() + '/admin/roles';

    $('#roles-table').DataTable({
        'processing': true,
        'serverSide': true,
        'columns': [
            null,
            null,
            null,
            { "orderable": false }
        ],
        'ajax':{
            url: rolesURL, // json datasource
            type: 'get',  //LIVE
            // type: 'post',  // KARLA ver
            error: function(){  // error handling
            }
        },
        //lengthMenu: [[10,50,100,-1],[10,50,100,"All"]]
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });

    $('#addRole').click(function()
    {
        var roleModal = $('#roleModal');

        //clear all fields in add role modal
        roleModal.find('.permission-input-text').val('');
        roleModal.find('.permission-input-checkbox').removeAttr('checked');
        roleModal.find('.modal-title').html('Add Role');

        //hide all sub-permissions
        $('.sub-section-permissions').css('display','none');

        //hide the error container
        $('.this_error_wrapper').hide();

        $('#updateRole').hide();
        $('#saveRole').show();

        roleModal.modal('show');
    });

    $('#updateRole').click(function()
    {
        var roleModal = $('#roleModal');

        //get all the data from the modal
        var roleID = roleModal.find('#roleID').val();
        var roleName = roleModal.find('#roleName').val();
        var roleDescription = roleModal.find('#description').val();

        var roleNameLength = roleName.length;
        var errorsHtml = '';

        if(roleNameLength==0)
        {
            //show the error container
            $('.this_error_wrapper').show();

            var thisError = $('.this_errors');
            //clear all previous errors
            thisError.empty();

            errorsHtml = '<ul>';

            errorsHtml += '<li><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> Role name is empty!</li>'; //showing only the first error.
            //$('label[for="'+key+'"]').addClass('error_label error');
            //$('#'+key).addClass('error_field error');

            errorsHtml += '</ul>';

            thisError.append(errorsHtml);
            thisError.show();

            console.log(errorsHtml);

            return;
        }

        var permissions = [];

        //get all checked items
        $('input:checkbox.permission-input-checkbox').each(function()
        {
            var code = $(this).attr('id');
            var value = this.checked;
            var actionID = $(this).data('action_id');

            var data = {
                action_id: actionID,
                code: code,
                value: value
            };

            permissions.push(data);
        });

        //create the get url
        var baseURL = $('#baseUrl').val()+'roles/'+roleID+'/update';
        var updateURL = baseURL+'?name='+encodeURIComponent(roleName)+'&description='+encodeURIComponent(roleDescription)+'&permissions='+encodeURIComponent(JSON.stringify(permissions));

        $.ajax({
            type: 'GET',
            url: updateURL,
            success: function(data){

                if(data.success)
                {
                    alert(data.message);

                    console.log(data.role);

                    //update the data displayed of the role in the table
                    $('#roles-'+data.role.id+'-name').html(data.role.name);
                    $('#roles-'+data.role.id+'-description').html(data.role.description);

                    roleModal.modal('hide');
                }
            },
            error: function(jqXHR,textStatus,errorThrown){
                alert(errorThrown);
                roleModal.modal('hide');
            }
        });

    });

    $('#saveRole').click(function()
    {
        var roleModal = $('#roleModal');

        //get all the data from the modal
        var roleName = roleModal.find('#roleName').val();
        var roleDescription = roleModal.find('#description').val();

        var roleNameLength = roleName.length;
        var errorsHtml = '';

        if(roleNameLength==0)
        {
            //show the error container
            $('.this_error_wrapper').show();

            var thisError = $('.this_errors');
            //clear all previous errors
            thisError.empty();

            errorsHtml = '<ul>';

            errorsHtml += '<li><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> Role name is empty!</li>'; //showing only the first error.
            //$('label[for="'+key+'"]').addClass('error_label error');
            //$('#'+key).addClass('error_field error');

            errorsHtml += '</ul>';

            thisError.append(errorsHtml);
            thisError.show();

            console.log(errorsHtml);

            return;
        }

        var permissions = [];

        //get all checked items
        $('input:checkbox.permission-input-checkbox').each(function()
        {

            var code = $(this).attr('id');
            //var value = $(this).val() == 'on';
            var value = this.checked;

            var data = {
                code: code,
                value: value
            };

            permissions.push(data);
        });

        //create the get url
        var baseURL = $('#baseUrl').val()+'roles/save';
        var getURL = baseURL+'?name='+encodeURIComponent(roleName)+'&description='+encodeURIComponent(roleDescription)+'&permissions='+encodeURIComponent(JSON.stringify(permissions));

        $.ajax({
            type: 'GET',
            url: getURL,
            success: function(data){

                if(data.success)
                {
                    alert(data.message);

                    var actionButtons = '<button class="editRole btn btn-default" data-id="'+data.role.id+'"><span class="glyphicon glyphicon-pencil"></span></button>';
                    actionButtons += '<button class="deleteRole btn btn-default" data-id="'+data.role.id+'"><span class="glyphicon glyphicon-trash"></span></button>';

                    //append the newly added role to the table
                    var table = $('#roles-table').DataTable();
                    table.row.add([
                        data.role.id,
                        data.role.name,
                        data.role.description,
                        actionButtons
                    ]).draw();

                    roleModal.modal('hide');
                }
            },
            error: function(jqXHR,textStatus,errorThrown){
                alert(errorThrown);
                roleModal.modal('hide');
            }
        });

    });

    //detect if user checks or unchecked the main sections and hide or show it's sub-permissions
    $(".main-permission").change(function() {

        //get the id of the checkbox
        var checkBoxID = $(this).attr('id');

        switch(checkBoxID)
        {
            case 'access_contacts':

                if(this.checked)
                {
                    $('#contactPermissions').show();
                }
                else
                {
                    $('#contactPermissions').hide();
                }

                break;

            case 'access_affiliates':

                if(this.checked)
                {
                    $('#affiliatesPermissions').show();
                }
                else
                {
                    $('#affiliatesPermissions').hide();
                }

                break;

            case 'access_campaigns':

                if(this.checked)
                {
                    $('#campaignPermissions').show();
                    $('#campaignInfoPermissions').show();
                }
                else
                {
                    $('#campaignPermissions').hide();
                    $('#campaignInfoPermissions').hide();
                }

                break;

            case 'access_advertisers':

                if(this.checked)
                {
                    $('#advertisersPermissions').show();
                }
                else
                {
                    $('#advertisersPermissions').hide();
                }

                break;

            case 'access_filter_types':

                if(this.checked)
                {
                    $('#filterTypesPermissions').show();
                }
                else
                {
                    $('#filterTypesPermissions').hide();
                }

                break;

            case 'access_revenue_trackers':

                if(this.checked)
                {
                    $('#revenueTrackersPermissions').show();
                }
                else
                {
                    $('#revenueTrackersPermissions').hide();
                }

                break;

            case 'access_gallery':

                if(this.checked)
                {
                    $('#galleryPermissions').show();
                }
                else
                {
                    $('#galleryPermissions').hide();
                }

                break;

            case 'access_categories':

                if(this.checked)
                {
                    $('#categoriesPermissions').show();
                }
                else
                {
                    $('#categoriesPermissions').hide();
                }

                break;
        }
    });

});

$(document).on('click','.editRole', function()
{
    var roleEditButton = $(this);
    var id = roleEditButton.data('id');

    //set the hidden id to the modal
    $('#roleID').val(id);

    //get the modal
    var roleModal = $('#roleModal');

    //clear all fields in add role modal
    roleModal.find('.permission-input-text').val('');
    roleModal.find('.permission-input-checkbox').removeAttr('checked');
    roleModal.find('.modal-title').html('Edit Role');

    //hide the error container
    $('.this_error_wrapper').hide();

    $('#updateRole').show();
    $('#saveRole').hide();

    //get the data role data from row
    var roleName = $('#roles-'+id+'-name').text();
    var roleDescription = $('#roles-'+id+'-description').text();

    //get all actions data from the database
    var actionsURL = $('#baseUrl').val()+'roles/'+id+'/actions';

    $.ajax({
        type: 'GET',
        url: actionsURL,
        success: function(data){

            var arrayLength = data.length;

            for(var i=0;i<arrayLength;i++)
            {
                var action = data[i];

                //determine if checked or not
                roleModal.find('#'+action.code).prop('checked', action.pivot.permitted==1);
            }

            roleModal.find('#roleName').val(roleName);
            roleModal.find('#description').val(roleDescription);

            //hide or show sub-permission if their main-permission is checked
            //get all checked items
            $('input:checkbox.main-permission').each(function()
            {
                var checkBoxID = $(this).attr('id');

                switch(checkBoxID)
                {
                    case 'access_contacts':

                        if(this.checked)
                        {
                            $('#contactPermissions').show();
                        }
                        else
                        {
                            $('#contactPermissions').hide();
                        }

                        break;

                    case 'access_affiliates':

                        if(this.checked)
                        {
                            $('#affiliatesPermissions').show();
                        }
                        else
                        {
                            $('#affiliatesPermissions').hide();
                        }

                        break;

                    case 'access_campaigns':

                        if(this.checked)
                        {
                            $('#campaignPermissions').show();
                            $('#campaignInfoPermissions').show();
                        }
                        else
                        {
                            $('#campaignPermissions').hide();
                            $('#campaignInfoPermissions').hide();
                        }

                        break;

                    case 'access_advertisers':

                        if(this.checked)
                        {
                            $('#advertisersPermissions').show();
                        }
                        else
                        {
                            $('#advertisersPermissions').hide();
                        }

                        break;

                    case 'access_filter_types':

                        if(this.checked)
                        {
                            $('#filterTypesPermissions').show();
                        }
                        else
                        {
                            $('#filterTypesPermissions').hide();
                        }

                        break;

                    case 'access_revenue_trackers':

                        if(this.checked)
                        {
                            $('#revenueTrackersPermissions').show();
                        }
                        else
                        {
                            $('#revenueTrackersPermissions').hide();
                        }

                        break;

                    case 'access_gallery':

                        if(this.checked)
                        {
                            $('#galleryPermissions').show();
                        }
                        else
                        {
                            $('#galleryPermissions').hide();
                        }

                        break;

                    case 'access_categories':

                        if(this.checked)
                        {
                            $('#categoriesPermissions').show();
                        }
                        else
                        {
                            $('#categoriesPermissions').hide();
                        }

                        break;
                }

            });

            roleModal.modal('show');
        },
        error: function(jqXHR,textStatus,errorThrown){
            alert(errorThrown);
        }
    });
});

$(document).on('click','.deleteRole', function()
{
    var roleDeleteButton = $(this);
    var id = roleDeleteButton.data('id');

    if(id==1)
    {
        alert('This role cannot be deleted!');
        return;
    }

    if(confirm('Are you sure you want to delete this role?'))
    {
        //delete the role
        var deleteURL = $('#baseUrl').val()+'roles/'+id+'/delete';

        $.ajax({
            type: 'GET',
            url: deleteURL,
            success: function(data){

                if(data.success)
                {
                    alert(data.message);

                    //append the newly added role to the table
                    var table = $('#roles-table').DataTable();
                    table.row(roleDeleteButton.parents('tr')).remove().draw();
                }
            },
            error: function(jqXHR,textStatus,errorThrown){
                alert(errorThrown);
            }
        });
    }
});
