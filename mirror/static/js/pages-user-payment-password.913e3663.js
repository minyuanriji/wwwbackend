(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-payment-password"],{"0f1b":function(t,e,a){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=a("2222"),n={data:function(){return{data:{},dataForm:{mobile:"",captcha:"",password:"",confirm_password:""},countDown:"",textCol:""}},onLoad:function(){this.textCol=this.globalSet("textCol"),this.getUserInfo()},methods:{dataSubmit:function(){var t=this;(0,i.isNullOrEmpty)(this.dataForm.captcha)?(0,i.isNullOrEmpty)(this.dataForm.password)&&6===this.dataForm.password.length?this.dataForm.confirm_password===this.dataForm.password?this.$http.request({url:this.$api.user.transactionPwd,method:"POST",data:this.dataForm}).then((function(e){0==e.code?(t.$http.toast("保存成功"),setTimeout((function(){t.navBack()}),2e3)):t.$http.toast(e.msg)})):this.$http.toast("请确认和上面的交易密码一致"):this.$http.toast("请输入6位数字交易密码"):this.$http.toast("验证码必须填写哦")},getCode:function(){if((0,i.isMobile)(this.dataForm.mobile)){var e=this;e.countDown=60;var a=setInterval((function(){e.countDown--,e.countDown<=0&&clearInterval(a)}),1e3);e.$http.request({url:e.$api.default.phoneCode,data:{mobile:e.dataForm.mobile},method:"POST"}).then((function(t){0==t.code?e.$http.toast("发送成功"):e.$http.toast(t.msg)})).catch((function(e){t("log",e," at pages/user/payment/password.vue:118")}))}else this.showMsg("请输入手机号后在获取验证码")},getUserInfo:function(){var e=this;this.$http.request({url:this.$api.user.userInfo,method:"POST",showLoading:!0}).then((function(t){0==t.code&&(e.$set(e,"data",t.data),t.data.mobile&&e.$set(e.dataForm,"mobile",t.data.mobile))})).catch((function(e){t("log",e," at pages/user/payment/password.vue:132")}))}}};e.default=n}).call(this,a("0de9")["log"])},"28c0":function(t,e,a){"use strict";var i=a("56fb"),n=a.n(i);n.a},"55d8":function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 商城主题色 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.app[data-v-463fcc01]{background-color:#fff;height:100%;width:100%;display:-webkit-box;display:-webkit-flex;display:flex}.app .center[data-v-463fcc01]{-webkit-box-flex:1;-webkit-flex:1;flex:1;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;padding:%?100?% %?30?% 0}.app .center .item[data-v-463fcc01]{padding:0 %?30?%;font-size:#000000;border-bottom:%?2?% solid #e0e0e0}.app .center .item[data-v-463fcc01]:last-child{border-bottom:0}.app .center .item .title[data-v-463fcc01]{line-height:%?60?%;font-size:13pt;padding:%?20?% 0}.app .center .item .input-btn[data-v-463fcc01]{margin-bottom:%?20?%;display:-webkit-box;display:-webkit-flex;display:flex}.app .center .item .input-btn .input[data-v-463fcc01]{font-size:11pt;-webkit-box-flex:1;-webkit-flex:1;flex:1}.app .center .item .input-btn .uni-input-placeholder[data-v-463fcc01]{color:#e0e0e0}.app .center .item .input-btn .btn[data-v-463fcc01]{-webkit-flex-basis:%?180?%;flex-basis:%?180?%;position:relative;color:#bc0100;font-size:11pt;text-align:center}.app .center .item .input-btn .btn[data-v-463fcc01]::before{color:#e0e0e0;content:"|";position:absolute;left:%?-20?%}.app .center .item .jx-btn[data-v-463fcc01]{text-align:center;height:%?90?%;line-height:%?90?%;background-color:#bc0100;border-radius:%?45?%;font-size:9pt;color:#fff}.app .center .item.last span[data-v-463fcc01]{margin-left:%?30?%;color:#000;font-size:8pt}',""]),t.exports=e},"56fb":function(t,e,a){var i=a("55d8");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("4f06").default;n("e1190eca",i,!0,{sourceMap:!1,shadowMode:!1})},bcc5:function(t,e,a){"use strict";a.r(e);var i=a("0f1b"),n=a.n(i);for(var s in i)"default"!==s&&function(t){a.d(e,t,(function(){return i[t]}))}(s);e["default"]=n.a},ee9b:function(t,e,a){"use strict";var i;a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return s})),a.d(e,"a",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{staticClass:"app"},[a("v-uni-view",{staticClass:"center"},[a("v-uni-view",{staticClass:"item"},[a("v-uni-view",{staticClass:"title"},[t._v("手机号码")]),a("v-uni-view",{staticClass:"input-btn"},[a("v-uni-input",{staticClass:"input",attrs:{type:"number",placeholder:"请输入手机号码"},model:{value:t.dataForm.mobile,callback:function(e){t.$set(t.dataForm,"mobile",t._n(e))},expression:"dataForm.mobile"}})],1)],1),a("v-uni-view",{staticClass:"item"},[a("v-uni-view",{staticClass:"title"},[t._v("验证码")]),a("v-uni-view",{staticClass:"input-btn"},[a("v-uni-input",{staticClass:"input",attrs:{type:"number",placeholder:"请输入验证码"},model:{value:t.dataForm.captcha,callback:function(e){t.$set(t.dataForm,"captcha",t._n(e))},expression:"dataForm.captcha"}}),a("v-uni-view",{staticClass:"btn",style:{color:"rgb(255, 113, 4)"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.getCode.apply(void 0,arguments)}}},[t._v(t._s(t.countDown||"发送验证码"))])],1)],1),a("v-uni-view",{staticClass:"item"},[a("v-uni-view",{staticClass:"title"},[t._v("设置支付密码")]),a("v-uni-view",{staticClass:"input-btn"},[a("v-uni-input",{staticClass:"input",staticStyle:{"-webkit-text-security":"disc"},attrs:{type:"number",maxlength:"6",placeholder:"请输入6位数支付密码"},model:{value:t.dataForm.password,callback:function(e){t.$set(t.dataForm,"password",e)},expression:"dataForm.password"}})],1)],1),a("v-uni-view",{staticClass:"item"},[a("v-uni-view",{staticClass:"title"},[t._v("确认支付密码")]),a("v-uni-view",{staticClass:"input-btn"},[a("v-uni-input",{staticClass:"input",staticStyle:{"-webkit-text-security":"disc"},attrs:{type:"number",maxlength:"6",placeholder:"请输入6位数支付密码"},model:{value:t.dataForm.confirm_password,callback:function(e){t.$set(t.dataForm,"confirm_password",e)},expression:"dataForm.confirm_password"}})],1)],1),a("v-uni-view",{staticClass:"item last"},[a("v-uni-view",{staticClass:"title"}),a("v-uni-view",{staticClass:"jx-btn",style:{background:"rgb(255, 113, 4)"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.dataSubmit.apply(void 0,arguments)}}},[t._v("确认提交")])],1)],1)],1)},s=[]},eed4:function(t,e,a){"use strict";a.r(e);var i=a("ee9b"),n=a("bcc5");for(var s in n)"default"!==s&&function(t){a.d(e,t,(function(){return n[t]}))}(s);a("28c0");var o,r=a("f0c5"),c=Object(r["a"])(n["default"],i["b"],i["c"],!1,null,"463fcc01",null,!1,i["a"],o);e["default"]=c.exports}}]);