<template>
  <b-container fluid>
    <h2>Admin Panel</h2>
    <b-card>
      <b-list-group>
        <b-list-group-item><router-link to="/products">Manage Products</router-link></b-list-group-item>
        <b-list-group-item><router-link to="/reports">Sales Reports</router-link></b-list-group-item>
        <b-list-group-item><router-link to="/inventory">Inventory</router-link></b-list-group-item>
      </b-list-group>
    </b-card>
  </b-container>
</template>

<script>
export default { name: 'Admin' }
</script>


