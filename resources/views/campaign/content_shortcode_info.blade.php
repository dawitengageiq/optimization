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

    </style>
</head>

<body>

	<table class="table table-bordered">
		<tr>
			<th>Short Code</th>
			<th>Description</th>
			<th>Format</th>
			<th>Example Value</th>
		</tr>
		<tr class="group">
			<th colspan="4">Campaign Content Creative</th>
		</tr>
		<tr>
			<th>[VALUE_CREATIVE_ID]</th>
			<td>Campaign's creative id</td>
			<td></td>
			<td>1</td>
		</tr>
		<tr>
			<th>[VALUE_CREATIVE_DESCRIPTION]</th>
			<td>Campaign's creative description</td>
			<td></td>
			<td>{{'<b>Be one of the lucky testers to keep the test products from Toluna!</b>'}} </td>
		</tr>
		<tr>
			<th>[VALUE_CREATIVE_IMAGE]</th>
			<td>Campaign's creative image</td>
			<td></td>
			<td><a target="_blank" href="http://leadreactor.engageiq.com/images/gallery/sticker-badge-survey-dollars.png">http://leadreactor.engageiq.com/images/gallery/sticker-badge-survey-dollars.png</a></td>
		</tr>
		<tr class="group">
			<th colspan="4">Survey Path</th>
		</tr>
		<tr>
			<th>[VALUE_PATH_ID]</th>
			<td>Survey Path ID</td>
			<td></td>
			<td>1</td>
		</tr>
		<tr class="group">
			<th colspan="4">User's Personal Information</th>
		</tr>
		<tr>
			<th>[VALUE_AGE]</th>
			<td>user's age</td>
			<td></td>
			<td>18</td>
		</tr>
		<tr>
			<th>[VALUE_BIRTHDATE]</th>
			<td>user's birthdate</td>
			<td>yyyy-mm-dd</td>
			<td>1990-01-30</td>
		</tr>
		<tr>
			<th>[VALUE_BIRTHDATE_MDY]</th>
			<td>user's birthdate</td>
			<td>mm-dd-yyyy</td>
			<td>01-30-1990</td>
		</tr>
		<tr>
			<th>[VALUE_DOBDAY]</th>
			<td>user's birth day</td>
			<td>dd</td>
			<td>30</td>
		</tr>
		<tr>
			<th>[VALUE_DOBMONTH]</th>
			<td>user's birth month</td>
			<td>mm</td>
			<td>01</td>
		</tr>
		<tr>
			<th>[VALUE_DOBYEAR]</th>
			<td>user's birth year</td>
			<td>yyyy</td>
			<td>1990</td>
		</tr>
		<tr>
			<th>[VALUE_ETHNICITY]</th>
			<td>user's ethnicity</td>
			<td></td>
			<td>Caucasian, Asian, etc.</td>
		</tr>
		<tr>
			<th>[VALUE_FIRST_NAME]</th>
			<td>user's first name</td>
			<td></td>
			<td>John</td>
		</tr>
		<tr>
			<th>[VALUE_LAST_NAME]</th>
			<td>user's last name</td>
			<td></td>
			<td>Doe</td>
		</tr>
		<tr>
			<th>[VALUE_GENDER]</th>
			<td>user's gender acronym</td>
			<td></td>
			<td>F, M</td>
		</tr>
		<tr>
			<th>[VALUE_GENDER_FULL]</th>
			<td>user's gender</td>
			<td></td>
			<td>Female, Male</td>
		</tr>
		<tr>
			<th>[VALUE_TITLE]</th>
			<td>user's title/honorific</td>
			<td></td>
			<td>Mr, Mrs, etc.</td>
		</tr>
		<tr class="group">
			<th colspan="4">User's Address Information</th>
		</tr>
		<tr>
			<th>[VALUE_EMAIL]</th>
			<td>user's email address</td>
			<td></td>
			<td>sample@email.com</td>
		</tr>
		<tr>
			<th>[VALUE_IP]</th>
			<td>user's ip address</td>
			<td></td>
			<td>184.154.155.59</td>
		</tr>
		<tr>
			<th>[VALUE_ADDRESS1]</th>
			<td>user's address</td>
			<td></td>
			<td>1600 Pennyslvania Avenue NW</td>
		</tr>
		<tr>
			<th>[VALUE_CITY]</th>
			<td>user's city</td>
			<td></td>
			<td>Washington</td>
		</tr>
		<tr>
			<th>[VALUE_STATE]</th>
			<td>user's state</td>
			<td></td>
			<td>DC</td>
		</tr>
		<tr>
			<th>[VALUE_ZIP]</th>
			<td>user's zip</td>
			<td></td>
			<td>20500</td>
		</tr>
		<tr>
			<th>[VALUE_PHONE]</th>
			<td>user's complete phone number</td>
			<td></td>
			<td>2024456213,(202) 445 6213, etc.</td>
		</tr>
		<tr>
			<th>[VALUE_PHONE1]</th>
			<td>user's first 3 digit of phone or numering plan area code</td>
			<td></td>
			<td>202</td>
		</tr>
		<tr>
			<th>[VALUE_PHONE2]</th>
			<td>user's second 3 digit of phone or central office exchange code</td>
			<td></td>
			<td>445</td>
		</tr>
		<tr>
			<th>[VALUE_PHONE3]</th>
			<td>user's last 4 digit of phone or subscriber number</td>
			<td></td>
			<td>6213</td>
		</tr>
		<tr class="group">
			<th colspan="4">Dates</th>
		</tr>
		<tr>
			<th>[VALUE_DATE_TIME]</th>
			<td>current date and time</td>
			<td>mm/dd/yyyy hh:mm:ss</td>
			<td>
				Jan. 10, 1990 01:30:59 PM 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				01/10/1990 13:30:00
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY]</th>
			<td>current date</td>
			<td>mm/dd/yyyy</td>
			<td>
				Jan. 10, 1990 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				01/10/1990
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY_MONTH]</th>
			<td>current month</td>
			<td>mm</td>
			<td>
				Jan. 10, 1990 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				01
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY_DAY]</th>
			<td>current day</td>
			<td>dd</td>
			<td>
				Jan. 10, 1990 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				10
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY_YEAR]</th>
			<td>current year</td>
			<td>yyyy</td>
			<td>
				Jan. 10, 1990 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				1990
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY_HOUR]</th>
			<td>current hour</td>
			<td>hh</td>
			<td>
				01:30:59 PM 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				13
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY_MIN]</th>
			<td>current minute</td>
			<td>mm</td>
			<td>
				01:30:59 PM 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				30
			</td>
		</tr>
		<tr>
			<th>[VALUE_TODAY_SEC]</th>
			<td>current second</td>
			<td>ss</td>
			<td>
				01:30:59 PM 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				59
			</td>
		</tr>
		<tr>
			<th>[VALUE_PUB_TIME]</th>
			<td>current date and time</td>
			<td>yyyy-mm-dd hh:mm:ss</td>
			<td>
				Jan. 10, 1990 01:30:59 PM 
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				1990-01-10 13:30:00
			</td>
		</tr>
		<tr class="group">
			<th colspan="4">URLs</th>
		</tr>
		<tr>
			<th>[VALUE_URL_REDIRECT_PAGE]</th>
			<td>returns next long path campaigns's survey url</td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<th>[VALUE_URL_REDIRECT_STACK_PAGE]</th>
			<td>returns next stack path campaigns's survey url</td>
			<td></td>
			<td></td>
		</tr>
		<tr class="group">
			<th colspan="4">IDs</th>
		</tr>
		<tr>
			<th>[VALUE_REV_TRACKER]</th>
			<td>user's revenue tracker id</td>
			<td></td>
			<td>CD1</td>
		</tr>
		<tr>
			<th>[VALUE_AFFILIATE_ID]</th>
			<td>user's affiliate id</td>
			<td></td>
			<td>1</td>
		</tr>
		<tr class="group">
			<th colspan="4">OS, Browser & Device Detection</th>
		</tr>
		<tr>
			<th>[DETECT_OS]</th>
			<td>user's operating system</td>
			<td></td>
			<td>Windows</td>
		</tr>
		<tr>
			<th>[DETECT_OS_VER]</th>
			<td>user's operating system version</td>
			<td></td>
			<td>Windows 10</td>
		</tr>
		<tr>
			<th>[DETECT_BROWSER]</th>
			<td>user's browser</td>
			<td></td>
			<td>Chrome</td>
		</tr>
		<tr>
			<th>[DETECT_BROWSER_VER]</th>
			<td>user's browser version</td>
			<td></td>
			<td>58.0.3029.96</td>
		</tr>
		<tr>
			<th>[DETECT_USER_AGENT]</th>
			<td>user's user agent</td>
			<td></td>
			<td>Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36</td>
		</tr>
		<tr>
			<th>[DETECT_DEVICE]</th>
			<td>device being used by user</td>
			<td></td>
			<td>Desktop, Mobile, Tablet</td>
		</tr>
		<tr>
			<th>[DETECT_ISMOBILE]</th>
			<td>returns 1 if user is using a mobile device</td>
			<td></td>
			<td>1</td>
		</tr>
		<tr>
			<th>[DETECT_ISTABLET]</th>
			<td>returns 1 if user is using a tablet device</td>
			<td></td>
			<td>1</td>
		</tr>
		<tr>
			<th>[DETECT_ISDESKTOP]</th>
			<td>returns 1 if user is using a desktop device</td>
			<td></td>
			<td>1</td>
		</tr>
	</table>

    <!-- jQuery -->
    <script src="{{ URL::asset('bower_components/jquery/dist/jquery.min.js') }}"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="{{ URL::asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</body>

</html>