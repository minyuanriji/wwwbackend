(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-shopping-shopping"],{"02ea":function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n={name:"jxNomore",props:{visible:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fafafa"},isDot:{type:Boolean,default:!1},text:{type:String,default:"没有更多了"}},data:function(){return{dotText:"●"}}};i.default=n},"097b":function(t,i,e){"use strict";e.r(i);var n=e("02ea"),o=e.n(n);for(var a in n)"default"!==a&&function(t){e.d(i,t,(function(){return n[t]}))}(a);i["default"]=o.a},"0e19":function(t,i,e){"use strict";var n=e("68ed"),o=e.n(n);o.a},"140c":function(t,i,e){"use strict";e("99af"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n={data:function(){return{currentTab:0,tabs:[{name:"永久红包券"},{name:"有效红包券"}],tab_list:["固定红包券","动态红包券"],static_integral:0,dynamic_integral:0,list:[],page:1,is_no_more:!1,textColor:"",queryFlag:!0}},computed:{get_static_integral:function(){var t=this.static_integral;return t},get_dynamic_integral:function(){var t=this.dynamic_integral;return t}},filters:{formatDate:function(t){var i=new Date(1e3*t),e=i.getFullYear(),n=i.getMonth()+1;n=n<10?"0"+n:n;var o=i.getDate();o=o<10?"0"+o:o;var a=i.getHours();a=a<10?"0"+a:a;var s=i.getMinutes();s=s<10?"0"+s:s;var r=i.getSeconds();return r=r<10?"0"+r:r,e+"-"+n+"-"+o+" "+a+":"+s+":"+r}},onLoad:function(){uni.getStorageSync("mall_config")&&(this.textColor=this.globalSet("textCol")),this.getList()},onReachBottom:function(t){this.queryFlag&&!this.is_no_more&&this.getList()},methods:{toSendPlan:function(){uni.navigateTo({url:"/pages/user/shopping/sendPlan"})},toRechargeRecord:function(){uni.navigateTo({url:"/pages/user/shopping/rechargeRecord"})},toRechargeCard:function(){uni.navigateTo({url:"/pages/user/shopping/rechargeCard"})},change:function(t){this.queryFlag&&(this.currentTab=t,this.list=[],this.page=1,this.is_no_more=!1,this.getList())},todetailed:function(t){uni.navigateTo({url:"./detailed?id="+t})},getList:function(){var t=this;if(this.is_no_more)uni.showToast({title:"暂无更多数据"});else{this.queryFlag=!1,uni.showLoading({title:"加载中"});var i=this.currentTab+1;this.$http.request({url:this.$api.user.integral_center,method:"POST",data:{page:this.page,type:i,controller_type:1}}).then((function(i){if(console.log(i),uni.hideLoading(),t.queryFlag=!0,0==i.code){var e=i.data.integral_list.list;t.list=t.list.concat(e),t.static_integral=i.data.wallet.static_integral,t.dynamic_integral=i.data.wallet.dynamic_integral,10==e.length?t.page++:t.is_no_more=!0}else uni.showToast({title:i.msg})}))}}}};i.default=n},2824:function(t,i,e){var n=e("7c07");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=e("4f06").default;o("00c0eaaf",n,!0,{sourceMap:!1,shadowMode:!1})},"295c":function(t,i,e){"use strict";e.r(i);var n=e("f0bb"),o=e.n(n);for(var a in n)"default"!==a&&function(t){e.d(i,t,(function(){return n[t]}))}(a);i["default"]=o.a},"4a0a":function(t,i,e){"use strict";e.r(i);var n=e("140c"),o=e.n(n);for(var a in n)"default"!==a&&function(t){e.d(i,t,(function(){return n[t]}))}(a);i["default"]=o.a},"4acb":function(t,i,e){var n=e("24fb");i=n(!1),i.push([t.i,'.jx-loadmore-none[data-v-332b08f2]{width:50%;margin:1.5em auto;line-height:1.5em;font-size:%?24?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.jx-nomore[data-v-332b08f2]{width:100%;height:100%;position:relative;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore[data-v-332b08f2]::before{content:" ";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:100%;top:%?18?%;left:0}.jx-nomore-text[data-v-332b08f2]{color:#999;font-size:%?24?%;text-align:center;padding:0 %?18?%;height:%?36?%;line-height:%?36?%;position:relative;z-index:1}.jx-nomore-dot[data-v-332b08f2]{position:relative;text-align:center;-webkit-display:flex;display:-webkit-box;display:flex;-webkit-justify-content:center;-webkit-box-pack:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore-dot[data-v-332b08f2]::before{content:"";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:%?360?%;top:%?18?%}.jx-dot-text[data-v-332b08f2]{position:relative;color:#e5e5e5;font-size:10px;text-align:center;width:%?50?%;height:%?36?%;line-height:%?36?%;-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:center center;transform-origin:center center;z-index:1}',""]),t.exports=i},"644b":function(t,i,e){"use strict";var n;e.d(i,"b",(function(){return o})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){return n}));var o=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.visible?e("v-uni-view",{staticClass:"jx-nomore-class jx-loadmore-none"},[e("v-uni-view",{class:[t.isDot?"jx-nomore-dot":"jx-nomore"]},[e("v-uni-view",{class:[t.isDot?"jx-dot-text":"jx-nomore-text"],style:{background:t.bgcolor}},[t._v(t._s(t.isDot?t.dotText:t.text))])],1)],1):t._e()},a=[]},"68ed":function(t,i,e){var n=e("fa72");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=e("4f06").default;o("140f1f56",n,!0,{sourceMap:!1,shadowMode:!1})},7110:function(t,i,e){"use strict";var n=e("2824"),o=e.n(n);o.a},"75d9":function(t,i,e){"use strict";var n;e.d(i,"b",(function(){return o})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){return n}));var o=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.type.length>0?e("v-uni-view",{staticClass:"tabBlock"},[e("v-uni-scroll-view",{attrs:{"scroll-x":"true","scroll-with-animation":!0,"scroll-left":t.tabsScrollLeft},on:{scroll:function(i){arguments[0]=i=t.$handleEvent(i),t.scroll.apply(void 0,arguments)}}},[e("v-uni-view",{class:["tab",{tab_block_line:t.blockLine}],attrs:{id:"tab_list"}},t._l(t.type,(function(i,n){return e("v-uni-view",{key:n,class:["tab__item",{"tab__item--active":t.currentIndex===n}],style:{color:t.currentIndex===n?""+t.itemColor:""},attrs:{id:"tab_item"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.select(i,n)}}},[e("v-uni-view",{staticClass:"tab__item-title"},[t._v(t._s(i.name))])],1)})),1),e("v-uni-view",{staticClass:"tab__line",style:{background:t.lineColor,width:t.lineStyle.width,transform:t.lineStyle.transform,transitionDuration:t.lineStyle.transitionDuration}})],1)],1):t._e()},a=[]},"768b":function(t,i,e){"use strict";e.r(i);var n=e("ffec"),o=e("4a0a");for(var a in o)"default"!==a&&function(t){e.d(i,t,(function(){return o[t]}))}(a);e("0e19");var s,r=e("f0c5"),l=Object(r["a"])(o["default"],n["b"],n["c"],!1,null,"4f721f43",null,!1,n["a"],s);i["default"]=l.exports},"7c07":function(t,i,e){var n=e("24fb");i=n(!1),i.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.tabBlock[data-v-025f754a]{position:relative;background:#fff}.tabBlock .tab[data-v-025f754a]{position:relative;display:-webkit-box;display:-webkit-flex;display:flex;font-size:%?28?%;padding-bottom:%?0?%;white-space:nowrap}.tabBlock .tab__item[data-v-025f754a]{-webkit-box-flex:1;-webkit-flex:1;flex:1;text-align:center;line-height:%?90?%;color:#333}.tabBlock .tab__item--active[data-v-025f754a]{color:#007aff}.tabBlock .tab__item-title[data-v-025f754a]{margin:0 %?40?%}.tabBlock .tab_block_line[data-v-025f754a]::before{content:"";position:absolute;border-bottom:%?1?% solid #eaeef1;-webkit-transform:scaleY(.5);transform:scaleY(.5);bottom:0;right:0;left:0}.tabBlock .tab__line[data-v-025f754a]{display:block;height:%?6?%;position:absolute;bottom:%?2?%;left:0;z-index:1;-webkit-border-radius:%?3?%;border-radius:%?3?%;position:relative;background:#007aff}',""]),t.exports=i},"95db":function(t,i,e){var n=e("4acb");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=e("4f06").default;o("0f41b927",n,!0,{sourceMap:!1,shadowMode:!1})},a109:function(t,i,e){"use strict";e.r(i);var n=e("75d9"),o=e("295c");for(var a in o)"default"!==a&&function(t){e.d(i,t,(function(){return o[t]}))}(a);e("7110");var s,r=e("f0c5"),l=Object(r["a"])(o["default"],n["b"],n["c"],!1,null,"025f754a",null,!1,n["a"],s);i["default"]=l.exports},c332:function(t,i,e){"use strict";var n=e("95db"),o=e.n(n);o.a},ca5f:function(t,i,e){"use strict";e.r(i);var n=e("644b"),o=e("097b");for(var a in o)"default"!==a&&function(t){e.d(i,t,(function(){return o[t]}))}(a);e("c332");var s,r=e("f0c5"),l=Object(r["a"])(o["default"],n["b"],n["c"],!1,null,"332b08f2",null,!1,n["a"],s);i["default"]=l.exports},f0bb:function(t,i,e){"use strict";e("a9e3"),e("ac1f"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n={props:{value:[Number,String],type:{type:Array,default:function(){return[]}},itemColor:String,lineColor:String,blockLine:{type:Boolean,default:!1},lineAnimated:{type:Boolean,default:!0}},data:function(){return{currentIndex:0,lineStyle:{},scrollLeft:0,tabsScrollLeft:0,duration:.3}},watch:{type:function(){this.setTabList()},value:function(){this.currentIndex=this.value,this.setTabList()}},mounted:function(){this.currentIndex=this.value,this.setTabList(),this.lineAnimated||(this.duration=0)},methods:{select:function(t,i){this.$emit("change",i)},setTabList:function(){var t=this;this.$nextTick((function(){t.type.length>0&&(t.setLine(),t.scrollIntoView())}))},setLine:function(){var t=this,i=0,e=0;this.getElementData("#tab_item",(function(n){var o=n[t.currentIndex];i=o.width/2,e=o.width/2-n[0].left+o.left,t.lineStyle={width:"".concat(i,"px"),transform:"translateX(".concat(e,"px) translateX(-50%)"),transitionDuration:"".concat(t.duration,"s")}}))},scrollIntoView:function(){var t=this,i=0;this.getElementData("#tab_list",(function(e){var n=e[0];t.getElementData("#tab_item",(function(e){var o=e[t.currentIndex];i=o.width/2-n.left+o.left-n.width/2-t.scrollLeft,t.tabsScrollLeft=t.scrollLeft+i}))}))},getElementData:function(t,i){uni.createSelectorQuery().in(this).selectAll(t).boundingClientRect().exec((function(t){i(t[0])}))},scroll:function(t){this.scrollLeft=t.detail.scrollLeft}}};i.default=n},fa72:function(t,i,e){var n=e("24fb");i=n(!1),i.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.shopping-coupon[data-v-4f721f43]{width:%?750?%;padding-bottom:%?20?%;background:#f7f7f7}.shopping-coupon .shopping-main[data-v-4f721f43]{-webkit-box-sizing:border-box;box-sizing:border-box;width:%?690?%;margin:0 %?30?%}.shopping-coupon .shopping-main .shopping-coupon-group[data-v-4f721f43]{width:100%;margin-top:%?20?%;-webkit-box-sizing:border-box;box-sizing:border-box;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-justify-content:space-around;justify-content:space-around;height:%?160?%;background:#fff;-webkit-border-radius:%?10?%;border-radius:%?10?%}.shopping-coupon .shopping-main .shopping-coupon-group .shopping-coupon-item[data-v-4f721f43]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.shopping-coupon .shopping-main .shopping-coupon-group .shopping-coupon-item .coupon-item-num[data-v-4f721f43]{font-size:%?46?%;font-family:Source Han Sans CN;font-weight:700;color:#0ad9ce;line-height:%?37?%}.shopping-coupon .shopping-main .shopping-coupon-group .shopping-coupon-item .coupon-item-title[data-v-4f721f43]{font-size:%?30?%;font-family:Source Han Sans CN;font-weight:400;color:#333;line-height:%?37?%;margin-top:%?20?%}.shopping-coupon .shopping-main .shopping-tools[data-v-4f721f43]{-webkit-box-sizing:border-box;box-sizing:border-box;margin-top:%?20?%;width:%?690?%;background:#fff;-webkit-border-radius:%?10?%;border-radius:%?10?%}.shopping-coupon .shopping-main .shopping-tools .shopping-tools-item[data-v-4f721f43]{-webkit-box-sizing:border-box;box-sizing:border-box;height:%?80?%;padding:%?26?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.shopping-coupon .shopping-main .shopping-tools .shopping-tools-item .iconfont[data-v-4f721f43]{font-size:16pt}.shopping-coupon .shopping-main .shopping-tools .shopping-tools-item[data-v-4f721f43]:first-child{border-bottom:%?1?% solid #f7f7f7}.shopping-coupon .shopping-main .shopping-tools .list-empty[data-v-4f721f43]{overflow:hidden}.shopping-coupon .shopping-main .shopping-coupon-list[data-v-4f721f43]{margin-bottom:%?90?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item[data-v-4f721f43]{-webkit-box-sizing:border-box;box-sizing:border-box;width:%?690?%;padding:%?20?% %?26?%;border-bottom:%?1?% solid #f7f7f7}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .item-time[data-v-4f721f43]{font-size:%?22?%;font-family:Source Han Sans CN;font-weight:400;color:#999;line-height:%?37?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main[data-v-4f721f43]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:start;-webkit-align-items:flex-start;align-items:flex-start;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;font-size:%?28?%;font-family:Source Han Sans CN;font-weight:400;color:#333;line-height:%?60?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main .item-expire-time[data-v-4f721f43]{font-size:%?18?%;font-family:Source Han Sans CN;font-weight:400;color:#999;line-height:%?36?%}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main .item-right[data-v-4f721f43]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.shopping-coupon .shopping-main .shopping-coupon-list .coupon-list .coupon-list-item .coupon-list-item-main .item-right .item-button[data-v-4f721f43]{width:%?145?%;height:%?40?%;text-align:center;background:#0ad9ce;-webkit-border-radius:%?20?%;border-radius:%?20?%;font-size:%?22?%;font-family:Source Han Sans CN;font-weight:400;color:#fff;line-height:%?40?%}.exchange-btn[data-v-4f721f43]{position:fixed;z-index:10;bottom:0;left:0;width:%?750?%;height:%?90?%;background:#0ad9ce;font-size:%?28?%;font-family:Source Han Sans CN;font-weight:400;color:#fff;line-height:%?90?%;text-align:center;letter-spacing:%?2?%}.tab[data-v-4f721f43]{background:#fff}.tab .tab-item[data-v-4f721f43]{width:50%;text-align:center;font-size:%?32?%;color:#000;border-top:1px solid #f3f3f3;padding:%?28?% 0;letter-spacing:%?2?%}.tab .border[data-v-4f721f43]{border-right:1px solid #f3f3f3}.tab .cut[data-v-4f721f43]{background:#0ad9ce;color:#fff}.detail-box[data-v-4f721f43]{padding:0 %?30?%}.detail-box .detail-item-box[data-v-4f721f43]{background:#fff;margin-top:%?20?%;-webkit-border-radius:%?10?%;border-radius:%?10?%;padding:%?30?% %?20?%}.detail-box .detail-item-box .time[data-v-4f721f43]{border-bottom:1px solid #f3f3f3;padding-bottom:%?16?%}.detail-box .detail-item-box .price[data-v-4f721f43]{padding:%?16?% 0;border-bottom:1px solid #f3f3f3}.detail-box .detail-item-box .explanation[data-v-4f721f43]{padding:%?16?% 0 0}.nothing[data-v-4f721f43]{padding-top:%?200?%;text-align:center;letter-spacing:1px}',""]),t.exports=i},ffec:function(t,i,e){"use strict";e.d(i,"b",(function(){return o})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){return n}));var n={tabs:e("a109").default,mainNomore:e("ca5f").default},o=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"shopping-coupon"},[e("v-uni-view",{staticClass:"shopping-main"},[e("v-uni-view",{staticClass:"shopping-coupon-group"},[e("v-uni-view",{staticClass:"shopping-coupon-item"},[e("v-uni-text",{staticClass:"coupon-item-num"},[t._v(t._s(t.get_static_integral))]),e("v-uni-text",{staticClass:"coupon-item-title"},[t._v("永久红包券")])],1),e("v-uni-view",{staticClass:"shopping-coupon-item"},[e("v-uni-text",{staticClass:"coupon-item-num"},[t._v(t._s(t.get_dynamic_integral))]),e("v-uni-text",{staticClass:"coupon-item-title"},[t._v("有效红包券")])],1)],1),e("v-uni-view",{staticClass:"shopping-tools"},[e("v-uni-view",{staticClass:"shopping-tools-item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toSendPlan.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("红包券发放计划")]),e("i",{staticClass:"iconfont icon-xiala i-icon"})],1),e("v-uni-view",{staticClass:"shopping-tools-item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toRechargeRecord.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("红包券充值明细")]),e("i",{staticClass:"iconfont icon-xiala i-icon"})],1)],1),e("v-uni-view",{staticClass:"shopping-tools shopping-coupon-list"},[e("v-uni-view",{staticClass:"tui-mtop"},[e("tabs",{attrs:{type:t.tabs,blockLine:!0,itemColor:t.textColor,lineColor:t.textColor},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.change.apply(void 0,arguments)}},model:{value:t.currentTab,callback:function(i){t.currentTab=i},expression:"currentTab"}})],1),t.list&&t.list.length>0?e("v-uni-view",{staticClass:"coupon-list"},t._l(t.list,(function(i){return e("v-uni-view",{staticClass:"coupon-list-item"},[e("v-uni-view",{staticClass:"item-time"},[t._v(t._s(t._f("formatDate")(i.created_at)))]),e("v-uni-view",{staticClass:"coupon-list-item-main"},[e("v-uni-view",{staticClass:"item-left"},[e("v-uni-view",{staticClass:"item-income"},[t._v("收入：¥"+t._s(i.money))]),e("v-uni-view",{class:[0==t.currentTab?"item-explain":"item-time"]},[t._v(t._s(i.desc))]),1==t.currentTab?e("v-uni-view",{staticClass:"item-expire-time"},[t._v("过期时间:"+t._s(t._f("formatDate")(i.expire_time)))]):t._e()],1),e("v-uni-view",{staticClass:"item-right"},[e("v-uni-view",{staticClass:"item-money"},[t._v("红包券：¥"+t._s(1*i.before_money+1*i.money))]),1==t.currentTab?e("v-uni-view",{staticClass:"item-button",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.todetailed(i.id)}}},[t._v("查看详情")]):t._e()],1)],1)],1)})),1):e("v-uni-view",{staticClass:"list-empty"},[e("main-nomore",{attrs:{text:"暂无数据",visible:!0,bgcolor:"#fff"}})],1)],1),e("v-uni-view",{staticClass:"exchange-btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toRechargeCard.apply(void 0,arguments)}}},[t._v("红包券充值")])],1)],1)},a=[]}}]);