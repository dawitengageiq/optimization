require('./utils/common');

import {dataTable} from './utils/table';


$(function () {

    // Select 2
    // Make it a multiple select for it was used in other tab as single select
    $('#legends, #revenue_tracker_selection').prop('multiple', true);

    // Since single select will add attribute:selected to only one option
    // We need to add selected attrtibute to other options as mupltiple select
    $('#legends option, #revenue_tracker_selection option', this).each(function() {
        if($(this).attr('data-selected') == 'true') $(this).prop('selected', true);
    });

    // Option "All": disable and pre selected
    $('#selection_all').prop('disabled', true).prop('selected', true);

    // Instantiate select 2 for affilaites selections
    $('#revenue_tracker_selection').select2({
        // minimumInputLength: 1,
        theme: 'bootstrap',
        closeOnSelect:false
    });

    // Instantiate select 2 for predefine dates selections
    $('#predefine_dates').select2({
        // minimumInputLength: 1,
        theme: 'bootstrap',
        closeOnSelect:true,
        // language: {
        //      noResults: function() {
        //          return "<a href='http://google.com'>Add</a>";
        //     }
        // },
        escapeMarkup: function (markup) {
            return markup;
        }
    });
    // $('#spredefine_dates').select2('container').append('<hr style="margin:5px"><a href="javascript:void(0)" onclick="add_new_option()"><img src="images/plus.png"/> Add New</a>');

    // Instantiate multiple select for legends selections
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

    // Remove the option "All" from selection
    $('#revenue_tracker_selection_wrap .select2-selection__rendered [title="ALL"] span').remove();

    // Remove the option "All" from selection on change ('select2') event.
    // On change events, the selection was populated again including the option "All"
    $('#revenue_tracker_selection').on('select2:select', function (e) {
       $('#revenue_tracker_selection_wrap .select2-selection__rendered [title="ALL"]').remove();
    });

    // Remove the option "All" from selection on change ('unselect') event.
    $('#revenue_tracker_selection').on('select2:unselect', function (e) {
        // Only remove when the slection option is not empty.
       if($('#revenue_tracker_selection_wrap .select2-selection__rendered li').length > 2) $('#revenue_tracker_selection_wrap .select2-selection__rendered [title="ALL"]').remove();
       // do not remove the option "All" but remove the "x" button
       else $('#revenue_tracker_selection_wrap .select2-selection__rendered [title="ALL"] span').remove();
    });

    // remove required in select
    $('#revenue_tracker_selection').prop('required', false);

    // Clear affiliates selection
    $("#clear_affiliates").click(function(){
        $('#revenue_tracker_selection').val(null).trigger('change');
        $('#revenue_tracker_selection_wrap .select2-selection__rendered [title="ALL"] span').remove();
    });

    // Functions when pre define dates was change
    $('#predefine_dates').change(function() {
        if($(this).val() == '') {
            $('#date_picker_wrap, .date_picker_wrap').show();
            $('#date_from, #date_to').prop('required', true);
        } else {
            $('#date_picker_wrap, .date_picker_wrap').hide();
            $('#date_from, #date_to').prop('required', false);
        }
    });
});



// module.exports here (in the entrypoint module) is the same object
// as ui in the page's scope (outside webpack)
export default {
    toDataTable: dataTable,
};
