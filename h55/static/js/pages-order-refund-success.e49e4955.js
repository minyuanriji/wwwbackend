(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-order-refund-success"],{"24bf":function(n,t,e){var i=e("24fb");t=i(!1),t.push([n.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.app[data-v-647be45c]{background-color:#f7f7f7;height:100%;padding:0 %?30?%}.app .container[data-v-647be45c]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:9pt;height:50%}.app .container .img-msg[data-v-647be45c]{margin-bottom:%?50?%}.app .container .img-msg .img[data-v-647be45c]{width:%?187?%;height:%?187?%;margin-bottom:%?35?%}.app .container .img-msg .msg[data-v-647be45c]{color:grey}.app .container .btn[data-v-647be45c]{width:100%;height:%?90?%;background-color:#bc0100;color:#fff;text-align:center;line-height:%?90?%;border-radius:%?45?%}',""]),n.exports=t},"46be":function(n,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i={data:function(){return{img_url:this.$api.img_url}},onLoad:function(){},methods:{goIndex:function(){uni.redirectTo({url:"../list"})}}};t.default=i},"4ef3":function(n,t,e){var i=e("24bf");"string"===typeof i&&(i=[[n.i,i,""]]),i.locals&&(n.exports=i.locals);var a=e("4f06").default;a("d60faad8",i,!0,{sourceMap:!1,shadowMode:!1})},"6ebe":function(n,t,e){"use strict";e.r(t);var i=e("46be"),a=e.n(i);for(var r in i)"default"!==r&&function(n){e.d(t,n,(function(){return i[n]}))}(r);t["default"]=a.a},"7eef":function(n,t,e){"use strict";e.r(t);var i=e("f91b"),a=e("6ebe");for(var r in a)"default"!==r&&function(n){e.d(t,n,(function(){return a[n]}))}(r);e("bce4");var c,o=e("f0c5"),s=Object(o["a"])(a["default"],i["b"],i["c"],!1,null,"647be45c",null,!1,i["a"],c);t["default"]=s.exports},bce4:function(n,t,e){"use strict";var i=e("4ef3"),a=e.n(i);a.a},f91b:function(n,t,e){"use strict";var i;e.d(t,"b",(function(){return a})),e.d(t,"c",(function(){return r})),e.d(t,"a",(function(){return i}));var a=function(){var n=this,t=n.$createElement,e=n._self._c||t;return e("v-uni-view",{staticClass:"app"},[e("v-uni-view",{staticClass:"container"},[e("v-uni-view",{staticClass:"img-msg flex-col flex-y-center"},[e("v-uni-image",{staticClass:"img",attrs:{src:n.img_url+"images/order/order-nothing.png",mode:""}}),e("v-uni-view",{staticClass:"msg"},[n._v("已收到您的售后退款信息，请耐心等待商家审核~")])],1),e("v-uni-view",{staticClass:"btn",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.goIndex.apply(void 0,arguments)}}},[n._v("返回首页")])],1)],1)},r=[]}}]);