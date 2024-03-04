$(document).ready(function() {

    $('[data-toggle="tooltip"]').tooltip();

    $.ajaxSetup({
       headers: {
           'X-CSRF-Token': $('meta[name="_token"]').attr('content')
       }
    });

    var campaignsURL = $('#baseUrl').html() + '/affiliate/campaignList';

    var eiqIframeID = $('#eiq_iframe_id').val();

    var campaignsDataTable = $('#campaigns-table').DataTable(
    {
        'processing': true,
        'serverSide': true,
        'order': [], //disable the initial ordering

        'initComplete': function()
        {

            var category_column = this.api().column(2);
            var cat_column_name = category_column.footer().innerHTML;

            var cat_select = $('<select id="datatable_category"><option value="">'+cat_column_name+'</option></select>')
                .appendTo($(category_column.header()).empty())
                .on('change', function ()
                {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    category_column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                });

            //add items in the column from the categories table
            $.ajax({
                url: $('#baseUrl').html()+'/getCategories',
                method: 'POST',
                success: function(categories)
                {
                    // console.log(categories);
                    $.each(categories, function(index,category) {
                        cat_select.append('<option value="'+category.id+'">'+category.name+'</option>');
                    });

                    //add undefined category
                    cat_select.append('<option value="null">Undefined</option>');
                },
                error: function(jqXHR,textStatus,errorThrown)
                {
                    console.log(textStatus);
                }
            });

            //Status
            var status_column = this.api().column(5);
            var stat_column_name = status_column.footer().innerHTML;

            var statusSelect = $('<select id="datatable_status"><option value="">'+stat_column_name+'</option></select>')
                .appendTo($(status_column.header()).empty())
                .on( 'change', function ()
                {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    status_column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                });

            //add items in the column from the categories table
            $.ajax({
                url: $('#baseUrl').html()+'/affiliateRequestStatusList',
                method: 'GET',
                success: function(data)
                {
                    for(var i=0;i<data.length;i++)
                    {
                        statusSelect.append('<option value="'+data[i].value+'">'+data[i].option+'</option>');
                    }
                },
                error: function(jqXHR,textStatus,errorThrown)
                {
                    console.log(textStatus);
                }
            });

            
        },
        'drawCallback': function(settings)
        {
            /*
            var category_column = this.api().column(2);
            var cat_column_name = category_column.footer().innerHTML;

            var cat_select = $('<select id="datatable_category"><option value="">'+cat_column_name+'</option></select>')
                .appendTo($(category_column.header()).empty())
                .on('change', function ()
                {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    category_column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                });

            category_column.data().unique().sort().each(function (d,j) {
                cat_select.append( '<option value="'+d+'">'+d+'</option>' )
            });


            //Status
            var status_column = this.api().column(5);
            var stat_column_name = status_column.footer().innerHTML;

            $('<select id="datatable_status"><option value="">'+stat_column_name+'</option><option value="Active">Active</option><option value="Pending">Pending</option><option value="Apply to Run">Apply to Run</option></select>')
                .appendTo( $(status_column.header()).empty())
                .on( 'change', function ()
                {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    status_column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                });
                */
        },

        'ajax':{
            url: campaignsURL,
            type: 'POST',  // method  , by default get
            'data': function(d)
            {

                var category = $('#datatable_category').val();
                var status = $('#datatable_status').val();

                if(category===undefined)
                {
                    category = '';
                }

                if(status===undefined)
                {
                    status = '';
                }

                d.datatable_category = category;
                d.datatable_status = status;
            },
            error: function()
            {
                // error handling
            }
        },

        'columns': [
            {
                'data': 'image',
                'orderable': false
            },
            {
                'data': 'name',
                'orderable': true
            },
            {
                'data': 'category_name',
                'orderable': false
            },
            {
                'data': 'description',
                'orderable': false
            },
            {
                'data': 'payouts',
                'orderable': true
            },
            {
                'data': 'request_status',
                'orderable': false
            },
            {
                'data': 'action',
                'orderable': false
            }
        ],
        'lengthMenu': [[20,50,100,-1],[20,50,100,'ALL']]
    });

    $(document).on('click','.displayMoreDetailsBtn',function(e) 
    {
        e.preventDefault();
        var id = $(this).data('id');
        var advertiser_name = $(this).data('advertiser_name');
        var campaign_name = $('#cmp-'+id+'-name').html();
        var img = $('#cmp-'+id+'-img').attr('src');
        var description = $('#cmp-'+id+'-description').html();

        $('#campaign-name.campaign-desc').html(campaign_name);
        $('#advertiser-name.campaign-desc').html(advertiser_name);
        $('#campaign-image.campaign-desc').attr('src',img);
        $('#campaign-full-description.campaign-desc').html(description);

        $('#campaignMoreDetailsModal').modal('show');
    });

    $('#campaignMoreDetailsModal').on('hidden.bs.modal', function (e) {
        $('#campaign-name.campaign-desc').html('');
        $('#advertiser-name.campaign-desc').html('');
        $('#campaign-image.campaign-desc').attr('src','');
        $('#campaign-full-description.campaign-desc').html('');
    });

    $(document).on('click','.applyToRunRequestBtn',function(e) 
    {
        var this_button = $(this);
        var campaign_id = $(this).data('campaign_id');
        var affiliate_id = $(this).data('affiliate_id');
        var the_url = $('#baseUrl').html() + '/receive_request_to_run_campaign';

        this_button.html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');

        $.ajax({
            type : 'POST',
            url  : the_url,
            data : {
                 'campaign'  :   campaign_id,
                 'affiliate' : affiliate_id
            }, 
            success : function(data) {
                $('#applyToRunMessageModal').modal('show');
                var table = $('#campaigns-table').DataTable();
                var pendingBtn = '<span class="btn btn-default btn-warning">Pending</span>';
                table.cell( this_button.parent('td') ).data(pendingBtn).draw();
                setTimeout(function(){ $('#applyToRunMessageModal').modal('hide'); }, 5000);      
            }
        });
    });

    var hasIframeInstructions = false;
        lastCampaignOpened = 0,
        oWebId = "'website_id'   : ",
        oRedUrl = "'redirect_url' : ",
        cWebId = "'{website_id}'",
        cRedUrl = "'{next_page}'",
        cSampleCode = '',
        scriptEnd = '&lt;/script&gt;',
        scriptEndPos = 0,
        oAppendTo = "options.appendTo = '",
        cAppendTo = '{append_element}',
        oSkipTime = "options.skipTimeout = ",
        cSkipTime = '{skip_timeout}',
        osubmitTxt = "options.submitBtntext = '",
        csubmitTxt = '{submit_text}',
        oLoadTxt = "options.loaderText = '",
        cLoadTxt = '{load_text}';

    var orig_sc = '',
        updated_sc = '';

    String.prototype.replaceAll = function(search, replacement) {
        var target = this;
        return target.replace(new RegExp(search, 'g'), replacement);
    };

    $(document).on('click','.getACodeBtn',function(e) 
    {
        var this_button = $(this);
        var campaign_id = $(this).data('campaign_id');
        var affiliate_id = $(this).data('affiliate_id');
        var the_url = $('#baseUrl').html() + '/get_campaign_posting_instruction';
        this_button.html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');

        if(campaign_id == eiqIframeID) {
            $('#iframeCustomCodeBtn').show();
        }

        if(lastCampaignOpened == campaign_id && hasIframeInstructions) {
            this_button.html('Get A Code');
            $('#campaignGetCodeModal').modal('show');  
            return;
        }

        $.ajax({
            type : 'POST',
            url  : the_url,
            data : {
                 'campaign'  :   campaign_id,
                 'affiliate' : affiliate_id
            }, 
            success : function(data) {
                var status = data.status;
                var campaign = data.data;
                if(status == 1) {
                    if(campaign != null) {

                        var sample_code = campaign.sample_code;

                        // Replace affiliate_id
                        sample_code = sample_code.replace("'{affiliate_id}'", affiliate_id);

                        $('#posting-tab').html(campaign.posting_instruction);
                        // $('#get-tab .code-sample-div p').html(campaign.sample_code);
                        $('#get-tab .code-sample-div pre code').html(sample_code);
                        $('#sampleCode4Custom').text(sample_code);
                        cSampleCode = sample_code;

                        orig_sc = sample_code;

                        $('pre code').each(function(i, block) {
                            hljs.highlightBlock(block);
                        });
                        // console.log(cSampleCode);

                        $('#campaignGetCodeModal').modal('show');  

                        hasIframeInstructions = true;

                    }else {
                        alert('Unfortunately, We have not created the posting instructions yet :(');
                    }
                }else {
                    alert('You do not have the right to run this campign');
                }
                this_button.html('Get A Code');

                lastCampaignOpened = campaign_id;
                // console.log(data);
            }
        });
    });
    
    $(document).on('click','#updateIframeCodeBtn',function(e) 
    {
        updated_sc = orig_sc;

        $('.customizeCode').each(function( index ) {
            var this_field = $(this).attr('id'),
            value = $(this).val();

            if(this_field == 'website') {
                if(value != '') updated_sc = updated_sc.replaceAll("'{website_id}'", value);
            }else if(this_field == 'redirect_url') {
                if(value != '') updated_sc = updated_sc.replaceAll("{next_page}", value);
            }else if(this_field == 'append_to') {
                if(value != '') {
                    og_app_val = "options.appendTo = '"+value+"';  // Optional\n";
                    scriptEndPos = updated_sc.search(scriptEnd);
                    updated_sc = updated_sc.slice(0, scriptEndPos) + og_app_val + updated_sc.slice(scriptEndPos);
                }
            }else if(this_field == 'timeout') {
                if(value != '') {
                    og_timeout_val = "options.skipTimeout = "+value+";  // Optional\n";
                    scriptEndPos = updated_sc.search(scriptEnd);
                    updated_sc = updated_sc.slice(0, scriptEndPos) + og_timeout_val + updated_sc.slice(scriptEndPos);
                }
            }else if(this_field == 'submit_btn') {
                if(value != '') {
                    og_submit_val = "options.submitBtntext = '"+value+"';  // Optional\n";
                    scriptEndPos = updated_sc.search(scriptEnd);
                    updated_sc = updated_sc.slice(0, scriptEndPos) + og_submit_val + updated_sc.slice(scriptEndPos);
                }
            }else if(this_field == 'loader_text') {
                if(value != '') {
                    og_load_val = "options.loaderText = '"+value+"';  // Optional\n";
                    scriptEndPos = updated_sc.search(scriptEnd);
                    updated_sc = updated_sc.slice(0, scriptEndPos) + og_load_val + updated_sc.slice(scriptEndPos);
                }
            }
        });
        
        console.log(updated_sc);
        $('#get-tab .code-sample-div pre code').html(updated_sc);
        $('pre code').each(function(i, block) {
            hljs.highlightBlock(block);
        });

        $('#customCodeCollapse').collapse('hide');
    });


    $('#campaignGetCodeModal').on('hidden.bs.modal', function (e) {
        $('#iframeCustomCodeBtn').hide();
        $('#customCodeCollapse').collapse('hide');
        // $('#sampleCode4Custom').text('');
    });
});
