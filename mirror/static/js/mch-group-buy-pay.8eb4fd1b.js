(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["mch-group-buy-pay"],{1205:function(t,i,e){"use strict";var a=e("dd4f"),n=e.n(a);n.a},5068:function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return o})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"cart-root"},[e("v-uni-view",{staticClass:"status_bar",staticStyle:{background:"#bc0100"}}),e("v-uni-view",{class:["status_bar2",t.showBool?"show":"hide"],style:{opacity:t.opacity,background:"#ffffff"}}),e("v-uni-view",{staticClass:"cart-bg"},[e("v-uni-image",{staticClass:"cart-bg-img",attrs:{src:t.img_url+"cartBg.png",mode:"widthFix"}})],1),e("v-uni-view",{staticClass:"cart-title",staticStyle:{background:"#bc0100"}},[t._v("确认支付"),e("v-uni-view",{staticClass:"iconfont icon-fanhui back",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.back.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"mainContent"},[e("v-uni-view",{staticClass:"up"},[e("v-uni-view",{staticClass:"balance"},[e("v-uni-text",[t._v("账户余额:")]),e("v-uni-view",[e("v-uni-text",{staticClass:"price"},[t._v(t._s(t.payData.balance))]),t._v("元")],1)],1),e("v-uni-view",{staticClass:"pay-content"},[e("v-uni-view",{staticClass:"pay-title"},[t._v("支付金额")]),t.payData.amount?e("v-uni-view",{staticClass:"pay-price"},[e("v-uni-text",{staticClass:"pay-icon"},[t._v("¥")]),t._v(t._s(t.payData.amount.toFixed(2)))],1):t._e(),e("v-uni-view",{staticClass:"numbering"},[t._v("订单编号："+t._s(t.payData.orderNo))])],1)],1),e("v-uni-view",{staticClass:"down"},t._l(t.payData.supportPayTypes,(function(i,a){return e("v-uni-view",{key:a,staticClass:"down-item"},[e("v-uni-view",{staticClass:"item-left"},[e("v-uni-image",{staticClass:"item-img",attrs:{src:t.img_url+"images/pay/"+i+".png",mode:""}}),"wechat"==i?e("v-uni-text",[t._v("微信支付")]):t._e(),"alipay"==i?e("v-uni-text",[t._v("支付宝支付")]):t._e(),"balance"==i?e("v-uni-text",[t._v("余额支付")]):t._e()],1),e("v-uni-view",{staticClass:"item-right-box",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.switchIcon(a)}}},[a==t.switchIndex?e("v-uni-view",{staticClass:"item-icon iconfont icon-dagou1"}):e("v-uni-view",{staticClass:"item-right"})],1)],1)})),1),e("v-uni-view",{staticClass:"confirmPay",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.confirmPay.apply(void 0,arguments)}}},[t._v("确认支付")])],1)],1)},o=[]},5368:function(t,i,e){"use strict";e.r(i);var a=e("5068"),n=e("a804");for(var o in n)"default"!==o&&function(t){e.d(i,t,(function(){return n[t]}))}(o);e("1205");var s,c=e("f0c5"),d=Object(c["a"])(n["default"],a["b"],a["c"],!1,null,"74bec908",null,!1,a["a"],s);i["default"]=d.exports},a804:function(t,i,e){"use strict";e.r(i);var a=e("f718"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,(function(){return a[t]}))}(o);i["default"]=n.a},dd4f:function(t,i,e){var a=e("ffc5");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("09cbcde0",a,!0,{sourceMap:!1,shadowMode:!1})},f718:function(t,i,e){"use strict";(function(t){Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;e("68bf");var a={data:function(){return{img_url:this.$api.img_url,showBool:!1,opacity:0,token:"",queue_id:"",payData:"",switchIndex:0,is_index:"",active_id:"",goods_id:""}},onLoad:function(i){this.goods_id=i.goods_id,t("log",this.goods_id," at mch/group-buy/pay.vue:75"),this.token=i.token,this.queue_id=i.queue_id;var e=i.orderId?i.orderId:"";this.getPayData(e),i.is_index&&(this.is_index=1,this.active_id=i.active_id,t("log","this.is_index:"+this.is_index," at mch/group-buy/pay.vue:84"),t("log","this.active_id:"+this.active_id," at mch/group-buy/pay.vue:85"))},methods:{back:function(){uni.navigateBack()},getPayData:function(t){var i=this;this.$http.request({url:this.$api.order.toPay,showLoading:!0,data:{token:this.token||"",queue_id:this.queue_id||"",id:t}}).then((function(t){0==t.code&&(i.payData=t.data)}))},confirmPay:function(){var t=this;this.$http.request({url:this.$api.payment.doPay,showLoading:!0,method:"post",data:{union_id:this.payData.union_id,pay_type:this.payData.supportPayTypes[this.switchIndex]}}).then((function(i){if(0==i.code){if("balance"==t.payData.supportPayTypes[t.switchIndex])return t.$http.toast("支付成功!"),void setTimeout((function(){uni.redirectTo({url:"/pages/order/list?status=1&goods_id="+t.goods_id})}),1e3);t.$wechatSdk.pay(i.data,"/pages/order/list?status=1&goods_id="+t.goods_id)}else t.$http.toast(i.msg),setTimeout((function(){uni.redirectTo({url:"/pages/order/list?status=0&goods_id="+t.goods_id})}),1e3)}))},switchIcon:function(t){this.switchIndex=t}}};i.default=a}).call(this,e("0de9")["log"])},ffc5:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,".status_bar[data-v-74bec908]{height:0;width:100%}.status_bar2[data-v-74bec908]{height:0;width:100%;position:fixed;top:0;z-index:99}body[data-v-74bec908],\nuni-page-body[data-v-74bec908]{background-color:$uni-bg-color-grey}.cart-root[data-v-74bec908]{position:relative;z-index:1}.cart-bg[data-v-74bec908]{position:absolute;width:100%}.cart-bg-img[data-v-74bec908]{width:100%}.cart-title[data-v-74bec908]{padding:%?40?% 0 %?0?%;color:#fff;font-size:14pt;letter-spacing:%?4?%;text-align:center;position:relative;margin-bottom:%?40?%}.back[data-v-74bec908]{position:absolute;top:%?40?%;left:%?30?%;font-size:%?40?%}.mainContent[data-v-74bec908]{z-index:999;position:relative;width:%?690?%;margin:%?118?% auto 0}.up[data-v-74bec908]{background:#fff;border-radius:%?15?%;margin-bottom:%?30?%}.balance[data-v-74bec908]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;padding:%?30?% %?23?%;font-size:10pt;color:#000;border-bottom:%?2?% solid #f2f2f2}.price[data-v-74bec908]{font-size:13pt;margin-right:%?10?%}.pay-content[data-v-74bec908]{padding:%?43?% 0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.pay-title[data-v-74bec908]{font-size:9pt;font-weight:600;margin-bottom:%?20?%;color:#000}.pay-icon[data-v-74bec908]{font-size:12pt;margin-right:%?20?%;font-weight:500}.pay-price[data-v-74bec908]{color:#bc0100;font-size:16pt;font-weight:600;margin-bottom:%?20?%}.numbering[data-v-74bec908]{font-size:10pt;color:#000}.down[data-v-74bec908]{background:#fff;border-radius:%?15?%;padding:%?40?% %?20?%}.down-item[data-v-74bec908]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;align-items:center;margin-bottom:%?40?%}.down .down-item[data-v-74bec908]:last-child{margin-bottom:%?0?%}.item-left[data-v-74bec908]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;color:#000;font-size:10pt}.item-img[data-v-74bec908]{width:%?40?%;height:%?40?%;margin-right:%?30?%}.item-right-box[data-v-74bec908]{width:%?38?%;height:%?38?%;position:relative}.item-right[data-v-74bec908]{width:%?38?%;height:%?38?%;border-radius:50%;border:%?2?% solid #8b8b8b;position:absolute}.item-icon[data-v-74bec908]{font-size:%?38?%;position:absolute;top:%?0?%;left:%?0?%;line-height:%?40?%;color:#bc0100}.confirmPay[data-v-74bec908]{color:#fff;background:#bc0100;padding:%?26?% 0;text-align:center;border-radius:%?80?%;font-size:10pt;letter-spacing:%?4?%;margin-top:%?80?%}body.?%PAGE?%[data-v-74bec908]{background-color:$uni-bg-color-grey}",""]),t.exports=i}}]);