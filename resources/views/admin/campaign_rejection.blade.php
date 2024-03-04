@extends('app')

@section('title')
	Campaign Rejection Charts
@stop

@section('header')
	<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
	<!--<link rel="stylesheet" href="/css/admin/chart.css" type="text/css" media="all" />-->
    <link href="{{ asset('css/admin/charts.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="panel panel-default">
    <div class="panel-body">
        {!! Form::open(['url' => url('admin/campaign/rejection'),'class'=> '', 'id' => 'campRejection-form', 'method' => 'GET']) !!}
            @include('partials.flash')
            @include('partials.error')
            <div class="row">
                <div class="form-group col-md-4 col-lg-4">
                    {!! Form::label('date_range','Predefined Date Range') !!}
                    {!! Form::select('date_range',['' => '','yesterday' => 'Yesterday','week' => 'Week to date', 'month' => 'Month to date','last_month' => 'Last Month', 'year' => 'Year to date'],isset($inputs['date_range']) ? $inputs['date_range'] : 'month' ,['class' => 'this_field form-control','id' => 'date_range']) !!}
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_from','Date From') !!}
                    <div class="input-group date">
                        <input name="date_from" id="date_from" value="{{ isset($inputs['date_from']) ? $inputs['date_from'] : '' }}" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    {!! Form::label('lead_date_to','Date To') !!}
                    <div class="input-group date">
                        <input name="date_to" id="date_to" value="{{ isset($inputs['date_to']) ? $inputs['date_to'] : '' }}" type="text" class="lead_date form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group col-md-2 text-center" style="padding-top: 22px;">
                	<button type="submit" id="generateChartBtn" class="btn btn-primary">Generate Chart</button>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>
	<input type="hidden" id="theDate" value="{!! $highcharts['date'] !!}"/>
	@if($highcharts['has_data'])
		<div class="row">
			<br>
			<div class="col-xs-12">
				@if(array_key_exists('critical', $highcharts['series']))
				<div id="criticalChart"></div>
				<div id="chart-slider-critical" class="carousel slide" data-ride="carousel" data-interval="false">
					<!-- Indicators -->
					<ol class="carousel-indicators">
						@for($li = 0; $li <  count($highcharts['series']['critical']); $li++)
						<li id="a" data-target="#chart-slider-critical" data-slide-to="{!! $li !!}" class="highchart_slider {!! ($li == 0) ? 'active' : '' !!}" data-type="critical" data-index="{!! $li !!}"></li>
						@endfor
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
						@for($li = 0; $li <  count($highcharts['series']['critical']); $li++)
						<div class="item {!! ($li == 0) ? 'active' : '' !!}" style="min-height: 50px;">
							<div class="charts" id="critical_chart_{!! $li !!}" data-error-type="critical" data-index="{!! $li !!}"></div>
						</div>
						@endfor
					</div>

				</div>
				@endif
			</div>
			<div class="col-xs-12">
				@if(array_key_exists('high', $highcharts['series']))
				<div id="highChart"></div>
				<div id="chart-slider-high" class="carousel slide" data-ride="carousel" data-interval="false">
					<!-- Indicators -->
					<ol class="carousel-indicators">
						@for($li = 0; $li <  count($highcharts['series']['high']); $li++)
						<li id="a" data-target="#chart-slider-high" data-slide-to="{!! $li !!}" class="highchart_slider {!! ($li == 0) ? 'active' : '' !!}" data-type="high" data-index="{!! $li !!}"></li>
						@endfor
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
						@for($li = 0; $li <  count($highcharts['series']['high']); $li++)
						<div class="item {!! ($li == 0) ? 'active' : '' !!}" style="min-height: 50px;">
							<div class="charts" id="high_chart_{!! $li !!}" data-error-type="high" data-index="{!! $li !!}"></div>
						</div>
						@endfor
					</div>

				</div>
				@endif
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
	<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#date_range').change(function()
			    {
			        if($(this).val() != '')
			        {
			            $('#date_from').val('');
			            $('#date_to').val('');
			        }
			    });

			    $('.lead_date').change(function()
			    {
			        if($(this).val() != '')
			        {
			            $('#date_range').val('');
			        }
			    });
			$('.input-group.date').datepicker({
		        format: "yyyy-mm-dd",
		        clearBtn: true,
		        autoclose: true,
		        todayHighlight: true
		    });
		});
	</script>
	@if($highcharts['has_data'])
	<script>
		var date_from = '{!! $highcharts['date_from'] !!}',
			date_to = '{!! $highcharts['date_to'] !!}';
		var campaigns = {!! json_encode($highcharts['campaigns'], JSON_NUMERIC_CHECK) !!};
		var group_categories = {!! json_encode($highcharts['categories'], JSON_NUMERIC_CHECK) !!};
		var series = {!! json_encode($highcharts['series'], JSON_NUMERIC_CHECK) !!};
		var data = {!! json_encode($highcharts['actual'], JSON_NUMERIC_CHECK) !!};

		console.log({!! json_encode($highcharts, JSON_NUMERIC_CHECK) !!});

		Highcharts.setOptions({
			chart: {
			    type: 'column',
			    height: 750,
			  },
		  	colors: [
				'#6b5b95', // Leads
				'#00BCD4', // ACCEPTABLE
				'#d64161', // DUPLICATES
				'#ff7b25', // FILTER ISSUE
				'#4CAF50',  // PRE-POP ISSUE,
				'#034f84' // OTHERS
		  	],
			credits: {
			  enabled: false
			},
			plotOptions: {
			    column: {
			      stacking: 'normal'
			    },
			    series: {
		            cursor: 'pointer',
		            point: {
		                events: {
		                    click: function () {
		                    	index = this.colorIndex;
		                    	campaign = this.category;
		                    	console.log(this);
		                        url = $('#baseUrl').html() + '/admin/searchLeads/?campaign_id='+campaign+'&limit_rows=50000&table=leads&lead_date_from='+date_from+'&lead_date_to='+date_to

		                        if(index > 0) url += '&lead_status=2';
								if(index == 1) url += '&rejected_type=acceptable';
								else if(index == 2) url += '&rejected_type=duplicates';
								else if(index == 3) url += '&rejected_type=filter_issues';
								else if(index == 4) url += '&rejected_type=pre_pop_issues';
								else if(index == 5) url += '&rejected_type=others';

		                        console.log(url);

		                        var win = window.open(url, '_blank');
  								win.focus();
		                    }
		                }
		            }
		        },
			},
			yAxis: {
			    allowDecimals: false,
			    min: 0,
			    title: {
			      text: 'Number of Leads'
			    }
			},
			tooltip: {
				useHTML: true,
				shadow: false,
				shared: true,
				backgroundColor: '#EEEEEE',
				borderColor: '#286090',
				hideDelay: 2000,
				style: {
				    pointerEvents: 'auto'
				},
			    formatter: function () {
			    	// console.log(this)
			    	var index;
					var actual_leads;
					var points = this.points;
					var pointsLength = points.length;
					var additionalData = data[points[0]['point']['category']];
			    	var tooltipMarkup = '<b>' + campaigns[this.x] + '</b><br/>';
			    	tooltipMarkup += 'Campaign ID: ' + '<strong>' + this.x + '</strong><br/>';

			    	var rejection_rate = 0,
			    		rejection_count = Number(additionalData.reject_count),
			    		total_count = Number(additionalData.total_count),
			    		acceptable_reject_count = Number(additionalData.acceptable_reject_count),
			    		duplicate_count = Number(additionalData.duplicate_count),
			    		filter_count = Number(additionalData.filter_count),
			    		prepop_count = Number(additionalData.prepop_count),
			    		other_count = Number(additionalData.other_count);

			    	if(rejection_count > 0 && total_count > 0) rejection_rate = ((rejection_count / total_count) * 100).toFixed(2);
			        tooltipMarkup += 'Actual Rejection: ' + '<strong>' + rejection_rate + '% </strong><br/><br/>';

			        var url = $('#baseUrl').html() + '/admin/searchLeads/?campaign_id='+this.x+'&limit_rows=50000&table=leads&lead_date_from='+date_from+'&lead_date_to='+date_to;

			        //LEADS
			        tooltipMarkup += '<a href="'+url+'" target="_blank"><span style="color:#6b5b95">\u25CF</span> ALL LEADS: <b>' + total_count  + '</b></a><br/>';
			        //REJECTED
			        tooltipMarkup += '<a href="'+url+'&lead_status=2" target="_blank"><span style="color:#795548">\u25CF</span> REJECTED LEADS: <b>' + rejection_count  + '</b></a><br/>';
			        //ACCEPTABLE
			        var acceptable_percentage = rejection_count > 0 && acceptable_reject_count > 0 ? ((acceptable_reject_count / rejection_count) * 100).toFixed(2) : 0;
			        tooltipMarkup += '<a href="'+url+'&lead_status=2&rejected_type=acceptable" target="_blank"><span style="color:#00BCD4">\u25CF</span> ACCEPTABLE: <b>' + acceptable_percentage  + '%</b></a><br/>';
			        //DUPLICATES
			        var duplicate_percentage = rejection_count > 0 && duplicate_count > 0 ? ((duplicate_count / rejection_count) * 100).toFixed(2) : 0;
			        tooltipMarkup += '<a href="'+url+'&lead_status=2&rejected_type=duplicates" target="_blank"><span style="color:#d64161">\u25CF</span> DUPLICATES: <b>' + duplicate_percentage  + '%</b></a><br/>';
			        //FILTER ISSUE
			        var filter_percentage = rejection_count > 0 && filter_count > 0 ? ((filter_count / rejection_count) * 100).toFixed(2) : 0;
			        tooltipMarkup += '<a href="'+url+'&lead_status=2&rejected_type=filter_issues" target="_blank"><span style="color:#ff7b25">\u25CF</span> FILTER ISSUE: <b>' + filter_percentage  + '%</b></a><br/>';
			        //PREPOP ISSUE
			        var prepop_percentage = rejection_count > 0 && prepop_count > 0 ? ((prepop_count / rejection_count) * 100).toFixed(2) : 0;
			        tooltipMarkup += '<a href="'+url+'&lead_status=2&rejected_type=pre_pop_issues" target="_blank"><span style="color:#4CAF50">\u25CF</span> PRE-POP ISSUE: <b>' + prepop_percentage  + '%</b></a><br/>';
			        //OTHERS
			        var other_percentage = rejection_count > 0 && other_count > 0 ? ((other_count / rejection_count) * 100).toFixed(2) : 0;
			        tooltipMarkup += '<a href="'+url+'&lead_status=2&rejected_type=others" target="_blank"><span style="color:#034f84">\u25CF</span> OTHERS: <b>' + other_percentage  + '%</b></a><br/>';

			        return tooltipMarkup;
			    },
			    positioner: function(labelWidth, labelHeight, point) {
			        var tooltipX = point.plotX + 20;
			        var tooltipY = point.plotY - 30;
			        return {
			            x: tooltipX,
			            y: tooltipY
			        };
			    }
			},
			xAxis: {
			  	labels: {
	                formatter: function() {
	                	return campaigns[this.value];
	                },
	            }
			},
		});

		var criticalChart = Highcharts.chart('criticalChart', {
		  title: {
		    text: 'Critical Rejection for {!! $highcharts['date'] !!}'
		  },
		  xAxis: {
		    categories: group_categories['critical'][0]
		  },
		  series: series['critical'][0]
		});

		var highChart = Highcharts.chart('highChart', {
		  title: {
		    text: 'High Rejection for {!! $highcharts['date'] !!}'
		  },
		  xAxis: {
		    categories: group_categories['high'][0]
		  },
		  series: series['high'][0]
		});

		$('.highchart_slider').click(function() {
			var id = $(this).data('index'),
				type = $(this).data('type');
			console.log(id)
			console.log(type)
			console.log(series[type][id]);
			console.log(group_categories[type][id])

			if(type == 'critical') {
				criticalChart.update({
			    	xAxis: {
					    categories: group_categories[type][id]
					},
			        series: series[type][id]
			    });
			}else {
				highChart.update({
			    	xAxis: {
					    categories: group_categories[type][id]
					},
			        series: series[type][id]
			    });
			}
		});

	</script>
	@endif
@stop
