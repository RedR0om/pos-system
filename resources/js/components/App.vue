<template>
  <div class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i> <strong>Digitizing School Canteen Transactions: A POS-Based Solution for Efficient Payment Management</strong></a>
          </li>
        </ul>
        <ul class="navbar-nav ml-auto align-items-center">
          <li class="nav-item" v-if="user">
            <span class="nav-link">{{ user.name }} ({{ isAdmin ? 'Admin' : 'User' }})</span>
          </li>
          <li class="nav-item" v-if="user">
            <a class="nav-link" href="#" @click.prevent="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </li>
          <li class="nav-item" v-else>
            <router-link class="nav-link" to="/login"><i class="fas fa-sign-in-alt"></i> Login</router-link>
          </li>
        </ul>
      </nav>

      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
          <span class="brand-text font-weight-light">POS</span>
        </a>
        <div class="sidebar">
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
              <li class="nav-item" v-if="user"><router-link class="nav-link" exact to="/"><i class="nav-icon fas fa-home"></i><p>Dashboard</p></router-link></li>
              <li class="nav-item" v-if="user"><router-link class="nav-link" to="/pos"><i class="nav-icon fas fa-receipt"></i><p>POS</p></router-link></li>
              <li class="nav-item" v-if="user"><router-link class="nav-link" to="/sales"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Sales</p></router-link></li>
              <li class="nav-item" v-if="user && isAdmin"><router-link class="nav-link" to="/products"><i class="nav-icon fas fa-box"></i><p>Products</p></router-link></li>
              <li class="nav-item" v-if="user && isAdmin"><router-link class="nav-link" to="/inventory"><i class="nav-icon fas fa-warehouse"></i><p>Inventory</p></router-link></li>
              <li class="nav-item" v-if="user && isAdmin"><router-link class="nav-link" to="/reports"><i class="nav-icon fas fa-chart-line"></i><p>Reports</p></router-link></li>
              <li class="nav-item" v-if="user"><router-link class="nav-link" to="/account"><i class="nav-icon fas fa-user"></i><p>Account</p></router-link></li>

              <li class="nav-item" v-if="!user"><router-link class="nav-link" to="/login"><i class="nav-icon fas fa-sign-in-alt"></i><p>Login</p></router-link></li>
            </ul>
          </nav>
        </div>
      </aside>

      <div class="content-wrapper">
        <section class="content pt-3">
          <div class="container-fluid">
            <router-view />
          </div>
        </section>
      </div>

      <footer class="main-footer">
        <strong>Copyright &copy; {{ currentYear }} Digitizing School Canteen Transactions: A POS-Based Solution for Efficient Payment Management</strong>
        <div class="float-right d-none d-sm-inline-block">
          <b>Version</b> 1.0.3
        </div>
      </footer>
    </div>
  </div>
  </template>
  
  <script>
  export default {
    name: 'App',
    computed: {
      currentYear() { return new Date().getFullYear(); },
      user() { return this.$auth ? this.$auth.user : null; },
      isAdmin() { return this.user && this.user.role === 'admin'; }
    },
    methods: {
      logout() {
        // best-effort API logout; even if it fails, clear local state
        try { 
          if (this.$axios && this.$axios.post) {
            this.$axios.post('/api/logout'); 
          }
        } catch (_) {}
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        if (this.$auth) { this.$auth.user = null; this.$auth.token = null; }
        if (this.$axios && this.$axios.defaults && this.$axios.defaults.headers && this.$axios.defaults.headers.common) {
          delete this.$axios.defaults.headers.common.Authorization;
        }
        this.$router.replace('/login');
      }
    }
  }
  </script>

  <style scoped>
  .brand-link { text-align: center; }
  .nav-link.router-link-exact-active { background-color: #4f5962 !important; color: #fff !important; }
  .nav-link.router-link-active { background-color: #4f5962 !important; color: #fff !important; }
  </style>