$(function()
{
	var campaign_types_count = parseInt($("#totalCampaignTypeCountCol").val()),
		campaign_type_columns = [],
		campaign_type_columns_string = '',
		current_campaign_type_count = campaign_types_count;

	for(x = 7; x <= campaign_types_count + 2; x++) {
		campaign_type_columns.push(x);
		campaign_type_columns_string += x + ',';
	}
   
	var campaign_type = null, campaign = null,
        linkout_ids = $.parseJSON($('#campaignLinkoutIds').val()),
        displayed_benchmarks = $.parseJSON($('#current_benchmarks').val());
    $('[data-toggle="tooltip"]').tooltip();

    var optin_table = $('#pageOptInRateStats-table').DataTable({
		'processing': true,
		'serverSide': true,
		"autoWidth": false,
        "searching": false,
		"columnDefs": [
		    { "orderable": false, "targets": [8,9,10,11,12,13,14,15,16,17,18,19,20] }
		],
        "deferLoading": 0,
		'ajax':{
			url: $('#baseUrl').html() + '/admin/pageOptIn',
			type: 'post',
			'data': function(d)
            {
                // d.id = $('#id').val();
                // d.benchmarks = $('.campaign_type_benchmark').serialize();
                d.campaign_type = campaign_type;
                d.campaign = campaign;
                d.affiliate_id = $('#affiliate_id').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.group_by = $('#group_by').val();
                d.date_range = $('#date_range').val();
                d.sib_s1 = $('#sib_s1').prop('checked');
                d.sib_s2 = $('#sib_s2').prop('checked');
                d.sib_s3 = $('#sib_s3').prop('checked');
                d.sib_s4 = $('#sib_s4').prop('checked');

                var benchmark = new Array();
                var offer_ids = new Array();
                $('.campaign_type_benchmark').each(function(i,obj) {
                    // console.log($(obj).data('type') + ' - ' + $(obj).val())
                    var type = $(obj).data('type'),
                        cmp = $(obj).val();
                    if(cmp !== '' || cmp.trim().length > 0) {
                        if(type == 5 || type == 6) {
                            var cmpx = new Array();
                            $.each(cmp, function(i, v) {
                                // offer_ids[type].push(linkout_ids[cmp]);
                                cmpx.push(linkout_ids[v]);
                            });
                            offer_ids[type] = cmpx;
                            // offer_ids[type] = linkout_ids[cmp];
                        }
                        if($.inArray('all', cmp) >= 0) {
                            // benchmark[11][]
                            cmp = $('[name="benchmark['+type+'][]"] option').map(function() {return $(this).val();}).get();
                        }

                        benchmark[type] = cmp;
                    }
                });

                // console.log(benchmark);
                // console.log(offer_ids);

                d.benchmarks = benchmark;
                d.offer_ids = offer_ids;
            },
            "dataSrc": function ( json ) {
            	optin_table.columns().visible(true);
                //Hide 
                $.each(json.hiddenCols, function(index, type) {
			        var column = optin_table.column( $('th.col-ct-'+type) );
			        column.visible(false);
                });
                current_campaign_type_count = campaign_types_count - json.hiddenCols.length;
                // console.log(current_campaign_type_count);

                //Benchmarks
                var benchmarks = json.benchmarks;
                $.each(benchmarks, function(campaign_type, campaign) {
                    $('[name="benchmark['+campaign_type+']"]').val(campaign);
                });
                
                $('#downloadPageOptInRateReport').removeAttr('disabled');
                $('#poisDateLabel').html(json.date);

                $('.campaign_type_column').html('');

                //Benchmark Types
                $.each(json.benchmarks, function(type, benchmarks) {
                    if($.inArray('all', benchmarks) >= 0) {
                        benchmarks = ['all'];

                        label = '<span class="label label-default bch-label benchmark-label" data-toggle="tooltip" data-placement="bottom" title="All">All</span><br>';
                        $('.col-ct-'+type).append(label);
                    }else {
                        $.each(benchmarks, function(j, campaign){
                            var cmp_name = $('[name="benchmark['+type+'][]"] option[value="'+campaign+'"]').html();
                            cmp_name_prev = cmp_name.substr(0,10);
                            if(cmp_name.length > 10) cmp_name_prev += '...';

                            label = '<span class="label label-default bch-label benchmark-label" data-toggle="tooltip" data-placement="bottom" title="'+cmp_name+'">'+cmp_name_prev+'</span><br>';
                            $('.col-ct-'+type).append(label);
                        });
                    } 

                    $('[name="benchmark['+type+'][]"]').val(benchmarks).trigger('change');
                });
                $('[data-toggle="tooltip"]').tooltip();
                displayed_benchmarks = json.benchmarks;
                return json.data;
            },
			error: function(data) //error handling
			{
				console.log(data);
			}
		},
		lengthMenu: [[25,50,100,1000,2000,3000],[25,50,100,1000,2000,3000]],
        "sDom": '<"pageOptInStats_displayDate">l<"pageOptInStats_Toolbar">frtip'
	});

    $('.campaign_type_benchmark').select2();

    $("div.pageOptInStats_Toolbar").html('<button type="button" class="openBenchmarkBtn btn btn-default btn-xs" data-toggle="modal" data-target="#benchmarkModal"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button><button id="refreshPageOptInRateStatsBtn" type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>');
    $("div.pageOptInStats_displayDate").html('<label>Displaying <span id="poisDateLabel"></span></label>');

	// $('.campaign_type_column select').change(function()
 //    {
 //        setTimeout(function () {
 //         $('#refreshPageOptInRateStatsBtn').focus();
 //        }, 100);
 //    });

    $(document).on('click', '#refreshPageOptInRateStatsBtn', function(){
        console.log('Refresh');
        optin_table.clear();
        optin_table.ajax.reload();
    });

    $('#pageOptInRate-form').submit(function(e)
    {
        e.preventDefault();
        $('#downloadPageOptInRateReport').attr('disabled', true);

        optin_table.order([]);
        optin_table.ajax.reload();
    });

    $('#generateReportBtn').click(function(e) {
        e.preventDefault();
        var from_date = $('#date_from').val(),
            to_date = $('#date_to').val(),
            form = $('#pageOptInRate-form');

        $('label[for="date_from"]').removeClass('error_label error');
        $('#date_from').removeClass('error_field error');
        $('label[for="date_to"]').removeClass('error_label error');
        $('#date_to').removeClass('error_field error');

        if(from_date == '' || to_date == '') {
            form.submit();
        }
        else if(from_date != '' && to_date != '' && to_date >= from_date) {
            $('label[for="date_from"]').removeClass('error_label error');
            $('#date_from').removeClass('error_field error');
            $('label[for="date_to"]').removeClass('error_label error');
            $('#date_to').removeClass('error_field error');
            form.submit();
        }else {
            $('label[for="date_from"]').addClass('error_label error');
            $('#date_from').addClass('error_field error');
            $('label[for="date_to"]').addClass('error_label error');
            $('#date_to').addClass('error_field error');
        }
    });

    $('#affiliate_id').select2({
        //tags: true,
        placeholder: 'Select the id or name of the affiliate.',
        minimumInputLength: 1,
        theme: 'bootstrap',
        language: {
            inputTooShort: function(args) {
                return "Please enter the id or name of the affiliate. (Enter 1 or more characters)";
            }
        },
        ajax: {
            url: $('#baseUrl').html()+'/search/select/activeAffiliatesIDName',
            dataType: "json",
            type: "POST",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                };

                return queryParameters;
            },
            processResults: function (data) {
                // console.log(data);
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    $('#clear').click(function()
    {
        $('.input-group.date input').val('');
        $('#affiliate_id').val('').trigger('change');
        $('#group_by').val('day');
    });

    $('#remove_affiliate_id_selections').click(function(){
        // Clear the ID selections
        $('#affiliate_id').val(null).trigger('change');
    });

    $('#date_range').change(function() {
        if($(this).val() != '') {
            $('#date_from').val('');
            $('#date_to').val('');
        }
    });

    $('.lead_date').change(function() {
        if($(this).val() != '') {
            $('#date_range').val('');
        }
    });

    $(document).on('click', '#updateBenchmarkBtn', function(){
        optin_table.clear();
        optin_table.ajax.reload();
        $('#benchmarkModal').modal('hide');
    });

    $('#benchmarkModal').on('hide.bs.modal', function (event) 
    {
        $.each(displayed_benchmarks, function(type, benchmarks) {
            if($.inArray('all', benchmarks) >= 0) {
                benchmarks = ['all'];
            }
            $('[name="benchmark['+type+'][]"]').val(benchmarks).trigger('change');
        });
    });

    $(document).on('click', '#selectAllBenchmarkBtn', function(e){
        e.preventDefault();
        $('.campaign_type_benchmark').each(function(index, element) {
            $(this).val('all').trigger('change');
        });
    });

    $('#group_by').change(function()
    {
        if($(this).val() != 'day') {
            $('.sibs').prop('checked', true).attr('disabled', true);
        }else {
            $('.sibs').removeAttr('disabled');
        }
    });
});