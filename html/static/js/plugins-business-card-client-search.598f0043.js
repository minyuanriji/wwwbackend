(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["plugins-business-card-client-search"],{"01b6":function(t,i,e){"use strict";var n=e("4ea4");e("a630"),e("d3b7"),e("6062"),e("3ca3"),e("ddb0"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var s=n(e("2909")),a=n(e("5500")),o=n(e("d482")),r=n(e("dd80")),c={components:{tuiIcon:a.default,tuiTag:o.default,tuiActionsheet:r.default},data:function(){return{searchVal:"",history:[],key:"",showActionSheet:!1,tips:"确认清空搜索历史吗？",type:""}},onLoad:function(t){var i=this;this.key=t.key,this.type=t.type,"businessList"==this.type?uni.getStorage({key:"business-history",success:function(t){var e;console.log(t.data,"res.data"),console.log(i.history,"this.history"),(e=i.history).push.apply(e,(0,s.default)(t.data))}}):uni.getStorage({key:"client-history",success:function(t){var e;(e=i.history).push.apply(e,(0,s.default)(t.data))}})},methods:{navTo:function(t){"businessList"==this.type?uni.navigateTo({url:"/plugins/business-card/business-list?keywords=".concat(t)}):uni.navigateTo({url:"/plugins/business-card/client/list?keywords=".concat(t)})},search:function(){this.key?(this.history.unshift(this.key),this.history=Array.from(new Set(this.history)),"businessList"==this.type?(uni.setStorage({key:"business-history",data:this.history}),uni.navigateTo({url:"/plugins/business-card/business-list?keywords=".concat(this.key)})):(uni.setStorage({key:"client-history",data:this.history}),uni.navigateTo({url:"/plugins/business-card/client/list?keywords=".concat(this.key)}))):this.$http.toast("请输入需要搜索的内容")},cleanKey:function(){this.key=""},closeActionSheet:function(){this.showActionSheet=!1},openActionSheet:function(){this.showActionSheet=!0},itemClick:function(t){var i=t.index;0==i&&(this.showActionSheet=!1,this.history=[],"businessList"==this.type?uni.removeStorage({key:"business-history"}):uni.removeStorage({key:"client-history"}))}}};i.default=c},"1cb4":function(t,i,e){"use strict";e.r(i);var n=e("01b6"),s=e.n(n);for(var a in n)"default"!==a&&function(t){e.d(i,t,(function(){return n[t]}))}(a);i["default"]=s.a},2886:function(t,i,e){"use strict";e.r(i);var n=e("b3da"),s=e("1cb4");for(var a in s)"default"!==a&&function(t){e.d(i,t,(function(){return s[t]}))}(a);e("5b75");var o,r=e("f0c5"),c=Object(r["a"])(s["default"],n["b"],n["c"],!1,null,"1b859082",null,!1,n["a"],o);i["default"]=c.exports},"33dd":function(t,i,e){var n=e("24fb");i=n(!1),i.push([t.i,"uni-page-body[data-v-1b859082]{color:#333;background:#fff}.container[data-v-1b859082]{padding:0 %?30?% %?30?% %?30?%;-webkit-box-sizing:border-box;box-sizing:border-box}.tui-searchbox[data-v-1b859082]{padding:%?20?% 0;-webkit-box-sizing:border-box;box-sizing:border-box;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.tui-search-input[data-v-1b859082]{width:100%;height:%?66?%;-webkit-border-radius:%?35?%;border-radius:%?35?%;padding:0 %?30?%;-webkit-box-sizing:border-box;box-sizing:border-box;background:#f2f2f2;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-flex-wrap:nowrap;flex-wrap:nowrap}.tui-input[data-v-1b859082]{-webkit-box-flex:1;-webkit-flex:1;flex:1;color:#333;padding:0 %?16?%;font-size:11pt}.tui-input-plholder[data-v-1b859082]{font-size:11pt;color:#b2b2b2}.tui-cancle[data-v-1b859082]{color:#888;font-size:11pt;padding-left:%?30?%;-webkit-flex-shrink:0;flex-shrink:0}.tui-history-header[data-v-1b859082]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;padding:%?30?% 0}.tui-icon-delete[data-v-1b859082]{padding:%?10?%}.tui-search-title[data-v-1b859082]{font-size:11pt;font-weight:700}.tui-hot-header[data-v-1b859082]{padding:%?30?% 0}.tui-tag-class[data-v-1b859082]{display:inline-block;margin-bottom:%?20?%;margin-right:%?20?%;font-size:9pt!important}.tui-history-content[data-v-1b859082]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap}body.?%PAGE?%[data-v-1b859082]{background:#fff}",""]),t.exports=i},"5b75":function(t,i,e){"use strict";var n=e("93dd"),s=e.n(n);s.a},"93dd":function(t,i,e){var n=e("33dd");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var s=e("4f06").default;s("7d338452",n,!0,{sourceMap:!1,shadowMode:!1})},b3da:function(t,i,e){"use strict";var n;e.d(i,"b",(function(){return s})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){return n}));var s=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"container"},[e("v-uni-view",{staticClass:"tui-searchbox"},[e("v-uni-view",{staticClass:"tui-search-input"},[e("v-uni-view",[e("tui-icon",{attrs:{name:"search",size:16,color:"#333"}})],1),e("v-uni-input",{staticClass:"tui-input",attrs:{"confirm-type":"search",placeholder:"搜索他的名字吧",focus:!0,"auto-focus":!0,"placeholder-class":"tui-input-plholder"},on:{confirm:function(i){arguments[0]=i=t.$handleEvent(i),t.search.apply(void 0,arguments)}},model:{value:t.key,callback:function(i){t.key="string"===typeof i?i.trim():i},expression:"key"}}),e("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:t.key,expression:"key"}],on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.cleanKey.apply(void 0,arguments)}}},[e("tui-icon",{attrs:{name:"close-fill",size:16,color:"#bcbcbc"}})],1)],1),e("v-uni-view",{staticClass:"tui-cancle",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.search.apply(void 0,arguments)}}},[t._v("搜索")])],1),t.history.length>0?e("v-uni-view",{staticClass:"tui-search-history"},[e("v-uni-view",{staticClass:"tui-history-header"},[e("v-uni-view",{staticClass:"tui-search-title"},[t._v("搜索历史")]),e("tui-icon",{staticClass:"tui-icon-delete",attrs:{name:"delete",size:14,color:"#333"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.openActionSheet.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"tui-history-content"},t._l(t.history,(function(i,n){return e("v-uni-view",{key:n,on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.navTo(i)}}},[e("tui-tag",{attrs:{type:"gray",shape:"circle"}},[t._v(t._s(i))])],1)})),1)],1):t._e(),e("tui-actionsheet",{attrs:{show:t.showActionSheet,tips:t.tips},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.itemClick.apply(void 0,arguments)},cancel:function(i){arguments[0]=i=t.$handleEvent(i),t.closeActionSheet.apply(void 0,arguments)}}})],1)},a=[]}}]);