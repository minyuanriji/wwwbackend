(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-article-detail"],{"06c5":function(t,e,r){"use strict";r("a630"),r("fb6a"),r("d3b7"),r("25f0"),r("3ca3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=a;var i=n(r("6b75"));function n(t){return t&&t.__esModule?t:{default:t}}function a(t,e){if(t){if("string"===typeof t)return(0,i.default)(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?(0,i.default)(t,e):void 0}}},"0aad":function(t,e,r){function i(t){for(var e={},r=t.split(","),i=r.length;i--;)e[r[i]]=!0;return e}r("ac1f"),r("1276"),t.exports={filter:null,highlight:null,onText:null,blankChar:i(" , ,\t,\r,\n,\f"),blockTags:i("address,article,aside,body,caption,center,cite,footer,header,html,nav,section,pre"),ignoreTags:i("area,base,basefont,canvas,command,frame,input,isindex,keygen,link,map,meta,param,script,source,style,svg,textarea,title,track,use,wbr,embed,iframe"),richOnlyTags:i("a,colgroup,fieldset,legend,picture,table"),selfClosingTags:i("area,base,basefont,br,col,circle,ellipse,embed,frame,hr,img,input,isindex,keygen,line,link,meta,param,path,polygon,rect,source,track,use,wbr"),trustAttrs:i("align,alt,app-id,author,autoplay,border,cellpadding,cellspacing,class,color,colspan,controls,data-src,dir,face,height,href,id,ignore,loop,media,muted,name,path,poster,rowspan,size,span,src,start,style,type,unit-id,width,xmlns"),boolAttrs:i("autoplay,controls,ignore,loop,muted"),trustTags:i("a,abbr,ad,audio,b,blockquote,br,code,col,colgroup,dd,del,dl,dt,div,em,fieldset,h1,h2,h3,h4,h5,h6,hr,i,img,ins,label,legend,li,ol,p,q,source,span,strong,sub,sup,table,tbody,td,tfoot,th,thead,tr,title,ul,video"),userAgentStyles:{address:"font-style:italic",big:"display:inline;font-size:1.2em",blockquote:"background-color:#f6f6f6;border-left:3px solid #dbdbdb;color:#6c6c6c;padding:5px 0 5px 10px",caption:"display:table-caption;text-align:center",center:"text-align:center",cite:"font-style:italic",dd:"margin-left:40px",img:"max-width:100%",mark:"background-color:yellow",picture:"max-width:100%",pre:"font-family:monospace;white-space:pre;overflow:scroll",s:"text-decoration:line-through",small:"display:inline;font-size:0.8em",u:"text-decoration:underline"}}},"18ae":function(t,e,r){"use strict";r.r(e);var i=r("35f8"),n=r.n(i);for(var a in i)"default"!==a&&function(t){r.d(e,t,(function(){return i[t]}))}(a);e["default"]=n.a},2367:function(t,e,r){var i=r("857b");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=r("4f06").default;n("9b29b9d0",i,!0,{sourceMap:!1,shadowMode:!1})},"35cb":function(t,e,r){"use strict";var i;r.d(e,"b",(function(){return n})),r.d(e,"c",(function(){return a})),r.d(e,"a",(function(){return i}));var n=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("v-uni-view",[t.nodes.length?t._e():t._t("default"),r("v-uni-view",{style:t.showAm+(t.selectable?";user-select:text;-webkit-user-select:text":""),attrs:{id:"top",animation:t.scaleAm},on:{touchstart:function(e){arguments[0]=e=t.$handleEvent(e),t._touchstart.apply(void 0,arguments)},touchmove:function(e){arguments[0]=e=t.$handleEvent(e),t._touchmove.apply(void 0,arguments)},click:function(e){arguments[0]=e=t.$handleEvent(e),t._tap.apply(void 0,arguments)}}},[r("div",{attrs:{id:"rtf"+t.uid}})])],2)},a=[]},"35f8":function(module,exports,__webpack_require__){"use strict";var _interopRequireDefault=__webpack_require__("4ea4");__webpack_require__("99af"),__webpack_require__("caad"),__webpack_require__("c975"),__webpack_require__("acd8"),__webpack_require__("ac1f"),__webpack_require__("2532"),__webpack_require__("466d"),__webpack_require__("5319"),__webpack_require__("1276"),Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var _createForOfIteratorHelper2=_interopRequireDefault(__webpack_require__("b85c")),rpx=uni.getSystemInfoSync().screenWidth/750,cfg=__webpack_require__("0aad"),_default={name:"parser",data:function(){return{uid:this._uid,scaleAm:"",showAm:"",imgs:[],nodes:[]}},props:{html:null,autopause:{type:Boolean,default:!0},autosetTitle:{type:Boolean,default:!0},domain:String,gestureZoom:Boolean,lazyLoad:Boolean,selectable:Boolean,tagStyle:Object,showWithAnimation:Boolean,useAnchor:Boolean},watch:{html:function(t){this.setContent(t)}},mounted:function(){this.imgList=[],this.imgList.each=function(t){for(var e=0,r=this.length;e<r;e++)this.setItem(e,t(this[e],e,this))},this.imgList.setItem=function(t,e){if(void 0!=t&&e){if(0==e.indexOf("http")&&this.includes(e)){for(var r,i="",n=0;r=e[n];n++){if("/"==r&&"/"!=e[n-1]&&"/"!=e[n+1])break;i+=Math.random()>.5?r.toUpperCase():r}return i+=e.substr(n),this[t]=i}if(this[t]=e,e.includes("data:image")){var a=e.match(/data:image\/(\S+?);(\S+?),(.+)/);if(!a)return}}},this.document=document.getElementById("rtf"+this._uid),this.html&&this.setContent(this.html)},beforeDestroy:function(){this._observer&&this._observer.disconnect(),this.imgList.each((function(t){})),clearInterval(this._timer)},methods:{_Dom2Str:function(t){var e,r="",i=(0,_createForOfIteratorHelper2.default)(t);try{for(i.s();!(e=i.n()).done;){var n=e.value;if("text"==n.type)r+=n.text;else{for(var a in r+="<"+n.name,n.attrs||{})r+=" "+a+'="'+n.attrs[a]+'"';n.children&&n.children.length?r+=">"+this._Dom2Str(n.children)+"</"+n.name+">":r+=">"}}}catch(o){i.e(o)}finally{i.f()}return r},_handleHtml:function(t,e){if("string"!=typeof t&&(t=this._Dom2Str(t.nodes||t)),t.includes("rpx")&&(t=t.replace(/[0-9.]+\s*rpx/g,(function(t){return parseFloat(t)*rpx+"px"}))),!e){var r="<style scoped>@keyframes show{0%{opacity:0}100%{opacity:1}}";for(var i in cfg.userAgentStyles)r+="".concat(i,"{").concat(cfg.userAgentStyles[i],"}");for(i in this.tagStyle)r+="".concat(i,"{").concat(this.tagStyle[i],"}");r+="</style>",t=r+t}return t},setContent:function(t,e){var r=this;if(t){var i=document.createElement("div");e?this.rtf?this.rtf.appendChild(i):this.rtf=i:(this.rtf&&this.rtf.parentNode.removeChild(this.rtf),this.rtf=i),i.innerHTML=this._handleHtml(t,e);for(var n,a=this.rtf.getElementsByTagName("style"),o=0;n=a[o++];)n.innerHTML=n.innerHTML.replace(/body/g,"#rtf"+this._uid),n.setAttribute("scoped","true");!this._observer&&this.lazyLoad&&IntersectionObserver&&(this._observer=new IntersectionObserver((function(t){for(var e,i=0;e=t[i++];)e.isIntersecting&&(e.target.src=e.target.getAttribute("data-src"),e.target.removeAttribute("data-src"),r._observer.unobserve(e.target))}),{rootMargin:"500px 0px 500px 0px"}));var s=this,c=this.rtf.getElementsByTagName("title");c.length&&this.autosetTitle&&uni.setNavigationBarTitle({title:c[0].innerText}),this.imgList.length=0;for(var l,u=this.rtf.getElementsByTagName("img"),f=0,d=0;l=u[f];f++){l.style.maxWidth="100%",l.style.display="block";var h=l.getAttribute("src");this.domain&&h&&("/"==h[0]?"/"==h[1]?l.src=(this.domain.includes("://")?this.domain.split("://")[0]:"")+":"+h:l.src=this.domain+h:h.includes("://")||(l.src=this.domain+"/"+h)),l.hasAttribute("ignore")||"A"==l.parentElement.nodeName||(l.i=d++,s.imgList.push(l.src||l.getAttribute("data-src")),l.onclick=function(){var t=!0;this.ignore=function(){return t=!1},s.$emit("imgtap",this),t&&uni.previewImage({current:this.i,urls:s.imgList})}),l.onerror=function(){var t=this;s.$emit("error",{source:"img",target:this,context:{setSrc:function(e){return t.src=e}}})},s.lazyLoad&&this._observer&&l.src&&0!=l.i&&(l.setAttribute("data-src",l.src),l.removeAttribute("src"),this._observer.observe(l))}var p,m=this.rtf.getElementsByTagName("a"),_=(0,_createForOfIteratorHelper2.default)(m);try{for(_.s();!(p=_.n()).done;){var g=p.value;g.onclick=function(){var t=!0,e=this.getAttribute("href");if(s.$emit("linkpress",{href:e,ignore:function(){return t=!1}}),t&&e)if("#"==e[0])s.useAnchor&&s.navigateTo({id:e.substr(1)});else{if(0==e.indexOf("http")||0==e.indexOf("//"))return!0;uni.navigateTo({url:e})}return!1}}}catch(M){_.e(M)}finally{_.f()}var v=this.rtf.getElementsByTagName("video");s.videoContexts=v;for(var b,y=0;b=v[y++];)b.style.maxWidth="100%",b.onerror=function(){s.$emit("error",{source:"video",target:this,context:this})},b.onplay=function(){if(s.autopause)for(var t,e=0;t=s.videoContexts[e++];)t!=this&&t.pause()};var x,w,A=this.rtf.getElementsByTagName("audios"),T=(0,_createForOfIteratorHelper2.default)(A);try{for(T.s();!(x=T.n()).done;){var k=x.value;k.onerror=function(){s.$emit("error",{source:"audio",target:this,context:this})}}}catch(M){T.e(M)}finally{T.f()}e||this.document.appendChild(this.rtf),this.$nextTick((function(){r.nodes=[1],r.$emit("load")})),setTimeout((function(){return r.showAm=""}),500),clearInterval(this._timer),this._timer=setInterval((function(){var t=[r.rtf.getBoundingClientRect()];r.width=t[0].width,t[0].height==w&&(r.$emit("ready",t[0]),clearInterval(r._timer)),w=t[0].height}),350),this.showWithAnimation&&!e&&(this.showAm="animation:show .5s")}else this.rtf&&!e&&this.rtf.parentNode.removeChild(this.rtf)},getText:function(){arguments.length>0&&void 0!==arguments[0]||this.nodes;return this.rtf.innerText},navigateTo:function(t){if(!this.useAnchor)return t.fail&&t.fail({errMsg:"Anchor is disabled"});if(!t.id)return window.scrollTo(0,this.rtf.offsetTop),t.success&&t.success({errMsg:"pageScrollTo:ok"});var e=document.getElementById(t.id);if(!e)return t.fail&&t.fail({errMsg:"Label not found"});t.scrollTop=this.rtf.offsetTop+e.offsetTop,uni.pageScrollTo(t)},getVideoContext:function(t){if(!t)return this.videoContexts;for(var e=this.videoContexts.length;e--;)if(this.videoContexts[e].id==t)return this.videoContexts[e]},preLoad:function preLoad(html,num){html.constructor==Array&&(html=this._Dom2Str(html));var script="var contain=document.createElement('div');contain.innerHTML='"+html.replace(/'/g,"\\'")+"';for(var imgs=contain.querySelectorAll('img'),i=imgs.length-1;i>="+num+";i--)imgs[i].removeAttribute('src');";eval(script)},_tap:function(t){if(this.gestureZoom&&t.timeStamp-this._lastT<300){var e=t.touches[0].pageY-t.currentTarget.offsetTop;if(this._zoom)this._scaleAm.translateX(0).scale(1).step(),uni.pageScrollTo({scrollTop:(e+this._initY)/2-t.touches[0].clientY,duration:400});else{var r=t.touches[0].pageX-t.currentTarget.offsetLeft;this._initY=e,this._scaleAm=uni.createAnimation({transformOrigin:"".concat(r,"px ").concat(this._initY,"px 0"),timingFunction:"ease-in-out"}),this._scaleAm.scale(2).step(),this._tMax=r/2,this._tMin=(r-this.width)/2,this._tX=0}this._zoom=!this._zoom,this.scaleAm=this._scaleAm.export()}this._lastT=t.timeStamp},_touchstart:function(t){1==t.touches.length&&(this._initX=this._lastX=t.touches[0].pageX)},_touchmove:function(t){var e=t.touches[0].pageX-this._lastX;if(this._zoom&&1==t.touches.length&&Math.abs(e)>20){if(this._lastX=t.touches[0].pageX,this._tX<=this._tMin&&e<0||this._tX>=this._tMax&&e>0)return;this._tX+=e*Math.abs(this._lastX-this._initX)*.05,this._tX<this._tMin&&(this._tX=this._tMin),this._tX>this._tMax&&(this._tX=this._tMax),this._scaleAm.translateX(this._tX).step(),this.scaleAm=this._scaleAm.export()}}}};exports.default=_default},4330:function(t,e,r){"use strict";var i=r("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i(r("72d6")),a={components:{jyfParser:n.default},data:function(){return{id:0,article_data:""}},onLoad:function(t){this.id=t.id,this.getData()},methods:{getData:function(){var t=this;this.$http.request({url:this.$api.article.detail,showLoading:!0,data:{id:this.id}}).then((function(e){0==e.code?t.article_data=e.data.article:t.$http.toast(e.msg)}))}}};e.default=a},5389:function(t,e,r){"use strict";var i=r("2367"),n=r.n(i);n.a},"6b75":function(t,e,r){"use strict";function i(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,i=new Array(e);r<e;r++)i[r]=t[r];return i}Object.defineProperty(e,"__esModule",{value:!0}),e.default=i},"72d6":function(t,e,r){"use strict";r.r(e);var i=r("35cb"),n=r("18ae");for(var a in n)"default"!==a&&function(t){r.d(e,t,(function(){return n[t]}))}(a);r("5389");var o,s=r("f0c5"),c=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"3fb10be4",null,!1,i["a"],o);e["default"]=c.exports},"788f":function(t,e,r){"use strict";r.d(e,"b",(function(){return n})),r.d(e,"c",(function(){return a})),r.d(e,"a",(function(){return i}));var i={jyfParser:r("72d6").default},n=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("v-uni-view",{staticClass:"app"},[r("jyf-parser",{attrs:{html:t.article_data.content}})],1)},a=[]},"857b":function(t,e,r){var i=r("24fb");e=i(!1),e.push([t.i,"@-webkit-keyframes show-data-v-3fb10be4{0%{opacity:0}100%{opacity:1}}@keyframes show-data-v-3fb10be4{0%{opacity:0}100%{opacity:1}}\n\n\n\n",""]),t.exports=e},"8d93":function(t,e,r){"use strict";r.r(e);var i=r("4330"),n=r.n(i);for(var a in i)"default"!==a&&function(t){r.d(e,t,(function(){return i[t]}))}(a);e["default"]=n.a},"91e0":function(t,e,r){"use strict";r.r(e);var i=r("788f"),n=r("8d93");for(var a in n)"default"!==a&&function(t){r.d(e,t,(function(){return n[t]}))}(a);var o,s=r("f0c5"),c=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"474bf40c",null,!1,i["a"],o);e["default"]=c.exports},b85c:function(t,e,r){"use strict";r("a4d3"),r("e01a"),r("d28b"),r("d3b7"),r("3ca3"),r("ddb0"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=a;var i=n(r("06c5"));function n(t){return t&&t.__esModule?t:{default:t}}function a(t,e){var r;if("undefined"===typeof Symbol||null==t[Symbol.iterator]){if(Array.isArray(t)||(r=(0,i.default)(t))||e&&t&&"number"===typeof t.length){r&&(t=r);var n=0,a=function(){};return{s:a,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:a}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var o,s=!0,c=!1;return{s:function(){r=t[Symbol.iterator]()},n:function(){var t=r.next();return s=t.done,t},e:function(t){c=!0,o=t},f:function(){try{s||null==r["return"]||r["return"]()}finally{if(c)throw o}}}}}}]);