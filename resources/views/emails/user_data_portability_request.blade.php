Dear {{$request->first_name}},<br><br>

On {{Carbon::parse($request->request_date)->format('l jS \\of F Y h:i:s A')}}, you made a data portability request on our web site.  After verifying your identity by matching the information you provided on our web form against our records, we have attached a Comma Separated File (.csv) file with the personal data about you contained on our web site.  This file was designed to comply with the data portability requirements of the California Consumer Privacy Act.<br><br>

If you have questions about this email, the contents of the file, or any other questions regarding your request please contact us at: [<a href="mailto:privacycomments@epicdemand.com">privacycomments@epicdemand.com</a>].<br><br>

Sincerely,<br>

The epicdemand.com Team