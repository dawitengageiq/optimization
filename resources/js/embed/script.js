! (function(w)
{
    var container = 'body';

    var params = {
        'affiliate_id': '',
        'campaign_id': '',
        'offer_id' : '',
        'creative_id' : '',
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
    };

    var url = 'https://leadreactor.engageiq.com';

    var setParams = function($data){
        // params  = $data;
        for (var key in $data) {
        	if(key in params) {
	        	params[key] = $data[key]
	        }
        }
    };

    var setContainer = function($container){
        container  = $container == '' || typeof $container == 'undefined' ? container : $container;
    };

    var setUrl = function($url){
        url  = $url == '' || typeof $url == 'undefined' ? url : $url;
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
        init: function($data, $container, $url){
            setParams($data);
            setContainer($container);
            setUrl($url);
            return this;
        },
        get: function() {
            console.log(params);
            // console.log(container);
            //return params;

            //Insert CSS
            var link = document.createElement( "link" );
            link.href = url + '/embed/style.min.css';//'https://engageiq.com/pfrmockup/pfr1-v2/style.css';
            link.type = "text/css";
            link.rel = "stylesheet";
            link.media = "screen,print";
            document.getElementsByTagName( "head" )[0].appendChild( link );

            var str = "";
            for (var key in params) {
                if (str != "") {
                    str += "&";
                }
                if(key != 'container') {
                    str += key + "=" + params[key];
                }
            }
            //console.log(url + '/campaign/embed?' + str);

            // var iframe = document.createElement('iframe');
            // // var html = '<body>Foo</body>';
            // iframe.id = 'eiqIframe'
            // iframe.src = url + '/campaign/embed?' + str;
            // iframe.width = '100%';
            // iframe.height = '100%';
            // iframe.style = 'border: 0px;';
            // //document.body.appendChild(iframe);
            // document.querySelectorAll(container)[0].appendChild(iframe);

            var landing_URL='https://path17.paidforresearch.com/dynamic_live_mdblue/?affiliate_id='+params['affiliate_id']+
            	'&campaign_id='+params['campaign_id']+'&offer_id='+params['offer_id']+'&creative_id='+params['creative_id']+
            	'&firstname='+params['first_name']+'&lastname='+params['last_name']+'&dobmonth='+params['dob_month']+
            	'&dobday='+params['dob_day']+'&dobyear='+params['dob_year']+'&zip='+params['zip']+'&email='+params['email']+
            	'&gender='+params['gender']+'&address='+params['address']+'&phone='+params['phone'];

			var impression_pixel_url='https://engageiq.nlrtrk.com/i.ashx?a='+params['affiliate_id']+
            	'&c='+params['creative_id']+
            	'&p=m&s1=' + params['s1'];

            var string = '<img src="'+impression_pixel_url+'" width="1" height="1" border="0"/>' +
            '<div id="_eiqXYZFromContainer" class="form-container">'+
				'<a href="'+landing_URL+'" class="overlay" target="_blank"></a>'+
				'<h1>'+
					'LESS THAN<br>' +
					'<span>5 MINUTES SURVEY</span>' +
				'</h1>' +
				'<div class="ribbon">' +
					'<h4>Confirm info and complete</h4>'+
				'</div>'+
				'<form>'+
					'<p>Hey '+params['first_name']+',<br>Your entry is Pending Confirmation</p>'+
					'<div class="form-input">'+
							'<input type="text" name="" placeholder="Name" value="'+params['first_name']+'">' +
							'<input type="email" name="" placeholder="Email Address" value="'+params['email']+'">'+
						'<input type="submit" value="Continue">'+
					'</div>'+
				'</form>'+
			'</div>'

			//var string ="<div class='form-container'><h1>LESS THAN<br><span>5 MINUTES SURVEY</span></div></h1>"
			document.querySelectorAll(container)[0].insertAdjacentHTML( 'beforeend', string );//.append(string);
        }
    }

    w.EIQHandler = new campaign_handler();
})(this);
