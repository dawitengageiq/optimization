/**
 * Created by magbanua-ariel on 11/09/2015.
 */

var inactiveAffiliates = [];
var inactiveAdvertisers = [];

$(document).ready(function()
{
    /*
    $('.table-datatable').DataTable({
        responsive: true,
        "order": [[ 0, "desc" ]],
        lengthMenu: [[1000,2000,3000,4000,5000,-1],[1000,2000,3000,4000,5000,"All"]]
    });
    */

    //select2 implementation for affiliate and advertiser select tags
    $('#affiliate_id').select2({
        theme: 'bootstrap'
    });
    $('#advertiser_id').select2({
        theme: 'bootstrap'
    });

    var contactsTable = $('#contacts-table');
    var advertiserID = contactsTable.data('advertiser');
    var affiliateID = contactsTable.data('affiliate');
    var dataContactsURL = $('#baseUrl').html() + '/contacts';
    var extraData = {};

    if(advertiserID!=undefined)
    {
        extraData = {
            'awesome_advertiser_id' : advertiserID
        }
    }
    else if(affiliateID!=undefined)
    {
        extraData = {
            'awesome_affiliate_id' : affiliateID
        }
    }

    contactsTable.DataTable({
        'processing': true,
        'serverSide': true,
        'columns': [
            null,
            null,
            null,
            null,
            null,
            null,
            { 'orderable': false }
        ],
        'ajax':{
            'url' : dataContactsURL, // json datasource
            'type' : 'post',  // method  , by default get
            'data' : extraData,
            error: function(){  // error handling

            }
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });

    /***
     * onclick listener for add contact
     */
    $('#add_contact_button').click(function()
    {
        var this_modal = $('#contacts_form_modal');
        var formActionURL = $('#baseUrl').html() + '/admin/contact/store';

        this_modal.find('.this_form').attr('action',formActionURL);
        this_modal.find('.this_form').attr('data-process', 'add_contact');
        this_modal.find('.this_form').data('confirmation','');
        this_modal.find('.this_form').attr('data-confirmation', '');
        this_modal.find('.modal-title').html('Add Contact');

        //reset all needed fields in the modal
        $('.this_field').val('');
        $('#title').val('Mr.');
        $('#gender').val('M');

        var affiliateIDSelect = $('#affiliate_id');
        var advertiserIDSelect = $('#advertiser_id');

        $('#affiliate_checkbox').prop('checked',false);
        affiliateIDSelect.prop('disabled',true);
        $('#advertiser_checkbox').prop('checked',false);
        advertiserIDSelect.prop('disabled',true);

        affiliateIDSelect.val($("#affiliate_id option:first").val()).trigger('change');
        advertiserIDSelect.val($("#advertiser_id option:first").val()).trigger('change');

        $('.password-fields-container').show();
        $('.password-fields').prop('required',true);

        //remove the inactive affiliates that was added during editing
        for(var n=0;n<inactiveAffiliates.length;n++)
        {
            var inactiveAffiliate = inactiveAffiliates[n];
            $("#affiliate_id option[value='"+inactiveAffiliate.value+"']").remove();
        }

        //remove the inactive advertisers that was added during editing
        for(var n=0;n<inactiveAdvertisers.length;n++)
        {
            var inactiveAdvertiser = inactiveAdvertisers[n];
            $("#advertiser_id option[value='"+inactiveAdvertiser.value+"']").remove();
        }

        this_modal.modal('show');

        //hide the error block
        $('.this_error_wrapper').hide();
    });

    $('#affiliate_checkbox').change( function()
    {
        var affiliateSelect = $('#affiliate_id');

        if ($(this).is(':checked'))
        {
            affiliateSelect.prop('disabled',false);
        }
        else
        {
            affiliateSelect.prop('disabled',true);
        }
    });

    $('#advertiser_checkbox').change( function()
    {
        var advertiserSelect = $('#advertiser_id');

        if ($(this).is(':checked'))
        {
            advertiserSelect.prop('disabled',false);
        }
        else
        {
            advertiserSelect.prop('disabled',true);
        }
    });

    /***
     * onclick listener for deleting affiliate
     */
    $(document).on('click','.delete-contact',function()
    {
        if(!confirm('Are you sure you want to delete this contact?'))
        {
            return;
        }

        var id = $(this).data('id');
        var url = $('#baseUrl').val() + '/admin/contact/delete/'+id;

        $.ajax({
            type: 'POST',
            url: url,
            success: function(data)
            {
                if(data.delete_status)
                {
                    var table = $('#contacts-table').DataTable();
                    table.row($('#row-contact-'+id)).remove();
                    table.draw();
                }
                else
                {
                    alert(data.message);
                }
            }
        });
    });

    function containsObject(obj,list){

        for (var i = 0; i < list.length; i++) {
            if (list[i].value === obj.value) {
                return true;
            }
        }

        return false;
    }

    /***
     * onclick listener for editing contact
     */
    $(document).on('click','.edit-contact',function()
    {
        var this_modal = $('#contacts_form_modal');
        var id = $(this).data('id');
        var formActionURL = $('#baseUrl').html() + '/admin/contact/update/'+id;

        this_modal.find('.this_form').attr('action',formActionURL);
        this_modal.find('.this_form').data('process','edit_contact');
        this_modal.find('.this_form').attr('data-process', 'edit_contact');
        this_modal.find('.this_form').data('confirmation','Are you sure you want to update this contact?');
        this_modal.find('.this_form').attr('data-confirmation', 'Are you sure you want to update this contact?');
        this_modal.find('.modal-title').html('Edit Contact');
        this_modal.find('#id').val(id);

        var this_contact = '#contact-'+id+'-';
        //var tableRow = $('#row-contact-'+id);
        //var url = tableRow.data('url');

        //fill all the fields
        var affiliateID = $(this_contact+'affiliate_id').val();
        var advertiserID = $(this_contact+'advertiser_id').val();
        var full_name = $(this_contact+'full_name').html().trim();
        var nameArray = full_name.split(' ');
        var title = nameArray[0];
        var first_name = nameArray[1];
        var middle_name = nameArray[2];
        var last_name = nameArray[3];
        var gender = $(this_contact+'gender').val();
        var position = $(this_contact+'position').html();
        var email = $(this_contact+'email').html();
        var address = $(this_contact+'address').val();
        var mobile_number = $(this_contact+'mobile_number').html();
        var phone_number = $(this_contact+'phone_number').html();
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
        $('#instant_messaging').val(instant_messaging);

        var affiliateSelect = $('#affiliate_id');
        var advertiserSelect = $('#advertiser_id');

        affiliateSelect.val($("#affiliate_id option:first").val()).trigger('change');
        advertiserSelect.val($("#advertiser_id option:first").val()).trigger('change');

        if(affiliateID!=='')
        {
            //check if affiliate is inactive. If inactive just include it in the selection list
            var url = $('#baseUrl').val() + '/affiliates/'+affiliateID+'/status';

            $.ajax({
                type: 'POST',
                url: url,
                success: function(data)
                {
                    if(!data.active)
                    {
                        var affiliateExists = $("#affiliate_id option[value='"+data.affiliate_id+"']").length > 0;

                        $('#affiliate_checkbox').prop('checked',true);

                        if(!affiliateExists)
                        {
                            //insert it in the current pool of options
                            affiliateSelect.append($('<option>',{
                                value: data.affiliate_id,
                                text: data.name
                            }));

                            affiliateSelect.val(affiliateID).trigger('change');
                            affiliateSelect.prop('disabled',false);
                            console.log(data.affiliate_id+" was added!");
                        }

                        var inactiveAffiliate = {
                            value: data.affiliate_id,
                            text: data.name
                        };

                        if(!containsObject(inactiveAffiliate,inactiveAffiliates)){
                            inactiveAffiliates.push(inactiveAffiliate);
                        }
                    }
                }
            });

            $('#affiliate_checkbox').prop('checked',true);
            affiliateSelect.val(affiliateID).trigger('change');
            affiliateSelect.prop('disabled',false);
        }
        else
        {
            $('#affiliate_checkbox').prop('checked',false);
            affiliateSelect.prop('disabled',true);
        }

        if(advertiserID!=='')
        {
            //check if advertiser is inactive. If inactive just include it in the selection list
            var url = $('#baseUrl').val() + '/advertisers/'+advertiserID+'/status';

            $.ajax({
                type: 'POST',
                url: url,
                success: function(data)
                {
                    if(!data.active)
                    {
                        var advertiserExists = $("#advertiser_id option[value='"+data.advertiser_id+"']").length > 0;

                        $('#advertiser_checkbox').prop('checked',true);

                        if(!advertiserExists)
                        {
                            //insert it in the current pool of options
                            advertiserSelect.append($('<option>',{
                                value: data.advertiser_id,
                                text: data.name
                            }));

                            advertiserSelect.val(advertiserID).trigger('change');
                            advertiserSelect.prop('disabled',false);
                            console.log(data.advertiser_id+" was added!");
                        }

                        var inactiveAdvertiser = {
                            value: data.advertiser_id,
                            text: data.name
                        };

                        if(!containsObject(inactiveAdvertiser,inactiveAdvertisers)){
                            inactiveAdvertisers.push(inactiveAdvertiser);
                        }
                    }
                }
            });

            $('#advertiser_checkbox').prop('checked',true);
            advertiserSelect.val(advertiserID).trigger('change');
            advertiserSelect.prop('disabled',false);
        }
        else
        {
            $('#advertiser_checkbox').prop('checked',false);
            advertiserSelect.prop('disabled',true);
        }

        $('.password-fields-container').hide();
        $('.password-fields').prop('required',false);

        //hide the error block
        $('.this_error_wrapper').hide();

        this_modal.modal('show');
    });
});
