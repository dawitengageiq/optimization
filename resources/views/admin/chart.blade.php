@extends('app')

@section('title')
	{!! strtoupper($highcharts['version']) !!} Charts
@stop

@section('header')
	<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
	<!--<link rel="stylesheet" href="/css/admin/chart.css" type="text/css" media="all" />-->
    <link href="{{ asset('css/admin/charts.min.css') }}" rel="stylesheet" media="all">
@stop

@section('content')
	<input type="hidden" id="theDate" value="{!! $highcharts['date'] !!}"/>
	@if($highcharts['has_data'])
		<div class="row">
			<br>
			<div class="col-xs-6">
				@if(array_key_exists('critical', $highcharts['group_series']))
				<div id="chart-slider-critical" class="carousel slide" data-ride="carousel" data-interval="false">
					<!-- Indicators -->
					<ol class="carousel-indicators">
						@for($li = 0; $li <  count($highcharts['group_series']['critical']); $li++)
						<li id="a" data-target="#chart-slider-critical" data-slide-to="{!! $li !!}" class="{!! ($li == 0) ? 'active' : '' !!}"></li>
						@endfor
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
						@for($li = 0; $li <  count($highcharts['group_series']['critical']); $li++)
						<div class="item {!! ($li == 0) ? 'active' : '' !!}" style="min-height: 400px;">
							<div class="charts" id="critical_chart_{!! $li !!}" data-error-type="critical" data-index="{!! $li !!}"></div>
						</div>
						@endfor
					</div>

				</div>
				@endif
			</div>
			<div class="col-xs-6">
				@if(array_key_exists('high', $highcharts['group_series']))
				<div id="chart-slider-high" class="carousel slide" data-ride="carousel" data-interval="false">
				<!-- Indicators -->
					<ol class="carousel-indicators">
						@for($li = 0; $li <  count($highcharts['group_series']['high']); $li++)
						<li id="a" data-target="#chart-slider-high" data-slide-to="{!! $li !!}" class="{!! ($li == 0) ? 'active' : '' !!}"></li>
						@endfor
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
						@for($li = 0; $li <  count($highcharts['group_series']['high']); $li++)
						<div class="item {!! ($li == 0) ? 'active' : 'inactive' !!}" style="min-height: 400px;">
							<div class="charts" id="high_chart_{!! $li !!}" data-error-type="high" data-index="{!! $li !!}"></div>
						</div>
						@endfor
					</div>
				</div>
				@endif
			</div>
			<div class="col-xs-12">
				<div id="legend">
				<div id="items"></div>
				</div>
			</div>
		</div>
	@else
		<div class="alert alert-info"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Oh Snap! No data to draw.</div>
	@endif
@stop

@section('footer')
	<!-- <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script> -->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script> -->
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<!--<script src="/js/admin/highcharts.js"></script>-->
    <script src="{{ asset('js/admin/highcharts.min.js') }}"></script>

	<script>

	$(function () {
	    @if ($highcharts['has_data'])

			// Initiate carousel
			var carousel = $('.carousel').carousel();

			// global variables
			var group_categories = {!! json_encode($highcharts['group_categories']) !!};
			var group_series = {!! json_encode($highcharts['group_series']) !!};
			var group_padding = {!! json_encode($highcharts['group_padding']) !!};

			// Function to determine the points width
			var getPointsWidth = function () {
				var points_width_by_container_width = {
					450 : 6, 480 : 7, 580 : 8, 670 : 9, 750 : 10, 840 : 11, 890 : 12, 980 : 13, 1030 : 14, 1130 : 15, 1220 : 16, 1270 : 17, 1340 : 18, 1440 : 19, 1550 : 20
				}

				indexes = $.map(points_width_by_container_width, function(obj, index) {
				    if(index <= $('#page-wrapper .row .col-xs-12').width()) {
				        return index;
				    }
				})

				var points_width = points_width_by_container_width[indexes[indexes.length - 1]];

				return (points_width) ? points_width : 5;
			}

			// Function to generate chart
			var populateActiveChart = function (index, error_type)
			{
				HIGHCHARTS.setVersion('{!! $highcharts['version'] !!}')
					.setErrorType(error_type)
					.setXAxisCategories(group_categories[error_type]['chart_' + index][0])
					.setPlotColumnGroupPadding(group_padding[error_type][index])
					.setSeriesPointsWidth(getPointsWidth())
					.setSeriesLeadsData(group_series[error_type]['chart_' + index][0]['data'])
					.setSeriesDuplicatesData(group_series[error_type]['chart_' + index][1]['data'])
					.setSeriesOthersData(group_series[error_type]['chart_' + index][2]['data'])
					.setSeriesFilterIssuesData(group_series[error_type]['chart_' + index][3]['data'])
					.setSeriesPrePopData(group_series[error_type]['chart_' + index][4]['data'])
					@if( $highcharts['version'] == 'nlr')
						.setSeriesLeadsUrls(group_series[error_type]['chart_' + index][0]['URLs'])
						.setSeriesDuplicatesUrls(group_series[error_type]['chart_' + index][1]['URLs'])
						.setSeriesOthersUrls(group_series[error_type]['chart_' + index][2]['URLs'])
						.setSeriesFilterIssuesUrls(group_series[error_type]['chart_' + index][3]['URLs'])
						.setSeriesPrePopUrls(group_series[error_type]['chart_' + index][4]['URLs'])
					@endif
					.create(error_type + '_chart_' + index);
			}

			// Initiate highcharts and include some global variables
			HIGHCHARTS.init()
				.setBaseUrl($('#baseUrl').html())
				.setActualRejection({!! json_encode($highcharts['actual_rejection']) !!})
				.setColumnExtraDetails({!! json_encode($highcharts['column_extra_details']) !!});

			// Create the first in group of charts
			@if(array_key_exists('critical', $highcharts['group_series']))
				populateActiveChart(0, 'critical');
			@endif

			@if(array_key_exists('high', $highcharts['group_series']))
				populateActiveChart(0, 'high');
			@endif

			// Load chart after slides
			carousel.bind('slid.bs.carousel', function (e) {
				populateActiveChart($(this).find('.item.active .charts').data('index'), $(this).find('.item.active .charts').data('error-type'));
			});

			// Add legend to the bottom of the page
		    var highcharts = $('.charts').highcharts();
		    var series = highcharts.series;
		    var $legend = $('#legend');
		    var $items = $('#items');

		    series.forEach(function(item) {
		        var el = renderLegendItem(item);
		        $items.prepend(el);
		    });

		    function renderLegendItem(item) {
		        return '<div class="legend-item"><div class="square" style="background: '
		            + item.color + '"></div><div class="legend-item-label">'
		            + item.name + '</div></div>';
		    }

	    @endif
	});
	</script>
@stop
