<template>
  <b-container fluid>
    <b-row class="mb-3 align-items-center">
      <b-col><h2 class="mb-0">User Management</h2></b-col>
      <b-col cols="auto" v-if="isAdmin">
        <b-button variant="success" @click="openCreateModal">
          <i class="fas fa-plus"></i> Create User
        </b-button>
      </b-col>
    </b-row>





    <!-- User Management (Admin Only) -->
    <b-card v-if="isAdmin">
      <h5 class="card-title">All Users</h5>
      <b-table 
        small 
        hover 
        :items="users" 
        :fields="userFields"
        responsive="sm"
        striped
        show-empty
      >
        <template #cell(role)="{ item }">
          <b-badge :variant="item.role === 'admin' ? 'danger' : 'info'">
            {{ item.role === 'admin' ? 'Admin' : 'Cashier' }}
          </b-badge>
        </template>
        <template #cell(status)="{ item }">
          <b-badge :variant="item.status === 'active' ? 'success' : 'warning'">
            {{ item.status === 'active' ? 'Active' : 'Inactive' }}
          </b-badge>
        </template>
        <template #cell(created_at)="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
        <template #cell(actions)="{ item }">
          <div class="d-flex gap-1">
            <b-button 
              size="sm" 
              variant="outline-primary" 
              @click="editUser(item)"
            >
              <i class="fas fa-edit"></i>
            </b-button>
            <b-button 
              size="sm" 
              variant="outline-warning" 
              @click="toggleUserStatus(item)"
            >
              <i :class="item.status === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
            </b-button>
            <b-button 
              size="sm" 
              variant="outline-danger" 
              @click="deleteUser(item)"
            >
              <i class="fas fa-trash"></i>
            </b-button>
          </div>
        </template>
        <template #empty>
          <div class="text-center text-muted py-4">
            <i class="fas fa-users fa-3x mb-3"></i>
            <p>No users found</p>
          </div>
        </template>
      </b-table>
    </b-card>

    <!-- Non-Admin Message -->
    <b-card v-else>
      <div class="text-center text-muted py-5">
        <i class="fas fa-lock fa-3x mb-3"></i>
        <h5>Access Restricted</h5>
        <p>Only administrators can manage user accounts.</p>
      </div>
    </b-card>

    <!-- Create/Edit User Modal -->
    <b-modal 
      :id="userModal.id" 
      :title="userModal.title" 
      hide-footer 
      size="lg"
      @hidden="resetUserForm"
    >
      <b-form @submit.prevent="saveUser">
        <b-row>
          <b-col sm="6">
            <b-form-group label="Name" label-for="user-name">
              <b-form-input 
                id="user-name"
                v-model="userForm.name" 
                required 
                placeholder="Enter full name"
              />
            </b-form-group>
          </b-col>
          <b-col sm="6">
            <b-form-group label="Email" label-for="user-email">
              <b-form-input 
                id="user-email"
                v-model="userForm.email" 
                type="email" 
                required 
                placeholder="Enter email address"
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col sm="6">
            <b-form-group label="Role" label-for="user-role">
              <b-form-select 
                id="user-role"
                v-model="userForm.role" 
                :options="roleOptions"
                required
              />
            </b-form-group>
          </b-col>
          <b-col sm="6">
            <b-form-group label="Status" label-for="user-status">
              <b-form-select 
                id="user-status"
                v-model="userForm.status" 
                :options="statusOptions"
                required
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row v-if="!userForm.id">
          <b-col sm="6">
            <b-form-group label="Password" label-for="user-password">
              <b-form-input 
                id="user-password"
                v-model="userForm.password" 
                type="password" 
                required 
                placeholder="Enter password"
              />
            </b-form-group>
          </b-col>
          <b-col sm="6">
            <b-form-group label="Confirm Password" label-for="user-confirm-password">
              <b-form-input 
                id="user-confirm-password"
                v-model="userForm.confirm_password" 
                type="password" 
                required 
                placeholder="Confirm password"
              />
            </b-form-group>
          </b-col>
        </b-row>
        
        <div class="d-flex justify-content-end gap-2 mt-3">
          <b-button variant="secondary" @click="$bvModal.hide(userModal.id)">
            Cancel
          </b-button>
          <b-button type="submit" variant="success">
            <i class="fas fa-save"></i> {{ userForm.id ? 'Update' : 'Create' }}
          </b-button>
        </div>
      </b-form>
    </b-modal>
  </b-container>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'Account',
  data() {
    return {
      users: [],
      userForm: {
        id: null,
        name: '',
        email: '',
        role: 'cashier',
        status: 'active',
        password: '',
        confirm_password: ''
      },
      userModal: {
        id: 'user-modal',
        title: 'Create User'
      },
      userFields: [
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'role', label: 'Role', class: 'text-center' },
        { key: 'status', label: 'Status', class: 'text-center' },
        { key: 'created_at', label: 'Created' },
        { key: 'actions', label: 'Actions', class: 'text-center' }
      ],
      roleOptions: [
        { value: 'admin', text: 'Administrator' },
        { value: 'cashier', text: 'Cashier' }
      ],
      statusOptions: [
        { value: 'active', text: 'Active' },
        { value: 'inactive', text: 'Inactive' }
      ]
    }
  },
  computed: {
    isAdmin() {
      return this.$auth && this.$auth.user && this.$auth.user.role === 'admin';
    }
  },
  created() {
    if (this.isAdmin) {
      this.loadUsers();
    }
  },
  methods: {
    async loadUsers() {
      try {
        const res = await axios.get('/api/users');
        this.users = res.data.data || [];
      } catch (e) {
        console.error('Failed to load users:', e);
        Swal.fire({
          icon: 'error',
          title: 'Load Failed',
          text: 'Failed to load users'
        });
      }
    },

    openCreateModal() {
      this.userModal.title = 'Create User';
      this.userModal.id = 'user-modal';
      this.resetUserForm();
      this.$bvModal.show('user-modal');
    },

    editUser(user) {
      this.userModal.title = 'Edit User';
      this.userModal.id = 'user-modal';
      this.userForm = {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
        status: user.status,
        password: '',
        confirm_password: ''
      };
      this.$bvModal.show('user-modal');
    },

    async saveUser() {
      if (!this.userForm.id && this.userForm.password !== this.userForm.confirm_password) {
        Swal.fire({
          icon: 'error',
          title: 'Password Mismatch',
          text: 'Password and confirm password do not match'
        });
        return;
      }

      try {
        if (this.userForm.id) {
          // Update user
          await axios.put(`/api/users/${this.userForm.id}`, {
            name: this.userForm.name,
            email: this.userForm.email,
            role: this.userForm.role,
            status: this.userForm.status
          });
        } else {
          // Create user
          await axios.post('/api/users', {
            name: this.userForm.name,
            email: this.userForm.email,
            role: this.userForm.role,
            status: this.userForm.status,
            password: this.userForm.password
          });
        }

        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: `User ${this.userForm.id ? 'updated' : 'created'} successfully`
        });

        this.$bvModal.hide('user-modal');
        this.loadUsers();
      } catch (e) {
        console.error('Failed to save user:', e);
        Swal.fire({
          icon: 'error',
          title: 'Save Failed',
          text: (e.response && e.response.data && e.response.data.message) || 'Failed to save user'
        });
      }
    },

    async toggleUserStatus(user) {
      const newStatus = user.status === 'active' ? 'inactive' : 'active';
      const action = newStatus === 'active' ? 'activate' : 'deactivate';

      try {
        await axios.put(`/api/users/${user.id}/status`, {
          status: newStatus
        });

        Swal.fire({
          icon: 'success',
          title: 'Status Updated',
          text: `User ${action}d successfully`
        });

        this.loadUsers();
      } catch (e) {
        console.error('Failed to update user status:', e);
        Swal.fire({
          icon: 'error',
          title: 'Update Failed',
          text: 'Failed to update user status'
        });
      }
    },

    async deleteUser(user) {
      const result = await Swal.fire({
        icon: 'warning',
        title: 'Delete User',
        text: `Are you sure you want to delete ${user.name}? This action cannot be undone.`,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete!'
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/api/users/${user.id}`);

          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'User has been deleted successfully'
          });

          this.loadUsers();
        } catch (e) {
          console.error('Failed to delete user:', e);
          Swal.fire({
            icon: 'error',
            title: 'Delete Failed',
            text: 'Failed to delete user'
          });
        }
      }
    },

    resetUserForm() {
      this.userForm = {
        id: null,
        name: '',
        email: '',
        role: 'cashier',
        status: 'active',
        password: '',
        confirm_password: ''
      };
    },

    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleDateString();
    }
  }
}
</script>

<style scoped>
.card-title {
  color: #374151;
  font-weight: 600;
  margin-bottom: 1rem;
}

.text-muted {
  color: #6b7280 !important;
}
</style>


