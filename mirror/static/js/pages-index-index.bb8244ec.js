(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-index-index"],{"0315":function(t,a,e){"use strict";e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var i={search:e("cafc").default,banners:e("199d").default,rubik:e("b140").default,commodity:e("e77c").default,mainTabbar:e("aa92").default},n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",{staticClass:"shouye-app"},[e("v-uni-view",{staticClass:"customer_service",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.linkService.apply(void 0,arguments)}}},[e("v-uni-image",{attrs:{src:t.img_url+"/service_logo.png",mode:""}})],1),t._l(t.dataForm,(function(a,i){return e("v-uni-view",{key:i},["search"==a.id?e("v-uni-view",{staticClass:"header"},[e("v-uni-view",{staticClass:"search_box"},[t.mall_logo?t._e():e("v-uni-view",{staticClass:"checksao",staticStyle:{width:"15%",background:"#fff"}},[e("v-uni-image",{staticStyle:{width:"100rpx",height:"90rpx",display:"block",margin:"5rpx auto 0"},attrs:{src:t.img_url+"/fillShop.png",mode:""}})],1),t.mall_logo?e("v-uni-view",{staticClass:"checksao",staticStyle:{width:"15%",background:"#fff"}},[e("v-uni-image",{staticStyle:{width:"100rpx",height:"90rpx",display:"block",margin:"5rpx auto 0"},attrs:{src:t.mall_pic,mode:""}})],1):t._e(),e("v-uni-view",{staticClass:"search",staticStyle:{width:"70%"},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.navTo("/pages/search/search")}}},[e("search",{attrs:{message:a.data.placeholder,textAlign:a.data.textPosition,frameColor:a.data.background,innerFrameColor:a.data.color,textColor:a.data.textColor,borderRadius:a.data.radius}})],1),e("v-uni-view",{staticClass:"checksao",staticStyle:{width:"15%",background:"#fff"},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.scanSome.apply(void 0,arguments)}}},[e("v-uni-image",{staticStyle:{width:"50rpx",height:"50rpx",display:"block",margin:"5rpx auto 0"},attrs:{src:t.img_url+"/scan.png",mode:""}}),e("v-uni-text",{staticStyle:{display:"block","font-size":"24rpx",width:"100%","text-align":"center"}},[t._v("扫一扫")])],1)],1)],1):t._e(),"banner"==a.id?e("v-uni-view",{staticClass:"bannersBox"},[e("banners",{attrs:{bannerData:a.data}})],1):t._e(),"rubik"==a.id?e("v-uni-view",{staticClass:"adBoxs"},[e("rubik",{attrs:{types:a.data.style,"image-data":a.data.list,hotspotData:a.data.hotspot}})],1):t._e(),"nav"==a.id?e("nav-icon",{staticClass:"navIcon_style",attrs:{list:a.data.navs,textColor:a.data.color,rowNums:a.data.rows,background:a.data.background,listNums:a.data.columns,standID:t.stands_mall_id}}):t._e(),"goods"==a.id?e("v-uni-view",{staticStyle:{background:"#FFFFFF","border-radius":"10rpx","margin-bottom":"80rpx"}},[e("commodity",{attrs:{listStyle:a.data.listStyle,showGoodsName:a.data.showGoodsName,showGoodsOriginalPrice:a.data.showGoodsOriginalPrice,showGoodsPrice:a.data.showGoodsPrice,showGoodsLevelPrice:a.data.showGoodsLevelPrice,originalPriceLabel:a.data.originalPriceLabel,priceLabel:a.data.priceLabel,levelPriceLabel:a.data.levelPriceLabel,showBuyBtn:a.data.showBuyBtn,subscriptIcon:a.data.goodsTagPicUrl,showGoodsTag:a.data.showGoodsTag,buyBtnStyle:a.data.buyBtnStyle,buyBtns:a.data.buyBtn,buyBtnText:a.data.buyBtnText,displayStyle:a.data.goodsStyle,productData:t.goodsList,buyBtnPic:a.data.buyBtnPic,buttonColor:a.data.buttonColor}})],1):t._e()],1)})),e("main-tabbar")],2)},o=[]},"0bfc":function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,".shouye-app[data-v-c13929ec]{width:100%;overflow:hidden}.customer_service[data-v-c13929ec]{width:%?100?%;height:%?100?%;position:fixed;z-index:999;right:%?40?%;bottom:%?200?%;border-radius:50%}.customer_service uni-image[data-v-c13929ec]{width:%?100?%;height:%?100?%;display:block;border-radius:50%}.buttonWeapp[data-v-c13929ec]{width:%?100?%;height:%?100?%;display:block;border-radius:50%;background:url(https://dev.mingyuanriji.cn/web/static/service_logo.png) no-repeat;background-size:cover}.header[data-v-c13929ec]{width:100%;overflow:hidden;position:fixed;z-index:999}.search_box[data-v-c13929ec]{width:100%;z-index:99;display:-webkit-box;display:-webkit-flex;display:flex}.bannersBox[data-v-c13929ec]{position:relative;margin-top:%?96?%}.adBoxs[data-v-c13929ec]{overflow:hidden}.navIcon_style[data-v-c13929ec]{width:100%;overflow:hidden}",""]),t.exports=a},"0e04":function(t,a,e){"use strict";e.r(a);var i=e("2717"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a},2717:function(t,a,e){"use strict";e("4160"),e("c975"),e("a9e3"),e("d3b7"),e("159b"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i={name:"jxTabbar",props:{current:{type:Number,default:4},color:{type:String,default:"#000000"},selectedColor:{type:String,default:"#5677FC"},hump:{type:Boolean,default:!1},isFixed:{type:Boolean,default:!0},tabBar:{type:Array,default:function(){return[]}},badgeColor:{type:String,default:"#fff"},badgeBgColor:{type:String,default:"#F74D54"},unlined:{type:Boolean,default:!1}},data:function(){return{tabBarItems:null,is_show_tabbar:!1,backgroundColor:"",is_shadow:!1}},mounted:function(){var t=this,a=getCurrentPages();if(a.length>0)var e=a[a.length-1].route;var i,n=uni.getStorageSync("mall_config")?JSON.parse(uni.getStorageSync("mall_config")).navbar:null;n?"plugins/short-video/index"==e?(this.backgroundColor="transparent",this.is_shadow=!1):(this.backgroundColor=n.bottom_background_color,this.is_shadow=n.shadow):this.initMall().then((function(a){t.backgroundColor=a.bottom_background_color,t.is_shadow=a.shadow,i=a,i.navs.forEach((function(a,i){a.index=i,-1!=a.url.indexOf(e)&&(a.active=!0,t.is_show_tabbar=!0)})),t.tabBarItems=i.navs}));var o=this.tabBar.length>0?this.tabBar:n.navs;o.forEach((function(a,i){a.index=i,-1!=a.url.indexOf(e)&&(a.active=!0,t.is_show_tabbar=!0)})),"pages/shop/shop"==e&&(this.is_show_tabbar=!0),this.tabBarItems=o;this.$http.getUrlParam("mall_id")},methods:{tabbarSwitch:function(t,a,e,i){"/plugins/short-video/index"==e&&(e+="?from=short-video"),uni.redirectTo({url:e})},initMall:function(){var t=this,a=uni.getStorageSync("mall_config")?JSON.parse(uni.getStorageSync("mall_config")).navbar:null;return new Promise((function(e,i){a?e(a):t.$http.request({url:t.$api.index.config}).then((function(t){if(0===t.code){var a=t.data,i=(a.cat_style,a.copyright,a.mall_setting,a.navbar);a.page_title;uni.setStorageSync("mall_config",JSON.stringify(t.data)),e(i)}}))}))}}};a.default=i},"36a2":function(t,a,e){"use strict";e.r(a);var i=e("4b05"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a},"4b05":function(t,a,e){"use strict";var i=e("4ea4");e("99af"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;e("7b0a");var n=i(e("cafc")),o=i(e("199d")),r=i(e("b140")),s=i(e("26e6")),d=e("68bf"),l={components:{search:n.default,banners:o.default,rubik:r.default,navIcon:s.default},data:function(){return{img_url:this.$api.img_url,serviceLink:"",webapp:{nickName:"",avatarUrl:"",province:"",city:"",pageUrl:"",pageTitle:"",phone:""},dataForm:[],page:1,page_count:"",goodsList:[],stands_mall_id:"",mall_logo:!1,mall_pic:""}},onLoad:function(){this.$wechatSdk.initJssdk((function(t){})),this.getService(),this.getData(),null!=JSON.parse(uni.getStorageSync("mall_config")).mall_log&&(this.mall_logo=!0,this.mall_pic=JSON.parse(uni.getStorageSync("mall_config")).mall_log)},methods:{getService:function(){var t=this;this.$http.request({url:this.$api.moreShop.getservice,method:"POST",showLoading:!0}).then((function(a){0==a.code&&(t.serviceLink=a.data)}))},linkService:function(){location.href=this.serviceLink},goService:function(){if(uni.getStorageSync("userInfo")){var t=JSON.parse(uni.getStorageSync("userInfo"));this.webapp=JSON.stringify({nickName:t.nickname,avatarUrl:t.avatar,province:"",city:"",pageUrl:"pages/index/index",pageTitle:"",phone:t.mobile})}},navTo:function(t){uni.navigateTo({url:t})},scanSome:function(){d.scanQRCode({needResult:0,scanType:["qrCode","barCode"],success:function(t){t.resultStr}})},getData:function(){var t=this,a=getCurrentPages(),e=(a[a.length-1].route,a[a.length-1].options);e.stands_mall_id?e.stands_mall_id=e.stands_mall_id:uni.getStorageSync("stands_mall_id")&&(e.stands_mall_id=uni.getStorageSync("stands_mall_id")),uni.setStorageSync("stands_mall_id",e.stands_mall_id),e.stands_mall_id?this.$http.request({url:"https://mirror.mingyuanriji.cn/web/index.php?r=api/index/index",data:{stands_mall_id:e.stands_mall_id,page:this.page},showLoading:!0}).then((function(a){if(0==a.code){t.$wechatSdk.initShareUrl(a.data.share_data,"source=1"),t.dataForm=a.data.page_data,t.stands_mall_id=a.data.share_data.stands_mall_id;for(var e=0;e<t.dataForm.length;e++)if("goods"==t.dataForm[e].id){if(0==t.dataForm[e].data.list.length)return!1;var i=t.dataForm[e].data.list,n=t.goodsList.concat(i);t.goodsList=n,t.page_count=a.data.goods_page.page_count}}})):this.$http.request({url:this.$api.index2,showLoading:!0}).then((function(a){if(0==a.code){t.$wechatSdk.initShareUrl(a.data.share_data,"source=1"),t.dataForm=a.data.page_data;for(var e=0;e<t.dataForm.length;e++)"goods"==t.dataForm[e].id&&(t.goodsList=t.dataForm[e].data.list)}}))}},onReachBottom:function(){var t=getCurrentPages(),a=(t[t.length-1].route,t[t.length-1].options);if(a.stands_mall_id){if(this.page==this.page_count)return!1;this.page=this.page+1,this.getData()}}};a.default=l},"63a8":function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,'.jx-tabbar[data-v-1373db16]{width:100%;height:%?100?%;\ndisplay:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;\nposition:relative}.jx-tabbar-fixed[data-v-1373db16]{position:fixed;z-index:99999;left:0;bottom:0;\npadding-bottom:env(safe-area-inset-bottom);\nbox-sizing:initial;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row}.jx-tabbar[data-v-1373db16]::before{\ncontent:"";width:100%;\n\t/* border-top: 1rpx solid #dadada; */position:absolute;top:0;left:0;-webkit-transform:scaleY(.5);transform:scaleY(.5);-webkit-transform-origin:0 100%;transform-origin:0 100%\n}.jx-tabbar-item[data-v-1373db16]{height:100%;\n-webkit-box-flex:1;-webkit-flex:1;flex:1;display:-webkit-box;display:-webkit-flex;display:flex;text-align:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;box-sizing:border-box;\nposition:relative;padding:%?10?% 0}.jx-icon-box[data-v-1373db16]{position:relative}.jx-item-hump[data-v-1373db16]{height:%?98?%;z-index:2}.jx-tabbar-icon[data-v-1373db16]{width:%?48?%;height:%?48?%;\ndisplay:block\n}.jx-hump-box[data-v-1373db16]{width:%?120?%;height:%?120?%;background-color:#fff;position:absolute;left:50%;-webkit-transform:translateX(-50%);transform:translateX(-50%);top:%?-50?%;border-radius:50%;z-index:1}.jx-hump-box[data-v-1373db16]::before{\ncontent:"";height:200%;width:200%;border:%?1?% solid #b2b2b2;position:absolute;top:0;left:0;-webkit-transform:scale(.5) translateZ(0);transform:scale(.5) translateZ(0);-webkit-transform-origin:0 0;transform-origin:0 0;border-radius:100%\n}.jx-unlined[data-v-1373db16]::before{border:0!important}.jx-tabbar-hump[data-v-1373db16]{width:%?100?%;height:%?100?%;position:absolute;left:50%;-webkit-transform:translateX(-50%) rotate(0deg);transform:translateX(-50%) rotate(0deg);top:%?-40?%;\n-webkit-transition:all .2s linear;transition:all .2s linear\n}.jx-tabbar-hump .img[data-v-1373db16]{width:%?100?%;height:%?100?%;\ndisplay:block\n}.jx-hump-active[data-v-1373db16]{\n-webkit-transform:translateX(-50%) rotate(135deg);transform:translateX(-50%) rotate(135deg)\n}.jx-text-hump[data-v-1373db16]{position:absolute;bottom:%?10?%}.jx-text-scale[data-v-1373db16]{font-weight:700;-webkit-transform:scale(.8);transform:scale(.8);font-size:%?25?%;line-height:%?28?%;-webkit-transform-origin:center 100%;transform-origin:center 100%}.jx-badge[data-v-1373db16]{position:absolute;font-size:%?24?%;height:%?30?%;\nmin-width:%?30?%;\n\n\npadding:0 %?6?%;border-radius:%?40?%;right:0;top:%?-5?%;-webkit-transform:translateX(60%);transform:translateX(60%);\ndisplay:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center\n}.jx-badge-dot[data-v-1373db16]{position:absolute;height:%?16?%;width:%?16?%;border-radius:50%;right:%?-4?%;top:%?-4?%}.jx-shadow[data-v-1373db16]{box-shadow:0 0 5px 0 #eee}',""]),t.exports=a},6915:function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",[t.is_show_tabbar?e("v-uni-view",{staticClass:"jx-tabbar",class:[t.isFixed?"jx-tabbar-fixed":"",t.unlined?"jx-unlined":"",t.is_shadow?"jx-shadow":""],style:{background:t.backgroundColor}},[t._l(t.tabBarItems,(function(a,i){return[e("v-uni-view",{key:i+"_0",staticClass:"jx-tabbar-item",class:[a.hump?"jx-item-hump":""],style:{backgroundColor:a.hump?t.tabBarItems.top_background_color:"none"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.tabbarSwitch(i,a.hump,a.url,a.verify)}}},[e("v-uni-view",{staticClass:"jx-icon-box",class:[a.hump?"jx-tabbar-hump":""]},[e("v-uni-image",{staticClass:"img",class:[a.hump?"":"jx-tabbar-icon"],attrs:{src:a.active?a.active_icon:a.icon}}),a.num?e("v-uni-view",{class:[a.isDot?"jx-badge-dot":"jx-badge"],style:{color:t.badgeColor,backgroundColor:t.badgeBgColor}},[t._v(t._s(a.isDot?"":a.num))]):t._e()],1),e("v-uni-view",{staticClass:"jx-text-scale",class:[a.hump?"jx-text-hump":""],style:{color:a.active?a.active_color:a.color}},[t._v(t._s(a.text))])],1)]})),t.hump&&!t.unlined?e("v-uni-view",{class:[t.hump?"jx-hump-box":""]}):t._e()],2):t._e()],1)},o=[]},"73ac":function(t,a,e){var i=e("0bfc");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("0f92166b",i,!0,{sourceMap:!1,shadowMode:!1})},"7b0a":function(t,a,e){"use strict";function i(t){var a=typeof t;return"undefined"===a||("string"===a&&0===t.replace(/(^[ \t\n\r]*)|([ \t\n\r]*$)/g,"").length||("string"===a&&("null"===t||null===t)||(!("number"!==a||!isNaN(t))||"object"===a&&(null===t||0===t.length))))}e("ac1f"),e("5319"),Object.defineProperty(a,"__esModule",{value:!0}),a.isEmpty=i},"826a":function(t,a,e){"use strict";var i=e("b2a8"),n=e.n(i);n.a},aa92:function(t,a,e){"use strict";e.r(a);var i=e("6915"),n=e("0e04");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);e("826a");var r,s=e("f0c5"),d=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"1373db16",null,!1,i["a"],r);a["default"]=d.exports},ad8f:function(t,a,e){"use strict";var i=e("73ac"),n=e.n(i);n.a},b2a8:function(t,a,e){var i=e("63a8");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("d6508c44",i,!0,{sourceMap:!1,shadowMode:!1})},ba9b:function(t,a,e){"use strict";e.r(a);var i=e("0315"),n=e("36a2");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);e("ad8f");var r,s=e("f0c5"),d=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"c13929ec",null,!1,i["a"],r);a["default"]=d.exports}}]);