(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["mch-picture-personal-editInfo"],{"03d3":function(t,e,i){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={data:function(){return{serverUrl:"http://upload.cn"}},methods:{result:function(e){t("log",e," at mch/picture/personal/editInfo.vue:33"),this.imageData=e.imgArr},remove:function(e){t("log",e," at mch/picture/personal/editInfo.vue:38");e.index},submit:function(){this.$http.toast("提交")}}};e.default=i}).call(this,i("0de9")["log"])},"04bd":function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var a={comUpload:i("abff").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",[i("v-uni-view",{staticClass:"line"}),i("v-uni-view",{staticClass:"main-box"},[i("v-uni-view",{staticClass:"tui-box-upload"},[i("v-uni-view",{staticClass:"content-tips"},[t._v("修改背景图:")]),i("com-upload",{attrs:{serverUrl:t.serverUrl,width:190,height:190},on:{complete:function(e){arguments[0]=e=t.$handleEvent(e),t.result.apply(void 0,arguments)},remove:function(e){arguments[0]=e=t.$handleEvent(e),t.remove.apply(void 0,arguments)}}})],1),i("v-uni-view",{staticClass:"signature"},[i("v-uni-view",{staticClass:"signature-title"},[t._v("个性签名:")]),i("v-uni-input",{staticClass:"signature-inp",attrs:{type:"text",value:"",placeholder:"填写属于你自己的个性签名吧!","placeholder-style":"color:#CCCCCC;font-size:10pt"}})],1),i("v-uni-view",{staticClass:"subBtn",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.submit.apply(void 0,arguments)}}},[t._v("确认编辑")])],1)],1)},o=[]},"06c5":function(t,e,i){"use strict";i("a630"),i("fb6a"),i("d3b7"),i("25f0"),i("3ca3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=o;var a=n(i("6b75"));function n(t){return t&&t.__esModule?t:{default:t}}function o(t,e){if(t){if("string"===typeof t)return(0,a.default)(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);return"Object"===i&&t.constructor&&(i=t.constructor.name),"Map"===i||"Set"===i?Array.from(t):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?(0,a.default)(t,e):void 0}}},"09be":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,"uni-page-body[data-v-a5e898dc]{background:#fff}.line[data-v-a5e898dc]{border-bottom:%?2?% solid #f7f7f7}.main-box[data-v-a5e898dc]{padding:0 %?30?%}.tui-box-upload[data-v-a5e898dc]{box-sizing:border-box;margin-top:%?18?%;padding-bottom:%?38?%;border-bottom:%?2?% solid #f7f7f7}.content-tips[data-v-a5e898dc]{font-weight:600;font-size:10pt;color:#000;margin:%?40?% 0 %?30?%}.signature[data-v-a5e898dc]{padding:%?40?% 0 0}.signature-title[data-v-a5e898dc]{font-weight:600;font-size:10pt;color:#000;letter-spacing:%?2?%;margin-bottom:%?20?%}.signature-inp[data-v-a5e898dc]{border-bottom:%?2?% solid #f7f7f7;padding-bottom:%?10?%}.subBtn[data-v-a5e898dc]{background:#bc0100;border-radius:%?50?%;padding:%?26?% 0;color:#fff;font-size:10pt;text-align:center;letter-spacing:%?4?%;margin-top:%?200?%}body.?%PAGE?%[data-v-a5e898dc]{background:#fff}",""]),t.exports=e},"0a84":function(t,e,i){"use strict";i.r(e);var a=i("a786"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},2243:function(t,e,i){"use strict";i.r(e);var a=i("04bd"),n=i("7496");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("66d0");var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"a5e898dc",null,!1,a["a"],r);e["default"]=d.exports},2909:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=d;var a=s(i("6005")),n=s(i("db90")),o=s(i("06c5")),r=s(i("3427"));function s(t){return t&&t.__esModule?t:{default:t}}function d(t){return(0,a.default)(t)||(0,n.default)(t)||(0,o.default)(t)||(0,r.default)()}},3244:function(t,e,i){"use strict";var a=i("60e5"),n=i.n(a);n.a},3427:function(t,e,i){"use strict";function a(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}Object.defineProperty(e,"__esModule",{value:!0}),e.default=a},4318:function(t,e,i){var a=i("09be");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("2f7f144f",a,!0,{sourceMap:!1,shadowMode:!1})},6005:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=o;var a=n(i("6b75"));function n(t){return t&&t.__esModule?t:{default:t}}function o(t){if(Array.isArray(t))return(0,a.default)(t)}},"60e5":function(t,e,i){var a=i("8d1b");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("3e285c98",a,!0,{sourceMap:!1,shadowMode:!1})},"66d0":function(t,e,i){"use strict";var a=i("4318"),n=i.n(a);n.a},"6b75":function(t,e,i){"use strict";function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,a=new Array(e);i<e;i++)a[i]=t[i];return a}Object.defineProperty(e,"__esModule",{value:!0}),e.default=a},7496:function(t,e,i){"use strict";i.r(e);var a=i("03d3"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"7c6b":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"jx-container",style:{opacity:t.use_type?0:1},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.chooseImage.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"jx-upload-box"},[t.use_type?t._e():t._l(t.imageList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"jx-image-item",style:t.diyStyle?t.diyStyle+"margin: "+(1!==t.limit||0)+";":"width: "+t.width+"rpx;height:"+t.height+"rpx;margin: "+(1!==t.limit||0)},[i("v-uni-image",{staticClass:"jx-item-img",style:t.diyStyle?""+t.diyStyle:"width: "+t.width+"rpx;height:"+t.height+"rpx;",attrs:{src:e,mode:"aspectFill"},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.previewImage(a)}}}),t.forbidDel?t._e():i("v-uni-view",{staticClass:"jx-img-del",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.delImage(a)}}}),1!=t.statusArr[a]?i("v-uni-view",{staticClass:"jx-upload-mask"},[2==t.statusArr[a]?i("v-uni-view",{staticClass:"jx-upload-loading"}):t._e(),i("v-uni-text",{staticClass:"jx-tips"},[t._v(t._s(2==t.statusArr[a]?"上传中...":"上传失败"))]),3==t.statusArr[a]?i("v-uni-view",{staticClass:"jx-mask-btn",attrs:{"hover-class":"jx-hover","hover-stay-time":150},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.reUpLoad(a)}}},[t._v("重新上传")]):t._e()],1):t._e()],1)})),t.isShowAdd?i("v-uni-view",{staticClass:"jx-upload-add",staticStyle:{"z-index":"3"},style:t.diyStyle?""+t.diyStyle:"width: "+t.width+"rpx;height:"+t.height+"rpx;",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.chooseImage.apply(void 0,arguments)}}},[t.is_style?i("v-uni-view",{staticClass:"jx-upload-icon jx-icon-plus"}):i("v-uni-view",{staticClass:"upload-img flex-col flex-x-center flex-y-center",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.addImg.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"iconfont icon-xiangji"}),i("v-uni-view",{staticStyle:{"font-size":"30rpx"}},[t._v("添加图片")])],1)],1):t._e()],2)],1)},o=[]},"8d1b":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 商城主题色 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */@font-face{font-family:jxUpload;src:url(data:application/font-woff;charset=utf-8;base64,d09GRgABAAAAAATcAA0AAAAAByQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABGRlRNAAAEwAAAABoAAAAciR52BUdERUYAAASgAAAAHgAAAB4AKQALT1MvMgAAAaAAAABCAAAAVjxvR/tjbWFwAAAB+AAAAEUAAAFK5ibpuGdhc3AAAASYAAAACAAAAAj//wADZ2x5ZgAAAkwAAADXAAABAAmNjcZoZWFkAAABMAAAAC8AAAA2FpiS+WhoZWEAAAFgAAAAHQAAACQH3QOFaG10eAAAAeQAAAARAAAAEgwAACBsb2NhAAACQAAAAAwAAAAMAEoAgG1heHAAAAGAAAAAHwAAACABEgA2bmFtZQAAAyQAAAFJAAACiCnmEVVwb3N0AAAEcAAAACgAAAA6OMUs4HjaY2BkYGAAYo3boY/i+W2+MnCzMIDAzb3qdQj6fwPzf+YGIJeDgQkkCgA/KAtvAHjaY2BkYGBu+N/AEMPCAALM/xkYGVABCwBZ4wNrAAAAeNpjYGRgYGBl0GJgZgABJiDmAkIGhv9gPgMADTABSQB42mNgZGFgnMDAysDA1Ml0hoGBoR9CM75mMGLkAIoysDIzYAUBaa4pDA7PGJ9xMjf8b2CIYW5gaAAKM4LkANt9C+UAAHjaY2GAABYIVmBgAAAA+gAtAAAAeNpjYGBgZoBgGQZGBhBwAfIYwXwWBg0gzQakGRmYnjE+4/z/n4EBQksxSf6GqgcCRjYGOIeRCUgwMaACRoZhDwCiLwmoAAAAAAAAAAAAAAAASgCAeNpdjkFKw0AARf/vkIR0BkPayWRKQZtYY90ohJju2kOIbtz0KD1HVm50UfEmWXoAr9ADOHFARHHzeY//Fx8Ci+FJfIgdJFa4AhgiMshbrCuIsLxhFJZVs+Vl1bT1GddtbXTC3OhohN4dg4BJ3zMJAnccyfm468ZzHXddrH9ZKbHzdf9n/vkY/xv9sPQXgGEvBrHHwst5kTbXLE+YpYVPkxepPmW94W16UbdNJd6f3SAzo5W7m1jaKd+8ZZIvk5nlKw9SK6Wle7BLS3f/bTzQLmfAF2T1NsQAeNp9kD1OAzEQhZ/zByQSQiCoXVEA2vyUKRMp9Ailo0g23pBo1155nUg5AS0VB6DlGByAGyDRcgpelkmTImvt6PObmeexAZzjGwr/3yXuhBWO8ShcwREy4Sr1F+Ea+V24jhY+hRvUf4SbuFUD4RYu1BsdVO2Eu5vSbcsKZxgIV3CKJ+Eq9ZVwjfwqXMcVPoQb1L+EmxjjV7iFa2WpDOFhMEFgnEFjig3jAjEcLJIyBtahOfRmEsxMTzd6ETubOBso71dilwMeaDnngCntPbdmvkon/mDLgdSYbh4FS7YpjS4idCgbXyyc1d2oc7D9nu22tNi/a4E1x+xRDWzU/D3bM9JIbAyvkJI18jK3pBJTj2hrrPG7ZynW814IiU68y/SIx5o0dTr3bmniwOLn8owcfbS5kj33qBw+Y1kIeb/dTsQgil2GP5PYcRkAAAB42mNgYoAALjDJyIAOWMGiTIxMjMxsKak5qSWpbFmZiRmJ+QAmgAUIAAAAAf//AAIAAQAAAAwAAAAWAAAAAgABAAMABAABAAQAAAACAAAAAHjaY2BgYGQAgqtL1DlA9M296nUwGgA+8QYgAAA=) format("woff");font-weight:400;font-style:normal}.jx-upload-icon[data-v-5b1898e4]{font-family:jxUpload!important;font-style:normal;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;padding:%?10?%}.jx-icon-delete[data-v-5b1898e4]:before{content:"\\e601"}.jx-icon-plus[data-v-5b1898e4]:before{content:"\\e609"}.jx-upload-box[data-v-5b1898e4]{width:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap}.jx-upload-add[data-v-5b1898e4]{width:%?220?%;height:%?220?%;font-size:%?50?%;font-weight:100;color:#b3b3b3;background-color:#fff;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;padding:0;border:%?2?% solid #e6e6e6;border-radius:%?15?%}.jx-image-item[data-v-5b1898e4]{width:%?220?%;height:%?220?%;position:relative;margin-right:%?20?%;margin-bottom:%?16?%}.jx-image-item[data-v-5b1898e4]:nth-of-type(4n){margin-right:0}.jx-item-img[data-v-5b1898e4]{width:%?220?%;height:%?220?%;display:block}.jx-img-del[data-v-5b1898e4]{width:%?36?%;height:%?36?%;position:absolute;right:%?-12?%;top:%?-12?%;background:#eb0909;border-radius:50%;color:#fff;font-size:%?34?%;z-index:999}.jx-img-del[data-v-5b1898e4]::before{content:"";width:%?16?%;height:1px;position:absolute;left:%?10?%;top:%?18?%;background:#fff}.jx-upload-mask[data-v-5b1898e4]{width:100%;height:100%;position:absolute;left:0;top:0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-justify-content:space-around;justify-content:space-around;padding:%?40?% 0;box-sizing:border-box;background:rgba(0,0,0,.6)}.jx-upload-loading[data-v-5b1898e4]{width:%?28?%;height:%?28?%;border-radius:50%;border:2px solid;border-color:#b2b2b2 #b2b2b2 #b2b2b2 #fff;-webkit-animation:jx-rotate-data-v-5b1898e4 .7s linear infinite;animation:jx-rotate-data-v-5b1898e4 .7s linear infinite}@-webkit-keyframes jx-rotate-data-v-5b1898e4{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes jx-rotate-data-v-5b1898e4{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}.jx-tips[data-v-5b1898e4]{font-size:%?26?%;color:#fff}.jx-mask-btn[data-v-5b1898e4]{padding:%?2?% %?10?%;border-radius:%?40?%;text-align:center;font-size:%?24?%;color:#fff;border:%?1?% solid #fff;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.jx-hover[data-v-5b1898e4]{opacity:.5}.upload-img[data-v-5b1898e4]{width:%?180?%;height:%?180?%;color:#bfbfbf;z-index:10}.upload-img .iconfont[data-v-5b1898e4]{font-size:16pt}',""]),t.exports=e},a786:function(t,e,i){"use strict";var a=i("4ea4");i("c975"),i("a434"),i("a9e3"),i("d3b7"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("b85c")),o=a(i("2909")),r={name:"jxUpload",props:{use_type:{type:Boolean,default:!1},diyKey:{type:String,default:""},value:{type:Array,default:function(){return[]}},forbidDel:{type:Boolean,default:!1},forbidAdd:{type:Boolean,default:!1},serverUrl:{type:String,default:""},limit:{type:Number,default:9},fileKeyName:{type:String,default:"file"},diyStyle:{type:String,default:""},width:{type:Number,default:220},height:{type:Number,default:220},is_style:{type:Boolean,default:!0}},watch:{value:function(){this.imageList=(0,o.default)(this.value);var t,e=(0,n.default)(this.imageList);try{for(e.s();!(t=e.n()).done;){t.value;this.statusArr.push("1")}}catch(i){e.e(i)}finally{e.f()}}},data:function(){return{imageList:[],statusArr:[]}},created:function(){this.imageList=(0,o.default)(this.value);var t,e=(0,n.default)(this.imageList);try{for(e.s();!(t=e.n()).done;){t.value;this.statusArr.push("1")}}catch(i){e.e(i)}finally{e.f()}},computed:{isShowAdd:function(){var t=!0;return this.use_type||(this.forbidAdd||this.limit&&this.imageList.length>=this.limit)&&(t=!1),t}},methods:{addImg:function(){this.$emit("addImg",{})},reUpLoad:function(t){var e=this;this.$set(this.statusArr,t,"2"),this.change(),this.uploadImage(t,this.imageList[t]).then((function(){e.change()})).catch((function(){e.change()}))},change:function(){var t=~this.statusArr.indexOf("2")?2:1;2!=t&&~this.statusArr.indexOf("3")&&(t=3),this.$emit("complete",{status:t,key:this.diyKey?this.diyKey:-1,imgArr:this.imageList})},chooseImage:function(){var t=this;!this.use_type&&this.imageList.length>=t.limit||uni.chooseImage({count:t.limit-t.imageList.length,sizeType:"compressed",success:function(e){for(var i=[],a=0;a<e.tempFilePaths.length;a++){var n=t.imageList.length;if(n>=t.limit){uni.showToast({title:"最多可上传".concat(t.limit,"张图片"),icon:"none"});break}var o=e.tempFilePaths[a];i.push(o),t.imageList.push(o),t.statusArr.push("2")}t.change();for(var r=t.imageList.length-i.length,s=function(e){var a=r+e;t.$http.uploadFile({serverUrl:t.$api.default.upload+"&width=3000&height=3000&type=1",file:i[e],fileKeyName:t.fileKeyName}).then((function(e){0==e.code?t.uploadImage(a,e).then((function(){t.change()})).catch((function(){t.change()})):(uni.showToast({title:"图片太大，请重新上传",icon:"none"}),setTimeout((function(){uni.hideToast()}),2e3))})).catch((function(){t.$set(t.statusArr,a,"3"),t.change()}))},d=0;d<i.length;d++)s(d)}})},uploadImage:function(t,e){var i=this;return new Promise((function(a,n){e.code%100===0?(e.data.url&&(i.imageList[t]=e.data.url),i.$set(i.statusArr,t,e.data.url?"1":"3")):i.$set(i.statusArr,t,"3"),a(t)}))},delImage:function(t){this.imageList.splice(t,1),this.statusArr.splice(t,1),this.$emit("remove",{index:t}),this.change()},previewImage:function(t){this.imageList.length&&uni.previewImage({current:this.imageList[t],loop:!0,urls:this.imageList})}}};e.default=r},abff:function(t,e,i){"use strict";i.r(e);var a=i("7c6b"),n=i("0a84");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("3244");var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"5b1898e4",null,!1,a["a"],r);e["default"]=d.exports},b85c:function(t,e,i){"use strict";i("a4d3"),i("e01a"),i("d28b"),i("d3b7"),i("3ca3"),i("ddb0"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=o;var a=n(i("06c5"));function n(t){return t&&t.__esModule?t:{default:t}}function o(t,e){var i;if("undefined"===typeof Symbol||null==t[Symbol.iterator]){if(Array.isArray(t)||(i=(0,a.default)(t))||e&&t&&"number"===typeof t.length){i&&(t=i);var n=0,o=function(){};return{s:o,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var r,s=!0,d=!1;return{s:function(){i=t[Symbol.iterator]()},n:function(){var t=i.next();return s=t.done,t},e:function(t){d=!0,r=t},f:function(){try{s||null==i["return"]||i["return"]()}finally{if(d)throw r}}}}},db90:function(t,e,i){"use strict";function a(t){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}i("a4d3"),i("e01a"),i("d28b"),i("a630"),i("d3b7"),i("3ca3"),i("ddb0"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=a}}]);