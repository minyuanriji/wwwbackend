(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-setting"],{"0d39":function(t,a,e){"use strict";e("a9e3"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i={name:"jxListCell",props:{arrow:{type:Boolean,default:!1},hover:{type:Boolean,default:!0},lineLeft:{type:Boolean,default:!0},lineRight:{type:Boolean,default:!1},padding:{type:String,default:"26rpx 30rpx"},last:{type:Boolean,default:!1},radius:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fff"},size:{type:Number,default:32},color:{type:String,default:"#333"},index:{type:Number,default:0}},methods:{handleClick:function(){this.$emit("click",{index:this.index})}}};a.default=i},"13bb":function(t,a,e){"use strict";e.r(a);var i=e("f9e1"),n=e("dcc9");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);e("253f");var r,l=e("f0c5"),d=Object(l["a"])(n["default"],i["b"],i["c"],!1,null,"256fadaa",null,!1,i["a"],r);a["default"]=d.exports},1906:function(t,a,e){"use strict";e("a9e3"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i={name:"tuiButton",props:{type:{type:String,default:"primary"},shadow:{type:Boolean,default:!1},width:{type:String,default:"100%"},height:{type:String,default:"94rpx"},size:{type:Number,default:32},shape:{type:String,default:"square"},plain:{type:Boolean,default:!1},disabled:{type:Boolean,default:!1},loading:{type:Boolean,default:!1}},methods:{handleClick:function(){if(this.disabled)return!1;this.$emit("click",{})},getShadowClass:function(t,a,e){var i="";return a&&"white"!=t&&!e&&(i="tui-shadow-"+t),i},getDisabledClass:function(t,a){var e="";return t&&"white"!=a&&"gray"!=a&&(e="tui-dark-disabled"),e},getShapeClass:function(t,a){var e="";return"circle"==t?e=a?"tui-outline-fillet":"tui-fillet":"rightAngle"==t&&(e=a?"tui-outline-rightAngle":"tui-rightAngle"),e},getHoverClass:function(t,a,e){var i="";return t||(i=e?"tui-outline-hover":"tui-"+(a||"primary")+"-hover"),i}}};a.default=i},"20e3":function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-button",{staticClass:"tui-btn-class tui-btn",class:[t.plain?"tui-"+t.type+"-outline":"tui-btn-"+(t.type||"primary"),t.getDisabledClass(t.disabled,t.type),t.getShapeClass(t.shape,t.plain),t.getShadowClass(t.type,t.shadow,t.plain)],style:{width:t.width,height:t.height,lineHeight:t.height,fontSize:t.size+"rpx"},attrs:{"hover-class":t.getHoverClass(t.disabled,t.type,t.plain),loading:t.loading,disabled:t.disabled},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.handleClick.apply(void 0,arguments)}}},[t._t("default")],2)},o=[]},2263:function(t,a,e){"use strict";e.r(a);var i=e("0d39"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a},"253f":function(t,a,e){"use strict";var i=e("42f6"),n=e.n(i);n.a},"3a37":function(t,a,e){"use strict";e.r(a);var i=e("7082"),n=e("2263");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);e("cb59");var r,l=e("f0c5"),d=Object(l["a"])(n["default"],i["b"],i["c"],!1,null,"56e2521a",null,!1,i["a"],r);a["default"]=d.exports},"3dd1":function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,'.jx-list-cell[data-v-56e2521a]{position:relative;width:100%;box-sizing:border-box;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.jx-radius[data-v-56e2521a]{border-radius:%?20?%;overflow:hidden}.jx-cell-hover[data-v-56e2521a]{background:#f7f7f9!important}.jx-list-cell[data-v-56e2521a]::after{content:"";position:absolute;\n\t/* border-bottom: 1rpx solid #eaeef1; */-webkit-transform:scaleY(.5);transform:scaleY(.5);bottom:0;right:0;left:0}.jx-line-left[data-v-56e2521a]::after{left:%?30?%!important}.jx-line-right[data-v-56e2521a]::after{right:%?30?%!important}.jx-cell-last[data-v-56e2521a]::after{border-bottom:0!important}.jx-list-cell.jx-cell-arrow[data-v-56e2521a]:before{content:" ";height:11px;width:11px;border-width:2px 2px 0 0;border-color:#b2b2b2;border-style:solid;-webkit-transform:matrix(.5,.5,-.5,.5,0,0);transform:matrix(.5,.5,-.5,.5,0,0);position:absolute;top:50%;margin-top:-7px;right:%?30?%}',""]),t.exports=a},"42f6":function(t,a,e){var i=e("d789");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("17c4a193",i,!0,{sourceMap:!1,shadowMode:!1})},4474:function(t,a,e){"use strict";var i=e("4ea4");Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n=i(e("3a37")),o=i(e("5409")),r={components:{tuiListCell:n.default,tuiButton:o.default},data:function(){return{userInfo:null,is_login:!0,is_open:!1,textColor:"#bc0100"}},onLoad:function(){uni.getStorageSync("mall_config")&&(this.textColor=this.globalSet("textCol")),this.getUser(),this.is_login=this.$http.isLogin(),this.getSetting()},methods:{openMsg:function(t){var a=this;this.is_open=t.detail.value,this.$http.request({url:this.$api.update_setting,showLoading:!0,method:"post",data:{setting_key:"wechat_notice",data:{is_open:this.is_open?1:0}}}).then((function(t){t.code,a.$http.toast(t.msg)}))},getSetting:function(){var t=this;this.$http.request({url:this.$api.get_setting,showLoading:!0,data:{setting_key:"wechat_notice"}}).then((function(a){0==a.code?0==a.data.data.is_open?t.is_open=!1:t.is_open=!0:98==a.code&&(t.is_open=!0)}))},getUser:function(){},logout:function(){uni.removeStorageSync("token"),uni.removeStorageSync("userInfo"),this.$http.toast("登出成功"),uni.reLaunch({url:"/pages/index/index"})},href:function(t){var a="";switch(t){case 1:a="./info";break;case 2:a="./address/list";break;case 3:a="/pages/user/payment/password";break;case 4:a="/pages/about/about";break;default:break}uni.navigateTo({url:a})}}};a.default=r},5409:function(t,a,e){"use strict";e.r(a);var i=e("20e3"),n=e("9542");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);e("bc77");var r,l=e("f0c5"),d=Object(l["a"])(n["default"],i["b"],i["c"],!1,null,"0163bb38",null,!1,i["a"],r);a["default"]=d.exports},5827:function(t,a,e){var i=e("3dd1");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("76ba2f10",i,!0,{sourceMap:!1,shadowMode:!1})},5868:function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,'.tui-btn-primary[data-v-0163bb38]{background:#1582ad!important;color:#fff}.tui-shadow-primary[data-v-0163bb38]{box-shadow:0 %?10?% %?14?% 0 rgba(15,96,128,.14)}.tui-btn-danger[data-v-0163bb38]{background:#bc0100!important;color:#fff}.tui-shadow-danger[data-v-0163bb38]{box-shadow:0 %?10?% %?14?% 0 rgba(235,9,9,.2)}.tui-btn-warning[data-v-0163bb38]{background:#fc872d!important;color:#fff}.tui-shadow-warning[data-v-0163bb38]{box-shadow:0 %?10?% %?14?% 0 rgba(252,135,45,.2)}.tui-btn-green[data-v-0163bb38]{background:#35b06a!important;color:#fff}.tui-shadow-green[data-v-0163bb38]{box-shadow:0 %?10?% %?14?% 0 rgba(53,176,106,.2)}.tui-btn-blue[data-v-0163bb38]{background:#5677fc!important;color:#fff}.tui-shadow-blue[data-v-0163bb38]{box-shadow:0 %?10?% %?14?% 0 rgba(86,119,252,.2)}.tui-btn-white[data-v-0163bb38]{background:#fff!important;color:#333!important}.tui-btn-gray[data-v-0163bb38]{background:#bfbfbf!important;color:#fff!important}.tui-btn-black[data-v-0163bb38]{background:#333!important;color:#fff!important}.tui-shadow-gray[data-v-0163bb38]{box-shadow:0 %?10?% %?14?% 0 hsla(0,0%,74.9%,.2)}.tui-hover-gray[data-v-0163bb38]{background:#f7f7f9!important}\n\n/* button start*/.tui-btn[data-v-0163bb38]{width:100%;position:relative;border:0!important;border-radius:%?6?%;padding-left:0;padding-right:0;overflow:visible}.tui-btn[data-v-0163bb38]::after{content:"";position:absolute;width:200%;height:200%;-webkit-transform-origin:0 0;transform-origin:0 0;-webkit-transform:scale(.5) translateZ(0);transform:scale(.5) translateZ(0);box-sizing:border-box;left:0;top:0;border-radius:%?12?%;border:0}.tui-btn-white[data-v-0163bb38]::after{border:%?1?% solid #bfbfbf}.tui-white-hover[data-v-0163bb38]{background:#e5e5e5!important;color:#2e2e2e!important}.tui-dark-disabled[data-v-0163bb38]{opacity:.6!important;color:#fafbfc!important}.tui-dark-disabled.tui-btn-danger[data-v-0163bb38]{opacity:1!important;background:#fc8888!important}.tui-outline-hover[data-v-0163bb38]{opacity:.5}.tui-primary-hover[data-v-0163bb38]{background:#126f93!important;color:#e5e5e5!important}.tui-primary-outline[data-v-0163bb38]::after{border:%?1?% solid #1582ad!important}.tui-primary-outline[data-v-0163bb38]{color:#1582ad!important;background:none}.tui-danger-hover[data-v-0163bb38]{background:#c80808!important;color:#e5e5e5!important}.tui-danger-outline[data-v-0163bb38]{color:#eb0909!important;background:none}.tui-danger-outline[data-v-0163bb38]::after{border:%?1?% solid #eb0909!important}.tui-warning-hover[data-v-0163bb38]{background:#d67326!important;color:#e5e5e5!important}.tui-warning-outline[data-v-0163bb38]{color:#fc872d!important;background:none}.tui-warning-outline[data-v-0163bb38]::after{border:1px solid #fc872d!important}.tui-green-hover[data-v-0163bb38]{background:#2d965a!important;color:#e5e5e5!important}.tui-green-outline[data-v-0163bb38]{color:#35b06a!important;background:none}.tui-green-outline[data-v-0163bb38]::after{border:%?1?% solid #35b06a!important}.tui-blue-hover[data-v-0163bb38]{background:#4a67d6!important;color:#e5e5e5!important}.tui-blue-outline[data-v-0163bb38]{color:#5677fc!important;background:none}.tui-blue-outline[data-v-0163bb38]::after{border:%?1?% solid #5677fc!important}.tui-gray-hover[data-v-0163bb38]{background:#a3a3a3!important;color:#898989}.tui-gray-outline[data-v-0163bb38]{color:#999!important;background:none!important}.tui-white-outline[data-v-0163bb38]{color:#fff!important;background:none!important}.tui-black-outline[data-v-0163bb38]{background:none!important;color:#333!important}.tui-gray-outline[data-v-0163bb38]::after{border:%?1?% solid #ccc!important}.tui-white-outline[data-v-0163bb38]::after{border:1px solid #fff!important}.tui-black-outline[data-v-0163bb38]::after{border:1px solid #333!important}\n\n/*圆角 */.tui-fillet[data-v-0163bb38]{border-radius:%?50?%}.tui-btn-white.tui-fillet[data-v-0163bb38]::after{border-radius:%?98?%}.tui-outline-fillet[data-v-0163bb38]::after{border-radius:%?98?%}\n\n/*平角*/.tui-rightAngle[data-v-0163bb38]{border-radius:0}.tui-btn-white.tui-rightAngle[data-v-0163bb38]::after{border-radius:0}.tui-outline-rightAngle[data-v-0163bb38]::after{border-radius:0}',""]),t.exports=a},7082:function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",{staticClass:"jx-cell-class jx-list-cell",class:{"jx-cell-arrow":t.arrow,"jx-cell-last":t.last,"jx-line-left":t.lineLeft,"jx-line-right":t.lineRight,"jx-radius":t.radius},style:{background:t.bgcolor,fontSize:t.size+"rpx",color:t.color,padding:t.padding},attrs:{"hover-class":t.hover?"jx-cell-hover":"","hover-stay-time":150},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.handleClick.apply(void 0,arguments)}}},[t._t("default")],2)},o=[]},9542:function(t,a,e){"use strict";e.r(a);var i=e("1906"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a},bc77:function(t,a,e){"use strict";var i=e("d2dc"),n=e.n(i);n.a},cb59:function(t,a,e){"use strict";var i=e("5827"),n=e.n(i);n.a},d2dc:function(t,a,e){var i=e("5868");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("5dae9cbf",i,!0,{sourceMap:!1,shadowMode:!1})},d789:function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.tui-set-box[data-v-256fadaa]{padding-bottom:%?20?%;color:#333}.tui-list-cell[data-v-256fadaa]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding:%?24?% %?30?%;font-size:%?30?%}.tui-info-box[data-v-256fadaa]{font-size:%?34?%}.tui-avatar[data-v-256fadaa]{width:%?140?%;height:%?140?%;margin-right:%?20?%;border-radius:%?100?%;border:%?1?% solid #000}.tui-mtop[data-v-256fadaa]{margin-top:%?20?%}.tui-exit[data-v-256fadaa]{padding:%?100?% %?24?%}.exit-btn[data-v-256fadaa]{text-align:center;padding:%?20?% 0;color:#fff}',""]),t.exports=a},dcc9:function(t,a,e){"use strict";e.r(a);var i=e("4474"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a},f9e1:function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",{staticClass:"app"},[e("v-uni-view",{staticClass:"tui-set-box"},[e("tui-list-cell",{attrs:{padding:"0",lineLeft:!1,arrow:!0},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.href(1)}}},[t.userInfo?e("v-uni-view",{staticClass:"tui-list-cell tui-info-box"},[e("v-uni-image",{staticClass:"tui-avatar",attrs:{src:t.userInfo.avatar}}),e("v-uni-view",[t._v(t._s(t.userInfo.nickname))])],1):t._e()],1),e("tui-list-cell",{attrs:{padding:"0",lineLeft:!1,arrow:!0},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.href(2)}}},[e("v-uni-view",{staticClass:"tui-list-cell"},[t._v("地址管理")])],1),e("v-uni-view",{staticClass:"tui-mtop"},[e("tui-list-cell",{attrs:{padding:"0",lineLeft:!1,arrow:!0},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.href(3)}}},[e("v-uni-view",{staticClass:"tui-list-cell"},[t._v("支付密码设置")])],1),e("tui-list-cell",{attrs:{padding:"0",lineLeft:!1,arrow:!1}},[e("v-uni-view",{staticClass:"flex flex-x-between flex-y-center",staticStyle:{width:"100%"}},[e("v-uni-view",{staticClass:"tui-list-cell"},[t._v("消息提醒设置")]),e("v-uni-switch",{staticStyle:{transform:"scale(0.7)"},attrs:{checked:t.is_open,color:t.textColor},on:{change:function(a){arguments[0]=a=t.$handleEvent(a),t.openMsg.apply(void 0,arguments)}}})],1)],1)],1),e("v-uni-view",{staticClass:"tui-mtop"},[e("tui-list-cell",{attrs:{padding:"0",lineLeft:!1,arrow:!0},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.href(4)}}},[e("v-uni-view",{staticClass:"tui-list-cell"},[t._v("关于我们")])],1)],1),t.is_login?e("v-uni-view",{staticClass:"tui-exit"},[e("v-uni-view",{staticClass:"exit-btn",style:{background:t.textColor},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.logout.apply(void 0,arguments)}}},[t._v("退出登录")])],1):t._e()],1)],1)},o=[]}}]);