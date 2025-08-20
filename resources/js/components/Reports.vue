<template>
  <b-container fluid>
    <h2>Sales Report</h2>
    <b-card class="mb-3">
      <b-form @submit.prevent="load">
        <b-row class="g-2 align-items-end">
          <b-col sm="3">
            <b-form-group label="From">
              <b-form-input type="date" v-model="from" />
            </b-form-group>
          </b-col>
          <b-col sm="3">
            <b-form-group label="To">
              <b-form-input type="date" v-model="to" />
            </b-form-group>
          </b-col>
          <b-col sm="2">
            <b-button type="submit" variant="primary" block>Filter</b-button>
          </b-col>
        </b-row>
      </b-form>
    </b-card>
    <b-row>
      <b-col md="6"><b-card><div class="text-muted">Transactions</div><div class="h3 mb-0">{{ report.count }}</div></b-card></b-col>
      <b-col md="6"><b-card><div class="text-muted">Subtotal</div><div class="h3 mb-0">{{ report.subtotal }}</div></b-card></b-col>
    </b-row>
    <b-row class="mt-3">
      <b-col md="12"><b-card><div class="text-muted">Total</div><div class="display-6">{{ report.total }}</div></b-card></b-col>
    </b-row>
  </b-container>
</template>

<script>
export default {
  name: 'Reports',
  data() {
    return { from: '', to: '', report: null }
  },
  created() { this.load(); },
  methods: {
    async load() {
      const q = new URLSearchParams();
      if (this.from) q.set('from', this.from);
      if (this.to) q.set('to', this.to);
      const res = await fetch(`/api/reports/sales-summary?${q.toString()}`);
      this.report = await res.json();
    }
  }
}
</script>


