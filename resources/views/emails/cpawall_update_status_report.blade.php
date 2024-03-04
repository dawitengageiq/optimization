Dear Admin, <br><br>

Below are the link out campaigns whose status were changed based on the cap set in Cake:<br><br>

<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <thead>
        <tr>
            <th style="border:1px solid black;padding: 5px; text-align: center">ID</th>
            <th style="border:1px solid black;padding: 5px; text-align: center">Campaign Name</th>
            <th style="border:1px solid black;padding: 5px; text-align: center">Cake Offer ID</th>
            <th style="border:1px solid black;padding: 5px; text-align: center">Old Status</th>
            <th style="border:1px solid black;padding: 5px; text-align: center">NEW Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $row) 
            <tr>
                <td style="border:1px solid black; text-align: center">{{ $row['id'] }}</td>
                <td style="border:1px solid black; text-align: center">{{ $row['name'] }}</td>
                <td style="border:1px solid black; text-align: center">{{ $row['offer'] }}</td>
                <td style="border:1px solid black; text-align: center">{{ $statuses[$row['status']] }}</td>
                <td style="border:1px solid black; text-align: center">{{ $statuses[$row['new_status']] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>