<template>
  <b-container fluid>
    <b-row class="mb-3 align-items-center">
      <b-col><h2 class="mb-0">Sales Reports</h2></b-col>
      <b-col cols="auto">
        <b-button variant="success" @click="exportReport">
          <i class="fas fa-download"></i> Export Report
        </b-button>
      </b-col>
    </b-row>

    <!-- Date Filters -->
    <b-card class="mb-3">
      <h6 class="card-title">Filter Reports</h6>
      <b-form @submit.prevent="handleFilterSubmit">
        <b-row class="g-3">
          <b-col sm="3">
            <b-form-group label="From Date">
              <b-form-input 
                v-model="filters.from_date" 
                type="date" 
                placeholder="Start date"
              />
            </b-form-group>
          </b-col>
          <b-col sm="3">
            <b-form-group label="To Date">
              <b-form-input 
                v-model="filters.to_date" 
                type="date" 
                placeholder="End date"
              />
            </b-form-group>
          </b-col>
          <b-col sm="3">
            <b-form-group label="Report Type">
              <b-form-select 
                v-model="filters.report_type" 
                :options="reportTypeOptions"
                placeholder="Select Report Type"
              />
            </b-form-group>
          </b-col>
          <b-col sm="3" class="d-flex align-items-end">
            <div class="d-flex gap-2">
              <b-button type="submit" variant="primary" :disabled="isLoading">
                <i class="fas fa-search"></i> {{ isLoading ? 'Loading...' : 'Generate Report' }}
              </b-button>
              <b-button variant="outline-secondary" @click="clearFilters" :disabled="isLoading">
                <i class="fas fa-times"></i> Clear
              </b-button>
            </div>
          </b-col>
        </b-row>
      </b-form>
    </b-card>

    <!-- Loading Overlay -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading report data...</p>
    </div>

    <!-- Main Content (hidden when loading) -->
    <div v-if="!isLoading">
      <!-- Summary Cards -->
      <b-row class="mb-3">
        <b-col md="3">
          <b-card class="text-center" bg-variant="primary" text-variant="white">
            <h4>{{ summary.total_sales || 0 }}</h4>
            <small>Total Sales</small>
          </b-card>
        </b-col>
        <b-col md="3">
          <b-card class="text-center" bg-variant="success" text-variant="white">
            <h4>₱{{ (summary.total_revenue || 0).toFixed(2) }}</h4>
            <small>Total Revenue</small>
          </b-card>
        </b-col>
        <b-col md="3">
          <b-card class="text-center" bg-variant="info" text-variant="white">
            <h4>{{ summary.total_items || 0 }}</h4>
            <small>Items Sold</small>
          </b-card>
        </b-col>
        <b-col md="3">
          <b-card class="text-center" bg-variant="warning" text-variant="white">
            <h4>₱{{ (summary.average_sale || 0).toFixed(2) }}</h4>
            <small>Average Sale</small>
          </b-card>
        </b-col>
      </b-row>

      <!-- Charts Row -->
      <b-row class="mb-3">
        <b-col md="6">
          <b-card>
            <h5 class="card-title">Sales by Day</h5>
            <canvas ref="salesByDayChart"></canvas>
          </b-card>
        </b-col>
        <b-col md="6">
          <b-card>
            <h5 class="card-title">Sales by Category</h5>
            <canvas ref="salesByCategoryChart" style="max-height: 358px !important; height: 80% !important;"></canvas>
          </b-card>
        </b-col>
      </b-row>

      <!-- Top Products -->
      <b-row class="mb-3">
        <b-col md="6">
          <b-card>
            <h5 class="card-title">Top Selling Products</h5>
            <b-table 
              small 
              hover 
              :items="topProducts" 
              :fields="topProductFields"
              responsive="sm"
              striped
            >
              <template #cell(quantity)="{ item }">
                <b-badge variant="success">{{ item.quantity || 0 }}</b-badge>
              </template>
              <template #cell(revenue)="{ item }">
                ₱{{ (item.revenue || 0).toFixed(2) }}
              </template>
            </b-table>
          </b-card>
        </b-col>
        <b-col md="6">
          <b-card>
            <h5 class="card-title">Recent Sales</h5>
            <b-table 
              small 
              hover 
              :items="recentSales" 
              :fields="recentSalesFields"
              responsive="sm"
              striped
            >
              <template #cell(total_amount)="{ item }">
                ₱{{ (item.total || 0).toFixed(2) }}
              </template>
              <template #cell(created_at)="{ item }">
                {{ formatDate(item.created_at) }}
              </template>
              <template #cell(actions)="{ item }">
                <b-button 
                  size="sm" 
                  variant="outline-primary" 
                  @click="viewSaleDetails(item.id)"
                >
                  <i class="fas fa-eye"></i>
                </b-button>
              </template>
            </b-table>
          </b-card>
        </b-col>
      </b-row>

      <!-- Detailed Sales Table -->
      <b-card>
        <h5 class="card-title">Detailed Sales Report</h5>
        <b-table 
          small 
          hover 
          :items="sales.data" 
          :fields="salesFields"
          responsive="sm"
          striped
          show-empty
        >
          <template #cell(total_amount)="{ item }">
            ₱{{ (item.total || 0).toFixed(2) }}
          </template>
          <template #cell(created_at)="{ item }">
            {{ formatDate(item.created_at) }}
          </template>
          <template #cell(actions)="{ item }">
            <div class="d-flex gap-1">
              <b-button 
                size="sm" 
                variant="outline-primary" 
                @click="viewSaleDetails(item.id)"
              >
                <i class="fas fa-eye"></i>
              </b-button>
              <b-button 
                size="sm" 
                variant="outline-success" 
                @click="printReceipt(item)"
              >
                <i class="fas fa-print"></i>
              </b-button>
            </div>
          </template>
          <template #empty>
            <div class="text-center text-muted py-4">
              <i class="fas fa-chart-line fa-3x mb-3"></i>
              <p>No sales data found for the selected period</p>
            </div>
          </template>
        </b-table>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted">
            Showing {{ sales.from || 0 }} to {{ sales.to || 0 }} of {{ sales.total || 0 }} sales
          </div>
          <b-pagination
            v-model="currentPage"
            :total-rows="sales.total || 0"
            :per-page="sales.per_page || 15"
            :page="currentPage"
            @change="loadSales"
            align="center"
            size="sm"
            first-text="«"
            last-text="»"
            prev-text="‹"
            next-text="›"
          />
        </div>
      </b-card>
    </div>

    <!-- Sale Details Modal -->
    <b-modal id="sale-details-modal" title="Sale Details" hide-footer size="lg">
      <div v-if="selectedSale">
        <b-row>
          <b-col sm="6">
            <strong>Sale ID:</strong><br>
            #{{ selectedSale.id }}
          </b-col>
          <b-col sm="6">
            <strong>Date:</strong><br>
            {{ formatDate(selectedSale.created_at) }}
          </b-col>
        </b-row>
        <hr>
        <b-row>
          <b-col sm="6">
            <strong>Total Amount:</strong><br>
            ₱{{ (selectedSale.total || 0).toFixed(2) }}
          </b-col>
          <b-col sm="6">
            <strong>Payment Method:</strong><br>
            {{ selectedSale.payment_method || 'GCash' }}
          </b-col>
        </b-row>
        <hr>
        <h6>Items:</h6>
        <b-table 
          small 
          :items="selectedSale.sale_items || []" 
          :fields="saleItemFields"
          responsive="sm"
        >
          <template #cell(unit_price)="{ item }">
            ₱{{ (item.unit_price || 0).toFixed(2) }}
          </template>
          <template #cell(total)="{ item }">
            ₱{{ (item.total || 0).toFixed(2) }}
          </template>
        </b-table>
        
        <!-- Print Actions -->
        <div class="d-flex justify-content-end gap-2 mt-3">
          <b-button variant="success" @click="openPrintModal(selectedSale)">
            <i class="fas fa-print"></i> Print Receipt
          </b-button>
        </div>
      </div>
    </b-modal>

    <!-- Print Modal -->
    <b-modal id="print-modal" title="Print Options" hide-footer size="md">
      <div v-if="selectedSaleForPrint">
        <h6 class="mb-3">Print Options for Sale #{{ selectedSaleForPrint.id }}</h6>
        
        <b-form @submit.prevent="printReceiptFromModal">
          <b-form-group label="Print Type">
            <b-form-select 
              v-model="printOptions.type" 
              :options="printTypeOptions"
              required
            />
          </b-form-group>
          
          <b-form-group label="Include Details">
            <b-form-checkbox-group v-model="printOptions.includes">
              <b-form-checkbox value="header">Header & Logo</b-form-checkbox>
              <b-form-checkbox value="items">Item Details</b-form-checkbox>
              <b-form-checkbox value="summary">Summary & Total</b-form-checkbox>
              <b-form-checkbox value="footer">Footer & Terms</b-form-checkbox>
            </b-form-checkbox-group>
          </b-form-group>
          
          <b-form-group label="Paper Size">
            <b-form-select 
              v-model="printOptions.paperSize" 
              :options="paperSizeOptions"
            />
          </b-form-group>
          
          <div class="d-flex justify-content-end gap-2 mt-3">
            <b-button variant="secondary" @click="$bvModal.hide('print-modal')">
              Cancel
            </b-button>
            <b-button type="submit" variant="success">
              <i class="fas fa-print"></i> Print Receipt
            </b-button>
          </div>
        </b-form>
      </div>
    </b-modal>
  </b-container>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';

export default {
  name: 'Reports',
  data() {
    return {
      filters: {
        from_date: '',
        to_date: '',
        report_type: 'sales'
      },
      reportTypeOptions: [
        { value: 'sales', text: 'Sales Report' },
        { value: 'products', text: 'Product Performance' },
        { value: 'inventory', text: 'Inventory Report' }
      ],
      summary: {},
      topProducts: [],
      recentSales: [],
      sales: { data: [], total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 },
      currentPage: 1,
      selectedSale: null,
      topProductFields: [
        { key: 'name', label: 'Product' },
        { key: 'quantity', label: 'Qty Sold', class: 'text-center' },
        { key: 'revenue', label: 'Revenue', class: 'text-end' }
      ],
      recentSalesFields: [
        { key: 'id', label: 'Sale #', class: 'text-center' },
        { key: 'total_amount', label: 'Amount', class: 'text-end' },
        { key: 'created_at', label: 'Date' },
        { key: 'actions', label: 'Actions', class: 'text-center' }
      ],
      salesFields: [
        { key: 'id', label: 'Sale #', class: 'text-center' },
        { key: 'total_amount', label: 'Amount', class: 'text-end' },
        { key: 'payment_method', label: 'Payment' },
        { key: 'created_at', label: 'Date' },
        { key: 'actions', label: 'Actions', class: 'text-center' }
      ],
      saleItemFields: [
        { key: 'product_name', label: 'Product' },
        { key: 'quantity', label: 'Qty', class: 'text-center' },
        { key: 'unit_price', label: 'Price', class: 'text-end' },
        { key: 'total', label: 'Total', class: 'text-end' }
      ],
      charts: {
        salesByDay: null,
        salesByCategory: null
      },
      selectedSaleForPrint: null,
      printOptions: {
        type: 'receipt',
        includes: ['header', 'items', 'summary'],
        paperSize: 'a4'
      },
      printTypeOptions: [
        { value: 'receipt', text: 'Standard Receipt' },
        { value: 'detailed', text: 'Detailed Receipt' },
        { value: 'invoice', text: 'Invoice Format' }
      ],
      paperSizeOptions: [
        { value: 'a4', text: 'A4 (Standard)' },
        { value: 'letter', text: 'Letter (US)' },
        { value: 'receipt', text: 'Thermal Receipt' }
      ],
      isLoading: false
    }
  },
  created() {
    this.setDefaultDates();
    this.loadReports();
  },
  beforeDestroy() {
    // Clean up charts to prevent memory leaks
    if (this.charts.salesByDay) {
      this.charts.salesByDay.destroy();
      this.charts.salesByDay = null;
    }
    if (this.charts.salesByCategory) {
      this.charts.salesByCategory.destroy();
      this.charts.salesByCategory = null;
    }
  },
  methods: {
    setDefaultDates() {
      const today = new Date();
      const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
      
      this.filters.from_date = thirtyDaysAgo.toISOString().split('T')[0];
      this.filters.to_date = today.toISOString().split('T')[0];
    },
    
    async loadReports() {
      try {
        if (this.isLoading) {
          return; // Prevent multiple simultaneous loads
        }
        
        this.isLoading = true;

        await Promise.all([
          this.loadSummary(),
          this.loadTopProducts(),
          this.loadRecentSales(),
          this.loadSales(1)
        ]);
        
        // Always update charts after loading data (like Dashboard)
        // Wait a bit more to ensure DOM is ready and visible
        setTimeout(() => {
          console.log('Attempting to update charts...');
          this.updateCharts();
        }, 500);
      } catch (e) {
        console.error('Failed to load reports:', e);
        Swal.fire({ 
          icon: 'error', 
          title: 'Load Failed', 
          text: 'Failed to load report data' 
        });
      } finally {
        this.isLoading = false;
      }
    },

    async handleFilterSubmit() {
      // Force refresh charts when filters change
      this.refreshCharts();
      await this.loadReports();
    },

    async loadSummary() {
      try {
        const params = {
          from_date: this.filters.from_date,
          to_date: this.filters.to_date
        };
        const res = await axios.get('/api/reports/dashboard-summary', { params });
        
                                                // Calculate summary from the data like Dashboard does
                    const salesData = res.data.sales || {};
                    const totalSales = parseInt(salesData.paid_count) || 0;
                    const totalRevenue = parseFloat(salesData.paid_total) || 0;
                    const totalItems = parseInt(salesData.paid_items_count) || 0;
                    const avgSale = totalSales > 0 ? totalRevenue / totalSales : 0;
        
        this.summary = {
          total_sales: totalSales,
          total_revenue: totalRevenue,
          total_items: totalItems,
          average_sale: avgSale
        };
      } catch (e) {
        console.error('Failed to load summary:', e);
        this.summary = {
          total_sales: 0,
          total_revenue: 0,
          total_items: 0,
          average_sale: 0
        };
      }
    },

    async loadTopProducts() {
      try {
        const params = {
          from_date: this.filters.from_date,
          to_date: this.filters.to_date,
          limit: 5
        };
        const res = await axios.get('/api/reports/top-products', { params });
        // Ensure data is properly formatted with numeric values
        this.topProducts = (res.data.data || []).map(item => ({
          name: item.name || '',
          quantity: parseInt(item.quantity) || 0,
          revenue: parseFloat(item.revenue) || 0
        }));
      } catch (e) {
        console.error('Failed to load top products:', e);
        this.topProducts = [];
      }
    },

    async loadRecentSales() {
      try {
        const params = {
          from_date: this.filters.from_date,
          to_date: this.filters.to_date,
          limit: 5
        };
        const res = await axios.get('/api/reports/recent-sales', { params });
        // Ensure data is properly formatted with numeric values
        this.recentSales = (res.data.data || []).map(item => ({
          id: item.id || 0,
          total: parseFloat(item.total) || 0,
          created_at: item.created_at || ''
        }));
      } catch (e) {
        console.error('Failed to load recent sales:', e);
        this.recentSales = [];
      }
    },

    async loadSales(page = 1) {
      this.currentPage = page;
      try {
        const params = {
          page: page,
          from_date: this.filters.from_date,
          to_date: this.filters.to_date
        };
        const res = await axios.get('/api/sales', { params });
        // Ensure sales data is properly formatted
        if (res.data && res.data.data) {
          res.data.data = res.data.data.map(sale => ({
            ...sale,
            total: parseFloat(sale.total) || 0
          }));
        }
        this.sales = res.data;
      } catch (e) {
        console.error('Failed to load sales:', e);
        this.sales = { data: [], total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 };
        Swal.fire({ 
          icon: 'error', 
          title: 'Load Failed', 
          text: 'Failed to load sales data' 
        });
      }
    },

    updateCharts() {
      // Use the same approach as Dashboard - simple and working
      console.log('updateCharts called');
      console.log('salesByDayChart ref:', this.$refs.salesByDayChart);
      console.log('salesByCategoryChart ref:', this.$refs.salesByCategoryChart);
      console.log('existing charts:', this.charts);
      
      if (this.$refs.salesByDayChart && !this.charts.salesByDay) {
        console.log('Updating sales by day chart');
        this.updateSalesByDayChart();
      } else {
        console.log('Skipping sales by day chart:', !this.$refs.salesByDayChart ? 'ref not found' : 'chart exists');
      }
      
      if (this.$refs.salesByCategoryChart && !this.charts.salesByCategory) {
        console.log('Updating sales by category chart');
        this.updateSalesByCategoryChart();
      } else {
        console.log('Skipping sales by category chart:', !this.$refs.salesByCategoryChart ? 'ref not found' : 'chart exists');
      }
    },

    async updateSalesByDayChart() {
      try {
        // Simple approach like Dashboard - no complex state management
        if (this.charts.salesByDay) {
          console.log('Sales by day chart already exists, skipping');
          return;
        }

        console.log('Loading sales by day chart data...');
        const res = await axios.get('/api/reports/sales-by-day');
        const byDay = res.data;
        console.log('Sales by day data:', byDay);

        if (this.$refs.salesByDayChart) {
          console.log('Creating sales by day chart...');
          this.charts.salesByDay = new Chart(this.$refs.salesByDayChart.getContext('2d'), {
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
          });
          console.log('Sales by day chart created successfully');
        } else {
          console.log('salesByDayChart ref not found');
        }
      } catch (e) {
        console.error('Failed to update sales by day chart:', e);
      }
    },

    async updateSalesByCategoryChart() {
      try {
        // Simple approach like Dashboard - no complex state management
        if (this.charts.salesByCategory) {
          console.log('Sales by category chart already exists, skipping');
          return;
        }

        console.log('Loading sales by category chart data...');
        const res = await axios.get('/api/reports/sales-by-category');
        const byCat = res.data;
        console.log('Sales by category data:', byCat);

        if (this.$refs.salesByCategoryChart) {
          console.log('Creating sales by category chart...');
          this.charts.salesByCategory = new Chart(this.$refs.salesByCategoryChart.getContext('2d'), {
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
          });
          console.log('Sales by category chart created successfully');
        } else {
          console.log('salesByCategoryChart ref not found');
        }
      } catch (e) {
        console.error('Failed to update sales by category chart:', e);
      }
    },

    async viewSaleDetails(saleId) {
      try {
        const res = await axios.get(`/api/sales/${saleId}`);
        const saleData = res.data.data;
        // Ensure sale data is properly formatted with numeric values
        this.selectedSale = {
          ...saleData,
          total: parseFloat(saleData.total) || 0,
          sale_items: (saleData.sale_items || []).map(item => ({
            ...item,
            unit_price: parseFloat(item.unit_price) || 0,
            total: parseFloat(item.total) || 0,
            quantity: parseInt(item.quantity) || 0
          }))
        };
        this.$bvModal.show('sale-details-modal');
      } catch (e) {
        console.error('Failed to load sale details:', e);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load sale details'
        });
      }
    },

    async printReceipt(sale) {
      try {
        const res = await axios.get(`/api/sales/${sale.id}`);
        const saleDetails = res.data.data;
        // Ensure sale data is properly formatted with numeric values
        const formattedSale = {
          ...saleDetails,
          total: parseFloat(saleDetails.total) || 0,
          sale_items: (saleDetails.sale_items || []).map(item => ({
            ...item,
            unit_price: parseFloat(item.unit_price) || 0,
            total: parseFloat(item.total) || 0,
            quantity: parseInt(item.quantity) || 0
          }))
        };
        
        const receiptHtml = this.generateReceiptHtml(formattedSale);
        this.openPrintWindow(receiptHtml);
        
        Swal.fire({
          icon: 'success',
          title: 'Receipt Generated',
          text: 'Receipt has been opened for printing',
          timer: 1500,
          showConfirmButton: false
        });
      } catch (e) {
        console.error('Failed to print receipt:', e);
        Swal.fire({
          icon: 'error',
          title: 'Print Failed',
          text: 'Failed to generate receipt'
        });
      }
    },

    generateReceiptHtml(sale) {
      const items = sale.sale_items || [];
      const lines = items.map(item => 
        `<tr><td>${item.product_name}</td><td style="text-align:right;">${item.quantity}</td><td style="text-align:right;">₱${(item.unit_price || 0).toFixed(2)}</td><td style="text-align:right;">₱${(item.total || 0).toFixed(2)}</td></tr>`
      ).join('');
      
      const total = (sale.total || 0).toFixed(2);
      const date = this.formatDate(sale.created_at);
      
      return `<!doctype html><html><head><meta charset="utf-8"><title>Receipt #${sale.id}</title>
        <style>body{font-family:Arial,sans-serif;padding:16px;} h2{margin:0 0 8px;} table{width:100%;border-collapse:collapse;} td,th{padding:6px;border-bottom:1px solid #eee;} th{text-align:left;} .tot{font-weight:bold;}</style>
      </head><body>
        <h2>Receipt #${sale.id}</h2>
        <div>Date: ${date}</div>
        <hr/>
        <table>
          <thead><tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
          <tbody>${lines}</tbody>
          <tfoot><tr><td colspan="3" class="tot" style="text-align:right;">Grand Total</td><td class="tot" style="text-align:right;">₱${total}</td></tr></tfoot>
        </table>
      </body></html>`;
    },

    openPrintWindow(html) {
      const w = window.open('', 'PRINT', 'height=600,width=800');
      if (!w) return;
      w.document.write(html);
      w.document.close();
      w.focus();
      w.print();
      w.close();
    },

    async exportReport() {
      try {
        const params = {
          from_date: this.filters.from_date,
          to_date: this.filters.to_date,
          report_type: this.filters.report_type
        };
        
        const res = await axios.get('/api/reports/export', { 
          params,
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
                            link.setAttribute('download', `sales-report-${this.filters.from_date}-to-${this.filters.to_date}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        
                            Swal.fire({
                      icon: 'success',
                      title: 'Export Successful',
                      text: 'CSV report has been downloaded',
                      timer: 1500,
                      showConfirmButton: false
                    });
      } catch (e) {
        console.error('Failed to export report:', e);
        Swal.fire({
          icon: 'error',
          title: 'Export Failed',
          text: 'Failed to export report'
        });
      }
    },

    clearFilters() {
      this.filters = {
        from_date: '',
        to_date: '',
        report_type: 'sales'
      };
      this.setDefaultDates();
      this.loadReports();
    },

    refreshCharts() {
      // Simple approach like Dashboard - destroy and recreate
      if (this.charts.salesByDay) {
        this.charts.salesByDay.destroy();
        this.charts.salesByDay = null;
      }
      if (this.charts.salesByCategory) {
        this.charts.salesByCategory.destroy();
        this.charts.salesByCategory = null;
      }
    },

    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
    },

    openPrintModal(sale) {
      this.selectedSaleForPrint = sale;
      this.$bvModal.show('print-modal');
    },

    async printReceiptFromModal() {
      try {
        const saleId = this.selectedSaleForPrint.id;
        const res = await axios.get(`/api/sales/${saleId}`);
        const saleDetails = res.data.data;
        // Ensure sale data is properly formatted with numeric values
        const formattedSale = {
          ...saleDetails,
          total: parseFloat(saleDetails.total) || 0,
          sale_items: (saleDetails.sale_items || []).map(item => ({
            ...item,
            unit_price: parseFloat(item.unit_price) || 0,
            total: parseFloat(item.total) || 0,
            quantity: parseInt(item.quantity) || 0
          }))
        };

        let receiptHtml = '';
        if (this.printOptions.type === 'receipt') {
          receiptHtml = this.generateReceiptHtml(formattedSale);
        } else if (this.printOptions.type === 'detailed') {
          receiptHtml = this.generateDetailedReceiptHtml(formattedSale);
        } else if (this.printOptions.type === 'invoice') {
          receiptHtml = this.generateInvoiceHtml(formattedSale);
        }

        this.openPrintWindow(receiptHtml);

        Swal.fire({
          icon: 'success',
          title: 'Receipt Generated',
          text: 'Receipt has been opened for printing',
          timer: 1500,
          showConfirmButton: false
        });
      } catch (e) {
        console.error('Failed to print receipt from modal:', e);
        Swal.fire({
          icon: 'error',
          title: 'Print Failed',
          text: 'Failed to generate receipt'
        });
      } finally {
        this.$bvModal.hide('print-modal');
      }
    },

    generateDetailedReceiptHtml(sale) {
      const items = sale.sale_items || [];
      const lines = items.map(item => 
        `<tr><td>${item.product_name}</td><td style="text-align:right;">${item.quantity}</td><td style="text-align:right;">₱${(item.unit_price || 0).toFixed(2)}</td><td style="text-align:right;">₱${(item.total || 0).toFixed(2)}</td></tr>`
      ).join('');
      
      const total = (sale.total || 0).toFixed(2);
      const date = this.formatDate(sale.created_at);
      const paymentMethod = sale.payment_method || 'GCash';

      return `<!doctype html><html><head><meta charset="utf-8"><title>Receipt #${sale.id}</title>
        <style>body{font-family:Arial,sans-serif;padding:16px;} h2{margin:0 0 8px;} table{width:100%;border-collapse:collapse;} td,th{padding:6px;border-bottom:1px solid #eee;} th{text-align:left;} .tot{font-weight:bold;}</style>
      </head><body>
        <h2>Receipt #${sale.id}</h2>
        <div>Date: ${date}</div>
        <hr/>
        <table>
          <thead><tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
          <tbody>${lines}</tbody>
          <tfoot><tr><td colspan="3" class="tot" style="text-align:right;">Grand Total</td><td class="tot" style="text-align:right;">₱${total}</td></tr></tfoot>
        </table>
        <p>Payment Method: ${paymentMethod}</p>
      </body></html>`;
    },

    generateInvoiceHtml(sale) {
      const items = sale.sale_items || [];
      const lines = items.map(item => 
        `<tr><td>${item.product_name}</td><td style="text-align:right;">${item.quantity}</td><td style="text-align:right;">₱${(item.unit_price || 0).toFixed(2)}</td><td style="text-align:right;">₱${(item.total || 0).toFixed(2)}</td></tr>`
      ).join('');
      
      const total = (sale.total || 0).toFixed(2);
      const date = this.formatDate(sale.created_at);
      const paymentMethod = sale.payment_method || 'GCash';

      return `<!doctype html><html><head><meta charset="utf-8"><title>Invoice #${sale.id}</title>
        <style>body{font-family:Arial,sans-serif;padding:16px;} h2{margin:0 0 8px;} table{width:100%;border-collapse:collapse;} td,th{padding:6px;border-bottom:1px solid #eee;} th{text-align:left;} .tot{font-weight:bold;}</style>
      </head><body>
        <h2>Invoice #${sale.id}</h2>
        <div>Date: ${date}</div>
        <hr/>
        <table>
          <thead><tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
          <tbody>${lines}</tbody>
          <tfoot><tr><td colspan="3" class="tot" style="text-align:right;">Grand Total</td><td class="tot" style="text-align:right;">₱${total}</td></tr></tfoot>
        </table>
        <p>Payment Method: ${paymentMethod}</p>
      </body></html>`;
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

.h3, .h4, .display-6 {
  color: #111827;
  font-weight: 700;
}

/* Control pie chart size specifically - target the exact chart */
canvas[ref="salesByCategoryChart"] {
  max-height: 80px !important;
  height: 80px !important;
  width: 100% !important;
}

/* Keep bar chart at current size */
canvas[ref="salesByDayChart"] {
  height: auto !important;
  max-height: none !important;
}
</style>