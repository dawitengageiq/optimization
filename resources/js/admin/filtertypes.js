/**
 * Created by magbanua-ariel on 19/09/2015.
 */

$(document).ready(function()
{
    /*
    $('.table-datatable').DataTable({
        responsive: true,
        "order": [[ 0, "desc" ]],
        lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
    });
    */

    var dataFilterTypesURL = $('#baseUrl').html() + '/filtertypes';

    $('#filtertypes-table').DataTable({
        'processing': true,
        'serverSide': true,
        "columns": [
            null,
            null,
            null,
            { "orderable": false }
        ],
        'ajax':{
            url:dataFilterTypesURL, // json datasource
            type: 'post',  // method  , by default get LIVE
            error: function(){  // error handling

            }
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });

    /***
     * onclick listener for add filter type
     */
    $('#add_filtertype_button').click(function()
    {
        var this_modal = $('#filtertype_form_modal');
        var formActionURL = $('#baseUrl').html() + '/admin/filter/store';

        this_modal.find('form').attr('action',formActionURL);
        this_modal.find('form').attr('data-process', 'add_filter_type');
        this_modal.find('form').attr('data-confirmation', '');
        this_modal.find('.modal-title').html('Add Filter Type');

        this_modal.modal('show');
        //hide the error block
        $('.this_error_wrapper').hide();
    });

    /**
     * Listener for edit filter types
     */
    $(document).on('click','.edit-filtertype',function()
    {
        var this_modal = $('#filtertype_form_modal');
        var id = $(this).data('id');
        var formActionURL = $('#baseUrl').html() + '/admin/filtertype/update/'+id;

        this_modal.find('form').attr('action',formActionURL);
        this_modal.find('form').data('process','edit_filter_type');
        this_modal.find('form').attr('data-process', 'edit_filter_type');
        this_modal.find('form').data('confirmation','Are you sure you want to update this filter type?');
        this_modal.find('form').attr('data-confirmation', 'Are you sure you want to update this filter type?')
        this_modal.find('.modal-title').html('Edit Filter Type');
        this_modal.find('#id').val(id);

        var this_filtertype = '#filtertype-'+id+'-';

        var type = $(this_filtertype+'type').html().trim();
        var name = $(this_filtertype+'name').html().trim();
        var status = $(this_filtertype+'status').data('status');
        var icon = $(this_filtertype+'image').html();

        $('#type').val(type).trigger('change');
        $('#name').val(name);
        $('input[name="status"][value="'+status+'"]').prop('checked', true);
        $('.imgPreview').show();
        $('.imgPreview img').attr('src',icon);

        this_modal.modal('show');
    });


    /***
     * onclick listener for deleting affiliate
     */
    $(document).on('click','.delete-filtertype',function()
    {
        if(!confirm('Are you sure you want to delete this Filter Type?'))
        {
            return;
        }

        var id = $(this).data('id');
        var url = $('#baseUrl').val() + '/admin/filtertype/delete/'+id;

        $.ajax({
            type: 'POST',
            url: url,
            success: function(data)
            {
                if(data.delete_status)
                {
                    var table = $('#filtertypes-table').DataTable();
                    table.row($('#row-filtertype-'+id)).remove();
                    table.draw();
                }
                else
                {
                    alert(data.message);
                }
            }
        });
    });

    $('#type').change(function()
    {
        var input_type = $(this).val();
        if(input_type == 'question') {
            $('.forPrflIconDiv').show();
        }else {
            $('.forPrflIconDiv').hide();
        }
        $('[name="img_type"][value="1"]').prop('checked',true).trigger('change')
    });

    $('.img_type').change(function() {
        //1 - upload
        //2 - img url
        var process = $('#filtertype_form_modal').find('form').attr('data-process');
        if(process == 'add_filter_type'){
            $('.imgPreview').hide();
            $('.imgPreview img').attr('src','');
        }
       
        $('#icon').val('');
        var type = $(this).val();
        if(type == 2 ) {
            $('#icon').attr('type','text');
        }else {
            $('#icon').attr('type','file');
        }
    });

    $("#icon").change(function () 
    {
        var this_input = $(this);
        var img_type = $('input[name="img_type"]:checked').val();

        this_input.removeClass('error_field error');

        if($(this).val() != '') {
            $('.imgPreview').show();
            if(img_type == 1) {
                imgPreview($(this),$('.imgPreview img'));
            }else {
                //check if url is valid
                $('.imgPreview img')
                    .on('load', function() { console.log("image loaded correctly"); })
                    .on('error', function() { 
                        if(this_input.val() != '') {
                            console.log(this_input.val());
                            this_input.addClass('error_field error').val('');
                            console.log("error loading image"); 
                        }
                    })
                    .attr("src", $(this).val());  
            }
        }
        else 
        {
            $('#icon').removeClass('error_field error');
            $('.imgPreview').hide();
            $('.imgPreview img').attr('src','');
        }
    });

    $('#filtertype_form_modal').on('hide.bs.modal', function (event) 
    {
        var form = $(this).find('.form_with_file');

        $('.imgPreview').hide();
        $('.imgPreview img').attr('src','');
        $('.forPrflIconDiv').hide();
        $('[name="img_type"][value="1"]').prop('checked',true).trigger('change')

        form.find('.this_field').each(function() 
        {
            if($(this).attr('name') == 'status') {
                $('input[name="status"][value="0"]').prop('checked', true);
            }else if($(this).attr('name') == 'type') {
                $('#type').val('profile');
            }else {
                $(this).val('');
            }
        });
        form.find('.error').each(function(){
            $(this).removeClass('error');
            $(this).removeClass('error_field');
            $(this).removeClass('error_label');
        });

        $('#icon').removeClass('error error_field');

    });
});