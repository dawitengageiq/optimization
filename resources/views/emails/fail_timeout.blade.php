<?php
    $totalFailCount = 0;
    $totalTimeoutCount = 0;
?>

<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <caption align="left" style="text-align:left; margin-bottom: 5px">Time Checked: {{ $dateNow }}</caption>
    <thead>
        <tr>
            <th style="padding: 5px">Campaign ID</th>
            <th style="padding: 5px">Campaign Name</th>
            <th style="padding: 5px">Fail Count</th>
            <th style="padding: 5px">Timeout Count</th>
        </tr>
    </thead>
    <tbody>
        @foreach($campaigns as $campaign)

            <?php
                if($campaign->fail_count==null)
                {
                    $campaign->fail_count = 0;
                }

                if($campaign->timeout_count==null)
                {
                    $campaign->timeout_count = 0;
                }
            ?>

            <tr>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $campaign->id }}</td>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $campaign->name }}</td>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $campaign->fail_count }}</td>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $campaign->timeout_count }}</td>
            </tr>

            <?php
                $totalFailCount = $totalFailCount + $campaign->fail_count;
                $totalTimeoutCount = $totalTimeoutCount + $campaign->timeout_count;
            ?>

        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" align="right" style="padding: 5px">Grand Total</td>
            <td style="border:1px solid black; text-align:center; padding: 5px">{{ $totalFailCount }}</td>
            <td style="border:1px solid black; text-align:center; padding: 5px">{{ $totalTimeoutCount }}</td>
        </tr>
    </tfoot>
</table>