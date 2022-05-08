// jquery
import jQuery from "jquery";
window.$ = window.jQuery = jQuery;

// CSS
import './styles/app.css';

//bootstrap
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.min.js';
import 'bootstrap-icons/font/bootstrap-icons.css';

//libs
import '@fortawesome/fontawesome-free/css/all.css';

//custom JS
import Flashes from './js/Flashes.js';
import QuillEditor from './js/QuillEditor.js';
import EmbedForm from './js/EmbedForm';
import Select2 from './js/Select2';

$(document).ready(function() {
    Flashes.init();
    QuillEditor.init();
    EmbedForm.init();
    Select2.init();
});
