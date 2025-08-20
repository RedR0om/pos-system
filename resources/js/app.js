require('./bootstrap');

import Vue from 'vue';
import App from './components/App.vue'; // Adjust the path to your main Vue component
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import router from './router'; // Import the router configuration
import { LayoutPlugin, ModalPlugin, CardPlugin, VBScrollspyPlugin, DropdownPlugin, TablePlugin } from 'bootstrap-vue';
// Import Bootstrap and BootstrapVue CSS files
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import 'admin-lte/dist/css/adminlte.min.css';
import 'admin-lte/plugins/fontawesome-free/css/all.min.css';

// AdminLTE requires jQuery and Bootstrap JS (v4)
import $ from 'jquery';
window.$ = window.jQuery = $;
import 'bootstrap/dist/js/bootstrap.bundle.js';
import 'admin-lte/dist/js/adminlte.min.js';

// Attach axios auth header from localStorage token if present
import axios from 'axios';
const savedToken = localStorage.getItem('token');
if (savedToken) {
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + savedToken;
}
Vue.prototype.$axios = axios;

// Simple reactive auth store
const savedUserStr = localStorage.getItem('user');
const savedUser = savedUserStr ? JSON.parse(savedUserStr) : null;
const auth = Vue.observable({ user: savedUser, token: savedToken || null });
Vue.prototype.$auth = auth;

// Use BootstrapVue and VueRouter
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(LayoutPlugin);
Vue.use(ModalPlugin);
Vue.use(CardPlugin);
Vue.use(VBScrollspyPlugin);
Vue.use(DropdownPlugin);
Vue.use(TablePlugin);

// Simple auth guard
router.beforeEach((to, from, next) => {
  const token = auth.token;
  const user = auth.user;
  if (to.matched.some(r => r.meta.requiresAuth)) {
    if (!token || !user) return next({ name: 'login' });
    if (to.matched.some(r => r.meta.adminOnly) && user.role !== 'admin') return next({ name: 'pos' });
  }
  if (to.name === 'login' && token && user) return next({ name: 'dashboard' });
  next();
});

// Create and mount Vue instance
new Vue({
  router,
  render: h => h(App)
}).$mount('#app');
