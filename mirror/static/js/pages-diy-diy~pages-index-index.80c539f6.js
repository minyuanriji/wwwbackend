(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-diy-diy~pages-index-index"],{"08f8":function(e,t,i){"use strict";i("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={name:"search",props:{padding:{type:String,default:""},innerPadding:{type:String,default:""},message:{type:String,default:"搜索"},frameColor:{type:String,default:"#eeeeee"},innerFrameColor:{type:String,default:"#ffffff"},textColor:{type:String,default:"#eeeeee"},searchIcon:{type:String,default:"../../static/images/search/searchIcon.png"},borderRadius:{type:Number,default:20},textAlign:{type:String,default:"left"},is_fixed:{type:Number,default:0}},created:function(){},methods:{px:function(e){return uni.upx2px(e)+"px"},openUrl:function(e){uni.navigateTo({url:e})}}};t.default=a},"199d":function(e,t,i){"use strict";i.r(t);var a=i("716c"),n=i("5d59");for(var s in n)"default"!==s&&function(e){i.d(t,e,(function(){return n[e]}))}(s);i("8afe");var r,o=i("f0c5"),l=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"3dd84966",null,!1,a["a"],r);t["default"]=l.exports},"1d84":function(e,t,i){"use strict";i.r(t);var a=i("3d7a"),n=i.n(a);for(var s in a)"default"!==s&&function(e){i.d(t,e,(function(){return a[e]}))}(s);t["default"]=n.a},"28d8":function(e,t,i){"use strict";i.r(t);var a=i("e85d"),n=i("3fd2");for(var s in n)"default"!==s&&function(e){i.d(t,e,(function(){return n[e]}))}(s);i("e5d6");var r,o=i("f0c5"),l=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"dd021460",null,!1,a["a"],r);t["default"]=l.exports},"33d8":function(e,t,i){"use strict";var a=i("3a9d"),n=i.n(a);n.a},3760:function(e,t,i){"use strict";var a=i("b75b"),n=i.n(a);n.a},"376c":function(e,t,i){var a=i("24fb");t=a(!1),t.push([e.i,".rubik-root[data-v-413cd7f0]{position:relative}.type1[data-v-413cd7f0]{width:100%}.type1 .img[data-v-413cd7f0]{width:100%;display:block}.type3[data-v-413cd7f0]{width:100%;height:%?360?%;position:relative}.type5[data-v-413cd7f0]{height:%?240?%;position:relative}.type7[data-v-413cd7f0]{height:%?188?%;position:relative}.type8[data-v-413cd7f0]{height:%?372?%;position:relative}.backgroundSize[data-v-413cd7f0]{background-size:cover!important;background-position:50%!important;background-repeat:no-repeat!important;position:absolute}.hotspot[data-v-413cd7f0]{position:absolute;z-index:2}",""]),e.exports=t},"3a9d":function(e,t,i){var a=i("376c");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=i("4f06").default;n("42f4825b",a,!0,{sourceMap:!1,shadowMode:!1})},"3d7a":function(e,t,i){"use strict";(function(e){i("c975"),i("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={name:"commodity",props:{keyValue:{type:String,default:""},productData:{type:Array,default:function(){return[]}},listStyle:{type:Number,default:1},displayStyle:{type:Number,default:1},showGoodsName:{type:String,default:"1"},showGoodsOriginalPrice:{type:Boolean,default:!1},originalPriceLabel:{type:String,default:""},showGoodsPrice:{type:String,default:"1"},priceLabel:{type:String,default:""},showGoodsLevelPrice:{type:String,default:"0"},levelPriceLabel:{type:String,default:""},showBuyBtn:{type:Boolean,default:!0},buyBtnPic:{type:String,default:"http://www.mingyuanriji.cn/web/statics/img/mall/goods/cart_active.png"},buyBtns:{type:String,default:"pic"},buyBtnColor:{type:String,default:"#bc0100"},buyBtnStyle:{type:Number,default:1},subscriptIcon:{type:String,default:""},showGoodsTag:{type:String,default:"1"},buyBtnText:{type:String,default:"购买"},buttonColor:{type:String,default:"#bc0100"},styleTitle:{type:String}},data:function(){return{textColor:"#bc0100",styleShow:!1}},created:function(){this.styleTitle&&(-1!=this.styleTitle.indexOf("名品专区")?this.styleShow=!0:this.styleShow=!1)},mounted:function(){uni.getStorageSync("mall_config")&&(this.textColor=this.globalSet("textCol"))},onLoad:function(t){e("log",this.$route.query," at components/commodity/commodity.vue:278")},methods:{navTo:function(e){this.$route.query;uni.navigateTo({url:"/pages/goods/detail?proId=".concat(e)})},buyBtn:function(){return 1==this.showBuyBtn&&(1==this.displayStyle||2==this.displayStyle)},subscript:function(){return 1==this.showGoodsTag},px:function(e){return uni.upx2px(e)+"px"}}};t.default=a}).call(this,i("0de9")["log"])},"3fd2":function(e,t,i){"use strict";i.r(t);var a=i("f9c1"),n=i.n(a);for(var s in a)"default"!==s&&function(e){i.d(t,e,(function(){return a[e]}))}(s);t["default"]=n.a},4477:function(e,t,i){"use strict";var a;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return s})),i.d(t,"a",(function(){return a}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"root",class:2==e.listStyle||3==e.listStyle?"flex":"",style:{background:"cart"==e.keyValue?"transparent":"#ffffff"}},[e._l(e.productData,(function(t,a){return 1==e.listStyle?i("v-uni-view",{key:a,staticClass:"select1",on:{click:function(i){i.stopPropagation(),arguments[0]=i=e.$handleEvent(i),e.navTo(t.id)}}},[i("v-uni-view",{staticClass:"select1_box",style:{border:2==e.displayStyle||4==e.displayStyle?"1px solid #E0E0E0":""}},[i("v-uni-image",{staticClass:"select1_proImg",attrs:{src:t.cover_pic,mode:"widthFix"}}),i("v-uni-view",{staticClass:"select1_proDetails"},["1"==e.showGoodsName?i("v-uni-view",{staticClass:"select1_proName"},[e._v(e._s(t.name))]):e._e(),i("v-uni-view",{staticClass:"select1_price flex flex-y-center",class:3==e.displayStyle||4==e.displayStyle?"flex-x-center":"flex-x-between"},[i("v-uni-view",[e.showGoodsOriginalPrice?i("v-uni-view",{staticClass:"select1_original_price"},[e._v(e._s(e.originalPriceLabel)+" ¥"+e._s(t.original_price))]):e._e(),"1"==e.showGoodsPrice?i("v-uni-view",{style:{color:e.textColor}},[e._v(e._s(e.priceLabel)+" ¥"+e._s(t.price))]):e._e(),"1"==e.showGoodsLevelPrice?i("v-uni-view",{staticClass:"select1_level_price"},[e._v(e._s(e.levelPriceLabel)+" ¥"+e._s(t.level_price))]):e._e()],1),"pic"==e.buyBtns?i("v-uni-view",[i("v-uni-image",{staticClass:"select1_buyBtn1",class:e.buyBtn()?"":"hide",attrs:{src:e.buyBtnPic,mode:""}})],1):i("v-uni-view",[1==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn2",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),2==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn3",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),3==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn5",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),4==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn4",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e()],1)],1)],1),e.subscriptIcon?i("v-uni-image",{staticClass:"subscript",class:e.subscript()?"":"hide",attrs:{src:e.subscriptIcon,mode:"widthFix"}}):e._e()],1)],1):e._e()})),e._l(e.productData,(function(t,a){return-1==e.listStyle?i("v-uni-view",{key:a,staticClass:"select2",on:{click:function(i){i.stopPropagation(),arguments[0]=i=e.$handleEvent(i),e.navTo(t.id)}}},[i("v-uni-view",{staticClass:"select2_box",style:{border:2==e.displayStyle?"1px solid #eeeeee":""}},[i("v-uni-view",{staticClass:"flex"},[i("v-uni-image",{staticClass:"select2_proImg",attrs:{src:t.cover_pic,mode:"scaleToFill"}}),i("v-uni-view",{staticClass:"select2_proDetails flex-col"},["1"==e.showGoodsName?i("v-uni-view",{staticClass:"select2_proName"},[e._v(e._s(t.name))]):i("v-uni-view"),i("v-uni-view",{staticClass:"select1_price flex flex-y-center",class:3==e.displayStyle||4==e.displayStyle?"flex-x-center":"flex-x-between"},[i("v-uni-view",[e.showGoodsOriginalPrice?i("v-uni-view",{staticClass:"select1_original_price"},[e._v(e._s(e.originalPriceLabel)+" ¥"+e._s(t.original_price))]):e._e(),"1"==e.showGoodsPrice?i("v-uni-view",{style:{color:e.textColor}},[e._v(e._s(e.priceLabel)+" ¥"+e._s(t.price))]):e._e(),"1"==e.showGoodsLevelPrice?i("v-uni-view",{staticClass:"select1_level_price"},[e._v(e._s(e.levelPriceLabel)+" ¥"+e._s(t.level_price))]):e._e()],1),"pic"==e.buyBtns?i("v-uni-view",[i("v-uni-image",{staticClass:"select1_buyBtn1",class:e.buyBtn()?"":"hide",attrs:{src:e.buyBtnPic,mode:""}})],1):i("v-uni-view",[1==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn2",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),2==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn3",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),3==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn5",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),4==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn4",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e()],1)],1)],1),e.subscriptIcon?i("v-uni-image",{staticClass:"subscript",class:e.subscript()?"":"hide",attrs:{src:e.subscriptIcon,mode:"widthFix"}}):e._e()],1)],1)],1):e._e()})),e._l(e.productData,(function(t,a){return 2==e.listStyle?i("v-uni-view",{key:a,staticClass:"select3",on:{click:function(i){i.stopPropagation(),arguments[0]=i=e.$handleEvent(i),e.navTo(t.id)}}},[i("v-uni-view",{staticClass:"select1_box select3_box",style:{border:2==e.displayStyle||4==e.displayStyle?"1px solid #efefef":""}},[i("v-uni-image",{staticClass:"select3_proImg",attrs:{src:t.cover_pic,mode:"aspectFill"}}),i("v-uni-view",{staticClass:"select1_proDetails"},["1"==e.showGoodsName?i("v-uni-view",{staticClass:"select2_proName"},[e._v(e._s(t.name))]):e._e(),i("v-uni-view",{staticClass:"select1_price flex flex-y-center",class:3==e.displayStyle||4==e.displayStyle?"flex-x-center":"flex-x-between"},[e.styleShow?e._e():i("v-uni-view",[e.showGoodsOriginalPrice?i("v-uni-view",{staticClass:"select1_original_price"},[e._v(e._s(e.originalPriceLabel)+" ¥"+e._s(t.original_price))]):e._e(),"1"==e.showGoodsPrice?i("v-uni-view",{style:{color:"#FF7104"}},[e._v(e._s(e.priceLabel)+" ¥"+e._s(t.price))]):e._e(),"1"==e.showGoodsLevelPrice?i("v-uni-view",{staticClass:"select1_level_price"},[e._v(e._s(e.levelPriceLabel)+" ¥"+e._s(t.level_price))]):e._e()],1),e.styleShow?i("v-uni-view",[e.showGoodsOriginalPrice?i("v-uni-view",{staticClass:"select1_original_price"},[e._v(e._s(e.originalPriceLabel)+" ¥"+e._s(t.original_price))]):e._e(),"1"==e.showGoodsPrice?i("v-uni-view",{staticStyle:{"font-size":"25rpx"},style:{color:"#FF7104"}},[e._v(e._s(e.priceLabel)+" "+e._s(t.price)),i("v-uni-text",{staticStyle:{"font-size":"25rpx",color:"red",transform:"scale(0.83)",display:"inline-block"}},[e._v("红包")])],1):e._e(),"1"==e.showGoodsLevelPrice?i("v-uni-view",{staticClass:"select1_level_price"},[e._v(e._s(e.levelPriceLabel)+" "+e._s(t.level_price)),i("v-uni-text",{staticStyle:{"font-size":"25rpx",color:"red","margin-left":"10rpx"}},[e._v("红包")])],1):e._e()],1):e._e(),"pic"==e.buyBtns?i("v-uni-view",[i("v-uni-image",{staticClass:"select1_buyBtn1",class:e.buyBtn()?"":"hide",attrs:{src:e.buyBtnPic,mode:""}})],1):i("v-uni-view",[1==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn2",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),2==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn3",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),3==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn5",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),4==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn4",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e()],1)],1)],1),e.subscriptIcon?i("v-uni-image",{staticClass:"subscript",class:e.subscript()?"":"hide",attrs:{src:e.subscriptIcon,mode:"widthFix"}}):e._e()],1)],1):e._e()})),e._l(e.productData,(function(t,a){return 3==e.listStyle?i("v-uni-view",{key:a,staticClass:"select4",on:{click:function(i){i.stopPropagation(),arguments[0]=i=e.$handleEvent(i),e.navTo(t.id)}}},[i("v-uni-view",{staticClass:"select1_box",style:{border:2==e.displayStyle||4==e.displayStyle?"1px solid #E0E0E0":""}},[i("v-uni-image",{staticClass:"select4_proImg",attrs:{src:t.cover_pic,mode:"scaleToFill"}}),i("v-uni-view",{staticClass:"select1_proDetails"},["1"==e.showGoodsName?i("v-uni-view",{staticClass:"select2_proName"},[e._v(e._s(t.name))]):e._e(),i("v-uni-view",{staticClass:"select1_price flex flex-y-center",class:3==e.displayStyle||4==e.displayStyle?"flex-x-center":"flex-x-between",staticStyle:{"font-size":"26rpx"}},[i("v-uni-view",[e.showGoodsOriginalPrice?i("v-uni-view",{staticClass:"select1_original_price"},[e._v(e._s(e.originalPriceLabel)+" ¥"+e._s(t.original_price))]):e._e(),"1"==e.showGoodsPrice?i("v-uni-view",{staticClass:"pic",style:{color:e.textColor}},[e._v(e._s(e.priceLabel)+" ¥"+e._s(t.price))]):e._e(),"1"==e.showGoodsLevelPrice?i("v-uni-view",{staticClass:"select1_level_price"},[e._v(e._s(e.levelPriceLabel)+" ¥"+e._s(t.level_price))]):e._e()],1),"pic"==e.buyBtns?i("v-uni-view",[i("v-uni-image",{staticClass:"select1_buyBtn1",class:e.buyBtn()?"":"hide",attrs:{src:e.buyBtnPic,mode:""}})],1):i("v-uni-view",[1==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn2",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),2==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn3",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),3==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn5",class:e.buyBtn()?"":"hide",style:{color:"#ffffff",background:e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e(),4==e.buyBtnStyle?i("v-uni-view",{staticClass:"select1_buyBtn4",class:e.buyBtn()?"":"hide",style:{color:e.buttonColor,border:"1px solid "+e.buttonColor}},[e._v(e._s(e.buyBtnText))]):e._e()],1)],1)],1),e.subscriptIcon?i("v-uni-image",{staticClass:"subscript",class:e.subscript()?"":"hide",attrs:{src:e.subscriptIcon,mode:"widthFix"}}):e._e()],1)],1):e._e()})),3==e.listStyle&&e.productData.length%2==1?i("v-uni-view",{staticClass:"select4"}):e._e(),i("v-uni-scroll-view",{staticClass:"scroll-view_H",attrs:{"scroll-x":"true"}},e._l(e.productData,(function(t,a){return 0==e.listStyle?i("v-uni-view",{key:a,staticClass:"select5"},[i("v-uni-view",{staticClass:"select5_box",style:{border:2==e.displayStyle||4==e.displayStyle?"1px solid #E0E0E0":""},on:{click:function(i){i.stopPropagation(),arguments[0]=i=e.$handleEvent(i),e.navTo(t.id)}}},[i("v-uni-image",{staticClass:"select4_proImg",attrs:{src:t.cover_pic,mode:"scaleToFill"}}),i("v-uni-view",{staticClass:"select5_proDetails"},["1"==e.showGoodsName?i("v-uni-view",{staticClass:"select5_proName"},[e._v(e._s(t.name))]):e._e(),i("v-uni-view",{staticClass:"select1_price flex flex-y-center",class:3==e.displayStyle||4==e.displayStyle?"flex-x-center":"flex-x-between"},[e.showGoodsOriginalPrice?i("v-uni-view",{staticClass:"select1_original_price"},[e._v(e._s(e.originalPriceLabel)+" ¥"+e._s(t.original_price))]):e._e(),"1"==e.showGoodsPrice?i("v-uni-view",{style:{color:e.textColor}},[e._v(e._s(e.priceLabel)+" ¥"+e._s(t.price))]):e._e(),"1"==e.showGoodsLevelPrice?i("v-uni-view",{staticClass:"select1_level_price"},[e._v(e._s(e.levelPriceLabel)+" ¥"+e._s(t.level_price))]):e._e()],1)],1),e.subscriptIcon?i("v-uni-image",{staticClass:"subscript",class:e.subscript()?"":"hide",attrs:{src:e.subscriptIcon,mode:"widthFix"}}):e._e()],1)],1):e._e()})),1)],2)},s=[]},4853:function(e,t,i){"use strict";(function(e){var a=i("4ea4");i("cb29"),i("ac1f"),i("1276"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=a(i("28d8")),s={name:"banners",props:{bannerData:{type:Object,default:function(){}}},components:{swiperDot:n.default},data:function(){return{data:{},backgroundCol:"",current:0}},created:function(){this.backgroundCol=this.bannerData.banners[0].picUrl,this.data=JSON.parse(JSON.stringify(this.bannerData)),e("log",this.data," at components/banners/banners.vue:70")},computed:{cContainerStyle:function(){return"height:".concat(this.data.height,"rpx;")},cBannerImgClass:function(){return 0==this.data.fill?"banner-img-contain":1==this.data.fill?"banner-img-cover":void 0}},methods:{navTo:function(e,t){e&&("/pages/web/web?url"==e.split("=")[0]?window.location.href=decodeURIComponent(e.split("=")[1]):"tel?tel"==e.split("=")[0]?uni.makePhoneCall({phoneNumber:e.split("=")[1],success:function(e){}}):uni.navigateTo({url:e}))},px:function(e){return uni.upx2px(e)+"px"},change:function(e){this.current=e.detail.current;var t=e.detail.current;this.backgroundCol=this.bannerData.banners[t].pic_url,this.$emit("change",this.backgroundCol)}}};t.default=s}).call(this,i("0de9")["log"])},"5d59":function(e,t,i){"use strict";i.r(t);var a=i("4853"),n=i.n(a);for(var s in a)"default"!==s&&function(e){i.d(t,e,(function(){return a[e]}))}(s);t["default"]=n.a},"716c":function(e,t,i){"use strict";i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return s})),i.d(t,"a",(function(){return a}));var a={swiperDot:i("28d8").default},n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{style:e.cContainerStyle},[1==e.data.style?i("v-uni-view",{staticClass:"banner-swiper"},[i("v-uni-swiper",{staticClass:"banner-swiper-content",style:{height:e.px(e.data.height)},attrs:{autoplay:"true","indicator-dots":!1,interval:3e3,duration:500,circular:"true"},on:{change:function(t){arguments[0]=t=e.$handleEvent(t),e.change.apply(void 0,arguments)}}},e._l(e.data.banners,(function(t,a){return i("v-uni-swiper-item",{key:a,on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.navTo(t.url)}}},[i("v-uni-view",{staticClass:"slid1_img",class:"banner-img "+e.cBannerImgClass,style:"background-image: url("+t.picUrl+");"})],1)})),1),i("v-uni-view",{staticClass:"swiper-dot-box"},[i("swiper-dot",{attrs:{length:e.data.banners.length,swiperCurrent:e.current,width:30,height:2,radius:"0"}})],1),1==e.data.fill?i("v-uni-view",{staticClass:"background-box",style:{height:e.px(e.data.height-80),background:1==e.data.fill?e.backgroundCol:""}}):e._e()],1):e._e(),2==e.data.style?i("v-uni-view",[i("v-uni-swiper",{class:1==e.data.fill?"":"swiper-box",style:{height:e.px(e.data.height)},attrs:{autoplay:!0,"indicator-dots":!1,interval:4e3,duration:500,circular:"true","previous-margin":"80rpx","next-margin":"80rpx"},on:{change:function(t){arguments[0]=t=e.$handleEvent(t),e.change.apply(void 0,arguments)}}},e._l(e.data.banners,(function(t,a){return i("v-uni-swiper-item",{key:a,on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.navTo(t.url)}}},[i("v-uni-view",{staticClass:"swiper-item",class:{big:e.current==a}},[i("v-uni-view",{staticClass:"slid1_img",style:"background-image: url("+t.picUrl+");"})],1)],1)})),1),i("v-uni-view",{staticClass:"swiper-dot-box"},[i("swiper-dot",{attrs:{length:e.data.banners.length,swiperCurrent:e.current,width:30,height:2,radius:"0"}})],1)],1):e._e()],1)},s=[]},7575:function(e,t,i){var a=i("24fb");t=a(!1),t.push([e.i,".searchBox[data-v-68872bac]{padding:%?20?% %?20?% %?16?%;height:%?96?%;box-sizing:border-box}.search[data-v-68872bac]{width:100%;padding:%?8?% %?20?% %?8?% %?20?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?30?%;position:relative;box-sizing:border-box}.icon-text[data-v-68872bac]{font-size:10pt;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.searchIcon[data-v-68872bac]{width:%?36?%;height:%?36?%;margin-right:%?16?%}.search-btn[data-v-68872bac]{color:#fff;position:absolute;right:%?-4?%;border-radius:%?60?%;padding:%?8?% %?28?%}",""]),e.exports=t},"770c":function(e,t,i){"use strict";i("a9e3"),i("ac1f"),i("1276"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={name:"rubik",data:function(){return{calssNames:""}},props:{types:{type:Number,default:0},imageData:{type:Array,default:function(){return[]}},space:{type:Number,default:0},hotspotData:{type:Array,default:function(){return[]}}},created:function(){1==this.types||2==this.types||3==this.types?this.calssNames="type3":4==this.types||5==this.types?this.calssNames="type5":6==this.types?this.calssNames="type7":this.calssNames="type8"},methods:{navTo:function(e,t){var i=this;"navigate"==t?e&&uni.navigateTo({url:e}):"web"==t?window.location.href=decodeURIComponent(e.split("=")[1]):uni.makePhoneCall({phoneNumber:e.split("=")[1],success:function(e){i.$http.toast("打电话回调成功！")}})}}};t.default=a},"7f5b":function(e,t,i){var a=i("24fb");t=a(!1),t.push([e.i,".pic[data-v-37e2b226]{max-width:55px;overflow:hidden;text-overflow:ellipsis;-webkit-box-flex:1;-webkit-flex:1;flex:1}.root[data-v-37e2b226]{padding:%?20?%;\n\t/* background: white; */-webkit-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;background-color:#fff}\n\n/* 样式一 */.select1_box[data-v-37e2b226]{background:#fff;border-radius:%?10?%;margin-bottom:%?20?%;position:relative;overflow:hidden}.select1_proDetails[data-v-37e2b226]{padding:%?24?%;box-sizing:border-box}.select1_proImg[data-v-37e2b226]{width:100%;max-height:%?712?%}.select3_proImg[data-v-37e2b226]{width:100%;max-height:%?348?%}.select1_proName[data-v-37e2b226]{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:%?28?%;margin-bottom:%?24?%}.select1_price[data-v-37e2b226]{font-size:%?28?%}.select1_original_price[data-v-37e2b226]{font-size:%?28?%;color:silver}.select1_level_price[data-v-37e2b226]{font-size:%?24?%;color:red}\n\n/* 购物车按钮样式 */.select1_buyBtn1[data-v-37e2b226]{width:%?44?%;height:%?44?%;display:block}.select1_buyBtn2[data-v-37e2b226]{padding:%?6?% %?24?%;background-color:#bc0100;color:#fff;max-width:%?200?%;font-size:9pt}.select1_buyBtn3[data-v-37e2b226]{padding:%?4?% %?24?%;border-radius:%?4?%;background-color:#fff;color:#bc0100;max-width:%?200?%;font-size:9pt}.select1_buyBtn4[data-v-37e2b226]{padding:%?4?% %?24?%;font-size:9pt;border:1px solid #bc0100;border-radius:%?50?%;background-color:#fff;color:#bc0100;max-width:%?200?%}.select1_buyBtn5[data-v-37e2b226]{padding:%?4?% %?30?%;border-radius:%?40?%;font-size:9pt;background-color:#bc0100;color:#fff;max-width:%?200?%}.select5_buyBtn2[data-v-37e2b226]{padding:%?4?% %?18?%;border-radius:%?50?%;background-color:#bc0100;color:#fff;max-width:%?200?%;font-size:%?24?%}.select5_buyBtn3[data-v-37e2b226]{padding:%?4?% %?18?%;border:1px solid #bc0100;border-radius:%?4?%;background-color:#fff;color:#bc0100;max-width:%?200?%;font-size:%?24?%}.select5_buyBtn4[data-v-37e2b226]{padding:%?4?% %?18?%;border:1px solid #bc0100;border-radius:%?50?%;background-color:#fff;color:#bc0100;max-width:%?200?%;font-size:%?24?%}.select5_buyBtn5[data-v-37e2b226]{padding:%?4?% %?18?%;border-radius:%?4?%;background-color:#bc0100;color:#fff;max-width:%?200?%;font-size:%?24?%}\n\n/* 购物车按钮样式 */.subscript[data-v-37e2b226]{position:absolute;top:0;left:0;width:%?62?%\n\t/* height: 36rpx; */}\n\n/* 样式二 */.select2_box[data-v-37e2b226]{position:relative;margin:%?20?% 0\n\t/* border-bottom: 1px solid #EEEEEE; */}.select2_proImg[data-v-37e2b226]{width:%?200?%;max-height:%?200?%;border-radius:%?10?%}.select2_proDetails[data-v-37e2b226]{padding:%?14?% %?24?% %?20?%;-webkit-box-flex:1;-webkit-flex:1;flex:1;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.select2_proName[data-v-37e2b226]{height:%?84?%;font-size:%?28?%;margin-bottom:%?20?%;text-overflow:-o-ellipsis-lastline;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;color:#000}\n\n/* 样式三 */.select3[data-v-37e2b226]{width:49%;\n\t/* height: 580rpx;*/\n\t/* width: 348rpx; */\n\t/* margin-right: 14rpx; */box-sizing:border-box}.root .select3[data-v-37e2b226]:nth-of-type(2n){margin-right:0}.select3_box[data-v-37e2b226]{margin-bottom:%?20?%}\n\n/* 样式四 */.select4[data-v-37e2b226]{width:33.3%;padding:0 %?10?%;box-sizing:border-box}.select4_proImg[data-v-37e2b226]{width:100%;height:%?220?%}\n\n/* 样式五 */.scroll-view_H[data-v-37e2b226]{white-space:nowrap}\n\n/* .scroll-view_H ::-webkit-scrollbar {\n\twidth: 0;\n\theight: 0;\n\tbackground-color: transparent;\n} */.select5_box[data-v-37e2b226]{background:#fff;border-radius:%?10?%;position:relative;overflow:hidden}.select5[data-v-37e2b226]{width:33.3%;margin-right:%?20?%;box-sizing:border-box;display:inline-block;padding-top:%?16?%}.select5_proDetails[data-v-37e2b226]{padding:%?16?% %?16?% %?8?%;box-sizing:border-box}.select5_proName[data-v-37e2b226]{font-size:%?28?%;white-space:normal;text-overflow:-o-ellipsis-lastline;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;color:#000;height:%?84?%}.flex-x-between[data-v-37e2b226]{-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.flex-y-center[data-v-37e2b226]{-webkit-box-align:center;-webkit-align-items:center;align-items:center}.hide[data-v-37e2b226]{display:none}",""]),e.exports=t},"8afe":function(e,t,i){"use strict";var a=i("a525"),n=i.n(a);n.a},"8b95":function(e,t,i){"use strict";i.r(t);var a=i("770c"),n=i.n(a);for(var s in a)"default"!==s&&function(e){i.d(t,e,(function(){return a[e]}))}(s);t["default"]=n.a},"8bb1":function(e,t,i){var a=i("24fb");t=a(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 商城主题色 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.banner-img-contain[data-v-3dd84966]{background-size:contain}.banner-img-cover[data-v-3dd84966]{background-size:cover}.banner-swiper[data-v-3dd84966]{position:relative}uni-swiper .uni-swiper-wrapper[data-v-3dd84966]{border-radius:%?16?%}.banner-swiper-content[data-v-3dd84966]{position:relative;z-index:8}.background-box[data-v-3dd84966]{position:absolute;width:100%;top:0;z-index:6;border-radius:0 0 %?60?% %?60?%}.banner_item[data-v-3dd84966]{padding:0 %?20?%;box-sizing:border-box}.banner_item.active .slid2_img[data-v-3dd84966]{-webkit-transform:scale(1);transform:scale(1)}.background[data-v-3dd84966]{padding:%?20?% %?30?%;box-sizing:border-box}.slid1_img[data-v-3dd84966]{width:100%;height:100%;background-repeat:no-repeat;background-position:50%;background-size:cover;background-color:#fff}.slid2_img[data-v-3dd84966]{width:100%;height:100%;background-repeat:no-repeat;background-position:50%;-webkit-transform:scale(.6);transform:scale(.6);-webkit-transition:all .3s ease;transition:all .3s ease}.swiper-box[data-v-3dd84966]{padding:%?40?% 0}.swiper-dot-box[data-v-3dd84966]{position:absolute;bottom:%?20?%;left:50%;-webkit-transform:translate(-50%);transform:translate(-50%);z-index:9}.swiper-item[data-v-3dd84966]{-webkit-transform:scale(.85);transform:scale(.85);height:100%;-webkit-transition:all .5s;transition:all .5s;border-radius:%?10?%;overflow:hidden}.big[data-v-3dd84966]{-webkit-transform:scale(.95);transform:scale(.95)}',""]),e.exports=t},9010:function(e,t,i){var a=i("b520");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=i("4f06").default;n("3990a5c9",a,!0,{sourceMap:!1,shadowMode:!1})},a525:function(e,t,i){var a=i("8bb1");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=i("4f06").default;n("f236e36e",a,!0,{sourceMap:!1,shadowMode:!1})},b140:function(e,t,i){"use strict";i.r(t);var a=i("e425"),n=i("8b95");for(var s in n)"default"!==s&&function(e){i.d(t,e,(function(){return n[e]}))}(s);i("33d8");var r,o=i("f0c5"),l=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"413cd7f0",null,!1,a["a"],r);t["default"]=l.exports},b520:function(e,t,i){var a=i("24fb");t=a(!1),t.push([e.i,".dot-box[data-v-dd021460]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.dot-item[data-v-dd021460]{background:#c8c8c8;margin-right:%?6?%;margin-top:%?15?%}.dot-item-active[data-v-dd021460]{background:#bc0100}",""]),e.exports=t},b75b:function(e,t,i){var a=i("7575");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=i("4f06").default;n("9bf4506e",a,!0,{sourceMap:!1,shadowMode:!1})},c9fb:function(e,t,i){var a=i("7f5b");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=i("4f06").default;n("bb748538",a,!0,{sourceMap:!1,shadowMode:!1})},cafc:function(e,t,i){"use strict";i.r(t);var a=i("f266"),n=i("d913");for(var s in n)"default"!==s&&function(e){i.d(t,e,(function(){return n[e]}))}(s);i("3760");var r,o=i("f0c5"),l=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"68872bac",null,!1,a["a"],r);t["default"]=l.exports},d004:function(e,t,i){"use strict";var a=i("c9fb"),n=i.n(a);n.a},d913:function(e,t,i){"use strict";i.r(t);var a=i("08f8"),n=i.n(a);for(var s in a)"default"!==s&&function(e){i.d(t,e,(function(){return a[e]}))}(s);t["default"]=n.a},e425:function(e,t,i){"use strict";var a;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return s})),i.d(t,"a",(function(){return a}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"rubik-root"},[0==e.types?i("v-uni-view",{staticClass:"type1"},e._l(e.imageData,(function(t,a){return i("v-uni-image",{key:a,staticClass:"img",attrs:{src:t.pic_url,mode:"widthFix"},on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.navTo(t.link.new_link_url,t.link.open_type)}}})})),1):i("v-uni-view",{class:e.calssNames},e._l(e.imageData,(function(t,a){return i("v-uni-view",{key:a,staticClass:"backgroundSize",style:{background:"url("+e.imageData[a].pic_url+")",width:e.imageData[a].width,height:e.imageData[a].height,left:e.imageData[a].left,top:e.imageData[a].top},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.navTo(e.imageData[a].link.new_link_url,e.imageData[a].link.open_type)}}})})),1),0!=e.hotspotData.length?e._l(e.hotspotData,(function(t,a){return i("v-uni-view",{key:a,staticClass:"hotspot",style:{width:e.hotspotData[a].width+"rpx",height:e.hotspotData[a].height+"rpx",left:e.hotspotData[a].left+"rpx",top:e.hotspotData[a].top+"rpx"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.navTo(e.hotspotData[a].link.new_link_url,e.hotspotData[a].link.open_type)}}})})):e._e()],2)},s=[]},e5d6:function(e,t,i){"use strict";var a=i("9010"),n=i.n(a);n.a},e77c:function(e,t,i){"use strict";i.r(t);var a=i("4477"),n=i("1d84");for(var s in n)"default"!==s&&function(e){i.d(t,e,(function(){return n[e]}))}(s);i("d004");var r,o=i("f0c5"),l=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"37e2b226",null,!1,a["a"],r);t["default"]=l.exports},e85d:function(e,t,i){"use strict";var a;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return s})),i.d(t,"a",(function(){return a}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"dot-box"},e._l(e.length,(function(t,a){return i("v-uni-view",{key:a,staticClass:"dot-item",class:{"dot-item-active":e.swiperCurrent==a},style:{width:e.width+"rpx",height:e.height+"rpx","border-radius":e.radius}})})),1)},s=[]},f266:function(e,t,i){"use strict";var a;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return s})),i.d(t,"a",(function(){return a}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"searchBox flex flex-y-center",style:{background:e.frameColor,padding:e.padding?e.padding:""}},[i("v-uni-view",{staticClass:"search",style:{background:e.innerFrameColor,color:e.textColor,"border-radius":e.px(e.borderRadius),border:"1px solid transparent","justify-content":e.textAlign,padding:e.innerPadding?e.innerPadding:""},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.openUrl("/pages/search/search")}}},[i("v-uni-view",{staticClass:"icon-text"},[i("v-uni-view",{staticClass:"iconfont icon-search",staticStyle:{"margin-right":"10rpx",position:"relative",top:"4rpx"}}),e._v(e._s(e.message))],1)],1)],1)},s=[]},f9c1:function(e,t,i){"use strict";i("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={props:{length:{type:Number,default:0},swiperCurrent:{type:Number,default:0},width:{type:Number,default:10},height:{type:Number,default:10},radius:{type:String,default:"50%"}},data:function(){return{}}};t.default=a}}]);