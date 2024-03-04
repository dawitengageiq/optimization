#The following guidelines should be followed in order to deal with API security.

1. In order that the API can be accessed and used you need to supply custom header that contains the token. The header should be named as leadreactortoken and the value is the token.

2. Developers can obtain token via ajax call using the URL http://leadreactor.engageiq.com/api/getMyToken and using POST method with custom headers useremail and userpassword. The values for useremail and userpassword are from Lead Reactor account that has API permission.

##RESPONSES:

###getMyToken responses when email exists
    {
        'token':'the_awesome_token',
        'message':'success'
    }

###getMyToken responses when email do not exists or invalid
    {
        'token':null,
        'message':'unauthorized'
    }

###API response when token is incorrect or invalid
    {
        'message': 'invalid token'
    }

###API response when email is incorrect or invalid</p>
    {
        'message': 'not authorized'
    }



#API Instructions for getConversionsEmailOfferID

_Please follow how to get the API token it will be needed in using this API._

##API URL:
http://leadreactor.engageiq.com/api/getConversionsEmailOfferID

##METHOD:
GET

##HEADERS:
* leadreactortoken - API token please follow the instruction above on how to obtain it.

##PARAMETERS:
* offer_id - email of the conversion
* email - conversion offer_id

##RESPONSES:
JSON array with found conversions as elements.


#API Instructions for getConversionsByAffiliateOfferS4

_Please follow how to get the API token it will be needed in using this API._

##API URL:
http://leadreactor.engageiq.com/api/getConversionsByAffiliateOfferS4

##METHOD:
GET

##HEADERS:
* leadreactortoken - API token please follow the instruction above on how to obtain it.

##PARAMETERS:
* affiliate_id - affiliate_id of the conversion
* offer_id - conversion offer_id
* sub_id_4 - the container value for user_id or email.

##RESPONSES:
JSON array with found conversions as elements.