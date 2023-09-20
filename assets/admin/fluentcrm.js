/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!../admin/Pieces/Pagination.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!../admin/Pieces/Pagination.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: 'Pagination',
  props: {
    pagination: {
      required: true,
      type: Object
    },
    extra_sizes: {
      required: false,
      type: Array,
      "default": function _default() {
        return [];
      }
    },
    hide_on_single: {
      required: false,
      type: Boolean,
      "default": function _default() {
        return true;
      }
    }
  },
  computed: {
    page_sizes: function page_sizes() {
      var sizes = [];
      if (this.pagination.per_page < 10) {
        sizes.push(this.pagination.per_page);
      }
      var defaults = [10, 20, 50, 80, 100, 120, 150];
      return [].concat(sizes, defaults, _toConsumableArray(this.extra_sizes));
    }
  },
  methods: {
    changePage: function changePage(page) {
      this.pagination.current_page = page;
      this.$emit('fetch');
    },
    changeSize: function changeSize(size) {
      this.pagination.per_page = size;
      this.$emit('per_page_change', size);
      this.$emit('fetch');
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _admin_Pieces_Pagination_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../admin/Pieces/Pagination.vue */ "../admin/Pieces/Pagination.vue");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: 'ScheduledMeetings',
  props: ['subscriber'],
  components: {
    Pagination: _admin_Pieces_Pagination_vue__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  data: function data() {
    return {
      loading: false,
      meetings: [],
      pagination: {
        per_page: 10,
        current_page: 1,
        total: 0
      },
      columnsConfig: {}
    };
  },
  computed: {
    table_columns: function table_columns() {
      var columns = [];
      if (this.meetings.length) {
        columns = this.meetings[0];
      }
      return columns;
    }
  },
  methods: {
    fetch: function fetch() {
      var _this = this;
      this.loading = true;
      this.$get("subscribers/".concat(this.subscriber.id, "/scheduled-meetings"), {
        page: this.pagination.current_page,
        per_page: this.pagination.per_page
      }).then(function (response) {
        _this.meetings = response.meetings.data;
        _this.pagination.total = parseInt(response.meetings.total);
        if (response.meetings.columns_config) {
          _this.columnsConfig = response.meetings.columns_config;
        }
      })["catch"](function (errors) {
        console.log(errors);
      })["finally"](function () {
        _this.loading = false;
      });
    }
  },
  mounted: function mounted() {
    console.log("Yes.. ScheduledMeetings.vue is loadedn");
    this.fetch();
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!../admin/Pieces/Pagination.vue?vue&type=template&id=4b86c0c6&":
/*!******************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!../admin/Pieces/Pagination.vue?vue&type=template&id=4b86c0c6& ***!
  \******************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c("el-pagination", {
    staticClass: "fluent-pagination",
    attrs: {
      background: false,
      layout: "total, sizes, prev, pager, next",
      "hide-on-single-page": _vm.hide_on_single,
      "current-page": _vm.pagination.current_page,
      "page-sizes": _vm.page_sizes,
      "page-size": _vm.pagination.per_page,
      total: _vm.pagination.total
    },
    on: {
      "current-change": _vm.changePage,
      "size-change": _vm.changeSize,
      "update:currentPage": function updateCurrentPage($event) {
        return _vm.$set(_vm.pagination, "current_page", $event);
      },
      "update:current-page": function updateCurrentPage($event) {
        return _vm.$set(_vm.pagination, "current_page", $event);
      }
    }
  });
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=template&id=42449727&":
/*!***********************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=template&id=42449727& ***!
  \***********************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c("div", {
    staticClass: "purchase_history_block"
  }, [_c("h3", {
    staticClass: "history_title"
  }, [_vm._v("Title")]), _vm._v(" "), _c("div", {
    staticClass: "provider_data"
  }, [_c("el-table", {
    directives: [{
      name: "loading",
      rawName: "v-loading",
      value: _vm.loading,
      expression: "loading"
    }],
    attrs: {
      "empty-text": _vm.$t("No Data Found"),
      border: "",
      stripe: "",
      data: _vm.meetings
    }
  }, [_vm._l(_vm.table_columns, function (column, columnKey) {
    return _c("el-table-column", {
      key: columnKey,
      attrs: {
        width: _vm.columnsConfig[columnKey] ? _vm.columnsConfig[columnKey].width : "",
        label: _vm.columnsConfig[columnKey] && _vm.columnsConfig[columnKey].label ? _vm.columnsConfig[columnKey].label : _vm.ucFirst(columnKey)
      },
      scopedSlots: _vm._u([{
        key: "default",
        fn: function fn(scope) {
          return [_c("div", {
            domProps: {
              innerHTML: _vm._s(scope.row[columnKey])
            }
          })];
        }
      }], null, true)
    }, [_vm._v("\n            >\n                ")]);
  }), _vm._v(" "), _c("template", {
    slot: "empty"
  }, [_c("p", [_vm._v(_vm._s(_vm.$t("Scheduled Meetings")) + " "), _c("b", [_vm._v("Fluent Booking")]), _vm._v(" " + _vm._s(_vm.$t("no_scheduled_meetings_found_for_this_subscriber")))])])], 2), _vm._v(" "), _c("pagination", {
    attrs: {
      pagination: _vm.pagination
    },
    on: {
      fetch: _vm.fetch
    }
  })], 1)]);
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "../admin/Pieces/Pagination.vue":
/*!**************************************!*\
  !*** ../admin/Pieces/Pagination.vue ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Pagination_vue_vue_type_template_id_4b86c0c6___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Pagination.vue?vue&type=template&id=4b86c0c6& */ "../admin/Pieces/Pagination.vue?vue&type=template&id=4b86c0c6&");
/* harmony import */ var _Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Pagination.vue?vue&type=script&lang=js& */ "../admin/Pieces/Pagination.vue?vue&type=script&lang=js&");
/* harmony import */ var _fluentCRMProfile_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../fluentCRMProfile/node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_fluentCRMProfile_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Pagination_vue_vue_type_template_id_4b86c0c6___WEBPACK_IMPORTED_MODULE_0__.render,
  _Pagination_vue_vue_type_template_id_4b86c0c6___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "admin/Pieces/Pagination.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./ScheduledMeetings.vue":
/*!*******************************!*\
  !*** ./ScheduledMeetings.vue ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ScheduledMeetings_vue_vue_type_template_id_42449727___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ScheduledMeetings.vue?vue&type=template&id=42449727& */ "./ScheduledMeetings.vue?vue&type=template&id=42449727&");
/* harmony import */ var _ScheduledMeetings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ScheduledMeetings.vue?vue&type=script&lang=js& */ "./ScheduledMeetings.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !./node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ScheduledMeetings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ScheduledMeetings_vue_vue_type_template_id_42449727___WEBPACK_IMPORTED_MODULE_0__.render,
  _ScheduledMeetings_vue_vue_type_template_id_42449727___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "ScheduledMeetings.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "../admin/Pieces/Pagination.vue?vue&type=script&lang=js&":
/*!***************************************************************!*\
  !*** ../admin/Pieces/Pagination.vue?vue&type=script&lang=js& ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _fluentCRMProfile_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_fluentCRMProfile_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../fluentCRMProfile/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../fluentCRMProfile/node_modules/vue-loader/lib/index.js??vue-loader-options!./Pagination.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!../admin/Pieces/Pagination.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_fluentCRMProfile_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_fluentCRMProfile_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./ScheduledMeetings.vue?vue&type=script&lang=js&":
/*!********************************************************!*\
  !*** ./ScheduledMeetings.vue?vue&type=script&lang=js& ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ScheduledMeetings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ScheduledMeetings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "../admin/Pieces/Pagination.vue?vue&type=template&id=4b86c0c6&":
/*!*********************************************************************!*\
  !*** ../admin/Pieces/Pagination.vue?vue&type=template&id=4b86c0c6& ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _fluentCRMProfile_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_fluentCRMProfile_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_fluentCRMProfile_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_template_id_4b86c0c6___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _fluentCRMProfile_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_fluentCRMProfile_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_fluentCRMProfile_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_template_id_4b86c0c6___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _fluentCRMProfile_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_fluentCRMProfile_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_fluentCRMProfile_node_modules_vue_loader_lib_index_js_vue_loader_options_Pagination_vue_vue_type_template_id_4b86c0c6___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../fluentCRMProfile/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../fluentCRMProfile/node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../fluentCRMProfile/node_modules/vue-loader/lib/index.js??vue-loader-options!./Pagination.vue?vue&type=template&id=4b86c0c6& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!../admin/Pieces/Pagination.vue?vue&type=template&id=4b86c0c6&");


/***/ }),

/***/ "./ScheduledMeetings.vue?vue&type=template&id=42449727&":
/*!**************************************************************!*\
  !*** ./ScheduledMeetings.vue?vue&type=template&id=42449727& ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_ScheduledMeetings_vue_vue_type_template_id_42449727___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_ScheduledMeetings_vue_vue_type_template_id_42449727___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_ScheduledMeetings_vue_vue_type_template_id_42449727___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=template&id=42449727& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./ScheduledMeetings.vue?vue&type=template&id=42449727&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ normalizeComponent)
/* harmony export */ });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent(
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */,
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options =
    typeof scriptExports === 'function' ? scriptExports.options : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) {
    // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () {
          injectStyles.call(
            this,
            (options.functional ? this.parent : this).$root.$options.shadowRoot
          )
        }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functional component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection(h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing ? [].concat(existing, hook) : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./fluentcrm.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ScheduledMeetings__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ScheduledMeetings */ "./ScheduledMeetings.vue");

var CrmApp = window.FLUENTCRM;
console.log("CrmApp");
CrmApp.addFilter('fluentcrm_profile_routes', 'fluent_booking', function (profileRoute) {
  profileRoute.children.push({
    name: 'fluent_booking',
    path: 'scheduled_meetings',
    component: _ScheduledMeetings__WEBPACK_IMPORTED_MODULE_0__["default"],
    meta: {
      parent: 'subscribers',
      active_menu: 'contacts',
      permission: 'fcrm_read_contacts'
    }
  });
  return profileRoute;
}, 1);
CrmApp.addFilter('fluentcrm_profile_sections', 'fluent_booking', function (sections) {
  sections.fluent_booking = {
    title: 'Bookings',
    name: 'fluent_booking',
    handler: 'route'
  };
  return sections;
}, 1);
})();

/******/ })()
;