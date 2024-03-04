<?php
/**
 * Don't use the extension ".blade" here for the eval() fuction conflicts with blade.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">

        <link href="http://path2.paidforresearch.com/assets/css/api/style.min.css" type="text/css" rel="stylesheet">

        <?php if (! is_array($data)) { ?>
        <link href="http://path2.paidforresearch.com/assets/css/api/mobile.min.css" type="text/css" rel="stylesheet">
        <link href="http://path2.paidforresearch.com/assets/css/api/opt-in.min.css" type="text/css" rel="stylesheet">
        <link href="http://path2.paidforresearch.com/assets/css/api/stack.min.css" type="text/css" rel="stylesheet">
        <?php } ?>
        <style>
        body {
            text-align: center;
        }
        #skip {
            color: #2fa4e7;
            cursor: pointer;
            display: block;
            margin: 30px 0px 10px;
            text-align: center;
        }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <?php if (! is_array($data)) { ?>
        <script src="http://path2.paidforresearch.com/assets/js/api/jquery.validate.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/jquery.autotab.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/additional-methods.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/jquery.maskedinput.min.js"></script>
        <?php } ?>
    </head>
    <body>
    <?php
        if ($redirect_url) {
            echo '<input type="hidden" name="redirect_url" id="redirect_url" value="'.$redirect_url.'" />'."\n\r";
        }
        if ($reload_parent_frame) {
            echo '<input type="hidden" name="reload_parent_frame" id="reload_parent_frame" value="'.$reload_parent_frame.'" />'."\n\r";
        }
        if (is_array($data) && array_key_exists('message', $data)) {
            echo '<h3>'.$data['message'].'</h3>';
            if ($redirect_url) {
                echo '<a id="skip">Skip</a>';
            }
        } else {
            $age = date_diff(date_create($user_details['birthdate']), date_create('today'))->y;

            echo '<input type="hidden" name="user_phone" id="user_phone" value="'.$user_details['phone1'].$user_details['phone2'].$user_details['phone3'].'" />'."\n\r";
            echo '<input type="hidden" name="user_address" id="user_address" value="'.$user_details['address'].'" />'."\n\r";
            echo '<input type="hidden" name="error_validation_counter" id="error_validation_counter" value="0" />'."\n\r";
            eval('?>'.$data);
        }
?>

    <?php if (! is_array($data)) { ?>
        <script src="http://path2.paidforresearch.com/assets/js/api/campaigns_custom_script.js?t<?php echo time(); ?>"></script>
        <script src="<?php echo url(); ?>/js/api/iframeResizer.contentWindow.min.js"></script>
        <iframe src="http://engageiq.nlrtrk.com/?a=<?php echo $user_details['affiliate_id']; ?>&c=818&p=c&s1=<?php echo $user_details['cs1']; ?>&s2=<?php echo $user_details['cs2']; ?>&s3=<?php echo $user_details['cs3']; ?>&s4=<?php echo $user_details['cs4']; ?>&s5=<?php echo $user_details['cs5']; ?>" height="1" width="1" frameborder="0"></iframe>
    <?php } ?>
        <script>
            $(document)
        	/**
        	 * Skip to next page
        	 *
        	 *  @var form - will be used after
        	 */
        	.on('click','#skip', function()
            {
            	console.log('setNextStackSet');
            	var redUrl = $('#redirect_url').val();
            	console.log(redUrl);
            	if(redUrl) window.top.location.href = redUrl;
        	})
        </script>
    </body>
</html>
