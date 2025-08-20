<template>
  <b-container fluid>
    <b-row>
      <b-col md="4">
        <b-card class="mb-3" body-class="py-3">
          <div class="text-muted">Total Sales</div>
          <div class="display-6">₱{{ Number(summary.total || 0).toFixed(2) }}</div>
        </b-card>
      </b-col>
      <b-col md="4">
        <b-card class="mb-3" body-class="py-3">
          <div class="text-muted">Transactions</div>
          <div class="display-6">{{ summary.count || 0 }}</div>
        </b-card>
      </b-col>
      <b-col md="4">
        <b-card class="mb-3" body-class="py-3">
          <div class="text-muted">Today's Sales</div>
          <div class="display-6">₱{{ Number((extra.sales && extra.sales.today && extra.sales.today.total) || 0).toFixed(2) }}</div>
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
          <div class="d-flex justify-content-between"><span>Total Products</span><strong>{{ (extra.inventory && extra.inventory.total_products) || 0 }}</strong></div>
          <div class="d-flex justify-content-between"><span>Low Stock (≤5)</span><strong>{{ (extra.inventory && extra.inventory.low_stock) || 0 }}</strong></div>
          <div class="d-flex justify-content-between"><span>Categories</span><strong>{{ (extra.inventory && extra.inventory.total_categories) || 0 }}</strong></div>
          <div class="d-flex justify-content-between"><span>Stock on Hand</span><strong>{{ (extra.inventory && extra.inventory.stock_on_hand) || 0 }}</strong></div>
          <div class="d-flex justify-content-between"><span>Stock Value</span><strong>₱{{ Number((extra.inventory && extra.inventory.stock_value) || 0).toFixed(2) }}</strong></div>
          <div class="d-flex justify-content-between"><span>Movements (7d)</span><strong>In: {{ (extra.inventory && extra.inventory.movements_last7 && extra.inventory.movements_last7.in) || 0 }} | Out: {{ (extra.inventory && extra.inventory.movements_last7 && extra.inventory.movements_last7.out) || 0 }}</strong></div>
        </b-card>
      </b-col>
      <b-col md="6">
        <b-card no-body>
          <template #header>
            Recent Sales
          </template>
          <b-card-body>
            <ul class="list-unstyled mb-0">
              <li v-for="s in (extra.sales && extra.sales.recent) || []" :key="s.id" class="d-flex justify-content-between">
                <span>{{ firstItemName(s) }}</span>
                <span>₱{{ Number(s.total || 0).toFixed(2) }} • {{ formatDate(s.created_at) }}</span>
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
              <li v-for="p in (extra.sales && extra.sales.top_products) || []" :key="p.name" class="d-flex justify-content-between">
                <span>{{ p.name }}</span>
                <span>{{ p.qty || 0 }} pcs • ₱{{ Number(p.total || 0).toFixed(2) }}</span>
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
import axios from 'axios'

export default {
  name: 'Dashboard',
  data() {
    return {
      summary: { total: 0, count: 0, subtotal: 0 },
      charts: { bar: null, pie: null },
      extra: { 
        inventory: { 
          total_products: 0, 
          low_stock: 0, 
          total_categories: 0, 
          stock_on_hand: 0, 
          stock_value: 0, 
          movements_last7: { in: 0, out: 0 } 
        }, 
        sales: { 
          paid_total: 0,
          today: { count: 0, total: 0 },
          recent: [], 
          top_products: [] 
        } 
      }
    }
  },
  async created() {
    try {
      const res = await axios.get('/api/reports/sales-summary');
      this.summary = res.data.data || { total: 0, count: 0, subtotal: 0 };
    } catch (e) {
      console.error('Failed to load sales summary:', e);
      this.summary = { total: 0, count: 0, subtotal: 0 };
    }
    this.loadCharts()
    this.loadSummary()
  },
  beforeDestroy() {
    // Clean up charts to prevent memory leaks
    if (this.charts.bar) {
      this.charts.bar.destroy();
    }
    if (this.charts.pie) {
      this.charts.pie.destroy();
    }
  },
  methods: {
    async loadCharts() {
      try {
        const [byDayRes, byCatRes] = await Promise.all([
          axios.get('/api/reports/sales-by-day'),
          axios.get('/api/reports/sales-by-category')
        ])
        
        const byDay = byDayRes.data
        const byCat = byCatRes.data

        if (this.charts.bar) this.charts.bar.destroy()
        if (this.$refs.bar) {
          this.charts.bar = new Chart(this.$refs.bar.getContext('2d'), {
            type: 'bar',
            data: { 
              labels: (byDay.data && byDay.data.map(d => d.date)) || [], 
              datasets: [{ 
                label: 'Sales', 
                data: (byDay.data && byDay.data.map(d => d.amount)) || [], 
                backgroundColor: '#4e73df' 
              }] 
            },
            options: { 
              scales: { y: { beginAtZero: true } }, 
              plugins: { legend: { display: false } } 
            }
          })
        }

        if (this.charts.pie) this.charts.pie.destroy()
        if (this.$refs.pie) {
          this.charts.pie = new Chart(this.$refs.pie.getContext('2d'), {
            type: 'pie',
            data: { 
              labels: (byCat.data && byCat.data.map(d => d.category)) || [], 
              datasets: [{ 
                data: (byCat.data && byCat.data.map(d => d.amount)) || [], 
                backgroundColor: ['#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#4e73df'] 
              }] 
            },
            options: { 
              plugins: { legend: { position: 'bottom' } } 
            }
          })
        }
      } catch (e) {
        console.error('Failed to load charts:', e);
      }
    },
    async loadSummary() {
      try {
        const res = await axios.get('/api/reports/dashboard-summary')
        this.extra = res.data
      } catch (e) {
        console.error('Failed to load dashboard summary:', e);
      }
    },
    firstItemName(sale) {
      if (sale.items && sale.items.length && sale.items[0] && sale.items[0].product) {
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


