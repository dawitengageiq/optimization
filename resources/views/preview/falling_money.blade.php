<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="lrUrl" content="http://localhost:1234/" />
		<!-- <meta name="lrUrl" content="http://leadreactor.engageiq.com/" /> -->
		<title>Welcome to Paid For Research</title>
		<!-- Custom CSS -->
		<link href="{{ URL::asset('css/falling-money/style.css') }}" rel="stylesheet">
		<link href="{{ URL::asset('css/falling-money/mobile.css') }}" rel="stylesheet">
		<link href="{{ URL::asset('css/falling-money/opt-in.css') }}" rel="stylesheet">
	</head>
	<body>   
  		<div class="wrapper"> <!--BEGIN Div wrapper-->
		    <div id="headline1"></div>
		    <div id="headline2">
		    	<img src="{{ URL::asset('images/preview/1ststep.png') }}">
		    	<img src="{{ URL::asset('images/preview/100guaranteed.png') }}" style="padding-right:10px">
		    	<img src="{{ URL::asset('images/preview/100secure.png') }}" >
		    	<img src="{{ URL::asset('images/preview/arrow.png') }}">
		    	<img src="{{ URL::asset('images/preview/bbb_acredited.png') }}">
		    </div>

			<?php 
				$cur_bar = 1;
				$bar_num = 10;
			?>
			<div id="progress_bar_box">
				<div class="the-progress"></div>
				<div id="progress_bar_holder">
					<table id="progress_bar_table" width="100%" border="0" cellpadding="0" cellspacing="1">
						<tr id="progress_bar_row">
							<?php 
							for($lop=1;$lop<=$bar_num;$lop+=1)
							{
								if ($lop<=$cur_bar) 
								{ echo '<td class="cell_shade" align="center">&nbsp;'.'</td>'; }
							  	else
							  	{ echo '<td class="cell_noshade" align="center">&nbsp;'.'</td>'; }
							 }		
							?>
						</tr>
						<tr>
							<td id="progress_bar_row_label" align="center" style="height: 26px;" colspan="<?= $bar_num ?>">
								<strong id="progress_bar_current_number"><?= $cur_bar?></strong>
								<strong><?=' of '.$bar_num ?></strong> Question(s) to go
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div style="height:10px;"></div>
			<div id="contentbox">   
			    
			</div>
			<br />
		    <div style="clear:both"></div>    
		    <div id="footer1">
		      <div style="clear:both"></div>
		    </div>
  		</div>
  		<span id="base_url" hidden>{{url()}}</span>
		<script src="{{ URL::asset('js/falling-money/jquery-1.11.1.js') }}"></script>
		<script src="{{ URL::asset('js/falling-money/jquery.validate.min.js') }}"></script>
		<script src="{{ URL::asset('js/falling-money/additional-methods.min.js') }}"></script>
		<script src="{{ URL::asset('js/falling-money/jquery.autotab.min.js') }}"></script>
		<script src="{{ URL::asset('js/falling-money/custom.js') }}"></script>
		
		<script>
			$(document).ready(function() {
				$.ajax({
					url: $('#base_url').html()+'/get_preview_campaign',
					type: 'GET',
					success: function(content) {
						console.log(content);
						$('#contentbox').html(content);
					}
				});
			});
		</script>   	
	</body>
</html>