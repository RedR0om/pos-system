<template>
  <b-container fluid>
    <b-row>
      <b-col md="4">
        <b-card class="mb-3" body-class="py-3">
          <div class="text-muted">Total Sales</div>
          <div class="display-6">{{ Number(summary.total).toFixed(2) }}</div>
        </b-card>
      </b-col>
      <b-col md="4">
        <b-card class="mb-3" body-class="py-3">
          <div class="text-muted">Transactions</div>
          <div class="display-6">{{ summary.count }}</div>
        </b-card>
      </b-col>
      
    </b-row>
    <b-row>
      <b-col md="8">
        <b-card title="Sales - Last 7 days">
          <canvas ref="bar"></canvas>
        </b-card>
      </b-col>
      <b-col md="4">
        <b-card title="Sales by Category (30d)">
          <canvas ref="pie"></canvas>
        </b-card>
      </b-col>
    </b-row>
    <b-row class="mt-3">
      <b-col md="6">
        <b-card title="Inventory">
          <div class="d-flex justify-content-between"><span>Total Products</span><strong>{{ extra.inventory.total_products }}</strong></div>
          <div class="d-flex justify-content-between"><span>Low Stock (&le;5)</span><strong>{{ extra.inventory.low_stock }}</strong></div>
          <div class="d-flex justify-content-between"><span>Categories</span><strong>{{ extra.inventory.total_categories }}</strong></div>
          <div class="d-flex justify-content-between"><span>Stock on Hand</span><strong>{{ extra.inventory.stock_on_hand }}</strong></div>
          <div class="d-flex justify-content-between"><span>Stock Value</span><strong>{{ Number(extra.inventory.stock_value).toFixed(2) }}</strong></div>
          <div class="d-flex justify-content-between"><span>Movements (7d)</span><strong>In: {{ extra.inventory.movements_last7.in }} | Out: {{ extra.inventory.movements_last7.out }}</strong></div>
        </b-card>
      </b-col>
      <b-col md="6">
        <b-card no-body>
          <template #header>
            Recent Sales
          </template>
          <b-card-body>
            <ul class="list-unstyled mb-0">
              <li v-for="s in extra.sales.recent" :key="s.id" class="d-flex justify-content-between">
                <span>{{ firstItemName(s) }}</span>
                <span>{{ Number(s.total).toFixed(2) }} • {{ formatDate(s.paid_at) }}</span>
              </li>
            </ul>
          </b-card-body>
        </b-card>
        <b-card no-body class="mt-3">
          <template #header>
            Top Products (30d)
          </template>
          <b-card-body>
            <ul class="list-unstyled mb-0">
              <li v-for="p in extra.sales.top_products" :key="p.name" class="d-flex justify-content-between">
                <span>{{ p.name }}</span>
                <span>{{ p.qty }} pcs • {{ Number(p.total).toFixed(2) }}</span>
              </li>
            </ul>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </b-container>
  </template>

<script>
import Chart from 'chart.js/auto'
export default {
  name: 'Dashboard',
  data() {
    return {
      summary: { total: 0, count: 0, tax: 0 },
      charts: { bar: null, pie: null },
      extra: { inventory: { total_products: 0, low_stock: 0, total_categories: 0, stock_on_hand: 0, stock_value: 0, movements_last7: { in: 0, out: 0 } }, sales: { recent: [], top_products: [] } }
    }
  },
  async created() {
    try {
      const res = await fetch('/api/reports/sales-summary');
      this.summary = await res.json();
    } catch (e) {}
    this.loadCharts()
    this.loadSummary()
  },
  methods: {
    async loadCharts() {
      const [byDayRes, byCatRes] = await Promise.all([
        fetch('/api/reports/sales-by-day'),
        fetch('/api/reports/sales-by-category')
      ])
      const byDay = await byDayRes.json()
      const byCat = await byCatRes.json()

      if (this.charts.bar) this.charts.bar.destroy()
      this.charts.bar = new Chart(this.$refs.bar.getContext('2d'), {
        type: 'bar',
        data: { labels: byDay.labels, datasets: [{ label: 'Sales', data: byDay.data, backgroundColor: '#4e73df' }] },
        options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
      })

      if (this.charts.pie) this.charts.pie.destroy()
      this.charts.pie = new Chart(this.$refs.pie.getContext('2d'), {
        type: 'pie',
        data: { labels: byCat.labels, datasets: [{ data: byCat.data, backgroundColor: ['#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#4e73df'] }] },
        options: { plugins: { legend: { position: 'bottom' } } }
      })
    },
    async loadSummary() {
      try {
        const res = await fetch('/api/reports/dashboard-summary')
        const d = await res.json()
        this.extra = d
      } catch (_) {}
    },
    firstItemName(sale) {
      if (sale.items && sale.items.length && sale.items[0].product) {
        return sale.items[0].product.name
      }
      return '#' + sale.id
    },
    formatDate(dt) {
      try {
        const d = new Date(dt)
        const y = d.getFullYear()
        const m = String(d.getMonth() + 1).padStart(2, '0')
        const day = String(d.getDate()).padStart(2, '0')
        return `${y}-${m}-${day}`
      } catch (_) {
        return dt
      }
    }
  }
}
</script>


