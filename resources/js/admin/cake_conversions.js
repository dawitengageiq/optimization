
$(document).ready(function()
{
    var serverSideConversionsURL = $('#baseUrl').html() + '/admin/cake_conversions_list';

    $('#cakeConversionsTable').DataTable({
        'processing': true,
        'serverSide': true,
        'order': [[ 0, 'desc' ]],
        'columns': [
            null,
            null,
            null,
            null,
            null,
            null,
            { 'orderable': false }
        ],

        'ajax': {
            'url': serverSideConversionsURL,
            'type': 'post',
            error: function(){}
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });

    $(document).on('click', '.all-details-button', function(){

        var button = $(this);
        var dataDetailsJSON = button.data('details');
        var modalBodyContainer = $('#modal-content-container');
        modalBodyContainer.empty();

        var id = '<span class="modal-data-field">ID: </span><span>'+dataDetailsJSON.id+'</span><br>';
        var visitorID = '<span class="modal-data-field">Visitor ID: </span><span>'+dataDetailsJSON.visitor_id+'</span><br>';
        var requestSessionID = '<span class="modal-data-field">Request Session ID: </span><span>'+dataDetailsJSON.request_session_id+'</span><br>';
        var clickRequestSessionID = '<span class="modal-data-field">Click Request Session ID: </span><span>'+dataDetailsJSON.click_request_session_id+'</span><br>';
        var clickID = '<span class="modal-data-field">Click ID: </span><span>'+dataDetailsJSON.click_id+'</span><br>';
        var conversionDate = '<span class="modal-data-field">Conversion Date: </span><span>'+dataDetailsJSON.conversion_date+'</span><br>';
        var lastUpdated = '<span class="modal-data-field">Last Updated: </span><span>'+dataDetailsJSON.last_updated+'</span><br>';
        var clickDate = '<span class="modal-data-field">Click Date: </span><span>'+dataDetailsJSON.click_date+'</span><br>';
        var eventID = '<span class="modal-data-field">Event ID: </span><span>'+dataDetailsJSON.event_id+'</span><br>';
        var affiliateID = '<span class="modal-data-field">Affiliate ID: </span><span>'+dataDetailsJSON.affiliate_id+'</span><br>';
        var advertiserID = '<span class="modal-data-field">Advertiser ID: </span><span>'+dataDetailsJSON.advertiser_id+'</span><br>';
        var offerID = '<span class="modal-data-field">Offer ID: </span><span>'+dataDetailsJSON.offer_id+'</span><br>';
        var offerName = '<span class="modal-data-field">Offer Name: </span><span>'+dataDetailsJSON.offer_name+'</span><br>';
        var campaignID = '<span class="modal-data-field">Campaign ID: </span><span>'+dataDetailsJSON.campaign_id+'</span><br>';
        var creativeID = '<span class="modal-data-field">Creative ID: </span><span>'+dataDetailsJSON.creative_id+'</span><br>';
        var subID1 = '<span class="modal-data-field">Sub ID 1: </span><span>'+dataDetailsJSON.sub_id_1+'</span><br>';
        var subID2 = '<span class="modal-data-field">Sub ID 2: </span><span>'+dataDetailsJSON.sub_id_2+'</span><br>';
        var subID3 = '<span class="modal-data-field">Sub ID 3: </span><span>'+dataDetailsJSON.sub_id_3+'</span><br>';
        var subID4 = '<span class="modal-data-field">Sub ID 4: </span><span>'+dataDetailsJSON.sub_id_4+'</span><br>';
        var subID5 = '<span class="modal-data-field">Sub ID 5: </span><span>'+dataDetailsJSON.sub_id_5+'</span><br>';
        var conversionIPAddress = '<span class="modal-data-field">Conversion IP Address: </span><span>'+dataDetailsJSON.conversion_ip_address+'</span><br>';
        var clickIPAddress = '<span class="modal-data-field">Click IP Address: </span><span>'+dataDetailsJSON.click_ip_address+'</span><br>';
        var receivedAmount = '<span class="modal-data-field">Received Amount: </span><span>'+dataDetailsJSON.received_amount+'</span><br>';
        var test = '<span class="modal-data-field">Test: </span><span>'+dataDetailsJSON.test+'</span><br>';
        var transactionID = '<span class="modal-data-field">Transaction ID: </span><span>'+dataDetailsJSON.transaction_id+'</span><br>';

        modalBodyContainer.append(id);
        modalBodyContainer.append(visitorID);
        modalBodyContainer.append(requestSessionID);
        modalBodyContainer.append(clickRequestSessionID);
        modalBodyContainer.append(clickID);
        modalBodyContainer.append(conversionDate);
        modalBodyContainer.append(lastUpdated);
        modalBodyContainer.append(clickDate);
        modalBodyContainer.append(eventID);
        modalBodyContainer.append(affiliateID);
        modalBodyContainer.append(advertiserID);
        modalBodyContainer.append(offerID);
        modalBodyContainer.append(offerName);
        modalBodyContainer.append(campaignID);
        modalBodyContainer.append(creativeID);
        modalBodyContainer.append(subID1);
        modalBodyContainer.append(subID2);
        modalBodyContainer.append(subID3);
        modalBodyContainer.append(subID4);
        modalBodyContainer.append(subID5);
        modalBodyContainer.append(conversionIPAddress);
        modalBodyContainer.append(clickIPAddress);
        modalBodyContainer.append(receivedAmount);
        modalBodyContainer.append(test);
        modalBodyContainer.append(transactionID);

        $('#conversion-details-modal').modal('show');
    });
});