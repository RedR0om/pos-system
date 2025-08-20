"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_Sales_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/Sales.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/Sales.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: 'Sales',
  data: function data() {
    return {
      sales: {
        data: [],
        total: 0,
        per_page: 20
      },
      currentPage: 1,
      fields: [{
        key: 'id',
        label: 'ID',
        tdClass: 'text-muted'
      }, {
        key: 'paid_at',
        label: 'Date'
      }, {
        key: 'total',
        label: 'Total',
        "class": 'text-end',
        tdClass: 'text-end'
      }, {
        key: 'actions',
        label: '',
        "class": 'text-end'
      }],
      receiptHtml: ''
    };
  },
  created: function created() {
    this.load(1);
  },
  computed: {
    salesLinks: function salesLinks() {
      return this.sales && this.sales.links ? this.sales.links : [];
    }
  },
  methods: {
    load: function load() {
      var _arguments = arguments,
        _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var page, res;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              page = _arguments.length > 0 && _arguments[0] !== undefined ? _arguments[0] : 1;
              _this.currentPage = page;
              _context.n = 1;
              return axios__WEBPACK_IMPORTED_MODULE_0___default().get('/api/sales', {
                params: {
                  page: page
                }
              });
            case 1:
              res = _context.v;
              _this.sales = res.data;
            case 2:
              return _context.a(2);
          }
        }, _callee);
      }))();
    },
    viewReceipt: function viewReceipt(sale) {
      this.receiptHtml = this.buildReceiptFromSale(sale);
      this.$bvModal.show('receipt-modal');
    },
    printReceipt: function printReceipt(sale) {
      var html = this.buildReceiptFromSale(sale);
      this.openPrintWindow(html);
    },
    formatDate: function formatDate(dt) {
      if (!dt) return '';
      var d = new Date(dt);
      var y = d.getFullYear(),
        m = String(d.getMonth() + 1).padStart(2, '0'),
        day = String(d.getDate()).padStart(2, '0');
      return "".concat(y, "-").concat(m, "-").concat(day);
    },
    buildReceiptFromSale: function buildReceiptFromSale(sale) {
      var items = (sale.items || []).map(function (it) {
        return {
          name: it.product && it.product.name || it.product_id,
          qty: it.quantity,
          price: Number(it.unit_price)
        };
      });
      var lines = items.map(function (l) {
        return "<tr><td>".concat(l.name, "</td><td style=\"text-align:right;\">").concat(l.qty, "</td><td style=\"text-align:right;\">").concat(l.price.toFixed(2), "</td><td style=\"text-align:right;\">").concat((l.qty * l.price).toFixed(2), "</td></tr>");
      }).join('');
      var total = Number(sale.total || 0).toFixed(2);
      var paid = this.formatDate(sale.paid_at);
      return "<!doctype html><html><head><meta charset=\"utf-8\"><title>Receipt</title>\n        <style>body{font-family:Arial,sans-serif;padding:16px;} h2{margin:0 0 8px;} table{width:100%;border-collapse:collapse;} td,th{padding:6px;border-bottom:1px solid #eee;} th{text-align:left;} .tot{font-weight:bold;}</style>\n      </head><body>\n        <h2>Receipt</h2>\n        <div>Date: ".concat(paid, "</div>\n        <hr/>\n        <table>\n          <thead><tr><th>Item</th><th style=\"text-align:right;\">Qty</th><th style=\"text-align:right;\">Price</th><th style=\"text-align:right;\">Total</th></tr></thead>\n          <tbody>").concat(lines, "</tbody>\n          <tfoot><tr><td colspan=\"3\" class=\"tot\" style=\"text-align:right;\">Grand Total</td><td class=\"tot\" style=\"text-align:right;\">").concat(total, "</td></tr></tfoot>\n        </table>\n      </body></html>");
    },
    openPrintWindow: function openPrintWindow(html) {
      var w = window.open('', 'PRINT', 'height=600,width=800');
      if (!w) return;
      w.document.write(html);
      w.document.close();
      w.focus();
      w.print();
      w.close();
    },
    downloadWordDoc: function downloadWordDoc(html, filename) {
      var header = "<!doctype html><html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:w=\"urn:schemas-microsoft-com:office:word\" xmlns=\"http://www.w3.org/TR/REC-html40\"><head><meta charset=\"utf-8\"></head><body>";
      var footer = "</body></html>";
      var blob = new Blob([header + html + footer], {
        type: 'application/msword'
      });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/Sales.vue?vue&type=template&id=6545489e":
/*!**********************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/Sales.vue?vue&type=template&id=6545489e ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c("b-container", {
    attrs: {
      fluid: ""
    }
  }, [_c("div", {
    staticClass: "d-flex align-items-center justify-content-between mb-3"
  }, [_c("h2", {
    staticClass: "mb-0"
  }, [_vm._v("Sales History")])]), _vm._v(" "), _c("b-card", [_c("b-table", {
    attrs: {
      small: "",
      hover: "",
      items: _vm.sales.data,
      fields: _vm.fields,
      responsive: ""
    },
    scopedSlots: _vm._u([{
      key: "cell(paid_at)",
      fn: function fn(_ref) {
        var item = _ref.item;
        return [_vm._v(_vm._s(_vm.formatDate(item.paid_at)))];
      }
    }, {
      key: "cell(total)",
      fn: function fn(_ref2) {
        var item = _ref2.item;
        return [_vm._v(_vm._s(Number(item.total).toFixed(2)))];
      }
    }, {
      key: "cell(actions)",
      fn: function fn(_ref3) {
        var item = _ref3.item;
        return [_c("b-button", {
          staticClass: "mr-1",
          attrs: {
            size: "sm",
            variant: "outline-primary"
          },
          on: {
            click: function click($event) {
              return _vm.viewReceipt(item);
            }
          }
        }, [_vm._v("View")]), _vm._v(" "), _c("b-button", {
          attrs: {
            size: "sm",
            variant: "outline-secondary"
          },
          on: {
            click: function click($event) {
              return _vm.printReceipt(item);
            }
          }
        }, [_vm._v("Print")])];
      }
    }, {
      key: "empty",
      fn: function fn() {
        return [_c("div", {
          staticClass: "text-center text-muted"
        }, [_vm._v("No sales found")])];
      },
      proxy: true
    }])
  }), _vm._v(" "), _vm.salesLinks.length ? _c("div", {
    staticClass: "d-flex justify-content-end"
  }, [_c("b-pagination", {
    attrs: {
      "total-rows": _vm.sales.total,
      "per-page": _vm.sales.per_page
    },
    on: {
      input: _vm.load
    },
    model: {
      value: _vm.currentPage,
      callback: function callback($$v) {
        _vm.currentPage = $$v;
      },
      expression: "currentPage"
    }
  })], 1) : _vm._e()], 1), _vm._v(" "), _c("b-modal", {
    attrs: {
      id: "receipt-modal",
      title: "Receipt",
      "hide-footer": "",
      size: "lg"
    }
  }, [_c("div", {
    domProps: {
      innerHTML: _vm._s(_vm.receiptHtml)
    }
  }), _vm._v(" "), _c("div", {
    staticClass: "text-right mt-3"
  }, [_c("b-button", {
    staticClass: "mr-2",
    attrs: {
      size: "sm",
      variant: "secondary"
    },
    on: {
      click: function click($event) {
        return _vm.downloadWordDoc(_vm.receiptHtml, "receipt.doc");
      }
    }
  }, [_vm._v("Download .doc")]), _vm._v(" "), _c("b-button", {
    attrs: {
      size: "sm",
      variant: "primary"
    },
    on: {
      click: function click($event) {
        return _vm.openPrintWindow(_vm.receiptHtml);
      }
    }
  }, [_vm._v("Print")])], 1)])], 1);
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "./resources/js/components/Sales.vue":
/*!*******************************************!*\
  !*** ./resources/js/components/Sales.vue ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Sales_vue_vue_type_template_id_6545489e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Sales.vue?vue&type=template&id=6545489e */ "./resources/js/components/Sales.vue?vue&type=template&id=6545489e");
/* harmony import */ var _Sales_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Sales.vue?vue&type=script&lang=js */ "./resources/js/components/Sales.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Sales_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"],
  _Sales_vue_vue_type_template_id_6545489e__WEBPACK_IMPORTED_MODULE_0__.render,
  _Sales_vue_vue_type_template_id_6545489e__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) // removed by dead control flow
{ var api; }
component.options.__file = "resources/js/components/Sales.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/components/Sales.vue?vue&type=script&lang=js":
/*!*******************************************************************!*\
  !*** ./resources/js/components/Sales.vue?vue&type=script&lang=js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Sales_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Sales.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/Sales.vue?vue&type=script&lang=js");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Sales_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/Sales.vue?vue&type=template&id=6545489e":
/*!*************************************************************************!*\
  !*** ./resources/js/components/Sales.vue?vue&type=template&id=6545489e ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Sales_vue_vue_type_template_id_6545489e__WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Sales_vue_vue_type_template_id_6545489e__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Sales_vue_vue_type_template_id_6545489e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Sales.vue?vue&type=template&id=6545489e */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/Sales.vue?vue&type=template&id=6545489e");


/***/ })

}]);