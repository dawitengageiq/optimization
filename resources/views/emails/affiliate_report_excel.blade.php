<b>Affiliate Report Excel Report Date Range: </b>{{ "$startDate - $endDate" }}<br><br>
<b>Execution Duration: </b>{{ $executionDuration }}<br>

@if($status)
Attached is the report.
@else
An error occured. No report created.
@endif