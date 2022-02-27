import Quill from 'quill';

export default class QuillEditor {

    static init() {
        $('.app_quil-editor').each(function(i) {
            let element = $(this);
            let id = 'app_quil-editor-' + i;
            let val = element.val();
            let editorHeight = element.data('quill-height');
            let div = $('<div/>').attr('id', id).css('height', editorHeight + 'px').html(val);
            element.addClass('d-none');
            element.parent().append(div);

            let quill = new Quill('#' + id, {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                    ]
                },
                theme: 'snow'
            });

            let maxLength = element.attr('maxlength');
            if (typeof maxLength !== 'undefined' && maxLength !== false) {
                let divCounter = $('<div/>').attr('id', id + '-counter')
                    .css('color', 'grey')
                    .html((quill.getLength() - 1) + ' / ' + maxLength);
                element.parent().append(divCounter);
            }

            quill.on('text-change', function() {
                if (typeof maxLength !== 'undefined' && maxLength !== false) {
                    if (quill.getLength() > maxLength) {
                        quill.deleteText(maxLength, quill.getLength());
                    }

                    $('#' + id + '-counter').html((quill.getLength() - 1) + ' / ' + maxLength);
                }

                element.html(quill.root.innerHTML);
            });
        });
    }

}