(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["plugins-extensions-poster"],{"18c0":function(t,e,n){"use strict";n.r(e);var i=n("6cad"),o=n("911f");for(var a in o)"default"!==a&&function(t){n.d(e,t,(function(){return o[t]}))}(a);n("7706");var r,s=n("f0c5"),c=Object(s["a"])(o["default"],i["b"],i["c"],!1,null,"2cfea040",null,!1,i["a"],r);e["default"]=c.exports},"6cad":function(t,e,n){"use strict";var i;n.d(e,"b",(function(){return o})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){return i}));var o=function(){var t=this,e=t.$createElement,n=t._self._c||e;return t.visible?n("v-uni-view",{staticClass:"jx-loading-init"},[n("v-uni-view",{staticClass:"jx-loading-center"}),n("v-uni-view",{staticClass:"jx-loadmore-tips"},[t._v(t._s(t.text))])],1):t._e()},a=[]},7706:function(t,e,n){"use strict";var i=n("d1fe"),o=n.n(i);o.a},"911f":function(t,e,n){"use strict";n.r(e);var i=n("cb3b"),o=n.n(i);for(var a in i)"default"!==a&&function(t){n.d(e,t,(function(){return i[t]}))}(a);e["default"]=o.a},a906:function(t,e,n){var i=n("24fb");e=i(!1),e.push([t.i,".jx-loading-init[data-v-2cfea040]{min-width:%?200?%;min-height:%?200?%;max-width:%?500?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;position:fixed;top:50%;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);z-index:9999;font-size:%?26?%;color:#fff;background:rgba(0,0,0,.7);border-radius:%?10?%}.jx-loading-center[data-v-2cfea040]{width:%?50?%;height:%?50?%;border:3px solid #fff;border-radius:50%;margin:0 6px;display:inline-block;vertical-align:middle;-webkit-clip-path:polygon(0 0,100% 0,100% 40%,0 40%);clip-path:polygon(0 0,100% 0,100% 40%,0 40%);-webkit-animation:rotate-data-v-2cfea040 1s linear infinite;animation:rotate-data-v-2cfea040 1s linear infinite;margin-bottom:%?36?%}.jx-loadmore-tips[data-v-2cfea040]{text-align:center;padding:0 %?20?%;box-sizing:border-box}@-webkit-keyframes rotate-data-v-2cfea040{from{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes rotate-data-v-2cfea040{from{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}",""]),t.exports=e},bef5:function(t,e,n){"use strict";n.r(e);var i=n("cd7d"),o=n.n(i);for(var a in i)"default"!==a&&function(t){n.d(e,t,(function(){return i[t]}))}(a);e["default"]=o.a},bfd5:function(t,e,n){"use strict";n.r(e);var i=n("e5bc"),o=n("bef5");for(var a in o)"default"!==a&&function(t){n.d(e,t,(function(){return o[t]}))}(a);n("fa2b");var r,s=n("f0c5"),c=Object(s["a"])(o["default"],i["b"],i["c"],!1,null,"8cfd1278",null,!1,i["a"],r);e["default"]=c.exports},cb3b:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={name:"jxLoading",props:{text:{type:String,default:"正在加载..."},visible:{type:Boolean,default:!1}}};e.default=i},cd7d:function(t,e,n){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={data:function(){return{poster_url:"",loading:!1}},onLoad:function(){this.getData()},methods:{saveImage:function(t){var e=this;uni.authorize({scope:"scope.writePhotosAlbum",success:function(){e.saveImg(t)},complete:function(t){uni.getSetting({success:function(t){t.authSetting["scope.writePhotosAlbum"]||e.opensit()}})}})},opensit:function(){uni.showModal({content:"由于您还没有允许保存图片到您相册里,请点击确定去允许授权",success:function(e){e.confirm?uni.openSetting({success:function(e){t("log",e.authSetting," at plugins/extensions/poster.vue:72")}}):e.cancel&&uni.showModal({cancelText:"依然取消",confirmText:"重新授权",content:"很遗憾你点击了取消，请慎重考虑",success:function(e){e.confirm?uni.openSetting({success:function(e){t("log",e.authSetting," at plugins/extensions/poster.vue:84")}}):e.cancel&&t("log","用户不授权"," at plugins/extensions/poster.vue:88")}})}})},saveImg:function(e){uni.getImageInfo({src:e,success:function(e){uni.saveImageToPhotosAlbum({filePath:e.path,success:function(){t("log","save success"," at plugins/extensions/poster.vue:105")},complete:function(e){t("log",e," at plugins/extensions/poster.vue:108")}})}})},appSaveImg:function(t){var e=this;uni.saveImageToPhotosAlbum({filePath:t,success:function(){e.$http.toast("保存成功")},fail:function(t){e.$http.toast("保存失败,请稍后重试")}})},getData:function(){var t=this;this.$http.request({url:this.$api.plugin.extensions.poster,method:"POST",showLoading:!0}).then((function(e){0==e.code?t.poster_url=e.data.pic_url:(t.$http.toast(e.msg),uni.redirectTo())}))}}};e.default=n}).call(this,n("0de9")["log"])},d1fe:function(t,e,n){var i=n("a906");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var o=n("4f06").default;o("0f6fa7cc",i,!0,{sourceMap:!1,shadowMode:!1})},d7a7:function(t,e,n){var i=n("eede");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var o=n("4f06").default;o("35765931",i,!0,{sourceMap:!1,shadowMode:!1})},e5bc:function(t,e,n){"use strict";n.d(e,"b",(function(){return o})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){return i}));var i={mainLoading:n("18c0").default},o=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticClass:"app"},[n("v-uni-view",{staticClass:"goods-qrcode-modal"},[n("v-uni-view",{staticClass:"goods-qrcode-body flex-col"},[n("v-uni-view",{staticClass:"flex-grow-1",staticStyle:{position:"relative"}},[n("v-uni-view",{staticStyle:{width:"100%",height:"100%"}},[n("v-uni-view",{staticClass:"goods-qrcode-box"},[n("v-uni-image",{staticClass:"goods-qrcode",attrs:{src:t.poster_url,mode:"widthFix"}})],1)],1)],1),n("v-uni-view",{staticClass:"flex-grow-0 flex-col flex-y-center",staticStyle:{padding:"30rpx 60rpx"}},[n("v-uni-view",[t._v("长按图片保存至本地")])],1)],1)],1),n("main-loading",{attrs:{visible:t.loading}})],1)},a=[]},eede:function(t,e,n){var i=n("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 商城主题色 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.app[data-v-8cfd1278]{height:100%;background-color:#fff}.goods-qrcode-modal[data-v-8cfd1278]{width:100%;height:100%}.goods-qrcode-body[data-v-8cfd1278]{background:#fff;border-radius:%?10?%;padding:%?40?%}.goods-qrcode-body .goods-qrcode-box[data-v-8cfd1278]{height:100%;position:relative;box-shadow:0 0 %?15?% rgba(0,0,0,.15);border-radius:16px}.goods-qrcode[data-v-8cfd1278]{width:100%;height:100%;display:block;background:#fffffff}.goods-qrcode-close[data-v-8cfd1278]{position:absolute;top:%?40?%;right:%?40?%;padding:%?15?%}',""]),t.exports=e},fa2b:function(t,e,n){"use strict";var i=n("d7a7"),o=n.n(i);o.a}}]);