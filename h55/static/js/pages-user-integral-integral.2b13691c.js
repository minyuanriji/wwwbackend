(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-integral-integral"],{"096c":function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){return n}));var o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.type.length>0?i("v-uni-view",{staticClass:"tabBlock"},[i("v-uni-scroll-view",{attrs:{"scroll-x":"true","scroll-with-animation":!0,"scroll-left":t.tabsScrollLeft},on:{scroll:function(e){arguments[0]=e=t.$handleEvent(e),t.scroll.apply(void 0,arguments)}}},[i("v-uni-view",{class:["tab",{tab_block_line:t.blockLine}],attrs:{id:"tab_list"}},t._l(t.type,(function(e,n){return i("v-uni-view",{key:n,class:["tab__item",{"tab__item--active":t.currentIndex===n}],style:{color:t.currentIndex===n?""+t.itemColor:""},attrs:{id:"tab_item"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.select(e,n)}}},[i("v-uni-view",{staticClass:"tab__item-title"},[t._v(t._s(e.name))])],1)})),1),t.tabLine?i("v-uni-view",{staticClass:"tab__line",style:{background:t.lineColor,width:t.lineStyle.width,transform:t.lineStyle.transform,transitionDuration:t.lineStyle.transitionDuration}}):t._e()],1)],1):t._e()},a=[]},1668:function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){return n}));var o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.visible?i("v-uni-view",{staticClass:"jx-nomore-class jx-loadmore-none"},[i("v-uni-view",{class:[t.isDot?"jx-nomore-dot":"jx-nomore"]},[i("v-uni-view",{class:[t.isDot?"jx-dot-text":"jx-nomore-text"],style:{background:t.bgcolor}},[t._v(t._s(t.isDot?t.dotText:t.text))])],1)],1):t._e()},a=[]},"20b9":function(t,e,i){var n=i("24fb");e=n(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.tabBlock[data-v-72edca06]{position:relative;background:#fff}.tabBlock .tab[data-v-72edca06]{position:relative;display:-webkit-box;display:-webkit-flex;display:flex;font-size:%?28?%;padding-bottom:%?0?%;white-space:nowrap}.tabBlock .tab__item[data-v-72edca06]{-webkit-box-flex:1;-webkit-flex:1;flex:1;text-align:center;line-height:%?90?%;color:#333}.tabBlock .tab__item--active[data-v-72edca06]{color:#007aff}.tabBlock .tab__item-title[data-v-72edca06]{margin:0 %?40?%}.tabBlock .tab_block_line[data-v-72edca06]::before{content:"";position:absolute;border-bottom:%?1?% solid #eaeef1;-webkit-transform:scaleY(.5);transform:scaleY(.5);bottom:0;right:0;left:0}.tabBlock .tab__line[data-v-72edca06]{display:block;height:%?6?%;position:absolute;bottom:%?2?%;left:0;z-index:1;border-radius:%?3?%;position:relative;background:#007aff}',""]),t.exports=e},"2aed":function(t,e,i){var n=i("7a2f");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=i("4f06").default;o("06e89a15",n,!0,{sourceMap:!1,shadowMode:!1})},"3e9c":function(t,e,i){var n=i("24fb");e=n(!1),e.push([t.i,'.jx-loadmore-none[data-v-332b08f2]{width:50%;margin:1.5em auto;line-height:1.5em;font-size:%?24?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.jx-nomore[data-v-332b08f2]{width:100%;height:100%;position:relative;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore[data-v-332b08f2]::before{content:" ";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:100%;top:%?18?%;left:0}.jx-nomore-text[data-v-332b08f2]{color:#999;font-size:%?24?%;text-align:center;padding:0 %?18?%;height:%?36?%;line-height:%?36?%;position:relative;z-index:1}.jx-nomore-dot[data-v-332b08f2]{position:relative;text-align:center;-webkit-display:flex;display:-webkit-box;display:flex;-webkit-justify-content:center;-webkit-box-pack:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore-dot[data-v-332b08f2]::before{content:"";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:%?360?%;top:%?18?%}.jx-dot-text[data-v-332b08f2]{position:relative;color:#e5e5e5;font-size:10px;text-align:center;width:%?50?%;height:%?36?%;line-height:%?36?%;-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:center center;transform-origin:center center;z-index:1}',""]),t.exports=e},"52b7":function(t,e,i){var n=i("20b9");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=i("4f06").default;o("00bd6f3c",n,!0,{sourceMap:!1,shadowMode:!1})},5713:function(t,e,i){"use strict";i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){return n}));var n={tabs:i("c79b").default,mainNomore:i("eace").default},o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"shopping-coupon"},[i("v-uni-view",{staticClass:"shopping-main"},[i("v-uni-view",{staticClass:"shopping-coupon-group"},[i("v-uni-view",{staticClass:"shopping-coupon-item"},[i("v-uni-text",{staticClass:"coupon-item-num"},[t._v(t._s(t.current_integral))]),i("v-uni-text",{staticClass:"coupon-item-title"},[t._v("总积分")])],1),i("v-uni-view",{staticClass:"shopping-coupon-item"},[i("v-uni-text",{staticClass:"coupon-item-num"},[t._v(t._s(t.get_static_integral))]),i("v-uni-text",{staticClass:"coupon-item-title"},[t._v("永久积分券")])],1),i("v-uni-view",{staticClass:"shopping-coupon-item"},[i("v-uni-text",{staticClass:"coupon-item-num"},[t._v(t._s(t.get_dynamic_integral))]),i("v-uni-text",{staticClass:"coupon-item-title"},[t._v("有效积分券")])],1)],1),i("v-uni-view",{staticClass:"shopping-tools"},[i("v-uni-view",{staticClass:"shopping-tools-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toMyCard.apply(void 0,arguments)}}},[i("v-uni-text",[t._v("我的积分券")]),i("i",{staticClass:"iconfont icon-xiala i-icon"})],1),i("v-uni-view",{staticClass:"shopping-tools-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toSendPlan.apply(void 0,arguments)}}},[i("v-uni-text",[t._v("积分券发放计划")]),i("i",{staticClass:"iconfont icon-xiala i-icon"})],1),i("v-uni-view",{staticClass:"shopping-tools-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toRechargeRecord.apply(void 0,arguments)}}},[i("v-uni-text",[t._v("积分券充值明细")]),i("i",{staticClass:"iconfont icon-xiala i-icon"})],1),i("v-uni-view",{staticClass:"shopping-tools-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toScoreDetails.apply(void 0,arguments)}}},[i("v-uni-text",[t._v("积分明细")]),i("i",{staticClass:"iconfont icon-xiala i-icon"})],1)],1),i("v-uni-view",{staticClass:"shopping-tools shopping-coupon-list"},[i("v-uni-view",{staticClass:"tui-mtop"},[i("tabs",{attrs:{type:t.tabs,blockLine:!0,itemColor:t.textColor,lineColor:t.textColor},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.change.apply(void 0,arguments)}},model:{value:t.currentTab,callback:function(e){t.currentTab=e},expression:"currentTab"}})],1),t.list&&t.list.length>0?i("v-uni-view",{staticClass:"coupon-list"},t._l(t.list,(function(e){return i("v-uni-view",{staticClass:"coupon-list-item"},[i("v-uni-view",{staticClass:"item-time"},[t._v(t._s(t._f("formatDate")(e.created_at)))]),i("v-uni-view",{staticClass:"coupon-list-item-main"},[i("v-uni-view",{staticClass:"item-left"},[i("v-uni-view",{staticClass:"item-income"},[t._v("收入：¥"+t._s(e.money))]),i("v-uni-view",{class:[0==t.currentTab?"item-explain":"item-time"]},[t._v(t._s(e.desc))]),1==t.currentTab?i("v-uni-view",{staticClass:"item-expire-time"},[t._v("过期时间:"+t._s(t._f("formatDate")(e.expire_time)))]):t._e()],1),i("v-uni-view",{staticClass:"item-right"},[i("v-uni-view",{staticClass:"item-money"},[t._v("积分券：¥"+t._s(1*e.before_money+1*e.money))]),1==t.currentTab?i("v-uni-view",{staticClass:"item-button",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.todetailed(e.id)}}},[t._v("查看详情")]):t._e()],1)],1)],1)})),1):i("v-uni-view",{staticClass:"list-empty"},[i("main-nomore",{attrs:{text:"暂无数据",visible:!0,bgcolor:"#fff"}})],1)],1),i("v-uni-view",{staticClass:"exchange-btn",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toRechargeCard.apply(void 0,arguments)}}},[t._v("积分券充值")])],1)],1)},a=[]},"5a82":function(t,e,i){"use strict";i("a9e3"),i("ac1f"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={props:{value:[Number,String],type:{type:Array,default:function(){return[]}},itemColor:String,lineColor:String,blockLine:{type:Boolean,default:!1},lineAnimated:{type:Boolean,default:!0},tabLine:{type:Boolean,default:!0}},data:function(){return{currentIndex:0,lineStyle:{},scrollLeft:0,tabsScrollLeft:0,duration:.3}},watch:{type:function(){this.setTabList()},value:function(){this.currentIndex=this.value,this.setTabList()}},mounted:function(){this.currentIndex=this.value,this.setTabList(),this.lineAnimated||(this.duration=0)},methods:{select:function(t,e){this.$emit("change",e)},setTabList:function(){var t=this;this.$nextTick((function(){t.type.length>0&&(t.setLine(),t.scrollIntoView())}))},setLine:function(){var t=this,e=0,i=0;this.getElementData("#tab_item",(function(n){var o=n[t.currentIndex];e=o.width/2,i=o.width/2-n[0].left+o.left,t.lineStyle={width:"".concat(e,"px"),transform:"translateX(".concat(i,"px) translateX(-50%)"),transitionDuration:"".concat(t.duration,"s")}}))},scrollIntoView:function(){var t=this,e=0;this.getElementData("#tab_list",(function(i){var n=i[0];t.getElementData("#tab_item",(function(i){var o=i[t.currentIndex];e=o.width/2-n.left+o.left-n.width/2-t.scrollLeft,t.tabsScrollLeft=t.scrollLeft+e}))}))},getElementData:function(t,e){uni.createSelectorQuery().in(this).selectAll(t).boundingClientRect().exec((function(t){e(t[0])}))},scroll:function(t){this.scrollLeft=t.detail.scrollLeft}}};e.default=n},"6e16":function(t,e,i){"use strict";i.r(e);var n=i("e958"),o=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,(function(){return n[t]}))}(a);e["default"]=o.a},"72af":function(t,e,i){"use strict";var n=i("52b7"),o=i.n(n);o.a},"7a2f":function(t,e,i){var n=i("24fb");e=n(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.shopping-coupon[data-v-5f95a336]{width:%?750?%;padding-bottom:%?20?%;background:#f7f7f7}.shopping-coupon .shopping-main[data-v-5f95a336]{box-sizing:border-box;width:%?690?%;margin:0 %?30?%}.shopping-coupon .shopping-main .shopping-coupon-group[data-v-5f95a336]{width:100%;margin-top:%?20?%;box-sizing:border-box;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-justify-content:space-around;justify-content:space-around;height:%?160?%;background:#fff;border-radius:%?10?%}.shopping-coupon .shopping-main .shopping-coupon-group .shopping-coupon-item[data-v-5f95a336]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.shopping-coupon .shopping-main .shopping-coupon-group .shopping-coupon-item .coupon-item-num[data-v-5f95a336]{font-size:%?46?%;font-family:Source Han Sans CN;font-weight:700;color:#0ad9ce;line-height:%?37?%}.shopping-coupon .shopping-main .shopping-coupon-group .shopping-coupon-item .coupon-item-title[data-v-5f95a336]{font-size:%?30?%;font-family:Source Han Sans CN;font-weight:400;color:#333;line-height:%?37?%;margin-top:%?20?%}.shopping-coupon .shopping-main .shopping-tools[data-v-5f95a336]{box-sizing:border-box;margin-top:%?20?%;width:%?690?%;background:#fff;border-radius:%?10?%}.shopping-coupon .shopping-main .shopping-tools .shopping-tools-item[data-v-5f95a336]{box-sizing:border-box;height:%?80?%;padding:%?26?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;border-bottom:%?1?% solid #f7f7f7}.shopping-coupon .shopping-main .shopping-tools .shopping-tools-item .iconfont[data-v-5f95a336]{font-size:16pt}.shopping-coupon .shopping-main .shopping-tools .shopping-tools-item[data-v-5f95a336]:last-child{border-bottom:0}.shopping-coupon .shopping-main .shopping-tools .list-empty[data-v-5f95a336]{overflow:hidden}.shopping-coupon .shopping-main .shopping-coupon-list[data-v-5f95a336]{margin-bottom:%?90?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item[data-v-5f95a336]{box-sizing:border-box;width:%?690?%;padding:%?20?% %?26?%;border-bottom:%?1?% solid #f7f7f7}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .item-time[data-v-5f95a336]{font-size:%?22?%;font-family:Source Han Sans CN;font-weight:400;color:#999;line-height:%?37?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main[data-v-5f95a336]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:start;-webkit-align-items:flex-start;align-items:flex-start;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;font-size:%?28?%;font-family:Source Han Sans CN;font-weight:400;color:#333;line-height:%?60?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main .item-expire-time[data-v-5f95a336]{font-size:%?18?%;font-family:Source Han Sans CN;font-weight:400;color:#999;line-height:%?36?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main .item-right[data-v-5f95a336]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main .item-right .item-button[data-v-5f95a336]{width:%?145?%;height:%?40?%;text-align:center;background:#0ad9ce;border-radius:%?20?%;font-size:%?22?%;font-family:Source Han Sans CN;font-weight:400;color:#fff;line-height:%?40?%}.exchange-btn[data-v-5f95a336]{position:fixed;z-index:10;bottom:0;left:0;width:%?750?%;height:%?90?%;background:#0ad9ce;font-size:%?28?%;font-family:Source Han Sans CN;font-weight:400;color:#fff;line-height:%?90?%;text-align:center;letter-spacing:%?2?%}.tab[data-v-5f95a336]{background:#fff}.tab .tab-item[data-v-5f95a336]{width:50%;text-align:center;font-size:%?32?%;color:#000;border-top:1px solid #f3f3f3;padding:%?28?% 0;letter-spacing:%?2?%}.tab .border[data-v-5f95a336]{border-right:1px solid #f3f3f3}.tab .cut[data-v-5f95a336]{background:#0ad9ce;color:#fff}.detail-box[data-v-5f95a336]{padding:0 %?30?%}.detail-box .detail-item-box[data-v-5f95a336]{background:#fff;margin-top:%?20?%;border-radius:%?10?%;padding:%?30?% %?20?%}.detail-box .detail-item-box .time[data-v-5f95a336]{border-bottom:1px solid #f3f3f3;padding-bottom:%?16?%}.detail-box .detail-item-box .price[data-v-5f95a336]{padding:%?16?% 0;border-bottom:1px solid #f3f3f3}.detail-box .detail-item-box .explanation[data-v-5f95a336]{padding:%?16?% 0 0}.nothing[data-v-5f95a336]{padding-top:%?200?%;text-align:center;letter-spacing:1px}',""]),t.exports=e},8202:function(t,e,i){"use strict";var n=i("2aed"),o=i.n(n);o.a},"933d":function(t,e,i){"use strict";i.r(e);var n=i("5713"),o=i("f7ac");for(var a in o)"default"!==a&&function(t){i.d(e,t,(function(){return o[t]}))}(a);i("8202");var s,r=i("f0c5"),l=Object(r["a"])(o["default"],n["b"],n["c"],!1,null,"5f95a336",null,!1,n["a"],s);e["default"]=l.exports},"95cc":function(t,e,i){"use strict";var n=i("ae94"),o=i.n(n);o.a},ae94:function(t,e,i){var n=i("3e9c");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=i("4f06").default;o("42a7678a",n,!0,{sourceMap:!1,shadowMode:!1})},c79b:function(t,e,i){"use strict";i.r(e);var n=i("096c"),o=i("f282");for(var a in o)"default"!==a&&function(t){i.d(e,t,(function(){return o[t]}))}(a);i("72af");var s,r=i("f0c5"),l=Object(r["a"])(o["default"],n["b"],n["c"],!1,null,"72edca06",null,!1,n["a"],s);e["default"]=l.exports},e1a0:function(t,e,i){"use strict";(function(t){i("99af"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={data:function(){return{currentTab:0,tabs:[{name:"永久积分券"},{name:"有效积分券"}],tab_list:["固定积分券","动态积分券"],current_integral:0,static_integral:0,dynamic_integral:0,list:[],page:1,is_no_more:!1,textColor:"",queryFlag:!0,userInfo:{balance:0,score:0,coupon:0}}},computed:{get_current_integral:function(){var t=this.current_integral;return t},get_static_integral:function(){var t=this.static_integral;return t},get_dynamic_integral:function(){var t=this.dynamic_integral;return t}},filters:{formatDate:function(t){var e=new Date(1e3*t),i=e.getFullYear(),n=e.getMonth()+1;n=n<10?"0"+n:n;var o=e.getDate();o=o<10?"0"+o:o;var a=e.getHours();a=a<10?"0"+a:a;var s=e.getMinutes();s=s<10?"0"+s:s;var r=e.getSeconds();return r=r<10?"0"+r:r,i+"-"+n+"-"+o+" "+a+":"+s+":"+r}},onLoad:function(){uni.getStorageSync("mall_config")&&(this.textColor=this.globalSet("textCol")),uni.getStorageSync("userInfo")&&(this.userInfo=JSON.parse(uni.getStorageSync("userInfo"))),this.getList()},onReachBottom:function(t){this.queryFlag&&!this.is_no_more&&this.getList()},methods:{toMyCard:function(){uni.navigateTo({url:"/pages/user/integral/myCard"})},toSendPlan:function(){uni.navigateTo({url:"/pages/user/integral/sendPlan"})},toRechargeRecord:function(){uni.navigateTo({url:"/pages/user/integral/rechargeRecord"})},toScoreDetails:function(){uni.navigateTo({url:"/pages/user/score/details"})},toRechargeCard:function(){uni.navigateTo({url:"/pages/user/integral/rechargeCard"})},change:function(e){this.queryFlag&&(t("log",e," at pages/user/integral/integral.vue:182"),this.currentTab=e,this.list=[],this.page=1,this.is_no_more=!1,this.getList())},todetailed:function(t){uni.navigateTo({url:"./detailed?id="+t})},getList:function(){var e=this;if(this.is_no_more)uni.showToast({title:"暂无更多数据"});else{this.queryFlag=!1,uni.showLoading({title:"加载中"});var i=this.currentTab+1;this.$http.request({url:this.$api.user.integral_center,method:"POST",data:{page:this.page,type:i,controller_type:0}}).then((function(i){if(t("log",i," at pages/user/integral/integral.vue:221"),uni.hideLoading(),e.queryFlag=!0,0==i.code){var n=i.data.integral_list.list;e.list=e.list.concat(n),e.current_integral=i.data.wallet.score,e.static_integral=i.data.wallet.static_score,e.dynamic_integral=i.data.wallet.dynamic_score,10==n.length?e.page++:e.is_no_more=!0}else uni.showToast({title:i.msg})}))}}}};e.default=n}).call(this,i("0de9")["log"])},e958:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={name:"jxNomore",props:{visible:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fafafa"},isDot:{type:Boolean,default:!1},text:{type:String,default:"没有更多了"}},data:function(){return{dotText:"●"}}};e.default=n},eace:function(t,e,i){"use strict";i.r(e);var n=i("1668"),o=i("6e16");for(var a in o)"default"!==a&&function(t){i.d(e,t,(function(){return o[t]}))}(a);i("95cc");var s,r=i("f0c5"),l=Object(r["a"])(o["default"],n["b"],n["c"],!1,null,"332b08f2",null,!1,n["a"],s);e["default"]=l.exports},f282:function(t,e,i){"use strict";i.r(e);var n=i("5a82"),o=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,(function(){return n[t]}))}(a);e["default"]=o.a},f7ac:function(t,e,i){"use strict";i.r(e);var n=i("e1a0"),o=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,(function(){return n[t]}))}(a);e["default"]=o.a}}]);