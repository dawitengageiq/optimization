$(document).ready(function() {

	var table = $('#gallery-table').dataTable( {
	  "ordering": false,
	  // "bLengthChange": false,
	  "bInfo" : false,
      "info":     false,
      "bFilter" : false,
      //lengthMenu: [[500,1000,2000,5000,-1],[500,1000,2000,5000,"All"]]
      lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
	} );

	$('#gallery-table tbody').on( 'click', 'td', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            table.$('td.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    } );

    var clipboard = new Clipboard('.copyUrlToClipboard');
    clipboard.on('success', function(e) {
    	var this_btn = $(e.trigger);
    	this_btn.tooltip('show');
    	this_btn.parents('td').addClass('selected');
    	// console.info('Trigger:', e.trigger);
    	//console.log(e);
    	
    	setTimeout(function(){
	        this_btn.tooltip('destroy');
	        this_btn.parents('td').removeClass('selected');
	    }, 500);

    });
    // clipboard.on('error', function(e) {
    //     console.log(e);
    // });

	$('.img_type').change(function() {
		
		$('.imgPreview').hide();
		$('.imgPreview img').attr('src','');
		$('.gallery_img').val('');
		$('#name').val('');

		var type = $(this).val();
		if(type == 2 ) {
			$('.gallery_img').attr('type','text');
		}else {
			$('.gallery_img').attr('type','file');
		}
	});

	$(".gallery_img").change(function () 
	{
		var this_input = $(this);
		var img_type = $('input[name="img_type"]:checked').val();
		var suggestedName = '';
		this_input.removeClass('error_field error');

		if($(this).val() != '') {
			$('.imgPreview').show();
        	if(img_type == 1) {
        		imgPreview($(this),$('.imgPreview img'));

        		suggestedName = $('#image').val().split('\\').pop().split('.')[0];
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
				suggestedName = $('.gallery_img').val().split('/').pop().split('.')[0];

				//$('.imgPreview img').attr('src',$(this).val());   		
        	}
        	$('#name').val(suggestedName);
		}
		else 
		{
			$('.gallery_img').removeClass('error_field error');
			$('.imgPreview').hide();
			$('.imgPreview img').attr('src','');
			$('#name').val('');
		}
    });

	$(document).on('click','.deleteGalImg',function() 
	{
		var confirmation = confirm('Are you sure you want to delete this image?');

		if(confirmation === true) {
			var the_url = $('#baseUrl').html() + '/delete_gallery_image';
			//var this_image = $(this).parents('.gal-wrap').find('.gal-img-name').html();
			var this_image = $(this).data('img');

			$.ajax({
				type : 'POST',
				url  : the_url,
				data : {
					'image' : this_image
				}, 
				success : function(gallery) {
					// console.log(gallery);
					refreshGallery(gallery) 
				}
			});
		}
	});

	$(document).on('click','.viewGalImg',function() 
	{
		var img = $(this).data('url');
		var name = $(this).parents('.gal-wrap').find('.gal-img-name').html();
		$('#put-img-here').attr('src',img);
		$('#gal-modal-title').html(name);
		$('#viewGalImgModal').modal('show');
	});

	$('#addGalImgModal').on('hide.bs.modal', function (event) 
	{
		var form = $(this).find('.form_with_file');
	  	$('input[name="img_type"][value="1"]').click();
	  	$('.imgPreview img').attr('src','');
	  	$('#image').val('');
	  	$('#name').val('');
	  	$('.this_modal_submit').html('Save');

		form.find('.error').each(function(){
			$(this).removeClass('error');
			$(this).removeClass('error_field');
			$(this).removeClass('error_label');
		});

		$('.this_error_wrapper').hide();
	});

});