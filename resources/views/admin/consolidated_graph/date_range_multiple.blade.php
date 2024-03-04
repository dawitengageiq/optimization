@extends('admin.consolidated_graph.index')

@section('graph_header')
<link href="{{ asset('css/admin/consolidated/date_range_multiple.min.css') }}" rel="stylesheet" media="all">
<style media="screen">
	li.select2-results__option[aria-disabled="true"] {
		display: none;
	}
	.red {color: #f00;}
	.yellow {color: #ffc513;}
	.green {color: #3daa10;}
</style>
@stop

@section('graph_search')
@include('admin.consolidated_graph.includes.graph_search')
@stop

@section('graph_table')

<div class="panel panel-default" style="margin-bottom: 3px;">
	<div class="panel-heading">
		<h3 class="panel-title">Graph Details</h3>
	</div>
	<div class="panel-body">
		<div>
			<table id="" class="grp_tbl table table-bordered table-striped fixed_headers" role="grid" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th style="min-width: 65px;">Date</th>
						<th>Rev Tracker ID</th>
						<th style="width:60px !important">S1</th>
						<th style="width:60px !important">S2</th>
						<th style="width:60px !important">S3</th>
						<th style="width:60px !important">S4</th>
						<th style="width:60px !important">S5</th>
						@foreach($columns as $column)
						<th>
							{!! $legends[$column]['alias'] !!}
						</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					<?php 
						$current_rev_tracker = null;
					?>
					@foreach($records as $record)
						<?php
							$border = '';
							if($current_rev_tracker != null && $record['revenue_tracker_id'] != $current_rev_tracker) {
								$border = 'border-top: 2px solid #7e7e7e;';
							}
							$current_rev_tracker = $record['revenue_tracker_id'];
						?>
					<tr>
						<td style="{!! $border !!}min-width: 65px;">{{ $record['date'] }}</td>
						<td style="{!! $border !!}">{{ $record['revenue_tracker_id'] }}</td>
						<td style="{!! $border !!}">{!! $inputs['sib_s1'] ? $record['s1'] : '' !!}</td>
						<td style="{!! $border !!}">{!! $inputs['sib_s2'] ? $record['s2'] : '' !!}</td>
						<td style="{!! $border !!}">{!! $inputs['sib_s3'] ? $record['s3'] : '' !!}</td>
						<td style="{!! $border !!}">{!! $inputs['sib_s4'] ? $record['s4'] : '' !!}</td>
						<td style="{!! $border !!}">{{ $record['s5'] }}</td>
						@foreach ($columns as $column)
							@if(array_key_exists($column, $record))
								<td style="{!! $border !!}background-color: {!! $legends[$column]['color'] !!}">
									<?php 
										$value = $record[$column];
										if($value == '') $value = 0;
										$value = sprintf('%0.2f', $record[$column]);
									?>
								{!! $value !!}</td>
							@else
								<td style="background-color: {!! $legends[$column]['color'] !!}"></td>
							@endif
						@endforeach
					</tr>
				@endforeach
				</tbody>
				{{-- </tfooter>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						@foreach($columns as $column)
						<th style="background-color: {!! $legends[$column]['color'] !!}"></th>
						@endforeach
					</tr>
				</tfooter> --}}
				@if(isset($ab_testing['yesterday']))
				<tfooter>
					<tr>
						<th style="min-width: 65px;border-top:2px solid #7e7e7e">Total</th>
						<th style="border-top:2px solid #7e7e7e">Today Vs. Yesterday</th>
						<td style="border-top:2px solid #7e7e7e"></td>
						<td style="border-top:2px solid #7e7e7e"></td>
						<td style="border-top:2px solid #7e7e7e"></td>
						<td style="border-top:2px solid #7e7e7e"></td>
						<td style="border-top:2px solid #7e7e7e"></td>
						@foreach($columns as $column)
							@if(array_key_exists($column, $ab_testing['yesterday']))
								<td style="border-top:2px solid #7e7e7e" class="{!! $ab_testing['yesterday'][$column]['c'] !!}">{!! $ab_testing['yesterday'][$column]['r'] !!}</td>
							@endif
						@endforeach
					</tr>
					<tr>
						<th style="min-width: 65px;">Total</th>
						<th>Today Vs. 30-day Average</th>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						@foreach($columns as $column)
							@if(array_key_exists($column, $ab_testing['30']))
								<td class="{!! $ab_testing['30'][$column]['c'] !!}">{!! $ab_testing['30'][$column]['r'] !!}</td>
							@endif
						@endforeach
					</tr>
				</tfooter>
				@endif
			</table>

			<br>

			{{-- @if(isset($ab_testing['yesterday']))
			<table id="consolidated_graph_ab_testing" class="table table-bordered table-striped fixed_headers" role="grid" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th style="min-width: 65px;">Date</th>
						<th>Rev Tracker ID</th>
						<th style="width:60px !important">S1</th>
						<th style="width:60px !important">S2</th>
						<th style="width:60px !important">S3</th>
						<th style="width:60px !important">S4</th>
						<th style="width:60px !important">S5</th>
						@foreach($columns as $column)
						<th>
							{!! $legends[$column]['alias'] !!}
						</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					<tr>
						<th style="min-width: 65px;">Total</th>
						<th>Today Vs. Yesterday</th>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						@foreach($columns as $column)
							@if(array_key_exists($column, $ab_testing['yesterday']))
								<td class="{!! $ab_testing['yesterday'][$column]['c'] !!}">{!! $ab_testing['yesterday'][$column]['r'] !!}</td>
							@endif
						@endforeach
					</tr>
					<tr>
						<th style="min-width: 65px;">Total</th>
						<th>Today Vs. 30-day Average</th>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						@foreach($columns as $column)
							@if(array_key_exists($column, $ab_testing['30']))
								<td class="{!! $ab_testing['30'][$column]['c'] !!}">{!! $ab_testing['30'][$column]['r'] !!}</td>
							@endif
						@endforeach
					</tr>
				</tbody>
			</table>
			@endif --}}
		</div>
	</div>
</div>
@stop

@section('graph_footer')
<!-- list of bower components, can't compile for the dependencies will not be found -->
<script src="/bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
<script src="/bower_components/select2/dist/js/select2.min.js"></script>
<script src="/bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script>

<!-- Compile script -->
<script src="/js/admin/consolidated/date_range_multiple.min.js"></script>

<!-- Script with data from php -->
<script type="text/javascript">
$(function () {
	@if($has_records)
	// @foreach($records as $index => $record)
	// 	lib.default.toDataTable('#grp_tbl_{{ $index }}');
	// @endforeach
	//
	$('.grp_tbl').dataTable({
	    "scrollX": true,
	    "searching": false,
	    "ordering": false,
	    "info":     false,
	    lengthMenu: [[10, 25, 50, 100, -1],[10, 25, 50, 100, 'All']],
	    // "order": [[ 1, "asc" ], [ 0, "asc" ], [ 2, "asc" ], [ 3, "asc" ], [ 4, "asc" ], [ 5, "asc" ], [ 6, "asc" ]],
	});

	$('#consolidated_graph_ab_testing').dataTable({
	    "scrollX": true,
	    "searching": false,
	    "ordering": false,
	    "info":     false,
	    "bPaginate": false,
	});
	
	@endif


	// $(".dataTables_scrollBody").on("scroll", function (e) {
	// 	console.log('Scroll')
	//     horizontal = e.currentTarget.scrollLeft;
	//     vertical = e.currentTarget.scrollTop;
 //    });

    var target = $(".dataTables_scrollBody");
	$(".dataTables_scrollBody").scroll(function() {
		console.log('Scroll')
		target.prop("scrollTop", this.scrollTop)
	      	.prop("scrollLeft", this.scrollLeft);
	});
});
</script>
@stop
