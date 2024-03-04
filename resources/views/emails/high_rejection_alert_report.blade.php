<b>Rejection Rate Summary Per Affiliate for {!! $yesterday !!}</b>
<br>
<br>
<p>Please visit the site page for charts and more details: <a href="{!! $chart_url !!}">{!! $chart_url !!}</a></p>
<br>
Sorted by Lead count <b style="font-style: italic;">(High to Low)</b>
<table style="border-collapse: collapse;border: 1px solid black;">
	<thead>
		<tr>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Rev Tracker [Affiliate (ID)]</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Campaign</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Campaign ID</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Lead Count</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Actual Rejection</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Split</th>
		</tr>
	</thead>
	<tbody>
		@foreach($reports as $report)
			<tr>
				<td style="border: 1px solid black;text-align: center;">{!! $report['revenue_tracker_column'] !!}</td>
				<td style="border: 1px solid black;text-align: center;">{!! $report['campaign'] !!}</td>
				<th style="border: 1px solid black;text-align: center;">{!! $report['campaign_id'] !!}</th>
				<td style="border: 1px solid black;text-align: center;">{!! $report['lead_count'] !!}</td>
				<th style="border: 1px solid black;text-align: center;{!! $report['reject_rate'] == 'CRITICAL' ? 'color: #fe0304;' : 'color: #ffc002;'!!}">{!! $report['actual_rejection'] !!}</th>
				<td style="border: 1px solid black;text-align: center;">{!! $report['split'] !!}</td>
			</tr>
		@endforeach
	</tbody>
</table>
@if($duplicates)
<br>
<hr>
<br>
<b>Duplicate Rejection Rate Summary of Campaigns</b>
<br>
<br>
Sorted by Lead count <b style="font-style: italic;">(High to Low)</b>
<table style="border-collapse: collapse;border: 1px solid black;">
	<thead>
		<tr>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Rev Tracker [Affiliate (ID)]</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Campaign</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Campaign ID</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Lead Count</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Actual Rejection</th>
			<th style="padding: 2px 5px;border: 1px solid black;text-align: center;">Split</th>
		</tr>
	</thead>
	<tbody>
		@foreach($duplicates as $duplicate)
			<tr>
				<td style="border: 1px solid black;text-align: center;">{!! $duplicate['revenue_tracker_column'] !!}</td>
				<td style="border: 1px solid black;text-align: center;">{!! $duplicate['campaign'] !!}</td>
				<th style="border: 1px solid black;text-align: center;">{!! $duplicate['campaign_id'] !!}</th>
				<td style="border: 1px solid black;text-align: center;">{!! $duplicate['lead_count'] !!}</td>
				<th style="border: 1px solid black;text-align: center;{!! $duplicate['reject_rate'] == 'CRITICAL' ? 'color: #fe0304;' : 'color: #ffc002;'!!}">{!! $duplicate['actual_rejection'] !!}</th>
				<td style="border: 1px solid black;text-align: center;">{!! $duplicate['split'] !!}</td>
			</tr>
		@endforeach
	</tbody>
</table>
@endif
<br>
<br>
<p>Please visit the site page for charts and more details: <a href="{!! $chart_url !!}">{!! $chart_url !!}</a></p>
