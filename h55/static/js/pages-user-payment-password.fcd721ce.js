(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-payment-password"],{"092b":function(t,a,e){"use strict";var i=e("7181"),n=e.n(i);n.a},"2aea":function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return s})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",{staticClass:"app"},[e("v-uni-view",{staticClass:"center"},[e("v-uni-view",{staticClass:"item"},[e("v-uni-view",{staticClass:"title"},[t._v("手机号码")]),e("v-uni-view",{staticClass:"input-btn"},[e("v-uni-input",{staticClass:"input",attrs:{type:"number",placeholder:"请输入手机号码"},model:{value:t.dataForm.mobile,callback:function(a){t.$set(t.dataForm,"mobile",t._n(a))},expression:"dataForm.mobile"}})],1)],1),e("v-uni-view",{staticClass:"item"},[e("v-uni-view",{staticClass:"title"},[t._v("验证码")]),e("v-uni-view",{staticClass:"input-btn"},[e("v-uni-input",{staticClass:"input",attrs:{type:"number",placeholder:"请输入验证码"},model:{value:t.dataForm.captcha,callback:function(a){t.$set(t.dataForm,"captcha",t._n(a))},expression:"dataForm.captcha"}}),e("v-uni-view",{staticClass:"btn",style:{color:t.textCol},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.getCode.apply(void 0,arguments)}}},[t._v(t._s(t.countDown||"发送验证码"))])],1)],1),e("v-uni-view",{staticClass:"item"},[e("v-uni-view",{staticClass:"title"},[t._v("设置支付密码")]),e("v-uni-view",{staticClass:"input-btn"},[e("v-uni-input",{staticClass:"input",staticStyle:{"-webkit-text-security":"disc"},attrs:{type:"number",maxlength:"6",placeholder:"请输入6位数支付密码"},model:{value:t.dataForm.password,callback:function(a){t.$set(t.dataForm,"password",a)},expression:"dataForm.password"}})],1)],1),e("v-uni-view",{staticClass:"item"},[e("v-uni-view",{staticClass:"title"},[t._v("确认支付密码")]),e("v-uni-view",{staticClass:"input-btn"},[e("v-uni-input",{staticClass:"input",staticStyle:{"-webkit-text-security":"disc"},attrs:{type:"number",maxlength:"6",placeholder:"请输入6位数支付密码"},model:{value:t.dataForm.confirm_password,callback:function(a){t.$set(t.dataForm,"confirm_password",a)},expression:"dataForm.confirm_password"}})],1)],1),e("v-uni-view",{staticClass:"item last"},[e("v-uni-view",{staticClass:"title"}),e("v-uni-view",{staticClass:"jx-btn",style:{background:t.textCol},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.dataSubmit.apply(void 0,arguments)}}},[t._v("确认提交")])],1)],1)],1)},s=[]},"5af1":function(t,a,e){"use strict";e.r(a);var i=e("cb65"),n=e.n(i);for(var s in i)"default"!==s&&function(t){e.d(a,t,(function(){return i[t]}))}(s);a["default"]=n.a},7181:function(t,a,e){var i=e("8ac0");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("793cccce",i,!0,{sourceMap:!1,shadowMode:!1})},"8ac0":function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.app[data-v-57daf9c7]{background-color:#fff;height:100%;width:100%;display:-webkit-box;display:-webkit-flex;display:flex}.app .center[data-v-57daf9c7]{-webkit-box-flex:1;-webkit-flex:1;flex:1;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;padding:%?100?% %?30?% 0}.app .center .item[data-v-57daf9c7]{padding:0 %?30?%;font-size:#000000;border-bottom:%?2?% solid #e0e0e0}.app .center .item[data-v-57daf9c7]:last-child{border-bottom:0}.app .center .item .title[data-v-57daf9c7]{line-height:%?60?%;font-size:13pt;padding:%?20?% 0}.app .center .item .input-btn[data-v-57daf9c7]{margin-bottom:%?20?%;display:-webkit-box;display:-webkit-flex;display:flex}.app .center .item .input-btn .input[data-v-57daf9c7]{font-size:11pt;-webkit-box-flex:1;-webkit-flex:1;flex:1}.app .center .item .input-btn .uni-input-placeholder[data-v-57daf9c7]{color:#e0e0e0}.app .center .item .input-btn .btn[data-v-57daf9c7]{-webkit-flex-basis:%?180?%;flex-basis:%?180?%;position:relative;color:#bc0100;font-size:11pt;text-align:center}.app .center .item .input-btn .btn[data-v-57daf9c7]::before{color:#e0e0e0;content:"|";position:absolute;left:%?-20?%}.app .center .item .jx-btn[data-v-57daf9c7]{text-align:center;height:%?90?%;line-height:%?90?%;background-color:#bc0100;border-radius:%?45?%;font-size:9pt;color:#fff}.app .center .item.last span[data-v-57daf9c7]{margin-left:%?30?%;color:#000;font-size:8pt}',""]),t.exports=a},c2d9:function(t,a,e){"use strict";e.r(a);var i=e("2aea"),n=e("5af1");for(var s in n)"default"!==s&&function(t){e.d(a,t,(function(){return n[t]}))}(s);e("092b");var o,c=e("f0c5"),r=Object(c["a"])(n["default"],i["b"],i["c"],!1,null,"57daf9c7",null,!1,i["a"],o);a["default"]=r.exports},cb65:function(t,a,e){"use strict";(function(t){Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i=e("0b76"),n={data:function(){return{data:{},dataForm:{mobile:"",captcha:"",password:"",confirm_password:""},countDown:"",textCol:""}},onLoad:function(){this.textCol=this.globalSet("textCol"),this.getUserInfo()},methods:{dataSubmit:function(){var t=this;(0,i.isNullOrEmpty)(this.dataForm.captcha)?(0,i.isNullOrEmpty)(this.dataForm.password)&&6===this.dataForm.password.length?this.dataForm.confirm_password===this.dataForm.password?this.$http.request({url:this.$api.user.transactionPwd,method:"POST",data:this.dataForm}).then((function(a){0==a.code?(t.$http.toast("保存成功"),setTimeout((function(){t.navBack()}),2e3)):t.$http.toast(a.msg)})):this.$http.toast("请确认和上面的交易密码一致"):this.$http.toast("请输入6位数字交易密码"):this.$http.toast("验证码必须填写哦")},getCode:function(){if((0,i.isMobile)(this.dataForm.mobile)){var a=this;a.countDown=60;var e=setInterval((function(){a.countDown--,a.countDown<=0&&clearInterval(e)}),1e3);a.$http.request({url:a.$api.default.phoneCode,data:{mobile:a.dataForm.mobile},method:"POST"}).then((function(t){0==t.code?a.$http.toast("发送成功"):a.$http.toast(t.msg)})).catch((function(a){t("log",a," at pages/user/payment/password.vue:118")}))}else this.showMsg("请输入手机号后在获取验证码")},getUserInfo:function(){var a=this;this.$http.request({url:this.$api.user.userInfo,method:"POST",showLoading:!0}).then((function(t){0==t.code&&(a.$set(a,"data",t.data),t.data.mobile&&a.$set(a.dataForm,"mobile",t.data.mobile))})).catch((function(a){t("log",a," at pages/user/payment/password.vue:132")}))}}};a.default=n}).call(this,e("0de9")["log"])}}]);