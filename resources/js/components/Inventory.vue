<template>
  <b-container fluid>
    <b-row class="mb-3 align-items-center">
      <b-col><h2 class="mb-0">Inventory Management</h2></b-col>
      <b-col cols="auto">
        <b-button variant="success" @click="openPrintModal">
          <i class="fas fa-print"></i> Print Report
        </b-button>
      </b-col>
    </b-row>

    <!-- Date Filters -->
    <b-card class="mb-3">
      <h6 class="card-title">Filter Movements</h6>
      <b-form @submit.prevent="applyFilters">
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
            <b-form-group label="Type">
              <b-form-select 
                v-model="filters.type" 
                :options="filterTypeOptions"
                placeholder="All Types"
              />
            </b-form-group>
          </b-col>
          <b-col sm="3" class="d-flex align-items-end">
            <div class="d-flex gap-2">
              <b-button type="submit" variant="primary">Apply Filters</b-button>
              <b-button variant="outline-secondary" @click="clearFilters">Clear</b-button>
            </div>
          </b-col>
        </b-row>
      </b-form>
    </b-card>

    <!-- Inventory Summary Cards -->
    <b-row class="mb-3">
      <b-col md="3">
        <b-card class="text-center" bg-variant="primary" text-variant="white">
          <h4>{{ summary.total_products || 0 }}</h4>
          <small>Total Products</small>
        </b-card>
      </b-col>
      <b-col md="3">
        <b-card class="text-center" bg-variant="success" text-variant="white">
          <h4>{{ summary.total_stock || 0 }}</h4>
          <small>Total Stock</small>
        </b-card>
      </b-col>
      <b-col md="3">
        <b-card class="text-center" bg-variant="warning" text-variant="white">
          <h4>{{ summary.low_stock || 0 }}</h4>
          <small>Low Stock Items</small>
        </b-card>
      </b-col>
      <b-col md="3">
        <b-card class="text-center" bg-variant="info" text-variant="white">
          <h4>{{ summary.stock_value || '₱0.00' }}</h4>
          <small>Stock Value</small>
        </b-card>
      </b-col>
    </b-row>

    <!-- Inventory Adjustment Form -->
    <b-card class="mb-3">
      <h5 class="card-title">Adjust Inventory</h5>
      <b-form @submit.prevent="adjust">
        <b-row class="g-3">
          <b-col sm="4">
            <b-form-group label="Product">
              <b-form-select 
                v-model="form.product_id" 
                :options="productOptions" 
                placeholder="Select Product"
                required
              />
            </b-form-group>
          </b-col>
          <b-col sm="2">
            <b-form-group label="Type">
              <b-form-select 
                v-model="form.type" 
                :options="typeOptions" 
                required
              />
            </b-form-group>
          </b-col>
          <b-col sm="2">
            <b-form-group label="Quantity">
              <b-form-input 
                v-model.number="form.quantity" 
                type="number" 
                min="1" 
                required 
              />
            </b-form-group>
          </b-col>
          <b-col sm="3">
            <b-form-group label="Reason">
              <b-form-select 
                v-model="form.reason" 
                :options="reasonOptions"
                placeholder="Select Reason"
              />
            </b-form-group>
          </b-col>
          <b-col sm="1" class="d-flex align-items-end">
            <b-button type="submit" variant="primary" class="w-100">
              <i class="fas fa-check"></i> Apply
            </b-button>
          </b-col>
        </b-row>
      </b-form>
    </b-card>

    <!-- Inventory Movements Table -->
    <b-card>
      <h5 class="card-title">Inventory Movements</h5>
      <b-table 
        small 
        hover 
        :items="movements.data" 
        :fields="fields"
        responsive="sm"
        striped
      >
        <template #cell(product)="{ item }">
          <div>
            <strong>{{ (item.product && item.product.name) || 'Unknown Product' }}</strong>
            <br>
            <small class="text-muted">SKU: {{ (item.product && item.product.sku) || item.product_id }}</small>
          </div>
        </template>
        <template #cell(type)="{ item }">
          <b-badge 
            :variant="item.type === 'in' ? 'success' : 'danger'"
            :text="item.type === 'in' ? 'In' : 'Out'"
          />
        </template>
        <template #cell(quantity)="{ item }">
          <span :class="item.type === 'in' ? 'text-success' : 'text-danger'">
            {{ item.type === 'in' ? '+' : '-' }}{{ item.quantity }}
          </span>
        </template>
        <template #cell(created_at)="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
        <template #cell(actions)="{ item }">
          <b-button 
            size="sm" 
            variant="outline-secondary" 
            @click="viewDetails(item)"
          >
            Details
          </b-button>
        </template>
      </b-table>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
          Showing {{ movements.from || 0 }} to {{ movements.to || 0 }} of {{ movements.total || 0 }} movements
        </div>
        <b-pagination
          v-model="currentPage"
          :total-rows="movements.total || 0"
          :per-page="movements.per_page || 15"
          :page="currentPage"
          @change="load"
          align="center"
          size="sm"
          first-text="«"
          last-text="»"
          prev-text="‹"
          next-text="›"
        />
      </div>
    </b-card>

    <!-- Movement Details Modal -->
    <b-modal id="movement-details-modal" title="Movement Details" hide-footer>
      <div v-if="selectedMovement">
        <b-row>
          <b-col sm="6">
            <strong>Product:</strong><br>
            {{ (selectedMovement.product && selectedMovement.product.name) || 'Unknown' }}
          </b-col>
          <b-col sm="6">
            <strong>Type:</strong><br>
            <b-badge :variant="selectedMovement.type === 'in' ? 'success' : 'danger'">
              {{ selectedMovement.type === 'in' ? 'Stock In' : 'Stock Out' }}
            </b-badge>
          </b-col>
        </b-row>
        <hr>
        <b-row>
          <b-col sm="6">
            <strong>Quantity:</strong><br>
            {{ selectedMovement.quantity }}
          </b-col>
          <b-col sm="6">
            <strong>Date:</strong><br>
            {{ formatDate(selectedMovement.created_at) }}
          </b-col>
        </b-row>
        <hr>
        <b-row>
          <b-col sm="12">
            <strong>Reason:</strong><br>
            {{ selectedMovement.reason || 'No reason specified' }}
          </b-col>
        </b-row>
        <hr>
        <b-row v-if="selectedMovement.sale_id">
          <b-col sm="12">
            <strong>Related Sale:</strong><br>
            <div class="mt-2">
              <b-button 
                size="sm" 
                variant="outline-primary" 
                @click="viewSaleDetails(selectedMovement.sale_id)"
              >
                <i class="fas fa-eye"></i> View Sale #{{ selectedMovement.sale_id }}
              </b-button>
            </div>
          </b-col>
        </b-row>
      </div>
    </b-modal>

    <!-- Print Report Modal -->
    <b-modal id="print-report-modal" title="Print Inventory Report" hide-footer size="lg">
      <b-form @submit.prevent="printInventoryReport">
        <b-row class="g-3">
          <b-col md="6">
            <b-form-group label="Report Date Range">
              <b-form-input 
                v-model="printFilters.from_date" 
                type="date" 
                placeholder="Start date"
                required
              />
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="To Date">
              <b-form-input 
                v-model="printFilters.to_date" 
                type="date" 
                placeholder="End date"
                required
              />
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="Movement Type">
              <b-form-select 
                v-model="printFilters.type" 
                :options="filterTypeOptions"
                placeholder="All Types"
              />
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="Report Type">
              <b-form-select 
                v-model="printFilters.report_type" 
                :options="reportTypeOptions"
                required
              />
            </b-form-group>
          </b-col>
          <b-col md="12">
            <b-form-group label="Include in Report">
              <b-form-checkbox-group v-model="printFilters.includes">
                <b-form-checkbox value="summary">Summary Cards</b-form-checkbox>
                <b-form-checkbox value="movements">Movement Details</b-form-checkbox>
                <b-form-checkbox value="products">Product List</b-form-checkbox>
              </b-form-checkbox-group>
            </b-form-group>
          </b-col>
        </b-row>
        
        <div class="d-flex justify-content-end gap-2 mt-3">
          <b-button variant="secondary" @click="$bvModal.hide('print-report-modal')">
            Cancel
          </b-button>
          <b-button type="submit" variant="success">
            <i class="fas fa-print"></i> Generate & Print Report
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
  name: 'Inventory',
  data() {
    return {
      movements: { data: [], total: 0, per_page: 15, current_page: 1, last_page: 1, from: 0, to: 0 },
      currentPage: 1,
      summary: {},
      form: { 
        product_id: null, 
        type: 'in', 
        quantity: 1, 
        reason: '' 
      },
      selectedMovement: null,
      productOptions: [],
      typeOptions: [
        { value: 'in', text: 'Stock In' },
        { value: 'out', text: 'Stock Out' },
      ],
      reasonOptions: [
        { value: 'purchase', text: 'Purchase' },
        { value: 'sale', text: 'Sale' },
        { value: 'adjustment', text: 'Manual Adjustment' },
        { value: 'return', text: 'Return' },
        { value: 'damage', text: 'Damage/Loss' },
        { value: 'expiry', text: 'Expiry' },
        { value: 'other', text: 'Other' },
      ],
      fields: [
        { key: 'type', label: 'Type', class: 'text-center' },
        { key: 'product', label: 'Product' },
        { key: 'quantity', label: 'Quantity', class: 'text-center' },
        { key: 'reason', label: 'Reason' },
        { key: 'created_at', label: 'Date' },
        { key: 'actions', label: 'Actions', class: 'text-center' },
      ],
      filters: {
        from_date: '',
        to_date: '',
        type: 'all'
      },
      filterTypeOptions: [
        { value: 'all', text: 'All Types' },
        { value: 'in', text: 'Stock In' },
        { value: 'out', text: 'Stock Out' },
      ],
      printFilters: {
        from_date: '',
        to_date: '',
        type: 'all',
        report_type: 'html', // 'html' or 'pdf'
        includes: ['summary', 'movements', 'products']
      },
      reportTypeOptions: [
        { value: 'html', text: 'HTML Report' },
        { value: 'pdf', text: 'PDF Report' },
      ]
    }
  },
  created() { 
    this.load(1);
    this.loadProducts();
    this.loadSummary();
  },
  methods: {
    async loadProducts() {
      try {
        const res = await axios.get('/api/products');
        this.productOptions = res.data.data.map(p => ({
          value: p.id,
          text: `${p.name} (${p.sku}) - Stock: ${p.stock}`
        }));
      } catch (e) {
        console.error('Failed to load products:', e);
      }
    },
    async loadSummary() {
      try {
        const res = await axios.get('/api/reports/dashboard-summary');
        this.summary = res.data.inventory || {};
      } catch (e) {
        console.error('Failed to load summary:', e);
      }
    },
    async load(page = 1) {
      this.currentPage = page;
      try {
        const params = {
          page: page,
          ...this.filters
        };
        const res = await axios.get(`/api/inventory`, { params });
        this.movements = res.data;
      } catch (e) {
        console.error('Failed to load movements:', e);
        Swal.fire({ 
          icon: 'error', 
          title: 'Load Failed', 
          text: 'Failed to load inventory movements' 
        });
      }
    },
    async adjust() {
      if (!this.form.product_id) {
        Swal.fire({ icon: 'warning', title: 'Select Product', text: 'Please select a product' });
        return;
      }

      try {
        await axios.post('/api/inventory/adjust', this.form);
        
        Swal.fire({ 
          icon: 'success', 
          title: 'Success', 
          text: 'Inventory adjusted successfully',
          timer: 1500,
          showConfirmButton: false
        });

        // Reset form
        this.form = { product_id: null, type: 'in', quantity: 1, reason: '' };
        
        // Reload data
        this.load(this.currentPage);
        this.loadSummary();
        this.loadProducts(); // Refresh product options with new stock levels
      } catch (e) {
        let msg = 'Adjustment failed';
        if (e.response && e.response.data) {
          msg = e.response.data.message || Object.values(e.response.data.errors || {}).flat().join('\n');
        }
        Swal.fire({ icon: 'error', title: 'Adjustment Failed', text: msg });
      }
    },
    viewDetails(movement) {
      this.selectedMovement = movement;
      this.$bvModal.show('movement-details-modal');
    },
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
    },
    async applyFilters() {
      this.currentPage = 1; // Reset to first page when filters change
      await this.load(1);
      this.loadSummary();
      this.loadProducts(); // Refresh product options with new stock levels
    },
    clearFilters() {
      this.filters = {
        from_date: '',
        to_date: '',
        type: 'all'
      };
      this.currentPage = 1; // Reset to first page when filters are cleared
      this.load(1);
      this.loadSummary();
      this.loadProducts(); // Refresh product options with new stock levels
    },
    openPrintModal() {
      this.$bvModal.show('print-report-modal');
    },
    async printInventoryReport() {
      try {
        const params = {
          from_date: this.printFilters.from_date,
          to_date: this.printFilters.to_date,
          type: this.printFilters.type,
          report_type: this.printFilters.report_type,
          includes: this.printFilters.includes.join(',')
        };

        const res = await axios.get(`/api/reports/inventory-report`, { params });
        const reportContent = res.data;

        if (this.printFilters.report_type === 'html') {
          this.printHtmlReport(reportContent);
        } else if (this.printFilters.report_type === 'pdf') {
          this.printPdfReport(reportContent);
        }

        this.$bvModal.hide('print-report-modal');
      } catch (e) {
        console.error('Failed to generate report:', e);
        Swal.fire({ 
          icon: 'error', 
          title: 'Print Failed', 
          text: 'Failed to generate or print inventory report' 
        });
      }
    },
    printHtmlReport(reportContent) {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Inventory Report</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { display: flex; justify-content: space-between; margin-bottom: 30px; }
            .summary-item { text-align: center; }
            .summary-value { font-size: 24px; font-weight: bold; }
            .summary-label { font-size: 12px; color: #666; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .type-in { color: green; }
            .type-out { color: red; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
          </style>
        </head>
        <body>
          ${reportContent}
        </body>
        </html>
      `);
      
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    },
    printPdfReport(reportContent) {
      // This method would typically involve a PDF generation library (e.g., jsPDF, pdfmake)
      // For demonstration, we'll just log the content to the console.
      console.log('PDF Report Content:', reportContent);
      Swal.fire({
        icon: 'info',
        title: 'PDF Report',
        html: `<pre>${reportContent}</pre>`,
        showConfirmButton: true,
        confirmButtonText: 'Close'
      });
    },
    async viewSaleDetails(saleId) {
      try {
        const res = await axios.get(`/api/sales/${saleId}`);
        const saleDetails = res.data.data;
        Swal.fire({
          title: `Sale #${saleId} Details`,
          html: `
            <p><strong>Sale ID:</strong> ${saleDetails.id}</p>
            <p><strong>Date:</strong> ${this.formatDate(saleDetails.created_at)}</p>
            <p><strong>Customer:</strong> ${saleDetails.customer_name || 'N/A'}</p>
            <p><strong>Total Amount:</strong> ₱${saleDetails.total_amount.toFixed(2)}</p>
            <p><strong>Payment Status:</strong> ${saleDetails.payment_status}</p>
            <p><strong>Payment Method:</strong> ${saleDetails.payment_method}</p>
            <p><strong>Items:</strong></p>
            <ul>
              ${saleDetails.sale_items.map(item => `
                <li>${item.product_name} (Qty: ${item.quantity}, Price: ₱${item.price.toFixed(2)})</li>
              `).join('')}
            </ul>
          `,
          showConfirmButton: true,
          confirmButtonText: 'Close'
        });
      } catch (e) {
        console.error('Failed to load sale details:', e);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `Failed to load sale details for Sale #${saleId}`
        });
      }
    }
  }
}
</script>


