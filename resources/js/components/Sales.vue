<template>
  <b-container fluid>
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <b-spinner variant="primary" label="Loading..."></b-spinner>
        <div class="mt-2">Loading sales history...</div>
      </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
      <h2 class="mb-0">Sales History</h2>
    </div>
    <b-card>
      <div v-if="loading" class="text-center py-5">
        <b-spinner variant="primary" label="Loading..."></b-spinner>
        <div class="mt-2">Loading sales history...</div>
      </div>
      
      <b-table v-else small hover :items="sales.data" :fields="fields" responsive>
        <template #cell(paid_at)="{ item }">{{ formatDate(item.paid_at) }}</template>
        <template #cell(total)="{ item }">{{ Number(item.total).toFixed(2) }}</template>
        <template #cell(actions)="{ item }">
          <b-button size="sm" variant="outline-primary" class="mr-1" @click="viewReceipt(item)">View</b-button>
          <b-button size="sm" variant="outline-secondary" @click="printReceipt(item)">Print</b-button>
        </template>
        <template #empty>
          <div class="text-center text-muted">No sales found</div>
        </template>
      </b-table>
      <div class="d-flex justify-content-end" v-if="salesLinks.length">
        <b-pagination
          v-model="currentPage"
          :total-rows="sales.total"
          :per-page="sales.per_page"
          @input="load"
        />
      </div>
    </b-card>

    <b-modal id="receipt-modal" title="Receipt" hide-footer size="lg">
      <div v-html="receiptHtml"></div>
      <div class="text-right mt-3">
        <b-button size="sm" variant="secondary" class="mr-2" @click="downloadWordDoc(receiptHtml, 'receipt.doc')">Download .doc</b-button>
        <b-button size="sm" variant="primary" @click="openPrintWindow(receiptHtml)">Print</b-button>
      </div>
    </b-modal>
  </b-container>
</template>

<script>
import axios from 'axios'
export default {
  name: 'Sales',
  data() {
    return {
      loading: true,
      sales: { data: [], total: 0, per_page: 20 },
      currentPage: 1,
      fields: [
        { key: 'id', label: 'ID', tdClass: 'text-muted' },
        { key: 'paid_at', label: 'Date' },
        { key: 'total', label: 'Total', class: 'text-end', tdClass: 'text-end' },
        { key: 'actions', label: '', class: 'text-end' },
      ],
      receiptHtml: ''
    }
  },
  created() {
    this.load(1)
  },
  computed: {
    salesLinks() { return this.sales && this.sales.links ? this.sales.links : [] }
  },
  methods: {
    async load(page = 1) {
      this.loading = true;
      try {
        this.currentPage = page
        const res = await axios.get('/api/sales', { params: { page } })
        this.sales = res.data
      } catch (e) {
        console.error('Failed to load sales:', e);
      } finally {
        this.loading = false;
      }
    },
    viewReceipt(sale) {
      this.receiptHtml = this.buildReceiptFromSale(sale)
      this.$bvModal.show('receipt-modal')
    },
    printReceipt(sale) {
      const html = this.buildReceiptFromSale(sale)
      this.openPrintWindow(html)
    },
    formatDate(dt) {
      if (!dt) return ''
      const d = new Date(dt)
      const y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), day = String(d.getDate()).padStart(2,'0')
      return `${y}-${m}-${day}`
    },
    buildReceiptFromSale(sale) {
      const items = (sale.items || []).map(it => ({ name: (it.product && it.product.name) || it.product_id, qty: it.quantity, price: Number(it.unit_price) }))
      const lines = items.map(l => `<tr><td>${l.name}</td><td style="text-align:right;">${l.qty}</td><td style="text-align:right;">${l.price.toFixed(2)}</td><td style="text-align:right;">${(l.qty*l.price).toFixed(2)}</td></tr>`).join('')
      const total = Number(sale.total || 0).toFixed(2)
      const paid = this.formatDate(sale.paid_at)
      const time = sale.paid_at ? new Date(sale.paid_at).toLocaleTimeString() : ''
      
      // Get payment information
      const payments = sale.payments || []
      const paymentMethod = payments.length > 0 ? this.getPaymentMethodDisplay(payments[0].method) : 'Unknown'
      const amountPaid = payments.length > 0 ? Number(payments[0].amount).toFixed(2) : total
      const change = payments.length > 0 && payments[0].method === 'cash' ? (Number(payments[0].amount) - Number(total)).toFixed(2) : '0.00'
      
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
            <div>Date: ${paid}</div>
            ${time ? `<div>Time: ${time}</div>` : ''}
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
          ${payments.length > 0 && payments[0].method === 'cash' && Number(change) > 0 ? `<div class="payment-row change-row">
            <span>Change:</span>
            <span><strong>₱${change}</strong></span>
          </div>` : ''}
        </div>
        
        <div class="footer">
          Thank you for your purchase!<br>
          Please come again.
        </div>
      </body></html>`
    },
    getPaymentMethodDisplay(method) {
      const methodMap = {
        'cash': 'Cash',
        'card': 'Card',
        'mobile': 'GCash',
        'other': 'Other'
      }
      return methodMap[method] || method
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


