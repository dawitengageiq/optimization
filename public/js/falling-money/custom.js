function sendForm() {
	var form = $('.survey_form');
	$.ajax({
		url: form.attr('action'),
		dataType: 'jsonp',
		data: form.serialize(),
		// success: function(data) {
		// 	console.log('sent');
		// }
	});
}

function setNextSurvey(id, callback) {
	$.ajax({
		url:"includes/ajax.php",
		type: 'POST',
		data: {
			type : 'set_next_survey',
			id : id
		},
		success: function(next_campaign) {
			console.log('setNextSurvey');
			console.log(next_campaign);
			callback(next_campaign);
		}
	});
}

function loadSurvey(id) {
	var lrUrl = $("meta[name='lrUrl']").attr('content');
	$.ajax({
		url: lrUrl + "get_campaign_content",
		dataType: 'jsonp', 
		data: {
			'id' : id
		},
		success:function(json){
			$.ajax({
				url:"includes/ajax.php",
				type: 'POST',
				data: {
					type : 'convert_html',
					html : json.content
				},
				success: function(converted_html) {
					//$('#contentbox').html(json.content);
					$( "#progress_bar_row td.cell_noshade:first" ).removeClass('cell_noshade').addClass('cell_shade');
					var cur_num = parseInt($('#progress_bar_current_number').html()) + 1;
					$('#progress_bar_current_number').html(cur_num);
					$('#contentbox').html(converted_html);
					//console.log(converted_html);
					console.log('loadSurvey');
				}
			});
		},
		error:function(){
		 alert("Error");
		}      
	});
	// $.ajax({
	// 	url:"includes/ajax.php",
	// 	type: 'POST',
	// 	data: {
	// 		type : 'get_campaign_id'
	// 	},
	// 	success: function(id) {
			
	// 	}
	// });

}

function convertHtml(html,callback) {
	$.ajax({
		url:"includes/ajax.php",
		type: 'POST',
		data: {
			type : 'convert_html',
			html : html
		},
		success: function(converted_html) {
			callback(converted_html);
		}
	});
}

$(document).ready(function() {

	var lrUrl = $("meta[name='lrUrl']").attr('content');
	
	$("#registration_form").validate({
		rules : {
			gender : {
				required : true
			}
		},submitHandler: function(form) {

			$('.submit_button_form').attr('type','button').css('background-image','url(images/loading-button.gif)');

		    var birthdate = $('select[name="dobyear"]').val() + '-' + $('select[name="dobmonth"]').val() + '-' + $('select[name="dobday"]').val();
		    $('input[name="birthdate"]').val(birthdate);
		    var form_data = $("#registration_form").serialize();
		    console.log($("#registration_form").serialize());

		    $.ajax({
				url: lrUrl + "get_campaign_list",
				dataType: 'jsonp',
				data: form_data,
				success:function(json){
					console.log(json);
					$.ajax({
						url:"includes/ajax.php",
						type: 'POST',
						data: {
							type : 'set_session',
							data : json
						},
						success: function(data) {
							console.log(data);
							window.location.replace("survey.php");
						}
					});
				},
				error:function(){
				 alert("Error");
				}      
			});
		}
	});

	

	$(document).on('submit','.survey_form',function(e) 
    {
		e.preventDefault();

		$('.submit_form').attr('type','button').css('background-image','url(images/yes-loading.gif)');

		sendForm();

		var id = $('.survey_form input[name="eiq_campaign_id"]').val();
		console.log(id);
		setNextSurvey(id, function(next_campaign) {
			loadSurvey(next_campaign);
		});

	});

	// $(document).on('click','.submit_form',function(e) 
 //    {
	// 	e.preventDefault();
	// 	//$('#contentbox').html('<div align="center"><img src="images/icon_loading.gif" /><h2>Loading Surveys...</h2></div>');
		
	// 	$('.survey_form').submit();
	// });

	$(document).on('click','.next_survey',function(e) 
    {
		e.preventDefault();

		$(this).css('background-image','url(images/no-loading.gif)');
		
		var id = $('.survey_form input[name="eiq_campaign_id"]').val();
		console.log(id);
		setNextSurvey(id, function(next_campaign) {
			loadSurvey(next_campaign);
		});
	});
});