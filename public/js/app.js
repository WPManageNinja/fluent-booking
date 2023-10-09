/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/public/BookingApp.svelte":
/*!********************************************!*\
  !*** ./resources/public/BookingApp.svelte ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _DayPicker_svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DayPicker.svelte */ "./resources/public/DayPicker.svelte");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
/* resources/public/BookingApp.svelte generated by Svelte v3.55.1 */


function create_if_block(ctx) {
  var div;
  var pre;
  var t;
  return {
    c: function c() {
      div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      pre = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("pre");
      t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)( /*appData*/ctx[0]);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div, "class", "fcal_slot_picker");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, div, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div, pre);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(pre, t);
    },
    p: function p(ctx, dirty) {
      if (dirty & /*appData*/1) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t, /*appData*/ctx[0]);
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(div);
    }
  };
}
function create_fragment(ctx) {
  var div9;
  var div7;
  var div6;
  var div2;
  var div0;
  var img;
  var img_src_value;
  var t0;
  var div1;
  var t2;
  var div4;
  var h2;
  var t4;
  var div3;
  var svg;
  var g0;
  var g1;
  var g2;
  var path;
  var t5;
  var span;
  var t8;
  var div5;
  var raw_value = /*slot*/ctx[2].description + "";
  var t9;
  var div8;
  var daypicker;
  var t10;
  var current;
  daypicker = new _DayPicker_svelte__WEBPACK_IMPORTED_MODULE_1__["default"]({});
  var if_block = selectedDate && create_if_block(ctx);
  return {
    c: function c() {
      div9 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div7 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div6 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
      t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div1.textContent = "".concat( /*author*/ctx[1].name);
      t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      div4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      h2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h2");
      h2.textContent = "".concat( /*slot*/ctx[2].title);
      t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      div3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      svg = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.svg_element)("svg");
      g0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.svg_element)("g");
      g1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.svg_element)("g");
      g2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.svg_element)("g");
      path = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.svg_element)("path");
      t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
      span.textContent = "".concat( /*slot*/ctx[2].duration, " minutes");
      t8 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      div5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      t9 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      div8 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(daypicker.$$.fragment);
      t10 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      if (if_block) if_block.c();
      if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = /*author*/ctx[1].avatar)) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(img, "src", img_src_value);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(img, "alt", "Author Avatar");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div0, "class", "fcal_author_avatar");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div1, "class", "fcal_author_name");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div2, "class", "fcal_author");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(h2, "class", "fcal_slot_heading");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(g0, "stroke-width", "0");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(g1, "stroke-linecap", "round");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(g1, "stroke-linejoin", "round");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(path, "d", "M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm5,11H12a1,1,0,0,1-1-1V6a1,1,0,0,1,2,0v5h4a1,1,0,0,1,0,2Z");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(svg, "fill", "#000000");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(svg, "width", "16px");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(svg, "height", "16px");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(svg, "viewBox", "0 0 24 24");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(svg, "xmlns", "http://www.w3.org/2000/svg");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(svg, "stroke", "#000000");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div3, "class", "slot_timing");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div4, "class", "fcal_slot_info");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div5, "class", "fcal_slot_description");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div6, "class", "fcal_slot_wrapper");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div7, "class", "fcal_side");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div8, "class", "fcal_date_wrapper");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div9, "class", "fcal_calendar_inner");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, div9, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div9, div7);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div7, div6);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div6, div2);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div2, div0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div0, img);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div2, t0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div2, div1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div6, t2);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div6, div4);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div4, h2);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div4, t4);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div4, div3);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div3, svg);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(svg, g0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(svg, g1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(svg, g2);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(g2, path);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div3, t5);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div3, span);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div6, t8);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div6, div5);
      div5.innerHTML = raw_value;
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div9, t9);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div9, div8);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(daypicker, div8, null);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div9, t10);
      if (if_block) if_block.m(div9, null);
      current = true;
    },
    p: function p(ctx, _ref) {
      var _ref2 = _slicedToArray(_ref, 1),
        dirty = _ref2[0];
      if (selectedDate) if_block.p(ctx, dirty);
    },
    i: function i(local) {
      if (current) return;
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(daypicker.$$.fragment, local);
      current = true;
    },
    o: function o(local) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(daypicker.$$.fragment, local);
      current = false;
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(div9);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(daypicker);
      if (if_block) if_block.d();
    }
  };
}
var selectedDate = false;
function instance($$self, $$props, $$invalidate) {
  var appData = $$props.appData;
  var author = appData.author_profile;
  var slot = appData.slot;
  console.log(appData);
  $$self.$$set = function ($$props) {
    if ('appData' in $$props) $$invalidate(0, appData = $$props.appData);
  };
  return [appData, author, slot];
}
var BookingApp = /*#__PURE__*/function (_SvelteComponent) {
  _inherits(BookingApp, _SvelteComponent);
  var _super = _createSuper(BookingApp);
  function BookingApp(options) {
    var _this;
    _classCallCheck(this, BookingApp);
    _this = _super.call(this);
    (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(_assertThisInitialized(_this), options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, {
      appData: 0
    });
    return _this;
  }
  return _createClass(BookingApp);
}(svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponent);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (BookingApp);

/***/ }),

/***/ "./resources/public/Calendar/App.svelte":
/*!**********************************************!*\
  !*** ./resources/public/Calendar/App.svelte ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../util */ "./resources/public/util.js");
/* harmony import */ var _Calendar_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Calendar.svelte */ "./resources/public/Calendar/Calendar.svelte");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
/* resources/public/Calendar/App.svelte generated by Svelte v3.55.1 */




function add_css(target) {
  (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "svelte-2j7ede", ".calendar-container.svelte-2j7ede.svelte-2j7ede{width:fit-content;overflow:auto;box-shadow:0 2px 20px rgba(0, 0, 0, 0.1);border-radius:10px;background:#fff;max-width:1200px}.calendar-header.svelte-2j7ede.svelte-2j7ede{display:flex;align-items:center;justify-content:space-between;padding:5px 15px;background:#eef;border-bottom:1px solid rgba(166, 168, 179, 0.12)}.calendar-header.svelte-2j7ede button.svelte-2j7ede{background:#eef;border:1px;padding:6px;color:rgba(81, 86, 93, 0.7);cursor:pointer;outline:0}.calendar-header.svelte-2j7ede h3.svelte-2j7ede{margin:0;font-size:18px}");
}
function create_fragment(ctx) {
  var div3;
  var div2;
  var div0;
  var h3;
  var t0_value = /*monthNames*/ctx[5][/*month*/ctx[1]] + "";
  var t0;
  var t1;
  var t2;
  var t3;
  var div1;
  var button0;
  var t5;
  var button1;
  var t7;
  var calendar;
  var t8;
  var t9;
  var current;
  var mounted;
  var dispose;
  calendar = new _Calendar_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
    props: {
      headers: /*headers*/ctx[2],
      days: /*days*/ctx[4],
      items: /*items*/ctx[6]
    }
  });
  calendar.$on("dayClick", /*dayClick_handler*/ctx[12]);
  return {
    c: function c() {
      div3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      h3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h3");
      t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
      t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)( /*year*/ctx[0]);
      t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      button0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
      button0.textContent = "<";
      t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      button1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
      button1.textContent = ">";
      t7 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(calendar.$$.fragment);
      t8 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      t9 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)( /*eventText*/ctx[3]);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(h3, "class", "svelte-2j7ede");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div0, "class", "calendar-month-year");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(button0, "class", "svelte-2j7ede");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(button1, "class", "svelte-2j7ede");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div1, "class", "calendar_nav");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div2, "class", "calendar-header svelte-2j7ede");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div3, "class", "calendar-container svelte-2j7ede");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, div3, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div3, div2);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div2, div0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div0, h3);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(h3, t0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(h3, t1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(h3, t2);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div2, t3);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div2, div1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div1, button0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div1, t5);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div1, button1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div3, t7);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(calendar, div3, null);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, t8, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, t9, anchor);
      current = true;
      if (!mounted) {
        dispose = [(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen)(button0, "click", /*click_handler*/ctx[10]), (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen)(button1, "click", /*click_handler_1*/ctx[11])];
        mounted = true;
      }
    },
    p: function p(ctx, _ref) {
      var _ref2 = _slicedToArray(_ref, 1),
        dirty = _ref2[0];
      if ((!current || dirty & /*month*/2) && t0_value !== (t0_value = /*monthNames*/ctx[5][/*month*/ctx[1]] + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t0, t0_value);
      if (!current || dirty & /*year*/1) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t2, /*year*/ctx[0]);
      var calendar_changes = {};
      if (dirty & /*headers*/4) calendar_changes.headers = /*headers*/ctx[2];
      if (dirty & /*days*/16) calendar_changes.days = /*days*/ctx[4];
      calendar.$set(calendar_changes);
      if (!current || dirty & /*eventText*/8) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t9, /*eventText*/ctx[3]);
    },
    i: function i(local) {
      if (current) return;
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(calendar.$$.fragment, local);
      current = true;
    },
    o: function o(local) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(calendar.$$.fragment, local);
      current = false;
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(div3);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(calendar);
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(t8);
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(t9);
      mounted = false;
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
    }
  };
}
function randInt(max) {
  return Math.floor(Math.random() * max) + 1;
}
function instance($$self, $$props, $$invalidate) {
  var dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  var headers = [];
  var now = new Date();
  var year = now.getFullYear(); //	this is the month & year displayed
  var month = now.getMonth();
  var eventText = "Click an item or date";
  var days = []; //	The days to display in each box

  //	The Calendar Component just displays stuff in a row & column. It has no knowledge of dates.
  //	The items[] below are placed (by you) in a specified row & column of the calendar.
  //	You need to call findRowCol() to calc the row/col based on each items start date. Each date box has a Date() property.
  //	And, if an item overlaps rows, then you need to add a 2nd item on the subsequent row.
  var items = [];

  // choose what date/day gets displayed in each date box.
  function initContent() {
    $$invalidate(2, headers = dayNames);
    initMonth();
  }
  function initMonth() {
    $$invalidate(4, days = []);

    //	find the last Monday of the previous month
    var firstDay = new Date(year, month, 1).getDay();
    var daysInThisMonth = new Date(year, month + 1, 0).getDate();
    var daysInLastMonth = new Date(year, month, 0).getDate();
    var prevMonth = month === 0 ? 11 : month - 1;

    //	show the days before the start of this month (disabled) - always less than 7
    for (var i = daysInLastMonth - firstDay; i < daysInLastMonth; i++) {
      var d = new Date(prevMonth == 11 ? year - 1 : year, prevMonth, i + 1);
      days.push({
        name: '',
        enabled: false,
        date: d
      });
    }

    //	show the days in this month (enabled) - always 28 - 31
    for (var _i2 = 0; _i2 < daysInThisMonth; _i2++) {
      var _d2 = new Date(year, month, _i2 + 1);
      var date = _util__WEBPACK_IMPORTED_MODULE_1__.util.dayjs(_d2).format('YYYY-MM-DD');
      var enabled = !!Math.floor(Math.random() * 2);
      days.push({
        name: '' + (_i2 + 1),
        enabled: enabled,
        date: date
      });
    }
  }
  function findRowCol(dt) {
    for (var i = 0; i < days.length; i++) {
      var d = days[i].date;
      if (d.getYear() === dt.getYear() && d.getMonth() === dt.getMonth() && d.getDate() === dt.getDate()) return {
        row: Math.floor(i / 7) + 2,
        col: i % 7 + 1
      };
    }
    return null;
  }
  function dayClick(e) {
    $$invalidate(3, eventText = 'onDayClick ' + JSON.stringify(e));
  }
  function next() {
    $$invalidate(1, month++, month);
    if (month == 12) {
      $$invalidate(0, year++, year);
      $$invalidate(1, month = 0);
    }
  }
  function prev() {
    if (month == 0) {
      $$invalidate(1, month = 11);
      $$invalidate(0, year--, year);
    } else {
      $$invalidate(1, month--, month);
    }
  }
  var click_handler = function click_handler() {
    return prev();
  };
  var click_handler_1 = function click_handler_1() {
    return next();
  };
  var dayClick_handler = function dayClick_handler(e) {
    return dayClick(e.detail);
  };
  $$self.$$.update = function () {
    if ($$self.$$.dirty & /*month, year*/3) {
      $: month, year, initContent();
    }
  };
  return [year, month, headers, eventText, days, monthNames, items, dayClick, next, prev, click_handler, click_handler_1, dayClick_handler];
}
var App = /*#__PURE__*/function (_SvelteComponent) {
  _inherits(App, _SvelteComponent);
  var _super = _createSuper(App);
  function App(options) {
    var _this;
    _classCallCheck(this, App);
    _this = _super.call(this);
    (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(_assertThisInitialized(_this), options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, {}, add_css);
    return _this;
  }
  return _createClass(App);
}(svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponent);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (App);

/***/ }),

/***/ "./resources/public/Calendar/Calendar.svelte":
/*!***************************************************!*\
  !*** ./resources/public/Calendar/Calendar.svelte ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
/* resources/public/Calendar/Calendar.svelte generated by Svelte v3.55.1 */


function add_css(target) {
  (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "svelte-wc0j8", ".calendar.svelte-wc0j8.svelte-wc0j8{display:grid;width:100%;grid-template-columns:repeat(7, minmax(60px, 6px));grid-template-rows:50px;grid-auto-rows:50px;overflow:auto;align-items:center}.day.svelte-wc0j8.svelte-wc0j8{text-align:center;padding:10px;letter-spacing:1px;font-size:14px;box-sizing:border-box;color:#98a0a6;position:relative;z-index:1}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 7){border-right:0}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(n + 1):nth-of-type(-n + 7){grid-row:1}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(n + 8):nth-of-type(-n + 14){grid-row:2}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(n + 15):nth-of-type(-n + 21){grid-row:3}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(n + 22):nth-of-type(-n + 28){grid-row:4}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(n + 29):nth-of-type(-n + 35){grid-row:5}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(n + 36):nth-of-type(-n + 42){grid-row:6}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 1){grid-column:1/1}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 2){grid-column:2/2}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 3){grid-column:3/3}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 4){grid-column:4/4}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 5){grid-column:5/5}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 6){grid-column:6/6}.day.svelte-wc0j8.svelte-wc0j8:nth-of-type(7n + 7){grid-column:7/7}.day-name.svelte-wc0j8.svelte-wc0j8{font-size:12px;text-transform:uppercase;text-align:center;line-height:30px;font-weight:500}.day-disabled.svelte-wc0j8.svelte-wc0j8{color:rgb(152 160 166);background-color:#ffffff;cursor:not-allowed}span.day.day-enabled.svelte-wc0j8.svelte-wc0j8{cursor:pointer}span.day.day-enabled.svelte-wc0j8:hover span.svelte-wc0j8{background-color:rgba(0,105,255,0.15);color:#0060d4}span.day.day-enabled.svelte-wc0j8 span.svelte-wc0j8{color:#0060e6;font-weight:700;display:block;width:30px;height:30px;line-height:30px;margin:0 auto;border-radius:50%;background-color:rgba(0,105,255,0.065)}");
}
function get_each_context(ctx, list, i) {
  var child_ctx = ctx.slice();
  child_ctx[6] = list[i];
  return child_ctx;
}
function get_each_context_1(ctx, list, i) {
  var child_ctx = ctx.slice();
  child_ctx[9] = list[i];
  return child_ctx;
}

// (2:4) {#each headers as header}
function create_each_block_1(ctx) {
  var span;
  var t_value = /*header*/ctx[9] + "";
  var t;
  return {
    c: function c() {
      span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
      t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(span, "class", "day-name svelte-wc0j8");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, span, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span, t);
    },
    p: function p(ctx, dirty) {
      if (dirty & /*headers*/1 && t_value !== (t_value = /*header*/ctx[9] + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t, t_value);
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(span);
    }
  };
}

// (11:8) {:else}
function create_else_block(ctx) {
  var span1;
  var span0;
  var t0_value = /*day*/ctx[6].name + "";
  var t0;
  var t1;
  return {
    c: function c() {
      span1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
      span0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
      t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
      t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(span1, "class", "day day-disabled svelte-wc0j8");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, span1, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span1, span0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span0, t0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span1, t1);
    },
    p: function p(ctx, dirty) {
      if (dirty & /*days*/2 && t0_value !== (t0_value = /*day*/ctx[6].name + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t0, t0_value);
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(span1);
    }
  };
}

// (7:8) {#if day.enabled}
function create_if_block(ctx) {
  var span1;
  var span0;
  var t0_value = /*day*/ctx[6].name + "";
  var t0;
  var t1;
  var mounted;
  var dispose;
  function click_handler() {
    return (/*click_handler*/ctx[4]( /*day*/ctx[6])
    );
  }
  return {
    c: function c() {
      span1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
      span0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
      t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
      t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(span0, "class", "svelte-wc0j8");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(span1, "class", "day day-enabled svelte-wc0j8");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, span1, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span1, span0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span0, t0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(span1, t1);
      if (!mounted) {
        dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen)(span1, "click", click_handler);
        mounted = true;
      }
    },
    p: function p(new_ctx, dirty) {
      ctx = new_ctx;
      if (dirty & /*days*/2 && t0_value !== (t0_value = /*day*/ctx[6].name + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data)(t0, t0_value);
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(span1);
      mounted = false;
      dispose();
    }
  };
}

// (6:4) {#each days as day}
function create_each_block(ctx) {
  var if_block_anchor;
  function select_block_type(ctx, dirty) {
    if ( /*day*/ctx[6].enabled) return create_if_block;
    return create_else_block;
  }
  var current_block_type = select_block_type(ctx, -1);
  var if_block = current_block_type(ctx);
  return {
    c: function c() {
      if_block.c();
      if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
    },
    m: function m(target, anchor) {
      if_block.m(target, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, if_block_anchor, anchor);
    },
    p: function p(ctx, dirty) {
      if (current_block_type === (current_block_type = select_block_type(ctx, dirty)) && if_block) {
        if_block.p(ctx, dirty);
      } else {
        if_block.d(1);
        if_block = current_block_type(ctx);
        if (if_block) {
          if_block.c();
          if_block.m(if_block_anchor.parentNode, if_block_anchor);
        }
      }
    },
    d: function d(detaching) {
      if_block.d(detaching);
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(if_block_anchor);
    }
  };
}
function create_fragment(ctx) {
  var div;
  var t;
  var each_value_1 = /*headers*/ctx[0];
  var each_blocks_1 = [];
  for (var i = 0; i < each_value_1.length; i += 1) {
    each_blocks_1[i] = create_each_block_1(get_each_context_1(ctx, each_value_1, i));
  }
  var each_value = /*days*/ctx[1];
  var each_blocks = [];
  for (var _i = 0; _i < each_value.length; _i += 1) {
    each_blocks[_i] = create_each_block(get_each_context(ctx, each_value, _i));
  }
  return {
    c: function c() {
      div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      for (var _i2 = 0; _i2 < each_blocks_1.length; _i2 += 1) {
        each_blocks_1[_i2].c();
      }
      t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      for (var _i3 = 0; _i3 < each_blocks.length; _i3 += 1) {
        each_blocks[_i3].c();
      }
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div, "class", "calendar svelte-wc0j8");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, div, anchor);
      for (var _i4 = 0; _i4 < each_blocks_1.length; _i4 += 1) {
        each_blocks_1[_i4].m(div, null);
      }
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div, t);
      for (var _i5 = 0; _i5 < each_blocks.length; _i5 += 1) {
        each_blocks[_i5].m(div, null);
      }
    },
    p: function p(ctx, _ref) {
      var _ref2 = _slicedToArray(_ref, 1),
        dirty = _ref2[0];
      if (dirty & /*headers*/1) {
        each_value_1 = /*headers*/ctx[0];
        var _i6;
        for (_i6 = 0; _i6 < each_value_1.length; _i6 += 1) {
          var child_ctx = get_each_context_1(ctx, each_value_1, _i6);
          if (each_blocks_1[_i6]) {
            each_blocks_1[_i6].p(child_ctx, dirty);
          } else {
            each_blocks_1[_i6] = create_each_block_1(child_ctx);
            each_blocks_1[_i6].c();
            each_blocks_1[_i6].m(div, t);
          }
        }
        for (; _i6 < each_blocks_1.length; _i6 += 1) {
          each_blocks_1[_i6].d(1);
        }
        each_blocks_1.length = each_value_1.length;
      }
      if (dirty & /*daySelected, days*/6) {
        each_value = /*days*/ctx[1];
        var _i7;
        for (_i7 = 0; _i7 < each_value.length; _i7 += 1) {
          var _child_ctx = get_each_context(ctx, each_value, _i7);
          if (each_blocks[_i7]) {
            each_blocks[_i7].p(_child_ctx, dirty);
          } else {
            each_blocks[_i7] = create_each_block(_child_ctx);
            each_blocks[_i7].c();
            each_blocks[_i7].m(div, null);
          }
        }
        for (; _i7 < each_blocks.length; _i7 += 1) {
          each_blocks[_i7].d(1);
        }
        each_blocks.length = each_value.length;
      }
    },
    i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
    o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(div);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks_1, detaching);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
    }
  };
}
function instance($$self, $$props, $$invalidate) {
  var _$$props$headers = $$props.headers,
    headers = _$$props$headers === void 0 ? [] : _$$props$headers;
  var _$$props$days = $$props.days,
    days = _$$props$days === void 0 ? [] : _$$props$days;
  var _$$props$items = $$props.items,
    items = _$$props$items === void 0 ? [] : _$$props$items;
  var dispatch = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher)();
  function daySelected(day) {
    dispatch('dayClick', day);
  }
  var click_handler = function click_handler(day) {
    return daySelected(day);
  };
  $$self.$$set = function ($$props) {
    if ('headers' in $$props) $$invalidate(0, headers = $$props.headers);
    if ('days' in $$props) $$invalidate(1, days = $$props.days);
    if ('items' in $$props) $$invalidate(3, items = $$props.items);
  };
  return [headers, days, daySelected, items, click_handler];
}
var Calendar = /*#__PURE__*/function (_SvelteComponent) {
  _inherits(Calendar, _SvelteComponent);
  var _super = _createSuper(Calendar);
  function Calendar(options) {
    var _this;
    _classCallCheck(this, Calendar);
    _this = _super.call(this);
    (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(_assertThisInitialized(_this), options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, {
      headers: 0,
      days: 1,
      items: 3
    }, add_css);
    return _this;
  }
  return _createClass(Calendar);
}(svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponent);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Calendar);

/***/ }),

/***/ "./resources/public/DayPicker.svelte":
/*!*******************************************!*\
  !*** ./resources/public/DayPicker.svelte ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _Calendar_App_svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Calendar/App.svelte */ "./resources/public/Calendar/App.svelte");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }
/* resources/public/DayPicker.svelte generated by Svelte v3.55.1 */


function create_fragment(ctx) {
  var div1;
  var div0;
  var h3;
  var t1;
  var calendarapp;
  var current;
  calendarapp = new _Calendar_App_svelte__WEBPACK_IMPORTED_MODULE_1__["default"]({});
  return {
    c: function c() {
      div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
      h3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h3");
      h3.textContent = "Select a Date & Time";
      t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(calendarapp.$$.fragment);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div0, "class", "fcal_day_picker_head");
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr)(div1, "class", "fcal_day_picker");
    },
    m: function m(target, anchor) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert)(target, div1, anchor);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div1, div0);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div0, h3);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append)(div0, t1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(calendarapp, div0, null);
      current = true;
    },
    p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
    i: function i(local) {
      if (current) return;
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(calendarapp.$$.fragment, local);
      current = true;
    },
    o: function o(local) {
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(calendarapp.$$.fragment, local);
      current = false;
    },
    d: function d(detaching) {
      if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach)(div1);
      (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(calendarapp);
    }
  };
}
var DayPicker = /*#__PURE__*/function (_SvelteComponent) {
  _inherits(DayPicker, _SvelteComponent);
  var _super = _createSuper(DayPicker);
  function DayPicker(options) {
    var _this;
    _classCallCheck(this, DayPicker);
    _this = _super.call(this);
    (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(_assertThisInitialized(_this), options, null, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, {});
    return _this;
  }
  return _createClass(DayPicker);
}(svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponent);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (DayPicker);

/***/ }),

/***/ "./resources/public/util.js":
/*!**********************************!*\
  !*** ./resources/public/util.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "util": () => (/* binding */ util)
/* harmony export */ });
/* harmony import */ var dayjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! dayjs */ "./node_modules/dayjs/dayjs.min.js");
/* harmony import */ var dayjs__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(dayjs__WEBPACK_IMPORTED_MODULE_0__);

var util = {
  dayjs: dayjs__WEBPACK_IMPORTED_MODULE_0__
};

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[2]!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[3]!./resources/public/styles.scss":
/*!****************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[2]!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[3]!./resources/public/styles.scss ***!
  \****************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, ".fcal_calendar_inner {\n  display: flex;\n  border: 1px solid rgba(26, 26, 26, 0.1);\n  border-radius: 8px;\n  box-shadow: 0 1px 8px 0 rgba(0, 0, 0, 0.08);\n}\n.fcal_calendar_inner .fcal_side {\n  width: 50%;\n  min-width: 300px;\n  border-right: 1px solid var(--text-color-level3, rgba(26, 26, 26, 0.1));\n  transition: all 0.22s ease-out;\n  padding: 20px;\n}\n.fcal_calendar_inner .fcal_side .fcal_author_avatar img {\n  max-width: 64px;\n  border-radius: 50%;\n}\n.fcal_calendar_inner .fcal_side .fcal_author_name {\n  margin-top: 20px;\n  color: rgba(26, 26, 26, 0.61);\n  font-weight: 700;\n  font-size: 16px;\n  line-height: 24px;\n}\n.fcal_calendar_inner .fcal_side .slot_timing {\n  display: flex;\n  align-items: center;\n  color: #737373;\n  -moz-column-gap: 7px;\n       column-gap: 7px;\n}\n.fcal_calendar_inner .fcal_side .slot_timing svg {\n  stroke: #737373;\n  fill: #737373;\n}\n.fcal_calendar_inner .fcal_side .fcal_slot_heading {\n  margin-bottom: 0;\n}\n.fcal_calendar_inner .fcal_side .fcal_slot_description {\n  margin-top: 20px;\n}\n.fcal_calendar_inner .fcal_date_wrapper {\n  width: 50%;\n  padding: 20px;\n}", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/api.js":
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
// eslint-disable-next-line func-names
module.exports = function (cssWithMappingToString) {
  var list = []; // return the list of modules as css string

  list.toString = function toString() {
    return this.map(function (item) {
      var content = cssWithMappingToString(item);

      if (item[2]) {
        return "@media ".concat(item[2], " {").concat(content, "}");
      }

      return content;
    }).join("");
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === "string") {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, ""]];
    }

    var alreadyImportedModules = {};

    if (dedupe) {
      for (var i = 0; i < this.length; i++) {
        // eslint-disable-next-line prefer-destructuring
        var id = this[i][0];

        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }

    for (var _i = 0; _i < modules.length; _i++) {
      var item = [].concat(modules[_i]);

      if (dedupe && alreadyImportedModules[item[0]]) {
        // eslint-disable-next-line no-continue
        continue;
      }

      if (mediaQuery) {
        if (!item[2]) {
          item[2] = mediaQuery;
        } else {
          item[2] = "".concat(mediaQuery, " and ").concat(item[2]);
        }
      }

      list.push(item);
    }
  };

  return list;
};

/***/ }),

/***/ "./node_modules/dayjs/dayjs.min.js":
/*!*****************************************!*\
  !*** ./node_modules/dayjs/dayjs.min.js ***!
  \*****************************************/
/***/ (function(module) {

!function(t,e){ true?module.exports=e():0}(this,(function(){"use strict";var t=1e3,e=6e4,n=36e5,r="millisecond",i="second",s="minute",u="hour",a="day",o="week",f="month",h="quarter",c="year",d="date",l="Invalid Date",$=/^(\d{4})[-/]?(\d{1,2})?[-/]?(\d{0,2})[Tt\s]*(\d{1,2})?:?(\d{1,2})?:?(\d{1,2})?[.:]?(\d+)?$/,y=/\[([^\]]+)]|Y{1,4}|M{1,4}|D{1,2}|d{1,4}|H{1,2}|h{1,2}|a|A|m{1,2}|s{1,2}|Z{1,2}|SSS/g,M={name:"en",weekdays:"Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),months:"January_February_March_April_May_June_July_August_September_October_November_December".split("_"),ordinal:function(t){var e=["th","st","nd","rd"],n=t%100;return"["+t+(e[(n-20)%10]||e[n]||e[0])+"]"}},m=function(t,e,n){var r=String(t);return!r||r.length>=e?t:""+Array(e+1-r.length).join(n)+t},v={s:m,z:function(t){var e=-t.utcOffset(),n=Math.abs(e),r=Math.floor(n/60),i=n%60;return(e<=0?"+":"-")+m(r,2,"0")+":"+m(i,2,"0")},m:function t(e,n){if(e.date()<n.date())return-t(n,e);var r=12*(n.year()-e.year())+(n.month()-e.month()),i=e.clone().add(r,f),s=n-i<0,u=e.clone().add(r+(s?-1:1),f);return+(-(r+(n-i)/(s?i-u:u-i))||0)},a:function(t){return t<0?Math.ceil(t)||0:Math.floor(t)},p:function(t){return{M:f,y:c,w:o,d:a,D:d,h:u,m:s,s:i,ms:r,Q:h}[t]||String(t||"").toLowerCase().replace(/s$/,"")},u:function(t){return void 0===t}},g="en",D={};D[g]=M;var p=function(t){return t instanceof _},S=function t(e,n,r){var i;if(!e)return g;if("string"==typeof e){var s=e.toLowerCase();D[s]&&(i=s),n&&(D[s]=n,i=s);var u=e.split("-");if(!i&&u.length>1)return t(u[0])}else{var a=e.name;D[a]=e,i=a}return!r&&i&&(g=i),i||!r&&g},w=function(t,e){if(p(t))return t.clone();var n="object"==typeof e?e:{};return n.date=t,n.args=arguments,new _(n)},O=v;O.l=S,O.i=p,O.w=function(t,e){return w(t,{locale:e.$L,utc:e.$u,x:e.$x,$offset:e.$offset})};var _=function(){function M(t){this.$L=S(t.locale,null,!0),this.parse(t)}var m=M.prototype;return m.parse=function(t){this.$d=function(t){var e=t.date,n=t.utc;if(null===e)return new Date(NaN);if(O.u(e))return new Date;if(e instanceof Date)return new Date(e);if("string"==typeof e&&!/Z$/i.test(e)){var r=e.match($);if(r){var i=r[2]-1||0,s=(r[7]||"0").substring(0,3);return n?new Date(Date.UTC(r[1],i,r[3]||1,r[4]||0,r[5]||0,r[6]||0,s)):new Date(r[1],i,r[3]||1,r[4]||0,r[5]||0,r[6]||0,s)}}return new Date(e)}(t),this.$x=t.x||{},this.init()},m.init=function(){var t=this.$d;this.$y=t.getFullYear(),this.$M=t.getMonth(),this.$D=t.getDate(),this.$W=t.getDay(),this.$H=t.getHours(),this.$m=t.getMinutes(),this.$s=t.getSeconds(),this.$ms=t.getMilliseconds()},m.$utils=function(){return O},m.isValid=function(){return!(this.$d.toString()===l)},m.isSame=function(t,e){var n=w(t);return this.startOf(e)<=n&&n<=this.endOf(e)},m.isAfter=function(t,e){return w(t)<this.startOf(e)},m.isBefore=function(t,e){return this.endOf(e)<w(t)},m.$g=function(t,e,n){return O.u(t)?this[e]:this.set(n,t)},m.unix=function(){return Math.floor(this.valueOf()/1e3)},m.valueOf=function(){return this.$d.getTime()},m.startOf=function(t,e){var n=this,r=!!O.u(e)||e,h=O.p(t),l=function(t,e){var i=O.w(n.$u?Date.UTC(n.$y,e,t):new Date(n.$y,e,t),n);return r?i:i.endOf(a)},$=function(t,e){return O.w(n.toDate()[t].apply(n.toDate("s"),(r?[0,0,0,0]:[23,59,59,999]).slice(e)),n)},y=this.$W,M=this.$M,m=this.$D,v="set"+(this.$u?"UTC":"");switch(h){case c:return r?l(1,0):l(31,11);case f:return r?l(1,M):l(0,M+1);case o:var g=this.$locale().weekStart||0,D=(y<g?y+7:y)-g;return l(r?m-D:m+(6-D),M);case a:case d:return $(v+"Hours",0);case u:return $(v+"Minutes",1);case s:return $(v+"Seconds",2);case i:return $(v+"Milliseconds",3);default:return this.clone()}},m.endOf=function(t){return this.startOf(t,!1)},m.$set=function(t,e){var n,o=O.p(t),h="set"+(this.$u?"UTC":""),l=(n={},n[a]=h+"Date",n[d]=h+"Date",n[f]=h+"Month",n[c]=h+"FullYear",n[u]=h+"Hours",n[s]=h+"Minutes",n[i]=h+"Seconds",n[r]=h+"Milliseconds",n)[o],$=o===a?this.$D+(e-this.$W):e;if(o===f||o===c){var y=this.clone().set(d,1);y.$d[l]($),y.init(),this.$d=y.set(d,Math.min(this.$D,y.daysInMonth())).$d}else l&&this.$d[l]($);return this.init(),this},m.set=function(t,e){return this.clone().$set(t,e)},m.get=function(t){return this[O.p(t)]()},m.add=function(r,h){var d,l=this;r=Number(r);var $=O.p(h),y=function(t){var e=w(l);return O.w(e.date(e.date()+Math.round(t*r)),l)};if($===f)return this.set(f,this.$M+r);if($===c)return this.set(c,this.$y+r);if($===a)return y(1);if($===o)return y(7);var M=(d={},d[s]=e,d[u]=n,d[i]=t,d)[$]||1,m=this.$d.getTime()+r*M;return O.w(m,this)},m.subtract=function(t,e){return this.add(-1*t,e)},m.format=function(t){var e=this,n=this.$locale();if(!this.isValid())return n.invalidDate||l;var r=t||"YYYY-MM-DDTHH:mm:ssZ",i=O.z(this),s=this.$H,u=this.$m,a=this.$M,o=n.weekdays,f=n.months,h=function(t,n,i,s){return t&&(t[n]||t(e,r))||i[n].slice(0,s)},c=function(t){return O.s(s%12||12,t,"0")},d=n.meridiem||function(t,e,n){var r=t<12?"AM":"PM";return n?r.toLowerCase():r},$={YY:String(this.$y).slice(-2),YYYY:this.$y,M:a+1,MM:O.s(a+1,2,"0"),MMM:h(n.monthsShort,a,f,3),MMMM:h(f,a),D:this.$D,DD:O.s(this.$D,2,"0"),d:String(this.$W),dd:h(n.weekdaysMin,this.$W,o,2),ddd:h(n.weekdaysShort,this.$W,o,3),dddd:o[this.$W],H:String(s),HH:O.s(s,2,"0"),h:c(1),hh:c(2),a:d(s,u,!0),A:d(s,u,!1),m:String(u),mm:O.s(u,2,"0"),s:String(this.$s),ss:O.s(this.$s,2,"0"),SSS:O.s(this.$ms,3,"0"),Z:i};return r.replace(y,(function(t,e){return e||$[t]||i.replace(":","")}))},m.utcOffset=function(){return 15*-Math.round(this.$d.getTimezoneOffset()/15)},m.diff=function(r,d,l){var $,y=O.p(d),M=w(r),m=(M.utcOffset()-this.utcOffset())*e,v=this-M,g=O.m(this,M);return g=($={},$[c]=g/12,$[f]=g,$[h]=g/3,$[o]=(v-m)/6048e5,$[a]=(v-m)/864e5,$[u]=v/n,$[s]=v/e,$[i]=v/t,$)[y]||v,l?g:O.a(g)},m.daysInMonth=function(){return this.endOf(f).$D},m.$locale=function(){return D[this.$L]},m.locale=function(t,e){if(!t)return this.$L;var n=this.clone(),r=S(t,e,!0);return r&&(n.$L=r),n},m.clone=function(){return O.w(this.$d,this)},m.toDate=function(){return new Date(this.valueOf())},m.toJSON=function(){return this.isValid()?this.toISOString():null},m.toISOString=function(){return this.$d.toISOString()},m.toString=function(){return this.$d.toUTCString()},M}(),T=_.prototype;return w.prototype=T,[["$ms",r],["$s",i],["$m",s],["$H",u],["$W",a],["$M",f],["$y",c],["$D",d]].forEach((function(t){T[t[1]]=function(e){return this.$g(e,t[0],t[1])}})),w.extend=function(t,e){return t.$i||(t(e,_,w),t.$i=!0),w},w.locale=S,w.isDayjs=p,w.unix=function(t){return w(1e3*t)},w.en=D[g],w.Ls=D,w.p={},w}));

/***/ }),

/***/ "./resources/public/styles.scss":
/*!**************************************!*\
  !*** ./resources/public/styles.scss ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_1_node_modules_postcss_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_2_node_modules_sass_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_3_styles_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[1]!../../node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[2]!../../node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[3]!./styles.scss */ "./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[2]!./node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[6].oneOf[1].use[3]!./resources/public/styles.scss");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_1_node_modules_postcss_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_2_node_modules_sass_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_3_styles_scss__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_1_node_modules_postcss_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_2_node_modules_sass_loader_dist_cjs_js_ruleSet_1_rules_6_oneOf_1_use_3_styles_scss__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js":
/*!****************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js ***!
  \****************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var isOldIE = function isOldIE() {
  var memo;
  return function memorize() {
    if (typeof memo === 'undefined') {
      // Test for IE <= 9 as proposed by Browserhacks
      // @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
      // Tests for existence of standard globals is to allow style-loader
      // to operate correctly into non-standard environments
      // @see https://github.com/webpack-contrib/style-loader/issues/177
      memo = Boolean(window && document && document.all && !window.atob);
    }

    return memo;
  };
}();

var getTarget = function getTarget() {
  var memo = {};
  return function memorize(target) {
    if (typeof memo[target] === 'undefined') {
      var styleTarget = document.querySelector(target); // Special case to return head of iframe instead of iframe itself

      if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
        try {
          // This will throw an exception if access to iframe is blocked
          // due to cross-origin restrictions
          styleTarget = styleTarget.contentDocument.head;
        } catch (e) {
          // istanbul ignore next
          styleTarget = null;
        }
      }

      memo[target] = styleTarget;
    }

    return memo[target];
  };
}();

var stylesInDom = [];

function getIndexByIdentifier(identifier) {
  var result = -1;

  for (var i = 0; i < stylesInDom.length; i++) {
    if (stylesInDom[i].identifier === identifier) {
      result = i;
      break;
    }
  }

  return result;
}

function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];

  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var index = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3]
    };

    if (index !== -1) {
      stylesInDom[index].references++;
      stylesInDom[index].updater(obj);
    } else {
      stylesInDom.push({
        identifier: identifier,
        updater: addStyle(obj, options),
        references: 1
      });
    }

    identifiers.push(identifier);
  }

  return identifiers;
}

function insertStyleElement(options) {
  var style = document.createElement('style');
  var attributes = options.attributes || {};

  if (typeof attributes.nonce === 'undefined') {
    var nonce =  true ? __webpack_require__.nc : 0;

    if (nonce) {
      attributes.nonce = nonce;
    }
  }

  Object.keys(attributes).forEach(function (key) {
    style.setAttribute(key, attributes[key]);
  });

  if (typeof options.insert === 'function') {
    options.insert(style);
  } else {
    var target = getTarget(options.insert || 'head');

    if (!target) {
      throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
    }

    target.appendChild(style);
  }

  return style;
}

function removeStyleElement(style) {
  // istanbul ignore if
  if (style.parentNode === null) {
    return false;
  }

  style.parentNode.removeChild(style);
}
/* istanbul ignore next  */


var replaceText = function replaceText() {
  var textStore = [];
  return function replace(index, replacement) {
    textStore[index] = replacement;
    return textStore.filter(Boolean).join('\n');
  };
}();

function applyToSingletonTag(style, index, remove, obj) {
  var css = remove ? '' : obj.media ? "@media ".concat(obj.media, " {").concat(obj.css, "}") : obj.css; // For old IE

  /* istanbul ignore if  */

  if (style.styleSheet) {
    style.styleSheet.cssText = replaceText(index, css);
  } else {
    var cssNode = document.createTextNode(css);
    var childNodes = style.childNodes;

    if (childNodes[index]) {
      style.removeChild(childNodes[index]);
    }

    if (childNodes.length) {
      style.insertBefore(cssNode, childNodes[index]);
    } else {
      style.appendChild(cssNode);
    }
  }
}

function applyToTag(style, options, obj) {
  var css = obj.css;
  var media = obj.media;
  var sourceMap = obj.sourceMap;

  if (media) {
    style.setAttribute('media', media);
  } else {
    style.removeAttribute('media');
  }

  if (sourceMap && typeof btoa !== 'undefined') {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  } // For old IE

  /* istanbul ignore if  */


  if (style.styleSheet) {
    style.styleSheet.cssText = css;
  } else {
    while (style.firstChild) {
      style.removeChild(style.firstChild);
    }

    style.appendChild(document.createTextNode(css));
  }
}

var singleton = null;
var singletonCounter = 0;

function addStyle(obj, options) {
  var style;
  var update;
  var remove;

  if (options.singleton) {
    var styleIndex = singletonCounter++;
    style = singleton || (singleton = insertStyleElement(options));
    update = applyToSingletonTag.bind(null, style, styleIndex, false);
    remove = applyToSingletonTag.bind(null, style, styleIndex, true);
  } else {
    style = insertStyleElement(options);
    update = applyToTag.bind(null, style, options);

    remove = function remove() {
      removeStyleElement(style);
    };
  }

  update(obj);
  return function updateStyle(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap) {
        return;
      }

      update(obj = newObj);
    } else {
      remove();
    }
  };
}

module.exports = function (list, options) {
  options = options || {}; // Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
  // tags it will allow on a page

  if (!options.singleton && typeof options.singleton !== 'boolean') {
    options.singleton = isOldIE();
  }

  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];

    if (Object.prototype.toString.call(newList) !== '[object Array]') {
      return;
    }

    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDom[index].references--;
    }

    var newLastIdentifiers = modulesToDom(newList, options);

    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];

      var _index = getIndexByIdentifier(_identifier);

      if (stylesInDom[_index].references === 0) {
        stylesInDom[_index].updater();

        stylesInDom.splice(_index, 1);
      }
    }

    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ }),

/***/ "./node_modules/svelte/index.mjs":
/*!***************************************!*\
  !*** ./node_modules/svelte/index.mjs ***!
  \***************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "SvelteComponent": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev),
/* harmony export */   "SvelteComponentTyped": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentTyped),
/* harmony export */   "afterUpdate": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.afterUpdate),
/* harmony export */   "beforeUpdate": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.beforeUpdate),
/* harmony export */   "createEventDispatcher": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.createEventDispatcher),
/* harmony export */   "getAllContexts": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.getAllContexts),
/* harmony export */   "getContext": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.getContext),
/* harmony export */   "hasContext": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.hasContext),
/* harmony export */   "onDestroy": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.onDestroy),
/* harmony export */   "onMount": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.onMount),
/* harmony export */   "setContext": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.setContext),
/* harmony export */   "tick": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.tick)
/* harmony export */ });
/* harmony import */ var _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./internal/index.mjs */ "./node_modules/svelte/internal/index.mjs");


/***/ }),

/***/ "./node_modules/svelte/internal/index.mjs":
/*!************************************************!*\
  !*** ./node_modules/svelte/internal/index.mjs ***!
  \************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "HtmlTag": () => (/* binding */ HtmlTag),
/* harmony export */   "HtmlTagHydration": () => (/* binding */ HtmlTagHydration),
/* harmony export */   "SvelteComponent": () => (/* binding */ SvelteComponent),
/* harmony export */   "SvelteComponentDev": () => (/* binding */ SvelteComponentDev),
/* harmony export */   "SvelteComponentTyped": () => (/* binding */ SvelteComponentTyped),
/* harmony export */   "SvelteElement": () => (/* binding */ SvelteElement),
/* harmony export */   "action_destroyer": () => (/* binding */ action_destroyer),
/* harmony export */   "add_attribute": () => (/* binding */ add_attribute),
/* harmony export */   "add_classes": () => (/* binding */ add_classes),
/* harmony export */   "add_flush_callback": () => (/* binding */ add_flush_callback),
/* harmony export */   "add_location": () => (/* binding */ add_location),
/* harmony export */   "add_render_callback": () => (/* binding */ add_render_callback),
/* harmony export */   "add_resize_listener": () => (/* binding */ add_resize_listener),
/* harmony export */   "add_styles": () => (/* binding */ add_styles),
/* harmony export */   "add_transform": () => (/* binding */ add_transform),
/* harmony export */   "afterUpdate": () => (/* binding */ afterUpdate),
/* harmony export */   "append": () => (/* binding */ append),
/* harmony export */   "append_dev": () => (/* binding */ append_dev),
/* harmony export */   "append_empty_stylesheet": () => (/* binding */ append_empty_stylesheet),
/* harmony export */   "append_hydration": () => (/* binding */ append_hydration),
/* harmony export */   "append_hydration_dev": () => (/* binding */ append_hydration_dev),
/* harmony export */   "append_styles": () => (/* binding */ append_styles),
/* harmony export */   "assign": () => (/* binding */ assign),
/* harmony export */   "attr": () => (/* binding */ attr),
/* harmony export */   "attr_dev": () => (/* binding */ attr_dev),
/* harmony export */   "attribute_to_object": () => (/* binding */ attribute_to_object),
/* harmony export */   "beforeUpdate": () => (/* binding */ beforeUpdate),
/* harmony export */   "bind": () => (/* binding */ bind),
/* harmony export */   "binding_callbacks": () => (/* binding */ binding_callbacks),
/* harmony export */   "blank_object": () => (/* binding */ blank_object),
/* harmony export */   "bubble": () => (/* binding */ bubble),
/* harmony export */   "check_outros": () => (/* binding */ check_outros),
/* harmony export */   "children": () => (/* binding */ children),
/* harmony export */   "claim_component": () => (/* binding */ claim_component),
/* harmony export */   "claim_element": () => (/* binding */ claim_element),
/* harmony export */   "claim_html_tag": () => (/* binding */ claim_html_tag),
/* harmony export */   "claim_space": () => (/* binding */ claim_space),
/* harmony export */   "claim_svg_element": () => (/* binding */ claim_svg_element),
/* harmony export */   "claim_text": () => (/* binding */ claim_text),
/* harmony export */   "clear_loops": () => (/* binding */ clear_loops),
/* harmony export */   "component_subscribe": () => (/* binding */ component_subscribe),
/* harmony export */   "compute_rest_props": () => (/* binding */ compute_rest_props),
/* harmony export */   "compute_slots": () => (/* binding */ compute_slots),
/* harmony export */   "construct_svelte_component": () => (/* binding */ construct_svelte_component),
/* harmony export */   "construct_svelte_component_dev": () => (/* binding */ construct_svelte_component_dev),
/* harmony export */   "createEventDispatcher": () => (/* binding */ createEventDispatcher),
/* harmony export */   "create_animation": () => (/* binding */ create_animation),
/* harmony export */   "create_bidirectional_transition": () => (/* binding */ create_bidirectional_transition),
/* harmony export */   "create_component": () => (/* binding */ create_component),
/* harmony export */   "create_in_transition": () => (/* binding */ create_in_transition),
/* harmony export */   "create_out_transition": () => (/* binding */ create_out_transition),
/* harmony export */   "create_slot": () => (/* binding */ create_slot),
/* harmony export */   "create_ssr_component": () => (/* binding */ create_ssr_component),
/* harmony export */   "current_component": () => (/* binding */ current_component),
/* harmony export */   "custom_event": () => (/* binding */ custom_event),
/* harmony export */   "dataset_dev": () => (/* binding */ dataset_dev),
/* harmony export */   "debug": () => (/* binding */ debug),
/* harmony export */   "destroy_block": () => (/* binding */ destroy_block),
/* harmony export */   "destroy_component": () => (/* binding */ destroy_component),
/* harmony export */   "destroy_each": () => (/* binding */ destroy_each),
/* harmony export */   "detach": () => (/* binding */ detach),
/* harmony export */   "detach_after_dev": () => (/* binding */ detach_after_dev),
/* harmony export */   "detach_before_dev": () => (/* binding */ detach_before_dev),
/* harmony export */   "detach_between_dev": () => (/* binding */ detach_between_dev),
/* harmony export */   "detach_dev": () => (/* binding */ detach_dev),
/* harmony export */   "dirty_components": () => (/* binding */ dirty_components),
/* harmony export */   "dispatch_dev": () => (/* binding */ dispatch_dev),
/* harmony export */   "each": () => (/* binding */ each),
/* harmony export */   "element": () => (/* binding */ element),
/* harmony export */   "element_is": () => (/* binding */ element_is),
/* harmony export */   "empty": () => (/* binding */ empty),
/* harmony export */   "end_hydrating": () => (/* binding */ end_hydrating),
/* harmony export */   "escape": () => (/* binding */ escape),
/* harmony export */   "escape_attribute_value": () => (/* binding */ escape_attribute_value),
/* harmony export */   "escape_object": () => (/* binding */ escape_object),
/* harmony export */   "exclude_internal_props": () => (/* binding */ exclude_internal_props),
/* harmony export */   "fix_and_destroy_block": () => (/* binding */ fix_and_destroy_block),
/* harmony export */   "fix_and_outro_and_destroy_block": () => (/* binding */ fix_and_outro_and_destroy_block),
/* harmony export */   "fix_position": () => (/* binding */ fix_position),
/* harmony export */   "flush": () => (/* binding */ flush),
/* harmony export */   "getAllContexts": () => (/* binding */ getAllContexts),
/* harmony export */   "getContext": () => (/* binding */ getContext),
/* harmony export */   "get_all_dirty_from_scope": () => (/* binding */ get_all_dirty_from_scope),
/* harmony export */   "get_binding_group_value": () => (/* binding */ get_binding_group_value),
/* harmony export */   "get_current_component": () => (/* binding */ get_current_component),
/* harmony export */   "get_custom_elements_slots": () => (/* binding */ get_custom_elements_slots),
/* harmony export */   "get_root_for_style": () => (/* binding */ get_root_for_style),
/* harmony export */   "get_slot_changes": () => (/* binding */ get_slot_changes),
/* harmony export */   "get_spread_object": () => (/* binding */ get_spread_object),
/* harmony export */   "get_spread_update": () => (/* binding */ get_spread_update),
/* harmony export */   "get_store_value": () => (/* binding */ get_store_value),
/* harmony export */   "globals": () => (/* binding */ globals),
/* harmony export */   "group_outros": () => (/* binding */ group_outros),
/* harmony export */   "handle_promise": () => (/* binding */ handle_promise),
/* harmony export */   "hasContext": () => (/* binding */ hasContext),
/* harmony export */   "has_prop": () => (/* binding */ has_prop),
/* harmony export */   "head_selector": () => (/* binding */ head_selector),
/* harmony export */   "identity": () => (/* binding */ identity),
/* harmony export */   "init": () => (/* binding */ init),
/* harmony export */   "insert": () => (/* binding */ insert),
/* harmony export */   "insert_dev": () => (/* binding */ insert_dev),
/* harmony export */   "insert_hydration": () => (/* binding */ insert_hydration),
/* harmony export */   "insert_hydration_dev": () => (/* binding */ insert_hydration_dev),
/* harmony export */   "intros": () => (/* binding */ intros),
/* harmony export */   "invalid_attribute_name_character": () => (/* binding */ invalid_attribute_name_character),
/* harmony export */   "is_client": () => (/* binding */ is_client),
/* harmony export */   "is_crossorigin": () => (/* binding */ is_crossorigin),
/* harmony export */   "is_empty": () => (/* binding */ is_empty),
/* harmony export */   "is_function": () => (/* binding */ is_function),
/* harmony export */   "is_promise": () => (/* binding */ is_promise),
/* harmony export */   "is_void": () => (/* binding */ is_void),
/* harmony export */   "listen": () => (/* binding */ listen),
/* harmony export */   "listen_dev": () => (/* binding */ listen_dev),
/* harmony export */   "loop": () => (/* binding */ loop),
/* harmony export */   "loop_guard": () => (/* binding */ loop_guard),
/* harmony export */   "merge_ssr_styles": () => (/* binding */ merge_ssr_styles),
/* harmony export */   "missing_component": () => (/* binding */ missing_component),
/* harmony export */   "mount_component": () => (/* binding */ mount_component),
/* harmony export */   "noop": () => (/* binding */ noop),
/* harmony export */   "not_equal": () => (/* binding */ not_equal),
/* harmony export */   "now": () => (/* binding */ now),
/* harmony export */   "null_to_empty": () => (/* binding */ null_to_empty),
/* harmony export */   "object_without_properties": () => (/* binding */ object_without_properties),
/* harmony export */   "onDestroy": () => (/* binding */ onDestroy),
/* harmony export */   "onMount": () => (/* binding */ onMount),
/* harmony export */   "once": () => (/* binding */ once),
/* harmony export */   "outro_and_destroy_block": () => (/* binding */ outro_and_destroy_block),
/* harmony export */   "prevent_default": () => (/* binding */ prevent_default),
/* harmony export */   "prop_dev": () => (/* binding */ prop_dev),
/* harmony export */   "query_selector_all": () => (/* binding */ query_selector_all),
/* harmony export */   "raf": () => (/* binding */ raf),
/* harmony export */   "run": () => (/* binding */ run),
/* harmony export */   "run_all": () => (/* binding */ run_all),
/* harmony export */   "safe_not_equal": () => (/* binding */ safe_not_equal),
/* harmony export */   "schedule_update": () => (/* binding */ schedule_update),
/* harmony export */   "select_multiple_value": () => (/* binding */ select_multiple_value),
/* harmony export */   "select_option": () => (/* binding */ select_option),
/* harmony export */   "select_options": () => (/* binding */ select_options),
/* harmony export */   "select_value": () => (/* binding */ select_value),
/* harmony export */   "self": () => (/* binding */ self),
/* harmony export */   "setContext": () => (/* binding */ setContext),
/* harmony export */   "set_attributes": () => (/* binding */ set_attributes),
/* harmony export */   "set_current_component": () => (/* binding */ set_current_component),
/* harmony export */   "set_custom_element_data": () => (/* binding */ set_custom_element_data),
/* harmony export */   "set_custom_element_data_map": () => (/* binding */ set_custom_element_data_map),
/* harmony export */   "set_data": () => (/* binding */ set_data),
/* harmony export */   "set_data_dev": () => (/* binding */ set_data_dev),
/* harmony export */   "set_input_type": () => (/* binding */ set_input_type),
/* harmony export */   "set_input_value": () => (/* binding */ set_input_value),
/* harmony export */   "set_now": () => (/* binding */ set_now),
/* harmony export */   "set_raf": () => (/* binding */ set_raf),
/* harmony export */   "set_store_value": () => (/* binding */ set_store_value),
/* harmony export */   "set_style": () => (/* binding */ set_style),
/* harmony export */   "set_svg_attributes": () => (/* binding */ set_svg_attributes),
/* harmony export */   "space": () => (/* binding */ space),
/* harmony export */   "spread": () => (/* binding */ spread),
/* harmony export */   "src_url_equal": () => (/* binding */ src_url_equal),
/* harmony export */   "start_hydrating": () => (/* binding */ start_hydrating),
/* harmony export */   "stop_propagation": () => (/* binding */ stop_propagation),
/* harmony export */   "subscribe": () => (/* binding */ subscribe),
/* harmony export */   "svg_element": () => (/* binding */ svg_element),
/* harmony export */   "text": () => (/* binding */ text),
/* harmony export */   "tick": () => (/* binding */ tick),
/* harmony export */   "time_ranges_to_array": () => (/* binding */ time_ranges_to_array),
/* harmony export */   "to_number": () => (/* binding */ to_number),
/* harmony export */   "toggle_class": () => (/* binding */ toggle_class),
/* harmony export */   "transition_in": () => (/* binding */ transition_in),
/* harmony export */   "transition_out": () => (/* binding */ transition_out),
/* harmony export */   "trusted": () => (/* binding */ trusted),
/* harmony export */   "update_await_block_branch": () => (/* binding */ update_await_block_branch),
/* harmony export */   "update_keyed_each": () => (/* binding */ update_keyed_each),
/* harmony export */   "update_slot": () => (/* binding */ update_slot),
/* harmony export */   "update_slot_base": () => (/* binding */ update_slot_base),
/* harmony export */   "validate_component": () => (/* binding */ validate_component),
/* harmony export */   "validate_dynamic_element": () => (/* binding */ validate_dynamic_element),
/* harmony export */   "validate_each_argument": () => (/* binding */ validate_each_argument),
/* harmony export */   "validate_each_keys": () => (/* binding */ validate_each_keys),
/* harmony export */   "validate_slots": () => (/* binding */ validate_slots),
/* harmony export */   "validate_store": () => (/* binding */ validate_store),
/* harmony export */   "validate_void_dynamic_element": () => (/* binding */ validate_void_dynamic_element),
/* harmony export */   "xlink_attr": () => (/* binding */ xlink_attr)
/* harmony export */ });
function _wrapNativeSuper(Class) { var _cache = typeof Map === "function" ? new Map() : undefined; _wrapNativeSuper = function _wrapNativeSuper(Class) { if (Class === null || !_isNativeFunction(Class)) return Class; if (typeof Class !== "function") { throw new TypeError("Super expression must either be null or a function"); } if (typeof _cache !== "undefined") { if (_cache.has(Class)) return _cache.get(Class); _cache.set(Class, Wrapper); } function Wrapper() { return _construct(Class, arguments, _getPrototypeOf(this).constructor); } Wrapper.prototype = Object.create(Class.prototype, { constructor: { value: Wrapper, enumerable: false, writable: true, configurable: true } }); return _setPrototypeOf(Wrapper, Class); }; return _wrapNativeSuper(Class); }
function _construct(Parent, args, Class) { if (_isNativeReflectConstruct()) { _construct = Reflect.construct.bind(); } else { _construct = function _construct(Parent, args, Class) { var a = [null]; a.push.apply(a, args); var Constructor = Function.bind.apply(Parent, a); var instance = new Constructor(); if (Class) _setPrototypeOf(instance, Class.prototype); return instance; }; } return _construct.apply(null, arguments); }
function _isNativeFunction(fn) { return Function.toString.call(fn).indexOf("[native code]") !== -1; }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _get() { if (typeof Reflect !== "undefined" && Reflect.get) { _get = Reflect.get.bind(); } else { _get = function _get(target, property, receiver) { var base = _superPropBase(target, property); if (!base) return; var desc = Object.getOwnPropertyDescriptor(base, property); if (desc.get) { return desc.get.call(arguments.length < 3 ? target : receiver); } return desc.value; }; } return _get.apply(this, arguments); }
function _superPropBase(object, property) { while (!Object.prototype.hasOwnProperty.call(object, property)) { object = _getPrototypeOf(object); if (object === null) break; } return object; }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function noop() {}
var identity = function identity(x) {
  return x;
};
function assign(tar, src) {
  // @ts-ignore
  for (var k in src) tar[k] = src[k];
  return tar;
}
// Adapted from https://github.com/then/is-promise/blob/master/index.js
// Distributed under MIT License https://github.com/then/is-promise/blob/master/LICENSE
function is_promise(value) {
  return !!value && (_typeof(value) === 'object' || typeof value === 'function') && typeof value.then === 'function';
}
function add_location(element, file, line, column, _char) {
  element.__svelte_meta = {
    loc: {
      file: file,
      line: line,
      column: column,
      "char": _char
    }
  };
}
function run(fn) {
  return fn();
}
function blank_object() {
  return Object.create(null);
}
function run_all(fns) {
  fns.forEach(run);
}
function is_function(thing) {
  return typeof thing === 'function';
}
function safe_not_equal(a, b) {
  return a != a ? b == b : a !== b || a && _typeof(a) === 'object' || typeof a === 'function';
}
var src_url_equal_anchor;
function src_url_equal(element_src, url) {
  if (!src_url_equal_anchor) {
    src_url_equal_anchor = document.createElement('a');
  }
  src_url_equal_anchor.href = url;
  return element_src === src_url_equal_anchor.href;
}
function not_equal(a, b) {
  return a != a ? b == b : a !== b;
}
function is_empty(obj) {
  return Object.keys(obj).length === 0;
}
function validate_store(store, name) {
  if (store != null && typeof store.subscribe !== 'function') {
    throw new Error("'".concat(name, "' is not a store with a 'subscribe' method"));
  }
}
function subscribe(store) {
  if (store == null) {
    return noop;
  }
  for (var _len = arguments.length, callbacks = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    callbacks[_key - 1] = arguments[_key];
  }
  var unsub = store.subscribe.apply(store, callbacks);
  return unsub.unsubscribe ? function () {
    return unsub.unsubscribe();
  } : unsub;
}
function get_store_value(store) {
  var value;
  subscribe(store, function (_) {
    return value = _;
  })();
  return value;
}
function component_subscribe(component, store, callback) {
  component.$$.on_destroy.push(subscribe(store, callback));
}
function create_slot(definition, ctx, $$scope, fn) {
  if (definition) {
    var slot_ctx = get_slot_context(definition, ctx, $$scope, fn);
    return definition[0](slot_ctx);
  }
}
function get_slot_context(definition, ctx, $$scope, fn) {
  return definition[1] && fn ? assign($$scope.ctx.slice(), definition[1](fn(ctx))) : $$scope.ctx;
}
function get_slot_changes(definition, $$scope, dirty, fn) {
  if (definition[2] && fn) {
    var lets = definition[2](fn(dirty));
    if ($$scope.dirty === undefined) {
      return lets;
    }
    if (_typeof(lets) === 'object') {
      var merged = [];
      var len = Math.max($$scope.dirty.length, lets.length);
      for (var i = 0; i < len; i += 1) {
        merged[i] = $$scope.dirty[i] | lets[i];
      }
      return merged;
    }
    return $$scope.dirty | lets;
  }
  return $$scope.dirty;
}
function update_slot_base(slot, slot_definition, ctx, $$scope, slot_changes, get_slot_context_fn) {
  if (slot_changes) {
    var slot_context = get_slot_context(slot_definition, ctx, $$scope, get_slot_context_fn);
    slot.p(slot_context, slot_changes);
  }
}
function update_slot(slot, slot_definition, ctx, $$scope, dirty, get_slot_changes_fn, get_slot_context_fn) {
  var slot_changes = get_slot_changes(slot_definition, $$scope, dirty, get_slot_changes_fn);
  update_slot_base(slot, slot_definition, ctx, $$scope, slot_changes, get_slot_context_fn);
}
function get_all_dirty_from_scope($$scope) {
  if ($$scope.ctx.length > 32) {
    var dirty = [];
    var length = $$scope.ctx.length / 32;
    for (var i = 0; i < length; i++) {
      dirty[i] = -1;
    }
    return dirty;
  }
  return -1;
}
function exclude_internal_props(props) {
  var result = {};
  for (var k in props) if (k[0] !== '$') result[k] = props[k];
  return result;
}
function compute_rest_props(props, keys) {
  var rest = {};
  keys = new Set(keys);
  for (var k in props) if (!keys.has(k) && k[0] !== '$') rest[k] = props[k];
  return rest;
}
function compute_slots(slots) {
  var result = {};
  for (var key in slots) {
    result[key] = true;
  }
  return result;
}
function once(fn) {
  var ran = false;
  return function () {
    if (ran) return;
    ran = true;
    for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
      args[_key2] = arguments[_key2];
    }
    fn.call.apply(fn, [this].concat(args));
  };
}
function null_to_empty(value) {
  return value == null ? '' : value;
}
function set_store_value(store, ret, value) {
  store.set(value);
  return ret;
}
var has_prop = function has_prop(obj, prop) {
  return Object.prototype.hasOwnProperty.call(obj, prop);
};
function action_destroyer(action_result) {
  return action_result && is_function(action_result.destroy) ? action_result.destroy : noop;
}
var is_client = typeof window !== 'undefined';
var now = is_client ? function () {
  return window.performance.now();
} : function () {
  return Date.now();
};
var raf = is_client ? function (cb) {
  return requestAnimationFrame(cb);
} : noop;
// used internally for testing
function set_now(fn) {
  now = fn;
}
function set_raf(fn) {
  raf = fn;
}
var tasks = new Set();
function run_tasks(now) {
  tasks.forEach(function (task) {
    if (!task.c(now)) {
      tasks["delete"](task);
      task.f();
    }
  });
  if (tasks.size !== 0) raf(run_tasks);
}
/**
 * For testing purposes only!
 */
function clear_loops() {
  tasks.clear();
}
/**
 * Creates a new task that runs on each raf frame
 * until it returns a falsy value or is aborted
 */
function loop(callback) {
  var task;
  if (tasks.size === 0) raf(run_tasks);
  return {
    promise: new Promise(function (fulfill) {
      tasks.add(task = {
        c: callback,
        f: fulfill
      });
    }),
    abort: function abort() {
      tasks["delete"](task);
    }
  };
}

// Track which nodes are claimed during hydration. Unclaimed nodes can then be removed from the DOM
// at the end of hydration without touching the remaining nodes.
var is_hydrating = false;
function start_hydrating() {
  is_hydrating = true;
}
function end_hydrating() {
  is_hydrating = false;
}
function upper_bound(low, high, key, value) {
  // Return first index of value larger than input value in the range [low, high)
  while (low < high) {
    var mid = low + (high - low >> 1);
    if (key(mid) <= value) {
      low = mid + 1;
    } else {
      high = mid;
    }
  }
  return low;
}
function init_hydrate(target) {
  if (target.hydrate_init) return;
  target.hydrate_init = true;
  // We know that all children have claim_order values since the unclaimed have been detached if target is not <head>
  var children = target.childNodes;
  // If target is <head>, there may be children without claim_order
  if (target.nodeName === 'HEAD') {
    var myChildren = [];
    for (var i = 0; i < children.length; i++) {
      var node = children[i];
      if (node.claim_order !== undefined) {
        myChildren.push(node);
      }
    }
    children = myChildren;
  }
  /*
  * Reorder claimed children optimally.
  * We can reorder claimed children optimally by finding the longest subsequence of
  * nodes that are already claimed in order and only moving the rest. The longest
  * subsequence of nodes that are claimed in order can be found by
  * computing the longest increasing subsequence of .claim_order values.
  *
  * This algorithm is optimal in generating the least amount of reorder operations
  * possible.
  *
  * Proof:
  * We know that, given a set of reordering operations, the nodes that do not move
  * always form an increasing subsequence, since they do not move among each other
  * meaning that they must be already ordered among each other. Thus, the maximal
  * set of nodes that do not move form a longest increasing subsequence.
  */
  // Compute longest increasing subsequence
  // m: subsequence length j => index k of smallest value that ends an increasing subsequence of length j
  var m = new Int32Array(children.length + 1);
  // Predecessor indices + 1
  var p = new Int32Array(children.length);
  m[0] = -1;
  var longest = 0;
  for (var _i = 0; _i < children.length; _i++) {
    var current = children[_i].claim_order;
    // Find the largest subsequence length such that it ends in a value less than our current value
    // upper_bound returns first greater value, so we subtract one
    // with fast path for when we are on the current longest subsequence
    var seqLen = (longest > 0 && children[m[longest]].claim_order <= current ? longest + 1 : upper_bound(1, longest, function (idx) {
      return children[m[idx]].claim_order;
    }, current)) - 1;
    p[_i] = m[seqLen] + 1;
    var newLen = seqLen + 1;
    // We can guarantee that current is the smallest value. Otherwise, we would have generated a longer sequence.
    m[newLen] = _i;
    longest = Math.max(newLen, longest);
  }
  // The longest increasing subsequence of nodes (initially reversed)
  var lis = [];
  // The rest of the nodes, nodes that will be moved
  var toMove = [];
  var last = children.length - 1;
  for (var cur = m[longest] + 1; cur != 0; cur = p[cur - 1]) {
    lis.push(children[cur - 1]);
    for (; last >= cur; last--) {
      toMove.push(children[last]);
    }
    last--;
  }
  for (; last >= 0; last--) {
    toMove.push(children[last]);
  }
  lis.reverse();
  // We sort the nodes being moved to guarantee that their insertion order matches the claim order
  toMove.sort(function (a, b) {
    return a.claim_order - b.claim_order;
  });
  // Finally, we move the nodes
  for (var _i2 = 0, j = 0; _i2 < toMove.length; _i2++) {
    while (j < lis.length && toMove[_i2].claim_order >= lis[j].claim_order) {
      j++;
    }
    var anchor = j < lis.length ? lis[j] : null;
    target.insertBefore(toMove[_i2], anchor);
  }
}
function append(target, node) {
  target.appendChild(node);
}
function append_styles(target, style_sheet_id, styles) {
  var append_styles_to = get_root_for_style(target);
  if (!append_styles_to.getElementById(style_sheet_id)) {
    var style = element('style');
    style.id = style_sheet_id;
    style.textContent = styles;
    append_stylesheet(append_styles_to, style);
  }
}
function get_root_for_style(node) {
  if (!node) return document;
  var root = node.getRootNode ? node.getRootNode() : node.ownerDocument;
  if (root && root.host) {
    return root;
  }
  return node.ownerDocument;
}
function append_empty_stylesheet(node) {
  var style_element = element('style');
  append_stylesheet(get_root_for_style(node), style_element);
  return style_element.sheet;
}
function append_stylesheet(node, style) {
  append(node.head || node, style);
  return style.sheet;
}
function append_hydration(target, node) {
  if (is_hydrating) {
    init_hydrate(target);
    if (target.actual_end_child === undefined || target.actual_end_child !== null && target.actual_end_child.parentNode !== target) {
      target.actual_end_child = target.firstChild;
    }
    // Skip nodes of undefined ordering
    while (target.actual_end_child !== null && target.actual_end_child.claim_order === undefined) {
      target.actual_end_child = target.actual_end_child.nextSibling;
    }
    if (node !== target.actual_end_child) {
      // We only insert if the ordering of this node should be modified or the parent node is not target
      if (node.claim_order !== undefined || node.parentNode !== target) {
        target.insertBefore(node, target.actual_end_child);
      }
    } else {
      target.actual_end_child = node.nextSibling;
    }
  } else if (node.parentNode !== target || node.nextSibling !== null) {
    target.appendChild(node);
  }
}
function insert(target, node, anchor) {
  target.insertBefore(node, anchor || null);
}
function insert_hydration(target, node, anchor) {
  if (is_hydrating && !anchor) {
    append_hydration(target, node);
  } else if (node.parentNode !== target || node.nextSibling != anchor) {
    target.insertBefore(node, anchor || null);
  }
}
function detach(node) {
  if (node.parentNode) {
    node.parentNode.removeChild(node);
  }
}
function destroy_each(iterations, detaching) {
  for (var i = 0; i < iterations.length; i += 1) {
    if (iterations[i]) iterations[i].d(detaching);
  }
}
function element(name) {
  return document.createElement(name);
}
function element_is(name, is) {
  return document.createElement(name, {
    is: is
  });
}
function object_without_properties(obj, exclude) {
  var target = {};
  for (var k in obj) {
    if (has_prop(obj, k)
    // @ts-ignore
    && exclude.indexOf(k) === -1) {
      // @ts-ignore
      target[k] = obj[k];
    }
  }
  return target;
}
function svg_element(name) {
  return document.createElementNS('http://www.w3.org/2000/svg', name);
}
function text(data) {
  return document.createTextNode(data);
}
function space() {
  return text(' ');
}
function empty() {
  return text('');
}
function listen(node, event, handler, options) {
  node.addEventListener(event, handler, options);
  return function () {
    return node.removeEventListener(event, handler, options);
  };
}
function prevent_default(fn) {
  return function (event) {
    event.preventDefault();
    // @ts-ignore
    return fn.call(this, event);
  };
}
function stop_propagation(fn) {
  return function (event) {
    event.stopPropagation();
    // @ts-ignore
    return fn.call(this, event);
  };
}
function self(fn) {
  return function (event) {
    // @ts-ignore
    if (event.target === this) fn.call(this, event);
  };
}
function trusted(fn) {
  return function (event) {
    // @ts-ignore
    if (event.isTrusted) fn.call(this, event);
  };
}
function attr(node, attribute, value) {
  if (value == null) node.removeAttribute(attribute);else if (node.getAttribute(attribute) !== value) node.setAttribute(attribute, value);
}
function set_attributes(node, attributes) {
  // @ts-ignore
  var descriptors = Object.getOwnPropertyDescriptors(node.__proto__);
  for (var key in attributes) {
    if (attributes[key] == null) {
      node.removeAttribute(key);
    } else if (key === 'style') {
      node.style.cssText = attributes[key];
    } else if (key === '__value') {
      node.value = node[key] = attributes[key];
    } else if (descriptors[key] && descriptors[key].set) {
      node[key] = attributes[key];
    } else {
      attr(node, key, attributes[key]);
    }
  }
}
function set_svg_attributes(node, attributes) {
  for (var key in attributes) {
    attr(node, key, attributes[key]);
  }
}
function set_custom_element_data_map(node, data_map) {
  Object.keys(data_map).forEach(function (key) {
    set_custom_element_data(node, key, data_map[key]);
  });
}
function set_custom_element_data(node, prop, value) {
  if (prop in node) {
    node[prop] = typeof node[prop] === 'boolean' && value === '' ? true : value;
  } else {
    attr(node, prop, value);
  }
}
function xlink_attr(node, attribute, value) {
  node.setAttributeNS('http://www.w3.org/1999/xlink', attribute, value);
}
function get_binding_group_value(group, __value, checked) {
  var value = new Set();
  for (var i = 0; i < group.length; i += 1) {
    if (group[i].checked) value.add(group[i].__value);
  }
  if (!checked) {
    value["delete"](__value);
  }
  return Array.from(value);
}
function to_number(value) {
  return value === '' ? null : +value;
}
function time_ranges_to_array(ranges) {
  var array = [];
  for (var i = 0; i < ranges.length; i += 1) {
    array.push({
      start: ranges.start(i),
      end: ranges.end(i)
    });
  }
  return array;
}
function children(element) {
  return Array.from(element.childNodes);
}
function init_claim_info(nodes) {
  if (nodes.claim_info === undefined) {
    nodes.claim_info = {
      last_index: 0,
      total_claimed: 0
    };
  }
}
function claim_node(nodes, predicate, processNode, createNode) {
  var dontUpdateLastIndex = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;
  // Try to find nodes in an order such that we lengthen the longest increasing subsequence
  init_claim_info(nodes);
  var resultNode = function () {
    // We first try to find an element after the previous one
    for (var i = nodes.claim_info.last_index; i < nodes.length; i++) {
      var node = nodes[i];
      if (predicate(node)) {
        var replacement = processNode(node);
        if (replacement === undefined) {
          nodes.splice(i, 1);
        } else {
          nodes[i] = replacement;
        }
        if (!dontUpdateLastIndex) {
          nodes.claim_info.last_index = i;
        }
        return node;
      }
    }
    // Otherwise, we try to find one before
    // We iterate in reverse so that we don't go too far back
    for (var _i3 = nodes.claim_info.last_index - 1; _i3 >= 0; _i3--) {
      var _node = nodes[_i3];
      if (predicate(_node)) {
        var _replacement = processNode(_node);
        if (_replacement === undefined) {
          nodes.splice(_i3, 1);
        } else {
          nodes[_i3] = _replacement;
        }
        if (!dontUpdateLastIndex) {
          nodes.claim_info.last_index = _i3;
        } else if (_replacement === undefined) {
          // Since we spliced before the last_index, we decrease it
          nodes.claim_info.last_index--;
        }
        return _node;
      }
    }
    // If we can't find any matching node, we create a new one
    return createNode();
  }();
  resultNode.claim_order = nodes.claim_info.total_claimed;
  nodes.claim_info.total_claimed += 1;
  return resultNode;
}
function claim_element_base(nodes, name, attributes, create_element) {
  return claim_node(nodes, function (node) {
    return node.nodeName === name;
  }, function (node) {
    var remove = [];
    for (var j = 0; j < node.attributes.length; j++) {
      var attribute = node.attributes[j];
      if (!attributes[attribute.name]) {
        remove.push(attribute.name);
      }
    }
    remove.forEach(function (v) {
      return node.removeAttribute(v);
    });
    return undefined;
  }, function () {
    return create_element(name);
  });
}
function claim_element(nodes, name, attributes) {
  return claim_element_base(nodes, name, attributes, element);
}
function claim_svg_element(nodes, name, attributes) {
  return claim_element_base(nodes, name, attributes, svg_element);
}
function claim_text(nodes, data) {
  return claim_node(nodes, function (node) {
    return node.nodeType === 3;
  }, function (node) {
    var dataStr = '' + data;
    if (node.data.startsWith(dataStr)) {
      if (node.data.length !== dataStr.length) {
        return node.splitText(dataStr.length);
      }
    } else {
      node.data = dataStr;
    }
  }, function () {
    return text(data);
  }, true // Text nodes should not update last index since it is likely not worth it to eliminate an increasing subsequence of actual elements
  );
}

function claim_space(nodes) {
  return claim_text(nodes, ' ');
}
function find_comment(nodes, text, start) {
  for (var i = start; i < nodes.length; i += 1) {
    var node = nodes[i];
    if (node.nodeType === 8 /* comment node */ && node.textContent.trim() === text) {
      return i;
    }
  }
  return nodes.length;
}
function claim_html_tag(nodes, is_svg) {
  // find html opening tag
  var start_index = find_comment(nodes, 'HTML_TAG_START', 0);
  var end_index = find_comment(nodes, 'HTML_TAG_END', start_index);
  if (start_index === end_index) {
    return new HtmlTagHydration(undefined, is_svg);
  }
  init_claim_info(nodes);
  var html_tag_nodes = nodes.splice(start_index, end_index - start_index + 1);
  detach(html_tag_nodes[0]);
  detach(html_tag_nodes[html_tag_nodes.length - 1]);
  var claimed_nodes = html_tag_nodes.slice(1, html_tag_nodes.length - 1);
  var _iterator = _createForOfIteratorHelper(claimed_nodes),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var n = _step.value;
      n.claim_order = nodes.claim_info.total_claimed;
      nodes.claim_info.total_claimed += 1;
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return new HtmlTagHydration(claimed_nodes, is_svg);
}
function set_data(text, data) {
  data = '' + data;
  if (text.wholeText !== data) text.data = data;
}
function set_input_value(input, value) {
  input.value = value == null ? '' : value;
}
function set_input_type(input, type) {
  try {
    input.type = type;
  } catch (e) {
    // do nothing
  }
}
function set_style(node, key, value, important) {
  if (value === null) {
    node.style.removeProperty(key);
  } else {
    node.style.setProperty(key, value, important ? 'important' : '');
  }
}
function select_option(select, value) {
  for (var i = 0; i < select.options.length; i += 1) {
    var option = select.options[i];
    if (option.__value === value) {
      option.selected = true;
      return;
    }
  }
  select.selectedIndex = -1; // no option should be selected
}

function select_options(select, value) {
  for (var i = 0; i < select.options.length; i += 1) {
    var option = select.options[i];
    option.selected = ~value.indexOf(option.__value);
  }
}
function select_value(select) {
  var selected_option = select.querySelector(':checked') || select.options[0];
  return selected_option && selected_option.__value;
}
function select_multiple_value(select) {
  return [].map.call(select.querySelectorAll(':checked'), function (option) {
    return option.__value;
  });
}
// unfortunately this can't be a constant as that wouldn't be tree-shakeable
// so we cache the result instead
var crossorigin;
function is_crossorigin() {
  if (crossorigin === undefined) {
    crossorigin = false;
    try {
      if (typeof window !== 'undefined' && window.parent) {
        void window.parent.document;
      }
    } catch (error) {
      crossorigin = true;
    }
  }
  return crossorigin;
}
function add_resize_listener(node, fn) {
  var computed_style = getComputedStyle(node);
  if (computed_style.position === 'static') {
    node.style.position = 'relative';
  }
  var iframe = element('iframe');
  iframe.setAttribute('style', 'display: block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; ' + 'overflow: hidden; border: 0; opacity: 0; pointer-events: none; z-index: -1;');
  iframe.setAttribute('aria-hidden', 'true');
  iframe.tabIndex = -1;
  var crossorigin = is_crossorigin();
  var unsubscribe;
  if (crossorigin) {
    iframe.src = "data:text/html,<script>onresize=function(){parent.postMessage(0,'*')}</script>";
    unsubscribe = listen(window, 'message', function (event) {
      if (event.source === iframe.contentWindow) fn();
    });
  } else {
    iframe.src = 'about:blank';
    iframe.onload = function () {
      unsubscribe = listen(iframe.contentWindow, 'resize', fn);
    };
  }
  append(node, iframe);
  return function () {
    if (crossorigin) {
      unsubscribe();
    } else if (unsubscribe && iframe.contentWindow) {
      unsubscribe();
    }
    detach(iframe);
  };
}
function toggle_class(element, name, toggle) {
  element.classList[toggle ? 'add' : 'remove'](name);
}
function custom_event(type, detail) {
  var _ref = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
    _ref$bubbles = _ref.bubbles,
    bubbles = _ref$bubbles === void 0 ? false : _ref$bubbles,
    _ref$cancelable = _ref.cancelable,
    cancelable = _ref$cancelable === void 0 ? false : _ref$cancelable;
  var e = document.createEvent('CustomEvent');
  e.initCustomEvent(type, bubbles, cancelable, detail);
  return e;
}
function query_selector_all(selector) {
  var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document.body;
  return Array.from(parent.querySelectorAll(selector));
}
function head_selector(nodeId, head) {
  var result = [];
  var started = 0;
  var _iterator2 = _createForOfIteratorHelper(head.childNodes),
    _step2;
  try {
    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
      var node = _step2.value;
      if (node.nodeType === 8 /* comment node */) {
        var comment = node.textContent.trim();
        if (comment === "HEAD_".concat(nodeId, "_END")) {
          started -= 1;
          result.push(node);
        } else if (comment === "HEAD_".concat(nodeId, "_START")) {
          started += 1;
          result.push(node);
        }
      } else if (started > 0) {
        result.push(node);
      }
    }
  } catch (err) {
    _iterator2.e(err);
  } finally {
    _iterator2.f();
  }
  return result;
}
var HtmlTag = /*#__PURE__*/function () {
  function HtmlTag() {
    var is_svg = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    _classCallCheck(this, HtmlTag);
    this.is_svg = false;
    this.is_svg = is_svg;
    this.e = this.n = null;
  }
  _createClass(HtmlTag, [{
    key: "c",
    value: function c(html) {
      this.h(html);
    }
  }, {
    key: "m",
    value: function m(html, target) {
      var anchor = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      if (!this.e) {
        if (this.is_svg) this.e = svg_element(target.nodeName);else this.e = element(target.nodeName);
        this.t = target;
        this.c(html);
      }
      this.i(anchor);
    }
  }, {
    key: "h",
    value: function h(html) {
      this.e.innerHTML = html;
      this.n = Array.from(this.e.childNodes);
    }
  }, {
    key: "i",
    value: function i(anchor) {
      for (var i = 0; i < this.n.length; i += 1) {
        insert(this.t, this.n[i], anchor);
      }
    }
  }, {
    key: "p",
    value: function p(html) {
      this.d();
      this.h(html);
      this.i(this.a);
    }
  }, {
    key: "d",
    value: function d() {
      this.n.forEach(detach);
    }
  }]);
  return HtmlTag;
}();
var HtmlTagHydration = /*#__PURE__*/function (_HtmlTag) {
  _inherits(HtmlTagHydration, _HtmlTag);
  var _super = _createSuper(HtmlTagHydration);
  function HtmlTagHydration(claimed_nodes) {
    var _this;
    var is_svg = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    _classCallCheck(this, HtmlTagHydration);
    _this = _super.call(this, is_svg);
    _this.e = _this.n = null;
    _this.l = claimed_nodes;
    return _this;
  }
  _createClass(HtmlTagHydration, [{
    key: "c",
    value: function c(html) {
      if (this.l) {
        this.n = this.l;
      } else {
        _get(_getPrototypeOf(HtmlTagHydration.prototype), "c", this).call(this, html);
      }
    }
  }, {
    key: "i",
    value: function i(anchor) {
      for (var i = 0; i < this.n.length; i += 1) {
        insert_hydration(this.t, this.n[i], anchor);
      }
    }
  }]);
  return HtmlTagHydration;
}(HtmlTag);
function attribute_to_object(attributes) {
  var result = {};
  var _iterator3 = _createForOfIteratorHelper(attributes),
    _step3;
  try {
    for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
      var attribute = _step3.value;
      result[attribute.name] = attribute.value;
    }
  } catch (err) {
    _iterator3.e(err);
  } finally {
    _iterator3.f();
  }
  return result;
}
function get_custom_elements_slots(element) {
  var result = {};
  element.childNodes.forEach(function (node) {
    result[node.slot || 'default'] = true;
  });
  return result;
}
function construct_svelte_component(component, props) {
  return new component(props);
}

// we need to store the information for multiple documents because a Svelte application could also contain iframes
// https://github.com/sveltejs/svelte/issues/3624
var managed_styles = new Map();
var active = 0;
// https://github.com/darkskyapp/string-hash/blob/master/index.js
function hash(str) {
  var hash = 5381;
  var i = str.length;
  while (i--) hash = (hash << 5) - hash ^ str.charCodeAt(i);
  return hash >>> 0;
}
function create_style_information(doc, node) {
  var info = {
    stylesheet: append_empty_stylesheet(node),
    rules: {}
  };
  managed_styles.set(doc, info);
  return info;
}
function create_rule(node, a, b, duration, delay, ease, fn) {
  var uid = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : 0;
  var step = 16.666 / duration;
  var keyframes = '{\n';
  for (var p = 0; p <= 1; p += step) {
    var t = a + (b - a) * ease(p);
    keyframes += p * 100 + "%{".concat(fn(t, 1 - t), "}\n");
  }
  var rule = keyframes + "100% {".concat(fn(b, 1 - b), "}\n}");
  var name = "__svelte_".concat(hash(rule), "_").concat(uid);
  var doc = get_root_for_style(node);
  var _ref2 = managed_styles.get(doc) || create_style_information(doc, node),
    stylesheet = _ref2.stylesheet,
    rules = _ref2.rules;
  if (!rules[name]) {
    rules[name] = true;
    stylesheet.insertRule("@keyframes ".concat(name, " ").concat(rule), stylesheet.cssRules.length);
  }
  var animation = node.style.animation || '';
  node.style.animation = "".concat(animation ? "".concat(animation, ", ") : '').concat(name, " ").concat(duration, "ms linear ").concat(delay, "ms 1 both");
  active += 1;
  return name;
}
function delete_rule(node, name) {
  var previous = (node.style.animation || '').split(', ');
  var next = previous.filter(name ? function (anim) {
    return anim.indexOf(name) < 0;
  } // remove specific animation
  : function (anim) {
    return anim.indexOf('__svelte') === -1;
  } // remove all Svelte animations
  );

  var deleted = previous.length - next.length;
  if (deleted) {
    node.style.animation = next.join(', ');
    active -= deleted;
    if (!active) clear_rules();
  }
}
function clear_rules() {
  raf(function () {
    if (active) return;
    managed_styles.forEach(function (info) {
      var ownerNode = info.stylesheet.ownerNode;
      // there is no ownerNode if it runs on jsdom.
      if (ownerNode) detach(ownerNode);
    });
    managed_styles.clear();
  });
}
function create_animation(node, from, fn, params) {
  if (!from) return noop;
  var to = node.getBoundingClientRect();
  if (from.left === to.left && from.right === to.right && from.top === to.top && from.bottom === to.bottom) return noop;
  var _fn = fn(node, {
      from: from,
      to: to
    }, params),
    _fn$delay = _fn.delay,
    delay = _fn$delay === void 0 ? 0 : _fn$delay,
    _fn$duration = _fn.duration,
    duration = _fn$duration === void 0 ? 300 : _fn$duration,
    _fn$easing = _fn.easing,
    easing = _fn$easing === void 0 ? identity : _fn$easing,
    _fn$start = _fn.start,
    start_time = _fn$start === void 0 ? now() + delay : _fn$start,
    _fn$end = _fn.end,
    end = _fn$end === void 0 ? start_time + duration : _fn$end,
    _fn$tick = _fn.tick,
    tick = _fn$tick === void 0 ? noop : _fn$tick,
    css = _fn.css;
  var running = true;
  var started = false;
  var name;
  function start() {
    if (css) {
      name = create_rule(node, 0, 1, duration, delay, easing, css);
    }
    if (!delay) {
      started = true;
    }
  }
  function stop() {
    if (css) delete_rule(node, name);
    running = false;
  }
  loop(function (now) {
    if (!started && now >= start_time) {
      started = true;
    }
    if (started && now >= end) {
      tick(1, 0);
      stop();
    }
    if (!running) {
      return false;
    }
    if (started) {
      var p = now - start_time;
      var t = 0 + 1 * easing(p / duration);
      tick(t, 1 - t);
    }
    return true;
  });
  start();
  tick(0, 1);
  return stop;
}
function fix_position(node) {
  var style = getComputedStyle(node);
  if (style.position !== 'absolute' && style.position !== 'fixed') {
    var width = style.width,
      height = style.height;
    var a = node.getBoundingClientRect();
    node.style.position = 'absolute';
    node.style.width = width;
    node.style.height = height;
    add_transform(node, a);
  }
}
function add_transform(node, a) {
  var b = node.getBoundingClientRect();
  if (a.left !== b.left || a.top !== b.top) {
    var style = getComputedStyle(node);
    var transform = style.transform === 'none' ? '' : style.transform;
    node.style.transform = "".concat(transform, " translate(").concat(a.left - b.left, "px, ").concat(a.top - b.top, "px)");
  }
}
var current_component;
function set_current_component(component) {
  current_component = component;
}
function get_current_component() {
  if (!current_component) throw new Error('Function called outside component initialization');
  return current_component;
}
/**
 * Schedules a callback to run immediately before the component is updated after any state change.
 *
 * The first time the callback runs will be before the initial `onMount`
 *
 * https://svelte.dev/docs#run-time-svelte-beforeupdate
 */
function beforeUpdate(fn) {
  get_current_component().$$.before_update.push(fn);
}
/**
 * The `onMount` function schedules a callback to run as soon as the component has been mounted to the DOM.
 * It must be called during the component's initialisation (but doesn't need to live *inside* the component;
 * it can be called from an external module).
 *
 * `onMount` does not run inside a [server-side component](/docs#run-time-server-side-component-api).
 *
 * https://svelte.dev/docs#run-time-svelte-onmount
 */
function onMount(fn) {
  get_current_component().$$.on_mount.push(fn);
}
/**
 * Schedules a callback to run immediately after the component has been updated.
 *
 * The first time the callback runs will be after the initial `onMount`
 */
function afterUpdate(fn) {
  get_current_component().$$.after_update.push(fn);
}
/**
 * Schedules a callback to run immediately before the component is unmounted.
 *
 * Out of `onMount`, `beforeUpdate`, `afterUpdate` and `onDestroy`, this is the
 * only one that runs inside a server-side component.
 *
 * https://svelte.dev/docs#run-time-svelte-ondestroy
 */
function onDestroy(fn) {
  get_current_component().$$.on_destroy.push(fn);
}
/**
 * Creates an event dispatcher that can be used to dispatch [component events](/docs#template-syntax-component-directives-on-eventname).
 * Event dispatchers are functions that can take two arguments: `name` and `detail`.
 *
 * Component events created with `createEventDispatcher` create a
 * [CustomEvent](https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent).
 * These events do not [bubble](https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Building_blocks/Events#Event_bubbling_and_capture).
 * The `detail` argument corresponds to the [CustomEvent.detail](https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/detail)
 * property and can contain any type of data.
 *
 * https://svelte.dev/docs#run-time-svelte-createeventdispatcher
 */
function createEventDispatcher() {
  var component = get_current_component();
  return function (type, detail) {
    var _ref3 = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
      _ref3$cancelable = _ref3.cancelable,
      cancelable = _ref3$cancelable === void 0 ? false : _ref3$cancelable;
    var callbacks = component.$$.callbacks[type];
    if (callbacks) {
      // TODO are there situations where events could be dispatched
      // in a server (non-DOM) environment?
      var event = custom_event(type, detail, {
        cancelable: cancelable
      });
      callbacks.slice().forEach(function (fn) {
        fn.call(component, event);
      });
      return !event.defaultPrevented;
    }
    return true;
  };
}
/**
 * Associates an arbitrary `context` object with the current component and the specified `key`
 * and returns that object. The context is then available to children of the component
 * (including slotted content) with `getContext`.
 *
 * Like lifecycle functions, this must be called during component initialisation.
 *
 * https://svelte.dev/docs#run-time-svelte-setcontext
 */
function setContext(key, context) {
  get_current_component().$$.context.set(key, context);
  return context;
}
/**
 * Retrieves the context that belongs to the closest parent component with the specified `key`.
 * Must be called during component initialisation.
 *
 * https://svelte.dev/docs#run-time-svelte-getcontext
 */
function getContext(key) {
  return get_current_component().$$.context.get(key);
}
/**
 * Retrieves the whole context map that belongs to the closest parent component.
 * Must be called during component initialisation. Useful, for example, if you
 * programmatically create a component and want to pass the existing context to it.
 *
 * https://svelte.dev/docs#run-time-svelte-getallcontexts
 */
function getAllContexts() {
  return get_current_component().$$.context;
}
/**
 * Checks whether a given `key` has been set in the context of a parent component.
 * Must be called during component initialisation.
 *
 * https://svelte.dev/docs#run-time-svelte-hascontext
 */
function hasContext(key) {
  return get_current_component().$$.context.has(key);
}
// TODO figure out if we still want to support
// shorthand events, or if we want to implement
// a real bubbling mechanism
function bubble(component, event) {
  var _this2 = this;
  var callbacks = component.$$.callbacks[event.type];
  if (callbacks) {
    // @ts-ignore
    callbacks.slice().forEach(function (fn) {
      return fn.call(_this2, event);
    });
  }
}
var dirty_components = [];
var intros = {
  enabled: false
};
var binding_callbacks = [];
var render_callbacks = [];
var flush_callbacks = [];
var resolved_promise = Promise.resolve();
var update_scheduled = false;
function schedule_update() {
  if (!update_scheduled) {
    update_scheduled = true;
    resolved_promise.then(flush);
  }
}
function tick() {
  schedule_update();
  return resolved_promise;
}
function add_render_callback(fn) {
  render_callbacks.push(fn);
}
function add_flush_callback(fn) {
  flush_callbacks.push(fn);
}
// flush() calls callbacks in this order:
// 1. All beforeUpdate callbacks, in order: parents before children
// 2. All bind:this callbacks, in reverse order: children before parents.
// 3. All afterUpdate callbacks, in order: parents before children. EXCEPT
//    for afterUpdates called during the initial onMount, which are called in
//    reverse order: children before parents.
// Since callbacks might update component values, which could trigger another
// call to flush(), the following steps guard against this:
// 1. During beforeUpdate, any updated components will be added to the
//    dirty_components array and will cause a reentrant call to flush(). Because
//    the flush index is kept outside the function, the reentrant call will pick
//    up where the earlier call left off and go through all dirty components. The
//    current_component value is saved and restored so that the reentrant call will
//    not interfere with the "parent" flush() call.
// 2. bind:this callbacks cannot trigger new flush() calls.
// 3. During afterUpdate, any updated components will NOT have their afterUpdate
//    callback called a second time; the seen_callbacks set, outside the flush()
//    function, guarantees this behavior.
var seen_callbacks = new Set();
var flushidx = 0; // Do *not* move this inside the flush() function
function flush() {
  // Do not reenter flush while dirty components are updated, as this can
  // result in an infinite loop. Instead, let the inner flush handle it.
  // Reentrancy is ok afterwards for bindings etc.
  if (flushidx !== 0) {
    return;
  }
  var saved_component = current_component;
  do {
    // first, call beforeUpdate functions
    // and update components
    try {
      while (flushidx < dirty_components.length) {
        var component = dirty_components[flushidx];
        flushidx++;
        set_current_component(component);
        update(component.$$);
      }
    } catch (e) {
      // reset dirty state to not end up in a deadlocked state and then rethrow
      dirty_components.length = 0;
      flushidx = 0;
      throw e;
    }
    set_current_component(null);
    dirty_components.length = 0;
    flushidx = 0;
    while (binding_callbacks.length) binding_callbacks.pop()();
    // then, once components are updated, call
    // afterUpdate functions. This may cause
    // subsequent updates...
    for (var i = 0; i < render_callbacks.length; i += 1) {
      var callback = render_callbacks[i];
      if (!seen_callbacks.has(callback)) {
        // ...so guard against infinite loops
        seen_callbacks.add(callback);
        callback();
      }
    }
    render_callbacks.length = 0;
  } while (dirty_components.length);
  while (flush_callbacks.length) {
    flush_callbacks.pop()();
  }
  update_scheduled = false;
  seen_callbacks.clear();
  set_current_component(saved_component);
}
function update($$) {
  if ($$.fragment !== null) {
    $$.update();
    run_all($$.before_update);
    var dirty = $$.dirty;
    $$.dirty = [-1];
    $$.fragment && $$.fragment.p($$.ctx, dirty);
    $$.after_update.forEach(add_render_callback);
  }
}
var promise;
function wait() {
  if (!promise) {
    promise = Promise.resolve();
    promise.then(function () {
      promise = null;
    });
  }
  return promise;
}
function dispatch(node, direction, kind) {
  node.dispatchEvent(custom_event("".concat(direction ? 'intro' : 'outro').concat(kind)));
}
var outroing = new Set();
var outros;
function group_outros() {
  outros = {
    r: 0,
    c: [],
    p: outros // parent group
  };
}

function check_outros() {
  if (!outros.r) {
    run_all(outros.c);
  }
  outros = outros.p;
}
function transition_in(block, local) {
  if (block && block.i) {
    outroing["delete"](block);
    block.i(local);
  }
}
function transition_out(block, local, detach, callback) {
  if (block && block.o) {
    if (outroing.has(block)) return;
    outroing.add(block);
    outros.c.push(function () {
      outroing["delete"](block);
      if (callback) {
        if (detach) block.d(1);
        callback();
      }
    });
    block.o(local);
  } else if (callback) {
    callback();
  }
}
var null_transition = {
  duration: 0
};
function create_in_transition(node, fn, params) {
  var options = {
    direction: 'in'
  };
  var config = fn(node, params, options);
  var running = false;
  var animation_name;
  var task;
  var uid = 0;
  function cleanup() {
    if (animation_name) delete_rule(node, animation_name);
  }
  function go() {
    var _ref4 = config || null_transition,
      _ref4$delay = _ref4.delay,
      delay = _ref4$delay === void 0 ? 0 : _ref4$delay,
      _ref4$duration = _ref4.duration,
      duration = _ref4$duration === void 0 ? 300 : _ref4$duration,
      _ref4$easing = _ref4.easing,
      easing = _ref4$easing === void 0 ? identity : _ref4$easing,
      _ref4$tick = _ref4.tick,
      tick = _ref4$tick === void 0 ? noop : _ref4$tick,
      css = _ref4.css;
    if (css) animation_name = create_rule(node, 0, 1, duration, delay, easing, css, uid++);
    tick(0, 1);
    var start_time = now() + delay;
    var end_time = start_time + duration;
    if (task) task.abort();
    running = true;
    add_render_callback(function () {
      return dispatch(node, true, 'start');
    });
    task = loop(function (now) {
      if (running) {
        if (now >= end_time) {
          tick(1, 0);
          dispatch(node, true, 'end');
          cleanup();
          return running = false;
        }
        if (now >= start_time) {
          var t = easing((now - start_time) / duration);
          tick(t, 1 - t);
        }
      }
      return running;
    });
  }
  var started = false;
  return {
    start: function start() {
      if (started) return;
      started = true;
      delete_rule(node);
      if (is_function(config)) {
        config = config(options);
        wait().then(go);
      } else {
        go();
      }
    },
    invalidate: function invalidate() {
      started = false;
    },
    end: function end() {
      if (running) {
        cleanup();
        running = false;
      }
    }
  };
}
function create_out_transition(node, fn, params) {
  var options = {
    direction: 'out'
  };
  var config = fn(node, params, options);
  var running = true;
  var animation_name;
  var group = outros;
  group.r += 1;
  function go() {
    var _ref5 = config || null_transition,
      _ref5$delay = _ref5.delay,
      delay = _ref5$delay === void 0 ? 0 : _ref5$delay,
      _ref5$duration = _ref5.duration,
      duration = _ref5$duration === void 0 ? 300 : _ref5$duration,
      _ref5$easing = _ref5.easing,
      easing = _ref5$easing === void 0 ? identity : _ref5$easing,
      _ref5$tick = _ref5.tick,
      tick = _ref5$tick === void 0 ? noop : _ref5$tick,
      css = _ref5.css;
    if (css) animation_name = create_rule(node, 1, 0, duration, delay, easing, css);
    var start_time = now() + delay;
    var end_time = start_time + duration;
    add_render_callback(function () {
      return dispatch(node, false, 'start');
    });
    loop(function (now) {
      if (running) {
        if (now >= end_time) {
          tick(0, 1);
          dispatch(node, false, 'end');
          if (! --group.r) {
            // this will result in `end()` being called,
            // so we don't need to clean up here
            run_all(group.c);
          }
          return false;
        }
        if (now >= start_time) {
          var t = easing((now - start_time) / duration);
          tick(1 - t, t);
        }
      }
      return running;
    });
  }
  if (is_function(config)) {
    wait().then(function () {
      // @ts-ignore
      config = config(options);
      go();
    });
  } else {
    go();
  }
  return {
    end: function end(reset) {
      if (reset && config.tick) {
        config.tick(1, 0);
      }
      if (running) {
        if (animation_name) delete_rule(node, animation_name);
        running = false;
      }
    }
  };
}
function create_bidirectional_transition(node, fn, params, intro) {
  var options = {
    direction: 'both'
  };
  var config = fn(node, params, options);
  var t = intro ? 0 : 1;
  var running_program = null;
  var pending_program = null;
  var animation_name = null;
  function clear_animation() {
    if (animation_name) delete_rule(node, animation_name);
  }
  function init(program, duration) {
    var d = program.b - t;
    duration *= Math.abs(d);
    return {
      a: t,
      b: program.b,
      d: d,
      duration: duration,
      start: program.start,
      end: program.start + duration,
      group: program.group
    };
  }
  function go(b) {
    var _ref6 = config || null_transition,
      _ref6$delay = _ref6.delay,
      delay = _ref6$delay === void 0 ? 0 : _ref6$delay,
      _ref6$duration = _ref6.duration,
      duration = _ref6$duration === void 0 ? 300 : _ref6$duration,
      _ref6$easing = _ref6.easing,
      easing = _ref6$easing === void 0 ? identity : _ref6$easing,
      _ref6$tick = _ref6.tick,
      tick = _ref6$tick === void 0 ? noop : _ref6$tick,
      css = _ref6.css;
    var program = {
      start: now() + delay,
      b: b
    };
    if (!b) {
      // @ts-ignore todo: improve typings
      program.group = outros;
      outros.r += 1;
    }
    if (running_program || pending_program) {
      pending_program = program;
    } else {
      // if this is an intro, and there's a delay, we need to do
      // an initial tick and/or apply CSS animation immediately
      if (css) {
        clear_animation();
        animation_name = create_rule(node, t, b, duration, delay, easing, css);
      }
      if (b) tick(0, 1);
      running_program = init(program, duration);
      add_render_callback(function () {
        return dispatch(node, b, 'start');
      });
      loop(function (now) {
        if (pending_program && now > pending_program.start) {
          running_program = init(pending_program, duration);
          pending_program = null;
          dispatch(node, running_program.b, 'start');
          if (css) {
            clear_animation();
            animation_name = create_rule(node, t, running_program.b, running_program.duration, 0, easing, config.css);
          }
        }
        if (running_program) {
          if (now >= running_program.end) {
            tick(t = running_program.b, 1 - t);
            dispatch(node, running_program.b, 'end');
            if (!pending_program) {
              // we're done
              if (running_program.b) {
                // intro — we can tidy up immediately
                clear_animation();
              } else {
                // outro — needs to be coordinated
                if (! --running_program.group.r) run_all(running_program.group.c);
              }
            }
            running_program = null;
          } else if (now >= running_program.start) {
            var p = now - running_program.start;
            t = running_program.a + running_program.d * easing(p / running_program.duration);
            tick(t, 1 - t);
          }
        }
        return !!(running_program || pending_program);
      });
    }
  }
  return {
    run: function run(b) {
      if (is_function(config)) {
        wait().then(function () {
          // @ts-ignore
          config = config(options);
          go(b);
        });
      } else {
        go(b);
      }
    },
    end: function end() {
      clear_animation();
      running_program = pending_program = null;
    }
  };
}
function handle_promise(promise, info) {
  var token = info.token = {};
  function update(type, index, key, value) {
    if (info.token !== token) return;
    info.resolved = value;
    var child_ctx = info.ctx;
    if (key !== undefined) {
      child_ctx = child_ctx.slice();
      child_ctx[key] = value;
    }
    var block = type && (info.current = type)(child_ctx);
    var needs_flush = false;
    if (info.block) {
      if (info.blocks) {
        info.blocks.forEach(function (block, i) {
          if (i !== index && block) {
            group_outros();
            transition_out(block, 1, 1, function () {
              if (info.blocks[i] === block) {
                info.blocks[i] = null;
              }
            });
            check_outros();
          }
        });
      } else {
        info.block.d(1);
      }
      block.c();
      transition_in(block, 1);
      block.m(info.mount(), info.anchor);
      needs_flush = true;
    }
    info.block = block;
    if (info.blocks) info.blocks[index] = block;
    if (needs_flush) {
      flush();
    }
  }
  if (is_promise(promise)) {
    var _current_component = get_current_component();
    promise.then(function (value) {
      set_current_component(_current_component);
      update(info.then, 1, info.value, value);
      set_current_component(null);
    }, function (error) {
      set_current_component(_current_component);
      update(info["catch"], 2, info.error, error);
      set_current_component(null);
      if (!info.hasCatch) {
        throw error;
      }
    });
    // if we previously had a then/catch block, destroy it
    if (info.current !== info.pending) {
      update(info.pending, 0);
      return true;
    }
  } else {
    if (info.current !== info.then) {
      update(info.then, 1, info.value, promise);
      return true;
    }
    info.resolved = promise;
  }
}
function update_await_block_branch(info, ctx, dirty) {
  var child_ctx = ctx.slice();
  var resolved = info.resolved;
  if (info.current === info.then) {
    child_ctx[info.value] = resolved;
  }
  if (info.current === info["catch"]) {
    child_ctx[info.error] = resolved;
  }
  info.block.p(child_ctx, dirty);
}
var globals = typeof window !== 'undefined' ? window : typeof globalThis !== 'undefined' ? globalThis : global;
function destroy_block(block, lookup) {
  block.d(1);
  lookup["delete"](block.key);
}
function outro_and_destroy_block(block, lookup) {
  transition_out(block, 1, 1, function () {
    lookup["delete"](block.key);
  });
}
function fix_and_destroy_block(block, lookup) {
  block.f();
  destroy_block(block, lookup);
}
function fix_and_outro_and_destroy_block(block, lookup) {
  block.f();
  outro_and_destroy_block(block, lookup);
}
function update_keyed_each(old_blocks, dirty, get_key, dynamic, ctx, list, lookup, node, destroy, create_each_block, next, get_context) {
  var o = old_blocks.length;
  var n = list.length;
  var i = o;
  var old_indexes = {};
  while (i--) old_indexes[old_blocks[i].key] = i;
  var new_blocks = [];
  var new_lookup = new Map();
  var deltas = new Map();
  i = n;
  while (i--) {
    var child_ctx = get_context(ctx, list, i);
    var key = get_key(child_ctx);
    var block = lookup.get(key);
    if (!block) {
      block = create_each_block(key, child_ctx);
      block.c();
    } else if (dynamic) {
      block.p(child_ctx, dirty);
    }
    new_lookup.set(key, new_blocks[i] = block);
    if (key in old_indexes) deltas.set(key, Math.abs(i - old_indexes[key]));
  }
  var will_move = new Set();
  var did_move = new Set();
  function insert(block) {
    transition_in(block, 1);
    block.m(node, next);
    lookup.set(block.key, block);
    next = block.first;
    n--;
  }
  while (o && n) {
    var new_block = new_blocks[n - 1];
    var old_block = old_blocks[o - 1];
    var new_key = new_block.key;
    var old_key = old_block.key;
    if (new_block === old_block) {
      // do nothing
      next = new_block.first;
      o--;
      n--;
    } else if (!new_lookup.has(old_key)) {
      // remove old block
      destroy(old_block, lookup);
      o--;
    } else if (!lookup.has(new_key) || will_move.has(new_key)) {
      insert(new_block);
    } else if (did_move.has(old_key)) {
      o--;
    } else if (deltas.get(new_key) > deltas.get(old_key)) {
      did_move.add(new_key);
      insert(new_block);
    } else {
      will_move.add(old_key);
      o--;
    }
  }
  while (o--) {
    var _old_block = old_blocks[o];
    if (!new_lookup.has(_old_block.key)) destroy(_old_block, lookup);
  }
  while (n) insert(new_blocks[n - 1]);
  return new_blocks;
}
function validate_each_keys(ctx, list, get_context, get_key) {
  var keys = new Set();
  for (var i = 0; i < list.length; i++) {
    var key = get_key(get_context(ctx, list, i));
    if (keys.has(key)) {
      throw new Error('Cannot have duplicate keys in a keyed each');
    }
    keys.add(key);
  }
}
function get_spread_update(levels, updates) {
  var update = {};
  var to_null_out = {};
  var accounted_for = {
    $$scope: 1
  };
  var i = levels.length;
  while (i--) {
    var o = levels[i];
    var n = updates[i];
    if (n) {
      for (var key in o) {
        if (!(key in n)) to_null_out[key] = 1;
      }
      for (var _key3 in n) {
        if (!accounted_for[_key3]) {
          update[_key3] = n[_key3];
          accounted_for[_key3] = 1;
        }
      }
      levels[i] = n;
    } else {
      for (var _key4 in o) {
        accounted_for[_key4] = 1;
      }
    }
  }
  for (var _key5 in to_null_out) {
    if (!(_key5 in update)) update[_key5] = undefined;
  }
  return update;
}
function get_spread_object(spread_props) {
  return _typeof(spread_props) === 'object' && spread_props !== null ? spread_props : {};
}

// source: https://html.spec.whatwg.org/multipage/indices.html
var boolean_attributes = new Set(['allowfullscreen', 'allowpaymentrequest', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default', 'defer', 'disabled', 'formnovalidate', 'hidden', 'inert', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'playsinline', 'readonly', 'required', 'reversed', 'selected']);

/** regex of all html void element names */
var void_element_names = /^(?:area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr)$/;
function is_void(name) {
  return void_element_names.test(name) || name.toLowerCase() === '!doctype';
}
var invalid_attribute_name_character = /(?:[\t-\r "'\/=>\xA0\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000\uFDD0-\uFDEF\uFEFF\uFFFE\uFFFF]|[\uD83F\uD87F\uD8BF\uD8FF\uD93F\uD97F\uD9BF\uD9FF\uDA3F\uDA7F\uDABF\uDAFF\uDB3F\uDB7F\uDBBF\uDBFF][\uDFFE\uDFFF])/;
// https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
// https://infra.spec.whatwg.org/#noncharacter
function spread(args, attrs_to_add) {
  var attributes = Object.assign.apply(Object, [{}].concat(_toConsumableArray(args)));
  if (attrs_to_add) {
    var classes_to_add = attrs_to_add.classes;
    var styles_to_add = attrs_to_add.styles;
    if (classes_to_add) {
      if (attributes["class"] == null) {
        attributes["class"] = classes_to_add;
      } else {
        attributes["class"] += ' ' + classes_to_add;
      }
    }
    if (styles_to_add) {
      if (attributes.style == null) {
        attributes.style = style_object_to_string(styles_to_add);
      } else {
        attributes.style = style_object_to_string(merge_ssr_styles(attributes.style, styles_to_add));
      }
    }
  }
  var str = '';
  Object.keys(attributes).forEach(function (name) {
    if (invalid_attribute_name_character.test(name)) return;
    var value = attributes[name];
    if (value === true) str += ' ' + name;else if (boolean_attributes.has(name.toLowerCase())) {
      if (value) str += ' ' + name;
    } else if (value != null) {
      str += " ".concat(name, "=\"").concat(value, "\"");
    }
  });
  return str;
}
function merge_ssr_styles(style_attribute, style_directive) {
  var style_object = {};
  var _iterator4 = _createForOfIteratorHelper(style_attribute.split(';')),
    _step4;
  try {
    for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
      var individual_style = _step4.value;
      var colon_index = individual_style.indexOf(':');
      var _name = individual_style.slice(0, colon_index).trim();
      var _value = individual_style.slice(colon_index + 1).trim();
      if (!_name) continue;
      style_object[_name] = _value;
    }
  } catch (err) {
    _iterator4.e(err);
  } finally {
    _iterator4.f();
  }
  for (var name in style_directive) {
    var value = style_directive[name];
    if (value) {
      style_object[name] = value;
    } else {
      delete style_object[name];
    }
  }
  return style_object;
}
var ATTR_REGEX = /[&"]/g;
var CONTENT_REGEX = /[&<]/g;
/**
 * Note: this method is performance sensitive and has been optimized
 * https://github.com/sveltejs/svelte/pull/5701
 */
function escape(value) {
  var is_attr = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  var str = String(value);
  var pattern = is_attr ? ATTR_REGEX : CONTENT_REGEX;
  pattern.lastIndex = 0;
  var escaped = '';
  var last = 0;
  while (pattern.test(str)) {
    var i = pattern.lastIndex - 1;
    var ch = str[i];
    escaped += str.substring(last, i) + (ch === '&' ? '&amp;' : ch === '"' ? '&quot;' : '&lt;');
    last = i + 1;
  }
  return escaped + str.substring(last);
}
function escape_attribute_value(value) {
  // keep booleans, null, and undefined for the sake of `spread`
  var should_escape = typeof value === 'string' || value && _typeof(value) === 'object';
  return should_escape ? escape(value, true) : value;
}
function escape_object(obj) {
  var result = {};
  for (var key in obj) {
    result[key] = escape_attribute_value(obj[key]);
  }
  return result;
}
function each(items, fn) {
  var str = '';
  for (var i = 0; i < items.length; i += 1) {
    str += fn(items[i], i);
  }
  return str;
}
var missing_component = {
  $$render: function $$render() {
    return '';
  }
};
function validate_component(component, name) {
  if (!component || !component.$$render) {
    if (name === 'svelte:component') name += ' this={...}';
    throw new Error("<".concat(name, "> is not a valid SSR component. You may need to review your build config to ensure that dependencies are compiled, rather than imported as pre-compiled modules. Otherwise you may need to fix a <").concat(name, ">."));
  }
  return component;
}
function debug(file, line, column, values) {
  console.log("{@debug} ".concat(file ? file + ' ' : '', "(").concat(line, ":").concat(column, ")")); // eslint-disable-line no-console
  console.log(values); // eslint-disable-line no-console
  return '';
}
var on_destroy;
function create_ssr_component(fn) {
  function $$render(result, props, bindings, slots, context) {
    var parent_component = current_component;
    var $$ = {
      on_destroy: on_destroy,
      context: new Map(context || (parent_component ? parent_component.$$.context : [])),
      // these will be immediately discarded
      on_mount: [],
      before_update: [],
      after_update: [],
      callbacks: blank_object()
    };
    set_current_component({
      $$: $$
    });
    var html = fn(result, props, bindings, slots);
    set_current_component(parent_component);
    return html;
  }
  return {
    render: function render() {
      var props = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      var _ref7 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
        _ref7$$$slots = _ref7.$$slots,
        $$slots = _ref7$$$slots === void 0 ? {} : _ref7$$$slots,
        _ref7$context = _ref7.context,
        context = _ref7$context === void 0 ? new Map() : _ref7$context;
      on_destroy = [];
      var result = {
        title: '',
        head: '',
        css: new Set()
      };
      var html = $$render(result, props, {}, $$slots, context);
      run_all(on_destroy);
      return {
        html: html,
        css: {
          code: Array.from(result.css).map(function (css) {
            return css.code;
          }).join('\n'),
          map: null // TODO
        },

        head: result.title + result.head
      };
    },
    $$render: $$render
  };
}
function add_attribute(name, value, _boolean) {
  if (value == null || _boolean && !value) return '';
  var assignment = _boolean && value === true ? '' : "=\"".concat(escape(value, true), "\"");
  return " ".concat(name).concat(assignment);
}
function add_classes(classes) {
  return classes ? " class=\"".concat(classes, "\"") : '';
}
function style_object_to_string(style_object) {
  return Object.keys(style_object).filter(function (key) {
    return style_object[key];
  }).map(function (key) {
    return "".concat(key, ": ").concat(escape_attribute_value(style_object[key]), ";");
  }).join(' ');
}
function add_styles(style_object) {
  var styles = style_object_to_string(style_object);
  return styles ? " style=\"".concat(styles, "\"") : '';
}
function bind(component, name, callback) {
  var index = component.$$.props[name];
  if (index !== undefined) {
    component.$$.bound[index] = callback;
    callback(component.$$.ctx[index]);
  }
}
function create_component(block) {
  block && block.c();
}
function claim_component(block, parent_nodes) {
  block && block.l(parent_nodes);
}
function mount_component(component, target, anchor, customElement) {
  var _component$$$ = component.$$,
    fragment = _component$$$.fragment,
    after_update = _component$$$.after_update;
  fragment && fragment.m(target, anchor);
  if (!customElement) {
    // onMount happens before the initial afterUpdate
    add_render_callback(function () {
      var new_on_destroy = component.$$.on_mount.map(run).filter(is_function);
      // if the component was destroyed immediately
      // it will update the `$$.on_destroy` reference to `null`.
      // the destructured on_destroy may still reference to the old array
      if (component.$$.on_destroy) {
        var _component$$$$on_dest;
        (_component$$$$on_dest = component.$$.on_destroy).push.apply(_component$$$$on_dest, _toConsumableArray(new_on_destroy));
      } else {
        // Edge case - component was destroyed immediately,
        // most likely as a result of a binding initialising
        run_all(new_on_destroy);
      }
      component.$$.on_mount = [];
    });
  }
  after_update.forEach(add_render_callback);
}
function destroy_component(component, detaching) {
  var $$ = component.$$;
  if ($$.fragment !== null) {
    run_all($$.on_destroy);
    $$.fragment && $$.fragment.d(detaching);
    // TODO null out other refs, including component.$$ (but need to
    // preserve final state?)
    $$.on_destroy = $$.fragment = null;
    $$.ctx = [];
  }
}
function make_dirty(component, i) {
  if (component.$$.dirty[0] === -1) {
    dirty_components.push(component);
    schedule_update();
    component.$$.dirty.fill(0);
  }
  component.$$.dirty[i / 31 | 0] |= 1 << i % 31;
}
function init(component, options, instance, create_fragment, not_equal, props, append_styles) {
  var dirty = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : [-1];
  var parent_component = current_component;
  set_current_component(component);
  var $$ = component.$$ = {
    fragment: null,
    ctx: [],
    // state
    props: props,
    update: noop,
    not_equal: not_equal,
    bound: blank_object(),
    // lifecycle
    on_mount: [],
    on_destroy: [],
    on_disconnect: [],
    before_update: [],
    after_update: [],
    context: new Map(options.context || (parent_component ? parent_component.$$.context : [])),
    // everything else
    callbacks: blank_object(),
    dirty: dirty,
    skip_bound: false,
    root: options.target || parent_component.$$.root
  };
  append_styles && append_styles($$.root);
  var ready = false;
  $$.ctx = instance ? instance(component, options.props || {}, function (i, ret) {
    var value = (arguments.length <= 2 ? 0 : arguments.length - 2) ? arguments.length <= 2 ? undefined : arguments[2] : ret;
    if ($$.ctx && not_equal($$.ctx[i], $$.ctx[i] = value)) {
      if (!$$.skip_bound && $$.bound[i]) $$.bound[i](value);
      if (ready) make_dirty(component, i);
    }
    return ret;
  }) : [];
  $$.update();
  ready = true;
  run_all($$.before_update);
  // `false` as a special case of no DOM component
  $$.fragment = create_fragment ? create_fragment($$.ctx) : false;
  if (options.target) {
    if (options.hydrate) {
      start_hydrating();
      var nodes = children(options.target);
      // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
      $$.fragment && $$.fragment.l(nodes);
      nodes.forEach(detach);
    } else {
      // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
      $$.fragment && $$.fragment.c();
    }
    if (options.intro) transition_in(component.$$.fragment);
    mount_component(component, options.target, options.anchor, options.customElement);
    end_hydrating();
    flush();
  }
  set_current_component(parent_component);
}
var SvelteElement;
if (typeof HTMLElement === 'function') {
  SvelteElement = /*#__PURE__*/function (_HTMLElement) {
    _inherits(SvelteElement, _HTMLElement);
    var _super2 = _createSuper(SvelteElement);
    function SvelteElement() {
      var _this3;
      _classCallCheck(this, SvelteElement);
      _this3 = _super2.call(this);
      _this3.attachShadow({
        mode: 'open'
      });
      return _this3;
    }
    _createClass(SvelteElement, [{
      key: "connectedCallback",
      value: function connectedCallback() {
        var on_mount = this.$$.on_mount;
        this.$$.on_disconnect = on_mount.map(run).filter(is_function);
        // @ts-ignore todo: improve typings
        for (var key in this.$$.slotted) {
          // @ts-ignore todo: improve typings
          this.appendChild(this.$$.slotted[key]);
        }
      }
    }, {
      key: "attributeChangedCallback",
      value: function attributeChangedCallback(attr, _oldValue, newValue) {
        this[attr] = newValue;
      }
    }, {
      key: "disconnectedCallback",
      value: function disconnectedCallback() {
        run_all(this.$$.on_disconnect);
      }
    }, {
      key: "$destroy",
      value: function $destroy() {
        destroy_component(this, 1);
        this.$destroy = noop;
      }
    }, {
      key: "$on",
      value: function $on(type, callback) {
        // TODO should this delegate to addEventListener?
        if (!is_function(callback)) {
          return noop;
        }
        var callbacks = this.$$.callbacks[type] || (this.$$.callbacks[type] = []);
        callbacks.push(callback);
        return function () {
          var index = callbacks.indexOf(callback);
          if (index !== -1) callbacks.splice(index, 1);
        };
      }
    }, {
      key: "$set",
      value: function $set($$props) {
        if (this.$$set && !is_empty($$props)) {
          this.$$.skip_bound = true;
          this.$$set($$props);
          this.$$.skip_bound = false;
        }
      }
    }]);
    return SvelteElement;
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));
}
/**
 * Base class for Svelte components. Used when dev=false.
 */
var SvelteComponent = /*#__PURE__*/function () {
  function SvelteComponent() {
    _classCallCheck(this, SvelteComponent);
  }
  _createClass(SvelteComponent, [{
    key: "$destroy",
    value: function $destroy() {
      destroy_component(this, 1);
      this.$destroy = noop;
    }
  }, {
    key: "$on",
    value: function $on(type, callback) {
      if (!is_function(callback)) {
        return noop;
      }
      var callbacks = this.$$.callbacks[type] || (this.$$.callbacks[type] = []);
      callbacks.push(callback);
      return function () {
        var index = callbacks.indexOf(callback);
        if (index !== -1) callbacks.splice(index, 1);
      };
    }
  }, {
    key: "$set",
    value: function $set($$props) {
      if (this.$$set && !is_empty($$props)) {
        this.$$.skip_bound = true;
        this.$$set($$props);
        this.$$.skip_bound = false;
      }
    }
  }]);
  return SvelteComponent;
}();
function dispatch_dev(type, detail) {
  document.dispatchEvent(custom_event(type, Object.assign({
    version: '3.55.1'
  }, detail), {
    bubbles: true
  }));
}
function append_dev(target, node) {
  dispatch_dev('SvelteDOMInsert', {
    target: target,
    node: node
  });
  append(target, node);
}
function append_hydration_dev(target, node) {
  dispatch_dev('SvelteDOMInsert', {
    target: target,
    node: node
  });
  append_hydration(target, node);
}
function insert_dev(target, node, anchor) {
  dispatch_dev('SvelteDOMInsert', {
    target: target,
    node: node,
    anchor: anchor
  });
  insert(target, node, anchor);
}
function insert_hydration_dev(target, node, anchor) {
  dispatch_dev('SvelteDOMInsert', {
    target: target,
    node: node,
    anchor: anchor
  });
  insert_hydration(target, node, anchor);
}
function detach_dev(node) {
  dispatch_dev('SvelteDOMRemove', {
    node: node
  });
  detach(node);
}
function detach_between_dev(before, after) {
  while (before.nextSibling && before.nextSibling !== after) {
    detach_dev(before.nextSibling);
  }
}
function detach_before_dev(after) {
  while (after.previousSibling) {
    detach_dev(after.previousSibling);
  }
}
function detach_after_dev(before) {
  while (before.nextSibling) {
    detach_dev(before.nextSibling);
  }
}
function listen_dev(node, event, handler, options, has_prevent_default, has_stop_propagation) {
  var modifiers = options === true ? ['capture'] : options ? Array.from(Object.keys(options)) : [];
  if (has_prevent_default) modifiers.push('preventDefault');
  if (has_stop_propagation) modifiers.push('stopPropagation');
  dispatch_dev('SvelteDOMAddEventListener', {
    node: node,
    event: event,
    handler: handler,
    modifiers: modifiers
  });
  var dispose = listen(node, event, handler, options);
  return function () {
    dispatch_dev('SvelteDOMRemoveEventListener', {
      node: node,
      event: event,
      handler: handler,
      modifiers: modifiers
    });
    dispose();
  };
}
function attr_dev(node, attribute, value) {
  attr(node, attribute, value);
  if (value == null) dispatch_dev('SvelteDOMRemoveAttribute', {
    node: node,
    attribute: attribute
  });else dispatch_dev('SvelteDOMSetAttribute', {
    node: node,
    attribute: attribute,
    value: value
  });
}
function prop_dev(node, property, value) {
  node[property] = value;
  dispatch_dev('SvelteDOMSetProperty', {
    node: node,
    property: property,
    value: value
  });
}
function dataset_dev(node, property, value) {
  node.dataset[property] = value;
  dispatch_dev('SvelteDOMSetDataset', {
    node: node,
    property: property,
    value: value
  });
}
function set_data_dev(text, data) {
  data = '' + data;
  if (text.wholeText === data) return;
  dispatch_dev('SvelteDOMSetData', {
    node: text,
    data: data
  });
  text.data = data;
}
function validate_each_argument(arg) {
  if (typeof arg !== 'string' && !(arg && _typeof(arg) === 'object' && 'length' in arg)) {
    var msg = '{#each} only iterates over array-like objects.';
    if (typeof Symbol === 'function' && arg && Symbol.iterator in arg) {
      msg += ' You can use a spread to convert this iterable into an array.';
    }
    throw new Error(msg);
  }
}
function validate_slots(name, slot, keys) {
  for (var _i4 = 0, _Object$keys = Object.keys(slot); _i4 < _Object$keys.length; _i4++) {
    var slot_key = _Object$keys[_i4];
    if (!~keys.indexOf(slot_key)) {
      console.warn("<".concat(name, "> received an unexpected slot \"").concat(slot_key, "\"."));
    }
  }
}
function validate_dynamic_element(tag) {
  var is_string = typeof tag === 'string';
  if (tag && !is_string) {
    throw new Error('<svelte:element> expects "this" attribute to be a string.');
  }
}
function validate_void_dynamic_element(tag) {
  if (tag && is_void(tag)) {
    console.warn("<svelte:element this=\"".concat(tag, "\"> is self-closing and cannot have content."));
  }
}
function construct_svelte_component_dev(component, props) {
  var error_message = 'this={...} of <svelte:component> should specify a Svelte component.';
  try {
    var instance = new component(props);
    if (!instance.$$ || !instance.$set || !instance.$on || !instance.$destroy) {
      throw new Error(error_message);
    }
    return instance;
  } catch (err) {
    var message = err.message;
    if (typeof message === 'string' && message.indexOf('is not a constructor') !== -1) {
      throw new Error(error_message);
    } else {
      throw err;
    }
  }
}
/**
 * Base class for Svelte components with some minor dev-enhancements. Used when dev=true.
 */
var SvelteComponentDev = /*#__PURE__*/function (_SvelteComponent) {
  _inherits(SvelteComponentDev, _SvelteComponent);
  var _super3 = _createSuper(SvelteComponentDev);
  function SvelteComponentDev(options) {
    _classCallCheck(this, SvelteComponentDev);
    if (!options || !options.target && !options.$$inline) {
      throw new Error("'target' is a required option");
    }
    return _super3.call(this);
  }
  _createClass(SvelteComponentDev, [{
    key: "$destroy",
    value: function $destroy() {
      _get(_getPrototypeOf(SvelteComponentDev.prototype), "$destroy", this).call(this);
      this.$destroy = function () {
        console.warn('Component was already destroyed'); // eslint-disable-line no-console
      };
    }
  }, {
    key: "$capture_state",
    value: function $capture_state() {}
  }, {
    key: "$inject_state",
    value: function $inject_state() {}
  }]);
  return SvelteComponentDev;
}(SvelteComponent);
/**
 * Base class to create strongly typed Svelte components.
 * This only exists for typing purposes and should be used in `.d.ts` files.
 *
 * ### Example:
 *
 * You have component library on npm called `component-library`, from which
 * you export a component called `MyComponent`. For Svelte+TypeScript users,
 * you want to provide typings. Therefore you create a `index.d.ts`:
 * ```ts
 * import { SvelteComponentTyped } from "svelte";
 * export class MyComponent extends SvelteComponentTyped<{foo: string}> {}
 * ```
 * Typing this makes it possible for IDEs like VS Code with the Svelte extension
 * to provide intellisense and to use the component like this in a Svelte file
 * with TypeScript:
 * ```svelte
 * <script lang="ts">
 * 	import { MyComponent } from "component-library";
 * </script>
 * <MyComponent foo={'bar'} />
 * ```
 *
 * #### Why not make this part of `SvelteComponent(Dev)`?
 * Because
 * ```ts
 * class ASubclassOfSvelteComponent extends SvelteComponent<{foo: string}> {}
 * const component: typeof SvelteComponent = ASubclassOfSvelteComponent;
 * ```
 * will throw a type error, so we need to separate the more strictly typed class.
 */
var SvelteComponentTyped = /*#__PURE__*/function (_SvelteComponentDev) {
  _inherits(SvelteComponentTyped, _SvelteComponentDev);
  var _super4 = _createSuper(SvelteComponentTyped);
  function SvelteComponentTyped(options) {
    _classCallCheck(this, SvelteComponentTyped);
    return _super4.call(this, options);
  }
  return _createClass(SvelteComponentTyped);
}(SvelteComponentDev);
function loop_guard(timeout) {
  var start = Date.now();
  return function () {
    if (Date.now() - start > timeout) {
      throw new Error('Infinite loop detected');
    }
  };
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
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/nonce */
/******/ 	(() => {
/******/ 		__webpack_require__.nc = undefined;
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*********************************!*\
  !*** ./resources/public/app.js ***!
  \*********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BookingApp_svelte__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BookingApp.svelte */ "./resources/public/BookingApp.svelte");
/* harmony import */ var _styles_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles.scss */ "./resources/public/styles.scss");


var calendarApps = document.querySelectorAll('.fluent_booking_app');
if (calendarApps.length) {
  calendarApps.forEach(function (item, index) {
    var elem = calendarApps[index];
    var calendarId = elem.dataset.calendar_id;
    var slot_id = elem.dataset.event_id;
    if (calendarId && event_id) {
      elem.innerHTML = '';
      new _BookingApp_svelte__WEBPACK_IMPORTED_MODULE_0__["default"]({
        target: elem,
        props: {
          appData: window['fcal_public_vars_' + calendarId]
        }
      });
    }
  });
}
})();

/******/ })()
;
