<style>
	thead tr th{
	  background-color: #203764;
	  color: #fceb22;
	  text-align: center;
	}

	thead tr td {
		background-color: #c9c9c9;
		text-align: center;
	}

	tbody tr {
		text-align: center;
	}
</style>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<table>
			<thead>
				<tr>
					<th rowspan="3" valign="middle">Date</th>
					<th rowspan="3" valign="middle">Name</th>
		            <th rowspan="3" valign="middle">Rev Tracker [Publisher]</th>
		            <th rowspan="3" valign="middle">S1</th>
		            <th rowspan="3" valign="middle">S2</th>
		            <th rowspan="3" valign="middle">S3</th>
		            <th rowspan="3" valign="middle">S4</th>
		            <th rowspan="3" valign="middle">S5</th>
		        @foreach($activeTypes as $type)
		          	<th colspan="{!! in_array($type,$no_leads) ? 3 : 5 !!}">
		          		{!! $type == 'external_combine' ? 'External Path Combine' : $campaign_types[$type] !!}
		          	</th>
		        @endforeach
		        </tr>
		        <tr>
		        	<th></th>
		        	<th></th>
		        	<th></th>
		        	<th></th>
		        	<th></th>
		        	<th></th>
		        	<th></th>
		        	<th></th>
		        <?php 
		        	$external_campaign_names = array_values($external_campaigns);
		        	$counter = 0;
		        ?>
		        @foreach($activeTypes as $type)
		          	<th colspan="{!! in_array($type,$no_leads) ? 3 : 5 !!}">
		          		<?php 
		          			if($type == 4) {
		          				/*$campaign = isset($external_campaigns[$benchmarks[$type]]) ? $external_campaigns[$benchmarks[$type]] : '';*/
		          				echo $campaign = $external_campaign_names[$counter++];
		          			}else if($type == 'external_combine') {
		          				$campaign = '';
		          				foreach($benchmarks[4] as $ex_id) {
		          					if($ex_id != 'all') {
		          						$campaign .= $external_campaigns[$ex_id]."\r\n<br />";
		          					}
		          				}
		          				echo $campaign;
		          			}else {
		          				// $campaign = isset($benchmarks[$type]) ? $campaigns[$benchmarks[$type]] : '';
		          				if(in_array('all', $benchmarks[$type])) {
			          				echo 'All';
			          			}else {
			          				foreach($benchmarks[$type] as $bm) {
			          					echo $campaigns[$bm]."\r\n<br />";
			          				}
			          			}	
		          			}
		          		?>
		          	</th>
		        @endforeach
		        </tr>
		        <tr>
		        	<td></td>
		        	<td></td>
		        	<td></td>
		        	<td></td>
		        	<td></td>
		        	<td></td>
		        	<td></td>
		        	<td></td>
		        @foreach($activeTypes as $type)
		        	@if(in_array($type, $no_leads)) 
		        		<td>Revenue</td>
		        	@else
		        		<td>Success</td>
			          	<td>Rejects</td>
			          	<td>Failed</td>
		        	@endif
		        		<td>Views</td>
			          	<td>OptIn Rate</td>
		        @endforeach
		        </tr>
			</thead>
			<tbody>
				@foreach($statsData as $stat) 
					<tr>
						@foreach($stat as $value)
							<td>{!! $value !!}</td>
						@endforeach
					</tr>
				@endforeach
			</tbody>
	    </table>
    </body>
</html>