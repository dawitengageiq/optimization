require('./utils/common');
require('./utils/chart');

$(function () {

    $('#legends').prop('multiple', true);

    // Since single select will add attribute:selected to only one option
    // We need to add selected attrtibutr to other options as mupltiple select
    $('#legends option', this).each(function() {
        if($(this).attr('data-selected') == 'true') $(this).prop('selected', true);
    });

    // Instantiate select 2
    $('#revenue_tracker_selection').select2({
        theme: 'bootstrap'
    });

    $('#legends').multiselect({
        inheritClass: true,
        enableFiltering: true,
        includeSelectAllOption: true,
         // buttonWidth: '600px',
        selectAllText: 'Select all to be the column of table!',
         nonSelectedText: 'Select a column!',
        selectAllValue: '',
        enableCaseInsensitiveFiltering: true,
        disableIfEmpty: true,
        maxHeight: 300
    });
});

// module.exports here (in the entrypoint module) is the same object
// as ui in the page's scope (outside webpack)
export default {

};
