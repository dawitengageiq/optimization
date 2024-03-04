<p>Consolidated Graph Data Job Queue Successfully Executed!</p>
<p>&nbsp;</p>
@if(count($revTrackerIDs))
<p>Revenue tracker ids that are update: <br />CD{{ implode(', CD', $revTrackerIDs) }} .</p>
@else
<p>Although the job was Successfully executed but there is no revenue tracker was updated due to this filter listed below:.</p>
<ul>
    <li>The affiliates have no revenue trackers related to it.</li>
    <li>The revenue trackers have no records for clicks vs registration on this date ({!! $dateExecuted->format('Y-m-d') !!})</li>
    <li>The revenue trackers have no/"0 count" registration records on this date ({!! $dateExecuted->format('Y-m-d') !!})</li>
    <li>The revenue trackers have no/"0 count" clicks records on this date ({!! $dateExecuted->format('Y-m-d') !!})</li>
</ul>
@endif
<p>&nbsp;</p>
<h4>Execution Details: </h4>
<b>Date Executed: </b>{{ $dateExecuted }}<br><br>
<b>Execution Duration: </b>{{ $executionDuration }}
<p>&nbsp;</p>
<p><span style="color: #d9534f!important">Note:</span> This is an automated email, don't reply to this but contact admin if you have questions.</p>
<p>&nbsp;</p>
