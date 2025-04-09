import './bootstrap';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

document.addEventListener('DOMContentLoaded', () => {
    const editorElement = document.querySelector('#editor');
    if (editorElement) {
        ClassicEditor
            .create(editorElement)
            .then(editor => {
                console.log('CKEditor initialized', editor);
            })
            .catch(error => {
                console.error('Error initializing CKEditor', error);
            });
    }
});