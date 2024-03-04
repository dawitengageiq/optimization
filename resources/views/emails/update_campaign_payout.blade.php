<b>Report Date: </b>{{ $date }}<br><br>

@if(count($notes['coreg']) > 0)
<b>Coreg Breakdown</b>
<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <thead>
        <tr>
            <th style="border:1px solid black; text-align:center; padding: 5px">#</th>
            <th style="border:1px solid black; text-align:center; padding: 5px">Campaign ID</th>
            <th style="border:1px solid black; text-align:center; padding: 5px">New Payout</th>
            <th style="border:1px solid black; text-align:center; padding: 5px">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($notes['coreg'] as $c => $error)
            <tr>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $c+1 }}</td>
                <td style="border:1px solid black">{{ $error['campaign_id'] }}</td>
                <td style="border:1px solid black">{{ $error['payout'] }}</td>
                <td style="border:1px solid black">{{ $error['nlr_status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(count($notes['cpa']) > 0)
<b>CPA Breakdown</b>
<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <thead>
        <tr>
            <th style="border:1px solid black; text-align:center; padding: 5px">#</th>
            <th style="border:1px solid black; text-align:center; padding: 5px">Offer ID</th>
            <th style="border:1px solid black; text-align:center; padding: 5px">New Received</th>
            <th style="border:1px solid black; text-align:center; padding: 5px">Cake Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($notes['cpa'] as $c => $error)
            <tr>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $c+1 }}</td>
                <td style="border:1px solid black">{{ $error['offer_id'] }}</td>
                <td style="border:1px solid black">{{ $error['received'] }}</td>
                <td style="border:1px solid black">{{ $error['curl_status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif