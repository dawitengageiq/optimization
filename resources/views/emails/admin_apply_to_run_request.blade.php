Hello {{ $admin->first_name }}! <br>
This is to notify you that affiliate {{ $affiliate->id.' - '.$affiliate->company }} 
wants to run our campaign, {{ $campaign->name }} <br>
Click <a href="{{ url('admin/affiliate_requests') }}">here</a> to go to lead reactor.