!function(e){const t=e.en=e.en||{};t.dictionary=Object.assign(t.dictionary||{},{"Horizontal line":"Horizontal line"})}(window.CKEDITOR_TRANSLATIONS||(window.CKEDITOR_TRANSLATIONS={})),
/*!
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md.
 */(()=>{var e={427:(e,t,n)=>{"use strict";n.d(t,{A:()=>i});var r=n(935),o=n.n(r)()((function(e){return e[1]}));o.push([e.id,".ck-editor__editable .ck-horizontal-line{display:flow-root}.ck-content hr{background:#dedede;border:0;height:4px;margin:15px 0}",""]);const i=o},935:e=>{"use strict";e.exports=function(e){var t=[];return t.toString=function(){return this.map((function(t){var n=e(t);return t[2]?"@media ".concat(t[2]," {").concat(n,"}"):n})).join("")},t.i=function(e,n,r){"string"==typeof e&&(e=[[null,e,""]]);var o={};if(r)for(var i=0;i<this.length;i++){var a=this[i][0];null!=a&&(o[a]=!0)}for(var s=0;s<e.length;s++){var c=[].concat(e[s]);r&&o[c[0]]||(n&&(c[2]?c[2]="".concat(n," and ").concat(c[2]):c[2]=n),t.push(c))}},t}},591:(e,t,n)=>{"use strict";var r,o=function(){return void 0===r&&(r=Boolean(window&&document&&document.all&&!window.atob)),r},i=function(){var e={};return function(t){if(void 0===e[t]){var n=document.querySelector(t);if(window.HTMLIFrameElement&&n instanceof window.HTMLIFrameElement)try{n=n.contentDocument.head}catch(e){n=null}e[t]=n}return e[t]}}(),a=[];function s(e){for(var t=-1,n=0;n<a.length;n++)if(a[n].identifier===e){t=n;break}return t}function c(e,t){for(var n={},r=[],o=0;o<e.length;o++){var i=e[o],c=t.base?i[0]+t.base:i[0],l=n[c]||0,u="".concat(c," ").concat(l);n[c]=l+1;var d=s(u),f={css:i[1],media:i[2],sourceMap:i[3]};-1!==d?(a[d].references++,a[d].updater(f)):a.push({identifier:u,updater:v(f,t),references:1}),r.push(u)}return r}function l(e){var t=document.createElement("style"),r=e.attributes||{};if(void 0===r.nonce){var o=n.nc;o&&(r.nonce=o)}if(Object.keys(r).forEach((function(e){t.setAttribute(e,r[e])})),"function"==typeof e.insert)e.insert(t);else{var a=i(e.insert||"head");if(!a)throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");a.appendChild(t)}return t}var u,d=(u=[],function(e,t){return u[e]=t,u.filter(Boolean).join("\n")});function f(e,t,n,r){var o=n?"":r.media?"@media ".concat(r.media," {").concat(r.css,"}"):r.css;if(e.styleSheet)e.styleSheet.cssText=d(t,o);else{var i=document.createTextNode(o),a=e.childNodes;a[t]&&e.removeChild(a[t]),a.length?e.insertBefore(i,a[t]):e.appendChild(i)}}function h(e,t,n){var r=n.css,o=n.media,i=n.sourceMap;if(o?e.setAttribute("media",o):e.removeAttribute("media"),i&&"undefined"!=typeof btoa&&(r+="\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(i))))," */")),e.styleSheet)e.styleSheet.cssText=r;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(r))}}var m=null,p=0;function v(e,t){var n,r,o;if(t.singleton){var i=p++;n=m||(m=l(t)),r=f.bind(null,n,i,!1),o=f.bind(null,n,i,!0)}else n=l(t),r=h.bind(null,n,t),o=function(){!function(e){if(null===e.parentNode)return!1;e.parentNode.removeChild(e)}(n)};return r(e),function(t){if(t){if(t.css===e.css&&t.media===e.media&&t.sourceMap===e.sourceMap)return;r(e=t)}else o()}}e.exports=function(e,t){(t=t||{}).singleton||"boolean"==typeof t.singleton||(t.singleton=o());var n=c(e=e||[],t);return function(e){if(e=e||[],"[object Array]"===Object.prototype.toString.call(e)){for(var r=0;r<n.length;r++){var o=s(n[r]);a[o].references--}for(var i=c(e,t),l=0;l<n.length;l++){var u=s(n[l]);0===a[u].references&&(a[u].updater(),a.splice(u,1))}n=i}}}},782:(e,t,n)=>{e.exports=n(237)("./src/core.js")},311:(e,t,n)=>{e.exports=n(237)("./src/ui.js")},901:(e,t,n)=>{e.exports=n(237)("./src/widget.js")},237:e=>{"use strict";e.exports=CKEditor5.dll}},t={};function n(r){var o=t[r];if(void 0!==o)return o.exports;var i=t[r]={id:r,exports:{}};return e[r](i,i.exports,n),i.exports}n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var r in t)n.o(t,r)&&!n.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),n.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.nc=void 0;var r={};(()=>{"use strict";n.r(r),n.d(r,{HorizontalLine:()=>f,HorizontalLineEditing:()=>l,HorizontalLineUI:()=>d});var e=n(782),t=n(901);class o extends e.Command{refresh(){const e=this.editor.model,n=e.schema,r=e.document.selection;this.isEnabled=function(e,n,r){const o=function(e,n){const r=(0,t.findOptimalInsertionRange)(e,n),o=r.start.parent;if(o.isEmpty&&!o.is("element","$root"))return o.parent;return o}(e,r);return n.checkChild(o,"horizontalLine")}(r,n,e)}execute(){const e=this.editor.model;e.change((t=>{const n=t.createElement("horizontalLine");e.insertObject(n,null,null,{setSelection:"after"})}))}}var i=n(591),a=n.n(i),s=n(427),c={injectType:"singletonStyleTag",attributes:{"data-cke":!0},insert:"head",singleton:!0};a()(s.A,c);s.A.locals;class l extends e.Plugin{static get pluginName(){return"HorizontalLineEditing"}init(){const e=this.editor,n=e.model.schema,r=e.t,i=e.conversion;n.register("horizontalLine",{inheritAllFrom:"$blockObject"}),i.for("dataDowncast").elementToElement({model:"horizontalLine",view:(e,{writer:t})=>t.createEmptyElement("hr")}),i.for("editingDowncast").elementToStructure({model:"horizontalLine",view:(e,{writer:n})=>{const o=r("Horizontal line"),i=n.createContainerElement("div",null,n.createEmptyElement("hr"));return n.addClass("ck-horizontal-line",i),n.setCustomProperty("hr",!0,i),function(e,n,r){return n.setCustomProperty("horizontalLine",!0,e),(0,t.toWidget)(e,n,{label:r})}(i,n,o)}}),i.for("upcast").elementToElement({view:"hr",model:"horizontalLine"}),e.commands.add("horizontalLine",new o(e))}}var u=n(311);class d extends e.Plugin{static get pluginName(){return"HorizontalLineUI"}init(){const e=this.editor;e.ui.componentFactory.add("horizontalLine",(()=>{const e=this._createButton(u.ButtonView);return e.set({tooltip:!0}),e})),e.ui.componentFactory.add("menuBar:horizontalLine",(()=>this._createButton(u.MenuBarMenuListItemButtonView)))}_createButton(t){const n=this.editor,r=n.locale,o=n.commands.get("horizontalLine"),i=new t(n.locale),a=r.t;return i.set({label:a("Horizontal line"),icon:e.icons.horizontalLine}),i.bind("isEnabled").to(o,"isEnabled"),this.listenTo(i,"execute",(()=>{n.execute("horizontalLine"),n.editing.view.focus()})),i}}class f extends e.Plugin{static get requires(){return[l,d,t.Widget]}static get pluginName(){return"HorizontalLine"}}})(),(window.CKEditor5=window.CKEditor5||{}).horizontalLine=r})();