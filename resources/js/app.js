import './bootstrap'
import {createApp} from 'vue'
import AppTemplate from '../js/pages/AppTemplate.vue';
import { pinia } from '../js/stores';
/*
 * Vendors/Plugins
*/
//npm i @fortawesome/fontawesome-free@5.15.4
import { library } from '@fortawesome/fontawesome-svg-core'; /* import the fontawesome core | npm i @fortawesome/fontawesome-svg-core*/
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'; /* import font awesome icon component | npm i @fortawesome/vue-fontawesome*/
import { fas } from '@fortawesome/free-solid-svg-icons'; /* import entire style | npm i @fortawesome/free-solid-svg-icons*/
library.add(fas) /* add icons to the library */

//Vue Multi Select
//Multiselect, needs to pass reactive state of ARRAY, import vueselect with default css, check the data to the component by using console.log
import Multiselect from '@vueform/multiselect'; //npm i @vueform/multiselect

// import Multiselect from 'vue-multiselect' // I didnt use this, this is an object type of multiselect
import '@vueform/multiselect/themes/default.css'; //multiselect css is required
import 'vue-toast-notification/dist/theme-bootstrap.css';

// /* Startbootstrap-sb-admin template */
import "startbootstrap-sb-admin/dist/css/styles.css";
import "startbootstrap-sb-admin/dist/js/scripts.js";

import 'bootstrap/dist/css/bootstrap.min.css' //Need to run collapse // npm install bootstrap@v5.3.5
import "startbootstrap-sb-admin/dist/js/datatables-simple-demo.js"
/* Local JS extensions */
import router from "../js/routes";

// import DataTable from 'datatables.net-vue3';
// import DataTablesCore from 'datatables.net-bs5';
// DataTable.use(DataTablesCore)

createApp(AppTemplate)
.use(pinia)
.use(router)
.component('font-awesome-icon',FontAwesomeIcon)
.component('Multiselect',Multiselect)
.mount('#app');
