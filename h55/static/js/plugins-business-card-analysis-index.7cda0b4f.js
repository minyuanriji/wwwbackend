(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["plugins-business-card-analysis-index"],{"02bf":function(t,e,a){"use strict";a.r(e);var i=a("2158"),s=a.n(i);for(var n in i)"default"!==n&&function(t){a.d(e,t,(function(){return i[t]}))}(n);e["default"]=s.a},"0721":function(t,e,a){var i=a("3d87");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var s=a("4f06").default;s("760a4f98",i,!0,{sourceMap:!1,shadowMode:!1})},2158:function(t,e,a){"use strict";(function(t){var i=a("4ea4");a("7db0"),a("4160"),a("b680"),a("d3b7"),a("e25e"),a("3ca3"),a("159b"),a("ddb0"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var s,n=i(a("2909")),r=i(a("9dc1")),o=null,l={data:function(){return{img_url:this.$api.img_url,page:1,is_no_more:!1,department_text:"",department_id:"",tabs_list:["概况","销售排行","AI分析","热度排行"],tabs_index:0,time_list:["昨天","7天","15天","30天","汇总"],time_index:0,time_list2:["7天","15天","30天"],time_index2:0,time_index3:0,time_index4:0,time_index5:0,MallOrderStat_data:"",AddCustomer_data:"",Follow_data:"",UserActivity_data:"",CustomerInterest_data:"",Ai_data:[],cWidth:"",cHeight:"",pixelRatio:1,mWidth:"",mHeight:"",arcbarWidth:0,general_data:"",sale_list:["客户人数","线索数量","订单数量","交易金额"],sale_index:0,sale_data:[],rWidth:"",rHeight:"",arr:[0,1,2,3,4,5],Ai_data_item:[],hot_list:["商品热度","名片热度","资讯热度","直播热度"],hot_index:0,hot_data:[],is_modal_day:!1,day_index:0,day_list:["近7天","近15天","近30天"],is_modal:!1}},onLoad:function(){this.moreRequest(),this.getOverviewData(),s=this,this.cWidth=uni.upx2px(690),this.cHeight=uni.upx2px(470),this.mWidth=uni.upx2px(340),this.mHeight=uni.upx2px(140),this.arcbarWidth=uni.upx2px(26)},onReachBottom:function(){switch(this.tabs_index){case 1:this.getSalesRank();break;case 2:this.getAI();break;case 3:this.getHotRank();break}},computed:{calculation:function(){return function(t){return parseInt(100*t)+"%"}}},methods:{getOverviewData:function(){var t=this;this.$http.request({url:this.$api.plugin.business.general,method:"post",showLoading:!0,data:{time_type:this.time_index,department_id:this.department_id||0}}).then((function(e){0==e.code?(t.general_data=e.data,t.initDepartment(e.data.department_list)):4==e.code?(t.$http.toast(e.msg),setTimeout((function(){t.navBack()}),1e3)):t.$http.toast(e.msg)}))},getMallOrderStat:function(){var t=this;this.$http.request({url:this.$api.plugin.business.mall_order_stat,method:"post",showLoading:!0,data:{time_type:this.time_index2+1,department_id:this.department_id||0}}).then((function(e){0==e.code?(t.MallOrderStat_data=e.data.list,t.getServerData()):t.$http.toast(e.msg)}))},getAddCustomer:function(){var t=this;this.$http.request({url:this.$api.plugin.business.add_customer_stat,method:"post",showLoading:!0,data:{time_type:this.time_index3+1,department_id:this.department_id||0}}).then((function(e){0==e.code?(t.AddCustomer_data=e.data.list,t.getServerData()):t.$http.toast(e.msg)}))},getFollow:function(){var t=this;this.$http.request({url:this.$api.plugin.business.follow_customer_stat,method:"post",showLoading:!0,data:{time_type:this.time_index4+1,department_id:this.department_id||0}}).then((function(e){0==e.code?(t.Follow_data=e.data.list,t.getServerData()):t.$http.toast(e.msg)}))},getCustomerInterest:function(){var t=this;this.$http.request({url:this.$api.plugin.business.interest_stat,method:"post",showLoading:!0,data:{time_type:0,department_id:this.department_id||0}}).then((function(e){0==e.code?(t.CustomerInterest_data=e.data.list,t.getServerData()):t.$http.toast(e.msg)}))},getCustomerAvtive:function(){var t=this;this.$http.request({url:this.$api.plugin.business.user_activity_stat,method:"post",showLoading:!0,data:{time_type:this.time_index5+1,department_id:this.department_id||0}}).then((function(e){0==e.code?(t.UserActivity_data=e.data.list,t.getServerData()):t.$http.toast(e.msg)}))},moreRequest:function(){var t=this,e=this.$http.request({url:this.$api.plugin.business.mall_order_stat,method:"post",showLoading:!0,data:{time_type:this.time_index2+1,department_id:this.department_id||0}}),a=this.$http.request({url:this.$api.plugin.business.add_customer_stat,method:"post",showLoading:!0,data:{time_type:this.time_index3+1,department_id:this.department_id||0}}),i=this.$http.request({url:this.$api.plugin.business.follow_customer_stat,method:"post",showLoading:!0,data:{time_type:this.time_index4+1,department_id:this.department_id||0}}),s=this.$http.request({url:this.$api.plugin.business.user_activity_stat,method:"post",showLoading:!0,data:{time_type:this.time_index5+1,department_id:this.department_id||0}}),n=this.$http.request({url:this.$api.plugin.business.interest_stat,method:"post",showLoading:!0,data:{time_type:0,department_id:this.department_id||0}}),r=Promise.all([e,a,i,s,n]);r.then((function(e){t.MallOrderStat_data=e[0].data.list,t.AddCustomer_data=e[1].data.list,t.Follow_data=e[2].data.list,t.UserActivity_data=e[3].data.list,t.CustomerInterest_data=e[4].data.list,t.getServerData()}))},getSalesRank:function(){var t=this;this.is_no_more?this.$http.toast("没有更多信息了"):this.$http.request({url:this.$api.plugin.business.sales_rank,method:"post",showLoading:!0,data:{department_id:this.department_id||0,page_type:this.sale_index+1,page:this.page}}).then((function(e){var a;0==e.code?((a=t.sale_data).push.apply(a,(0,n.default)(e.data.list)),e.data.pagination.page_count>=t.page?t.page++:(t.$http.toast("没有更多信息了"),t.is_no_more=!0)):t.$http.toast(e.msg)}))},getAI:function(){var t=this;this.is_no_more?this.$http.toast("没有更多信息了"):this.$http.request({url:this.$api.plugin.business.ai_analysis,method:"post",showLoading:!0,data:{department_id:this.department_id||0,page:this.page}}).then((function(e){var a;0==e.code?((a=t.Ai_data).push.apply(a,(0,n.default)(e.data.list)),t.Ai_data_item=[],t.Ai_data.forEach((function(e,a){a>2&&t.Ai_data_item.push(e)})),t.getServerData(),e.data.pagination.page_count>=t.page?t.page++:(t.$http.toast("没有更多信息了"),t.is_no_more=!0)):t.$http.toast(e.msg)}))},getHotRank:function(){var t=this;this.is_no_more?this.$http.toast("没有更多信息了"):this.$http.request({url:this.$api.plugin.business.hot_rank,method:"post",showLoading:!0,data:{page_type:this.hot_index+1,time_type:this.day_index+1,department_id:this.department_id||0,page:this.page}}).then((function(e){var a;0==e.code?((a=t.hot_data).push.apply(a,(0,n.default)(e.data.list)),e.data.pagination.page_count>=t.page?t.page++:(t.$http.toast("没有更多信息了"),t.is_no_more=!0)):t.$http.toast(e.msg)}))},showDayPop:function(){this.is_modal_day=!0},daySelect:function(t){this.day_index=t,this.is_modal_day=!1,this.hot_data=[],this.page=1,this.is_no_more=!1,this.getHotRank()},initDepartment:function(t){var e=this;if(""==this.department_id)this.department_text=t[0].name;else{var a=t.find((function(t){return t.id==e.department_id}));this.department_text=a.name}},depSelect:function(t,e){switch(this.department_text=this.general_data.department_list[t].name,this.is_modal=!1,this.department_id=e,this.page=1,this.is_no_more=!1,this.tabs_index){case 0:this.getOverviewData(),this.moreRequest();break;case 1:this.sale_data=[],this.getSalesRank();break;case 2:this.Ai_data=[],this.getAI();break;case 3:this.hot_data=[],this.getHotRank();break}},showPop:function(){this.is_modal=!0},hidePop:function(){this.is_modal=!1,this.is_modal_day=!1},tabSwitch:function(t){switch(this.tabs_index=t,this.page=1,this.is_no_more=!1,t){case 0:this.moreRequest();break;case 1:this.sale_data=[],this.getSalesRank();break;case 2:this.Ai_data=[],this.getAI();break;case 3:this.hot_data=[],this.getHotRank();break}},switchTime:function(t,e){"time1"==e?(this.time_index=t,this.getOverviewData()):"time2"==e?(this.time_index2=t,this.getMallOrderStat()):"time3"==e?(this.time_index3=t,this.getAddCustomer()):"time4"==e?(this.time_index4=t,this.getFollow()):"time5"==e&&(this.time_index5=t,this.getCustomerAvtive())},switchSale:function(t){this.sale_index=t,this.sale_data=[],this.page=1,this.is_no_more=!1,this.getSalesRank()},switchHot:function(t){this.hot_index=t,this.hot_data=[],this.page=1,this.is_no_more=!1,this.getHotRank()},getServerData:function(){if(2==this.tabs_index)s.showRadar("canvasRadar",this.Ai_data[0].radar_data),s.showRadar("canvasRadarTwo",this.Ai_data[1].radar_data),s.showRadar("canvasRadarThree",this.Ai_data[2].radar_data),this.Ai_data_item.forEach((function(t,e){s.showRadar("canvasRadar".concat(e),t.radar_data)}));else{s.showLineA("canvasLineA",this.MallOrderStat_data),s.showLineOA("canvasLineOA",this.AddCustomer_data),s.showLineOA2("canvasLineOA2",this.Follow_data),s.showLineOA3("canvasLineOA3",this.UserActivity_data),t("log",this.CustomerInterest_data,"CustomerInterest_data"," at plugins/business-card/analysis/index.vue:827");var e={series:this.CustomerInterest_data};this.showArea("canvasArea",e)}},showRadar:function(t,e){var a,i;"canvasRadar"==t?(s.rWidth=uni.upx2px(650),s.rHeight=uni.upx2px(500),i=[15,15,0,15]):"canvasRadarTwo"==t||"canvasRadarThree"==t?(s.rWidth=uni.upx2px(390),s.rHeight=uni.upx2px(390),e.categories=["","","","","",""],i=[15,15,0,15]):(s.rWidth=uni.upx2px(242),s.rHeight=uni.upx2px(290),e.categories=["","","","","",""],i=[10,0,0,0]),new r.default({$this:s,canvasId:t,type:"radar",fontSize:10,padding:i,legend:{show:!1},background:"#FFFFFF",pixelRatio:s.pixelRatio,animation:!1,dataLabel:!1,categories:e.categories,series:e.series,width:s.rWidth*s.pixelRatio,height:s.rHeight*s.pixelRatio,dataPointShape:!1,extra:{radar:{max:200,gridCount:4,opacity:1,labelColor:a}}})},showArea:function(t,e){var a=this.arcbarWidth,i=this.mWidth/2-a,n=this.mHeight-a,o=(i*i+n*n)/n/2,l={x:this.mWidth/2*this.pixelRatio,y:(o+a)*this.pixelRatio};new r.default({$this:s,canvasId:t,type:"arcbar",fontSize:11,colors:["#BC0100","#F12726","#FF6665"],legend:{show:!1},background:"#FFFFFF",pixelRatio:s.pixelRatio,series:e.series,animation:!1,width:s.mWidth*s.pixelRatio,height:s.mHeight*s.pixelRatio,extra:{arcbar:{type:"circle",width:a*s.pixelRatio,backgroundColor:"rgba(233,233,233,0.4)",startAngle:1.5,endAngle:1e-4,radius:o*s.pixelRatio,gap:10*s.pixelRatio,center:l}}})},showLineOA:function(t,e){o=new r.default({$this:s,canvasId:t,type:"area",fontSize:11,padding:[15,20,0,15],legend:{show:!1},background:"#FFFFFF",pixelRatio:s.pixelRatio,categories:e.categories,series:e.series,animation:!1,xAxis:{type:"grid",gridColor:"#ffffff",gridType:"dash",dashLength:8,labelCount:10,boundaryGap:"justify",rotateLabel:!0},yAxis:{gridType:"dash",gridColor:"#EEEEEE",dashLength:8,splitNumber:5,format:function(t){return t.toFixed(0)}},width:s.cWidth*s.pixelRatio,height:s.cHeight*s.pixelRatio,extra:{area:{type:"curve",addLine:!0,gradient:!0,opacity:.7}}})},showLineOA2:function(t,e){o=new r.default({$this:s,canvasId:t,type:"area",fontSize:11,padding:[15,20,0,15],legend:{show:!1},background:"#FFFFFF",pixelRatio:s.pixelRatio,categories:e.categories,series:e.series,animation:!1,xAxis:{type:"grid",gridColor:"#ffffff",gridType:"dash",dashLength:8,labelCount:10,boundaryGap:"justify",rotateLabel:!0},yAxis:{gridType:"dash",gridColor:"#EEEEEE",dashLength:8,splitNumber:5,format:function(t){return t.toFixed(0)}},width:s.cWidth*s.pixelRatio,height:s.cHeight*s.pixelRatio,extra:{area:{type:"curve",addLine:!0,gradient:!0,opacity:.7}}})},showLineOA3:function(t,e){o=new r.default({$this:s,canvasId:t,type:"area",fontSize:11,padding:[15,20,0,15],legend:{show:!1},background:"#FFFFFF",pixelRatio:s.pixelRatio,categories:e.categories,series:e.series,animation:!1,xAxis:{type:"grid",gridColor:"#ffffff",gridType:"dash",dashLength:8,labelCount:10,boundaryGap:"justify",rotateLabel:!0},yAxis:{gridType:"dash",gridColor:"#EEEEEE",dashLength:8,splitNumber:5,format:function(t){return t.toFixed(0)}},width:s.cWidth*s.pixelRatio,height:s.cHeight*s.pixelRatio,extra:{area:{type:"curve",addLine:!0,gradient:!0,opacity:.7}}})},showLineA:function(t,e){o=new r.default({$this:s,canvasId:t,type:"line",fontSize:10,padding:[15,20,0,15],colors:["#BC0100","#2873F2"],legend:{show:!0,position:"top"},dataLabel:!0,dataPointShape:!0,background:"#FFFFFF",pixelRatio:s.pixelRatio,categories:e.categories,series:e.series,animation:!1,xAxis:{type:"grid",gridColor:"#ffffff",gridType:"dash",dashLength:8,labelCount:10,boundaryGap:"justify",rotateLabel:!0},yAxis:{gridType:"grid",gridColor:"#e5e4e6",dashLength:8,splitNumber:5,format:function(t){return t.toFixed(0)}},width:s.cWidth*s.pixelRatio,height:s.cHeight*s.pixelRatio,extra:{line:{type:"curve"}}})},touchLineA:function(t){o.touchLegend(t),o.showToolTip(t,{format:function(t,e){return e+" "+t.name+":"+t.data}})}}};e.default=l}).call(this,a("0de9")["log"])},"3d87":function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 颜色变量 */\n/* 商城主题色 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */uni-page-body[data-v-4e1bcf7c]{background:#fff}.select[data-v-4e1bcf7c]{color:#000;font-size:%?38?%;font-weight:700}.select .triangle[data-v-4e1bcf7c]{width:0;height:0;border-width:%?16?% %?10?% %?0?% %?10?%;border-color:#000 transparent transparent transparent;border-style:solid;margin-left:%?10?%}.tabs-box[data-v-4e1bcf7c]{padding:%?60?% 0 %?40?%;border-bottom:1px solid #f2f2f2}.tabs-box .tabs-item[data-v-4e1bcf7c]{width:25%;text-align:center;color:#666;font-size:%?30?%;letter-spacing:1px}.tabs-box .tabs-active[data-v-4e1bcf7c]{color:#bc0100;padding-bottom:%?20?%;border-bottom:2px solid #bc0100}.bor[data-v-4e1bcf7c]{border-bottom:1px solid #f2f2f2}.timeTab-box[data-v-4e1bcf7c]{padding:%?20?% %?30?%;background:#fff;border-top:1px solid #f2f2f2}.timeTab-box .timeTab-item[data-v-4e1bcf7c]{width:%?110?%;height:%?50?%;text-align:center;line-height:%?50?%;border:1px solid silver;border-radius:%?16?%;font-size:%?24?%}.timeTab-box .active[data-v-4e1bcf7c]{border:1px solid #bc0100;color:#bc0100}.content[data-v-4e1bcf7c]{background:#fff;padding:%?30?%;-webkit-flex-wrap:wrap;flex-wrap:wrap}.content .content-item[data-v-4e1bcf7c]{width:%?216?%;height:%?216?%;border:1px solid silver;font-size:%?32?%;color:#bc0100;margin-bottom:%?18?%}.content .content-item .title[data-v-4e1bcf7c]{color:#8f8f8f;font-size:%?24?%}.funnel-title[data-v-4e1bcf7c]{font-size:%?30?%;color:#000;text-align:center;padding-top:%?50?%}.funnel[data-v-4e1bcf7c]{color:#fff;margin:%?40?% 0;background:#fff}.funnel .floor[data-v-4e1bcf7c]{height:%?56?%;margin-bottom:%?20?%}.funnel .floor .left-triangle[data-v-4e1bcf7c]{width:0;height:0;border-color:#ff6565 #ff6565 transparent transparent;border-width:%?30?% %?18?% %?30?% %?18?%;border-style:solid}.funnel .floor .center-rectangle[data-v-4e1bcf7c]{background:#ff6565;width:%?380?%;height:%?60?%;font-size:%?26?%;line-height:%?60?%;text-align:center}.funnel .floor .right-triangle[data-v-4e1bcf7c]{width:0;height:0;border-color:#ff6565 transparent transparent #ff6565;border-width:%?30?% %?18?% %?30?% %?18?%;border-style:solid}.funnel .floor .left-triangle2[data-v-4e1bcf7c]{border-color:#f12625 #f12625 transparent transparent}.funnel .floor .center-rectangle2[data-v-4e1bcf7c]{width:%?294?%;background:#f12625}.funnel .floor .right-triangle2[data-v-4e1bcf7c]{border-color:#f12625 transparent transparent #f12625}.funnel .floor .left-triangle3[data-v-4e1bcf7c]{border-color:#bb0100 #bb0100 transparent transparent}.funnel .floor .center-rectangle3[data-v-4e1bcf7c]{width:%?210?%;background:#bb0100}.funnel .floor .right-triangle3[data-v-4e1bcf7c]{border-color:#bb0100 transparent transparent #bb0100}.explanation[data-v-4e1bcf7c]{margin-bottom:%?50?%}.explanation .explanation-item[data-v-4e1bcf7c]{font-size:%?24?%;color:#000;margin-right:%?20?%}.explanation .explanation-item .rectangle[data-v-4e1bcf7c]{width:%?50?%;height:%?30?%;background:#ff6665;margin-right:%?10?%}.explanation .explanation-item .cricle[data-v-4e1bcf7c]{width:%?26?%;height:%?26?%;border-radius:50%;background:#bc0100;margin-right:%?26?%}.line-title[data-v-4e1bcf7c]{padding:%?46?% 0 0;font-size:%?28?%;color:#000;text-align:center;letter-spacing:1px}.qiun-charts[data-v-4e1bcf7c]{width:%?690?%;height:%?500?%;background-color:#fff}.charts[data-v-4e1bcf7c]{width:%?690?%;height:%?500?%;background-color:#fff}.curve[data-v-4e1bcf7c]{padding:%?30?% 0;margin:0 %?30?%;box-shadow:%?0?% %?0?% %?6?% %?0?% #dfdfdf}.add-client[data-v-4e1bcf7c]{padding-bottom:%?50?%}.interest[data-v-4e1bcf7c]{font-size:%?28?%;color:#000;text-align:center;padding:%?48?% %?30?%}.interest .interest-title[data-v-4e1bcf7c]{margin-bottom:%?50?%}.area-box[data-v-4e1bcf7c]{box-shadow:%?0?% %?0?% %?6?% %?0?% #dfdfdf;padding:%?40?% %?50?% %?80?% %?40?%}.area-box .qiun-charts[data-v-4e1bcf7c]{width:%?340?%;height:%?340?%;background:"#ffffff";background-size:100% 100%}.area-box .chartsa[data-v-4e1bcf7c]{width:%?340?%;height:%?340?%}.area-box .legend[data-v-4e1bcf7c]{margin-left:%?80?%;padding:%?40?% 0 0}.area-box .legend .legend-item[data-v-4e1bcf7c]{margin-bottom:%?30?%}.area-box .legend .left[data-v-4e1bcf7c]{width:%?40?%;height:%?40?%;border-radius:50%;border:1px solid #f2f2f2;margin-right:%?16?%}.area-box .legend .left .solidCricle[data-v-4e1bcf7c]{width:%?20?%;height:%?20?%;background:#bc0100;border-radius:50%}.area-box .legend .right[data-v-4e1bcf7c]{color:#bc0100;font-size:%?30?%;-webkit-box-flex:1;-webkit-flex:1;flex:1;text-align:left;font-weight:700;-webkit-transform:scale(.7);transform:scale(.7);position:relative;top:%?-14?%;left:%?-20?%}.area-box .legend .right .text[data-v-4e1bcf7c]{font-size:%?24?%;white-space:nowrap;color:#666;font-weight:500}.rank-item[data-v-4e1bcf7c]{border-bottom:1px solid #f2f2f2;padding:%?30?%;color:#000;font-size:%?28?%}.rank-item .rank-avatar[data-v-4e1bcf7c]{width:%?80?%;height:%?80?%;border-radius:50%;margin-left:%?40?%;margin-right:%?20?%}.AI .one[data-v-4e1bcf7c]{padding:%?40?% %?30?%;border-bottom:1px solid #f2f2f2}.AI .one .medals[data-v-4e1bcf7c]{width:%?66?%;height:%?76?%;display:block;margin-right:%?10?%}.AI .one .userInfo .userInfo-avatar[data-v-4e1bcf7c]{width:%?84?%;height:%?84?%;margin-right:%?16?%;border-radius:50%}.AI .one .userInfo .userName[data-v-4e1bcf7c]{font-size:%?26?%;color:#000;margin-bottom:%?4?%}.AI .one .userInfo .userName2[data-v-4e1bcf7c]{width:%?164?%}.AI .one .userInfo .user-position[data-v-4e1bcf7c]{font-size:%?24?%;color:#999}.AI .gold-radar[data-v-4e1bcf7c]{width:%?750?%;height:%?480?%;background-color:#fff;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.AI .gold-radar2[data-v-4e1bcf7c]{width:%?375?%;height:%?380?%}.AI .charts[data-v-4e1bcf7c]{width:%?600?%;height:%?470?%;background-color:#fff}.AI .charts2[data-v-4e1bcf7c]{width:%?400?%;height:%?380?%}.AI .bottom[data-v-4e1bcf7c]{padding-top:%?60?%;-webkit-flex-wrap:wrap;flex-wrap:wrap}.AI .bottom .item[data-v-4e1bcf7c]{width:33.33%;text-align:center}.AI .bottom .item .name[data-v-4e1bcf7c]{font-size:%?26?%;color:#000;margin-bottom:%?4?%}.AI .bottom .item .position[data-v-4e1bcf7c]{font-size:%?24?%;color:#999}.AI .bottom .item .gold-radar2[data-v-4e1bcf7c]{width:100%;height:%?300?%}.AI .bottom .item .charts2[data-v-4e1bcf7c]{width:%?242?%;height:%?290?%}.hot-list .title-btn[data-v-4e1bcf7c]{padding:%?20?% %?30?%;color:#000;font-size:%?24?%;letter-spacing:2px;border-bottom:1px solid #f2f2f2}.hot-list .title-btn .triangle[data-v-4e1bcf7c]{width:0;height:0;border-width:%?20?% %?10?% 0 %?10?%;border-color:#bc0100 transparent transparent transparent;border-style:solid;margin-right:%?10?%}.hot-list .goods-list .goods-item[data-v-4e1bcf7c]{padding:%?50?% %?24?%;border-top:1px solid #f2f2f2}.hot-list .goods-list .goods-item .goods-img[data-v-4e1bcf7c]{width:%?154?%;height:%?154?%;border-radius:%?10?%;margin:0 %?20?%}.hot-list .goods-list .goods-item .goods-info[data-v-4e1bcf7c]{color:#999;font-size:%?24?%;-webkit-box-flex:1;-webkit-flex:1;flex:1;height:%?154?%}.hot-list .goods-list .goods-item .goods-name[data-v-4e1bcf7c]{color:#000;font-size:%?30?%;margin-bottom:%?6?%}.department[data-v-4e1bcf7c]{border-radius:%?10?%;overflow:hidden;background:#fff}.department .title[data-v-4e1bcf7c]{background:#bc0100;padding:%?28?% 0;text-align:center;color:#fff;font-size:%?28?%}.department .dep-scroll[data-v-4e1bcf7c]{height:%?700?%}.department .dep-item[data-v-4e1bcf7c]{padding:%?30?% %?26?%;border-bottom:1px solid #e6e6e6;font-size:%?26?%;color:#000}.department[data-v-4e1bcf7c]{border-radius:%?10?%;overflow:hidden;background:#fff}.department .title[data-v-4e1bcf7c]{background:#bc0100;padding:%?28?% 0;text-align:center;color:#fff;font-size:%?28?%}.department .dep-item[data-v-4e1bcf7c]{padding:%?30?% %?26?%;border-bottom:1px solid #e6e6e6;font-size:%?26?%;color:#000}body.?%PAGE?%[data-v-4e1bcf7c]{background:#fff}',""]),t.exports=e},"8a29":function(t,e,a){"use strict";var i=a("0721"),s=a.n(i);s.a},baea:function(t,e,a){"use strict";a.r(e);var i=a("e2a9"),s=a("02bf");for(var n in s)"default"!==n&&function(t){a.d(e,t,(function(){return s[t]}))}(n);a("8a29");var r,o=a("f0c5"),l=Object(o["a"])(s["default"],i["b"],i["c"],!1,null,"4e1bcf7c",null,!1,i["a"],r);e["default"]=l.exports},e2a9:function(t,e,a){"use strict";a.d(e,"b",(function(){return s})),a.d(e,"c",(function(){return n})),a.d(e,"a",(function(){return i}));var i={comModal:a("c10d").default},s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{staticClass:"analysis-root"},[a("v-uni-view",{staticClass:"select flex flex-y-center flex-x-center",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.showPop.apply(void 0,arguments)}}},[t._v(t._s(t.department_text)),a("v-uni-view",{staticClass:"triangle"})],1),a("com-modal",{attrs:{show:t.is_modal,padding:"0rpx 0rpx",width:"70%",custom:!0},on:{cancel:function(e){arguments[0]=e=t.$handleEvent(e),t.hidePop.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"department"},[a("v-uni-view",{staticClass:"title"},[t._v("部门选择")]),a("v-uni-scroll-view",{staticClass:"dep-scroll",attrs:{"scroll-y":"true"}},t._l(t.general_data.department_list,(function(e,i){return a("v-uni-view",{key:e.name,staticClass:"dep-item",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.depSelect(i,e.id)}}},[t._v(t._s(e.name))])})),1)],1)],1),a("v-uni-view",{staticClass:"tabs-box flex",class:{bor:0==t.tabs_index}},t._l(t.tabs_list,(function(e,i){return a("v-uni-view",{staticClass:"tabs-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.tabSwitch(i,"time1")}}},[a("v-uni-text",{class:{"tabs-active":t.tabs_index==i}},[t._v(t._s(e))])],1)})),1),a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:0==t.tabs_index,expression:"tabs_index == 0"}]},[a("v-uni-view",{staticClass:"timeTab-box flex flex-x-between flex-y-center"},t._l(t.time_list,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.time_index},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchTime(i,"time1")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"content flex flex-x-between"},[t.general_data?[a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("新增客户")]),a("v-uni-view",[t._v(t._s(t.general_data.new_client_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("浏览量")]),a("v-uni-view",[t._v(t._s(t.general_data.browse_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("新增线索")]),a("v-uni-view",[t._v(t._s(t.general_data.new_clue_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("订单数")]),a("v-uni-view",[t._v(t._s(t.general_data.team_order_count))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("订单金额")]),a("v-uni-view",[t._v(t._s(t.general_data.team_order_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("下单人数")]),a("v-uni-view",[t._v(t._s(t.general_data.order_user_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("意向客户")]),a("v-uni-view",[t._v(t._s(t.general_data.intent_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("比较客户")]),a("v-uni-view",[t._v(t._s(t.general_data.compare_total))])],1),a("v-uni-view",{staticClass:"content-item flex flex-col flex-x-center flex-y-center"},[a("v-uni-view",{staticClass:"title"},[t._v("待成交客户")]),a("v-uni-view",[t._v(t._s(t.general_data.clinch_total))])],1)]:t._e()],2),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"funnel-title"},[t._v("成交转化率")]),a("v-uni-view",{staticClass:"funnel flex flex-col flex-y-center"},[t.general_data?[a("v-uni-view",{staticClass:"floor flex flex-y-center"},[a("v-uni-view",{staticClass:"left-triangle"}),a("v-uni-view",{staticClass:"center-rectangle"},[t._v("总客户数:"+t._s(t.general_data.stat.client_total))]),a("v-uni-view",{staticClass:"right-triangle"})],1),a("v-uni-view",{staticClass:"floor flex flex-y-center"},[a("v-uni-view",{staticClass:"left-triangle left-triangle2"}),a("v-uni-view",{staticClass:"center-rectangle center-rectangle2"},[t._v("跟进中:"+t._s(t.general_data.stat.follow_total))]),a("v-uni-view",{staticClass:"right-triangle right-triangle2"})],1),a("v-uni-view",{staticClass:"floor flex flex-y-center"},[a("v-uni-view",{staticClass:"left-triangle left-triangle3"}),a("v-uni-view",{staticClass:"center-rectangle center-rectangle3"},[t._v("成交数:"+t._s(t.general_data.stat.deal_total))]),a("v-uni-view",{staticClass:"right-triangle right-triangle3"})],1)]:t._e()],2),a("v-uni-view",{staticClass:"explanation flex flex-x-center"},[t.general_data?[a("v-uni-view",{staticClass:"explanation-item flex flex-y-center"},[a("v-uni-view",{staticClass:"rectangle"}),a("v-uni-view",[t._v("总客户数"+t._s(t.general_data.stat.client_total))])],1),a("v-uni-view",{staticClass:"explanation-item flex flex-y-center"},[a("v-uni-view",{staticClass:"rectangle",staticStyle:{background:"#F12726"}}),a("v-uni-view",[t._v("跟进中"+t._s(t.general_data.stat.follow_total))])],1),a("v-uni-view",{staticClass:"explanation-item flex flex-y-center"},[a("v-uni-view",{staticClass:"rectangle",staticStyle:{background:"#BC0100"}}),a("v-uni-view",[t._v("成交数"+t._s(t.general_data.stat.deal_total))])],1)]:t._e()],2),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"line",staticStyle:{"padding-bottom":"30rpx"}},[a("v-uni-view",{staticClass:"line-title"},[t._v("商城订单量&交易金额")]),a("v-uni-view",{staticClass:"timeTab-box flex flex-x-center flex-y-center",staticStyle:{"border-top":"0","margin-bottom":"6rpx"}},t._l(t.time_list2,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.time_index2},staticStyle:{"margin-right":"40rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchTime(i,"time2")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticClass:"curve"},[a("v-uni-canvas",{staticClass:"charts",attrs:{"canvas-id":"canvasLineA",id:"canvasLineA"},on:{touchstart:function(e){arguments[0]=e=t.$handleEvent(e),t.touchLineA.apply(void 0,arguments)}}})],1)],1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"add-client"},[a("v-uni-view",{staticClass:"line-title"},[t._v("新增客户")]),a("v-uni-view",{staticClass:"timeTab-box flex flex-x-center flex-y-center",staticStyle:{"border-top":"0","margin-bottom":"6rpx"}},t._l(t.time_list2,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.time_index3},staticStyle:{"margin-right":"40rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchTime(i,"time3")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticClass:"curve"},[a("v-uni-canvas",{staticClass:"charts",attrs:{"canvas-id":"canvasLineOA",id:"canvasLineOA"}})],1)],1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"follow-client add-client"},[a("v-uni-view",{staticClass:"line-title"},[t._v("跟进客户")]),a("v-uni-view",{staticClass:"timeTab-box flex flex-x-center flex-y-center",staticStyle:{"border-top":"0","margin-bottom":"6rpx"}},t._l(t.time_list2,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.time_index4},staticStyle:{"margin-right":"40rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchTime(i,"time4")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticClass:"curve"},[a("v-uni-canvas",{staticClass:"charts",attrs:{"canvas-id":"canvasLineOA2",id:"canvasLineOA2"}})],1)],1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"interest"},[a("v-uni-view",{staticClass:"interest-title"},[t._v("客户兴趣占比")]),a("v-uni-view",{staticClass:"flex area-box"},[a("v-uni-view",{staticClass:"qiun-charts"},[a("v-uni-canvas",{staticClass:"chartsa",attrs:{"canvas-id":"canvasArea",id:"canvasArea"}})],1),a("v-uni-view",{staticClass:"legend"},t._l(t.CustomerInterest_data,(function(e,i){return a("v-uni-view",{staticClass:"legend-item flex"},[a("v-uni-view",{staticClass:"left flex flex-y-center flex-x-center"},[a("v-uni-view",{staticClass:"solidCricle",style:{background:1==i?"#ea2424":2==i?"#FF6665":""}})],1),a("v-uni-view",{staticClass:"right"},[a("v-uni-view",{style:{color:1==i?"#ea2424":2==i?"#FF6665":""}},[t._v(t._s(t.calculation(e.data)))]),a("v-uni-view",{staticClass:"text"},[t._v(t._s(e.name))])],1)],1)})),1)],1)],1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"add-client"},[a("v-uni-view",{staticClass:"line-title"},[t._v("客户活跃度")]),a("v-uni-view",{staticClass:"timeTab-box flex flex-x-center flex-y-center",staticStyle:{"border-top":"0","margin-bottom":"6rpx"}},t._l(t.time_list2,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.time_index5},staticStyle:{"margin-right":"40rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchTime(i,"time5")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticClass:"curve"},[a("v-uni-canvas",{staticClass:"charts",attrs:{"canvas-id":"canvasLineOA3",id:"canvasLineOA3"}})],1),a("v-uni-input",{attrs:{type:"text",value:""}})],1)],1),a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:1==t.tabs_index,expression:"tabs_index == 1"}]},[a("v-uni-view",{staticClass:"timeTab-box flex flex-x-between flex-y-center"},t._l(t.sale_list,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.sale_index},staticStyle:{width:"158rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchSale(i,"time1")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"rank-list"},t._l(t.sale_data,(function(e,i){return a("v-uni-view",{key:i,staticClass:"rank-item flex flex-x-between flex-y-center"},[a("v-uni-view",{staticClass:"flex flex-y-center"},[a("v-uni-view",{staticClass:"sequence"},[t._v(t._s(i+1))]),a("v-uni-view",{staticClass:"flex flex-y-center"},[a("v-uni-image",{staticClass:"rank-avatar",attrs:{src:e.avatar_url||t.img_url+"images/business/business-default.jpg",mode:""}}),a("v-uni-view",[t._v(t._s(e.nickname))])],1)],1),a("v-uni-view",{staticStyle:{color:"#BC0100","font-size":"36rpx"}},[t._v(t._s(e.total))])],1)})),1)],1),a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:2==t.tabs_index,expression:"tabs_index == 2"}]},[a("v-uni-view",{staticClass:"AI"},[a("v-uni-view",{staticClass:"up"},[0!=t.Ai_data.length?[a("v-uni-view",{staticClass:"one flex flex-y-center"},[a("v-uni-image",{staticClass:"medals",attrs:{src:t.img_url+"gold.png",mode:""}}),a("v-uni-view",{staticClass:"userInfo flex flex-y-center"},[a("v-uni-image",{staticClass:"userInfo-avatar",attrs:{src:t.Ai_data[0].avatar_url||t.img_url+"images/business/business-default.jpg",mode:""}}),a("v-uni-view",[a("v-uni-view",{staticClass:"userName"},[t._v(t._s(t.Ai_data[0].nickname))]),a("v-uni-view",{staticClass:"user-position"},[t._v(t._s(t.Ai_data[0].position_name))])],1)],1)],1)]:t._e(),a("v-uni-view",{staticClass:"gold-radar"},[a("v-uni-canvas",{staticClass:"charts",attrs:{"canvas-id":"canvasRadar",id:"canvasRadar"}})],1)],2),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"center"},[0!=t.Ai_data.length?[a("v-uni-view",{staticClass:"one flex flex-y-center"},[a("v-uni-view",{staticClass:"flex flex-y-center"},[a("v-uni-image",{staticClass:"medals",attrs:{src:t.img_url+"silver.png",mode:""}}),a("v-uni-view",{staticClass:"userInfo flex flex-y-center"},[a("v-uni-image",{staticClass:"userInfo-avatar",attrs:{src:t.Ai_data[1].avatar_url||t.img_url+"images/business/business-default.jpg",mode:""}}),a("v-uni-view",[a("v-uni-view",{staticClass:"userName userName2 over1"},[t._v(t._s(t.Ai_data[1].nickname))]),a("v-uni-view",{staticClass:"user-position"},[t._v(t._s(t.Ai_data[1].position_name))])],1)],1)],1),a("v-uni-view",{staticClass:"flex flex-y-center"},[a("v-uni-image",{staticClass:"medals",attrs:{src:t.img_url+"silver.png",mode:""}}),a("v-uni-view",{staticClass:"userInfo flex flex-y-center"},[a("v-uni-image",{staticClass:"userInfo-avatar",attrs:{src:t.Ai_data[2].avatar_url||t.img_url+"images/business/business-default.jpg",mode:""}}),a("v-uni-view",[a("v-uni-view",{staticClass:"userName userName2 over1"},[t._v(t._s(t.Ai_data[2].nickname))]),a("v-uni-view",{staticClass:"user-position"},[t._v(t._s(t.Ai_data[2].position_name))])],1)],1)],1)],1)]:t._e(),a("v-uni-view",{staticClass:"flex"},[a("v-uni-view",{staticClass:"gold-radar gold-radar2"},[a("v-uni-canvas",{staticClass:"charts charts2",attrs:{"canvas-id":"canvasRadarTwo",id:"canvasRadarTwo"}})],1),a("v-uni-view",{staticClass:"gold-radar gold-radar2"},[a("v-uni-canvas",{staticClass:"charts charts2",attrs:{"canvas-id":"canvasRadarThree",id:"canvasRadarThree"}})],1)],1)],2),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"bottom flex"},t._l(t.Ai_data_item,(function(e,i){return a("v-uni-view",{key:i,staticClass:"item"},[a("v-uni-view",{staticClass:"name over1"},[t._v(t._s(e.nickname))]),a("v-uni-view",{staticClass:"position"},[t._v(t._s(e.position_name))]),a("v-uni-view",{staticClass:"gold-radar gold-radar2"},[a("v-uni-canvas",{staticClass:"charts charts2",attrs:{"canvas-id":"canvasRadar"+i,id:"canvasRadar"+i}})],1)],1)})),1)],1)],1),a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:3==t.tabs_index,expression:"tabs_index == 3"}]},[a("v-uni-view",{staticClass:"timeTab-box flex flex-x-between flex-y-center"},t._l(t.hot_list,(function(e,i){return a("v-uni-view",{key:i,staticClass:"timeTab-item",class:{active:i==t.hot_index},staticStyle:{width:"158rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchHot(i,"time1")}}},[t._v(t._s(e))])})),1),a("v-uni-view",{staticStyle:{height:"16rpx",background:"#F7F7F7"}}),a("v-uni-view",{staticClass:"hot-list"},[a("v-uni-view",{staticClass:"title-btn flex flex-y-center",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.showDayPop.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"triangle"}),t._v(t._s(t.day_list[t.day_index]))],1),a("v-uni-view",{staticClass:"goods-list"},t._l(t.hot_data,(function(e,i){return a("v-uni-view",{staticClass:"goods-item flex flex-y-center"},[a("v-uni-view",[t._v(t._s(i+1))]),a("v-uni-image",{staticClass:"goods-img",attrs:{src:e.image,mode:""}}),a("v-uni-view",{staticClass:"goods-info flex flex-col flex-x-between"},[a("v-uni-view",[a("v-uni-view",{staticClass:"goods-name over2"},[t._v(t._s(e.name))]),1==t.hot_index?a("v-uni-view",{staticClass:"spec"},[t._v(t._s(e.position_name))]):t._e()],1),a("v-uni-view",{staticClass:"flex flex-x-between"},[a("v-uni-view",[t._v("浏览量"+t._s(e.total))]),a("v-uni-view",[t._v("销量"+t._s(e.sales))])],1)],1)],1)})),1)],1)],1),a("com-modal",{attrs:{show:t.is_modal_day,padding:"0rpx 0rpx",width:"70%",custom:!0},on:{cancel:function(e){arguments[0]=e=t.$handleEvent(e),t.hidePop.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"department"},[a("v-uni-view",{staticClass:"title"},[t._v("天数选择")]),a("v-uni-scroll-view",{attrs:{"scroll-y":"true"}},t._l(t.day_list,(function(e,i){return a("v-uni-view",{key:e,staticClass:"dep-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.daySelect(i)}}},[t._v(t._s(e))])})),1)],1)],1)],1)},n=[]}}]);