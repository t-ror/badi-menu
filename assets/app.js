// jquery
import jQuery from "jquery";
window.$ = window.jQuery = jQuery;

// CSS
import './styles/app.css';

//bootstrap
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.min.js';
import 'bootstrap-icons/font/bootstrap-icons.css'

//custom JS
import Flashes from './js/Flashes.js';

$(document).ready(function() {
    Flashes.init();
});
