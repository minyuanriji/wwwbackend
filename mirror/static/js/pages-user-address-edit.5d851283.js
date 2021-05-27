(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-address-edit"],{"015e":function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return s})),e.d(i,"c",(function(){return d})),e.d(i,"a",(function(){return a}));var s=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"tui-addr-box"},[e("v-uni-form",{attrs:{"report-submit":!0}},[e("tui-list-cell",{attrs:{hover:!1,padding:"0"}},[e("v-uni-view",{staticClass:"tui-line-cell"},[e("v-uni-view",{staticClass:"tui-title"},[t._v("收货人")]),e("v-uni-input",{staticClass:"tui-input",attrs:{"placeholder-class":"tui-phcolor",name:"name",placeholder:"请输入收货人姓名",maxlength:"15",type:"text"},model:{value:t.userName,callback:function(i){t.userName=i},expression:"userName"}})],1)],1),e("tui-list-cell",{attrs:{hover:!1,padding:"0"}},[e("v-uni-view",{staticClass:"tui-line-cell"},[e("v-uni-view",{staticClass:"tui-title"},[t._v("手机号码")]),e("v-uni-input",{staticClass:"tui-input",attrs:{"placeholder-class":"tui-phcolor",name:"mobile",placeholder:"请输入收货人手机号码",maxlength:"11",type:"number"},model:{value:t.phone,callback:function(i){t.phone=i},expression:"phone"}})],1)],1),e("v-uni-picker",{attrs:{value:t.value,mode:"multiSelector",range:t.multiArray},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.picker.apply(void 0,arguments)},columnchange:function(i){arguments[0]=i=t.$handleEvent(i),t.columnPicker.apply(void 0,arguments)}}},[e("tui-list-cell",{attrs:{arrow:!0,padding:"0"}},[e("v-uni-view",{staticClass:"tui-line-cell"},[e("v-uni-view",{staticClass:"tui-title"},[e("v-uni-text",{staticClass:"tui-title-city-text"},[t._v("所在城市")])],1),e("v-uni-input",{staticClass:"tui-input",attrs:{"placeholder-class":"tui-phcolor",disabled:!0,name:"city",placeholder:"请选择城市",maxlength:"50",type:"text"},model:{value:t.text,callback:function(i){t.text=i},expression:"text"}})],1)],1)],1),1==t.is_town?[e("v-uni-picker",{attrs:{value:t.index,range:t.array},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.bindPickerChange.apply(void 0,arguments)}}},[e("tui-list-cell",{attrs:{arrow:!0,padding:"0"}},[e("v-uni-view",{staticClass:"tui-line-cell"},[e("v-uni-view",{staticClass:"tui-title"},[e("v-uni-text",{staticClass:"tui-title-city-text"},[t._v("选择乡镇")])],1),e("v-uni-input",{staticClass:"tui-input",attrs:{"placeholder-class":"tui-phcolor",disabled:!0,name:"city",placeholder:"请选择乡镇",maxlength:"50",type:"text"},model:{value:t.town_text,callback:function(i){t.town_text=i},expression:"town_text"}})],1)],1)],1)]:t._e(),e("tui-list-cell",{attrs:{hover:!1,padding:"0"}},[e("v-uni-view",{staticClass:"tui-line-cell"},[e("v-uni-view",{staticClass:"tui-title"},[t._v("收货地址")]),e("v-uni-input",{staticClass:"tui-input",attrs:{"placeholder-class":"tui-phcolor",name:"address",placeholder:"请输入详细的收货地址",maxlength:"50",type:"text"},model:{value:t.detailed,callback:function(i){t.detailed=i},expression:"detailed"}})],1)],1),e("tui-list-cell",{attrs:{hover:!1,padding:"0"}},[e("v-uni-view",{staticClass:"tui-swipe-cell"},[e("v-uni-view",[t._v("设为默认地址")]),e("v-uni-switch",{staticClass:"tui-switch-small",attrs:{checked:1==t.is_default,color:t.textColor},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.switchChange.apply(void 0,arguments)}}})],1)],1),0!=t.id?e("v-uni-view",{staticClass:"delete-address",style:{color:"#FF7104"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.deleteAddress.apply(void 0,arguments)}}},[t._v("删除收货地址")]):t._e(),e("v-uni-view",{staticClass:"tui-addr-save"},[e("v-uni-view",{staticClass:"save-btn",style:{background:"#FF7104"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.saveAddress()}}},[t._v("保存收货地址")])],1)],2)],1)},d=[]},"189f":function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,"uni-picker .uni-picker-action.uni-picker-action-confirm[data-v-edd0b6d0]{color:#bc0100!important}.tui-addr-box[data-v-edd0b6d0]{padding:%?20?% 0}.tui-line-cell[data-v-edd0b6d0]{width:100%;padding:%?24?% %?30?%;box-sizing:border-box;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.tui-title[data-v-edd0b6d0]{width:%?180?%;font-size:11pt}.tui-title-city-text[data-v-edd0b6d0]{width:%?180?%;height:%?40?%;display:block;line-height:%?46?%}.tui-input[data-v-edd0b6d0]{width:%?500?%}.tui-input-city[data-v-edd0b6d0]{-webkit-box-flex:1;-webkit-flex:1;flex:1;height:%?40?%;font-size:11pt;padding-right:%?30?%}.tui-phcolor[data-v-edd0b6d0]{color:#ccc;font-size:11pt}.tui-cell-title[data-v-edd0b6d0]{font-size:11pt}.tui-addr-label[data-v-edd0b6d0]{margin-left:%?70?%}.tui-label-item[data-v-edd0b6d0]{width:%?76?%;height:%?40?%;border:%?1?% solid #888;border-radius:%?6?%;font-size:10pt;text-align:center;line-height:%?40?%;margin-right:%?20?%;display:inline-block;-webkit-transform:scale(.9);transform:scale(.9)}.tui-label-active[data-v-edd0b6d0]{background:#e41f19;border-color:#e41f19;color:#fff}.tui-swipe-cell[data-v-edd0b6d0]{width:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;align-items:center;background:#fff;padding:%?10?% %?24?%;border-radius:%?6?%;overflow:hidden;font-size:11pt}.tui-switch-small[data-v-edd0b6d0]{-webkit-transform:scale(.8);transform:scale(.8);-webkit-transform-origin:100% center;transform-origin:100% center}\n[data-v-edd0b6d0] uni-switch .uni-switch-input{margin-right:0!important}\n.tui-addr-save[data-v-edd0b6d0]{padding:%?24?%;margin-top:%?100?%}.tui-del[data-v-edd0b6d0]{padding:%?24?%}.delete-address[data-v-edd0b6d0]{color:#bc0100;background:#fff;padding:%?24?% %?30?%;margin-top:%?40?%}.save-btn[data-v-edd0b6d0]{height:%?88?%;line-height:%?88?%;text-align:center;color:#fff}",""]),t.exports=i},"1a0a":function(t,i,e){"use strict";e.r(i);var a=e("015e"),s=e("fbfc");for(var d in s)"default"!==d&&function(t){e.d(i,t,(function(){return s[t]}))}(d);e("9585");var n,l=e("f0c5"),r=Object(l["a"])(s["default"],a["b"],a["c"],!1,null,"edd0b6d0",null,!1,a["a"],n);i["default"]=r.exports},9585:function(t,i,e){"use strict";var a=e("e2c9"),s=e.n(a);s.a},c4e4:function(t,i,e){"use strict";(function(t){var a=e("4ea4");e("4160"),e("d81d"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var s=a(e("9789")),d=a(e("faf8")),n=a(e("509b")),l={components:{tuiButton:s.default,tuiListView:d.default,tuiListCell:n.default},data:function(){return{lists:["公司","家","学校","其他"],userName:"",phone:"",detailed:"",selectList:"",multiArray:[],value:[0,0,0],text:"",id:"",provice:"",city:"",district:"",proviceId:"",cityId:"",districtId:"",is_default:0,is_shake:!1,index:0,array:[],town_data:"",town_id:0,town_text:"",is_town:0,mall_config:"",textColor:"#bc0100",form:""}},onLoad:function(t){uni.getStorageSync("mall_config")&&(this.textColor=this.globalSet("textCol")),this.mall_config=JSON.parse(uni.getStorageSync("mall_config")),this.is_town=this.mall_config.mall_setting.setting.is_town,this.id=t.id,this.form=t.form,this.getCity(),0!=this.id&&this.detailAddress()},methods:{bindPickerChange:function(t){this.index=t.target.value,this.town_text=this.town_data[this.index].name,this.town_id=this.town_data[this.index].id},switchChange:function(t){t.detail.value?this.is_default=1:this.is_default=0},getDistrict:function(){var t=this;this.$http.request({url:this.$api.district.town_list,data:{district_id:this.districtId}}).then((function(i){0==i.code&&(t.town_data=i.list,t.array=i.list.map((function(t){return t.name})))}))},getCity:function(){var t=this;this.$http.request({url:this.$api.user.addressInfo,method:"post"}).then((function(i){var e=[],a=[],s=[];for(var d in i.data)"province"!=i.data[d].level&&"city"!=i.data[d].level||t.$set(i.data[d],"children",[]),"province"==i.data[d].level&&e.push(i.data[d]),"city"==i.data[d].level&&a.push(i.data[d]),"district"==i.data[d].level&&s.push(i.data[d]);t.multiArray=[e,a,s],a.forEach((function(t,i){s.forEach((function(i,e){t.id==i.parent_id&&t.children.push(i)}))})),e.forEach((function(t,i){a.forEach((function(i,e){t.id==i.parent_id&&t.children.push(i)}))})),t.selectList=e,t.multiArray=[t.toArr(t.selectList),t.toArr(t.selectList[0].children),t.toArr(t.selectList[0].children[0].children)]}))},deleteAddress:function(){var t=this;uni.showModal({content:"确定要删除该地址吗?",confirmColor:this.textColor,success:function(i){i.confirm&&t.$http.request({url:t.$api.user.addressDelete,data:{id:t.id}}).then((function(i){0==i.code&&(t.$http.toast(i.msg),setTimeout((function(){uni.navigateBack()}),1e3))}))}})},saveAddress:function(){var t=this;this.is_shake||(this.is_shake=!0,this.$http.request({url:this.$api.user.addressSave,method:"post",data:{id:this.id,name:this.userName,province_id:this.proviceId,province:this.provice,city_id:this.cityId,city:this.city,district_id:this.districtId,district:this.district,mobile:this.phone,detail:this.detailed,is_default:this.is_default,town_id:this.town_id,town:this.town_text}}).then((function(i){0==i.code?(t.$http.toast("添加成功"),setTimeout((function(){"submit"==t.form?uni.redirectTo({url:"/pages/order/submit?addressId="+i.data.id}):t.navBack()}),1e3)):(t.$http.toast(i.msg),t.is_shake=!1)})))},detailAddress:function(){var t=this;this.$http.request({url:this.$api.user.addressDetail,showLoading:!0,data:{id:this.id}}).then((function(i){0==i.code&&(t.userName=i.data.name,t.phone=i.data.mobile,t.text=i.data.province+i.data.city+i.data.district,t.detailed=i.data.detail,t.proviceId=i.data.province_id,t.provice=i.data.province,t.cityId=i.data.city_id,t.city=i.data.city,t.districtId=i.data.district_id,t.district=i.data.district,t.is_default=i.data.is_default,t.town_id=i.data.town_id,t.town_text=i.data.town,t.getDistrict())}))},picker:function(i){var e=i.detail.value;this.selectList.length>0&&(this.provice=this.selectList[e[0]].name,this.city=this.selectList[e[0]].children[e[1]].name,this.district=this.selectList[e[0]].children[e[1]].children[e[2]].name,this.text=this.provice+" "+this.city+" "+this.district,this.proviceId=this.selectList[e[0]].id,this.cityId=this.selectList[e[0]].children[e[1]].id,this.districtId=this.selectList[e[0]].children[e[1]].children[e[2]].id),this.getDistrict(),t("log",this.districtId,"districtId"," at pages/user/address/edit.vue:309")},toArr:function(t){var i=[];for(var e in t)i.push(t[e].name);return i},columnPicker:function(t){var i=t.detail.column,e=t.detail.value;0===i?(this.multiArray=[this.multiArray[0],this.toArr(this.selectList[e].children),this.toArr(this.selectList[e].children[0].children)],this.value=[e,0,0]):1===i&&(this.multiArray=[this.multiArray[0],this.multiArray[1],this.toArr(this.selectList[this.value[0]].children[e].children)],this.value=[this.value[0],e,0])}}};i.default=l}).call(this,e("0de9")["log"])},e2c9:function(t,i,e){var a=e("189f");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var s=e("4f06").default;s("2ce3ca68",a,!0,{sourceMap:!1,shadowMode:!1})},fbfc:function(t,i,e){"use strict";e.r(i);var a=e("c4e4"),s=e.n(a);for(var d in a)"default"!==d&&function(t){e.d(i,t,(function(){return a[t]}))}(d);i["default"]=s.a}}]);