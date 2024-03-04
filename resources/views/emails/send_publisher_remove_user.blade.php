Dear {{$campaign['advertiser']['company']}},
<br><br>

EngageIQ strives to be compliant with all of its data privacy obligations.  Accordingly, when we receive requests from consumers exercising their rights under certain data privacy laws, we have an obligation to notify our partners of such consumer requests if we have sold or otherwise transmitted these consumers’ information to our partners.  <br><br>

Certain individuals identified in this email have requested that their personal information be deleted.  We have complied with their requests and it is our opinion that our obligations under the GDPR and CCPA require us to notify our “downstream” partners and ask you to comply with these consumers’ requests to be deleted from your systems. <br><br>

Other individuals identified in this email have requested that EngageIQ no longer sell their information.  We have complied with their request.  While not required under the CCPA, but as a courtesy to our web site visitors, we choose to also convey to you that these individual no longer wish their information to be sold to third parties. <br><br>

If you have questions about this email or any other questions regarding this request please contact us at: <a href="mailto:legal@engageiq.com">legal@engageiq.com</a> <br><br>

@if($campaign)
Campaign : <b>{{$campaign['name']}}</b><br><br>
@endif

<table class="count-table" style="border-collapse:collapse; border:1px solid black; padding: 5px" cellspacing="0">
    <thead>
        <tr>
            <th style="padding: 5px;border:1px solid black;">Name</th>
            <th style="padding: 5px;border:1px solid black;">Email</th>
            <th style="padding: 5px;border:1px solid black;">Phone</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $user['first_name'].' '.$user['last_name'] }}</td>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $user['email'] }}</td>
                <td style="border:1px solid black; text-align:center; padding: 3px">{{ $user['phone_number'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>