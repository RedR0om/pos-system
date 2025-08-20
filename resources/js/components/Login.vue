<template>
  <div class="hold-transition login-page">
    <b-row class="text-center" style="padding-bottom: 5rem;">
      <h4>Digitizing School Canteen Transactions: A POS-Based Solution for Efficient Payment Management</h4>
    </b-row>
    <div class="login-box">
      <div class="login-logo">
        <b>POS</b> Login
      </div>
      <div class="card">
        <div class="card-body login-card-body">
          <p class="login-box-msg">Sign in to start your session</p>
          <b-form @submit.prevent="submit">
            <div class="input-group mb-3">
              <b-form-input v-model="email" type="email" placeholder="Email" required />
              <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
              </div>
            </div>
            <div class="input-group mb-3">
              <b-form-input v-model="password" type="password" placeholder="Password" required />
              <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <b-button type="submit" variant="primary" block :disabled="loading">Sign In</b-button>
              </div>
            </div>
          </b-form>
        </div>
      </div>
    </div>
  </div>
 </template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
export default {
  name: 'Login',
  data() {
    return { email: '', password: '', loading: false }
  },
  methods: {
    submit() {
      this.loading = true
      axios.post('/api/login', { email: this.email, password: this.password })
        .then(res => {
          const { token, user } = res.data
          localStorage.setItem('token', token)
          localStorage.setItem('user', JSON.stringify(user))
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + token
          // update reactive auth store if present
          if (this.$auth) { this.$auth.user = user; this.$auth.token = token }
          this.$router.replace('/')
        })
        .catch(e => {
          const msg = (e.response && (e.response.data.message || JSON.stringify(e.response.data))) || e.message
          Swal.fire({ icon: 'error', title: 'Login failed', text: msg })
        })
        .finally(() => { this.loading = false })
    }
  }
}
</script>


