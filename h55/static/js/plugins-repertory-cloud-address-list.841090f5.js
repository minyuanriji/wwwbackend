(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["plugins-repertory-cloud-address-list"],{"053f":function(e,t,i){var n=i("8139");"string"===typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);var a=i("4f06").default;a("0533a19e",n,!0,{sourceMap:!1,shadowMode:!1})},"0fdd":function(e,t,i){"use strict";i("99af"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={data:function(){return{manageType:"",loading:!1,dataList:[]}},onLoad:function(e){var t=e.type;this.manageType=t+""},methods:{chooseAddress:function(e){if("click"==this.manageType)return uni.setStorageSync("_temp_address","111"),void uni.navigateBack()},edit:function(e,t){uni.navigateTo({url:"/plugins/repertory-cloud/address/edit?type=".concat(e,"&id=").concat(t)})}}};t.default=n},"141e":function(e,t,i){"use strict";i.r(t);var n=i("918e"),a=i("1e70");for(var o in a)"default"!==o&&function(e){i.d(t,e,(function(){return a[e]}))}(o);i("6164");var r,d=i("f0c5"),c=Object(d["a"])(a["default"],n["b"],n["c"],!1,null,"85db7258",null,!1,n["a"],r);t["default"]=c.exports},"1e70":function(e,t,i){"use strict";i.r(t);var n=i("d2fd"),a=i.n(n);for(var o in n)"default"!==o&&function(e){i.d(t,e,(function(){return n[e]}))}(o);t["default"]=a.a},6164:function(e,t,i){"use strict";var n=i("053f"),a=i.n(n);a.a},"671d8":function(e,t,i){"use strict";i.d(t,"b",(function(){return a})),i.d(t,"c",(function(){return o})),i.d(t,"a",(function(){return n}));var n={comNavBar:i("deaf").default,mainLoading:i("141e").default},a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"app"},[n("com-nav-bar",{attrs:{"left-icon":"back",title:"发货地址","status-bar":!0,"background-color":"#BC0100",border:!1,color:"#ffffff"},on:{clickLeft:function(t){arguments[0]=t=e.$handleEvent(t),e.back.apply(void 0,arguments)}}}),n("v-uni-view",{staticClass:"container"},[n("v-uni-view",{staticClass:"list"},e._l(9,(function(t,a){return n("v-uni-view",{key:a,staticClass:"item flex flex-x-between flex-y-center",on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.chooseAddress(t)}}},[n("v-uni-view",{staticClass:"item-info"},[n("v-uni-view",{staticClass:"address-detail over1"},[e._v(e._s("广东省广州市白云区江夏村松猫岭街6号"))]),n("v-uni-view",{staticClass:"user flex"},[n("v-uni-view",{staticClass:"user-name"},[e._v(e._s("梁瑞文(女士)"))]),n("v-uni-view",{staticClass:"user-phone"},[e._v(e._s("18666666891"))])],1)],1),n("v-uni-view",{staticClass:"edit",on:{click:function(t){t.stopPropagation(),arguments[0]=t=e.$handleEvent(t),e.edit("edit","1")}}},[n("v-uni-image",{staticClass:"img-edit",attrs:{src:i("6a57")}})],1)],1)})),1),n("v-uni-view",{staticClass:"fixed flex flex-x-center flex-y-center"},[n("v-uni-view",{staticClass:"btn add flex-grow-1",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.edit("add")}}},[e._v("添加收货人")])],1)],1),n("main-loading",{attrs:{visible:e.loading}})],1)},o=[]},"6a57":function(e,t){e.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACtWK6eAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQ4IDc5LjE2NDAzNiwgMjAxOS8wOC8xMy0wMTowNjo1NyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjAgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjY0OUFCNzlDRDVGNjExRUE5M0ZCQjZCRUJBNThGNDE3IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjY0OUFCNzlERDVGNjExRUE5M0ZCQjZCRUJBNThGNDE3Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NjQ5QUI3OUFENUY2MTFFQTkzRkJCNkJFQkE1OEY0MTciIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NjQ5QUI3OUJENUY2MTFFQTkzRkJCNkJFQkE1OEY0MTciLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5i3391AAAI/0lEQVR42uzdbYgU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"},8139:function(e,t,i){var n=i("24fb");t=n(!1),t.push([e.i,".jx-loading-init[data-v-85db7258]{min-width:%?200?%;min-height:%?200?%;max-width:%?500?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;position:fixed;top:50%;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);z-index:9999;font-size:%?26?%;color:#fff;background:rgba(0,0,0,.7);border-radius:%?10?%}.jx-loading-center[data-v-85db7258]{width:%?50?%;height:%?50?%;border:3px solid #fff;border-radius:50%;margin:0 6px;display:inline-block;vertical-align:middle;-webkit-clip-path:polygon(0 0,100% 0,100% 40%,0 40%);clip-path:polygon(0 0,100% 0,100% 40%,0 40%);-webkit-animation:rotate-data-v-85db7258 1s linear infinite;animation:rotate-data-v-85db7258 1s linear infinite;margin-bottom:%?36?%}.jx-loadmore-tips[data-v-85db7258]{text-align:center;padding:0 %?20?%;box-sizing:border-box}@-webkit-keyframes rotate-data-v-85db7258{from{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes rotate-data-v-85db7258{from{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}",""]),e.exports=t},"918e":function(e,t,i){"use strict";var n;i.d(t,"b",(function(){return a})),i.d(t,"c",(function(){return o})),i.d(t,"a",(function(){return n}));var a=function(){var e=this,t=e.$createElement,i=e._self._c||t;return e.visible?i("v-uni-view",{staticClass:"jx-loading-init"},[i("v-uni-view",{staticClass:"jx-loading-center"}),i("v-uni-view",{staticClass:"jx-loadmore-tips"},[e._v(e._s(e.text))])],1):e._e()},o=[]},9885:function(e,t,i){var n=i("e2a7");"string"===typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);var a=i("4f06").default;a("bb58376a",n,!0,{sourceMap:!1,shadowMode:!1})},d2fd:function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={name:"jxLoading",props:{text:{type:String,default:"正在加载..."},visible:{type:Boolean,default:!1}}};t.default=n},ddad:function(e,t,i){"use strict";i.r(t);var n=i("0fdd"),a=i.n(n);for(var o in n)"default"!==o&&function(e){i.d(t,e,(function(){return n[e]}))}(o);t["default"]=a.a},e2a7:function(e,t,i){var n=i("24fb");t=n(!1),t.push([e.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.app[data-v-73eea2e5]{line-height:100%;color:#f7f7f7}.container[data-v-73eea2e5]{position:relative;margin-top:%?20?%;background-color:#fff;height:100%}.container .item[data-v-73eea2e5]{padding:%?32?% %?30?% %?22?%;border-bottom:%?1?% solid #f2f2f2}.container .item .item-info[data-v-73eea2e5]{color:#000;font-size:%?36?%;line-height:%?40?%}.container .item .item-info .address-detail[data-v-73eea2e5]{margin-bottom:%?26?%;max-width:%?570?%}.container .item .item-info .user[data-v-73eea2e5]{color:#999;font-size:%?28?%}.container .item .item-info .user-name[data-v-73eea2e5]{margin-right:%?24?%}.container .item .edit[data-v-73eea2e5]{width:%?44?%;height:%?42?%;z-index:3}.container .item .edit .img-edit[data-v-73eea2e5]{width:100%;height:100%}.fixed[data-v-73eea2e5]{position:fixed;left:0;right:0;bottom:0;height:%?150?%;padding:0 %?29?%;background-color:#fff;border-top:%?1?% solid #f2f2f2;z-index:3}.fixed .btn[data-v-73eea2e5]{height:%?90?%;line-height:%?90?%;font-size:%?26?%;text-align:center;border-radius:%?45?%}.fixed .btn.add[data-v-73eea2e5]{background-color:#bc0100}',""]),e.exports=t},e469:function(e,t,i){"use strict";i.r(t);var n=i("671d8"),a=i("ddad");for(var o in a)"default"!==o&&function(e){i.d(t,e,(function(){return a[e]}))}(o);i("fd71");var r,d=i("f0c5"),c=Object(d["a"])(a["default"],n["b"],n["c"],!1,null,"73eea2e5",null,!1,n["a"],r);t["default"]=c.exports},fd71:function(e,t,i){"use strict";var n=i("9885"),a=i.n(n);a.a}}]);