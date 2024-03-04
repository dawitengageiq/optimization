<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">

        <link href="http://path2.paidforresearch.com/assets/css/api/mobile.min.css" type="text/css" rel="stylesheet">
        <link href="http://path2.paidforresearch.com/assets/css/api/opt-in.min.css" type="text/css" rel="stylesheet">
        <link href="http://path2.paidforresearch.com/assets/css/api/stack.min.css" type="text/css" rel="stylesheet">
        <link href="http://path2.paidforresearch.com/assets/css/api/style.min.css" type="text/css" rel="stylesheet">
        <link href="http://path2.paidforresearch.com/assets/css/api/waitme.min.css" type="text/css" rel="stylesheet">

        <style>
            body {
                text-align: center;
            }
            .button-btn-skip {
                display: none;
            }
            .button-btn-skip span {
                color: #03AEEF;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
    	<input type="text" name="current_campaign_set" id="current_campaign_set" value="" style="height: 0; padding: 0; margin: 0; border: none;" readonly>
    	<input type="hidden" name="user_phone" id="user_phone" value="" />
    	<input type="hidden" name="user_address" id="user_address" value="" />

        <input type="hidden" name="redirect_url" id="redirect_url" value="{!! SurveyStack::redirectUrl() !!}" />
        <input type="hidden" name="reload_parent_frame" id="reload_parent_frame" value="{!! SurveyStack::reloadParentFrame() !!}" />

    	<div id="main-contentbox" style="min-height: 165px;padding-top: 2%; background: transparent;">
    		<div id="contentbox" style="background: transparent;">
    			<ul style="padding: 0;">
    			</ul>
    		</div>
    		<div class="button-btn-submit" style="text-align: center; margin-top: 20px;"></div>
    	</div>
        <div class="button-btn-skip" style="text-align: center; margin-top: 20px;"></div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/jquery.validate.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/jquery.autotab.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/waitme.min.js"></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/additional-methods.min.js" async></script>
        <script src="http://path2.paidforresearch.com/assets/js/api/jquery.maskedinput.min.js" async></script>
        <script>
            var path_id = {!! SurveyStack::pathID() !!};
            var campaign_links = {!! json_encode(SurveyStack::queryString()) !!};
            var user_details = {!! json_encode(SurveyStack::userDetails()) !!};
            var target_url = '{!! SurveyStack::targetUrl(true) !!}';
        </script>
        <script src="{!! SurveyStack::iframeContentJS() !!}"></script>
        <script src="{!! SurveyStack::targetUrl() !!}js/api/iframeResizer.contentWindow.min.js" async></script>
        <iframe src="http://engageiq.nlrtrk.com/?a={!! SurveyStack::userDetails()['affiliate_id'] !!}&c=818&p=c&s1={!! SurveyStack::userDetails()['cs1'] !!}&s2={!! SurveyStack::userDetails()['cs2'] !!}&s3={!! SurveyStack::userDetails()['cs3'] !!}&s4={!! SurveyStack::userDetails()['cs4'] !!}&s5={!! SurveyStack::userDetails()['cs5'] !!}" height="1" width="1" frameborder="0"></iframe>
    </body>
</html>
