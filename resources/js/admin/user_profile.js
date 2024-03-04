$(document).ready(function()
{
    // var uploadImage = '<button type="submit" class="btn btn-primary" title="Save Image" >' +
    //     '<i id="uploadImageIcon" class="fa fa-save fa-fw fa-lg"></i>' +
    //     ' Save</button>';
    var uploadImage = '<br>'+ '<button type="submit" class="btn btn-primary" title="Save Image" >' + 'Save</button>';

    var defaultProfilePath = '../images/profile/profile_default.png';
    var defaultAlt = 'profile_default.png';
    var currentProfileImage = $('#current_profile_image').val();

    $("#profileImageInput").fileinput({
        overwriteInitial: true,
        maxFileSize: 1500,
        showClose: false,
        showCaption: false,
        browseLabel: 'Upload Image',
        removeLabel: 'Remove',
        browseIcon: '<i class="fa fa-folder-open fa-fw fa-lg"></i>',
        // removeIcon: '<i class="fa fa-remove fa-fw fa-lg"></i>',
        removeIcon: '',
        removeTitle: 'Remove Current Image',
        elErrorContainer: '#profileImageErrorContainer',
        msgErrorClass: 'alert alert-block alert-danger',
        defaultPreviewContent: '<img src="'+defaultProfilePath+'" alt="'+defaultAlt+'" style="width:160px">',
        // layoutTemplates: {main2: '{preview} ' +  uploadImage + ' {remove} {browse}'},
        layoutTemplates: {main2: '{preview} ' + '{browse}'  + uploadImage + '  ' + '{remove}'},
        allowedFileExtensions: ['jpg','png','gif']
    });

    //set the initial image
    var profileImage = $('div.file-default-preview').find('img');

    if(currentProfileImage!='')
    {
        $.ajax({
            url: currentProfileImage,
            error: function()
            {
                //file not exists
            },
            success: function()
            {
                profileImage.attr('src',currentProfileImage);
            }
        });
    }

    $('#editProfile').click(function()
    {
        //fill in the modal form
        var fullName = $('#current_full_name').html();
        var fullNameArray = fullName.split(' ');
        var currentTitle = fullNameArray[0];
        var currentFirstName = fullNameArray[1];
        var currentMiddleName = fullNameArray[2];
        var currentLastName = fullNameArray[3];
        var currentGender = $('#current_gender').html();
        var currentAddress = $('#current_address').html();
        var currentPosition = $('#current_position').html();
        var currentEmail = $('#current_email').html();
        var currentMobileNumber = $('#current_mobile_number').html();
        var currentPhoneNumber = $('#current_phone_number').html();
        var currentIM = $('#current_im').html();

        //set the modal fields
        $('#title').val(currentTitle);
        $('#first_name').val(currentFirstName);
        $('#middle_name').val(currentMiddleName);
        $('#last_name').val(currentLastName);
        $('#gender').val(currentGender);
        $('#address').val(currentAddress);
        $('#position').val(currentPosition);
        $('#email').val(currentEmail);
        $('#mobile_number').val(currentMobileNumber);
        $('#phone_number').val(currentPhoneNumber);
        $('#instant_messaging').val(currentIM);

        $('#editProfileModal').modal('show');
    });

    $('#changePassword').click(function()
    {
        $('.this_field').val('');
        $('.this_error_wrapper').hide();
        $('#changePasswordModal').modal('show');
    });
});

$('#profileImageInput').on('fileclear', function(event) {
    //triggered when profile image is cleared
    $('#current_profile_image').val('');
});