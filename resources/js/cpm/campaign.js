! (function(w)
{
    var container = 'body';
    var params = {
        'affiliate_id': '',
        's1' : '',
        's2' : '',
        's3' : '',
        's4' : '',
        's5' : '',
        'email' : '',
        'first_name' : '',
        'last_name' : '',
        'dob_month' : '',
        'dob_day' : '',
        'dob_year' : '',
        'zip' : '',
        'gender' : '',
        'address' : '',
        'phone' : '',
        'redirect_url' : '',
    };

    var campaigns = [];

    var creatives = [];

    var campaign_id = '';
    var creative_id = '';

    var url = '//path17.epicdemand.com/embed';

    var redirect_url = '';

    var setParams = function($data){
        // params  = $data;
        for (var key in $data) {
            if(key in params) {
                params[key] = $data[key]
            }

            if(key == 'redirect_url') redirect_url = $data[key];
            if(key == 'campaign_id') campaign_id = $data[key];
            if(key == 'creative_id') creative_id = $data[key];
        }
    };

    var setContainer = function($container){
        container  = $container == '' || typeof $container == 'undefined' ? container : $container;
    };

    var setURL = function($url){
        url  = $url == '' || typeof $url == 'undefined' ? url : $url;
    };

    var setCampaigns = function($campaigns){
        campaigns  = $campaigns == '' || typeof $campaigns == 'undefined' ? campaigns : $campaigns;
    };

    var this_callback = function() {
    };

    /**
    * Will be extended as prototype
    */
    var campaign_handler = function ()
    {
        this.construct();
    }
    
    /**
    * Extend, public methods
    */
    campaign_handler.prototype = {
        construct: function(){
        },
        init: function($data, $container, $callback, $campaigns, $url){
            setParams($data);
            setContainer($container);
            // setCampaigns($campaigns);
            setURL($url)

            this_callback = $callback;

            //Insert SCRIPT
            var script = document.createElement( "script" );
            script.src = url + '/iframe-resizer/js/iframeResizer.min.js';
            document.getElementsByTagName( "head" )[0].appendChild( script );

            // <script src="iframe-resizer/js/iframeResizer.min.js"></script>
            // <script>iFrameResize({ log: true, enablePublicMethods     : true, }, '#myIframe')</script> 
            return this;
        },
        get: function() {

            var str = "";
            // for (var key in campaigns) {
            //     if (str != "") {
            //         str += "&";
            //     }
            //     str += "campaigns[]=" + campaigns[key];
            // }

            str += '&campaigns[]=' + campaign_id + '&creatives[' + campaign_id + ']=' + creative_id

            for (var key in params) {
                if (str != "") {
                    str += "&";
                }
                if(key != 'container') {
                    str += key + "=" + params[key];
                }
            }

            var iframe = document.createElement('iframe');
            iframe.id = 'eiqIframe'
            iframe.src = url + '/?' + str;
            iframe.width = '100%';
            iframe.height = '100%';
            iframe.style = 'border: 0px;';
            //document.body.appendChild(iframe);
            document.querySelectorAll(container)[0].appendChild(iframe);
            var counter = 0;
            iframe.onload = function(){
                // console.log(document.getElementById("eiqIframe").contentWindow.location.href);
                // iframe_link = document.getElementById("eiqIframe").contentWindow.location.href;
                // if(iframe_link.includes('done.php')) {
                //     if(redirect_url != '') {
                //         window.location.href= redirect_url; 
                //     }else {
                //         this_callback();
                //     }
                // }
                if(counter == 0) {
                    iFrameResize();
                }
                if(counter > 0) {
                    if(redirect_url != '') {
                        window.location.href= redirect_url; 
                    }else {
                        this_callback();
                    }
                }
                counter++; 
            }

            // var inter = window.setInterval(function() {
            //     if (iframe.contentWindow.document.readyState === "complete") {
            //       window.clearInterval(inter);
            //     }
            // }, 100);

            // iFrameResize({ log: false}, '#eiqIframe')
        }
    }

    w.EIQHandler = new campaign_handler();
})(this);
