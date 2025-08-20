import Vue from 'vue';
import Router from 'vue-router';

// Admin/Account/Products/POS/Inventory/Reports placeholders
const Dashboard = () => import('../components/Dashboard.vue');
const Products = () => import('../components/Products.vue');
const Pos = () => import('../components/Pos.vue');
const Inventory = () => import('../components/Inventory.vue');
const Reports = () => import('../components/Reports.vue');
const Account = () => import('../components/Account.vue');
const Admin = () => import('../components/Admin.vue');
const Login = () => import('../components/Login.vue');
const Sales = () => import('../components/Sales.vue');

Vue.use(Router);

export default new Router({
  mode: 'history',
  routes: [
    { path: '/login', name: 'login', component: Login },
    { path: '/', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/dashboard', redirect: '/' },
    { path: '/products', name: 'products', component: Products, meta: { requiresAuth: true, adminOnly: true } },
    { path: '/pos', name: 'pos', component: Pos, meta: { requiresAuth: true } },
    { path: '/sales', name: 'sales', component: Sales, meta: { requiresAuth: true } },
    { path: '/inventory', name: 'inventory', component: Inventory, meta: { requiresAuth: true, adminOnly: true } },
    { path: '/reports', name: 'reports', component: Reports, meta: { requiresAuth: true, adminOnly: true } },
    { path: '/account', name: 'account', component: Account, meta: { requiresAuth: true } },
    { path: '/admin', name: 'admin', component: Admin, meta: { requiresAuth: true, adminOnly: true } },
  ]
});
