<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="EngageIQ - Lead Reactor">
    <meta name="_token" content="{{ csrf_token() }}" />
    <title>Engage IQ - Lead Reactor</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ URL::asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
    	table, th, td {
    		border: 2px solid #4F81BD !important;
    		font-weight: 800;
    	}
    	th {
    		text-align: center;
    		color: #2F5B90;
    	}
    	tr.group {
    		background-color: #D3DFEE;
    	}
    	hr {
    		border-top: 1px solid #b9b5b5
    	}
    	.xml-code {
    		color: #22863a;
    	}

    </style>
</head>

<body class="jumbotron">
	<div class="row">
		<div class="col-md-8" style="float: none;margin: 0 auto;display: block;">
			<h3><b>EIQ HTTP Request Documentation</b></h3>
			<hr>
			<p>Request URL</p>
			<ul>
			  <li>Send the request to ex:<em>http://example.com/awesome.php</em></li>
			</ul>
			<p>Required Parameters</p>
			<ul>
			  <li><b>base_url</b> - Base URL of the advertiser and should be url encoded when loaded as parameter value.</li>
			  <li><b>request_method</b> - Either POST or GET depending on the requirements of the advertiser.</li>
			  <li><b>body_type </b> - Either json or xml depending on the requirements of the advertiser.</li>
			  <li><b>xml_body_template </b> - This parameter must be used if body_type is xml. This template must be urlencoded json format and should indicate LEAD_DATA_HERE placeholder which will be replaced by actual lead data.</li>
			</ul>

<div style="margin-left: 40px;">
			Example:<br>
			(Actual XML Payload Template required by the advertiser.)
			<br>	
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">
&lt;? xml version='1.0' encoding='UTF-8' ?&gt;
&lt;homelight-partner-lead-request&gt;
	&lt;secret&gt;the_secret&lt;/secret&gt;
	&lt;token&gt;the_token&lt;/token&gt;
	&lt;leads type='array'&gt;
		&lt;lead&gt;
			&lt;city&gt;Santa Clara&lt;/city&gt;
			&lt;email&gt;test@google.com&lt;/email&gt;
			&lt;name&gt;John Doe&lt;/name&gt;
			&lt;phone&gt;7897897898&lt;/phone&gt;
			&lt;price&gt;10&lt;/price&gt;
			&lt;property-type&gt;condo&lt;/property-type&gt;
			&lt;user-type&gt;seller&lt;/user-type&gt;
			&lt;address&gt;address&lt;/address&gt;
			&lt;timeline&gt;timeline&lt;/timeline&gt;
		&lt;/lead&gt;
	&lt;/leads&gt;
&lt;/homelight-partner-lead-request&gt;</pre>

			(Payload value in JSON.)
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">
{
	&quot;homelight-partner-lead-request&quot;: {
		&quot;secret&quot;: &quot;the_secret&quot;,
		&quot;token&quot;: &quot;the_token&quot;,
		&quot;leads&quot;: {
			&quot;_attributes&quot;: {
				&quot;type&quot;: &quot;array&quot;
			},
			&quot;lead&quot;: &quot;LEAD_DATA_HERE&quot;
		}
	}
}</pre>

			(Payload value in JSON after urlencode.)
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">
%7B%0D%0A%09%22homelight-partner-lead-request%22%3A+%7B%0D%0A%09%09%22secret%22%3A+%22the_secret%22%2C%0D%0A%09%09%22token%22%3A+%22the_token%22%2C%0D%0A%09%09%22leads%22%3A+%7B%0D%0A%09%09%09%22_attributes%22%3A+%7B%0D%0A%09%09%09%09%22type%22%3A+%22array%22%0D%0A%09%09%09%7D%2C%0D%0A%09%09%09%22lead%22%3A+%22LEAD_DATA_HERE%22%0D%0A%09%09%7D%0D%0A%09%7D%0D%0A%7D</pre>
</div>
			
			<ul>
			  <li><b>json_body_template</b> -  This parameter must be used if <b>body_type</b> is json. This template must be urlencoded json format and should indicate <b>LEAD_DATA_HERE</b> placeholder which will be replaced by actual lead data.</li>
			</ul>

<div style="margin-left: 40px;">
			Example:<br>
			(Actual JSON Payload Template required by the advertiser. This will be the payload itself.)<br>
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">
{
    "Client":"LEAD_DATA_HERE"
}</pre>
			(Payload value in JSON after urlencode.)
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">%7B%0D%0A%09%22Client%22%3A%22LEAD_DATA_HERE%22%0D%0A%7D</pre>
</div>
			<p>Optional Parameters</p>
			<ul>
			  <li><b>eiq_header_map </b> -  Contains map of headers required by the advertiser in json format.</li>
			</ul>
<div style="margin-left: 40px;">
			Example required headers:<br>
			AuthenticationKey => $eiq_$2a$12$rQAlpvAdfTIUnLioTB5NNuXfmgrsA7BlkuolSl/ZMHFzkUL1644bS<br> 
			API_key => eiq_asjdfjlhagdgfa;lsdkfkk--$asfjsjf <br>
			agent => eiq<br>
			<br>
			(JSON format parameter value)<br>
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">
{
	"AuthenticationKey":"$2a$12$rQAlpvAdfTIUnLioTB5NNuXfmgrsA7BlkuolSl/ZMHFzkUL1644bS",
	"API_key":"eiq_asjdfjlhagdgfa;lsdkfkk--$asfjsjf",
	"agent":"eiq"
}</pre>
			Example required headers:<br>
			<pre lang="xml" style="color: #c7254e;background-color: #f9f2f4;">
%7B%0D%0A%09%22AuthenticationKey%22%3A%22%242a%2412%24rQAlpvAdfTIUnLioTB5NNuXfmgrsA7BlkuolSl%2FZMHFzkUL1644bS%22%2C%0D%0A%09%22API_key%22%3A%22eiq_asjdfjlhagdgfa%3Blsdkfkk--%24asfjsjf%22%2C%0D%0A%09%22agent%22%3A%22eiq%22%0D%0A%7D</pre>
</div>
			<p>Lead Data</p>
			<ul>
			  <li>Lead data should be loaded using traditional parameter name and value pair.</li>
			</ul>
		</div>
	</div>

    <!-- jQuery -->
    <script src="{{ URL::asset('bower_components/jquery/dist/jquery.min.js') }}"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="{{ URL::asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</body>

</html>