/**!
 * NLR Campaign API Script
 * Campaign Loader v1 (http://leadreactor.engageiq.com)
 * All action, event related to appending the iframe for NLR offers.
 *
 * @param options - Host site given parameters
 * @uses iFrameResize - Correct the iframe dimmension in relation to inner content
 * @uses MobileDetect - Detect mobile view
 */

(function(inputs)
{
    /**
     * Debug loader
     */
    var loader_debug = false;

    /**
     * Flag to determine if needed to show skip link
     */
     var show_skip = true;

    /**
     * Global container for merge default_options and options
     */
    var _options = {};

    /**
     * The default value needed to get the list of campaigns
     *
     */
    var default_options = {
        // This will be used for iframe query string
        // add website id
        'src': {
            'screen_view': '1',
            'submitBtn': '',
            'submit': '',
            'first_name': '',
            'last_name': '',
            'email': '',
            'zip': '',
            'birthdate': '',
            'dobmonth': '',
            'dobday': '',
            'dobyear': '',
            'gender': '',
            'address': '',
            'phone': '',
            'phone1': '',
            'phone2': '',
            'phone3': '',
            'ethnicity': '',
            'source_url': '',
            'image': '',
            'affiliate_id': '',
            'website_id':'',
            'offer_id': '',
            'campaign_id': '',
            's1': '',
            's2': '',
            's3': '',
            's4': '',
            's5': '',
            'cs1': '',
            'cs2': '',
            'cs3': '',
            'cs4': '',
            'cs5': '',
            'q2': '',
            'redirect_url': ''
        },
        // Append the iframe to given value
        'appendTo': 'body',
        // The NLR url to send request
        'targetUrl': 'http://leadreactor.engageiq.com/',
        // Iframe attributes
        'ifrWidth': '100%',
        'ifrBorder': 'none',
        // Time duration before showing the skip button, default 1 second
        'skipTimeout': 6000,
        // Button text
        'submitBtntext': 'Submit',
        // True: initialize resizer when the resizer script is ready
        // False: initialize resizer when  the resizer script is ready and the iframe content is done
        'initiateResizerBeforeIframeContentLoad': true,
        // Loading message
        'loaderText': 'Please wait while we fetch offers to match your profile.',
        // Loading text attributes
        'loaderTextColor': '#53A653',
        'loaderWrapperBackgroundColor': '#fff',
        'loaderTop': '175px',
        'loaderMarginTop': '-175px',
        'toReloadParentFrame': false
    };

    /**
     * Function to overwrite default data with given inputs
     *
     * @param object $default
     * @param object $input
     * @return object
     */
    var extend = function($default, $input)
    {
        // Go through default object and iterate
        // We used the default object for it has the complete parameters
        for (var property in $default) {
            // Check $input object if the property of defaut exists on its own
            if ($input.hasOwnProperty(property)) {
                // Detemine if the $input property is object
                if(typeof($input[property]) === 'object') {
                    // go through the $default property
                    // We used the default object property for it has the complete parameters
                    for (var inner_property in $default[property]) {
                        // Check $input object if the property of defaut exists on its own
                        if ($input[property].hasOwnProperty(inner_property)) {
                            // Replace the default property value with $input property value
                            $default[property][inner_property] = $input[property][inner_property];
                        }
                    }
                }
                // $input property is not object
                else {
                    // Replace the default property value with $input property value
                    $default[property] = $input[property];
                }
            }
        }

        if($input.loaderDebug) loader_debug = $input.loaderDebug;

        return $default;
    };

    /**
     * Catch script error
     *
     */
    window.addEventListener('error', function(event)
    {

        if (event.type === "error") {
            if(event.target.id != 'mobile_detect') return;

            if(inputs.appendTo.indexOf('#') !== -1) var iframe_wrapper = document.getElementById(inputs.appendTo.substring(1));
            // Uses class attribute
            else if(inputs.appendTo.indexOf('.') !== -1) var iframe_wrapper = document.getElementsByClassName(inputs.appendTo.substring(1))[0];
            // Uses plain html tag or default html tag
            else var iframe_wrapper = document.getElementsByTagName(inputs.appendTo)[0];

            var html = '<span style="font-size: 20px; font-family: Verdana, Arial, Helvetica, sans-serif; color:' + default_options.loaderTextColor + '">There is a problem loading the script from EIQ website. <br /> Please report to  EIQ about this error.</span>';
            if(inputs.src.redirect_url) html += '<br /><br /><span id="skip_error" style="font-size: 20px; font-family: Verdana, Arial, Helvetica, sans-serif; color:#2fa4e7; cursor: pointer">Skip</span>';

            iframe_wrapper.innerHTML= html;
        }

    }, true);

    /**
     * Catch skip click event
     *
     */
    window.addEventListener('click', function(event) {
        if (event.target.id === "skip_error") {
            if(inputs.toReloadParentFrame !== 'false') {
                window.location.href = inputs.src.redirect_url;
            } else {
                window.top.location.href = inputs.src.redirect_url;
            }
        }
    });

    /**
     * initialization
     * Load the mobile detect first and
     * Run initialization when mobile detect script is ready
     *
     */
    window.onload = function()
    {
       _options = extend(default_options, inputs);

        // Add mobile detect script to header
        var html = document.getElementsByTagName("html")[0];
        var detectMobile = document.createElement("script");

        detectMobile.type = 'text/javascript';
        detectMobile.id = 'mobile_detect';
        detectMobile.src =  _options.targetUrl + 'js/api/mobile_detect.min.js';

        // Function after finish loading of script
        html.addEventListener("load", function(event) {
            //if (event.target.id === "mobile_detect") getUserIp('http://ipinfo.io', processAndAppend);
            if (event.target.id === "mobile_detect") getUserIp('http://postleads.engageiq.com/myipaddress.php', processAndAppend);
        }, true);

        // append to header
        html.appendChild(detectMobile);
    }

    /**
     * Function to generate query string
     *
     * @param array arr
     * @return string
     */
    var queryString = function (arr)
    {
        var s = '';
        for ( var e in arr ) {
           s += '&' + e + '=' + escape( arr[e] );
        }
        return s.substring(1);
    }

    /**
     * Function to load iframe with given parameters
     *
     * @param array options
     * @return void
     */
    var appendIframe = function ($options)
    {
        // Iframe source url
        ifr_src = $options.targetUrl + 'frame/get_campaign_list_by_api?' + queryString($options.src);
        console.log(ifr_src);
        // Where to append
        var appendTo = $options.appendTo;

        // Detemine what elements where used to append the iframe
        // Uses id attribute
        if(appendTo.indexOf('#') !== -1) var iframe_wrapper = document.getElementById(appendTo.substring(1));
        // Uses class attribute
        else if(appendTo.indexOf('.') !== -1) var iframe_wrapper = document.getElementsByClassName(appendTo.substring(1))[0];
        // Uses plain html tag or default html tag
        else var iframe_wrapper = document.getElementsByTagName(appendTo)[0];

        // Append the looding message before loading the iframe
        appendLoaderText(iframe_wrapper, $options);

        // Creation of iframe element
        var ifr = document.createElement('iframe');
        // Some iframe attributes
        ifr.src = ifr_src;
        ifr.width = $options.ifrWidth;
        ifr.style.border = $options.ifrBorder;

        // Function to determine if the iframe is done loading
        ifr.onload = function() {
            // Remove the loading text and image
            var loader_text_wrapper = document.getElementById('loader_text_wrapper');
            if(!loader_debug) if(loader_text_wrapper) loader_text_wrapper.remove();

            // Iframe resizer will be called after complete loading of iframe
            if(!loader_debug) if(!$options.initiateResizerBeforeIframeContentLoad) iFrameResize();

            // Need to remove the skip button since the iframe is done loading
            if($options.src.redirect_url) {
                // If the iframe is first to load before the skip link was shown,
                // We need to set to false so that the skip button will not show
                show_skip = false;
                // If ever the skip button was shown before the loading is complete,
                // We need to remove the skip link
                var parent_skip = document.getElementById('parent_skip');
                if(!loader_debug) if(parent_skip) parent_skip.remove();
            }
        };

        // append now the iframe
        iframe_wrapper.appendChild(ifr);

        // If redirection url is available, we need to append the skip button,
        // so that in case of not showing or incomplete loading
        // the user has the ability to skip the page
        appendSkipLink ($options.src.redirect_url, $options.skipTimeout, iframe_wrapper);
    }

    /**
     * Function to append skip link
     *
     * @param string $redirect_url
     * @param integer $timeout
     * @param node $iframe_wrapper
     * @param bolean $show_skip
     * @return void
     */
    var appendSkipLink = function ($redirect_url, $timeout, $iframe_wrapper)
    {
        if($redirect_url) {
            // Set time delay before showing the skip link
            setTimeout(function () {
                // Create the skip anchor element
                var skip = document.createElement("a");
                // Skip attribute
                skip.id = 'parent_skip';
                skip.href = $redirect_url;
                skip.style.cssText = 'color: #2fa4e7; cursor: pointer; display: block; margin: 30px 0 15px; text-align: center; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none;';
                // Determine that in this moment the button is needed to show,
                // means the iframe is not yet finish loading
                // and ifr.onload is not yet triggered

                if(show_skip) {
                    $iframe_wrapper.appendChild(skip);
                    // Create the text for skip element
                    var text = document.createTextNode('Skip');
                    skip.appendChild(text);
                }
            },
            // time delay, Default time is  1 second
            $timeout);
        }
    }

    /**
     * Function to load loading rotator styling
     *
     * @return void
     */
    var appendLoadingRotatorStyle = function ()
    {
        var css = document.createElement('style');
        css.type = 'text/css';

        var styles = '@keyframes spin {\
                0% { transform: rotate(0deg); }\
                100% { transform: rotate(360deg); }\
            }';

        if (css.styleSheet) css.styleSheet.cssText = styles;
        else css.appendChild(document.createTextNode(styles));

        document.getElementsByTagName("head")[0].appendChild(css);
    }

    /**
     * Function to load loading text and image
     *
     * @param node $iframe_wrapper
     * @param string $loaderText
     * @return void
     */

    var appendLoaderText = function ($iframe_wrapper, $option)
    {
        // Text message
        var loader_text = document.createTextNode($option.loaderText);

        // Rotator
        var loader_image = document.createElement("div");
        loader_image.style.cssText = 'border: 8px solid #f3f3f3; border-radius: 50%; width: 25px; height: 25px; animation: spin 2s linear infinite; margin: 0 auto 10px;';
        loader_image.style.borderTopColor = $option.loaderTextColor;

        // Inner text wrapper
        var loader_inner_text_wrapper = document.createElement("div");
        loader_inner_text_wrapper.style.cssText = 'display:  table-cell; vertical-align: middle; font-size: 20px; font-family: Verdana, Arial, Helvetica, sans-serif;';
        loader_inner_text_wrapper.style.color =  $option.loaderTextColor;

        // Append rotator and message to innertext wrappar
        loader_inner_text_wrapper.appendChild(loader_image);
        loader_inner_text_wrapper.appendChild(loader_text);

        // Text message wrapper
        var loader_text_wrapper = document.createElement("div");
        loader_text_wrapper.id = 'loader_text_wrapper';
        loader_text_wrapper.style.cssText = 'display: table; position: relative; width: 100%; top: ' + $option.loaderTop + '; margin-top: ' + $option.loaderMarginTop + '; height: 175px; overflow: hidden;';
        loader_text_wrapper.style.backgroundColor = $option.loaderWrapperBackgroundColor;

        // Append iner text wrapper to parent wrapper
        loader_text_wrapper.appendChild(loader_inner_text_wrapper);

        // Append text message wrapper tp iframe wrapper
        $iframe_wrapper.appendChild(loader_text_wrapper);

        // Append loader style
        appendLoadingRotatorStyle();
    }

    /**
     * Function to load iframe resizer
     *
     * @param string targetUrl
     * @param bolean initiateResizer
     * @return void
     */
    var appendIframeResizer = function (targetUrl, initiateResizer)
    {
        // Creation of iframe resizer script element
        var ifr_resizer = document.createElement('script');
        ifr_resizer.type = 'text/javascript';
        ifr_resizer.id = 'irame_resizer';
        ifr_resizer.src = targetUrl + 'js/api/iframeResizer.min.js';

        // Function after finish loading of script
        ifr_resizer.onload = function() {
            if(initiateResizer) iFrameResize();
        };
        // append below body tag
        document.getElementsByTagName('html')[0].appendChild(ifr_resizer);
    }

    /**
     * Function to detect screen view
     *
     * @return bolean
     */
    var windowNavigator = function ()
    {
        var browser = {
            'os': '',
            'os_version': '',
            'browser': '',
            'browser_version': '',
            'user_agent': window.navigator.userAgent
        }
        var device = {
            'isMobile': '',
            'isTablet': '',
            'isDesktop': '',
            'type': ''
        }

        var nVer = navigator.appVersion;
        var nAgt = navigator.userAgent;
        var browserName  = navigator.appName;
        var fullVersion  = ''  +parseFloat(navigator.appVersion);
        var majorVersion = parseInt(navigator.appVersion,10);
        var nameOffset, verOffset, ix;

        // In Opera, the true version is after "Opera" or after "Version"
        if ((verOffset = nAgt.indexOf("Opera")) != -1) {
            browserName = "Opera";
            fullVersion = nAgt.substring(verOffset + 6);
            if ((verOffset = nAgt.indexOf("Version")) != -1)
                fullVersion = nAgt.substring(verOffset + 8);
        }
        // In MSIE, the true version is after "MSIE" in userAgent
        else if ((verOffset = nAgt.indexOf("MSIE")) != -1) {
            browserName = "Microsoft Internet Explorer";
            fullVersion = nAgt.substring(verOffset + 5);
        }
        // In Chrome, the true version is after "Chrome"
        else if ((verOffset = nAgt.indexOf("Chrome")) != -1) {
            browserName = "Chrome";
            fullVersion = nAgt.substring(verOffset + 7);
        }
        // In Safari, the true version is after "Safari" or after "Version"
        else if ((verOffset = nAgt.indexOf("Safari")) != -1) {
        browserName = "Safari";
        fullVersion = nAgt.substring(verOffset + 7);
        if ((verOffset = nAgt.indexOf("Version")) != -1)
            fullVersion = nAgt.substring(verOffset + 8);
        }
        // In Firefox, the true version is after "Firefox"
        else if ((verOffset = nAgt.indexOf("Firefox")) != -1) {
            browserName = "Firefox";
            fullVersion = nAgt.substring(verOffset+8);
        }
        // In most other browsers, "name/version" is at the end of userAgent
        else if ( (nameOffset = nAgt.lastIndexOf(' ') + 1) <
                  (verOffset = nAgt.lastIndexOf('/')) )
        {
            browserName = nAgt.substring(nameOffset, verOffset);
            fullVersion = nAgt.substring(verOffset + 1);
            if (browserName.toLowerCase() == browserName.toUpperCase()) {
                browserName = navigator.appName;
            }
        }
        // trim the fullVersion string at semicolon/space if present
        if ((ix = fullVersion.indexOf(";")) != -1) fullVersion = fullVersion.substring(0, ix);
        if ((ix = fullVersion.indexOf(" ")) != -1) fullVersion = fullVersion.substring(0, ix);
        if ((ix = fullVersion.indexOf(')')) != -1) fullVersion = fullVersion.substring(0, ix);

        majorVersion = parseInt('' + fullVersion, 10);
        if (isNaN(majorVersion)) {
            fullVersion  = '' + parseFloat(navigator.appVersion);
            majorVersion = parseInt(navigator.appVersion, 10);
        }

        // mobile version
        var mobileVr = /Mobile|mini|Fennec|Android|iP(ad|od|hone)/.test(nVer);

        // system
        var os = 'unknown';
        var clientStrings = [
            {s:'Windows 10', r:/(Windows 10.0|Windows NT 10.0)/},
            {s:'Windows 8.1', r:/(Windows 8.1|Windows NT 6.3)/},
            {s:'Windows 8', r:/(Windows 8|Windows NT 6.2)/},
            {s:'Windows 7', r:/(Windows 7|Windows NT 6.1)/},
            {s:'Windows Vista', r:/Windows NT 6.0/},
            {s:'Windows Server 2003', r:/Windows NT 5.2/},
            {s:'Windows XP', r:/(Windows NT 5.1|Windows XP)/},
            {s:'Windows 2000', r:/(Windows NT 5.0|Windows 2000)/},
            {s:'Windows ME', r:/(Win 9x 4.90|Windows ME)/},
            {s:'Windows 98', r:/(Windows 98|Win98)/},
            {s:'Windows 95', r:/(Windows 95|Win95|Windows_95)/},
            {s:'Windows NT 4.0', r:/(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/},
            {s:'Windows CE', r:/Windows CE/},
            {s:'Windows 3.11', r:/Win16/},
            {s:'Android', r:/Android/},
            {s:'Open BSD', r:/OpenBSD/},
            {s:'Sun OS', r:/SunOS/},
            {s:'Linux', r:/(Linux|X11)/},
            {s:'iOS', r:/(iPhone|iPad|iPod)/},
            {s:'Mac OS X', r:/Mac OS X/},
            {s:'Mac OS', r:/(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/},
            {s:'QNX', r:/QNX/},
            {s:'UNIX', r:/UNIX/},
            {s:'BeOS', r:/BeOS/},
            {s:'OS/2', r:/OS\/2/},
            {s:'Search Bot', r:/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/}
        ];
        for (var id in clientStrings) {
            var cs = clientStrings[id];
            if (cs.r.test(nAgt)) {
                os = cs.s;
                break;
            }
        }

        var osVersion = 'unknown';

        if (/Windows/.test(os)) {
            osVersion = 'Windows ' + /Windows (.*)/.exec(os)[1];
            os = 'Windows';
        }

        switch (os) {
            case 'Mac OS X':
                osVersion = /Mac OS X (10[\.\_\d]+)/.exec(nAgt)[1];
                break;

            case 'Android':
                osVersion = /Android ([\.\_\d]+)/.exec(nAgt)[1];
                break;

            case 'iOS':
                osVersion = /OS (\d+)_(\d+)_?(\d+)?/.exec(nVer);
                osVersion = osVersion[1] + '.' + osVersion[2] + '.' + (osVersion[3] | 0);
                break;
        }

        var md = new MobileDetect(window.navigator.userAgent);
        // Desktop
        var view = 1;
        device.isDesktop = 1;
        device.type = 'desktop';
        browser.browser = browserName;
        browser.browser_version = fullVersion;
        browser.os = (device.os) ? device.os : os;
        browser.os_version = osVersion;
        // Phone
        if (md.mobile() && md.phone()) {
             view = 2;
             device.isMobile = 1;
             device.isDesktop = '';
             device.type = md.phone();
        }
        // Tablet
        if (md.tablet() || (md.mobile() && !md.phone())) {
            view = 3;
            device.isTablet = 1;
            device.isDesktop = '';
            device.type = md.tablet();
        }

        return {
            'screen_view': view,
            'browser': browser,
            'device': device
        };
    }

    /**
     * Birthdate
     *
     * @param integer $year
     * @param integer $month
     * @param integer $day
     * @return string
     */
    var birthDate = function ($year, $month, $day)
    {
        return $year + '-' + $month  + '-' + $day;
    }

    /**
     * Gender
     *
     * @param string $gender
     * @return string
     */
    var gender = function ($gender)
    {
        if($gender == 'male' || $gender == 'm') return 'M';
        else return 'F';
    }

    /**
     * Function to get the user IP
     *
     * @param string $url
     * @param callable callback
     * @return void
     */
    var getUserIp = function (url, callback)
    {
        // Create callback for jsonp response
        var callbackName = 'jsonp_callback_' + Math.round(100000 * Math.random());
        window[callbackName] = function(data) {
            delete window[callbackName];
            document.body.removeChild(script);
            callback(data.ip);
        };

        // Create temporary script
        var script = document.createElement('script');
        script.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'callback=' + callbackName;
        document.body.appendChild(script);

        // Callback when error
        script.onerror = function(data) {
           callback('');
        };
    }

    /**
     *  Assign source data and append iframe
     *
     * @param string $ip
     * @return void
     */
    var processAndAppend = function (ip)
    {
        var windowData = windowNavigator();
        // IP adress
        _options.src.ip = ip;
        // Screen view
        _options.src.screen_view = windowData.screen_view;
        //browser
         _options.src.os = windowData.browser.os;
        _options.src.os_version = windowData.browser.os_version;
        _options.src.browser = windowData.browser.browser;
        _options.src.browser_version = windowData.browser.browser_version;
        _options.src.user_agent = windowData.browser.user_agent;
        //device
        _options.src.is_mobile = windowData.device.isMobile;
        _options.src.is_tablet = windowData.device.isTablet;
        _options.src.is_desktop = windowData.device.isDesktop;
        _options.src.type = windowData.device.type;
        // Full birthdate
        _options.src.birthdate = birthDate(_options.src.dobyear, _options.src.dobmonth, _options.src.dobday);
        // Gender
        _options.src.gender = gender(_options.src.gender.toLowerCase());
        // Source url
        _options.src.source_url = window.location.href;
        // Phone
        var phone = _options.src.phone.replace(/\D/g,'');
        _options.src.phone = phone;
        _options.src.phone1 = phone.substr(0, 3);
        _options.src.phone2 = phone.substr(3, 3);
        _options.src.phone3 = phone.substr(6, 4);
        // Submit
        _options.src.submitBtn = _options.submitBtntext;
        _options.src.submit = 'engageiq_iframeapi_post_data';
        // Check wether to relaod parent frame/self frame
        _options.src.target_url = _options.targetUrl;
        _options.src.reload_parent_frame = _options.toReloadParentFrame;

        appendIframe(_options);
        appendIframeResizer(_options.targetUrl, _options.initiateResizerBeforeIframeContentLoad);
    }
})(options);
