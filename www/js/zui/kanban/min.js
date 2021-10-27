/*!
 * ZUI: ZUI Kanban View - v1.9.2 - 2021-10-27
 * http://openzui.com
 * GitHub: https://github.com/easysoft/zui.git 
 * Copyright (c) 2021 cnezsoft.com; Licensed MIT
 */
!function(a){"use strict";var n="zui.kanban",e="object"==typeof CSS&&CSS.supports("display","flex"),t=function(o,i){var d=this;d.name=n,d.$=a(o).addClass("kanban"),d.options=i=a.extend({},t.DEFAULTS,this.$.data(),i),d.data=i.data||[],d.render(d.data);var s=function(n){if(i.onAction){var e=a(this);i.onAction(e.data("action"),e,n,d)}};if(d.$.on("click",".action",s).on("dblclick",".action-dbc",s),"auto"===i.droppable&&(i.droppable=!i.readonly),i.droppable){var r={selector:".kanban-item",target:'.kanban-lane-col:not([data-type="EMPTY"])',drop:function(a){"function"==typeof i.droppable?i.droppable(a):i.onAction&&i.onAction("dropItem",a.element,a,d)}};"object"==typeof i.droppable&&a.extend(r,i.droppable),d.$.droppable(r)}e&&i.useFlex||a(window).on("resize",function(){d.adjustSize()}),i.useFlex&&e||d.$.addClass("not-use-flex"),i.onCreate&&i.onCreate(d)};t.prototype.render=function(a){var n=this;a&&(n.data=a),n.data&&!Array.isArray(n.data)&&(n.data=[n.data]);var t=n.data||[];n.options.beforeRender&&n.options.beforeRender(n,t),n.$.toggleClass("kanban-readonly",!!n.options.readonly).toggleClass("kanban-no-lane-name",!!n.options.noLaneName),n.$.children(".kanban-board").addClass("kanban-expired");for(var o=0;o<t.length;++o)n.renderKanban(o);n.$.children(".kanban-expired").remove(),e&&n.options.useFlex||setTimeout(n.adjustSize.bind(this),200),n.options.onRender&&n.options.onRender(n)},t.prototype.renderKanban=function(n){if("number"==typeof n)n=this.data[n];else{var t=this.data.findIndex(function(a){return a.id===n.id});if(t>-1){var o=this.data[t];n=a.extend(o,n),this.data[t]=n}else this.data.push(n)}n.id||(n.id=a.zui.uuid());var i=this,d=i.$,s=d.children('.kanban-board[data-id="'+n.id+'"]');s.length?s.removeClass("kanban-expired"):(s=a('<div class="kanban-board" data-id="'+n.id+'"></div>').appendTo(d),e||s.addClass("no-flex")),i.renderKanbanHeader(n,s),s.children(".kanban-lane").addClass("kanban-expired");for(var r=n.lanes||[],l=0;l<r.length;++l)i.renderLane(r[l],n.columns,s,n);if(s.children(".kanban-expired").remove(),i.options.showCount)for(var l=0;l<n.columns.length;++l){var c=n.columns[l].id,p=s.find('.kanban-lane-col[data-id="'+c+'"] > .kanban-lane-items .kanban-item').length,h=s.find('.kanban-header-col[data-id="'+c+'"] > .title > .count');i.options.countRender?i.options.countRender(h,p,n.columns[l],i):h.text(p||(i.options.showZeroCount?0:""))}i.adjustKanbanSize(n,s),i.options.onRenderKanban&&i.options.onRenderKanban(s,n,i)},t.prototype.renderKanbanHeader=function(n,t){var o=this;t=t||o.$.children('.kanban-board[data-id="'+n.id+'"]');var i=t.children(".kanban-header");i.length||(i=a('<div class="kanban-header"></div>').prependTo(t),e||i.addClass("clearfix")),i.children(".kanban-col").addClass("kanban-expired");for(var d=n.columns,s={},r=!1,l=0;l<d.length;++l){var c=d[l];c.id||(c.id=c.type),c.asParent&&(r=!0,c.subs=[],s[c.type]=c)}for(var l=0;l<d.length;++l){var c=d[l];if(c.asParent)o.renderHeaderParentCol(d[l],i);else{var p=null;c.parentType&&(p=s[c.parentType],p.subs.push(c)),o.renderHeaderCol(d[l],i,p)}}this.options.readonly||o.renderHeaderCol({id:"ADD",kanban:n.id,name:o.options.createColumnText,icon:"plus",type:"ADD"},i),i.toggleClass("kanban-header-has-parent",!!r).children(".kanban-expired").remove()},t.prototype.renderHeaderParentCol=function(n,e){var t=this,o=e.children('.kanban-header-parent-col[data-id="'+n.id+'"]');o.length||(o=a(['<div class="kanban-col kanban-header-col kanban-header-parent-col" data-id="'+n.id+'">','<div class="kanban-header-col">','<div class="title">','<i class="icon"></i>','<span class="text"></span>',t.options.showCount?'<span class="count"></span>':"","</div>","</div>",'<div class="kanban-header-sub-cols">',"</div>","</div>"].join("")).appendTo(e)),o.data("col",n),o.attr("data-type",n.type).attr("data-subs-count",n.subs.length);var i=o.children(".kanban-header-col");i.find(".title>.icon").attr("class","icon icon-"+(n.icon||""));var d=i.find(".title>.text").text(n.name);n.color&&d.css("color",n.color),t.options.showCount&&i.find(".title>.count").text(n.count||(t.options.showZeroCount?0:"")),t.options.onRenderHeaderCol&&t.options.onRenderHeaderCol(o,n,e)},t.prototype.renderHeaderCol=function(n,e,t){var o=this;if(n.parentType&&t){var i=e.children('.kanban-header-parent-col[data-id="'+t.id+'"]');i.attr("data-subs-count",t.subs.length),o.options.useFlex&&i.css("flex",t.subs.length+" 1 0%"),e=i.children(".kanban-header-sub-cols")}var d=e.children('.kanban-header-col[data-id="'+n.id+'"]'),s="ADD"===n.id;if(d.length)d.removeClass("kanban-expired"),s&&d.appendTo(e);else{d=a(['<div class="kanban-col kanban-header-col" data-id="'+n.id+'">',"<"+(s?"a":"div")+' class="title">','<i class="icon"></i>','<span class="text"></span>',o.options.showCount?'<span class="count"></span>':"","</"+(s?"a":"div")+">","</div>"].join("")).appendTo(e),s?d.children(".title").addClass("action").attr("data-action","addCol"):(d.children(".title").addClass("action-dbc").attr("data-action","editCol"),o.options.readonly||d.append(['<div class="actions">','<button class="btn btn-link action" type="button" data-action="headerMore"><i class="icon icon-more-v"></i></button>',"</div>"].join("")));var r=o.options.minColWidth;r&&d.css("min-width",r);var l=o.options.maxColWidth;l&&d.css("max-width",o.options.maxColWidth)}d.data("col",n),d.attr("data-type",n.type),d.find(".title>.icon").attr("class","icon icon-"+(n.icon||""));var c=d.find(".title>.text").text(n.name);n.color&&c.css("color",n.color),o.options.showCount&&d.find(".title>.count").text(n.count||(o.options.showZeroCount?0:"")),o.options.onRenderHeaderCol&&o.options.onRenderHeaderCol(d,n,e)},t.prototype.renderLane=function(n,t,o,i){var d=this;o=o||d.$.children('.kanban-board[data-id="'+n.kanban+'"]');var s=o.children('.kanban-lane[data-id="'+n.id+'"]');if(s.length?s.removeClass("kanban-expired"):(s=a('<div class="kanban-lane" data-id="'+n.id+'"></div>').appendTo(o),e||s.addClass("clearfix")),s.data("lane",n),!d.options.noLaneName){var r=s.children('.kanban-lane-name[data-id="'+n.id+'"]');r.length||(r=a('<div class="kanban-lane-name action-dbc" data-action="editLaneName" data-id="'+n.id+'"></div>').appendTo(s)),r.empty().attr("title",n.name).append(a('<span class="text" />').text(n.name)),n.color&&r.css("background-color",n.color),d.options.onRenderLaneName&&d.options.onRenderLaneName(r,n,o,t)}s.children(".kanban-col,.kanban-sub-lanes").addClass("kanban-expired"),s.toggleClass("has-sub-lane",!!n.subLanes),n.subLanes?d.renderSubLanes(n,t,s):d.renderLaneItems(t,n.items,s,n,i),d.options.readonly||d.renderLaneCol({id:"EMPTY",type:"EMPTY"},s),s.children(".kanban-expired").remove()},t.prototype.renderSubLanes=function(n,e,t){var o=this,i=t.children('.kanban-sub-lanes[data-id="'+n.id+'"]');i.length?i.removeClass("kanban-expired"):i=a('<div class="kanban-sub-lanes" data-id="'+n.id+'"></div>').appendTo(t),i.children(".kanban-sub-lane").addClass("kanban-expired");for(var d=0;d<n.subLanes.length;++d){var s=n.subLanes[d];o.renderSubLane(s,e,i)}i.toggleClass("no-sub-lane",!n.subLanes.length).attr("data-sub-lanes-count",n.subLanes.length).children(".kanban-expired").remove()},t.prototype.renderSubLane=function(n,t,o,i){var d=o.children('.kanban-sub-lane[data-id="'+n.id+'"]');d.length?d.removeClass("kanban-expired"):(d=a('<div class="kanban-sub-lane" data-id="'+n.id+'"></div>').appendTo(o),e||d.addClass("clearfix")),d.children(".kanban-col").addClass("kanban-expired"),this.renderLaneItems(t,n.items,d,n,i),d.children(".kanban-expired").remove()},t.prototype.renderLaneItems=function(a,n,e,t,o){for(var i=this,d=0;d<a.length;++d){var s=a[d];if(!s.asParent){var r=i.renderLaneCol(s,e),l=n[s.type]||[];i.renderColumnItems(s,l,r,t,o)}}},t.prototype.renderColumnItems=function(a,n,e,t,o){var i=e.find(".kanban-lane-items");i.children(".kanban-item").addClass("kanban-expired");for(var d=0;d<n.length;++d){var s=n[d];this.renderLaneItem(s,i,a,t,o)}i.children(".kanban-expired").remove()},t.prototype.renderLaneCol=function(n,e){var t=this,o=e.children('.kanban-lane-col[data-id="'+n.id+'"]');if(o.length)o.removeClass("kanban-expired");else{o=a('<div class="kanban-col kanban-lane-col" data-id="'+n.id+'"></div>').appendTo(e),"EMPTY"!==n.id&&(o.append('<div class="kanban-lane-items"></div>'),t.options.readonly||o.append(['<div class="kanban-lane-actions">','<button class="btn btn-default btn-block action" type="button" data-action="addItem"><span class="text-muted"><i class="icon icon-plus"></i> '+t.options.addItemText+"</span></button>","</div>"].join("")));var i=t.options;i.minColWidth&&o.css("min-width",i.minColWidth),i.maxColWidth&&o.css("max-width",i.maxColWidth),i.maxColHeight&&o.find(".kanban-lane-items").css("max-height",i.maxColHeight),i.laneItemsClass&&o.find(".kanban-lane-items").addClass(i.laneItemsClass),i.laneColClass&&o.addClass(i.laneColClass)}return"EMPTY"===n.id&&o.appendTo(e),o.attr("data-type",n.type),o},t.prototype.renderLaneItem=function(n,e,t,o,i){var d=e.children('.kanban-item[data-id="'+n.id+'"]');if(d.length?d.removeClass("kanban-expired"):d=a('<div class="kanban-item" data-id="'+n.id+'"></div>').appendTo(e),d.data("item",n),this.options.itemRender)this.options.itemRender(n,d,t,o,i);else{var s=d.find(".title");s.length||(s=a('<div class="title"></div>').appendTo(d)),s.text(n.name||n.title)}return d},t.prototype.adjustKanbanSize=function(n,t){for(var o=this,i=n.columns,d=o.options.noLaneName?0:o.options.laneNameWidth,s=o.options.readonly?0:1,r=0;r<i.length;++r)i[r].asParent||s++;var l=o.options.fluidBoardWidth,c=o.options.minColWidth*s+d;if(t=t||o.$.children('.kanban-board[data-id="'+n.id+'"]'),t.removeClass("kanban-size-inited").css(l?"width":"min-width",c),!o.options.useFlex||!e){var p=l?c:Math.max(c,o.$.width()),h=Math.floor((p-d)/s);t.find(".kanban-col").each(function(){var n=a(this),e=h;n.hasClass("kanban-header-parent-col")&&(e*=n.data("subsCount")),n.css("width",e)}),o.options.maxColHeight&&"auto"!==o.options.maxColHeight&&(t.children(".kanban-lane").each(function(){var n=a(this),e=n.height();n.children(".kanban-col,.kanban-sub-lanes").css("min-height",e)}),t.find(".kanban-lane:not(.has-sub-lane),.kanban-sub-lane").each(function(){var n=a(this),e=n.height(),t=n.children(".kanban-lane-col").css("height","auto");t.each(function(){e=Math.max(e,a(this).outerHeight()+1)}),t.css("height",e)})),t.addClass("kanban-size-inited")}},t.prototype.adjustSize=function(){for(var a=0;a<this.data.length;++a)this.adjustKanbanSize(this.data[a])},t.DEFAULTS={minColWidth:100,maxColHeight:400,fluidBoardWidth:!1,laneNameWidth:20,addItemText:"添加条目",createColumnText:"添加看板列",useFlex:!0,itemRender:null,droppable:"auto",readonly:!1,laneColClass:"",noLaneName:!1,showCount:!0,showZeroCount:!1},a.fn.kanban=function(e){return this.each(function(){var o=a(this),i=o.data(n),d="object"==typeof e&&e;i||o.data(n,i=new t(this,d)),"string"==typeof e&&i[e]()})},t.NAME=n,a.fn.kanban.Constructor=t}(jQuery);