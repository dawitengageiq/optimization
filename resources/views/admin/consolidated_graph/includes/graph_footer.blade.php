<!-- External file -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<!-- list of bower components, can't compile for the dependencies will not be found -->
<script src="/bower_components/select2/dist/js/select2.min.js"></script>
<script src="/bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script>



<!-- Compile script -->
<script src="/js/admin/consolidated/date_range.min.js"></script>

<script>
$(function () {
    $('#legends').prop('multiple', true);

    // Since single select will add attribute:selected to only one option
    // We need to add selected attrtibutr to other options as mupltiple select
    $('#legends option', this).each(function() {
        if($(this).attr('data-selected') == 'true') $(this).prop('selected', true);
    });

    // Instantiate select 2
    $('#revenue_tracker_selection').select2({
        theme: 'bootstrap'
    });

    $('#legends').multiselect({
        inheritClass: true,
        enableFiltering: true,
        includeSelectAllOption: true,
         // buttonWidth: '600px',
        selectAllText: 'Select all to be the column of table!',
         nonSelectedText: 'Select a column!',
        selectAllValue: '',
        enableCaseInsensitiveFiltering: true,
        disableIfEmpty: true,
        maxHeight: 300
    });

    // Set the chart type.
    COMMON.setChartType('{!! $inputs["chart_type"] !!}');
    // COMMON.setChartType('{!! $inputs["chart_type"] !!}');
    @if($inputs["chart_type"] == '#all_affiliate')
        // disable other fields.
        COMMON.disableAnBlur();
    @endif
    @if($inputs["chart_type"] == '#all_inbox')
        // disable other fields.
        COMMON.showAllInboxForm();
        COMMON.setAllInboxRevenueOnDate({'{!! $inputs["date_from"] !!}' : '{!! $inputs["all_inbox_rev"] !!}'});
    @endif

    @if($has_records)
    // If have records, process chart.
    CHART.init({!! json_encode($colors) !!});
                     // Graph data/ column
    CHART.setGeneralSeries({!! json_encode($series) !!})
                     // X-axis
                     .setGeneralCategories({!! json_encode($categories) !!})
                     // Set the chart height
                     .setChartHeight('850px')
                     // /setChartHeight('588px')
                     // Set the space at the bottom of chart, add space for slider indicator
                     .setChartSpacingbottom(80)
                     // set the chart title.
                     .setChartTitle('Consolidated Graph')
                     // set the chart sub title.
                     .setChartSubTitle('Source: EngageIq')
                     // Set the legend title and the text to trigger open modal, note: open-legends is the class name that will trigger the open modal.
                     .setLegendTitleText('LEGENDS <span class="open-legends">( Descriptions )</span>')
                     // provide the list of legends were value was converted to percentage.
                     .setLegendsValue2Percent({!! json_encode($value_to_percent) !!})
                     // Set to show y axis label
                     .setYAxisLabelEnable(($('#show_yaxis').is(':checked')) ? true : false)
                     // Set the tooltip if shared to all column or not
                     .setTooltipShared(($('#shared_tooltip').is(':checked')) ? true : false)
                      // Set the tooltip to follow mouse direction, only affects when shared is true.
                     .setFollowPointer(($('#follow_pointer').is(':checked')) ? true : false)
                      // Set to show legend or not.
                     .setLegendEnabled(($('#show_legend').is(':checked')) ? true : false)
                      // Set to show export or not.
                     .setExportingEnabled(($('#show_export').is(':checked')) ? true : false)
                     // Draw the first chart on the active item of slider, 0 is the index of first series group
                     .populateActiveChart(0);

    // Legends modal
    COMMON.setLegends({!! json_encode($legends) !!})
                      .createModal();

    @endif
});
</script>
