var CONSOLIDATEDCHART = (function($)
{
    /** BOF GENERAL CHART DATA **/
    // Base url container
    var base_url = '';
    var all_series = [];
    var all_categories = [];
    /** EOF GENERAL CHART DATA **/

    /** BOF CHART PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_chart = false;
    // Chart default variables
    var chart = {};
    var chart_type = 'column'; // Default type of chart. Range: bar, column ....
    var chart_height = '500px'; // Default height of chart.
    var chart_spacingBottom = 30; // Default spsace at the bottom of chart.
    // Set chart parameters
    var _setChart = function() {
        if(custom_chart) return chart;
        // Chart parameters
        chart = {
            type: chart_type,
            height: chart_height,
            spacingBottom: chart_spacingBottom
        };
        return chart;
    }
    /** EOF CHART PARAMETERS **/

    /** BOF TITLE PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_title = false;
    // Title dafault variables
    var title = {};
    var chart_title = 'Daily Graphs';
    // Set title parameters
    var _setTitle = function() {
        if(custom_title) return title;
        // Title parameters
        title = {
            text: chart_title
        }
        return title;
    }
    /** EOF TITLE PARAMETERS **/

    /** BOF SUB TITLE PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_sub_title = false;
    // Title dafault variables
    var sub_title = {};
    var chart_sub_title = 'Source: Unknown';
    // Set title parameters
    var _setSubTitle = function() {
        if(custom_sub_title) return sub_title;
        // Title parameters
        sub_title = {
            text: chart_sub_title
        }
        return sub_title;
    }
    /** EOF SUB TITLE PARAMETERS **/

    /** BOF LEGENDS PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_legend = false;
    // Title dafault variables
    var legends = {};
    var legend_enabled = true; // Default for enable legend
    var legends_align = 'right';
    var legends_vertical_align = 'middle';
    var legends_layout = 'vertical';
    var legends_font_weight = 'normal';
    var legends_title_text = 'Legends';
    var legends_title_font_weight = 'bold';
    var legends_title_font_style = 'italic';
    // Set title parameters
    var _setLegends = function() {
        if(custom_legend) return legends;
        // Title parameters
        legends = {
            enabled: legend_enabled,
            align: legends_align,
            verticalAlign: legends_vertical_align,
            layout: legends_layout,
            itemStyle: {
                fontWeight: legends_font_weight
            },
            title: {
                text: legends_title_text,
                style: {
                    fontWeight: legends_title_font_weight,
                    fontStyle: legends_title_font_style
                }
            }
        }

        return legends;
    }
    /** EOF LEGENDS PARAMETERS **/

    /** BOF EXPORTING PARAMETERS **/
    var exporting = {};
    // Exporting dafault variables
    var exporting_enabled = true;

    var _setExporting = function  () {
        exporting = {
            enabled: exporting_enabled
        }
        return exporting;
    }
    /** EOF EXPORTING PARAMETERS **/

    /** BOF X-AXIS PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_x_axis = false;
    // X-axis default variables
    var xAxis = {};
    var x_axis_categories = [];
    var xAxis_crosshair = true;
    // Set x-axis parameters
    var _setXAxis = function () {
        if(custom_x_axis) return xAxis;
        // X-axis parameters
        xAxis = {
            categories: x_axis_categories,
            crosshair: xAxis_crosshair,
            offset: 4,
            lineWidth: 2
        };
        return xAxis;
    }
    /** EOF X-AXIS PARAMETERS **/

    /** BOF Y-AXIS PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_y_axis = false;
    // Y-axis default variables
    var yAxis = {};
    var y_axis_title = '';
    var y_axis_min = 0;	// Default value for y axis min
    var y_axis_label_enable = false;
    // Set y-axis parameters
    var _setYAxis = function (){
        if(custom_y_axis) return yAxis;
        // Y-axis parameters
        yAxis = {
            visible: true,
            min: y_axis_min,
            lineWidth: 2,
            offset: 4,
            tickWidth: 1,
            title: {
                text: y_axis_title
            },
            labels: {
                enabled: y_axis_label_enable
            },
        };
        return yAxis;
    }
    /** EOF Y-AXIS PARAMETERS **/

    /** BOF TOOLTIP PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_tooltip = false;
    // Tooltip default variables
    var values_to_percent = [];
    var series_has_percent = ['Margin'];
    var tooltip = {};
    var headerFormat = '<span style="font-size:10px">{point.key}</span><table>';
    var pointFormat = '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' + '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>';
    var footerFormat = '</table>';
    var tooltip_shared = false;
    var tooltip_shadow = false;
    var tooltip_split = false;
    var follow_pointer = false;
    var tooltip_backgroundColor = '#EEEEEE';
    var tooltip_borderColor = '#286090';

    // Percentage extension when
    var _point_extension = function ($series_name) {
        return ($.inArray($series_name ,series_has_percent) != -1) ? ' %' : '';
    };

    // Display actual data of converted value to percentage.
    var _actualData = function ($series_name, $yAxisData){
        // reverse the formula to get actual data.
        var actual_data = ($.inArray($series_name ,values_to_percent) != -1 && $yAxisData > 0) ? (($yAxisData - 100) / 100) : $yAxisData;
        // Add percent sign
        if($series_name.toLowerCase() == 'margin') actual_data = actual_data + ' &#37;';

        return actual_data;
    }
    // Formatter for unshared tooltip
    var _unshared_formatter = function () {
        return function() {
            return '<span style="font-size:10px">' + this.x + '</span><table>\
                    <tr><td style="color:' + this.series.color + ';padding:0">' + this.series.name + ': </td>\
                    <!-- <td style="padding:0"><b>' + this.y + _point_extension(this.series.name) + '</b></td></tr> -->\
                    <td style="padding:0"><b>' + _actualData(this.series.name, this.y) + '</b></td></tr>\
                    </table>';

        };
    };
    // Formatter for shared tooltip
    var _shared_formatter = function () {
        return function() {
            var tooltipMarkup = '';
            points = this.points;
            if(this.points) {
                tooltipMarkup += '<span style="font-size: 12px">';
                tooltipMarkup += points[0].key + '</span><br/>';

                var pointsLength = points.length;
                var index;
                tooltipMarkup += '<table>';
                for(index = 0; index < pointsLength; index += 1) {
                    tooltipMarkup += '<tr>\
                                <td><span style="color: ' + points[index].series.color + '">\u25CF</span>&nbsp;</td>\
                                <td>' + points[index].series.name + '</td>\
                                <td><span>&nbsp;:&nbsp;</td>\
                                <td style="text-align: right;"><strong>' +  _actualData(points[index].series.name, points[index].y) + '</strong></td>\
                            </tr>';
                }
                tooltipMarkup += '</table>';
            }
            return tooltipMarkup;
        };
    };

    // Determine the type of formatter
    var _tooltip_formatter = function () {
        if(tooltip_shared) return _shared_formatter();
        return _unshared_formatter();
    };

    // Determine to follow pointer
    var _follow_pointer = function () {
        if(tooltip_shared) return follow_pointer;
        return false;
    };

    // Set tooltip parameters
    var _setTooltip = function() {
        if(custom_tooltip) return tooltip;
        // Tooltip parameters
        tooltip = {
            useHTML: true,
            shadow: tooltip_shadow,
            shared: tooltip_shared,
            split: tooltip_split,
            backgroundColor: tooltip_backgroundColor,
            borderColor: tooltip_borderColor,
            followPointer: _follow_pointer()
        };
        // Use formater when tool tip is not split
        if(!tooltip_split) tooltip.formatter = _tooltip_formatter();

        return tooltip;
    }
    /** EOF TOOLTIP PARAMETERS **/

    /** BOF PLOT OPTIONS PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_plot_options = false;
    var custom_plot_column = false;
    var custom_plot_series = false;
    // Plot column default variables
    var plot_column = {};
    var plot_column_pointPadding = 0.2;
    var plot_column_borderWidth = 0;
    var plot_column_pointRange = 1.4;
    var plot_column_pointWidth = 5;
    var plot_column_cursor ='pointer';
    // Set plot column parameters
    var _setPlotColumn = function () {
        if(custom_plot_column) return plot_column;
        // Plot column parameters
        plot_column = {
            pointPadding: plot_column_pointPadding,
            borderWidth: plot_column_borderWidth,
            pointRange: plot_column_pointRange,
            pointWidth: plot_column_pointWidth,
            cursor: plot_column_cursor
        };
    }
    // plot series default variables
    var plot_series = {};
    var plot_series_cursor = 'pointer';
    var plot_series_pointPadding = 0;
    // Set plot series parameters
    var _setPlotSerries = function () {
        if(custom_plot_series) return plot_series;
        // plot series parameters
        plot_series = {
            cursor: plot_series_cursor,
            pointPadding: plot_series_pointPadding
        };
    }
    // Plot options default variables
    var plotOptions = {};
    var _setPlotOptions = function () {
        if(custom_plot_options) return plotOptions;
        // Set the plot column first
        _setPlotColumn();
        // Set the plot serires first
        _setPlotSerries();
        // Plot options parameters
        plotOptions = {
            column: plot_column,
            series: plot_series
        };
        return plotOptions;
    }
    /** EOF PLOT OPTIONS PARAMETERS **/


    /** BOF SERIES PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_series = false;
    var custom_series_click = false;
    // Series default variables

    // Set series parameters
    var _setSeries = function (){
        if(custom_series) return _series;
        return _series;
    }

    /** BOF OPTIONS PARAMETERS **/
    // Flag for not using the custom variables, if set to false the default value of variables below will be used..
    var custom_options = false;
    // options default variable
    var options = {};
    // Set option parameters
    var _setOptions = function () {
        //if(custom_options) return options;
        // Option parameters
        options = {
            chart: _setChart(),
            title: _setTitle(),
            subtitle: _setSubTitle(),
            legend: _setLegends(),
            exporting: _setExporting(),
            xAxis: _setXAxis(),
            yAxis: _setYAxis(),
            tooltip: _setTooltip(),
            plotOptions: _setPlotOptions(),
            series: _setSeries()
        };
        _setCustomVariables2Default();
        return options;
    }
    /** EOF OPTIONS PARAMETERS **/

    // Set all flags to false after creating chart.
    var _setCustomVariables2Default = function () {
        custom_label = false;
        custom_chart = false;
        custom_title = false;
        custom_sub_title = false;
        custom_x_axis = false;
        custom_y_axis = false;
        custom_legend = false;
        custom_tooltip = false;
        custom_plot_options = false;
        custom_plot_column = false;
        custom_plot_series = false;
        custom_series = false;
        _series = {};
        custom_series_click = false;
        custom_options = false;
        option = {};

    }
    // Initial options
    var colors = [
          '#315493',
          '#b35d22',
          '#7b7b7b',
          '#c09000',
          '#4374a0',
          '#538233'
      ];

    var initOptions = function($colors){
        if($colors) colors = $colors;

        Highcharts.setOptions({
          colors: colors,
            credits: {
              enabled: false
            }
        });
    };

    // Intantiate highcharts
    var _create = function($id){
        $('#' + $id).highcharts(_setOptions());
    };

    var public_method =  {
        init: function($colors){
            initOptions($colors);
            return this;
        },
        setGeneralSeries: function ($all_series)
        {
            all_series = $all_series;
            return this;
        },
        setGeneralCategories: function ($all_categories)
        {
            all_categories = $all_categories;
            return this;
        },
        create: function($id)
        {
            _create($id);
            return this;
        },
        setBaseUrl: function ($base_url)
        {
            base_url = $base_url;
            return this;
        },
        setChart: function ($chart)
        {
            chart = $chart;
            custom_chart = true;
            return this;
        },
        setChartType: function ($chart_type)
        {
            chart_type = $chart_type;
            return this;
        },
        setChartHeight: function ($chart_height)
        {
            chart_height = $chart_height;
            return this;
        },
        setChartSpacingbottom: function ($chart_spacingBottom)
        {
            chart_spacingBottom = $chart_spacingBottom;
            return this;
        },
        setTitle: function ($title)
        {
            title = $title;
            custom_title = true;
            return this;
        },
        setChartTitle: function ($chart_title)
        {
            chart_title = $chart_title;
            return this;
        },
        setChartSubTitle: function ($chart_sub_title)
        {
            chart_sub_title = $chart_sub_title;
            return this;
        },
        setLegendTitleText: function ($legends_title_text)
        {
            legends_title_text = $legends_title_text;
            return this;
        },
        setXAxis: function ($xAxis)
        {
            xAxis = $xAxis;
            custom_x_axis = true;
            return this;
        },
        setXAxisCategories: function ($x_axis_categories)
        {
            x_axis_categories = $x_axis_categories;
            return this;
        },
        setYAxis: function ($yAxis)
        {
            yAxis = $yAxis;
            custom_y_axis = true;
            return this;
        },
        setYAxisTitle: function ($y_axis_title)
        {
            y_axis_title = $y_axis_title;
            return this;
        },
        setYAxisLabelEnable: function ($y_axis_label_enable)
        {
            y_axis_label_enable = $y_axis_label_enable;
            return this;
        },
        setYAxisMin: function ($y_axis_min)
        {
            y_axis_min = $y_axis_min;
            return this;
        },
        setTooltipShared: function ($tooltip_shared)
        {
            tooltip_shared = $tooltip_shared;
            return this;
        },
        setFollowPointer: function ($follow_pointer)
        {
            follow_pointer = $follow_pointer;
            return this;
        },
        setLegend: function ($legend)
        {
            legend = $legend;
            custom_legend = true;
            return this;
        },
        setLegendEnabled: function ($legend_enabled)
        {
            legend_enabled = $legend_enabled;
            return this;
        },
        setExportingEnabled: function ($exporting_enabled)
        {
            exporting_enabled = $exporting_enabled;
            return this;
        },
        setSeries: function ($series)
        {
            _series = $series;
            custom_series = true;
            return this;
        },
        setLegendsValue2Percent: function ($values_to_percent)
        {
            values_to_percent = $values_to_percent;
            series_has_percent = series_has_percent.concat($values_to_percent);
            return this;
        },
        populateActiveChart: function ($num)
        {
            var self = this;
            self.setXAxisCategories(all_categories['slide_' + $num])
                 // Set column
                 .setSeries(all_series['slide_' + $num])
                 // Draw the chart.
                 .create('series_' + $num);
        }
    }

    return public_method;

})(jQuery);
