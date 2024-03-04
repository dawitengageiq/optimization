$(document).ready(function()
{
    $('#user_id').select2({
        theme: 'bootstrap'
    });

    $('#section').select2({
        theme: 'bootstrap'
    });

    $('#sub_section_id').select2({
        theme: 'bootstrap'
    });

    $('#severity_id').select2({
        theme: 'bootstrap'
    });

    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    var baseURL = $('#baseUrl');

    var userActionHistoryReportURL = baseURL.html()+'/admin/userActionHistoryReport';

    var historyDataTable = $('#history-table').DataTable({
        'processing': true,
        'serverSide': true,
        'searching': true,
        language: {
            searchPlaceholder: 'ID, User, Reference ID or Summary'
        },
        'order': [[ 0, "asc" ]], //disable the initial ordering
        'ajax':{
            url: userActionHistoryReportURL,
            type: 'POST',  // method  , by default get
            'data': function(d)
            {
                d.user_id = $('#user_id').val();
                // Extract the section and sub section id
                var section = $('#section').val();
                console.log(section);

                var sectionID = null;
                var subSectionID = null;

                if(section !== '')
                {
                    var sectionIDs = section.split(' ');

                    if(sectionIDs.length > 1)
                    {
                        sectionID = sectionIDs[0];
                        subSectionID = sectionIDs[1];
                    }
                    else if(sectionIDs.length === 1)
                    {
                        sectionID = sectionIDs[0];
                    }
                }

                d.section_id = sectionID;
                d.sub_section_id = subSectionID;

                d.change_severity = $('#change_severity').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            },
            error: function()
            {
                // error handling
            },
            'dataSrc': function(json)
            {
                return json.data;
            }
        },
        'columns':[
            {
                'data':'id',
                'width': '5%'
            },
            {'data':'user'},
            {
                'data':'section',
                'orderable': false
            },
            {
                'data':'sub_section',
                'orderable': false
            },
            {
                'data': 'reference_id',
                'width': '10%'
            },
            {'data':'summary'},
            {'data':'severity'},
            {'data':'datetime'},
            {
                'data': 'details',
                'width': '5%',
                'orderable': false
            }
        ],
        //'initComplete': rowInitComplete,
        lengthMenu: [[25,50,100,-1],[25,50,100,'ALL']]
    });

    $('#view_logs').click(function()
    {
        historyDataTable.ajax.reload();
    });

    $('#clear').click(function()
    {
        $('#date_from').val('');
        $('#date_to').val('');
        $('#user_id').val('').trigger('change');
        $('#section').val('').trigger('change');
        $('#severity_id').val('').trigger('change');

        //clear the results as well
        historyDataTable.ajax.reload();
    });

    $(document).on('click', '.show-details', function()
    {
        var details = $(this);

        var logID = details.data('id');
        var section = details.data('section');
        var subSection = details.data('subsection');
        var referenceID = details.data('refid');
        var userFullName = details.data('fullname');
        var severity = details.data('severity');
        var summary = convertHTMLEntity(details.data('summary'));
        var logDate = convertHTMLEntity(details.data('date'));

        if(typeof(details.data('oldvalue')) == 'object') {
            oldV = JSON.stringify(details.data('oldvalue'));
        }else oldV = details.data('oldvalue');

        if(typeof(details.data('newvalue')) == 'object') {
            newV = JSON.stringify(details.data('newvalue'));
        }else newV = details.data('newvalue');

        var oldValue = convertHTMLEntity(oldV);
        var newValue = convertHTMLEntity(newV);

        // Fill in the needed modal data fields
        $('.modal-title').html(userFullName + ' | ' + logDate);
        $('#id-value').html(logID);
        $('#section-value').html(section);
        $('#sub-section-value').html(subSection);
        $('#reference-id-value').html(referenceID);
        $('#user-value').html(userFullName);
        $('#severity-value').html(severity);
        $('#summary-value').html(summary);

        $('#old-value-textarea').val(oldValue);
        $('#new-value-textarea').val(newValue);

        console.log(oldValue);
        console.log(newValue);
        // if(oldValue !== '' && newValue !== '')
        // {
             diffUsingJS(0);
        // }
        // else
        // {
        //     // remove the table diff
        //     $('#diffoutput > table.diff:first').remove();
        // }

        // show the modal
        $('#details-modal').modal('show');
    });

});

function diffUsingJS(viewType) {
    'use strict';

    var byId = function (id) { return document.getElementById(id); },
        // base = difflib.stringAsLines(byId("baseText").value),
        // newtxt = difflib.stringAsLines(byId("newText").value),
        base = difflib.stringAsLines(byId('old-value-textarea').value),
        newtxt = difflib.stringAsLines(byId('new-value-textarea').value),

        sm = new difflib.SequenceMatcher(base, newtxt),

        opcodes = sm.get_opcodes(),
        diffoutputdiv = byId('diffoutput');
        // contextSize = byId('contextSize').value;

    diffoutputdiv.innerHTML = "";
    // contextSize = contextSize || null;

    diffoutputdiv.appendChild(diffview.buildView({
        baseTextLines: base,
        newTextLines: newtxt,
        opcodes: opcodes,
        baseTextName: 'Old Value',
        newTextName: 'New Value',
        // contextSize: '100',
        viewType: viewType
    }));
}

function convertHTMLEntity(text)
{
    var span = document.createElement('span');

    return text.toString()
        .replace(/&[#A-Za-z0-9]+;/gi, function(entity,position,text) {
        span.innerHTML = entity;
        return span.innerText;
    });
}