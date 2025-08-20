<template>
  <b-container fluid>
    <b-row>
      <b-col lg="8">
        <b-row>
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
                      <i class="fas fa-plus"></i> Add
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
            <b-form-group label="GCash Amount">
              <b-input-group>
                <b-input-group-prepend><b-input-group-text>₱</b-input-group-text></b-input-group-prepend>
                <b-form-input
                  type="number"
                  v-model.number="payment.amount"
                  placeholder="0.00"
                  step="0.01"
                  min="0"
                  :state="amountState"
                />
              </b-input-group>
              <b-form-invalid-feedback v-if="amountState === false">
                Amount must be at least {{ total.toFixed(2) }}
              </b-form-invalid-feedback>
            </b-form-group>
            <b-button variant="success" block @click="checkout" :disabled="!cart.length || !isPaymentValid">Complete Sale</b-button>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
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
      cartFields: [
        { key: 'name', label: 'Item' },
        { key: 'quantity', label: 'Qty', tdClass: 'w-25' },
        { key: 'unit_price', label: 'Price', class: 'text-end', tdClass: 'text-end' },
        { key: 'line_total', label: 'Total', class: 'text-end', tdClass: 'text-end' },
        { key: 'actions', label: '', tdClass: 'text-end' },
      ],
      payOptions: [ { value: 'mobile', text: 'GCash' } ],
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
    }
  },
  created() {
    this.loadProducts();
  },
  methods: {
    async loadProducts() {
      try {
        const res = await axios.get('/api/products');
        this.products = res.data.data || [];
      } catch (e) {
        // ignore
      }
    },
    addProduct(p) {
      const existing = this.cart.find(x => x.product_id === p.id);
      if (existing) existing.quantity += 1; else this.cart.push({ product_id: p.id, name: p.name, unit_price: Number(p.price), quantity: 1 });
    },
    recalc() {},
    remove(idx) { this.cart.splice(idx, 1); },
    async checkout() {
      if (!this.cart.length) return;
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
        this.cart = []; this.payment = { method: 'mobile', amount: 0 };
        this.loadProducts();
      } catch (e) {
        const msg = (e.response && (e.response.data && (e.response.data.message || JSON.stringify(e.response.data)))) || e.message;
        Swal.fire({ icon: 'error', title: 'Checkout failed', text: msg });
      }
    },
    generateReceiptHtml(sale) {
      const lines = this.cart.map(l => `<tr><td>${l.name}</td><td style="text-align:right;">${l.quantity}</td><td style="text-align:right;">${l.unit_price.toFixed(2)}</td><td style="text-align:right;">${(l.quantity*l.unit_price).toFixed(2)}</td></tr>`).join('');
      const total = this.total.toFixed(2);
      const now = new Date();
      const y = now.getFullYear(), m = String(now.getMonth()+1).padStart(2,'0'), d = String(now.getDate()).padStart(2,'0');
      return `<!doctype html><html><head><meta charset="utf-8"><title>Receipt</title>
        <style>body{font-family:Arial,sans-serif;padding:16px;} h2{margin:0 0 8px;} table{width:100%;border-collapse:collapse;} td,th{padding:6px;border-bottom:1px solid #eee;} th{text-align:left;} .tot{font-weight:bold;}</style>
      </head><body>
        <h2>Receipt</h2>
        <div>Date: ${y}-${m}-${d}</div>
        <hr/>
        <table>
          <thead><tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
          <tbody>${lines}</tbody>
          <tfoot><tr><td colspan="3" class="tot" style="text-align:right;">Grand Total</td><td class="tot" style="text-align:right;">${total}</td></tr></tfoot>
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
}

@media (max-width: 575.98px) {
  .product-image-container { height: 160px; }
  .product-info { padding: 12px; }
  .product-title { font-size: 0.9rem; }
  .product-price { font-size: 1.1rem; }
}
</style>



