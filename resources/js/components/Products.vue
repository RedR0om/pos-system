<template>
  <b-container fluid>
    <b-row class="mb-3 align-items-center">
      <b-col><h2 class="mb-0">Products</h2></b-col>
      <b-col cols="auto"><b-button variant="primary" @click="openNew">New Product</b-button></b-col>
    </b-row>

    <b-modal id="product-modal" :title="form.id ? 'Edit Product' : 'New Product'" hide-footer @hidden="resetForm">
      <b-form @submit.prevent="save" enctype="multipart/form-data">
        <b-form-group label="Image">
          <b-form-file v-model="form.image_file" accept="image/*" placeholder="Choose image..." browse-text="Browse" />
        </b-form-group>
        <b-form-group label="SKU" label-for="sku">
          <b-form-input id="sku" v-model="form.sku" required placeholder="e.g. BUR-001" />
        </b-form-group>
        <b-form-group label="Name" label-for="name">
          <b-form-input id="name" v-model="form.name" required placeholder="e.g. Burger" />
        </b-form-group>
        <b-form-group label="Category">
          <b-form-select v-model="form.category_id" :options="categoryOptions" />
        </b-form-group>
        <b-form-group label="Price">
          <b-input-group>
            <b-input-group-prepend is-text>₱</b-input-group-prepend>
            <b-form-input v-model.number="form.price" type="number" min="0" step="0.01" required />
          </b-input-group>
        </b-form-group>
        <b-form-group label="Stock (optional)">
          <b-form-input v-model.number="form.stock" type="number" min="0" />
        </b-form-group>
        <b-form-group label="Description (optional)">
          <b-form-textarea v-model="form.description" rows="2" max-rows="4" placeholder="Short description" />
        </b-form-group>
        <div class="d-flex justify-content-end">
          <b-button variant="secondary" class="mr-2" @click="$bvModal.hide('product-modal')">Cancel</b-button>
          <b-button type="submit" variant="success">{{ form.id ? 'Update' : 'Save' }}</b-button>
        </div>
      </b-form>
    </b-modal>

    <b-card>
      <b-table small hover :items="products.data" :fields="fields" responsive="sm">
        <template #cell(price)="{ item }">{{ Number(item.price).toFixed(2) }}</template>
        <template #cell(actions)="{ item }">
          <b-button size="sm" variant="outline-primary" class="me-1" @click="edit(item)">Edit</b-button>
          <b-button size="sm" variant="outline-danger" @click="remove(item)">Delete</b-button>
        </template>
      </b-table>
    </b-card>
  </b-container>
  </template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
export default {
  name: 'Products',
  data() {
    return {
      products: { data: [] },
      form: { id: null, sku: '', name: '', category_id: null, price: 0, stock: 0, description: '', image_file: null },
      loading: false,
      fields: [
        { key: 'sku', label: 'SKU' },
        { key: 'name', label: 'Name' },
        { key: 'price', label: 'Price', class: 'text-end' },
        { key: 'stock', label: 'Stock', class: 'text-end' },
        { key: 'actions', label: '', class: 'text-end' },
      ],
      categoryOptions: [],
    }
  },
  created() {
    this.load();
    this.loadCategories();
  },
  methods: {
    async loadCategories() {
      try {
        const res = await axios.get('/api/categories');
        this.categoryOptions = [{ value: null, text: 'Uncategorized' }, ...res.data.map(c => ({ value: c.id, text: c.name }))];
      } catch (_) {}
    },
    load(page = 1) {
      axios.get('/api/products?page=' + page)
        .then(res => {
          this.products = res.data;
        })
        .catch(e => {
          console.error(e);
          const msg = (e.response && (e.response.data.message || JSON.stringify(e.response.data))) || e.message;
          alert('Failed to load products: ' + msg);
        });
    },
    edit(p) {
      this.form = { id: p.id, sku: p.sku, name: p.name, category_id: p.category_id || null, price: Number(p.price), stock: p.stock || 0, description: p.description || '', image_file: null };
      this.$bvModal.show('product-modal');
    },
    resetForm() {
      this.form = { id: null, sku: '', name: '', category_id: null, price: 0, stock: 0, description: '', image_file: null };
    },
    openNew() {
      this.resetForm();
      this.$bvModal.show('product-modal');
    },
    save() {
      this.loading = true;
      const formData = new FormData();
      const priceVal = isFinite(this.form.price) ? this.form.price : 0;
      const stockVal = isFinite(this.form.stock) ? this.form.stock : 0;
      formData.append('sku', this.form.sku);
      formData.append('name', this.form.name);
      if (this.form.category_id) formData.append('category_id', this.form.category_id);
      if (this.form.image_file) formData.append('image', this.form.image_file);
      formData.append('price', priceVal);
      formData.append('stock', stockVal);
      if (this.form.description) formData.append('description', this.form.description);
      const request = this.form.id
        ? axios.post('/api/products/' + this.form.id + '?_method=PUT', formData)
        : axios.post('/api/products', formData);

      request
        .then(() => {
          this.resetForm();
          Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false });
          this.$bvModal.hide('product-modal');
          return this.load();
        })
        .catch(e => {
          let msg = 'Save failed';
          if (e.response) {
            if (e.response.data && e.response.data.errors) {
              msg = Object.values(e.response.data.errors).flat().join('\n');
            } else if (e.response.data && e.response.data.message) {
              msg = e.response.data.message;
            } else {
              msg = e.message;
            }
          } else {
            msg = e.message;
          }
          Swal.fire({ icon: 'error', title: 'Save failed', text: msg });
        })
        .finally(() => {
          this.loading = false;
        });
    },
    remove(p) {
      Swal.fire({
        icon: 'warning',
        title: 'Delete product?',
        text: p.name,
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#d33'
      }).then(result => {
        if (!result.isConfirmed) return;
        axios.delete('/api/products/' + p.id)
          .then(() => {
            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1000, showConfirmButton: false });
            this.load();
          })
          .catch(e => {
            const msg = (e.response && (e.response.data.message || JSON.stringify(e.response.data))) || e.message;
            Swal.fire({ icon: 'error', title: 'Delete failed', text: msg });
          });
      });
    }
  }
}
</script>


