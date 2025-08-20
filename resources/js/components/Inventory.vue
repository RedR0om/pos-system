<template>
  <b-container fluid>
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <b-spinner variant="primary" label="Loading..."></b-spinner>
        <div class="mt-2">Loading inventory data...</div>
      </div>
    </div>

    <b-row class="mb-3 align-items-center">
      <b-col><h2 class="mb-0">Inventory Management</h2></b-col>
      <b-col cols="auto">
                 <b-button variant="success" @click="openExportModal">
           <i class="fas fa-file-csv"></i> Export to CSV
         </b-button>
      </b-col>
    </b-row>

    <!-- Date Filters -->
    <b-card class="mb-3">
      <b-row>
        <h6 class="card-title">Filter Movements</h6>
      </b-row>
      <br>
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
          <b-col sm="3" class="d-flex align-items-end" style="margin-bottom: 16px;">
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
          <h4>{{ summary.stock_on_hand || 0 }}</h4>
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
          <h4>₱{{ summary.stock_value || '0.00' }}</h4>
          <small>Stock Value</small>
        </b-card>
      </b-col>
    </b-row>

    <!-- Inventory Adjustment Form -->
    <b-card class="mb-3">
      <b-row>
        <h5 class="card-title">Adjust Inventory</h5>
      </b-row>
      <br>
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
          <b-col sm="1" class="d-flex align-items-end" style="margin-bottom: 16px;">
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
      
      <div v-if="loading" class="text-center py-5">
        <b-spinner variant="primary" label="Loading..."></b-spinner>
        <div class="mt-2">Loading movements...</div>
      </div>
      
      <b-table 
        v-else
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
           :per-page="movements.per_page || 25"
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

    <!-- Export CSV Modal -->
    <b-modal id="export-excel-modal" title="Export Inventory Report to CSV" hide-footer size="md">
      <b-form @submit.prevent="exportInventoryCsv">
        <b-row class="g-3">
          <b-col md="6">
            <b-form-group label="From Date">
              <b-form-input 
                v-model="exportFilters.from_date" 
                type="date" 
                placeholder="Start date"
              />
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="To Date">
              <b-form-input 
                v-model="exportFilters.to_date" 
                type="date" 
                placeholder="End date"
              />
            </b-form-group>
          </b-col>
          <b-col md="12">
            <b-form-group label="Movement Type">
              <b-form-select 
                v-model="exportFilters.type" 
                :options="filterTypeOptions"
                placeholder="All Types"
              />
            </b-form-group>
          </b-col>
        </b-row>
        
        <div class="d-flex justify-content-end gap-2 mt-3">
          <b-button variant="secondary" @click="$bvModal.hide('export-excel-modal')">
            Cancel
          </b-button>
                     <b-button type="submit" variant="success">
             <i class="fas fa-file-csv"></i> Export to CSV
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
      loading: true,
      movements: { data: [], total: 0, per_page: 25, current_page: 1, last_page: 1, from: 0, to: 0 },
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
      exportFilters: {
        from_date: '',
        to_date: '',
        type: 'all'
      }
    }
  },
  async created() { 
    this.setDefaultDates();
    await Promise.all([
      this.load(1),
      this.loadProducts(),
      this.loadSummary()
    ]);
    this.loading = false;
  },
  methods: {
    setDefaultDates() {
      const today = new Date();
      const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
      
      this.filters.from_date = thirtyDaysAgo.toISOString().split('T')[0];
      this.filters.to_date = today.toISOString().split('T')[0];
      this.exportFilters.from_date = thirtyDaysAgo.toISOString().split('T')[0];
      this.exportFilters.to_date = today.toISOString().split('T')[0];
    },
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
        console.log('Dashboard summary response:', res.data);
        console.log('Inventory data:', res.data.inventory);
        this.summary = res.data.inventory || {};
      } catch (e) {
        console.error('Failed to load summary:', e);
      }
    },
    async load(page = 1) {
      this.loading = true;
      this.currentPage = page;
      try {
        const params = { page: page };
        if (this.filters.from_date) params.from_date = this.filters.from_date;
        if (this.filters.to_date) params.to_date = this.filters.to_date;
        if (this.filters.type && this.filters.type !== 'all') params.type = this.filters.type;
        
        const res = await axios.get(`/api/inventory`, { params });
        this.movements = res.data;
      } catch (e) {
        console.error('Failed to load movements:', e);
        Swal.fire({ 
          icon: 'error', 
          title: 'Load Failed', 
          text: 'Failed to load inventory movements' 
        });
      } finally {
        this.loading = false;
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
      this.setDefaultDates(); // Reset to default dates
      this.load(1);
      this.loadSummary();
      this.loadProducts(); // Refresh product options with new stock levels
    },
    openExportModal() {
      this.$bvModal.show('export-excel-modal');
    },
         async exportInventoryCsv() {
      try {
        const params = {};
        if (this.exportFilters.from_date) params.from_date = this.exportFilters.from_date;
        if (this.exportFilters.to_date) params.to_date = this.exportFilters.to_date;
        if (this.exportFilters.type && this.exportFilters.type !== 'all') params.type = this.exportFilters.type;

        const res = await axios.get(`/api/reports/inventory-export`, { 
          params,
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `inventory-report-${this.exportFilters.from_date || 'all'}-to-${this.exportFilters.to_date || 'all'}.csv`);
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

        this.$bvModal.hide('export-excel-modal');
      } catch (e) {
        console.error('Failed to export inventory report:', e);
                 Swal.fire({ 
           icon: 'error', 
           title: 'Export Failed', 
           text: 'Failed to export inventory report to CSV' 
         });
      }
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

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  text-align: center;
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>


