(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["plugins-short-video-dividends-myIncome"],{"053f":function(t,e,i){var a=i("8139");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("0533a19e",a,!0,{sourceMap:!1,shadowMode:!1})},"141e":function(t,e,i){"use strict";i.r(e);var a=i("918e"),o=i("1e70");for(var r in o)"default"!==r&&function(t){i.d(e,t,(function(){return o[t]}))}(r);i("6164");var n,s=i("f0c5"),l=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"85db7258",null,!1,a["a"],n);e["default"]=l.exports},1668:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return r})),i.d(e,"a",(function(){return a}));var o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.visible?i("v-uni-view",{staticClass:"jx-nomore-class jx-loadmore-none"},[i("v-uni-view",{class:[t.isDot?"jx-nomore-dot":"jx-nomore"]},[i("v-uni-view",{class:[t.isDot?"jx-dot-text":"jx-nomore-text"],style:{background:t.bgcolor}},[t._v(t._s(t.isDot?t.dotText:t.text))])],1)],1):t._e()},r=[]},"1e70":function(t,e,i){"use strict";i.r(e);var a=i("d2fd"),o=i.n(a);for(var r in a)"default"!==r&&function(t){i.d(e,t,(function(){return a[t]}))}(r);e["default"]=o.a},2927:function(t,e,i){"use strict";i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return r})),i.d(e,"a",(function(){return a}));var a={comNavBar:i("deaf").default,mainNomore:i("eace").default,mainLoading:i("141e").default},o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"app"},[i("com-nav-bar",{attrs:{fixed:!0,"left-icon":"back",title:"收益明细","status-bar":!0,"background-color":"#ffffff",border:!1,color:"#000000"},on:{clickLeft:function(e){arguments[0]=e=t.$handleEvent(e),t.back.apply(void 0,arguments)}}}),t.parent_agent?[i("v-uni-view",{staticClass:"order"},[i("v-uni-scroll-view",{staticClass:"order-tabs flex",attrs:{"scroll-x":!0}},t._l(2,(function(e,a){return i("v-uni-view",{key:a,staticClass:"flex order-tabs-item"},[i("v-uni-view",{staticClass:"tab flex-grow-1 flex-x-center flex-y-center",style:{color:t.currentTabIndex==a?t.textColor:""},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.tabChange(a)}}},[i("v-uni-view",{staticClass:"name",style:{"border-bottom":t.currentTabIndex==a?"2px solid"+t.textColor:""}},[t._v(t._s(t.orderListTabs[a]))])],1)],1)})),1),i("v-uni-view",{staticStyle:{height:"100rpx","margin-bottom":"20rpx"}}),t.list&&t.list.length>0?i("v-uni-view",{staticClass:"order-list"},t._l(t.list,(function(e,a){return i("v-uni-view",{key:a,staticClass:"item"},[i("v-uni-view",{staticClass:"user-status flex flex-x-between flex-y-center"},[i("v-uni-view",{staticClass:"flex flex-y-center"},[i("v-uni-image",{staticClass:"acatar",attrs:{src:e.avatar_url,mode:"aspectFill"}}),i("v-uni-view",{staticClass:"name-datetime"},[i("v-uni-view",{staticClass:"flex"},[i("v-uni-view",{staticClass:"name over1"},[t._v(t._s(e.nickname))]),i("v-uni-view",{staticClass:"id",style:{color:t.textColor,border:"1px solid"+t.textColor}},[t._v("ID:"+t._s(e.user_id))])],1),i("v-uni-view",{staticClass:"datetime"},[t._v("下单时间:"+t._s(t.date(e.created_at)))])],1)],1),0!=t.currentTabIndex?i("v-uni-view",{staticClass:"price",style:{color:t.textColor}},[t._v(t._s(e.money)+"元")]):t._e(),0==t.currentTabIndex?i("v-uni-view",{staticClass:"status-text",style:{background:t.textColor,border:"1px solid"+t.textColor}},[t._v(t._s(e.is_settlement?"未结算":"已结算"))]):t._e()],1),0==t.currentTabIndex?i("v-uni-view",{staticClass:"info"},[i("v-uni-view",{staticClass:"mark"},[i("v-uni-view",{staticClass:"goods-name"},[t._v("商品名称: "+t._s(e.name))]),i("v-uni-view",{staticClass:"order-id"},[t._v("订单编号: "+t._s(e.order_no))])],1),i("v-uni-view",{staticClass:"money"},[i("v-uni-view",{staticClass:"order-money"},[t._v("订单金额: "+t._s(e.total_price)+"元")]),i("v-uni-view",{staticClass:"commission"},[t._v("带货奖励："+t._s(e.money)+" 元")])],1)],1):t._e()],1)})),1):i("v-uni-view",{staticClass:"order-list"},[i("main-nomore",{attrs:{text:"暂无记录",visible:!0,bgcolor:"transparent"}})],1)],1)]:t._e(),i("main-loading",{attrs:{visible:t.loading}})],2)},r=[]},"3e9c":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'.jx-loadmore-none[data-v-332b08f2]{width:50%;margin:1.5em auto;line-height:1.5em;font-size:%?24?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.jx-nomore[data-v-332b08f2]{width:100%;height:100%;position:relative;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore[data-v-332b08f2]::before{content:" ";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:100%;top:%?18?%;left:0}.jx-nomore-text[data-v-332b08f2]{color:#999;font-size:%?24?%;text-align:center;padding:0 %?18?%;height:%?36?%;line-height:%?36?%;position:relative;z-index:1}.jx-nomore-dot[data-v-332b08f2]{position:relative;text-align:center;-webkit-display:flex;display:-webkit-box;display:flex;-webkit-justify-content:center;-webkit-box-pack:center;justify-content:center;margin-top:%?10?%;padding-bottom:%?6?%}.jx-nomore-dot[data-v-332b08f2]::before{content:"";position:absolute;border-bottom:%?1?% solid #e5e5e5;-webkit-transform:scaleY(.5);transform:scaleY(.5);width:%?360?%;top:%?18?%}.jx-dot-text[data-v-332b08f2]{position:relative;color:#e5e5e5;font-size:10px;text-align:center;width:%?50?%;height:%?36?%;line-height:%?36?%;-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:center center;transform-origin:center center;z-index:1}',""]),t.exports=e},4148:function(t,e,i){"use strict";i.r(e);var a=i("2927"),o=i("5035");for(var r in o)"default"!==r&&function(t){i.d(e,t,(function(){return o[t]}))}(r);i("c274");var n,s=i("f0c5"),l=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"3c456281",null,!1,a["a"],n);e["default"]=l.exports},5035:function(t,e,i){"use strict";i.r(e);var a=i("cd2c"),o=i.n(a);for(var r in a)"default"!==r&&function(t){i.d(e,t,(function(){return a[t]}))}(r);e["default"]=o.a},6164:function(t,e,i){"use strict";var a=i("053f"),o=i.n(a);o.a},"6e16":function(t,e,i){"use strict";i.r(e);var a=i("e958"),o=i.n(a);for(var r in a)"default"!==r&&function(t){i.d(e,t,(function(){return a[t]}))}(r);e["default"]=o.a},"7b49":function(t,e,i){var a=i("c6eb");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("570b95ae",a,!0,{sourceMap:!1,shadowMode:!1})},8139:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,".jx-loading-init[data-v-85db7258]{min-width:%?200?%;min-height:%?200?%;max-width:%?500?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;position:fixed;top:50%;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);z-index:9999;font-size:%?26?%;color:#fff;background:rgba(0,0,0,.7);border-radius:%?10?%}.jx-loading-center[data-v-85db7258]{width:%?50?%;height:%?50?%;border:3px solid #fff;border-radius:50%;margin:0 6px;display:inline-block;vertical-align:middle;-webkit-clip-path:polygon(0 0,100% 0,100% 40%,0 40%);clip-path:polygon(0 0,100% 0,100% 40%,0 40%);-webkit-animation:rotate-data-v-85db7258 1s linear infinite;animation:rotate-data-v-85db7258 1s linear infinite;margin-bottom:%?36?%}.jx-loadmore-tips[data-v-85db7258]{text-align:center;padding:0 %?20?%;box-sizing:border-box}@-webkit-keyframes rotate-data-v-85db7258{from{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes rotate-data-v-85db7258{from{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}",""]),t.exports=e},"918e":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return r})),i.d(e,"a",(function(){return a}));var o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.visible?i("v-uni-view",{staticClass:"jx-loading-init"},[i("v-uni-view",{staticClass:"jx-loading-center"}),i("v-uni-view",{staticClass:"jx-loadmore-tips"},[t._v(t._s(t.text))])],1):t._e()},r=[]},"95cc":function(t,e,i){"use strict";var a=i("ae94"),o=i.n(a);o.a},ae94:function(t,e,i){var a=i("3e9c");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("42a7678a",a,!0,{sourceMap:!1,shadowMode:!1})},c274:function(t,e,i){"use strict";var a=i("7b49"),o=i.n(a);o.a},c6eb:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.app[data-v-3c456281]{min-height:100%;background-color:#f7f7f7}.app .order[data-v-3c456281]{color:#333;border-radius:%?15?%}.app .order .order-tabs[data-v-3c456281]{border-top:%?1?% solid #f3f3f3;border-bottom:%?1?% solid #f3f3f3;background-color:#fff;margin-bottom:%?20?%;height:%?100?%;white-space:nowrap;position:fixed;width:100%;z-index:99}.app .order .order-tabs .order-tabs-item[data-v-3c456281]{display:inline-block;width:50%}.app .order .order-tabs .tab[data-v-3c456281]{height:%?100?%;font-size:11pt;border-right:%?1?% solid #f2f2f2}.app .order .order-tabs .tab .name[data-v-3c456281]{position:relative}.app .order .order-tabs .tab[data-v-3c456281]:last-child{border-right:0}.app .order .order-list[data-v-3c456281]{height:100%;background-color:#fff}.app .order .order-list .item[data-v-3c456281]{padding:%?30?%;border-bottom:%?1?% solid #f3f3f3}.app .order .order-list .item .user-status[data-v-3c456281]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;position:relative;margin-bottom:%?16?%}.app .order .order-list .item .user-status .acatar[data-v-3c456281]{width:%?100?%;height:%?100?%;border-radius:50%;margin-right:%?16?%}.app .order .order-list .item .user-status .name-datetime .name[data-v-3c456281]{display:-webkit-box;display:-webkit-flex;display:flex;line-height:%?37?%;max-width:%?142?%}.app .order .order-list .item .user-status .name-datetime .id[data-v-3c456281]{margin-left:%?12?%;color:#bc0100;padding:0 %?10?%;font-size:9pt;-webkit-transform:scale(.8);transform:scale(.8);line-height:%?32?%;border:%?1?% solid #bc0100;border-radius:%?14?%}.app .order .order-list .item .user-status .name-datetime .datetime[data-v-3c456281]{font-size:9pt}.app .order .order-list .item .user-status .status-text[data-v-3c456281]{position:absolute;top:0;right:0;padding:0 %?10?%;background-color:#bc0100;border:%?1?% solid #bc0100;border-radius:%?18?%;color:#fff;font-size:9pt;-webkit-transform:scale(.8);transform:scale(.8)}.app .order .order-list .item .user-status .price[data-v-3c456281]{font-size:%?30?%}.app .order .order-list .item .info[data-v-3c456281]{display:-webkit-box;display:-webkit-flex;display:flex;font-size:9pt}.app .order .order-list .item .info .mark[data-v-3c456281]{-webkit-box-flex:1;-webkit-flex:1;flex:1}.app .order .order-list .item .info .mark .goods-name[data-v-3c456281],\n.app .order .order-list .item .info .mark .order-id[data-v-3c456281]{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:%?420?%}.app .order .order-list .item .info .money .commission[data-v-3c456281]{color:#bc0100}',""]),t.exports=e},cd2c:function(t,e,i){"use strict";i("99af"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={data:function(){return{loading:!1,data:null,orderListTabs:["带货奖励","观看奖励"],currentTabIndex:0,list:[],page:1,is_no_more:!1,parent_agent:null,frozen_price:"",is_price:"",total_price:"",yesterday_price:"",http_url:"",look_type:"",textColor:"#bc0100",bg_url:"",navBg:"",navCol:""}},onReachBottom:function(t){this.getList()},computed:{date:function(){return function(t){return this.dateFormat(t)}}},onLoad:function(){this.navBg=this.globalSet("navBg"),this.navCol=this.globalSet("navCol"),this.textColor=this.globalSet("textCol"),this.bg_url=this.globalSet("imgUrl"),this.getInfo(),this.getList()},methods:{openUrl:function(t){uni.navigateTo({url:t})},tabChange:function(t){this.currentTabIndex=t,this.list=[],this.page=1,this.is_no_more=!1,0==t?this.http_url=this.$api.plugin.video.shopping_award:(this.http_url=this.$api.plugin.video.look_award,this.look_type=1==t?3:t-1),this.getList()},getInfo:function(){var t=this;this.loading=!0,this.$http.request({url:this.$api.plugin.video.user_award,method:"post"}).then((function(e){t.loading=!1,0==e.code&&(t.parent_agent=e.data,t.frozen_price=e.data.unsettled,t.is_price=e.data.settled,t.total_price=e.data.sum_award,t.yesterday_price=e.data.yt_award)}))},getList:function(){var t=this;if(this.loading=!0,this.is_no_more)return uni.showToast({title:"没有更多数据"}),void(this.loading=!1);this.$http.request({url:this.http_url||this.$api.plugin.video.shopping_award,method:"post",data:{page:this.page,look_type:this.look_type}}).then((function(e){t.loading=!1,0==e.code?(1==t.page?t.list=e.data.list:t.list.concat(e.data.list),t.page<e.data.pagination.page_count?t.page++:t.is_no_more=!0):uni.showToast({title:e.msg})}))},back:function(){this.navBack()}},onPullDownRefresh:function(){setTimeout((function(){uni.stopPullDownRefresh()}),1e3)}};e.default=a},d2fd:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"jxLoading",props:{text:{type:String,default:"正在加载..."},visible:{type:Boolean,default:!1}}};e.default=a},e958:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"jxNomore",props:{visible:{type:Boolean,default:!1},bgcolor:{type:String,default:"#fafafa"},isDot:{type:Boolean,default:!1},text:{type:String,default:"没有更多了"}},data:function(){return{dotText:"●"}}};e.default=a},eace:function(t,e,i){"use strict";i.r(e);var a=i("1668"),o=i("6e16");for(var r in o)"default"!==r&&function(t){i.d(e,t,(function(){return o[t]}))}(r);i("95cc");var n,s=i("f0c5"),l=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"332b08f2",null,!1,a["a"],n);e["default"]=l.exports}}]);