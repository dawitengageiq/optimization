<b>Report Date: </b>{{ $startDate }}<br><br>
<b>Execution Duration: </b>{{ $executionDuration }}<br>

@if(isset($errors) && count($errors) > 0)
<b>Error Breakdown</b>
<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <thead>
        <tr>
            <th style="padding: 5px">#</th>
            <th style="padding: 5px">Area</th>
            <th style="padding: 5px">Info</th>
            <th style="padding: 5px">Message</th>
        </tr>
    </thead>
    <tbody>
        @foreach($errors as $c => $error)
            <tr>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $c+1 }}</td>
                <td style="border:1px solid black">{{ $error['Area'] }}</td>
                <td style="border:1px solid black">{{ $error['Info'] }}</td>
                <td style="border:1px solid black">{{ $error['MSG'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(isset($time_log) && count($time_log) > 0)
<b>Time Breakdown</b>
<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <thead>
        <tr>
            <th style="padding: 5px">#</th>
            <th style="padding: 5px">Name</th>
            <th style="padding: 5px">Duration</th>
        </tr>
    </thead>
    <tbody>
        @foreach($time_log as $c => $log)
            <tr>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $c+1 }}</td>
                <td style="border:1px solid black">{{ $log['name'] }}</td>
                <td style="border:1px solid black">{{ $log['duration'] }} minute/s</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif