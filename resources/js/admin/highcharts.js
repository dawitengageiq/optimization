/*!
 * Lead Reactor - EngageIq
 * highcharts object
 * All action, event related to highcharts were handled here.

 @class HIGHCHARTS
 @use https://code.highcharts.com/highcharts.js
 **/
var HIGHCHARTS = (function($)
{
	// Base url container
	var base_url ='';

	// Version of chart. Range olr, nlr.
	var version_name = 'olr';

	// Error type. Range crtical, high.
	var error_type = 'critical';

	/** BOF LABEL PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_label = false;
	// Label default variables
	var label_name = 'STATUS: ';
	var label_align = 'right';
	var label_x = 0; // offset
	var label_y = 40;// set the right offset
	var label_valign = 'bottom';
	var label_style = {
		width: '450px',
		fontSize: '20px',
		textAlign: 'right'
	};
	// Set label parameters
	var _setLabelName = function () {
		if(custom_label) return label_name.toUpperCase();
		else return label_name + error_type.toUpperCase();
	}
	/** EOF LABEL PARAMETERS **/

	/** BOF CHART PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_chart = false;
	// Chart default variables
	var chart = {};
	var chart_type = 'column'; // Default type of chart. Range: bar, column ....
	var chart_spacingBottom = 100; // Default spsace at the bottom of chart.
	// Set chart parameters
	var _setChart = function() {
		if(custom_chart) return chart;
		// Chart parameters
		chart = {
			type: chart_type,
			events: {
				load: function () {
					var label = this.renderer.label(_setLabelName()).css(label_style).add();
					label.align(Highcharts.extend(label.getBBox(), {
							align: label_align,
							x: label_x,
							verticalAlign: label_valign,
							y: label_y
						}),
						null,
						'spacingBox'
					);
				}
			},
			spacingBottom: chart_spacingBottom
		};
		return chart;
	}
	/** EOF CHART PARAMETERS **/

	/** BOF CHART TITLE PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_title = false;
	// Title dafault variables
	var title = {};
	var date = $('#theDate').val();
	var chart_title = 'Lead Reactor Leads Graph for ' + date;
	// Set title parameters
	var _setTitle = function() {
		if(custom_chart) return title;
		// Title parameters
		title = {
			text: chart_title
		}
		return title;
	}
	/** EOF CHART TITLE PARAMETERS **/

	/** BOF X-AXIS PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_x_axis = false;
	// X-axis default variables
	var xAxis = {};
	var x_axis_categories = [];
	// Set x-axis parameters
	var _setXAxis = function () {
		if(custom_x_axis) return xAxis;
		// X-axis parameters
		xAxis = {
			categories: x_axis_categories
		};
		return xAxis;
	}
	/** EOF X-AXIS PARAMETERS **/

	/** BOF Y-AXIS PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_y_axis = false;
	// Y-axis default variables
	var yAxis = {};
	var y_axis_title = 'Lead Reactor Leads Graph for ' + date;
	var y_axis_allowDecimals = false; // Default value for allow decimals
	var y_axis_min = 0;	// Default value for y axis min
	// Set y-axis parameters
	var _setYAxis = function (){
		if(custom_y_axis) return yAxis;
		// Y-axis parameters
		yAxis = {
			allowDecimals: y_axis_allowDecimals,
			min: y_axis_min,
			title: {
				text: y_axis_title
			}
		};
		return yAxis;
	}
	/** EOF Y-AXIS PARAMETERS **/

	/** BOF LEGEND PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_legend = false;
	// Legend default variables
	var legend = {};
	var legend_symbolHeight = 12; // Default symbol height
	var legend_symbolWidth = 12; // Default symbol width
	var legend_symbolRadius = 0; // Default symbol radius
	var legend_enabled = false; // Default for enable legend
	// Set legend parameters
	var _setLegend = function () {
		if(custom_legend) return legend;
		// Legend parameters
		legend = {
			symbolHeight: legend_symbolHeight,
			symbolWidth: legend_symbolWidth,
			symbolRadius: legend_symbolRadius,
			enabled: legend_enabled
		};
		return legend;
	}
	/** EOF LEGEND PARAMETERS **/

	/** BOF TOOLTIP PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_tooltip = false;
	var custom_tooltip_formatter = false;
	// Tooltip default variables
	var tooltip = {};
	var tooltip_formatter = {};
	var actual_rejection = {};
	var column_extra_details = {};
	var tooltip_shared = true;
    var tooltip_shadow = false;
	var tooltip_backgroundColor = '#EEEEEE';
	var tooltip_borderColor = '#286090';
	// Set tooltip formatter, rormat content.
	var _setTooltipFormatter = function () {
		if(custom_tooltip_formatter) return tooltip_formatter;
		// tooltip formatter parameters
		return function () {
			var tooltipMarkup = '';
			points = this.points;
			if(this.points) {
				var pointsLength = points.length;

				tooltipMarkup = '<span style="font-size: 10px">';
				tooltipMarkup += points[0].key + '</span><br/>';

				// Column extra details
				if('affiliate_id' in  column_extra_details[points[0].key]) {
					tooltipMarkup += 'AFFILIATE ID: ' + '<strong>' + column_extra_details[points[0].key]['affiliate_id'] + '</strong><br/>';
				}
				if('campaign_id' in  column_extra_details[points[0].key]) {
					tooltipMarkup += 'CAMPAIGN ID: ' + '<strong>' + column_extra_details[points[0].key]['campaign_id'] + '</strong><br/>';
				}
				if('campaign_name' in  column_extra_details[points[0].key]) {
					tooltipMarkup += 'CAMPAIGN NAME: ' + '<strong>' + column_extra_details[points[0].key]['campaign_name'] + '</strong><br/><br/>';
				}

				tooltipMarkup += 'ACTUAL REJECTION: ' + '<strong>';
				//tooltipMarkup += Math.round(actual_rejection[points[0].key]['actual']);
				//tooltipMarkup += ' - ';
				tooltipMarkup += actual_rejection[points[0].key]['percent'];
				tooltipMarkup += '</strong>' + '<br>';

				var index;
				var actual_leads;
				for(index = 0; index < pointsLength; index += 1) {
					if(index == 0) {
						var percentage = actual_leads = points[index].y;
						var actual_value = '';
					} else {
						var percentage = ((points[index].y/ points[index].total)*100).toFixed(0);
						var actual_value = Math.round(actual_leads * (percentage / 100)) + ' - ';
						var percentage = percentage + '%';
					}

					tooltipMarkup += '<span style="color:';
					tooltipMarkup += points[index].series.color;
					tooltipMarkup += '">\u25CF</span> ';
					tooltipMarkup += points[index].series.name;
					tooltipMarkup += ': <b>' + percentage  + '</b><br/>';
					//tooltipMarkup += ': <b>' + actual_value + percentage  + '</b><br/>';
				}
			}

			return tooltipMarkup;
		}
	}
	// Set tooltip parameters
	var _setTooltip = function() {
		if(custom_tooltip) return tooltip;
		// set the tooltip formatter first
		_setTooltipFormatter();
		// Tooltip parameters
		tooltip = {
			useHTML: true,
            shadow: tooltip_shadow,
			shared: tooltip_shared,
			backgroundColor: tooltip_backgroundColor,
			borderColor: tooltip_borderColor,
			formatter: _setTooltipFormatter()
		};
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
	var plot_column_stacking = 'normal';
	var plot_column_groupPadding = 0.25;
	var plot_column_borderWidth = 0;
	var plot_column_maxPointWidth = 50;
	// Set plot column parameters
	var _setPlotColumn = function () {
		if(custom_plot_column) return plot_column;
		// Plot column parameters
		plot_column = {
            stacking: plot_column_stacking,
			groupPadding: plot_column_groupPadding,
            borderWidth: plot_column_borderWidth,
            maxPointWidth: plot_column_maxPointWidth
        };
	}
	// plot series default variables
	var plot_series = {};
	var plot_series_pointPadding = 0;
	// Set plot series parameters
	var _setPlotSerries = function () {
		if(custom_plot_series) return plot_series;
		// plot series parameters
		plot_series = {
			cursor: 'pointer',
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
	var _series = [];
	var series_leads_data = [];
	var series_leads_urls = [];
	var series_duplicates_data = [];
	var series_duplicates_urls = [];
	var series_others_data = [];
	var series_others_urls = [];
	var series_filter_issues_data = [];
	var series_filter_issues_urls = [];
	var series_pre_pop_issues_data = [];
	var series_pre_pop_issues_urls = [];
	var series_point_width = 18;
	var series_click = '';
	// Set series click parameters
	var _setSeriesClick = function () {
		if(custom_series_click) return series_click;
		// Series click parameters
		return function() {
			var leadURL = this.series.userOptions.URLs[this.x]; // onclick get the x index and use it to find the URL
			if (leadURL)
				window.open(base_url + '/' +leadURL);
		}
	}
	// Set series parameters
	var _setSeries = function (){
		if(custom_series) return _series;
		// Series parameters
		_series = [{
	            name: 'LEADS',
	            data: series_leads_data,
	            stack: '1',
	            pointWidth: series_point_width,
             	URLs: series_leads_urls,
				point: {
					events: {
					 	click: _setSeriesClick()
					}
				}
	        }, {
	        	name: 'FILTER ISSUE',
	            data: series_filter_issues_data,
	            stack: '2',
	            pointWidth: series_point_width,
	            URLs: series_filter_issues_urls,
             	point: {
                    events: {
                        click: _setSeriesClick()
                    }
                }
	        }, {
	        	name: 'PRE-POP ISSUE',
	            data: series_pre_pop_issues_data,
	            stack: '2',
	            pointWidth: series_point_width,
	            URLs: series_pre_pop_issues_urls,
             	point: {
                    events: {
                        click: _setSeriesClick()
                    }
                }
	        }, {
	            name: 'OTHERS',
	            data: series_others_data,
	            stack: '2',
	            pointWidth: series_point_width,
	            URLs: series_others_urls,
             	point: {
                    events: {
                        click: _setSeriesClick()
                    }
                }
	        }, {
	            name: 'DUPLICATES',
	            data: series_duplicates_data,
	            stack: '2',
	            pointWidth: series_point_width,
             	URLs: series_duplicates_urls,
             	point: {
                    events: {
                        click: _setSeriesClick()
                    }
                }
	        }
		];
		return _series;
	}

	/** EOF SERIES PARAMETERS **/

	/** BOF OPTIONS PARAMETERS **/
	// Flag for not using the custom variables, if set to false the default value of variables below will be used..
	var custom_options = false;
	// options default variable
	var options = {};
	// Set option parameters
	var _setOptions = function () {
		if(custom_options) return options;
		// Option parameters
		options = {
			chart: _setChart(),
			title: _setTitle(),
			xAxis: _setXAxis(),
			yAxis: _setYAxis(),
			legend: _setLegend(),
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
		custom_x_axis = false;
		custom_y_axis = false;
		custom_legend = false;
		custom_tooltip = false;
		custom_tooltip_formatter = false;
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
	var initOptions = function(){
		Highcharts.setOptions({
		  colors: [
				'#FCB040', // Leads
				'#FFF200', // Filter Issue
				'#000000', // Pre-pop Issue
				'#00aeef', // Others
				'#EC008C'  // Duplicates
		  	],
			credits: {
			  enabled: false
			}
		});
	};
	// Intantiate highcharts
	var _create = function($id){
		$highcharts = $('#' + $id).highcharts(_setOptions());
	};

	/** BOF PUBLIC METHOD **/
	var public_method =  {
		init: function(){
			initOptions();
			return this;
		},
		create: function($id){
			_create($id);
			return this;
		},
		setVersion: function ($version){
			version_name = $version;
			return this;
		},
		setBaseUrl: function ($base_url){
			base_url = $base_url;
			return this;
		},
		setErrorType: function ($error_type){
			error_type = $error_type;
			return this;
		},
		setActualRejection: function ($actual_rejection){
			actual_rejection = $actual_rejection;
			return this;
		},
		setColumnExtraDetails: function ($column_extra_details){
			column_extra_details = $column_extra_details;
			return this;
		},
		setChart: function ($chart){
			chart = $chart;
			custom_chart = true;
			return this;
		},
		setChartType: function ($chart_type){
			chart_type = $chart_type;
			return this;
		},
		setChartSpacingbottom: function ($chart_spacingBottom){
			chart_spacingBottom = $chart_spacingBottom;
			return this;
		},
		setTitle: function ($title){
			title = $title;
			custom_title = true;
			return this;
		},
		setChartTitle: function ($chart_title){
			chart_title = $chart_title;
			return this;
		},
		setLabelName: function ($label_name){
			label_name = $label_name;
			custom_label = true;
			return this;
		},
		setLabelAlign: function ($label_align){
			label_align = $label_align;
			return this;
		},
		setLabelX: function ($label_x){
			label_x = $label_x;
			return this;
		},
		setLabelY: function ($label_y){
			label_y = $label_y;
			return this;
		},
		setLabelVAlign: function ($label_valign){
			label_valign = $label_valign;
			return this;
		},
		setLabelStyle: function ($label_style){
			label_style = $label_style;
			return this;
		},
		setXAxis: function ($xAxis){
			xAxis = $xAxis;
			custom_x_axis = true;
			return this;
		},
		setXAxisCategories: function ($x_axis_categories){
			x_axis_categories = $x_axis_categories;
			return this;
		},
		setYAxis: function ($yAxis){
			yAxis = $yAxis;
			custom_y_axis = true;
			return this;
		},
		setYAxisTitle: function ($y_axis_title){
			y_axis_title = $y_axis_title;
			return this;
		},
		setYAxisAllowDecimal: function ($y_axis_allowDecimals){
			y_axis_allowDecimals = $y_axis_allowDecimals;
			return this;
		},
		setYAxisMin: function ($y_axis_min){
			y_axis_min = $y_axis_min;
			return this;
		},
		setLegend: function ($legend){
			legend = $legend;
			custom_legend = true;
			return this;
		},
		setLegendSymbolHeight: function ($legend_symbolHeight){
			legend_symbolHeight = $legend_symbolHeight;
			return this;
		},
		setLegendSymbolWidth: function ($legend_symbolWidth){
			legend_symbolWidth = $legend_symbolWidth;
			return this;
		},
		setLegendSymbolRadius: function ($legend_symbolRadius){
			legend_symbolRadius = $legend_symbolRadius;
			return this;
		},
		setLegendEnabled: function ($legend_enabled){
			legend_enabled = $legend_enabled;
			return this;
		},
		setTooltip: function ($tooltip){
			tooltip = $tooltip;
			custom_tooltip = true;
			return this;
		},
		setTooltipShared: function ($tooltip_shared){
			tooltip_shared = $tooltip_shared;
			return this;
		},
		setTooltipBackgroundColor: function ($tooltip_backgroundColor){
			tooltip_backgroundColor = $tooltip_backgroundColor;
			return this;
		},
		setTooltipBorderColor: function ($tooltip_borderColor){
			tooltip_borderColor = $tooltip_borderColor;
			return this;
		},
		setTooltipFormatter: function ($tooltip_formatter){
			tooltip_formatter = $tooltip_formatter;
			custom_tooltip_formatter = true;
			return this;
		},
		setPlotOptions: function ($plotOptions){
			plotOptions = $plotOptions;
			custom_plot_options = true;
			return this;
		},
		setPlotColumn: function ($plot_column){
			plot_column = $plot_column;
			custom_plot_column = true;
			return this;
		},
		setPlotColumnStacking: function ($plot_column_stacking){
			plot_column_stacking = $plot_column_stacking;
			return this;
		},
		setPlotColumnGroupPadding: function ($plot_column_groupPadding){
			plot_column_groupPadding = $plot_column_groupPadding;
			return this;
		},
		setPlotColumnBorderWidth: function ($plot_column_borderWidth){
			plot_column_borderWidth = $plot_column_borderWidth;
			return this;
		},
		setPlotColumnMaxPointWidth: function ($plot_column_maxPointWidth){
			plot_column_maxPointWidth = $plot_column_maxPointWidth;
			return this;
		},
		setPlotSeries: function ($plot_series){
			plot_series = $plot_series;
			custom_plot_series = true;
			return this;
		},
		setPlotSeriesPointPadding: function ($plot_series_pointPadding){
			plot_series_pointPadding = $plot_series_pointPadding;
			return this;
		},
		setSeries: function ($series){
			_series = $series;
			custom_series = true;
			return this;
		},
		setSeriesLeadsData: function ($series_leads_data){
			series_leads_data = $series_leads_data;
			return this;
		},
		setSeriesLeadsUrls: function ($series_leads_urls){
			series_leads_urls = $series_leads_urls;
			return this;
		},
		setSeriesDuplicatesData: function ($series_duplicates_data){
			series_duplicates_data = $series_duplicates_data;
			return this;
		},
		setSeriesDuplicatesUrls: function ($series_duplicates_urls){
			series_duplicates_urls = $series_duplicates_urls;
			return this;
		},
		setSeriesOthersData: function ($series_others_data){
			series_others_data = $series_others_data;
			return this;
		},
		setSeriesOthersUrls: function ($series_others_urls){
			series_others_urls = $series_others_urls;
			return this;
		},
		setSeriesFilterIssuesData: function ($series_filter_issues_data){
			series_filter_issues_data = $series_filter_issues_data;
			return this;
		},
		setSeriesFilterIssuesUrls: function ($series_filter_issues_urls){
			series_filter_issues_urls = $series_filter_issues_urls;
			return this;
		},
		setSeriesPrePopData: function ($series_pre_pop_issues_data){
			series_pre_pop_issues_data = $series_pre_pop_issues_data;
			return this;
		},
		setSeriesPrePopUrls: function ($series_pre_pop_issues_urls){
			series_pre_pop_issues_urls = $series_pre_pop_issues_urls;
			return this;
		},
		setSeriesPointsWidth: function ($series_point_width){
			series_point_width = $series_point_width;
			return this;
		},
		setSeriesClick: function ($series_click){
			series_click = $series_click;
			$custom_series_click = true;
			return this;
		},
		setOptions: function ($options){
			options = $options;
			custom_options = true;
			return this;
		}
	}

	return public_method;
	/** EOF PUBLIC METHOD **/

})(jQuery);

HIGHCHARTS.init();
console.log(HIGHCHARTS);
