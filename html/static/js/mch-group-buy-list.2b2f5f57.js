(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["mch-group-buy-list"],{"02ea":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"jxNomore",props:{visible:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fafafa"},isDot:{type:Boolean,default:!1},text:{type:String,default:"没有更多了"}},data:function(){return{dotText:"●"}}};e.default=a},"03d2":function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var a={comTabs:i("ba9f").default,mainNomore:i("ca5f").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"list-root"},[i("v-uni-view",{staticClass:"app-header"},[i("v-uni-view",{staticClass:"tui-mtop"},[i("com-tabs",{attrs:{tabs:t.tabs,currentTab:t.currentTab,selectedColor:t.textColor,sliderBgColor:t.textColor,color:"#000000",sliderWidth:60,itemWidth:"50%"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.change.apply(void 0,arguments)}}})],1)],1),i("v-uni-view",{staticClass:"list-box"},t._l(t.detailData,(function(e,a){return i("v-uni-view",{key:a,staticClass:"list-items flex flex-x-between",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.navTo(e.group_buy_goods.goods_id)}}},[i("v-uni-image",{staticClass:"list-item-left",attrs:{src:e.goods_warehouse.cover_pic}}),i("v-uni-view",{staticClass:"list-item-right flex flex-col flex-x-between"},[i("v-uni-view",{staticClass:"over2 pro-name"},[t._v(t._s(e.goods_warehouse.name))]),i("v-uni-view",{staticClass:"pro-detail"},[i("v-uni-view",{staticClass:"group-item-center flex"},[i("v-uni-view",{staticClass:"people-num flex flex-x-center flex-y-center",style:{background:t.textColor}},[t._v(t._s(e.group_buy_goods.people)+"人团")]),1==t.currentTab?i("v-uni-view",{staticClass:"group-start flex flex-y-center",style:{color:t.textColor}},[t._v(t._s(e.group_buy_goods.start_at_format)+"后开抢")]):t._e()],1),i("v-uni-view",{staticClass:"pro-detail-bottom flex flex-y-center flex-x-between"},[i("v-uni-view",{staticClass:"flex flex-y-center"},[i("v-uni-view",{staticClass:"flex flex-y-bottom price-box",style:{color:t.textColor}},[t._v("¥"),i("v-uni-text",{staticClass:"price"},[t._v(t._s(e.group_buy_goods.price))])],1),0==t.currentTab?[e.group_buy_goods.sales>1e4?i("v-uni-view",{staticClass:"group-num"},[t._v("已团:"+t._s(parseInt(e.group_buy_goods.sales/1e4))+"+万件")]):i("v-uni-view",{staticClass:"group-num"},[t._v("已团:"+t._s(e.group_buy_goods.sales)+"件")])]:t._e()],2),0==t.currentTab?i("v-uni-view",{staticClass:"start-btn-bottom start-btn",style:{background:t.textColor},on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.navTo(e.group_buy_goods.goods_id)}}},[t._v("开团抢")]):1==t.currentTab?i("v-uni-view",{staticClass:"start-btn-bottom show-start-btn",style:{background:t.textColor},on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.showStart(e.group_buy_goods.start_at_format)}}},[t._v("未开始")]):t._e()],1)],1)],1)],1)})),1),t.nodata?i("v-uni-view",{staticClass:"coupon-items"},[i("main-nomore",{attrs:{text:"暂无数据",visible:!0,bgcolor:"transparent"}})],1):t._e()],1)},o=[]},"06c5":function(t,e,i){"use strict";i("a630"),i("fb6a"),i("d3b7"),i("25f0"),i("3ca3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=o;var a=n(i("6b75"));function n(t){return t&&t.__esModule?t:{default:t}}function o(t,e){if(t){if("string"===typeof t)return(0,a.default)(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);return"Object"===i&&t.constructor&&(i=t.constructor.name),"Map"===i||"Set"===i?Array.from(t):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?(0,a.default)(t,e):void 0}}},"097b":function(t,e,i){"use strict";i.r(e);var a=i("02ea"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},1924:function(t,e,i){"use strict";i("a9e3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"jxTabs",props:{tabs:{type:Array,default:function(){return[]}},height:{type:Number,default:80},padding:{type:Number,default:30},bgColor:{type:String,default:"#FFFFFF"},isFixed:{type:Boolean,default:!1},top:{type:Number,default:44},unlined:{type:Boolean,default:!1},currentTab:{type:Number,default:0},sliderWidth:{type:Number,default:68},sliderHeight:{type:Number,default:6},sliderBgColor:{type:String,default:"#bc0100"},sliderRadius:{type:String,default:"50rpx"},bottom:{type:String,default:"0"},itemWidth:{type:String,default:"25%"},color:{type:String,default:"#666"},selectedColor:{type:String,default:"#bc0100"},size:{type:Number,default:28},bold:{type:Boolean,default:!1}},watch:{currentTab:function(){this.checkCor()}},created:function(){var t=this;setTimeout((function(){uni.getSystemInfo({success:function(e){t.winWidth=e.windowWidth,t.checkCor()}})}),50)},data:function(){return{jxWidth:750,winWidth:0,scrollLeft:0}},methods:{checkCor:function(){var t=this.tabs.length,e=this.winWidth/this.jxWidth*this.padding,i=this.winWidth-2*e,a=(i/t-this.winWidth/this.jxWidth*this.sliderWidth)/2+e,n=a;this.currentTab>0&&(n+=i/t*this.currentTab),this.scrollLeft=n},swichTabs:function(t){var e=this.tabs[t];if(!e||!e.disabled)return this.currentTab!=t&&void this.$emit("change",{index:Number(t)})}}};e.default=a},"1b84":function(t,e,i){var a=i("f439");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("2e4e6c13",a,!0,{sourceMap:!1,shadowMode:!1})},2909:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=l;var a=s(i("6005")),n=s(i("db90")),o=s(i("06c5")),r=s(i("3427"));function s(t){return t&&t.__esModule?t:{default:t}}function l(t){return(0,a.default)(t)||(0,n.default)(t)||(0,o.default)(t)||(0,r.default)()}},3427:function(t,e,i){"use strict";function a(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}Object.defineProperty(e,"__esModule",{value:!0}),e.default=a},"41a0":function(t,e,i){"use strict";var a=i("5344"),n=i.n(a);n.a},"4acb":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'.jx-loadmore-none[data-v-332b08f2]{width:50%;margin:1.5em auto;line-height:1.5em;font-size:%?24?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.jx-nomore[data-v-332b08f2]{width:100%;height:100%;position:relative;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore[data-v-332b08f2]::before{content:" ";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:100%;top:%?18?%;left:0}.jx-nomore-text[data-v-332b08f2]{color:#999;font-size:%?24?%;text-align:center;padding:0 %?18?%;height:%?36?%;line-height:%?36?%;position:relative;z-index:1}.jx-nomore-dot[data-v-332b08f2]{position:relative;text-align:center;-webkit-display:flex;display:-webkit-box;display:flex;-webkit-justify-content:center;-webkit-box-pack:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore-dot[data-v-332b08f2]::before{content:"";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:%?360?%;top:%?18?%}.jx-dot-text[data-v-332b08f2]{position:relative;color:#e5e5e5;font-size:10px;text-align:center;width:%?50?%;height:%?36?%;line-height:%?36?%;-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:center center;transform-origin:center center;z-index:1}',""]),t.exports=e},5344:function(t,e,i){var a=i("b4c7");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("8c4c822a",a,!0,{sourceMap:!1,shadowMode:!1})},"53c3":function(t,e,i){"use strict";var a=i("1b84"),n=i.n(a);n.a},6005:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=o;var a=n(i("6b75"));function n(t){return t&&t.__esModule?t:{default:t}}function o(t){if(Array.isArray(t))return(0,a.default)(t)}},"644b":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.visible?i("v-uni-view",{staticClass:"jx-nomore-class jx-loadmore-none"},[i("v-uni-view",{class:[t.isDot?"jx-nomore-dot":"jx-nomore"]},[i("v-uni-view",{class:[t.isDot?"jx-dot-text":"jx-nomore-text"],style:{background:t.bgcolor}},[t._v(t._s(t.isDot?t.dotText:t.text))])],1)],1):t._e()},o=[]},"6b75":function(t,e,i){"use strict";function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,a=new Array(e);i<e;i++)a[i]=t[i];return a}Object.defineProperty(e,"__esModule",{value:!0}),e.default=a},7869:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"jx-tabs-view",class:[t.isFixed?"jx-tabs-fixed":"jx-tabs-relative",t.unlined?"jx-unlined":""],style:{height:t.height+"rpx",padding:"0 "+t.padding+"rpx",background:t.bgColor,top:t.isFixed?t.top+"px":"auto"}},[t._l(t.tabs,(function(e,a){return i("v-uni-view",{key:a,staticClass:"jx-tabs-item",style:{width:t.itemWidth},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.swichTabs(a)}}},[i("v-uni-view",{staticClass:"jx-tabs-title",class:{"jx-tabs-active":t.currentTab==a,"jx-tabs-disabled":e.disabled},style:{color:t.currentTab==a?t.selectedColor:t.color,fontSize:t.size+"rpx",lineHeight:t.size+"rpx",fontWeight:t.bold&&t.currentTab==a?"bold":"normal"}},[t._v(t._s(e.name))])],1)})),i("v-uni-view",{staticClass:"jx-tabs-slider",style:{transform:"translateX("+t.scrollLeft+"px)",width:t.sliderWidth+"rpx",height:t.sliderHeight+"rpx",borderRadius:t.sliderRadius,bottom:t.bottom,background:t.sliderBgColor,marginBottom:"50%"==t.bottom?"-"+t.sliderHeight/2+"rpx":0}})],2)},o=[]},8764:function(t,e,i){"use strict";i.r(e);var a=i("1924"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"95db":function(t,e,i){var a=i("4acb");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("0f41b927",a,!0,{sourceMap:!1,shadowMode:!1})},ae0e:function(t,e,i){"use strict";i.r(e);var a=i("03d2"),n=i("d1e0");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("53c3");var r,s=i("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"3587b678",null,!1,a["a"],r);e["default"]=l.exports},b4c7:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'.jx-tabs-view[data-v-e6cd55f4]{width:100%;-webkit-box-sizing:border-box;box-sizing:border-box;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;z-index:9999}.jx-tabs-relative[data-v-e6cd55f4]{position:relative}.jx-tabs-fixed[data-v-e6cd55f4]{position:fixed;left:0}.jx-tabs-fixed[data-v-e6cd55f4]::before,\n.jx-tabs-relative[data-v-e6cd55f4]::before{content:"";position:absolute;border-bottom:%?1?% solid #eaeef1;-webkit-transform:scaleY(.5);transform:scaleY(.5);bottom:0;right:0;left:0}.jx-unlined[data-v-e6cd55f4]::before{border-bottom:0!important}.jx-tabs-item[data-v-e6cd55f4]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.jx-tabs-disabled[data-v-e6cd55f4]{opacity:.6}.jx-tabs-title[data-v-e6cd55f4]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;position:relative;z-index:2}.jx-tabs-active[data-v-e6cd55f4]{-webkit-transition:all .15s ease-in-out;transition:all .15s ease-in-out}.jx-tabs-slider[data-v-e6cd55f4]{position:absolute;left:0;-webkit-transition:all .15s ease-in-out;transition:all .15s ease-in-out;z-index:0;-webkit-transform:translateZ(0);transform:translateZ(0)}',""]),t.exports=e},ba9f:function(t,e,i){"use strict";i.r(e);var a=i("7869"),n=i("8764");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("41a0");var r,s=i("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"e6cd55f4",null,!1,a["a"],r);e["default"]=l.exports},c150:function(t,e,i){"use strict";var a=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("2909")),o=a(i("ade3")),r={data:function(){return(0,o.default)({dataStatus:0,currentTab:0,tabs:[{name:"进行中"},{name:"未开始"}],textColor:"#bc0100",img_url:this.$api.img_url,detailData:[],page:1,page_count:5,nodata:!1,requestFlag:!0},"textColor","#bc0100")},onLoad:function(){this.textColor=this.globalSet("textCol");var t=this.globalSet("navBg"),e=this.globalSet("navCol");console.log(e),t&&e&&uni.setNavigationBarColor({frontColor:e,backgroundColor:t,animation:{duration:500,timingFunc:"easeIn"}});var i=0==this.currentTab?1:0;console.log(i),this.getList(i)},onReachBottom:function(){var t=0==this.currentTab?1:0;this.page++,this.page<=this.page_count&&this.getList(t)},methods:{change:function(t){if(this.requestFlag){this.currentTab=t.index,this.detailData=[],this.page=1;var e=0==this.currentTab?1:0;this.getList(e)}},showStart:function(t){this.$http.toast(t+"钟后开抢")},navTo:function(t){0==this.currentTab&&uni.navigateTo({url:"/mch/group-buy/good?proId=".concat(t)})},navToDetail:function(t){uni.navigateTo({url:"/mch/group-buy/detail?detailId=".concat(t)})},getList:function(t){var e=this;this.requestFlag&&(this.requestFlag=!1,this.$http.request({url:this.$api.plugin.group.getList,method:"post",showLoading:!0,data:{status:t,page:this.page}}).then((function(t){var i;(e.requestFlag=!0,0==t.code)?(1==e.page&&(e.nodata=0==t.data.list.length),(i=e.detailData).push.apply(i,(0,n.default)(t.data.list)),e.page_count=t.data.pagination.page_count):e.$http.toast(t.msg)})))}}};e.default=r},c332:function(t,e,i){"use strict";var a=i("95db"),n=i.n(a);n.a},ca5f:function(t,e,i){"use strict";i.r(e);var a=i("644b"),n=i("097b");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("c332");var r,s=i("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"332b08f2",null,!1,a["a"],r);e["default"]=l.exports},d1e0:function(t,e,i){"use strict";i.r(e);var a=i("c150"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},db90:function(t,e,i){"use strict";function a(t){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}i("a4d3"),i("e01a"),i("d28b"),i("a630"),i("d3b7"),i("3ca3"),i("ddb0"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=a},f439:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.group-item-center[data-v-3587b678]{margin-bottom:%?32?%}.list-root[data-v-3587b678]{padding-top:%?20?%}.list-box[data-v-3587b678]{background:#fff;padding:0 %?30?%}.list-items[data-v-3587b678]{padding:%?24?% 0;border-bottom:1px solid #f2f2f2}.list-items .list-item-left[data-v-3587b678]{width:%?218?%;height:%?218?%;-webkit-border-radius:%?10?%;border-radius:%?10?%;margin-right:%?24?%}.list-items .list-item-right[data-v-3587b678]{-webkit-box-flex:1;-webkit-flex:1;flex:1;padding:%?6?% 0}.list-items .list-item-right .pro-name[data-v-3587b678]{font-size:%?32?%;font-family:Microsoft YaHei;font-weight:400;color:#333;line-height:%?48?%}.list-items .list-item-right .pro-detail .group-start[data-v-3587b678]{height:%?30?%;margin-left:%?4?%;margin-bottom:%?8?%;font-size:%?20?%;font-family:Microsoft YaHei;font-weight:400;line-height:%?48?%;padding:%?4?% %?8?%;background:rgba(248,221,221,.3);-webkit-border-radius:%?0?% %?4?% %?4?% %?0?%;border-radius:%?0?% %?4?% %?4?% %?0?%}.list-items .list-item-right .pro-detail .people-num[data-v-3587b678]{width:%?80?%;height:%?30?%;letter-spacing:%?2?%;margin-bottom:%?2?%;font-size:%?20?%;font-family:Microsoft YaHei;font-weight:400;color:#fff;-webkit-border-radius:%?4?% %?0?% %?0?% %?4?%;border-radius:%?4?% %?0?% %?0?% %?4?%}.list-items .list-item-right .pro-detail .pro-detail-bottom[data-v-3587b678]{font-size:%?24?%}.list-items .list-item-right .pro-detail .pro-detail-bottom .price-box[data-v-3587b678]{margin-right:%?14?%}.list-items .list-item-right .pro-detail .pro-detail-bottom .price-box .price[data-v-3587b678]{font-size:%?40?%;line-height:%?48?%}.list-items .list-item-right .pro-detail .pro-detail-bottom .group-num[data-v-3587b678]{color:#999;letter-spacing:1px;-webkit-transform:scale(.9);transform:scale(.9);margin-top:%?4?%}.list-items .list-item-right .pro-detail .pro-detail-bottom .start-btn-bottom[data-v-3587b678]{color:#fff;letter-spacing:1px;text-align:center;width:%?100?%;height:%?56?%;line-height:%?56?%;background:-webkit-gradient(linear,left top,right top,from(#d92f2f),to(#fb3939));background:-webkit-linear-gradient(left,#d92f2f,#fb3939);background:linear-gradient(90deg,#d92f2f,#fb3939);-webkit-border-radius:%?6?%;border-radius:%?6?%}',""]),t.exports=e}}]);