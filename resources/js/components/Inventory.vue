<template>
  <b-container fluid>
    <h2>Inventory</h2>
    <b-card class="mb-3">
      <b-form @submit.prevent="adjust">
        <b-row class="g-2">
          <b-col sm="3"><b-form-input v-model.number="form.product_id" placeholder="Product ID" type="number" min="1" required /></b-col>
          <b-col sm="2"><b-form-select v-model="form.type" :options="typeOptions" /></b-col>
          <b-col sm="2"><b-form-input v-model.number="form.quantity" placeholder="Qty" type="number" min="1" required /></b-col>
          <b-col sm="3"><b-form-input v-model="form.reason" placeholder="Reason" /></b-col>
          <b-col sm="2"><b-button type="submit" variant="primary" block>Apply</b-button></b-col>
        </b-row>
      </b-form>
    </b-card>
    <b-card>
      <b-table small hover :items="movements.data" :fields="fields">
        <template #cell(product)="{ item }">{{ (item.product && item.product.name) || item.product_id }}</template>
      </b-table>
    </b-card>
  </b-container>
</template>

<script>
export default {
  name: 'Inventory',
  data() {
    return {
      movements: { data: [] },
      form: { product_id: null, type: 'in', quantity: 1, reason: '' },
      typeOptions: [
        { value: 'in', text: 'In' },
        { value: 'out', text: 'Out' },
      ],
      fields: [
        { key: 'type', label: 'Type' },
        { key: 'product', label: 'Product' },
        { key: 'quantity', label: 'Qty' },
        { key: 'reason', label: 'Reason' },
        { key: 'created_at', label: 'Date' },
      ]
    }
  },
  created() { this.load(); },
  methods: {
    async load(page = 1) {
      const res = await fetch(`/api/inventory?page=${page}`);
      this.movements = await res.json();
    },
    async adjust() {
      await fetch('/api/inventory/adjust', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(this.form) });
      this.form = { product_id: null, type: 'in', quantity: 1, reason: '' };
      this.load();
    }
  }
}
</script>


