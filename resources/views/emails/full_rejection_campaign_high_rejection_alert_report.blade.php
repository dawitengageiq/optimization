<b>The following Campaigns <span style="color:red">has 100% Rejection Rate</span> for {!! $yesterday !!} and because of that they are currently turned OFF. Email has been sent to its designated advertiser to notify them this issue.</b>
<br>
<p>Please visit the site page for charts and more details: <a href="{!! $chart_url !!}">{!! $chart_url !!}</a></p>
<br>
Sorted by Lead count <b style="font-style: italic;">(High to Low)</b>
<table style="border-collapse: collapse;border: 1px solid black;">
	<thead>
		<tr>
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
				<td style="border: 1px solid black;text-align: center;">{!! $report['campaign'] !!}</td>
				<th style="border: 1px solid black;text-align: center;">{!! $report['campaign_id'] !!}</th>
				<td style="border: 1px solid black;text-align: center;">{!! $report['lead_count'] !!}</td>
				<th style="border: 1px solid black;text-align: center;{!! $report['reject_rate'] == 'CRITICAL' ? 'color: #fe0304;' : 'color: #ffc002;'!!}">{!! $report['actual_rejection'] !!}</th>
				<td style="border: 1px solid black;text-align: center;">{!! $report['split'] !!}</td>
			</tr>
		@endforeach
	</tbody>
</table>
