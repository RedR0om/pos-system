<template>
  <b-container fluid>
    <!-- Loading overlay for initial page load -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
          <span class="sr-only">Loading...</span>
        </div>
        <div class="mt-3">Loading products...</div>
      </div>
    </div>

    <b-row>
      <b-col lg="8">
        <!-- Loading skeleton when loading -->
        <div v-if="isLoading" class="loading-skeleton">
          <b-row>
            <b-col sm="6" md="3" v-for="n in 8" :key="n" class="mb-3">
              <b-card class="product-card h-100 skeleton-card" no-body>
                <div class="product-image-container skeleton-image"></div>
                <div class="product-info skeleton-info">
                  <div class="skeleton-title"></div>
                  <div class="skeleton-price"></div>
                  <div class="skeleton-button"></div>
                </div>
              </b-card>
            </b-col>
          </b-row>
        </div>
        
        <!-- Products when loaded -->
        <b-row v-else>
          <b-col sm="6" md="3" v-for="p in products" :key="p.id" class="mb-3">
            <b-card class="product-card h-100" no-body>
              <div class="product-image-container">
                <b-img :src="p.image_url || placeholder" alt="" class="product-thumb" fluid />
              </div>
              <div class="product-info">
                <b-row>
                  <b-col>
                    <div class="product-title text-truncate" :title="p.name">{{ p.name }}</div>
                    <div class="product-price">₱{{ Number(p.price).toFixed(2) }}</div>
                  </b-col>
                  <b-col>
                    <b-button size="sm" variant="primary" class="add-btn" @click="addProduct(p)">
                      <i class="fas fa-plus"></i>
                    </b-button>
                  </b-col>
                </b-row>
              </div>
            </b-card>
          </b-col>
        </b-row>
      </b-col>
      <b-col lg="4">
        <b-card>
          <b-table small hover :items="cart" :fields="cartFields" responsive>
            <template #cell(quantity)="{ item }">
              <b-form-input type="number" min="1" v-model.number="item.quantity" @change="recalc" />
            </template>
            <template #cell(unit_price)="{ item }">{{ item.unit_price.toFixed(2) }}</template>
            <template #cell(line_total)="{ item }">{{ (item.quantity * item.unit_price).toFixed(2) }}</template>
            <template #cell(actions)="{ index }">
              <b-button size="sm" variant="outline-danger" @click="remove(index)">✕</b-button>
            </template>
            <template #empty>
              <div class="text-center text-muted">No items</div>
            </template>
          </b-table>
        </b-card>
        <b-card class="mt-3">
          <h5 class="card-title">Summary</h5>
          <div class="d-flex justify-content-between"><span>Subtotal</span><strong>{{ subtotal.toFixed(2) }}</strong></div>
          <div class="d-flex justify-content-between fs-5 border-top pt-2"><span>Total</span><strong>{{ total.toFixed(2) }}</strong></div>
        </b-card>
        <b-card no-body class="mt-3">
          <template #header>
            Payment
          </template>
          <b-card-body>
            <!-- Payment Method Selection -->
            <b-form-group label="Payment Method" class="mb-3">
              <b-form-radio-group v-model="payment.method" name="payment-method">
                <b-form-radio value="mobile" class="mb-2">
                  <i class="fas fa-mobile-alt me-2 text-success"></i>GCash
                </b-form-radio>
                <b-form-radio value="cash" class="mb-2">
                  <i class="fas fa-money-bill-wave me-2 text-success"></i>Cash
                </b-form-radio>
              </b-form-radio-group>
            </b-form-group>

            <!-- GCash QR Code -->
            <div v-if="payment.method === 'mobile'" class="text-center mb-3">
              <div class="qr-code-container">
                <img src="https://res.cloudinary.com/dkcjftn5c/image/upload/v1755828253/sample_gcash_zowrty.png" 
                     alt="GCash QR Code" 
                     class="qr-code-image" />
                <div class="qr-code-label mt-2">Scan QR Code to Pay</div>
                <b-button 
                  variant="outline-primary" 
                  size="sm" 
                  class="mt-2"
                  @click="openQRModal"
                >
                  <i class="fas fa-expand-alt me-1"></i> View QR Code
                </b-button>
              </div>
            </div>

            <!-- Amount Input -->
            <b-form-group :label="payment.method === 'mobile' ? 'GCash Amount' : 'Cash Amount'">
              <b-input-group>
                <b-input-group-prepend><b-input-group-text>₱</b-input-group-text></b-input-group-prepend>
                <b-form-input
                  type="number"
                  v-model.number="payment.amount"
                  :placeholder="payment.method === 'mobile' ? '0.00' : '0.00'"
                  step="0.01"
                  min="0"
                  :state="amountState"
                  :disabled="isCheckoutLoading"
                />
              </b-input-group>
              <b-form-invalid-feedback v-if="amountState === false">
                Amount must be at least {{ total.toFixed(2) }}
              </b-form-invalid-feedback>
            </b-form-group>

            <!-- Change Display for Cash -->
            <div v-if="payment.method === 'cash' && payment.amount > total && total > 0" class="change-display mb-3">
              <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                <span class="font-weight-bold">Change:</span>
                <span class="text-success font-weight-bold fs-5">₱{{ change.toFixed(2) }}</span>
              </div>
            </div>

            <b-button 
              variant="success" 
              block 
              @click="checkout" 
              :disabled="!cart.length || !isPaymentValid || isCheckoutLoading"
              :class="{ 'loading-button': isCheckoutLoading }"
            >
              <span v-if="isCheckoutLoading">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Processing...
              </span>
              <span v-else>Complete Sale</span>
            </b-button>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>

    <!-- QR Code Modal -->
    <b-modal 
      v-model="showQRModal" 
      title="GCash QR Code" 
      size="xl" 
      centered
      hide-footer
      class="qr-modal"
    >
      <div class="text-center">
        <div class="qr-modal-container">
          <img 
            src="https://res.cloudinary.com/dkcjftn5c/image/upload/v1755828253/sample_gcash_zowrty.png" 
            alt="GCash QR Code" 
            class="qr-modal-image" 
          />
          <div class="qr-modal-instructions mt-3">
            <h5>Scan this QR Code with your GCash app</h5>
            <p class="text-muted">Open your GCash app and tap the scan button to pay</p>
          </div>
        </div>
      </div>
    </b-modal>
  </b-container>
</template>

<script>
import Swal from 'sweetalert2'
import axios from 'axios'
export default {
  name: 'Pos',
  data() {
    return {
      products: [],
      cart: [],
      payment: { method: 'mobile', amount: 0 },
      placeholder: '/images/placeholder.svg',
      isLoading: true,
      isCheckoutLoading: false,
      showQRModal: false,
      cartFields: [
        { key: 'name', label: 'Item' },
        { key: 'quantity', label: 'Qty', tdClass: 'w-25' },
        { key: 'unit_price', label: 'Price', class: 'text-end', tdClass: 'text-end' },
        { key: 'line_total', label: 'Total', class: 'text-end', tdClass: 'text-end' },
        { key: 'actions', label: '', tdClass: 'text-end' },
      ],
      payOptions: [ { value: 'mobile', text: 'GCash' }, { value: 'cash', text: 'Cash' } ],
    }
  },
  computed: {
    subtotal() {
      return this.cart.reduce((s, l) => s + l.quantity * l.unit_price, 0);
    },
    total() {
      return this.subtotal;
    },
    isPaymentValid() {
      return this.payment.amount >= this.total && this.total > 0;
    },
    amountState() {
      if (this.payment.amount === null || this.payment.amount === undefined) return null;
      return this.payment.amount >= this.total && this.total > 0;
    },
    change() {
      if (this.payment.method === 'cash' && this.payment.amount > this.total) {
        return this.payment.amount - this.total;
      }
      return 0;
    }
  },
  created() {
    this.loadProducts();
  },
  methods: {
    openQRModal() {
      this.showQRModal = true;
    },
    async loadProducts() {
      this.isLoading = true;
      const startTime = Date.now();
      const minLoadingTime = 800; // Minimum loading time in milliseconds
      
      try {
        const res = await axios.get('/api/products');
        this.products = res.data.data || [];
        
        // Ensure minimum loading time for better UX
        const elapsedTime = Date.now() - startTime;
        if (elapsedTime < minLoadingTime) {
          await new Promise(resolve => setTimeout(resolve, minLoadingTime - elapsedTime));
        }
      } catch (e) {
        console.error('Error loading products:', e);
        Swal.fire({ 
          icon: 'error', 
          title: 'Error', 
          text: 'Failed to load products. Please refresh the page.' 
        });
      } finally {
        this.isLoading = false;
      }
    },
    addProduct(p) {
      const existing = this.cart.find(x => x.product_id === p.id);
      if (existing) existing.quantity += 1; else this.cart.push({ product_id: p.id, name: p.name, unit_price: Number(p.price), quantity: 1 });
    },
    recalc() {},
    remove(idx) { this.cart.splice(idx, 1); },
    async checkout() {
      if (!this.cart.length || this.isCheckoutLoading) return;
      
      this.isCheckoutLoading = true;
      const payload = {
        items: this.cart.map(l => ({ product_id: l.product_id, quantity: l.quantity, unit_price: l.unit_price })),
        payments: [this.payment]
      };
      
      try {
        const res = await axios.post('/api/sales', payload);
        const sale = res.data || {};
        // Print receipt and offer Word download
        const receiptHtml = this.generateReceiptHtml(sale);
        this.openPrintWindow(receiptHtml);
        this.downloadWordDoc(receiptHtml, `receipt-${sale.id || Date.now()}.doc`);
        Swal.fire({ icon: 'success', title: 'Sale completed', timer: 1200, showConfirmButton: false });
        this.cart = []; 
        this.payment = { method: 'mobile', amount: 0 };
        this.loadProducts();
      } catch (e) {
        const msg = (e.response && (e.response.data && (e.response.data.message || JSON.stringify(e.response.data)))) || e.message;
        Swal.fire({ icon: 'error', title: 'Checkout failed', text: msg });
      } finally {
        this.isCheckoutLoading = false;
      }
    },
    generateReceiptHtml(sale) {
      const lines = this.cart.map(l => `<tr><td>${l.name}</td><td style="text-align:right;">${l.quantity}</td><td style="text-align:right;">${l.unit_price.toFixed(2)}</td><td style="text-align:right;">${(l.quantity*l.unit_price).toFixed(2)}</td></tr>`).join('');
      const total = this.total.toFixed(2);
      const paymentMethod = this.payment.method === 'mobile' ? 'GCash' : 'Cash';
      const amountPaid = this.payment.amount.toFixed(2);
      const change = this.change.toFixed(2);
      const now = new Date();
      const y = now.getFullYear(), m = String(now.getMonth()+1).padStart(2,'0'), d = String(now.getDate()).padStart(2,'0');
      const time = now.toLocaleTimeString();
      
      return `<!doctype html><html><head><meta charset="utf-8"><title>Receipt</title>
        <style>
          body{font-family:Arial,sans-serif;padding:16px;max-width:400px;margin:0 auto;}
          h2{margin:0 0 8px;text-align:center;color:#333;}
          .receipt-header{text-align:center;margin-bottom:20px;border-bottom:2px solid #333;padding-bottom:10px;}
          .receipt-info{text-align:center;margin-bottom:15px;color:#666;}
          table{width:100%;border-collapse:collapse;margin-bottom:20px;}
          td,th{padding:8px;border-bottom:1px solid #eee;text-align:left;}
          th{background-color:#f8f9fa;font-weight:bold;}
          .tot{font-weight:bold;background-color:#f8f9fa;}
          .payment-info{margin-top:20px;padding:15px;background-color:#f8f9fa;border-radius:8px;}
          .payment-row{display:flex;justify-content:space-between;margin-bottom:8px;}
          .change-row{color:#28a745;font-weight:bold;font-size:1.1em;}
          .footer{text-align:center;margin-top:20px;color:#666;font-size:0.9em;border-top:1px solid #eee;padding-top:15px;}
        </style>
      </head><body>
        <div class="receipt-header">
          <h2>RECEIPT</h2>
          <div class="receipt-info">
            <div>Date: ${y}-${m}-${d}</div>
            <div>Time: ${time}</div>
          </div>
        </div>
        
        <table>
          <thead><tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
          <tbody>${lines}</tbody>
          <tfoot><tr><td colspan="3" class="tot" style="text-align:right;">Grand Total</td><td class="tot" style="text-align:right;">₱${total}</td></tr></tfoot>
        </table>
        
        <div class="payment-info">
          <div class="payment-row">
            <span>Payment Method:</span>
            <span><strong>${paymentMethod}</strong></span>
          </div>
          <div class="payment-row">
            <span>Amount Paid:</span>
            <span><strong>₱${amountPaid}</strong></span>
          </div>
          ${this.payment.method === 'cash' && this.change > 0 ? `<div class="payment-row change-row">
            <span>Change:</span>
            <span><strong>₱${change}</strong></span>
          </div>` : ''}
        </div>
        
        <div class="footer">
          Thank you for your purchase!<br>
          Please come again.
        </div>
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
    downloadWordDoc(html, filename) {
      const header = `<!doctype html><html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="utf-8"></head><body>`;
      const footer = `</body></html>`;
      const blob = new Blob([header + html + footer], { type: 'application/msword' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }
  }
}
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-spinner {
  text-align: center;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.loading-skeleton {
  width: 100%;
}

.skeleton-card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.skeleton-image {
  height: 180px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton-info {
  background: #f8f9fa;
  padding: 16px;
}

.skeleton-title {
  height: 16px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  margin-bottom: 8px;
  border-radius: 4px;
}

.skeleton-price {
  height: 20px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  margin-bottom: 12px;
  border-radius: 4px;
  width: 60%;
}

.skeleton-button {
  height: 32px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 8px;
  width: 100%;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.loading-button {
  position: relative;
  pointer-events: none;
}

.loading-button:disabled {
  opacity: 0.8;
}

.qr-code-container {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  border: 2px dashed #dee2e6;
}

.qr-code-image {
  width: 150px;
  height: 150px;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.qr-code-label {
  color: #6c757d;
  font-weight: 600;
  font-size: 0.9rem;
}

.qr-modal-container {
  padding: 20px;
}

.qr-modal-image {
  width: 300px;
  height: 300px;
  object-fit: contain;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.qr-modal-instructions h5 {
  color: #28a745;
  margin-bottom: 10px;
}

.change-display {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.product-card { 
  border: none; 
  border-radius: 12px; 
  overflow: hidden; 
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  background: #fff;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.product-image-container { 
  position: relative; 
  width: 100%; 
  height: 180px; 
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-thumb { 
  width: 100%; 
  height: 100%; 
  object-fit: contain;
  border-radius: 8px;
}

.product-info { 
  background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
  color: #fff;
  padding: 16px;
  position: relative;
}

.product-title { 
  font-weight: 600; 
  font-size: 1rem; 
  margin-bottom: 8px;
  color: #f9fafb;
  line-height: 1.3;
}

.product-price { 
  font-size: 1.25rem; 
  font-weight: bold; 
  margin-bottom: 12px;
  color: #10b981;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.add-btn { 
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  border: none;
  color: #fff;
  color: #fff;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.add-btn:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.add-btn i { 
  margin-right: 6px; 
}

@media (min-width: 992px) { /* lg */
  .product-image-container { height: 200px; }
  .skeleton-image { height: 200px; }
}

@media (max-width: 575.98px) {
  .product-image-container { height: 160px; }
  .skeleton-image { height: 160px; }
  .product-info { padding: 12px; }
  .skeleton-info { padding: 12px; }
  .product-title { font-size: 0.9rem; }
  .product-price { font-size: 1.1rem; }
  .qr-code-image { width: 120px; height: 120px; }
  .qr-modal-image { width: 250px; height: 250px; }
}
</style>



