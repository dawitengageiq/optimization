var CONSOLIDATEDCOMMON = (function($)
{
    // Default variables;
    var chart_type = '#date_range';
    var revenueTracker_id;
    var dateTo;
    var carousel;
	var modal;
	var legends;
	var allinbox = {};

    $(function () {
        // Retrieve value from select and date input,
        // save it for it will be used after the "Specific Affiliates In Date Range" tab were click
        // and repopulate the select and input field.
        revenueTracker_id = $('#revenue_tracker_id').val();
        dateTo = $('#date_to').val();

        // Instantiate carousel
        carousel = $('.carousel').carousel();

        // Load chart after slides
		carousel.bind('slid.bs.carousel', function (e) {
			// Draw the first chart on the active item of slider, "data('index')" is the index of active series group
			CONSOLIDATEDCHART.populateActiveChart($(this).find('.item.active .consolidated-charts').data('index'));
		});

        // Instantiate date picker
		$('.input-group.date, .input-group .date').datepicker(
			{
				format:"yyyy-mm-dd",
				clearBtn:!0,
				autoclose:!0,
				todayHighlight:!0,
				endDate: '0d'
			}
		)
		// Change event
        .on('change', function (e) {
            if(e.target.value in allinbox) {
				$('#all_inbox_rev').val(allinbox[e.target.value]);
			} else {
				$('#all_inbox_rev').val('');
				allinbox[e.target.value] = '';
			}
        })
		;

        // Click events
		$(document)
		// Open modal
		.on('click', '.open-legends', function () {
			$('#modalLegends').modal('show');
		})
		// Clear input and select fields.
		.on('click', '#clear', function() {
	        $('#consolidated_search').find('input:text, select').val('');
	    })
        // Redraw
        .on('click', '#redraw', function () {

			CONSOLIDATEDCOMMON.fullView($('#full_view').is(':checked'));

							 // Set to show y axis label
			CONSOLIDATEDCHART.setYAxisLabelEnable(($('#show_yaxis').is(':checked')) ? true : false)
						  	 // Set the tooltip if shared to all column or not
							 .setTooltipShared(($('#shared_tooltip').is(':checked')) ? true : false)
					   	 	 // Set the tooltip to follow mouse direction, only affects when shared is true.
							 .setFollowPointer(($('#follow_pointer').is(':checked')) ? true : false)
    						  // Set to show legend or not.
    						 .setLegendEnabled(($('#show_legend').is(':checked')) ? true : false)
    						  // Set to show export or not.
    						 .setExportingEnabled(($('#show_export').is(':checked')) ? true : false)
							 // redraw
							 .populateActiveChart(CONSOLIDATEDCOMMON.carouselIndex());
		})
		// show backdrop and loader when form submit.
		.on('submit', '#consolidated_search', function() {
	        $('.search_backdrop, .loader').show();
	    })
        // Event when tabs where click
		.on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
             // activated tab
             var target = $(e.target).attr("href")
             // Populate chart_type input
             $('#chart_type').val(target);
             // disable/enable some fields, blur/ unblur fields
             if(target == '#all_affiliate') CONSOLIDATEDCOMMON.disableAnBlur();
             else if(target == '#all_inbox') CONSOLIDATEDCOMMON.showAllInboxForm();
             else CONSOLIDATEDCOMMON.enableAndUnblur();

             if(chart_type != target) $('#chart_wrap').hide();
             else $('#chart_wrap').show();
		});
    });

	// Modal html
    var _create_modal = function ()
    {
        modal = '<div class="modal fade" id="modalLegends" tabindex="-1" role="dialog">\
	        <div class="modal-dialog modal-lg" role="document">\
	            <div class="modal-content">\
	                <div class="modal-header">\
	                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">\
                            <span aria-hidden="true">&times;</span>\
                        </button>\
	                    <h4 class="modal-title"></h4>\
	                </div>\
	                <div class="modal-body">\
						<table class="table table-bordered table-striped responsive-data-table" style="font-size: 12px;">\
							<thead>\
								<tr>\
									<th>Legends</th>\
									<th>Descriptions</th>\
								</tr>\
							</thead>\
							<tbody>';
								if(legends) {
									for (var legend in legends) {
											modal += '<td>\
											<strong>' + legends[legend]['alias'] + '</strong></td>\
												<td>' + legends[legend]['desc'] + '</td>\
											</tr>';
									}
								}
		modal += '				</tbody>\
						</table>\
					</div>\
				</div>\
			</div>\
		</div>';
    }

	// Append modal before end body tag
	var _append_modal = function ()
	{
		$(modal).appendTo('body');
	}

    var public_method =  {
        init: function(){
            return this;
        },
		// Set legends for modal
		setLegends: function ($legends)
		{
			legends = $legends;
			return this;
		},
        // Set the active chart type
        setChartType: function ($chart_type)
        {
            chart_type = $chart_type;
            return this;
        },
        // disabling the select and date to innput fields and blur it
        disableAnBlur: function ()
        {
            // Retrieve the current value from select and date input,
            // save it for it will be used after the "Specific Affiliates In Date Range" tab were click
            // and repopulate the select and input field.
             this.revenueTracker_id = $('#revenue_tracker_id').val();
             this.dateTo = $('#date_to').val();
 			//
 			$('#legends').prop('disabled', false);
			 //
			 $('#legends_wrap').prop('disabled', false).css('opacity', '1');
             // disable and empty the fields
             $('#date_to, #revenue_tracker_id, #all_inbox').prop('disabled', true).val('');
             // Blur the disabled fields
             $('#date_to_wrap, #revenue_tracker_id_wrap').css('opacity', '0.2');
             //
             $('#date_to').prop('required', false);
 			//
 			$('#date_to_wrap').show();
 			$('#all_inbox_wrap').hide();
        },
        // enabling the select and date to innput fields and unblur it
        enableAndUnblur: function ()
        {
            // supplied the current value that was saved.
            $('#revenue_tracker_id').val(this.revenueTracker_id);
            $('#date_to').val(this.dateTo);
			//
			$('#legends').prop('disabled', false);
            // Enable the fields
            $('#date_to, #revenue_tracker_id').prop('disabled', false);
			//
			$('#all_inbox').prop('disabled', true);
            // Unblur the fields
            $('#date_to_wrap, #revenue_tracker_id_wrap, #legends_wrap').css('opacity', '1');
            //
            $('#date_to').prop('required', true);
			//
			$('#date_to_wrap').show();
			$('#all_inbox_wrap').hide();
        },
		//
		showAllInboxForm: function ()
		{
            // Retrieve the current value from select and date input,
            // save it for it will be used after the "Specific Affiliates In Date Range" tab were click
            // and repopulate the select and input field.
             this.revenueTracker_id = $('#revenue_tracker_id').val();
             this.dateTo = $('#date_to').val();
 			//
 			$('#legends').prop('disabled', true);
			//
			$('#legends_wrap').css('opacity', '0.2');
            //
			$('#revenue_tracker_id, #all_inbox').prop('disabled', false);
            $('#revenue_tracker_id_wrap').css('opacity', '1');
            //
            $('#date_to').prop('required', false);
			//
			$('#date_to_wrap').hide();
			$('#all_inbox_wrap').show();
		},
        // Create the modal for legends
        createModal: function ()
        {
			_create_modal();
			_append_modal();
        },
        // Add spinner to export to excel button.
        spinner: function ($id) {
            $('#' + $id).css('padding-left', '30px').addClass('spinner');
    		setTimeout(function(){ $('#' + $id).css('padding-left', '12px').removeClass('spinner') }, 3200);
        },
        // Set the all inbox revenue in reference to date.
        // Ex. {'2017-11-23' : '456.00'}
		setAllInboxRevenueOnDate: function ($allinbox)
		{
			if($allinbox) allinbox = $allinbox;
		},
        carouselIndex: function () {
            return $('.carousel').find('.item.active .consolidated-charts').data('index');
        },
		fullView: function ($full_view) {
			if($full_view) {
				$('.navbar, #graph_search, #graph_tab').hide('slow');
				$('.page-header').parents('.row').hide('slow');
				$('#page-wrapper').css('margin-left', '0');
			} else {
				$('.navbar, #graph_search, #graph_tab').show('slow');
				$('.page-header').parents('.row').show('slow');
				$('#page-wrapper').css('margin-left', '250px');
			}
		}
    }

    return public_method;

})(jQuery);
