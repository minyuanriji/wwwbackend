(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-order-submit"],{"0563":function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var a={comStatusBar:i("6e1b").default,comIcons:i("f934").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"uni-navbar"},[i("v-uni-view",{staticClass:"uni-navbar__content",class:{"uni-navbar--fixed":t.fixed,"uni-navbar--shadow":t.shadow,"uni-navbar--border":t.border},style:{"background-color":t.backgroundColor}},[t.statusBar?i("com-status-bar"):t._e(),i("v-uni-view",{staticClass:"uni-navbar__header uni-navbar__content_view",style:{color:t.color,backgroundColor:t.backgroundColor}},[i("v-uni-view",{staticClass:"uni-navbar__header-btns uni-navbar__header-btns-left uni-navbar__content_view",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.onClickLeft.apply(void 0,arguments)}}},[t.leftIcon.length?i("v-uni-view",{staticClass:"uni-navbar__content_view"},[i("com-icons",{attrs:{color:t.color,type:t.leftIcon,size:"24"}})],1):t._e(),t.leftText.length?i("v-uni-view",{staticClass:"uni-navbar-btn-text uni-navbar__content_view",class:{"uni-navbar-btn-icon-left":!t.leftIcon.length}},[i("v-uni-text",{style:{color:t.color,fontSize:"14px"}},[t._v(t._s(t.leftText))])],1):t._e(),t._t("left")],2),i("v-uni-view",{staticClass:"uni-navbar__header-container uni-navbar__content_view"},[t.title.length?i("v-uni-view",{staticClass:"uni-navbar__header-container-inner uni-navbar__content_view"},[i("v-uni-text",{staticClass:"uni-nav-bar-text",style:{color:t.color}},[t._v(t._s(t.title))])],1):t._e(),t._t("default")],2),i("v-uni-view",{staticClass:"uni-navbar__header-btns uni-navbar__content_view",class:t.title.length?"uni-navbar__header-btns-right":"",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.onClickRight.apply(void 0,arguments)}}},[t.rightIcon.length?i("v-uni-view",{staticClass:"uni-navbar__content_view"},[i("com-icons",{attrs:{color:t.color,type:t.rightIcon,size:"24"}})],1):t._e(),t.rightText.length&&!t.rightIcon.length?i("v-uni-view",{staticClass:"uni-navbar-btn-text uni-navbar__content_view"},[i("v-uni-text",{staticClass:"uni-nav-bar-right-text"},[t._v(t._s(t.rightText))])],1):t._e(),t._t("right")],2)],1)],1),t.fixed?i("v-uni-view",{staticClass:"uni-navbar__placeholder"},[t.statusBar?i("com-status-bar"):t._e(),i("v-uni-view",{staticClass:"uni-navbar__placeholder-view"})],1):t._e()],1)},o=[]},"0a2f":function(t,e,i){"use strict";var a=i("6bc0"),n=i.n(a);n.a},"2b25":function(t,e,i){"use strict";i.r(e);var a=i("43f1"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"32f6":function(t,e,i){"use strict";var a=i("4ea4");i("99af"),i("4160"),i("a434"),i("b680"),i("159b"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("be65")),o={components:{tuiListCell:n.default},data:function(){return{img_url:this.$api.img_url,hasCoupon:!0,insufficient:!1,list:"",addressId:0,user_address:"",popupShow2:!1,coupon_list:[],coupon_name:"",coupon_index:-1,coupon_id:0,user_coupon:"",shop_index:0,goods_index:0,remark:"",sendData:"",subSendData:"",use_coupon_list:[],coupon_goods_id:"",total_price:0,score_enable:0,is_checked:!1,use_score:0,total_score:0,total_score_use:0,is_request:!1,textColor:"#bc0100",couponImg:"",is_failure:!0,sign:"",related_id:"",is_auth:""}},onLoad:function(t){uni.getStorageSync("mall_config")&&(this.textColor=this.globalSet("textCol"),this.couponImg=this.globalSet("couponImg")),t.addressId?(this.addressId=t.addressId,this.getAddress()):this.addressId=0,this.sign=t.sign||"",this.related_id=t.related_id||"",this.related_user_id=t.related_user_id||"",this.sendData=uni.getStorageSync("orderData")},onShow:function(){this.getData()},onBackPress:function(t){uni.removeStorageSync("orderData")},computed:{groupName:function(){return function(t){var e="";return t.forEach((function(t){e+=t.attr_name})),e}}},methods:{onGetPhoneNumber:function(t){var e=this;"getPhoneNumber:fail user deny"==t.detail.errMsg?this.$http.toast("请授权手机号后再进行购物!"):this.$http.request({url:this.$api.default.authPhone,method:"post",data:{iv:t.detail.iv,encryptedData:t.detail.encryptedData}}).then((function(t){e.is_auth=1,e.btnPay()}))},back:function(){this.navBack()},use:function(t){var e=this;this.is_checked=t.detail.value,this.is_checked?this.use_score=1:this.use_score=0,this.getData(),this.total_score_use=0,this.list.forEach((function(t){e.total_score_use=e.total_score_use+1*t.score.deduction_price})),this.total_score=(this.total_price-this.total_score_use).toFixed(2)},getData:function(){var t=this;this.$http.request({url:this.$api.order.submit,method:"post",showLoading:!0,data:{list:[{mch_id:0,goods_list:this.sendData,use_coupon_list:this.use_coupon_list}],user_address_id:this.addressId,use_score:this.use_score}}).then((function(e){if(0==e.code){t.is_failure=!0;var i=e.data.list;t.is_auth=e.data.is_auth_phone,i.forEach((function(e){var i=t;i.use_coupon_list=[],e.same_goods_list&&e.same_goods_list.length>0&&e.same_goods_list.forEach((function(t,e){t["coupon_name"]="请选择优惠券",t.coupon_list.forEach((function(e,a){if(e.is_use=0,t.usable_user_coupon_id&&0!=t.usable_user_coupon_id&&t.usable_user_coupon_id==e.id){var n={};n["goods_id"]=t.goods_id,n["user_coupon_id"]=e.id,e.is_use=1,i.use_coupon_list.push(n),t.coupon_name=e.coupon_data.name}""!=t.coupon.coupon_error&&(t.coupon_name=t.coupon.coupon_error)}))}))})),t.list=i,t.score_enable=e.data.score_enable,t.list[0].score.use?(t.is_checked=!0,t.use_score=1):(t.is_checked=!1,t.use_score=0),t.subSendData=JSON.parse(JSON.stringify(t.sendData)),t.user_coupon=e.data.user_coupon[0],t.addressId||(t.user_address=e.data.user_address),t.calcTotalPrice(),t.total_price=e.data.total_price}else t.$http.toast(e.msg),t.is_failure=!1,e.data.address_id&&setTimeout((function(){uni.redirectTo({url:"/pages/user/address/edit?id=".concat(e.data.address_id,"&form=submit")})}),2e3)}))},getAddress:function(){var t=this;this.$http.request({url:this.$api.user.addressDetail,data:{id:this.addressId}}).then((function(e){0==e.code&&(e.data?t.user_address=e.data:t.user_address=[])}))},showPopup:function(t,e,i,a){this.shop_index=e,this.goods_index=i,this.coupon_goods_id=a,this.coupon_list=t,this.popupShow2=!0},hidePopup:function(){this.popupShow2=!1},useCoupon:function(t,e,i){var a=0,n=this.list[this.shop_index],o=n.same_goods_list[this.goods_index],s=this.coupon_list[e];if("notUse"==t){o.sale_price=1*o.total_price;var r=this;r.use_coupon_list.forEach((function(t,e){t.user_coupon_id==i&&r.use_coupon_list.splice(e,1)})),o.coupon_name="不使用优惠券",r.coupon_list.forEach((function(t,e){t.id==i&&(t.is_use="0",r.$set(r.coupon_list[e],"is_use","0"))})),r.getData()}else{var d=this,l=!1,u={};u["goods_id"]=d.coupon_goods_id,u["user_coupon_id"]=i,d.coupon_list.forEach((function(t,e){d.use_coupon_list.length>0&&d.use_coupon_list.forEach((function(t,e){if(t.user_coupon_id==i)return d.$http.toast("不能选择相同的优惠券"),l=!0,!1})),1==t.is_use&&(d.use_coupon_list.length>0&&d.use_coupon_list.forEach((function(e,i){e.user_coupon_id==t.id&&d.use_coupon_list.splice(i,1)})),t.is_use="0",d.$set(d.coupon_list[e],"is_use","0"))})),l||(d.coupon_list[e].is_use="1",d.$set(d.coupon_list[e],"is_use","1"),d.use_coupon_list.push(u),o.coupon_name=s.coupon_data.name),"1"==s.type?o.sale_price=1*(1*o.total_price*(1*s.coupon_data.discount)).toFixed(2):o.sale_price=1*(1*o.total_price-1*s.coupon_data.sub_price).toFixed(2),d.getData()}n.goods_list.forEach((function(t){a+=t.sale_price})),a+=1*n.express_price,n.total_price=a.toFixed(2),this.calcTotalPrice(),this.hidePopup()},calcTotalPrice:function(){var t=this;this.total_price=0,this.list.forEach((function(e){t.total_price=t.total_price+1*e.total_price})),this.total_price=this.total_price.toFixed(2)},chooseAddr:function(){this.is_failure&&("[]"==JSON.stringify(this.user_address)?uni.navigateTo({url:"/pages/user/address/edit?id=0&form=submit"}):uni.navigateTo({url:"/pages/user/address/list?name=cart"}))},btnPay:function(){var t=this;if(!this.is_request&&this.is_failure)return this.is_request=!0,this.is_auth?this.addressId||this.user_address.id?void this.$http.request({url:this.$api.order.doSubmitOrder,method:"post",data:{list:[{mch_id:0,goods_list:this.subSendData,remark:this.remark,delivery:[{send_type:"express"}],use_coupon_list:this.use_coupon_list}],user_address_id:this.addressId?this.addressId:this.user_address.id,use_score:this.is_checked?1:0,sign:this.sign,related_id:this.related_id,related_user_id:this.related_user_id}}).then((function(e){console.log("568res="+JSON.stringify(e)),t.is_request=!1,0==e.code?(uni.removeStorageSync("orderData"),uni.redirectTo({url:"/pages/order/pay?token=".concat(e.data.token,"&queue_id=").concat(e.data.queue_id)})):t.$http.toast(e.msg)})):(this.$http.toast("请添加收货地址!"),void(this.is_request=!1)):(uni.navigateTo({url:"/pages/public/authorization?form=order"}),void(this.is_request=!1))}}};e.default=o},"36f7":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=uni.getSystemInfoSync().statusBarHeight+"px",n={name:"ComStatusBar",data:function(){return{statusBarHeight:a}}};e.default=n},3940:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{on:{touchmove:function(e){e.stopPropagation(),e.preventDefault(),arguments[0]=e=t.$handleEvent(e)}}},[i("v-uni-view",{staticClass:"jx-popup-class jx-bottom-popup",class:[t.show?"jx-popup-show":""],style:{"z-index":t.z_index,background:t.bgcolor,"border-radius":t.borderRadius,height:t.height?t.height+"rpx":"auto"}},[t._t("default")],2),t.mask?i("v-uni-view",{staticClass:"jx-popup-mask",class:[t.show?"jx-mask-show":""],style:{"z-index":t.z_index2},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.handleClose.apply(void 0,arguments)}}}):t._e()],1)},o=[]},"41b8":function(t,e,i){"use strict";var a=i("a2b6"),n=i.n(a);n.a},"43f1":function(t,e,i){"use strict";i("a9e3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"jxBottomPopup",props:{mask:{type:Boolean,default:!0},show:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fff"},height:{type:Number,default:0},borderRadius:{type:String,default:"26rpx 26rpx 0 0"},z_index:{type:Number,default:999},z_index2:{type:Number,default:998}},methods:{handleClose:function(){this.show&&this.$emit("close",{})}}};e.default=a},"444c":function(t,e,i){"use strict";var a=i("6522"),n=i.n(a);n.a},"460d":function(t,e,i){"use strict";var a=i("8652"),n=i.n(a);n.a},6522:function(t,e,i){var a=i("ebee");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("befc6980",a,!0,{sourceMap:!1,shadowMode:!1})},"6bc0":function(t,e,i){var a=i("cbcf");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("2da195c2",a,!0,{sourceMap:!1,shadowMode:!1})},"6dac":function(t,e,i){"use strict";var a=i("9db0"),n=i.n(a);n.a},"6e1b":function(t,e,i){"use strict";i.r(e);var a=i("d1d3"),n=i("f82f");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("6dac");var s,r=i("f0c5"),d=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"31d08a7f",null,!1,a["a"],s);e["default"]=d.exports},7123:function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var a={comNavBar:i("c410").default,comBottomPopup:i("7b35").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"container"},[i("com-nav-bar",{attrs:{"left-icon":"back",title:"提交订单","status-bar":!0,"background-color":"#ffffff",border:!1,color:"#000000"},on:{clickLeft:function(e){arguments[0]=e=t.$handleEvent(e),t.back.apply(void 0,arguments)}}}),i("v-uni-view",{staticClass:"tui-box"},[i("tui-list-cell",{attrs:{arrow:!0,last:!0,radius:!0},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.chooseAddr.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"tui-address"},[0!=t.user_address.length?i("v-uni-view",[i("v-uni-view",{staticClass:"tui-userinfo"},[i("v-uni-text",{staticClass:"tui-name"},[t._v(t._s(t.user_address.name))]),t._v(t._s(t.user_address.mobile))],1),i("v-uni-view",{staticClass:"tui-addr"},[i("v-uni-text",[t._v(t._s(t.user_address.province)+t._s(t.user_address.city)+t._s(t.user_address.district)+t._s(t.user_address.detail))])],1)],1):i("v-uni-view",{staticClass:"tui-none-addr"},[i("v-uni-text",[t._v("请添加收货地址")])],1)],1)],1),t._l(t.list,(function(e,a){return i("v-uni-view",{key:a,staticClass:"tui-top tui-goods-info"},[t._l(e.same_goods_list,(function(e,n){return i("v-uni-view",{key:n,staticClass:"item-goods"},[t._l(e.goods_list,(function(e,a){return i("v-uni-view",{key:a},[i("tui-list-cell",{attrs:{hover:!1,padding:"0"}},[i("v-uni-view",{staticClass:"tui-goods-item"},[i("v-uni-image",{staticClass:"tui-goods-img",attrs:{src:e.goods_attr.pic_url?e.goods_attr.pic_url:e.goods_attr.cover_pic}}),i("v-uni-view",{staticClass:"tui-goods-center"},[i("v-uni-view",{staticClass:"tui-goods-name"},[t._v(t._s(e.name))]),i("v-uni-view",{staticClass:"tui-goods-attr"},[t._v(t._s(t.groupName(e.attr_list)))])],1),i("v-uni-view",{staticClass:"tui-price-right"},[i("v-uni-view",[t._v("￥"+t._s(e.unit_price))]),i("v-uni-view",[t._v("x"+t._s(e.num))])],1)],1)],1)],1)})),0!=e.coupon_list.length?i("v-uni-view",{staticStyle:{"border-bottom":"2rpx solid #e8e8e8"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.showPopup(e.coupon_list,a,n,e.goods_id)}}},[i("tui-list-cell",{attrs:{arrow:t.hasCoupon,hover:t.hasCoupon}},[i("v-uni-view",{staticClass:"tui-padding tui-flex"},[i("v-uni-view",[t._v("优惠券")]),i("v-uni-view",{class:{"tui-color-red":t.hasCoupon},style:{color:t.hasCoupon?t.textColor:""}},[t._v(t._s(e.coupon_name))])],1)],1)],1):t._e()],2)})),i("tui-list-cell",{attrs:{hover:!1}},[i("v-uni-view",{staticClass:"tui-padding tui-flex"},[i("v-uni-view",[t._v("配送方式")]),"express"==e.delivery.send_type?i("v-uni-view",[t._v("快递配送")]):t._e()],1)],1),i("tui-list-cell",{attrs:{hover:!1}},[i("v-uni-view",{staticClass:"tui-padding tui-flex"},[i("v-uni-view",[t._v("配送费")]),t.list?i("v-uni-view",{style:{color:t.textColor}},[t._v("+¥"+t._s(e.express_price))]):t._e()],1)],1),0!=e.total_full_relief_price?i("tui-list-cell",{attrs:{hover:!1}},[i("v-uni-view",{staticClass:"tui-padding tui-flex"},[i("v-uni-view",[t._v("满额减免")]),t.list?i("v-uni-view",{style:{color:t.textColor}},[t._v("-¥"+t._s(e.total_full_relief_price||0))]):t._e()],1)],1):t._e(),i("tui-list-cell",{attrs:{hover:!1,lineLeft:!1,padding:"0"}},[i("v-uni-view",{staticClass:"tui-remark-box tui-padding tui-flex"},[i("v-uni-view",[t._v("订单备注")]),i("v-uni-input",{staticClass:"tui-remark",attrs:{type:"text",placeholder:"选填: 请先和商家协商一致","placeholder-class":"tui-phcolor"},model:{value:t.remark,callback:function(e){t.remark=e},expression:"remark"}})],1)],1),i("tui-list-cell",{attrs:{hover:!1,last:!0}},[i("v-uni-view",{staticClass:"tui-padding tui-flex tui-total-flex"},[i("v-uni-view",{staticClass:"tui-flex-end tui-color-red",style:{color:t.textColor}},[i("v-uni-view",{staticClass:"tui-black"},[t._v("小计：")]),i("v-uni-view",{staticClass:"tui-size-26"},[t._v("￥")]),i("v-uni-view",{staticClass:"tui-price-large"},[t._v(t._s(e.total_price))])],1)],1)],1)],2)})),1==t.score_enable?i("v-uni-view",{staticClass:"use-points flex flex-y-center flex-x-between"},[i("v-uni-view",[t._v("使用积分")]),i("v-uni-switch",{staticClass:"points-switch",attrs:{checked:t.is_checked,color:t.textColor},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.use.apply(void 0,arguments)}}})],1):t._e()],2),i("com-bottom-popup",{attrs:{show:t.popupShow2},on:{close:function(e){arguments[0]=e=t.$handleEvent(e),t.hidePopup.apply(void 0,arguments)}}},[i("v-uni-scroll-view",{staticStyle:{"max-height":"1000rpx"},attrs:{"scroll-y":"true"}},[i("v-uni-view",{staticClass:"coupon-box"},[i("v-uni-view",{staticClass:"coupon-title2"},[t._v("优惠券"),i("v-uni-view",{staticClass:"coupon-icon iconfont icon-guanbi",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.hidePopup.apply(void 0,arguments)}}})],1),i("v-uni-view",{staticStyle:{height:"120rpx"}}),i("v-uni-view",{staticClass:"coupon-content"},t._l(t.coupon_list,(function(e,a){return i("v-uni-view",{key:a,staticClass:"coupon-item",style:{background:"url("+t.couponImg+")no-repeat"}},[i("v-uni-view",{staticClass:"coupon-item-left"},[i("v-uni-view",{staticClass:"coupon-item-price"},[2==e.type?[i("v-uni-text",{staticClass:"price-symbol"},[t._v("¥")]),i("v-uni-text",{staticClass:"price-int"},[t._v(t._s(e.sub_price))])]:[i("v-uni-text",{staticClass:"price-int"},[t._v(t._s(10*e.discount))]),i("v-uni-text",[t._v("折")])]],2),i("v-uni-view",{staticClass:"coupon-item-condition"},[t._v("满"+t._s(e.coupon_min_price)+"可用")])],1),i("v-uni-view",{staticClass:"coupon-item-right"},[i("v-uni-view",{staticClass:"coupon-item-name"},[t._v(t._s(e.coupon_data.name))]),i("v-uni-view",{staticClass:"coupon-item-time"},[1==e.coupon_data.expire_type?i("v-uni-view",[t._v("领取"+t._s(e.coupon_data.expire_day)+"天后过期")]):i("v-uni-view",[t._v(t._s(e.coupon_data.begin_at)+"~"+t._s(e.coupon_data.end_at))]),0==e.is_use?i("v-uni-view",{staticClass:"receive",style:{background:t.textColor},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.useCoupon("use",a,e.id)}}},[t._v("使用")]):i("v-uni-view",{staticClass:"receive receive-col",style:{border:"1px solid"+t.textColor,color:t.textColor},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.useCoupon("notUse",a,e.id)}}},[t._v("不使用")])],1)],1)],1)})),1)],1)],1)],1),i("v-uni-view",{staticClass:"tui-safe-area"}),i("v-uni-view",{staticClass:"tui-tabbar"},[i("v-uni-view",{staticClass:"tui-tabbar-box flex flex-x-between flex-y-center"},[i("v-uni-view",{staticClass:"tui-flex-end tui-color-red tui-pr-20",style:{color:t.textColor}},[i("v-uni-view",{staticClass:"tui-black"},[t._v("合计:")]),i("v-uni-view",{staticClass:"tui-size-26"},[t._v("￥")]),i("v-uni-view",{staticClass:"tui-price-large"},[t._v(t._s(t.total_price))])],1),i("v-uni-view",{staticClass:"tui-pr25",style:{background:t.textColor},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.btnPay.apply(void 0,arguments)}}},[t._v("去支付")])],1)],1)],1)},o=[]},"7b35":function(t,e,i){"use strict";i.r(e);var a=i("3940"),n=i("2b25");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("0a2f");var s,r=i("f0c5"),d=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"7ae8b0a6",null,!1,a["a"],s);e["default"]=d.exports},"7e82":function(t,e,i){"use strict";i.r(e);var a=i("e991"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"83ef":function(t,e,i){"use strict";i("a9e3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"jxListCell",props:{arrow:{type:Boolean,default:!1},hover:{type:Boolean,default:!0},lineLeft:{type:Boolean,default:!0},lineRight:{type:Boolean,default:!1},padding:{type:String,default:"26rpx 30rpx"},last:{type:Boolean,default:!1},radius:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fff"},size:{type:Number,default:32},color:{type:String,default:"#333"},index:{type:Number,default:0}},methods:{handleClick:function(){this.$emit("click",{index:this.index})}}};e.default=a},8652:function(t,e,i){var a=i("fbdf");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("15a9f5a8",a,!0,{sourceMap:!1,shadowMode:!1})},9567:function(t,e,i){"use strict";i.r(e);var a=i("7123"),n=i("a2af");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("444c");var s,r=i("f0c5"),d=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"0432a4d5",null,!1,a["a"],s);e["default"]=d.exports},"99a5":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'.jx-list-cell[data-v-56e2521a]{position:relative;width:100%;-webkit-box-sizing:border-box;box-sizing:border-box;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.jx-radius[data-v-56e2521a]{-webkit-border-radius:%?20?%;border-radius:%?20?%;overflow:hidden}.jx-cell-hover[data-v-56e2521a]{background:#f7f7f9!important}.jx-list-cell[data-v-56e2521a]::after{content:"";position:absolute;\n\t/* border-bottom: 1rpx solid #eaeef1; */-webkit-transform:scaleY(.5);transform:scaleY(.5);bottom:0;right:0;left:0}.jx-line-left[data-v-56e2521a]::after{left:%?30?%!important}.jx-line-right[data-v-56e2521a]::after{right:%?30?%!important}.jx-cell-last[data-v-56e2521a]::after{border-bottom:0!important}.jx-list-cell.jx-cell-arrow[data-v-56e2521a]:before{content:" ";height:11px;width:11px;border-width:2px 2px 0 0;border-color:#b2b2b2;border-style:solid;-webkit-transform:matrix(.5,.5,-.5,.5,0,0);transform:matrix(.5,.5,-.5,.5,0,0);position:absolute;top:50%;margin-top:-7px;right:%?30?%}',""]),t.exports=e},"9db0":function(t,e,i){var a=i("af90");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("0b5b5a3a",a,!0,{sourceMap:!1,shadowMode:!1})},a2af:function(t,e,i){"use strict";i.r(e);var a=i("32f6"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},a2b6:function(t,e,i){var a=i("99a5");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("0954bd92",a,!0,{sourceMap:!1,shadowMode:!1})},af90:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.uni-status-bar[data-v-31d08a7f]{width:%?750?%;height:20px}',""]),t.exports=e},b165:function(t,e,i){"use strict";i.r(e);var a=i("83ef"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},be65:function(t,e,i){"use strict";i.r(e);var a=i("d80f"),n=i("b165");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("41b8");var s,r=i("f0c5"),d=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"56e2521a",null,!1,a["a"],s);e["default"]=d.exports},c410:function(t,e,i){"use strict";i.r(e);var a=i("0563"),n=i("7e82");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("460d");var s,r=i("f0c5"),d=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"431c4463",null,!1,a["a"],s);e["default"]=d.exports},cbcf:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,".jx-bottom-popup[data-v-7ae8b0a6]{width:100%;position:fixed;left:0;right:0;bottom:0;\n\t/* z-index: 999; */visibility:hidden;-webkit-transform:translate3d(0,100%,0);transform:translate3d(0,100%,0);-webkit-transform-origin:center;transform-origin:center;-webkit-transition:all .3s ease-in-out;transition:all .3s ease-in-out;min-height:%?20?%}.jx-popup-show[data-v-7ae8b0a6]{-webkit-transform:translateZ(0);transform:translateZ(0);visibility:visible}.jx-popup-mask[data-v-7ae8b0a6]{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.6);\n\t/* z-index: 998; */-webkit-transition:all .3s ease-in-out;transition:all .3s ease-in-out;opacity:0;visibility:hidden}.jx-mask-show[data-v-7ae8b0a6]{opacity:1;visibility:visible}",""]),t.exports=e},d1d3:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"uni-status-bar",style:{height:t.statusBarHeight}},[t._t("default")],2)},o=[]},d80f:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"jx-cell-class jx-list-cell",class:{"jx-cell-arrow":t.arrow,"jx-cell-last":t.last,"jx-line-left":t.lineLeft,"jx-line-right":t.lineRight,"jx-radius":t.radius},style:{background:t.bgcolor,fontSize:t.size+"rpx",color:t.color,padding:t.padding},attrs:{"hover-class":t.hover?"jx-cell-hover":"","hover-stay-time":150},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.handleClick.apply(void 0,arguments)}}},[t._t("default")],2)},o=[]},e991:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"ComNavBar",props:{title:{type:String,default:""},leftText:{type:String,default:""},rightText:{type:String,default:""},leftIcon:{type:String,default:""},rightIcon:{type:String,default:""},fixed:{type:[Boolean,String],default:!1},color:{type:String,default:"#000000"},backgroundColor:{type:String,default:"#FFFFFF"},statusBar:{type:[Boolean,String],default:!1},shadow:{type:[String,Boolean],default:!1},border:{type:[String,Boolean],default:!0}},mounted:function(){uni.report&&""!==this.title&&uni.report("title",this.title)},methods:{onClickLeft:function(){var t=getCurrentPages();1==t.length?uni.redirectTo({url:"/pages/index/index"}):this.$emit("clickLeft")},onClickRight:function(){this.$emit("clickRight")}}};e.default=a},ebee:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'.container[data-v-0432a4d5]{padding-bottom:%?98?%}.tui-box[data-v-0432a4d5]{padding:%?20?% %?20?% %?118?%;-webkit-box-sizing:border-box;box-sizing:border-box}.tui-address[data-v-0432a4d5]{min-height:%?80?%;padding:%?10?% 0;-webkit-box-sizing:border-box;box-sizing:border-box;position:relative}.tui-userinfo[data-v-0432a4d5]{font-size:%?30?%;font-weight:500;line-height:%?30?%;padding-bottom:%?12?%}.tui-name[data-v-0432a4d5]{padding-right:%?40?%}.tui-addr[data-v-0432a4d5]{font-size:%?24?%;word-break:break-all;padding-right:%?25?%}.tui-addr-tag[data-v-0432a4d5]{padding:%?5?% %?8?%;-webkit-flex-shrink:0;flex-shrink:0;background:#eb0909;color:#fff;display:-webkit-inline-box;display:-webkit-inline-flex;display:inline-flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:%?25?%;line-height:%?25?%;-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:0 center;transform-origin:0 center;-webkit-border-radius:%?6?%;border-radius:%?6?%}.tui-bg-img[data-v-0432a4d5]{position:absolute;width:100%;height:%?8?%;left:0;bottom:0;background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAL0AAAAECAMAAADszM6/AAAAOVBMVEUAAAAVqfH/fp//vWH/vWEVqfH/fp8VqfH/fp//vWEVqfH/fp8VqfH/fp//vWH/vWEVqfH/fp//vWHpE7b6AAAAEHRSTlMA6urqqlVVFRUVq6upqVZUDT4vVAAAAEZJREFUKM/t0CcOACAQRFF6r3v/w6IQJGwyDsPT882IQzQE0E3chToByjG5LwMgLZN3TQATmdypCciBya0cgOT3/h//9PgF49kd+6lTSIIAAAAASUVORK5CYII=") repeat}.tui-top[data-v-0432a4d5]{margin-top:%?20?%;overflow:hidden;-webkit-border-radius:%?20?%;border-radius:%?20?%}.tui-goods-title[data-v-0432a4d5]{font-size:%?28?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.tui-padding[data-v-0432a4d5]{-webkit-box-sizing:border-box;box-sizing:border-box}.tui-goods-item[data-v-0432a4d5]{width:100%;padding:%?20?% %?30?%;-webkit-box-sizing:border-box;box-sizing:border-box;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.tui-goods-img[data-v-0432a4d5]{width:%?180?%;height:%?180?%;display:block;-webkit-flex-shrink:0;flex-shrink:0}.tui-goods-center[data-v-0432a4d5]{-webkit-box-flex:1;-webkit-flex:1;flex:1;padding:%?20?% %?8?%;-webkit-box-sizing:border-box;box-sizing:border-box}.tui-goods-name[data-v-0432a4d5]{max-width:%?310?%;word-break:break-all;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;font-size:%?26?%;line-height:%?32?%}.tui-goods-attr[data-v-0432a4d5]{font-size:%?22?%;color:#888;line-height:%?32?%;padding-top:%?20?%;word-break:break-all;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2}.tui-price-right[data-v-0432a4d5]{text-align:right;font-size:%?24?%;color:#888;line-height:%?30?%;padding-top:%?20?%}.tui-flex[data-v-0432a4d5]{width:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;font-size:%?26?%}.tui-total-flex[data-v-0432a4d5]{-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end}.tui-color-red[data-v-0432a4d5],\n.tui-invoice-text[data-v-0432a4d5]{color:#bc0100;padding-right:%?30?%}.tui-balance[data-v-0432a4d5]{font-size:%?28?%;font-weight:500}.tui-black[data-v-0432a4d5]{color:#222;line-height:%?30?%}.tui-gray[data-v-0432a4d5]{color:#888;font-weight:400}.tui-light-dark[data-v-0432a4d5]{color:#666}.tui-goods-price[data-v-0432a4d5]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding-top:%?20?%}.tui-size-26[data-v-0432a4d5]{font-size:%?26?%;line-height:%?26?%}.tui-price-large[data-v-0432a4d5]{font-size:%?34?%;line-height:%?32?%;font-weight:600}.tui-flex-end[data-v-0432a4d5]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:end;-webkit-align-items:flex-end;align-items:flex-end;padding-right:0}.tui-phcolor[data-v-0432a4d5]{color:#b3b3b3;font-size:%?26?%}\n.tui-remark-box[data-v-0432a4d5]{padding:%?26?% %?30?%}\n.tui-remark[data-v-0432a4d5]{-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?26?%;padding-left:%?64?%}.tui-scale-small[data-v-0432a4d5]{-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:100% center;transform-origin:100% center}.tui-scale-small .wx-switch-input[data-v-0432a4d5]{margin:0!important}\n[data-v-0432a4d5] uni-switch .uni-switch-input{margin-right:0!important}\n.tui-tabbar[data-v-0432a4d5]{width:100%;height:%?98?%;background:#fff;position:fixed;left:0;bottom:0;font-size:%?26?%;-webkit-box-shadow:0 0 1px rgba(0,0,0,.3);box-shadow:0 0 1px rgba(0,0,0,.3);padding-bottom:env(safe-area-inset-bottom);z-index:99}.tui-tabbar-box[data-v-0432a4d5]{padding:0 %?20?% 0 %?42?%;height:100%}.tui-pr-30[data-v-0432a4d5]{padding-right:%?30?%}.tui-pr-20[data-v-0432a4d5]{padding-right:%?20?%}.tui-none-addr[data-v-0432a4d5]{height:%?80?%;padding-left:%?5?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.tui-addr-img[data-v-0432a4d5]{width:%?36?%;height:%?46?%;display:block;margin-right:%?15?%}.tui-pr25[data-v-0432a4d5]{background:-webkit-linear-gradient(120deg,#d6100d,#f14822);background:linear-gradient(-30deg,#d6100d,#f14822);width:%?208?%;height:%?78?%;-webkit-border-radius:%?50?%;border-radius:%?50?%;color:#fff;font-size:11pt;text-align:center;line-height:%?78?%;position:relative;margin:0}.tui-safe-area[data-v-0432a4d5]{height:%?1?%;padding-bottom:env(safe-area-inset-bottom)}\n\n/*优惠券底部选择弹层*/.coupon-box[data-v-0432a4d5]{padding:0 %?20?% %?80?%;overflow:hidden}.coupon-title2[data-v-0432a4d5]{height:%?100?%;text-align:center;font-size:12pt;color:#000;position:fixed;background:#fff;width:100%;left:0;letter-spacing:%?4?%;font-weight:700;z-index:99;-webkit-border-radius:%?30?%;border-radius:%?30?%;line-height:%?100?%}.coupon-icon[data-v-0432a4d5]{position:absolute;right:%?30?%;top:%?4?%;color:#acacac;font-size:%?28?%}.coupon-tips[data-v-0432a4d5]{margin:%?50?% 0 %?30?%;color:#000;font-size:11pt}.coupon-item[data-v-0432a4d5]{width:100%;height:%?195?%;display:-webkit-box;display:-webkit-flex;display:flex;color:#000;margin-bottom:%?20?%;background-size:cover!important}.coupon-item-left[data-v-0432a4d5]{width:%?248?%;height:100%;color:#fff;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.price-symbol[data-v-0432a4d5]{font-size:14pt}.price-int[data-v-0432a4d5]{font-size:18pt;font-weight:700;margin:0 %?2?% 0 %?4?%}.coupon-item-right[data-v-0432a4d5]{-webkit-box-flex:1;-webkit-flex:1;flex:1;height:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-justify-content:space-around;justify-content:space-around;position:relative;padding:%?20?% 0 %?10?% %?20?%;-webkit-box-sizing:border-box;box-sizing:border-box}.coupon-item-name[data-v-0432a4d5]{font-size:11pt}.coupon-item-condition[data-v-0432a4d5]{font-size:10pt}.coupon-item-time[data-v-0432a4d5]{font-size:9pt;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;padding-right:%?12?%}.receive[data-v-0432a4d5]{background:#bc0100;color:#fff;padding:%?6?% %?30?%;-webkit-border-radius:%?40?%;border-radius:%?40?%;font-size:9pt}.receive-col[data-v-0432a4d5]{background:#fff;border:1px solid #bc0100;color:#bc0100}.received[data-v-0432a4d5]{position:absolute;top:0;right:0;font-size:%?60?%;color:#bc0100;line-height:%?60?%;height:%?60?%}\n\n/*优惠券底部选择弹层*/.use-points[data-v-0432a4d5]{background:#fff;margin-top:%?40?%;-webkit-border-radius:%?20?%;border-radius:%?20?%;padding:%?10?% %?30?%;font-size:%?26?%;color:#000}.points-switch[data-v-0432a4d5]{-webkit-transform:scale(.7);transform:scale(.7)}\n\n/* 0.2 商品列表样式 */.item-goods[data-v-0432a4d5]{-webkit-border-radius:%?20?%;border-radius:%?20?%;margin-bottom:%?20?%}',""]),t.exports=e},f82f:function(t,e,i){"use strict";i.r(e);var a=i("36f7"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},fbdf:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.uni-nav-bar-text[data-v-431c4463]{font-size:%?32?%}.uni-nav-bar-right-text[data-v-431c4463]{font-size:%?28?%}.uni-navbar[data-v-431c4463]{width:%?750?%}.uni-navbar__content[data-v-431c4463]{position:relative;width:%?750?%;background-color:#fff;overflow:hidden}.uni-navbar__content_view[data-v-431c4463]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row}.uni-navbar__header[data-v-431c4463]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;width:%?750?%;height:44px;line-height:44px;font-size:16px}.uni-navbar__header-btns[data-v-431c4463]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:nowrap;flex-wrap:nowrap;width:%?120?%;padding:0 6px;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.uni-navbar__header-btns-left[data-v-431c4463]{display:-webkit-box;display:-webkit-flex;display:flex;width:%?150?%;-webkit-box-pack:start;-webkit-justify-content:flex-start;justify-content:flex-start}.uni-navbar__header-btns-right[data-v-431c4463]{display:-webkit-box;display:-webkit-flex;display:flex;width:%?150?%;padding-right:%?30?%;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end}.uni-navbar__header-container[data-v-431c4463]{-webkit-box-flex:1;-webkit-flex:1;flex:1}.uni-navbar__header-container-inner[data-v-431c4463]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-flex:1;-webkit-flex:1;flex:1;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:%?28?%}.uni-navbar__placeholder-view[data-v-431c4463]{height:44px}.uni-navbar--fixed[data-v-431c4463]{position:fixed;z-index:998}.uni-navbar--shadow[data-v-431c4463]{-webkit-box-shadow:0 1px 6px #ccc;box-shadow:0 1px 6px #ccc}.uni-navbar--border[data-v-431c4463]{border-bottom-width:%?1?%;border-bottom-style:solid;border-bottom-color:#f3f3f3}',""]),t.exports=e}}]);