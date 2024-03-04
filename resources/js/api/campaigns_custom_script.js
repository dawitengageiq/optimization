/**!
 * NLR Campaign API Custom Script
 * Campaign Custom Script v1 (http://leadreactor.engageiq.com)
 * All action, event related to UI of campaigns/offers.
 *
 * @uses Validator - form submission and input validation
 * @uses maskedinput - Phone format
 */

(function($) {
    /**
     * Global variable
     *
     * @var bolean complete - Determine if the sumission looping is completed
     * @var array submitID - container for form entity that was submiited to NLR, later it will be remove when response is sent back
     */
    var complete = false;
    var submitID = [];

    /**
     * Required text
     *
     */
	$.validator.messages.required = "*";

    /**
     * On document ready
     *
     */
    $(function() {

    	/**
    	 * Phone number format
    	 *
    	 */
    	// $("input[name=phone]").mask("(999) 999-9999");
        $("form").each(function(index) {
            $("input[name=phone], input[name=phone1]").mask("9999999999");
            $("input[name=phone], input[name=phone1]").on("blur", function() {
                var last = $(this).val().substr( $(this).val().indexOf("-") + 1 );

                if( last.length == 5 ) {
                    var move = $(this).val().substr( $(this).val().indexOf("-") + 1, 1 );
                    var lastfour = last.substr(1,4);
                    var first = $(this).val().substr( 0, 9 );
                    $(this).val( first + move + '-' + lastfour );
                }
            });
        });

    	/**
    	 * Script to display additional input fields in Mobile
    	 *
    	 */
    	$(".yes-no-mobile .class-yes").click(function(){
    	    $('.yes-no-mobile').fadeOut(500);
    	    $('#formtable').fadeIn(500);
    	    $('html, body').animate({
    	        scrollTop: $("#formtable").offset().top
    	    }, 500);
    	});

    	$(document)
    	/**
    	 * Event after the yes button were clicked
    	 *
    	 *  @var form - will be used after
    	 */
    	.on('click','.submit_stack_campaign', function(e)
        {
    		var form = $(this).closest("form");
    	})
    	/**
    	 * Event after the yes button were clicked
    	 * Show the questionere in campaign content
    	 *
    	 *  @var form - will be used after
    	 */
    	.on('click','.show_custom_questions', function(e)
    	{
    		var form = $(this).closest('form');
    		form.find('#custom_questions').css("display", "block" );
    	})
    	/**
    	 * Event after the no button were clicked
    	 * Hide the questionere in campaign content
    	 *
    	 *  @var form - will be used after
    	 */
    	.on('click','.hide_custom_questions', function(e)
    	{
    		var form = $(this).closest('form');
    		form.find('#custom_questions').css("display", "none" );
    	})
    	/**
    	 * Event after focus out on address field
    	 * Update all address fields
    	 *
    	 *  @var address - will be used after
    	 */
    	.on('change','input[name="address"]', function()
        {
        	var address = $(this).val();
        	$('input[name="address"]').val(address);
            if($(this).hasClass('valid')) {
                $('input[name="address"]').attr('class', 'valid').attr('aria-required', 'false').next().hide().text('');
            }
    	})
    	/**
    	 * Event after focus out on phone field
    	 * Update all phone fields
    	 *
    	 *  @var phone
    	 */
    	.on('change','input[name="phone"]', function()
        {
        	var phone = $(this).val();
        	$('input[name="phone"]').val(phone);
            if($(this).hasClass('valid')) {
                $('input[name="phone"]').attr('class', 'valid').attr('aria-required', 'false').next().hide().text('');
            }
    	})
    	/**
    	 * Skip button clicked
    	 *
    	 */
       .on('click', '#skip_btn', function()
       {
           	setNextStackSet();
       })
    	/**
    	 * Event when submit button is clicked
    	 * Go through all form with yes button highlighted then submit it.
    	 *
    	 */
    	.on('click','#submit_stack_button', function(e)
        {
        	e.preventDefault();
    		// disble the submit button
    		$(this).html('Sending').attr('disabled',true).prop('disabled', true);

    		var total_answered = $('.submit_stack_campaign:checked').length;
            //if($(this).find('.new-yesno-button input#NetspendCMG-no').is(':checked')) total_answered++;
    		var validation_counter = 0;

    		// Reset value of #error_validation_counter input in every submission
    		$('#error_validation_counter').val( 0 );

            // Redirect to next page if no yes button was click
    		if(total_answered === 0) {
    	    	setNextStackSet();
                return;
    	    }
            // Process form submission to NLR
            // Go through for validation and submission
			$("form").each(function () {
                if($(this).attr('data-sent') !== 'true') {
                    if($(this).find('.submit_stack_campaign').is(':checked') && $(this).valid()) {
						$(this).submit();
			    		validation_counter++;
    			    }
                }else {
                    validation_counter++;
                }
			});

            // console.log('total_answered: ', total_answered);
            // console.log('validation_counter: ', validation_counter);

            // If the count is equal means all validation was passed
            // Need to submit data now to NLR
			if(total_answered == validation_counter) {
                //console.log('Next');
                complete = true;
                $('#skip_btn').remove();
                $('<a id="skip_btn" style="text-decoration: none; cursor: pointer; display: block; color: #4285f4;">Skip</a>').insertAfter('#submit_stack_button');
                setNextStackSet();
                return;
			}

			console.log('Not Submit');
			$(this).attr('disabled', false);
    	})
    	/**
    	 * Processor of form submit.
    	 * Prevent default action of form to process custom functions
    	 *
    	 */
    	.on('submit','.stack_survey_form', function(e)
        {
        	e.preventDefault();
    		var form = $(this);
            // Update other form with this phone number
    		setPhoneAndAddress(form);

            // Process the form action
    		if(form.attr('action') != '') {
                // Process now the submission
    			ajaxProcess(form, returnProcess, submitID);
    			console.log('SENT');
    			console.log(form.attr('action') + '?' + form.serialize());
                return;
    		}
            // Means the form has empty action so skip it.
            console.log('SKIP');
    	});
    });

    /**
     * Determine if browser is IE
     *
     */
    var checkIfBrowserIE = function () {
    	var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");
        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))
        	return true;
        else
        	return false;
    }

    // Remove the form elements to sbmitID array
    var removeInArray = function (form, submitID) {
    	if ($.inArray(form, submitID)) submitID.splice($.inArray(form, submitID), 1);
    }

    /**
     * JSONP call.
     *
     */
    var ajaxProcess = function (form, callback, submitID) {
        console.log('ajaxProcess');
    	var url = form.attr('action') + '?' + form.serialize();
    	var callbackName = 'eq_callback_' + Math.round(100000 * Math.random());

        // Create callback for jsonp response
    	window[callbackName] = function(data) {
    		delete window[callbackName];
    		document.body.removeChild(script);
    		returnProcess(form, data, 'sucess', submitID);
    	};

        // Create temporary script
    	var script = document.createElement('script');
    	script.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'callback=' + callbackName;
    	document.body.appendChild(script);

        // Callback when error
    	script.onerror = function(data) {
    		returnProcess('', data, 'error', submitID);
    	};
    }

    /**
     * Processafter send leads.
     *
     */
    var returnProcess = function (form, data, status, submitID) {
    	if(form) form.attr('data-sent','true').data('sent','true');
    	console.log(status + ': ', data);
    }

    /**
     * Redirect to next page if redirect url is available
     *
     */
    var setNextStackSet = function () {
        console.log('Next Stack');
    	var redUrl = $('#redirect_url').val();
    	if(redUrl) {
            if($('#reload_parent_frame').val() !== 'false') {
                console.log('parent_not_top');
                window.location.href = redUrl;
            } else {
                console.log('top');
                window.top.location.href = redUrl;
            }
        }
        $('#submit_stack_button').html('Submit').attr('disabled', false).prop('disabled', false);
    }

    /**
     * Process phone and address data
     *
     */
    var setPhoneAndAddress = function (form) {
    	if($('#user_phone').val() != '' && $('#user_address').val() != '') return;

    	if(form.find('input[name="phone"]').length == 1 && $('#user_phone').val() == '') {
    		var phone = form.find('input[name="phone"]').val();
    			phone = phone.replace(/\D/g,'');
    			$('#user_phone').val(phone);
    	}

    	//Checks if form has address input
    	if((form.find('input[name="address"]').length == 1 || form.find('input[name="address1"]').length == 1)&& $('#user_address').val() == '') {
    		if(form.find('input[name="address1"]').length == 1) var address = form.find('input[name="address1"]').val();
    		else var address = form.find('input[name="address"]').val();
    		$('#user_address').val(address);
    	}
    }

    /**
     * Error callback handler.
     *
     */
    $.validator.setDefaults({
    	focusInvalid: false,
        invalidHandler: function(form, validator) {
            if (!validator.numberOfInvalids())return;
    		else $('#submit_stack_button').html('Submit').attr('disabled',false).prop('disabled', false);

          	if($('input[name="eiq_campaign_id"]').length > 1) {
          		if(parseInt($('#error_validation_counter').val()) == 0) {
                    $(validator.errorList[0].element).focus();
    	  		}
    	        $('#error_validation_counter').val( parseInt($('#error_validation_counter').val()) + 1);
          	}else {
                $(validator.errorList[0].element).focus();
          	}

        }
    });

    /**
     * Add method on validation.
     *
     */
    $.validator.addMethod("letterswithspace", function(value, element) {
    	return this.optional(element) || /^[a-z\s]+$/i.test(value);
    }, "letters only");

})(jQuery);
