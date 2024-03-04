$(document).ready(function(){
    $('#cmp-posting-instruction').ckeditor({
        // uiColor: '#9AB8F3'
        removeButtons : 'Underline,Subscript,Superscript,Scayt,RemoveFormat,About,Copy,Paste,PasteText,PasteFromWord,Cut,Anchor'
    });

    CKEDITOR.config.readOnly = true;
    CKEDITOR.config.allowedContent = true;
    CKEDITOR.config.contentsCss = 'http://path.paidforresearch.com/dynamic_live/css/style.css';
    CKEDITOR.config.contentsCss = 'http://path.paidforresearch.com/dynamic_live/css/mobile.css';
    CKEDITOR.config.contentsCss = 'http://path.paidforresearch.com/dynamic_live/css/stack.css';

    // CKEDITOR.scriptLoader.load('http://path.paidforresearch.com/js/jquery.maskedinput.min.js');
    // CKEDITOR.scriptLoader.load('http://path.paidforresearch.com/js/jquery.validate.min.js');
    // CKEDITOR.scriptLoader.load('http://path.paidforresearch.com/js/additional-methods.min.js');
    // CKEDITOR.scriptLoader.load('http://path.paidforresearch.com/js/custom.js');

    // $('#cmp-posting-instruction').ckeditor(CKEDITOR.editorConfig);
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
        modal_this = this
        $(document).on('focusin.modal', function (e) {
            if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
                    // add whatever conditions you need here:
                &&
                !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
                modal_this.$element.focus()
            }
        })
    };

});