Dear Publisher, <br><br>

Below are your daily stats for the month of {{$date}}.<br><br>

@foreach($items as $website_id => $rows)
    <b>{!! $websites[$website_id] !!}</b>
    <table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
        <thead>
            <tr>
                <th style="border:1px solid black;padding: 5px; text-align: center">Date</th>
                <th style="border:1px solid black;padding: 5px; text-align: center">Unique Email Count</th>
                <th style="border:1px solid black;padding: 5px; text-align: center">Rate</th>
                <th style="border:1px solid black;padding: 5px; text-align: center">Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $lead_count = 0;
                $revenue = 0;
            ?>
            @foreach($rows as $row) 
                <?php
                    $lead_count += $row->count;
                    $revenue += $row->payout;
                ?>
                <tr>
                    <td style="border:1px solid black; text-align: center">{{ $row->date }}</td>
                    <td style="border:1px solid black; text-align: center">{{ $row->count }}</td>
                    <?php
                        $rate = $payouts[$website_id] * 1000;
                    ?>
                    <td style="border:1px solid black; text-align: center">{{ $rate }} CPM</td>
                    <td style="border:1px solid black; text-align: center">{{ sprintf("%.2f",$row->payout) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="border:1px solid black; text-align: center;font-weight: 800">MTD</td>
                <td style="border:1px solid black; text-align: center;font-weight: 800">{{ $lead_count }}</td>
                <td style="border:1px solid black; text-align: center"></td>
                <td style="border:1px solid black; text-align: center;font-weight: 800">{{ sprintf("%.2f",$revenue) }}</td>
            </tr>
        </tbody>
    </table>
    <br><br>
@endforeach
