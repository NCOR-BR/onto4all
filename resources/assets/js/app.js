
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue';
window.Vue = Vue;

/**
 * Register Vue Components
 */

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('tutorial-editor', require('./components/TutorialEditor.vue').default);

// SweetAlert2 para alerts
import Swal from 'sweetalert2'
window.Swal = Swal;
Vue.prototype.$swal = Swal;

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// A instância Vue é criada nas views individuais quando necessário
// Isso permite melhor controle sobre qual elemento será montado
