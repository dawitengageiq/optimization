$(document).ready(function()
{
    var zipMasterURL = $('#baseUrl').html() + '/zipCode';
    var table = $('#zipmaster-table').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax':{
            url:zipMasterURL, // json datasource
            type: 'get',  //LIVE
            // type: 'post',  // KARLA ver
            error: function(){  // error handling
            }
        },
        lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]]
    });
});