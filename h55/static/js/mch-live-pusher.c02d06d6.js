(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["mch-live-pusher"],{"0fc8":function(t,i,e){"use strict";e.r(i);var a=e("e3c6"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,(function(){return a[t]}))}(o);i["default"]=n.a},1111:function(t,i,e){"use strict";var a=e("4ea4");e("4160"),e("a9e3"),e("ac1f"),e("5319"),e("159b"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=a(e("5530")),o={name:"uniTransition",props:{show:{type:Boolean,default:!1},modeClass:{type:Array,default:function(){return[]}},duration:{type:Number,default:300},styles:{type:Object,default:function(){return{}}}},data:function(){return{isShow:!1,transform:"",ani:{in:"",active:""}}},watch:{show:{handler:function(t){t?this.open():this.close()},immediate:!0}},computed:{stylesObject:function(){var t=(0,n.default)((0,n.default)({},this.styles),{},{"transition-duration":this.duration/1e3+"s"}),i="";for(var e in t){var a=this.toLine(e);i+=a+":"+t[e]+";"}return i}},created:function(){},methods:{change:function(){this.$emit("click",{detail:this.isShow})},open:function(){var t=this;for(var i in clearTimeout(this.timer),this.isShow=!0,this.transform="",this.ani.in="",this.getTranfrom(!1))"opacity"===i?this.ani.in="fade-in":this.transform+="".concat(this.getTranfrom(!1)[i]," ");this.$nextTick((function(){setTimeout((function(){t._animation(!0)}),50)}))},close:function(t){clearTimeout(this.timer),this._animation(!1)},_animation:function(t){var i=this,e=this.getTranfrom(t);for(var a in this.transform="",e)"opacity"===a?this.ani.in="fade-".concat(t?"out":"in"):this.transform+="".concat(e[a]," ");this.timer=setTimeout((function(){t||(i.isShow=!1),i.$emit("change",{detail:i.isShow})}),this.duration)},getTranfrom:function(t){var i={transform:""};return this.modeClass.forEach((function(e){switch(e){case"fade":i.opacity=t?1:0;break;case"slide-top":i.transform+="translateY(".concat(t?"0":"-100%",") ");break;case"slide-right":i.transform+="translateX(".concat(t?"0":"100%",") ");break;case"slide-bottom":i.transform+="translateY(".concat(t?"0":"100%",") ");break;case"slide-left":i.transform+="translateX(".concat(t?"0":"-100%",") ");break;case"zoom-in":i.transform+="scale(".concat(t?1:.8,") ");break;case"zoom-out":i.transform+="scale(".concat(t?1:1.2,") ");break}})),i},_modeClassArr:function(t){var i=this.modeClass;if("string"!==typeof i){var e="";return i.forEach((function(i){e+=i+"-"+t+","})),e.substr(0,e.length-1)}return i+"-"+t},toLine:function(t){return t.replace(/([A-Z])/g,"-$1").toLowerCase()}}};i.default=o},"1c27":function(t,i,e){var a=e("d01a");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("9e62143c",a,!0,{sourceMap:!1,shadowMode:!1})},"23e2":function(t,i,e){"use strict";e.r(i);var a=e("f021"),n=e("5a74");for(var o in n)"default"!==o&&function(t){e.d(i,t,(function(){return n[t]}))}(o);e("b948");var r,s=e("f0c5"),c=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"3f9d119a",null,!1,a["a"],r);i["default"]=c.exports},2511:function(t,i,e){"use strict";var a=e("5b1a"),n=e.n(a);n.a},"3fd5":function(t,i,e){"use strict";var a=e("57f0"),n=e.n(a);n.a},"517a":function(t,i,e){"use strict";(function(t){var a=e("4ea4");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=a(e("cce2")),o={components:{uniPopup:n.default},data:function(){return{type:"",width:"500",height:"300",url:"rtmp://92090.livepush.myqcloud.com/live/222?txSecret=f73363d139f51c32e862a2f224293be3&txTime=5EDAEDD6",enableCamera:!1,context:null,markact:!0,arr:[],shopArr:[{img:"https://pic.downk.cc/item/5e8eee98504f4bcb0444a8ac.jpg",title:"测试商品",price:"111",num:"20"},{img:"https://pic.downk.cc/item/5e8eee98504f4bcb0444a8ac.jpg",title:"测试商品",price:"111",num:"20"},{img:"https://pic.downk.cc/item/5e8eee98504f4bcb0444a8ac.jpg",title:"测试商品",price:"111",num:"20"},{img:"https://pic.downk.cc/item/5e8eee98504f4bcb0444a8ac.jpg",title:"测试商品",price:"111",num:"20"},{img:"https://pic.downk.cc/item/5e8eee98504f4bcb0444a8ac.jpg",title:"测试商品",price:"111",num:"20"}],chatList:[{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"},{name:"用户01",text:"哈哈哈哈哈"}],beauty:0}},onLoad:function(){var t=uni.getSystemInfoSync();this.width=t.windowWidth,this.height=t.windowHeight},onReady:function(){},methods:{add:function(){t("log","s"," at mch/live/pusher.nvue:182")},openMark:function(t){this.type,this.$refs.popup.open()},openmeiyan:function(){0==this.beauty?this.beauty=9:this.beauty=0},returns:function(){this.stopLive(),uni.navigateBack({delta:1})},EnableCamera:function(){this.enableCamera=!0},startLive:function(){var i=this;this.context.start({success:function(e){t("log",e," at mch/live/pusher.nvue:210"),i.markact=!1,t("log","livePusher.start:"+JSON.stringify(e)," at mch/live/pusher.nvue:212")}})},stopLive:function(){this.context.stop({success:function(i){t("log",JSON.stringify(i)," at mch/live/pusher.nvue:219")}})},switchCamera:function(){this.context.switchCamera({success:function(i){t("log","livePusher.switchCamera:"+JSON.stringify(i)," at mch/live/pusher.nvue:226")}})}}};i.default=o}).call(this,e("0de9")["log"])},"57f0":function(t,i,e){var a=e("a943");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("5c5ebc9e",a,!0,{sourceMap:!1,shadowMode:!1})},"5a74":function(t,i,e){"use strict";e.r(i);var a=e("517a"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,(function(){return a[t]}))}(o);i["default"]=n.a},"5b1a":function(t,i,e){var a=e("9964");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("61e980d8",a,!0,{sourceMap:!1,shadowMode:!1})},"709b":function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return o})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.isShow?e("v-uni-view",{ref:"ani",staticClass:"uni-transition",class:[t.ani.in],style:"transform:"+t.transform+";"+t.stylesObject,on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.change.apply(void 0,arguments)}}},[t._t("default")],2):t._e()},o=[]},"761a":function(t,i,e){"use strict";e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return o})),e.d(i,"a",(function(){return a}));var a={uniTransition:e("bdb1").default},n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.showPopup?e("v-uni-view",{staticClass:"uni-popup",on:{touchmove:function(i){i.stopPropagation(),i.preventDefault(),arguments[0]=i=t.$handleEvent(i),t.clear.apply(void 0,arguments)}}},[e("uni-transition",{attrs:{"mode-class":["fade"],styles:t.maskClass,duration:t.duration,show:t.showTrans},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.onTap.apply(void 0,arguments)}}}),e("uni-transition",{attrs:{"mode-class":t.ani,styles:t.transClass,duration:t.duration,show:t.showTrans},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.onTap.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"uni-popup__wrapper-box",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.clear.apply(void 0,arguments)}}},[t._t("default")],2)],1)],1):t._e()},o=[]},"80cd":function(t,i,e){"use strict";e.r(i);var a=e("1111"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,(function(){return a[t]}))}(o);i["default"]=n.a},9964:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.uni-popup[data-v-697b345a]{position:fixed;top:var(--window-top);bottom:0;left:0;right:0;z-index:99}.uni-popup__mask[data-v-697b345a]{position:absolute;top:0;bottom:0;left:0;right:0;background-color:rgba(0,0,0,.4);opacity:0}.mask-ani[data-v-697b345a]{-webkit-transition-property:opacity;transition-property:opacity;-webkit-transition-duration:.2s;transition-duration:.2s}.uni-top-mask[data-v-697b345a]{opacity:1}.uni-bottom-mask[data-v-697b345a]{opacity:1}.uni-center-mask[data-v-697b345a]{opacity:1}.uni-popup__wrapper[data-v-697b345a]{display:block;position:absolute}.top[data-v-697b345a]{top:0;left:0;right:0;-webkit-transform:translateY(-500px);transform:translateY(-500px)}.bottom[data-v-697b345a]{bottom:0;left:0;right:0;-webkit-transform:translateY(500px);transform:translateY(500px)}.center[data-v-697b345a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;bottom:0;left:0;right:0;top:0;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-transform:scale(1.2);transform:scale(1.2);opacity:0}.uni-popup__wrapper-box[data-v-697b345a]{display:block;position:relative}.content-ani[data-v-697b345a]{-webkit-transition-property:opacity,-webkit-transform;transition-property:opacity,-webkit-transform;transition-property:transform,opacity;transition-property:transform,opacity,-webkit-transform;-webkit-transition-duration:.2s;transition-duration:.2s}.uni-top-content[data-v-697b345a]{-webkit-transform:translateY(0);transform:translateY(0)}.uni-bottom-content[data-v-697b345a]{-webkit-transform:translateY(0);transform:translateY(0)}.uni-center-content[data-v-697b345a]{-webkit-transform:scale(1);transform:scale(1);opacity:1}',""]),t.exports=i},a943:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,".uni-transition[data-v-1bd1f14e]{-webkit-transition-timing-function:ease;transition-timing-function:ease;-webkit-transition-duration:.3s;transition-duration:.3s;-webkit-transition-property:opacity,-webkit-transform;transition-property:opacity,-webkit-transform;transition-property:transform,opacity;transition-property:transform,opacity,-webkit-transform}.fade-in[data-v-1bd1f14e]{opacity:0}.fade-active[data-v-1bd1f14e]{opacity:1}.slide-top-in[data-v-1bd1f14e]{\n\t/* transition-property: transform, opacity; */-webkit-transform:translateY(-100%);transform:translateY(-100%)}.slide-top-active[data-v-1bd1f14e]{-webkit-transform:translateY(0);transform:translateY(0)\n\t/* opacity: 1; */}.slide-right-in[data-v-1bd1f14e]{-webkit-transform:translateX(100%);transform:translateX(100%)}.slide-right-active[data-v-1bd1f14e]{-webkit-transform:translateX(0);transform:translateX(0)}.slide-bottom-in[data-v-1bd1f14e]{-webkit-transform:translateY(100%);transform:translateY(100%)}.slide-bottom-active[data-v-1bd1f14e]{-webkit-transform:translateY(0);transform:translateY(0)}.slide-left-in[data-v-1bd1f14e]{-webkit-transform:translateX(-100%);transform:translateX(-100%)}.slide-left-active[data-v-1bd1f14e]{-webkit-transform:translateX(0);transform:translateX(0);opacity:1}.zoom-in-in[data-v-1bd1f14e]{-webkit-transform:scale(.8);transform:scale(.8)}.zoom-out-active[data-v-1bd1f14e]{-webkit-transform:scale(1);transform:scale(1)}.zoom-out-in[data-v-1bd1f14e]{-webkit-transform:scale(1.2);transform:scale(1.2)}",""]),t.exports=i},b948:function(t,i,e){"use strict";var a=e("1c27"),n=e.n(a);n.a},bdb1:function(t,i,e){"use strict";e.r(i);var a=e("709b"),n=e("80cd");for(var o in n)"default"!==o&&function(t){e.d(i,t,(function(){return n[t]}))}(o);e("3fd5");var r,s=e("f0c5"),c=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"1bd1f14e",null,!1,a["a"],r);i["default"]=c.exports},cce2:function(t,i,e){"use strict";e.r(i);var a=e("761a"),n=e("0fc8");for(var o in n)"default"!==o&&function(t){e.d(i,t,(function(){return n[t]}))}(o);e("2511");var r,s=e("f0c5"),c=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"697b345a",null,!1,a["a"],r);i["default"]=c.exports},d01a:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,".add-shop[data-v-3f9d119a]{font-size:16px;color:#333;position:absolute;top:20px;left:20px;z-index:10}.shop-list-but-box[data-v-3f9d119a]{position:absolute;bottom:10px;right:20px;\n\t\ndisplay:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row\n}.shop-list-but[data-v-3f9d119a]{color:#fff;border-radius:2px;font-size:16px;background-color:#d4237a;padding:10px 20px 10px 20px}.mark-title[data-v-3f9d119a]{padding-top:20px;padding-bottom:20px;text-align:center;font-size:20px}.shop-list-img[data-v-3f9d119a]{margin-right:10px;width:100px;height:100px}.shop-list-title[data-v-3f9d119a]{font-size:20px;color:#333}.shop-list-num[data-v-3f9d119a]{font-size:16px;color:silver;padding-top:10px}.shop-list-price[data-v-3f9d119a]{font-size:16px;color:#d4237a;padding-top:10px}.scroll-Ys[data-v-3f9d119a]{background-color:#fff;height:300px;width:100%}.shop-list-box[data-v-3f9d119a]{position:relative;background-color:#fff;width:100%;height:380px}.shop-list-list[data-v-3f9d119a]{position:relative;padding-top:5px;padding-bottom:5px;\n\t\ndisplay:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:start;-webkit-align-items:flex-start;align-items:flex-start;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row\n}.chat-username[data-v-3f9d119a]{font-size:18px;color:#fff}.chat-usertext[data-v-3f9d119a]{font-size:16px;color:#fff}.chat-list[data-v-3f9d119a]{margin-top:5px;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:start;-webkit-justify-content:flex-start;justify-content:flex-start;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;background-color:rgba(0,0,0,.5);padding:5px 10px 5px 10px;border-radius:3px}.scroll-Y[data-v-3f9d119a]{height:150px;width:250px}.chat[data-v-3f9d119a]{position:fixed;z-index:10;bottom:80px}.but-img[data-v-3f9d119a]{width:20px;height:20px}.meiyanbut[data-v-3f9d119a]{padding:10px 20px 10px 20px;margin-left:20px;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.but-text[data-v-3f9d119a]{padding-top:3px;font-size:13px;color:#fff}.id[data-v-3f9d119a]{font-size:14px;color:#fff}.text-box[data-v-3f9d119a]{font-size:12px;color:#fff}.user-img[data-v-3f9d119a]{margin-right:5px;width:30px;height:30px;border-radius:50%}.mark-text[data-v-3f9d119a]{color:#fff;font-size:20px}.mark[data-v-3f9d119a]{position:fixed;top:0;background-color:#000;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.but[data-v-3f9d119a]{background-color:rgba(0,0,0,.4);position:fixed;bottom:0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;padding-bottom:20px}.tui[data-v-3f9d119a]{width:200px;height:200px}.arrow-box[data-v-3f9d119a]{position:fixed;top:60px;left:20px;z-index:10;border-radius:20px;background-color:rgba(0,0,0,.4)}.arrow-boxs[data-v-3f9d119a]{padding:10px 20px 10px 20px;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;position:fixed;top:60px;left:140px;z-index:10;border-radius:30px;background-color:rgba(0,0,0,.4)}.jiesu[data-v-3f9d119a]{padding:10px 20px 10px 20px;color:#fff;font-size:16px}.arrow[data-v-3f9d119a]{width:15px;height:15px}",""]),t.exports=i},e3c6:function(t,i,e){"use strict";var a=e("4ea4");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=a(e("bdb1")),o={name:"UniPopup",components:{uniTransition:n.default},props:{animation:{type:Boolean,default:!0},type:{type:String,default:"center"},maskClick:{type:Boolean,default:!0}},data:function(){return{duration:300,ani:[],showPopup:!1,showTrans:!1,maskClass:{position:"fixed",bottom:0,top:0,left:0,right:0,backgroundColor:"rgba(0, 0, 0, 0.4)"},transClass:{position:"fixed",left:0,right:0}}},watch:{type:{handler:function(t){switch(this.type){case"top":this.ani=["slide-top"],this.transClass={position:"fixed",left:0,right:0};break;case"bottom":this.ani=["slide-bottom"],this.transClass={position:"fixed",left:0,right:0,bottom:0};break;case"center":this.ani=["zoom-out","fade"],this.transClass={position:"fixed",display:"flex",flexDirection:"column",bottom:0,left:0,right:0,top:0,justifyContent:"center",alignItems:"center"};break}},immediate:!0}},created:function(){this.animation?this.duration=300:this.duration=0},methods:{clear:function(t){t.stopPropagation()},open:function(){var t=this;this.showPopup=!0,this.$nextTick((function(){clearTimeout(t.timer),t.timer=setTimeout((function(){t.showTrans=!0}),50)})),this.$emit("change",{show:!0})},close:function(t){var i=this;this.showTrans=!1,this.$nextTick((function(){clearTimeout(i.timer),i.timer=setTimeout((function(){i.$emit("change",{show:!1}),i.showPopup=!1}),300)}))},onTap:function(){this.maskClick&&this.close()}}};i.default=o},f021:function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return o})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"app"},[t._v("直播暂时只支持 小程序 和 APP")])},o=[]}}]);