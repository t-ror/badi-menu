// jquery
import jQuery from "jquery";
window.$ = window.jQuery = jQuery;

// Vue
import Vue from 'vue';
import MealTagList from "./components/MealTagList.vue";
import FlashesVue from "./components/Flashes.vue";
export const eventBus = new Vue();

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
import PreventDoubleSubmit from './js/PreventDoubleSubmit';

$(document).ready(function() {
	Flashes.init();
	QuillEditor.init();
	EmbedForm.init();
	Select2.init();
	PreventDoubleSubmit.init();

	if ($('#flashes').length) {
		new Vue({
			el: '#flashes',
			components: { 'flashes': FlashesVue }
		});
	}

	if ($('#mealTagList').length) {
		new Vue({
			el: '#mealTagList',
			components: { 'vue-meal-tag-list': MealTagList }
		});
	}
});