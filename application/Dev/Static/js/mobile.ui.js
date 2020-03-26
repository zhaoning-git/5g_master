var NoMore = "<div class='nomore ui-border-t'><i class='uk-icon-uniF118'></i> 已经到底了</div>";
var ajax_container = '#ajax-page-container';


$(window).scroll(function() { 
	if($(window).scrollTop()>700){
		$('a.go-top').show();
	}else{
		$('a.go-top').hide();
	}
}); 

$(document).on('click','a.go-top', function() {
	$('body').scrollTop(0);
});

//监听键盘，避免键盘将输入框遮住
$("input[type='text'],textarea").focus(function(){
var thistop = $(this).offset().top;
setTimeout(function(){
	crollTop = thistop-40; 
},100);
})

//ajax get请求只要在提交按钮上加上ajax-get即可提交表单
$(document).on('click','[data-ajax]', function() {
	var that ,url, type, query, form ,info ,icon ,load_icon ,time, event, callback;
	that = $(this);
	type = that.data('ajax');
	load = that.data('load');
	form = that.data('form');
	time = that.data('time'); //data-info='1500'; 在不存在data-info的情况下，在顶部出现通知后默认多少秒执行跳转，如果为空则不弹出通知，直接跳转
	info = that.data('info');//获得提示弹层内容 使用json格式， data-info='{"title":"温馨提示","desc":"确定要删除吗","button":"确定"}
	event = that.data('event');//回调事件，如回调成功后删除某个dom节点 使用json格式，data-event='{"del":".dom-classname"} 目前只添加了2个回调，原地刷新页面和删除上级节点
	callback = that.data('callback');//自定义回调事件 举例 <a href="xxx" data-ajax="get"  data-info='{"desc":"确定要删除吗"}' data-callback="test">删除</a> 在页面中直接使用 function test(){ alert('回调成功')	} 即可

	icon = that.find('i').attr('class');//记录按钮里的图标
	load_icon = '<i class="uk-icon-spinner uk-icon-spin"></i>';
	

	//以下3中写法都可以进入ajax提交
	if (($(this).attr('type') == 'submit') || (url = $(this).attr('href')) || (url = $(this).attr('url'))) {
		//处理按钮中的图标
		if(that.hasClass('btn')){
			if(!icon){
				that.prepend(load_icon);
			}else{
				that.find('i').attr('class','uk-icon-spinner uk-icon-spin');	
			}
		}
		
		if(type == 'post'){
			if(form){
		  		form = $(form);
			}else{
				form = that.closest('form')
			}
			url = form.attr('action');
			query = form.find('input,select,textarea').serialize();
		}else{
			query = false;
		}
 
		//现将提交操作放到jquery内部自定义函数里等待执行
		$.extend({is_ok:function(){  
			$.ajax({
					async:true,
					type: type,
					data: query,
					url: url,
					dataType:'json',
					beforeSend:function(){
						that.addClass('disabled').attr('autocomplete', 'off').prop('disabled', true);
						OpenLoad(load);
					},
					success:function(data){
						if(data.status == 1){
							Alert(data.info,'success',info,data.url,time);
							if(event) do_event(that,event);
							if(callback) eval(''+callback+'("'+data.info+'" )');
						}else{
							Alert(data.info,'error',info,data.url,time);
						}
					},
					complete:function(html){
						CloseLoad();
						that.removeClass('disabled').prop('disabled', false);
						if(!icon){
							that.find('i').remove();
						}else{
							that.find('i').attr('class',icon);
						}
					},
					error:function(){
						Alert('服务器错误','error');
					}
			});
		}});
		
		//处理按钮提示语
		if(info){
			(info.title)?info.title=info.title : info.title='操作提示';
			(info.desc)?info.desc=info.desc : info.desc='你确定要进行该操作吗？';
			(info.button)?info.button=info.button : info.button='确定';
			swal({ title: info.title,   text: info.desc,   type: "warning",   showCancelButton: true, confirmButtonText: info.button,   closeOnConfirm: false }, function(isConfirm){ 
				if (isConfirm) { 
						$.is_ok(); 
				 } else { 
				 	if(!icon){
							that.find('i').remove();
					}else{
							that.find('i').attr('class',icon);
					}
				 }
			});
		}else{
			$.is_ok();
		}
		return false;
	}
});

//处理回调事件
function do_event(obj,event){
	$.each(event, function(index,value) {
		
		if(index == 'find_del'){ 
			var dom = value.split(",");
			obj.closest(dom[0]).find(dom[1]).remove();
			
		}
		
		if(index == 'del'){ 
			obj.closest(value).remove();
		}
		if(index == 'refresh'){ 
			 location.reload();
		}
	});
}

//信息提示消息
window.Alert = function (text, type, style ,url ,time) {
	var icon;
	if(style){
		//alert弹层类型	
		
		switch(type){
		case 'success':
		  title = '成功提示';  break;
		case 'warning':
		  title = '操作提示';  break;
		case 'error':
		  title = '失败提示';  break;
		default:
		  title = '温馨提示';
		}
		if(url){
			swal({ title: title, text: text, type: type }, function(){  
				location.href = url;
			});
		}else{
			swal({ title: title, text: text, type: type }, function(){  
				$('#triggerModal').modal('hide');
			});
		}
	}else{
		//顶部通知类型	
		switch(type){
		case 'success':
		  icon = 'check';  break;
		case 'warning':
		 icon = 'exclamation';  break;
		case 'error':
		  icon = 'close'; break;
		default:
		  icon = 'exclamation';
		}
	
		if(url&&time){
			var msg = $.zui.messager.show(text, {type:type,placement:'top',close:false,icon:icon}); msg.show();
			setTimeout(function () {
				location.href = url;
			}, time);
		}else if(url){
			location.href = url;
		}else{
			var msg = $.zui.messager.show(text, {type:type,placement:'top',close:false,icon:icon}); msg.show();
		}
	}
};



//ajax页面
$(document).on('touchstart','[data-page]', function() { 
    var that,url,container;
	that = $(this);
	url = that.attr('href');
	container = that.data('page');
	if(!container){
		container = ajax_container;
	}
	if(!url){
		return false
	}
	$.ajax({
			async:true,
			type: 'get',
			url: url,
			cache:false,
			data :'ajax_page=true',
			dataType:'html',
			beforeSend:function(){
				OpenLoad();
			},
			success:function(html){
				$(container).html(html); 
			},
			complete:function(){
				CloseLoad();
			},
			error:function(){
				alert('页面错误');
			}
	});
	return false
})



$(function(){
	$(document).on('DOMNodeInserted',ajax_container, function() {

		window.loading = false;
	})
	$(window).scroll(function(){
		
		if(($("html").height()-$("body").scrollTop())<=document.documentElement.clientHeight+150){
				
		if (window.loading) return false;	// 如果正在加载，则退出
		  window.loading = 1;
		  var obj = $('[data-load]');
		  if(!obj.html()) return false;
		  var url = obj.data('load').url;
		  var id = obj.attr('id');
		  var count = obj.data('load').count;
		  var number =  obj.find('.row').length;

		  if(count-number<=0){
			  $('.infinite-scroll-preloader').html(NoMore);
			 return false;
		  }
		  setTimeout(function(){ 
				$.ajax({
						async:true,
						type: 'get',
						url: url,
						cache:false,
						data :'number='+number+'&ajax_page=true',
						dataType:'html',
						beforeSend:function(){
							 $('.infinite-scroll-preloader').html('<div class="preloader"></div>');
						},
						success:function(html){
							if(html){
								 obj.append($(html).find('[data-load]').html());
								 window.loading = false;
							}else{
								Alert('没有数据');
								$('.preloader').remove();
							}
						},
						error:function(){
							alert('数据错误');
						}
				});
		 },500)	
		}
	}); 
});
 


function OpenLoad(text){
	if(text){
		$('body').append('<div class="load load-no-buttons load-in" style="display: block; margin-top: -61px;"><div class="load-inner"><div class="load-title">'+text+'</div><div class="load-text"><div class="preloader"></div></div></div></div><div class="load-overlay load-overlay-visible"></div>');
	}else{
		$('body').append('<div class="preloader-indicator-overlay" ></div><div class="preloader-indicator-load"><span class="preloader preloader-white"></span></div>');
	}
}
function CloseLoad(text){
	$('.load,.load-overlay,.preloader-indicator-overlay,.preloader-indicator-load').remove();
}



function Empty(text,dom,style){
	
	if(!style){
		ico = '/Public/icon/empty.svg';
	}else if(style == 'success'){
		ico = '/Public/icon/ok.svg';
	}
	
	var html = '';
		html+="<div class='empty-box'>";
		html+="<ul>";
		html+="<li class='big-icon'><img src='"+ico+"' width='60%' style='margin-left:10px'></li>";
		html+="<li class='title'>"+text+"</li>";
		html+="<li class='other'><a href='javascript:void(0)' onClick='wx.closeWindow();' >关闭页面</a>  <a href='/Ucenter/index/index.html'>个人中心</a> </li>";
		html+="</ul>";
		html+="</div>";
	$(dom).html(html);
}




/**
 * 模拟U函数
 * @param url
 * @param params
 * @returns {string}
 * @constructor
 */
function U(url, params, rewrite) {


	if (window.Think.MODEL[0] == 2) {

		var website = window.Think.ROOT + '/';
		url = url.split('/');

		if (url[0] == '' || url[0] == '@')
			url[0] = APPNAME;
		if (!url[1])
			url[1] = 'Index';
		if (!url[2])
			url[2] = 'index';
		website = website + '' + url[0] + '/' + url[1] + '/' + url[2];

		if (params) {
			params = params.join('/');
			website = website + '/' + params;
		}
		if (!rewrite) {
			website = website + '.html';
		}

	} else {
		var website = window.Think.ROOT + '/index.php';
		url = url.split('/');
		if (url[0] == '' || url[0] == '@')
			url[0] = APPNAME;
		if (!url[1])
			url[1] = 'Index';
		if (!url[2])
			url[2] = 'index';
		website = website + '?s=/' + url[0] + '/' + url[1] + '/' + url[2];
		if (params) {
			params = params.join('/');
			website = website + '/' + params;
		}
		if (!rewrite) {
			website = website + '.html';
		}
	}

	if(typeof (window.Think.MODEL[1])!='undefined'){
		website=website.toLowerCase();
	}
	return website;
}


 



/*提示框*/
!function(e,t,n){"use strict";!function o(e,t,n){function a(s,l){if(!t[s]){if(!e[s]){var i="function"==typeof require&&require;if(!l&&i)return i(s,!0);if(r)return r(s,!0);var u=new Error("Cannot find module '"+s+"'");throw u.code="MODULE_NOT_FOUND",u}var c=t[s]={exports:{}};e[s][0].call(c.exports,function(t){var n=e[s][1][t];return a(n?n:t)},c,c.exports,o,e,t,n)}return t[s].exports}for(var r="function"==typeof require&&require,s=0;s<n.length;s++)a(n[s]);return a}({1:[function(o,a,r){var s=function(e){return e&&e.__esModule?e:{"default":e}};Object.defineProperty(r,"__esModule",{value:!0});var l,i,u,c,d=o("./modules/handle-dom"),f=o("./modules/utils"),p=o("./modules/handle-swal-dom"),m=o("./modules/handle-click"),v=o("./modules/handle-key"),y=s(v),h=o("./modules/default-params"),b=s(h),g=o("./modules/set-params"),w=s(g);r["default"]=u=c=function(){function o(e){var t=a;return t[e]===n?b["default"][e]:t[e]}var a=arguments[0];if(d.addClass(t.body,"stop-scrolling"),p.resetInput(),a===n)return f.logStr("SweetAlert expects at least 1 attribute!"),!1;var r=f.extend({},b["default"]);switch(typeof a){case"string":r.title=a,r.text=arguments[1]||"",r.type=arguments[2]||"";break;case"object":if(a.title===n)return f.logStr('Missing "title" argument!'),!1;r.title=a.title;for(var s in b["default"])r[s]=o(s);r.confirmButtonText=r.showCancelButton?"Confirm":b["default"].confirmButtonText,r.confirmButtonText=o("confirmButtonText"),r.doneFunction=arguments[1]||null;break;default:return f.logStr('Unexpected type of argument! Expected "string" or "object", got '+typeof a),!1}w["default"](r),p.fixVerticalPosition(),p.openModal(arguments[1]);for(var u=p.getModal(),v=u.querySelectorAll("button"),h=["onclick","onmouseover","onmouseout","onmousedown","onmouseup","onfocus"],g=function(e){return m.handleButton(e,r,u)},C=0;C<v.length;C++)for(var S=0;S<h.length;S++){var x=h[S];v[C][x]=g}p.getOverlay().onclick=g,l=e.onkeydown;var k=function(e){return y["default"](e,r,u)};e.onkeydown=k,e.onfocus=function(){setTimeout(function(){i!==n&&(i.focus(),i=n)},0)},c.enableButtons()},u.setDefaults=c.setDefaults=function(e){if(!e)throw new Error("userParams is required");if("object"!=typeof e)throw new Error("userParams has to be a object");f.extend(b["default"],e)},u.close=c.close=function(){var o=p.getModal();d.fadeOut(p.getOverlay(),5),d.fadeOut(o,5),d.removeClass(o,"showSweetAlert"),d.addClass(o,"hideSweetAlert"),d.removeClass(o,"visible");var a=o.querySelector(".sa-icon.sa-success");d.removeClass(a,"animate"),d.removeClass(a.querySelector(".sa-tip"),"animateSuccessTip"),d.removeClass(a.querySelector(".sa-long"),"animateSuccessLong");var r=o.querySelector(".sa-icon.sa-error");d.removeClass(r,"animateErrorIcon"),d.removeClass(r.querySelector(".sa-x-mark"),"animateXMark");var s=o.querySelector(".sa-icon.sa-warning");return d.removeClass(s,"pulseWarning"),d.removeClass(s.querySelector(".sa-body"),"pulseWarningIns"),d.removeClass(s.querySelector(".sa-dot"),"pulseWarningIns"),setTimeout(function(){var e=o.getAttribute("data-custom-class");d.removeClass(o,e)},300),d.removeClass(t.body,"stop-scrolling"),e.onkeydown=l,e.previousActiveElement&&e.previousActiveElement.focus(),i=n,clearTimeout(o.timeout),!0},u.showInputError=c.showInputError=function(e){var t=p.getModal(),n=t.querySelector(".sa-input-error");d.addClass(n,"show");var o=t.querySelector(".sa-error-container");d.addClass(o,"show"),o.querySelector("p").innerHTML=e,setTimeout(function(){u.enableButtons()},1),t.querySelector("input").focus()},u.resetInputError=c.resetInputError=function(e){if(e&&13===e.keyCode)return!1;var t=p.getModal(),n=t.querySelector(".sa-input-error");d.removeClass(n,"show");var o=t.querySelector(".sa-error-container");d.removeClass(o,"show")},u.disableButtons=c.disableButtons=function(){var e=p.getModal(),t=e.querySelector("button.confirm"),n=e.querySelector("button.cancel");t.disabled=!0,n.disabled=!0},u.enableButtons=c.enableButtons=function(){var e=p.getModal(),t=e.querySelector("button.confirm"),n=e.querySelector("button.cancel");t.disabled=!1,n.disabled=!1},"undefined"!=typeof e?e.sweetAlert=e.swal=u:f.logStr("SweetAlert is a frontend module!"),a.exports=r["default"]},{"./modules/default-params":2,"./modules/handle-click":3,"./modules/handle-dom":4,"./modules/handle-key":5,"./modules/handle-swal-dom":6,"./modules/set-params":8,"./modules/utils":9}],2:[function(e,t,n){Object.defineProperty(n,"__esModule",{value:!0});var o={title:"",text:"",type:null,allowOutsideClick:!1,showConfirmButton:!0,showCancelButton:!1,closeOnConfirm:!0,closeOnCancel:!0,confirmButtonText:"OK",confirmButtonColor:"#8CD4F5",cancelButtonText:"取消",imageUrl:null,imageSize:null,timer:null,customClass:"",html:!1,animation:!0,allowEscapeKey:!0,inputType:"text",inputPlaceholder:"",inputValue:"",showLoaderOnConfirm:!1};n["default"]=o,t.exports=n["default"]},{}],3:[function(t,n,o){Object.defineProperty(o,"__esModule",{value:!0});var a=t("./utils"),r=(t("./handle-swal-dom"),t("./handle-dom")),s=function(t,n,o){function s(e){m}var u,c,d,f=t||e.event,p=f.target||f.srcElement,m=-1!==p.className.indexOf("confirm"),v=-1!==p.className.indexOf("sweet-overlay"),y=r.hasClass(o,"visible"),h=n.doneFunction&&"true"===o.getAttribute("data-has-done-function");switch(m&&n.confirmButtonColor&&(u=n.confirmButtonColor,c=a.colorLuminance(u,-.04),d=a.colorLuminance(u,-.14)),f.type){case"mouseover":s(c);break;case"mouseout":s(u);break;case"mousedown":s(d);break;case"mouseup":s(c);break;case"focus":var b=o.querySelector("button.confirm"),g=o.querySelector("button.cancel");break;case"click":var w=o===p,C=r.isDescendant(o,p);if(!w&&!C&&y&&!n.allowOutsideClick)break;m&&h&&y?l(o,n):h&&y||v?i(o,n):r.isDescendant(o,p)&&"BUTTON"===p.tagName&&sweetAlert.close()}},l=function(e,t){var n=!0;r.hasClass(e,"show-input")&&(n=e.querySelector("input").value,n||(n="")),t.doneFunction(n),t.closeOnConfirm&&sweetAlert.close(),t.showLoaderOnConfirm&&sweetAlert.disableButtons()},i=function(e,t){var n=String(t.doneFunction).replace(/\s/g,""),o="function("===n.substring(0,9)&&")"!==n.substring(9,10);o&&t.doneFunction(!1),t.closeOnCancel&&sweetAlert.close()};o["default"]={handleButton:s,handleConfirm:l,handleCancel:i},n.exports=o["default"]},{"./handle-dom":4,"./handle-swal-dom":6,"./utils":9}],4:[function(n,o,a){Object.defineProperty(a,"__esModule",{value:!0});var r=function(e,t){return new RegExp(" "+t+" ").test(" "+e.className+" ")},s=function(e,t){r(e,t)||(e.className+=" "+t)},l=function(e,t){var n=" "+e.className.replace(/[\t\r\n]/g," ")+" ";if(r(e,t)){for(;n.indexOf(" "+t+" ")>=0;)n=n.replace(" "+t+" "," ");e.className=n.replace(/^\s+|\s+$/g,"")}},i=function(e){var n=t.createElement("div");return n.appendChild(t.createTextNode(e)),n.innerHTML},u=function(e){e.style.opacity="",e.style.display="block"},c=function(e){if(e&&!e.length)return u(e);for(var t=0;t<e.length;++t)u(e[t])},d=function(e){e.style.opacity="",e.style.display="none"},f=function(e){if(e&&!e.length)return d(e);for(var t=0;t<e.length;++t)d(e[t])},p=function(e,t){for(var n=t.parentNode;null!==n;){if(n===e)return!0;n=n.parentNode}return!1},m=function(e){e.style.left="-9999px",e.style.display="block";var t,n=e.clientHeight;return t="undefined"!=typeof getComputedStyle?parseInt(getComputedStyle(e).getPropertyValue("padding-top"),10):parseInt(e.currentStyle.padding),e.style.left="",e.style.display="none","-"+parseInt((n+t)/2)+"px"},v=function(e,t){if(+e.style.opacity<1){t=t||16,e.style.opacity=0,e.style.display="block";var n=+new Date,o=function(e){function t(){return e.apply(this,arguments)}return t.toString=function(){return e.toString()},t}(function(){e.style.opacity=+e.style.opacity+(new Date-n)/100,n=+new Date,+e.style.opacity<1&&setTimeout(o,t)});o()}e.style.display="block"},y=function(e,t){t=t||16,e.style.opacity=1;var n=+new Date,o=function(e){function t(){return e.apply(this,arguments)}return t.toString=function(){return e.toString()},t}(function(){e.style.opacity=+e.style.opacity-(new Date-n)/100,n=+new Date,+e.style.opacity>0?setTimeout(o,t):e.style.display="none"});o()},h=function(n){if("function"==typeof MouseEvent){var o=new MouseEvent("click",{view:e,bubbles:!1,cancelable:!0});n.dispatchEvent(o)}else if(t.createEvent){var a=t.createEvent("MouseEvents");a.initEvent("click",!1,!1),n.dispatchEvent(a)}else t.createEventObject?n.fireEvent("onclick"):"function"==typeof n.onclick&&n.onclick()},b=function(t){"function"==typeof t.stopPropagation?(t.stopPropagation(),t.preventDefault()):e.event&&e.event.hasOwnProperty("cancelBubble")&&(e.event.cancelBubble=!0)};a.hasClass=r,a.addClass=s,a.removeClass=l,a.escapeHtml=i,a._show=u,a.show=c,a._hide=d,a.hide=f,a.isDescendant=p,a.getTopMargin=m,a.fadeIn=v,a.fadeOut=y,a.fireClick=h,a.stopEventPropagation=b},{}],5:[function(t,o,a){Object.defineProperty(a,"__esModule",{value:!0});var r=t("./handle-dom"),s=t("./handle-swal-dom"),l=function(t,o,a){var l=t||e.event,i=l.keyCode||l.which,u=a.querySelector("button.confirm"),c=a.querySelector("button.cancel"),d=a.querySelectorAll("button[tabindex]");if(-1!==[9,13,32,27].indexOf(i)){for(var f=l.target||l.srcElement,p=-1,m=0;m<d.length;m++)if(f===d[m]){p=m;break}9===i?(f=-1===p?u:p===d.length-1?d[0]:d[p+1],r.stopEventPropagation(l),f.focus(),o.confirmButtonColor&&s.setFocusStyle(f,o.confirmButtonColor)):13===i?("INPUT"===f.tagName&&(f=u,u.focus()),f=-1===p?u:n):27===i&&o.allowEscapeKey===!0?(f=c,r.fireClick(f,l)):f=n}};a["default"]=l,o.exports=a["default"]},{"./handle-dom":4,"./handle-swal-dom":6}],6:[function(n,o,a){var r=function(e){return e&&e.__esModule?e:{"default":e}};Object.defineProperty(a,"__esModule",{value:!0});var s=n("./utils"),l=n("./handle-dom"),i=n("./default-params"),u=r(i),c=n("./injected-html"),d=r(c),f=".sweet-alert",p=".sweet-overlay",m=function(){var e=t.createElement("div");for(e.innerHTML=d["default"];e.firstChild;)t.body.appendChild(e.firstChild)},v=function(e){function t(){return e.apply(this,arguments)}return t.toString=function(){return e.toString()},t}(function(){var e=t.querySelector(f);return e||(m(),e=v()),e}),y=function(){var e=v();return e?e.querySelector("input"):void 0},h=function(){return t.querySelector(p)},b=function(e,t){var n=s.hexToRgb(t);},g=function(n){var o=v();l.fadeIn(h(),10),l.show(o),l.addClass(o,"showSweetAlert"),l.removeClass(o,"hideSweetAlert"),e.previousActiveElement=t.activeElement;var a=o.querySelector("button.confirm");a.focus(),setTimeout(function(){l.addClass(o,"visible")},500);var r=o.getAttribute("data-timer");if("null"!==r&&""!==r){var s=n;o.timeout=setTimeout(function(){var e=(s||null)&&"true"===o.getAttribute("data-has-done-function");e?s(null):sweetAlert.close()},r)}},w=function(){var e=v(),t=y();l.removeClass(e,"show-input"),t.value=u["default"].inputValue,t.setAttribute("type",u["default"].inputType),t.setAttribute("placeholder",u["default"].inputPlaceholder),C()},C=function(e){if(e&&13===e.keyCode)return!1;var t=v(),n=t.querySelector(".sa-input-error");l.removeClass(n,"show");var o=t.querySelector(".sa-error-container");l.removeClass(o,"show")},S=function(){var e=v();e.style.marginTop=l.getTopMargin(v())};a.sweetAlertInitialize=m,a.getModal=v,a.getOverlay=h,a.getInput=y,a.setFocusStyle=b,a.openModal=g,a.resetInput=w,a.resetInputError=C,a.fixVerticalPosition=S},{"./default-params":2,"./handle-dom":4,"./injected-html":7,"./utils":9}],7:[function(e,t,n){Object.defineProperty(n,"__esModule",{value:!0});var o='<div class="sweet-overlay" tabIndex="-1"></div><div class="sweet-alert"><div class="sa-icon sa-error">\n      <span class="sa-x-mark">\n        <span class="sa-line sa-left"></span>\n        <span class="sa-line sa-right"></span>\n      </span>\n    </div><div class="sa-icon sa-warning">\n      <span class="sa-body"></span>\n      <span class="sa-dot"></span>\n    </div><div class="sa-icon sa-info"></div><div class="sa-icon sa-success">\n      <span class="sa-line sa-tip"></span>\n      <span class="sa-line sa-long"></span>\n\n      <div class="sa-placeholder"></div>\n      <div class="sa-fix"></div>\n    </div><div class="sa-icon sa-custom"></div><h2>Title</h2>\n    <p>Text</p>\n    <fieldset>\n      <input type="text" tabIndex="3" />\n      <div class="sa-input-error"></div>\n    </fieldset><div class="sa-error-container">\n      <div class="icon">!</div>\n      <p>Not valid!</p>\n    </div><div class="sa-button-container">\n      <button class="cancel" tabIndex="2">取消</button>\n      <div class="sa-confirm-button-container">\n        <button class="confirm" tabIndex="1">OK</button><div class="la-ball-fall">\n          <div></div>\n          <div></div>\n          <div></div>\n        </div>\n      </div>\n    </div></div>';n["default"]=o,t.exports=n["default"]},{}],8:[function(e,t,o){Object.defineProperty(o,"__esModule",{value:!0});var a=e("./utils"),r=e("./handle-swal-dom"),s=e("./handle-dom"),l=["error","warning","info","success","input","prompt"],i=function(e){var t=r.getModal(),o=t.querySelector("h2"),i=t.querySelector("p"),u=t.querySelector("button.cancel"),c=t.querySelector("button.confirm");if(o.innerHTML=e.html?e.title:s.escapeHtml(e.title).split("\n").join("<br>"),i.innerHTML=e.html?e.text:s.escapeHtml(e.text||"").split("\n").join("<br>"),e.text&&s.show(i),e.customClass)s.addClass(t,e.customClass),t.setAttribute("data-custom-class",e.customClass);else{var d=t.getAttribute("data-custom-class");s.removeClass(t,d),t.setAttribute("data-custom-class","")}if(s.hide(t.querySelectorAll(".sa-icon")),e.type&&!a.isIE8()){var f=function(){for(var o=!1,a=0;a<l.length;a++)if(e.type===l[a]){o=!0;break}if(!o)return logStr("Unknown alert type: "+e.type),{v:!1};var i=["success","error","warning","info"],u=n;-1!==i.indexOf(e.type)&&(u=t.querySelector(".sa-icon.sa-"+e.type),s.show(u));var c=r.getInput();switch(e.type){case"success":s.addClass(u,"animate"),s.addClass(u.querySelector(".sa-tip"),"animateSuccessTip"),s.addClass(u.querySelector(".sa-long"),"animateSuccessLong");break;case"error":s.addClass(u,"animateErrorIcon"),s.addClass(u.querySelector(".sa-x-mark"),"animateXMark");break;case"warning":s.addClass(u,"pulseWarning"),s.addClass(u.querySelector(".sa-body"),"pulseWarningIns"),s.addClass(u.querySelector(".sa-dot"),"pulseWarningIns");break;case"input":case"prompt":c.setAttribute("type",e.inputType),c.value=e.inputValue,c.setAttribute("placeholder",e.inputPlaceholder),s.addClass(t,"show-input"),setTimeout(function(){c.focus(),c.addEventListener("keyup",swal.resetInputError)},400)}}();if("object"==typeof f)return f.v}if(e.imageUrl){var p=t.querySelector(".sa-icon.sa-custom");;var m=80,v=80;if(e.imageSize){var y=e.imageSize.toString().split("x"),h=y[0],b=y[1];h&&b?(m=h,v=b):logStr("Parameter imageSize expects value with format WIDTHxHEIGHT, got "+e.imageSize)}p.setAttribute("style",p.getAttribute("style")+"width:"+m+"px; height:"+v+"px")}t.setAttribute("data-has-cancel-button",e.showCancelButton),e.showCancelButton?u.style.display="inline-block":s.hide(u),t.setAttribute("data-has-confirm-button",e.showConfirmButton),e.showConfirmButton?c.style.display="inline-block":s.hide(c),e.cancelButtonText&&(u.innerHTML=s.escapeHtml(e.cancelButtonText)),e.confirmButtonText&&(c.innerHTML=s.escapeHtml(e.confirmButtonText)),t.setAttribute("data-allow-outside-click",e.allowOutsideClick);var g=e.doneFunction?!0:!1;t.setAttribute("data-has-done-function",g),e.animation?"string"==typeof e.animation?t.setAttribute("data-animation",e.animation):t.setAttribute("data-animation","pop"):t.setAttribute("data-animation","none"),t.setAttribute("data-timer",e.timer)};o["default"]=i,t.exports=o["default"]},{"./handle-dom":4,"./handle-swal-dom":6,"./utils":9}],9:[function(t,n,o){Object.defineProperty(o,"__esModule",{value:!0});var a=function(e,t){for(var n in t)t.hasOwnProperty(n)&&(e[n]=t[n]);return e},r=function(e){var t=/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(e);return t?parseInt(t[1],16)+", "+parseInt(t[2],16)+", "+parseInt(t[3],16):null},s=function(){return e.attachEvent&&!e.addEventListener},l=function(t){e.console&&e.console.log("SweetAlert: "+t)},i=function(e,t){e=String(e).replace(/[^0-9a-f]/gi,""),e.length<6&&(e=e[0]+e[0]+e[1]+e[1]+e[2]+e[2]),t=t||0;var n,o,a="#";for(o=0;3>o;o++)n=parseInt(e.substr(2*o,2),16),n=Math.round(Math.min(Math.max(0,n+n*t),255)).toString(16),a+=("00"+n).substr(n.length);return a};o.extend=a,o.hexToRgb=r,o.isIE8=s,o.logStr=l,o.colorLuminance=i},{}]},{},[1]),"function"==typeof define&&define.amd?define(function(){return sweetAlert}):"undefined"!=typeof module&&module.exports&&(module.exports=sweetAlert)}(window,document);






/*!
 * ZUI - v1.4.0 - 2016-01-26
 * http://zui.sexy
 * GitHub: https://github.com/easysoft/zui.git 
 * Copyright (c) 2016 cnezsoft.com; Licensed MIT
 */

/* Some code copy from Bootstrap v3.0.0 by @fat and @mdo. (Copyright 2013 Twitter, Inc. Licensed under http://www.apache.org/licenses/)*/

/* ========================================================================
 * ZUI: jquery.extensions.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function($, window) {
    'use strict';

    /* Check jquery */
    if(typeof($) === 'undefined') throw new Error('ZUI requires jQuery');

    // ZUI shared object
    if(!$.zui) $.zui = function(obj) {
        if($.isPlainObject(obj)) {
            $.extend($.zui, obj);
        }
    };

    var lastUuidAmend = 0;
    $.zui({
        uuid: function() {
            return(new Date()).getTime() * 1000 + (lastUuidAmend++) % 1000;
        },

        callEvent: function(func, event, proxy) {
            if($.isFunction(func)) {
                if(proxy !== undefined) {
                    func = $.proxy(func, proxy);
                }
                var result = func(event);
                if(event) event.result = result;
                return !(result !== undefined && (!result));
            }
            return 1;
        },

        clientLang: function() {
            var lang;
            var config = window.config;
            if(typeof(config) != 'undefined' && config.clientLang) {
                lang = config.clientLang;
            } else {
                var hl = $('html').attr('lang');
                lang = hl ? hl : (navigator.userLanguage || navigator.userLanguage || 'zh_cn');
            }
            return lang.replace('-', '_').toLowerCase();
        }
    });

    $.fn.callEvent = function(name, event, model) {
        var $this = $(this);
        var dotIndex = name.indexOf('.zui.');
        var shortName = name;
        if(dotIndex < 0 && model && model.name) {
            name += '.' + model.name;
        } else {
            shortName = name.substring(0, dotIndex);
        }
        var e = $.Event(name, event);

        if((model === undefined) && dotIndex > 0) {
            model = $this.data(name.substring(dotIndex + 1));
        }

        if(model && model.options) {
            var func = model.options[shortName];
            if($.isFunction(func)) {
                $.zui.callEvent(model.options[shortName], e, model);
            }
        }
        return e;
    };
}(jQuery, window));



+ function($) {
    'use strict';

    // CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
    // ============================================================

    function transitionEnd() {
        var el = document.createElement('bootstrap')

        var transEndEventNames = {
            WebkitTransition: 'webkitTransitionEnd',
            MozTransition: 'transitionend',
            OTransition: 'oTransitionEnd otransitionend',
            transition: 'transitionend'
        }

        for(var name in transEndEventNames) {
            if(el.style[name] !== undefined) {
                return {
                    end: transEndEventNames[name]
                }
            }
        }

        return false // explicit for ie8 (  ._.)
    }

    // http://blog.alexmaccaw.com/css-transitions
    $.fn.emulateTransitionEnd = function(duration) {
        var called = false
        var $el = this
        $(this).one('bsTransitionEnd', function() {
            called = true
        })
        var callback = function() {
            if(!called) $($el).trigger($.support.transition.end)
        }
        setTimeout(callback, duration)
        return this
    }

    $(function() {
        $.support.transition = transitionEnd()

        if(!$.support.transition) return

        $.event.special.bsTransitionEnd = {
            bindType: $.support.transition.end,
            delegateType: $.support.transition.end,
            handle: function(e) {
                if($(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
            }
        }
    })

}(jQuery);

/* ========================================================================
 * ZUI: string.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function() {
    'use strict';

    if(!String.prototype.format) {
        String.prototype.format = function(args) {
            var result = this;
            if(arguments.length > 0) {
                var reg;
                if(arguments.length == 1 && typeof(args) == "object") {
                    for(var key in args) {
                        if(args[key] !== undefined) {
                            reg = new RegExp("({" + key + "})", "g");
                            result = result.replace(reg, args[key]);
                        }
                    }
                } else {
                    for(var i = 0; i < arguments.length; i++) {
                        if(arguments[i] !== undefined) {
                            reg = new RegExp("({[" + i + "]})", "g");
                            result = result.replace(reg, arguments[i]);
                        }
                    }
                }
            }
            return result;
        };
    }

    /**
     * Judge the string is a integer number
     *
     * @access public
     * @return bool
     */
    if(!String.prototype.isNum) {
        String.prototype.isNum = function(s) {
            if(s !== null) {
                var r, re;
                re = /\d*/i;
                r = s.match(re);
                return(r == s) ? true : false;
            }
            return false;
        };
    }

})();







/* ========================================================================
 * Bootstrap: modal.js v3.2.0
 * http://getbootstrap.com/javascript/#modals
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ========================================================================
 * Updates in ZUI：
 * 1. changed event namespace to *.zui.modal
 * 2. added position option to ajust poisition of modal
 * 3. added event 'escaping.zui.modal' with an param 'esc' to judge the esc
 *    key down
 * 4. get moveable options value from '.modal-moveable' on '.modal-dialog'
 * 5. add setMoveable method to make modal dialog moveable
 * ======================================================================== */

+ function($) {
    'use strict';

    // MODAL CLASS DEFINITION
    // ======================

    var zuiname = 'zui.modal'
    var Modal = function(element, options) {
        this.options = options
        this.$body = $(document.body)
        this.$element = $(element)
        this.$backdrop =
            this.isShown = null
        this.scrollbarWidth = 0

        if(typeof this.options.moveable === 'undefined') {
            this.options.moveable = this.$element.hasClass('modal-moveable');
        }

        if(this.options.remote) {
            this.$element
                .find('.modal-content')
                .load(this.options.remote, $.proxy(function() {
                    this.$element.trigger('loaded.' + zuiname)
                }, this))
        }
    }

    Modal.VERSION = '3.2.0'

    Modal.TRANSITION_DURATION = 300
    Modal.BACKDROP_TRANSITION_DURATION = 150

    Modal.DEFAULTS = {
        backdrop: true,
        keyboard: true,
        show: true,
        // rememberPos: false,
        // moveable: false,
        position: 'fit' // 'center' or '40px' or '10%'
    }

    Modal.prototype.toggle = function(_relatedTarget, position) {
        return this.isShown ? this.hide() : this.show(_relatedTarget, position)
    }

    Modal.prototype.ajustPosition = function(position) {
        if(typeof position === 'undefined') position = this.options.position;
        if(typeof position === 'undefined') return;
        var $dialog = this.$element.find('.modal-dialog');
        // if($dialog.hasClass('modal-dragged')) return;

        var half = Math.max(0, ($(window).height() - $dialog.outerHeight()) / 2);
        var topPos = position == 'fit' ? (half * 2 / 3) : (position == 'center' ? half : position);
        if($dialog.hasClass('modal-moveable')) {
            var pos = null;
            if(this.options.rememberPos) {
                if(this.options.rememberPos === true) {
                    pos = this.$element.data('modal-pos');
                } else if($.zui.store) {
                    pos = $.zui.store.pageGet(zuiname + '.rememberPos');
                }
            }
            if(!pos) {
                pos = {
                    left: Math.max(0, ($(window).width() - $dialog.outerWidth()) / 2),
                    top: topPos
                };
            }
            $dialog.css(pos);
        } else {
            $dialog.css('margin-top', topPos);
        }
    }

    Modal.prototype.setMoveale = function() {
        var that = this;
        var options = that.options;
        var $dialog = that.$element.find('.modal-dialog').removeClass('modal-dragged');
        $dialog.toggleClass('modal-moveable', options.moveable);

        if(!that.$element.data('modal-moveable-setup')) {
            $dialog.draggable({
                container: that.$element,
                handle: '.modal-header',
                before: function() {
                    $dialog.css('margin-top', '').addClass('modal-dragged');
                },
                finish: function(e) {
                    if(options.rememberPos) {
                        that.$element.data('modal-pos', e.pos);
                        if($.zui.store && options.rememberPos !== true) {
                            $.zui.store.pageSet(zuiname + '.rememberPos', e.pos);
                        }
                    }
                }
            });
        }
    }

    Modal.prototype.show = function(_relatedTarget, position) {
        var that = this
        var e = $.Event('show.' + zuiname, {
            relatedTarget: _relatedTarget
        })

        that.$element.trigger(e)

        if(that.isShown || e.isDefaultPrevented()) return

        that.isShown = true

        if(that.options.moveable) that.setMoveale();

        that.checkScrollbar()
        that.$body.addClass('modal-open')

        that.setScrollbar()
        that.escape()

        that.$element.on('click.dismiss.' + zuiname, '[data-dismiss="modal"]', $.proxy(that.hide, that))

        that.backdrop(function() {
            var transition = $.support.transition && that.$element.hasClass('fade')

            if(!that.$element.parent().length) {
                that.$element.appendTo(that.$body) // don't move modals dom position
            }

            that.$element
                .show()
                .scrollTop(0)

            if(transition) {
                that.$element[0].offsetWidth // force reflow
            }

            that.$element
                .addClass('in')
                .attr('aria-hidden', false)

            that.ajustPosition(position);

            that.enforceFocus()

            var e = $.Event('shown.' + zuiname, {
                relatedTarget: _relatedTarget
            })

            transition ?
                that.$element.find('.modal-dialog') // wait for modal to slide in
                .one('bsTransitionEnd', function() {
                    that.$element.trigger('focus').trigger(e)
                })
                .emulateTransitionEnd(Modal.TRANSITION_DURATION) :
                that.$element.trigger('focus').trigger(e)
        })
    }

    Modal.prototype.hide = function(e) {
 
        if(e) e.preventDefault()

        e = $.Event('hide.' + zuiname)

        this.$element.trigger(e)

        if(!this.isShown || e.isDefaultPrevented()) return

        this.isShown = false

        this.$body.removeClass('modal-open')

        this.resetScrollbar()
        this.escape()

        $(document).off('focusin.' + zuiname)

        this.$element
            .removeClass('in')
            .attr('aria-hidden', true)
            .off('click.dismiss.' + zuiname)

        $.support.transition && this.$element.hasClass('fade') ?
            this.$element
            .one('bsTransitionEnd', $.proxy(this.hideModal, this))
            .emulateTransitionEnd(Modal.TRANSITION_DURATION) :
            this.hideModal()
    }

    Modal.prototype.enforceFocus = function() {
        $(document)
            .off('focusin.' + zuiname) // guard against infinite focus loop
            .on('focusin.' + zuiname, $.proxy(function(e) {
                if(this.$element[0] !== e.target && !this.$element.has(e.target).length) {
                    this.$element.trigger('focus')
                }
            }, this))
    }

    Modal.prototype.escape = function() {
        if(this.isShown && this.options.keyboard) {
            $(document).on('keydown.dismiss.' + zuiname, $.proxy(function(e) {
                if(e.which == 27) {
                    var et = $.Event('escaping.' + zuiname)
                    var result = this.$element.triggerHandler(et, 'esc')
                    if(result != undefined && (!result)) return
                    this.hide()
                }
            }, this))
        } else if(!this.isShown) {
            $(document).off('keydown.dismiss.' + zuiname)
        }
    }

    Modal.prototype.hideModal = function() {
        var that = this
        this.$element.hide()
        this.backdrop(function() {
            that.$element.trigger('hidden.' + zuiname)
        })
    }

    Modal.prototype.removeBackdrop = function() {
        this.$backdrop && this.$backdrop.remove()
        this.$backdrop = null
    }

    Modal.prototype.backdrop = function(callback) {
        var that = this
        var animate = this.$element.hasClass('fade') ? 'fade' : ''

        if(this.isShown && this.options.backdrop) {
            var doAnimate = $.support.transition && animate

            this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
                .appendTo(this.$body)

            this.$element.on('mousedown.dismiss.' + zuiname, $.proxy(function(e) {
                if(e.target !== e.currentTarget) return
                this.options.backdrop == 'static' ? this.$element[0].focus.call(this.$element[0]) : this.hide.call(this)
            }, this))

            if(doAnimate) this.$backdrop[0].offsetWidth // force reflow

            this.$backdrop.addClass('in')

            if(!callback) return

            doAnimate ?
                this.$backdrop
                .one('bsTransitionEnd', callback)
                .emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
                callback()

        } else if(!this.isShown && this.$backdrop) {
            this.$backdrop.removeClass('in')

            var callbackRemove = function() {
                that.removeBackdrop()
                callback && callback()
            }
            $.support.transition && this.$element.hasClass('fade') ?
                this.$backdrop
                .one('bsTransitionEnd', callbackRemove)
                .emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
                callbackRemove()

        } else if(callback) {
            callback()
        }
    }

    Modal.prototype.checkScrollbar = function() {
        if(document.body.clientWidth >= window.innerWidth) return
        this.scrollbarWidth = this.scrollbarWidth || this.measureScrollbar()
    }

    Modal.prototype.setScrollbar = function() {
        var bodyPad = parseInt((this.$body.css('padding-right') || 0), 10)
        if(this.scrollbarWidth) this.$body.css('padding-right', bodyPad + this.scrollbarWidth)
    }

    Modal.prototype.resetScrollbar = function() {
        this.$body.css('padding-right', '')
    }

    Modal.prototype.measureScrollbar = function() { // thx walsh
        var scrollDiv = document.createElement('div')
        scrollDiv.className = 'modal-scrollbar-measure'
        this.$body.append(scrollDiv)
        var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
        this.$body[0].removeChild(scrollDiv)
        return scrollbarWidth
    }


    // MODAL PLUGIN DEFINITION
    // =======================

    function Plugin(option, _relatedTarget, position) {
        return this.each(function() {
            var $this = $(this)
            var data = $this.data(zuiname)
            var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option == 'object' && option)

            if(!data) $this.data(zuiname, (data = new Modal(this, options)))
            if(typeof option == 'string') data[option](_relatedTarget, position)
            else if(options.show) data.show(_relatedTarget, position)
        })
    }

    var old = $.fn.modal

    $.fn.modal = Plugin
    $.fn.modal.Constructor = Modal


    // MODAL NO CONFLICT
    // =================

    $.fn.modal.noConflict = function() {
        $.fn.modal = old
        return this
    }


    // MODAL DATA-API
    // ==============

    $(document).on('click.' + zuiname + '.data-api', '[data-toggle="modal"]', function(e) {
        var $this = $(this)
        var href = $this.attr('href')
        var $target = null
        try {
            // strip for ie7
            $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
        } catch(ex) {
            return
        }
        if(!$target.length) return;
        var option = $target.data(zuiname) ? 'toggle' : $.extend({
            remote: !/#/.test(href) && href
        }, $target.data(), $this.data())

        if($this.is('a')) e.preventDefault()

        $target.one('show.' + zuiname, function(showEvent) {
            // only register focus restorer if modal will actually get shown
            if(showEvent.isDefaultPrevented()) return
            $target.one('hidden.' + zuiname, function() {
                $this.is(':visible') && $this.trigger('focus')
            })
        })
        Plugin.call($target, option, this, $this.data('position'))
    })

}(jQuery);


/* ========================================================================
 * ZUI: modal.trigger.js v1.2.0
 * http://zui.sexy/docs/javascript.html#modals
 * Licensed under MIT
 * ======================================================================== */


(function($, window) {
    'use strict';

    if(!$.fn.modal) throw new Error('Modal trigger requires modal.js');

    var NAME = 'zui.modaltrigger',
        STR_AJAX = 'ajax',
        ZUI_MODAL = '.zui.modal',
        STR_STRING = 'string';

    // MODAL TRIGGER CLASS DEFINITION
    // ======================
    var ModalTrigger = function(options, $trigger) {
        options = $.extend({}, ModalTrigger.DEFAULTS, $.ModalTriggerDefaults, $trigger ? $trigger.data() : null, options);
        this.isShown;
        this.$trigger = $trigger;
        this.options = options;
        this.id = $.zui.uuid();

        // todo: handle when: options.show = true
    };

    ModalTrigger.DEFAULTS = {
        type: 'custom',
        // width: null, // number, css definition
        // size: null, // 'md', 'sm', 'lg', 'fullscreen'
        height: 'auto',
        // icon: null,
        name: 'triggerModal',
        fade: true,
        position: 'fit',
        showHeader: true,
        delay: 0,
        // iframeBodyClass: '',
        // onlyIncreaseHeight: false,
        // moveable: false,
        // rememberPos: false,
        backdrop: true,
        keyboard: true,
        waittime: 0,
        loadingIcon: 'uk-icon-spinner-indicator'
    };

    ModalTrigger.prototype.init = function(options) {
		OpenLoad();
        var that = this;
        if(options.url) {
            if(!options.type || (options.type != STR_AJAX && options.type != 'iframe')) {
                options.type = STR_AJAX;
            }
        }
        if(options.remote) {
            options.type = STR_AJAX;
            if(typeof options.remote === STR_STRING) options.url = options.remote;
        } else if(options.iframe) {
            options.type = 'iframe';
            if(typeof options.iframe === STR_STRING) options.url = options.iframe;
        } else if(options.custom) {
            options.type = 'custom';
            if(typeof options.custom === STR_STRING) {
                var $doms;
                try {
                    $doms = $(options.custom);
                } catch(e) {}

                if($doms && $doms.length) {
                    options.custom = $doms;
                } else if($.isFunction(window[options.custom])) {
                    options.custom = window[options.custom];
                }
            }
        }

        var $modal = $('#' + options.name);
        if($modal.length) {
            if(!that.isShown) $modal.off(ZUI_MODAL);
            $modal.remove();
        }
        $modal = $('<div id="' + options.name + '" class="uk-modal modal modal-trigger uk-open">' + (typeof options.loadingIcon === 'string' && options.loadingIcon.indexOf('uk-icon-') === 0 ? ('<div class="icon uk-icon-spin loader ' + options.loadingIcon + '"></div>') : options.loadingIcon) + '<div class="modal-dialog uk-modal-dialog"><div class="modal-content"><div class="modal-header uk-modal-header"><button class="uk-modal-close uk-close" data-dismiss="modal"></button><h4 class="modal-title"><i class="modal-icon"></i> <span class="modal-title-name"></span></h4></div><div class="modal-body"></div></div></div></div>').appendTo('body').data(NAME, that);

        var bindEvent = function(optonName, eventName) {
            var handleFunc = options[optonName];
            if($.isFunction(handleFunc)) $modal.on(eventName + ZUI_MODAL, handleFunc);
        };
        bindEvent('onShow', 'show');
        bindEvent('shown', 'shown');
        bindEvent('onHide', 'hide');
        bindEvent('hidden', 'hidden');
        bindEvent('loaded', 'loaded');

        $modal.on('shown' + ZUI_MODAL, function() {
            that.isShown = true;
        }).on('hidden' + ZUI_MODAL, function() {
            that.isShown = false;
        });

        this.$modal = $modal;
        this.$dialog = $modal.find('.modal-dialog');

        if(options.mergeOptions) this.options = options;
    };

    ModalTrigger.prototype.show = function(option) {
        var options = $.extend({}, this.options, {url: this.$trigger ? (this.$trigger.attr('href') || this.$trigger.attr('data-url') || this.$trigger.data('url')) : this.options.url}, option);
        this.init(options);
        var that = this,
            $modal = this.$modal,
            $dialog = this.$dialog,
            custom = options.custom;
        var $body = $dialog.find('.modal-body').css('padding', ''),
            $header = $dialog.find('.modal-header'),
            $content = $dialog.find('.modal-content');

        $modal.toggleClass('fade', options.fade)
            .addClass(options.cssClass)
            .toggleClass('modal-loading', !this.isShown)
			 .toggleClass('modal-fullscreen', options.size === 'fullscreen');

        $dialog.toggleClass('modal-md', options.size === 'md')
            .toggleClass('modal-sm', options.size === 'sm')
            .toggleClass('modal-lg', options.size === 'lg')
           

        $header.toggle(options.showHeader);
        $header.find('.modal-icon').attr('class', 'modal-icon uk-icon-' + options.icon);
        $header.find('.modal-title-name').html(options.title || '');
        if(options.size && options.size === 'fullscreen') {
            options.width = '';
            options.height = '';
        }

        var resizeDialog = function() {
            clearTimeout(this.resizeTask);
            this.resizeTask = setTimeout(function() {
                that.ajustPosition();
            }, 100);
        };

        var readyToShow = function(delay, callback) {
            if(typeof delay === 'undefined') delay = options.delay;
            return setTimeout(function() {
                $dialog = $modal.find('.modal-dialog');
                if(options.width && options.width != 'auto') {
                    $dialog.css('width', options.width);
                }
                if(options.height && options.height != 'auto') {
                    $dialog.css('height', options.height);
                    if(options.type === 'iframe') $body.css('height', $dialog.height() - $header.outerHeight());
                }
                that.ajustPosition(options.position);
                $modal.removeClass('modal-loading');

                if(options.type != 'iframe') {
                    $dialog.off('resize.' + NAME).on('resize.' + NAME, resizeDialog);
                }

                callback && callback();
            }, delay);
        };

        if(options.type === 'custom' && custom) {
            if($.isFunction(custom)) {
                var customContent = custom({
                    modal: $modal,
                    options: options,
                    modalTrigger: that,
                    ready: readyToShow
                });
                if(typeof customContent === STR_STRING) {
                    $body.html(customContent);
                    readyToShow();
                }
            } else if(custom instanceof $) {
                $body.html($('<div>').append(custom.clone()).html());
                readyToShow();
            } else {
                $body.html(custom);
                readyToShow();
            }
        } else if(options.url) {
            var onLoadBroken = function() {
                var brokenContent = $modal.callEvent('broken' + ZUI_MODAL, that, that);
                if(brokenContent) {
                    $body.html(brokenContent);
                }
            };

            $modal.attr('ref', options.url);
            if(options.type === 'iframe') {
                $modal.addClass('modal-iframe');
                this.firstLoad = true;
                var iframeName = 'iframe-' + options.name;
                $header.detach();
                $body.detach();
                $content.empty().append($header).append($body);
                $body.css('padding', 0)
                    .html('<iframe id="' + iframeName + '" name="' + iframeName + '" src="' + options.url + '" frameborder="no"  allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"  allowtransparency="true" scrolling="auto" style="width: 100%; height: 100%; left: 0px;"></iframe>');

                if(options.waittime > 0) {
                    that.waitTimeout = readyToShow(options.waittime, onLoadBroken);
                }

                var frame = document.getElementById(iframeName);
                frame.onload = frame.onreadystatechange = function() {
                    if(that.firstLoad) $modal.addClass('modal-loading');
                    if(this.readyState && this.readyState != 'complete') return;
                    that.firstLoad = false;

                    if(options.waittime > 0) {
                        clearTimeout(that.waitTimeout);
                    }

                    try {
                        $modal.attr('ref', frame.contentWindow.location.href);
                        var frame$ = window.frames[iframeName].$;
                        if(frame$ && options.height === 'auto' && options.size != 'fullscreen') {
                            // todo: update iframe url to ref attribute
                            var $framebody = frame$('body').addClass('body-modal');
                            if(options.iframeBodyClass) $framebody.addClass(options.iframeBodyClass);
                            var ajustFrameSize = function(check) {
                                $modal.removeClass('fade');
                                var height = $framebody.outerHeight();
                                if(check === true && options.onlyIncreaseHeight) {
                                    height = Math.max(height, $body.data('minModalHeight') || 0);
                                    $body.data('minModalHeight', height);
                                }
                                $body.css('height', height);
                                if(options.fade) $modal.addClass('fade');
								CloseLoad();
                                readyToShow();
                            };

                            $modal.callEvent('loaded' + ZUI_MODAL, {
                                modalType: 'iframe',
                                jQuery: frame$
                            }, that);
							
                            setTimeout(ajustFrameSize, 100);
                            $framebody.off('resize.' + NAME).on('resize.' + NAME, resizeDialog);
                        }

                        frame$.extend({
							
                            closeModal: window.closeModal
                        });
					
                    } catch(e) {
						
                        readyToShow();
                    }
					
                };
            } else {
                $.get(options.url, function(data) {
						
						if (data.status === 0 || data.status === false) {
							Alert(data.info, 'error', true, data.url);
							CloseLoad();
							return false
						}
					
                    try {
                        var $data = $(data);
                        if($data.hasClass('modal-dialog')) {
                            $dialog.replaceWith($data);
                        } else if($data.hasClass('modal-content')) {
                            $dialog.find('.modal-content').replaceWith($data);
                        } else {
                            $body.wrapInner($data);
							
                        }
                    } catch(e) {
						
                        $modal.html(data);
                    }
                    $modal.callEvent('loaded' + ZUI_MODAL, {
                        modalType: STR_AJAX
                    }, that);
					CloseLoad();
                    readyToShow();
                }).error(onLoadBroken);
            }
        }

        $modal.modal({
            show: 'show',
            backdrop: options.backdrop,
            moveable: options.moveable,
            keyboard: options.keyboard
        });
    };

    ModalTrigger.prototype.close = function(callback, redirect) {
        if(callback || redirect) {
            this.$modal.on('hidden' + ZUI_MODAL, function() {
				CloseLoad();
                if($.isFunction(callback)) callback();

                if(typeof redirect === STR_STRING) {
                    if(redirect === 'this') window.location.reload();
                    else window.location = redirect;
                }
            });
        }
        this.$modal.modal('hide');
    };

    ModalTrigger.prototype.toggle = function(options) {
		
        if(this.isShown) this.close();
        else this.show(options);
		
    };

    ModalTrigger.prototype.ajustPosition = function(position) {
        this.$modal.modal('ajustPosition', position || this.options.position);
    };

    $.zui({
        ModalTrigger: ModalTrigger,
        modalTrigger: new ModalTrigger()
    });

    $.fn.modalTrigger = function(option, settings) {
        return $(this).each(function() {
            var $this = $(this);
            var data = $this.data(NAME),
                options = $.extend({
                    title: $this.attr('title') || $this.text(),
                    url: $this.attr('href'),
                    type: $this.hasClass('iframe') ? 'iframe' : ''
                }, $this.data(), $.isPlainObject(option) && option);
            if(!data) $this.data(NAME, (data = new ModalTrigger(options, $this)));
            if(typeof option == STR_STRING) data[option](settings);
            else if(options.show) data.show(settings);

            $this.on((options.trigger || 'click') + '.toggle.' + NAME, function(e) {
                data.toggle(options);
                if($this.is('a')) e.preventDefault();
            });
        });
    };

    var old = $.fn.modal;
    $.fn.modal = function(option, settings) {
        return $(this).each(function() {
            var $this = $(this);
            if($this.hasClass('modal')) old.call($this, option, settings);
            else $this.modalTrigger(option, settings);
        });
    };

    var getModal = function(modal) {
        var modalType = typeof(modal);
        if(modalType === 'undefined') {
            modal = $('.modal.modal-trigger');
        } else if(modalType === STR_STRING) {
            modal = $(modal);
        }
        if(modal && (modal instanceof $)) return modal;
        return null;
    };

    // callback, redirect, modal
    var closeModal = function(modal, callback, redirect) {
        if($.isFunction(modal)) {
            var oldModal = redirect;
            redirect = callback;
            callback = modal;
            modal = oldModal;
        }
        modal = getModal(modal);
        if(modal && modal.length) {
            modal.each(function() {
                $(this).data(NAME).close(callback, redirect);
            });
        }
    };

    var ajustModalPosition = function(position, modal) {
        modal = getModal(modal);
        if(modal && modal.length) {
            modal.modal('ajustPosition', position);
        }
    };

    $.zui({
        closeModal: closeModal,
        ajustModalPosition: ajustModalPosition
    });

    $(document).on('click.' + NAME + '.data-api', '[data-toggle="modal"]', function(e) {
        var $this = $(this);
        var href = $this.attr('href');
        var $target = null;
        try {
            $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, '')));
        } catch(ex) {}
        if(!$target || !$target.length) {
            if(!$this.data(NAME)) {
                $this.modalTrigger({
                    show: true,
                });
            } else {
                $this.trigger('.toggle.' + NAME);
            }
        }
        if($this.is('a')) {
            e.preventDefault();
        }
    });
}(window.jQuery, window));



/* ========================================================================
 * image.ready.js
 * http://www.planeart.cn/?p=1121
 * ========================================================================
 * @version 2011.05.27
 * @author  TangBin
 * ======================================================================== */


(function($) {
    'use strict';

    /**
     * Image ready
     * @param {String}  image url
     * @param {Function}  callback on image ready
     * @param {Function}  callback on image load
     * @param {Function}  callback on error
     * @example imgReady('image.png', function () {
        alert('size ready: width=' + this.width + '; height=' + this.height);
      });
     */
    $.zui.imgReady = (function() {
        var list = [],
            intervalId = null,

            // 用来执行队列
            tick = function() {
                var i = 0;
                for(; i < list.length; i++) {
                    list[i].end ? list.splice(i--, 1) : list[i]();
                }!list.length && stop();
            },

            // 停止所有定时器队列
            stop = function() {
                clearInterval(intervalId);
                intervalId = null;
            };

        return function(url, ready, load, error) {
            var onready, width, height, newWidth, newHeight,
                img = new Image();

            img.src = url;

            // 如果图片被缓存，则直接返回缓存数据
            if(img.complete) {
				CloseLoad();
                ready.call(img);
                load && load.call(img);
                return;
            }

            width = img.width;
            height = img.height;

            // 加载错误后的事件
            img.onerror = function() {
				CloseLoad();
                error && error.call(img);
                onready.end = true;
                img = img.onload = img.onerror = null;
            };

            // 图片尺寸就绪
            onready = function() {
                newWidth = img.width;
                newHeight = img.height;
                if(newWidth !== width || newHeight !== height ||
                    // 如果图片已经在其他地方加载可使用面积检测
                    newWidth * newHeight > 1024
                ) {
                    ready.call(img);
                    onready.end = true;
                }
            };
            onready();

            // 完全加载完毕的事件
            img.onload = function() {
				CloseLoad();
                // onload在定时器时间差范围内可能比onready快
                // 这里进行检查并保证onready优先执行
                !onready.end && onready();

                load && load.call(img);

                // IE gif动画会循环执行onload，置空onload即可
                img = img.onload = img.onerror = null;
            };

            // 加入队列中定期执行
            if(!onready.end) {
                list.push(onready);
                // 无论何时只允许出现一个定时器，减少浏览器性能损耗
                if(intervalId === null) intervalId = setInterval(tick, 40);
            } 
        };
    })();
}(jQuery));


/* ========================================================================
 * ZUI: lightbox.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function($, window, Math) {
    'use strict';

    if(!$.fn.modalTrigger) throw new Error('modal & modalTrigger requires for lightbox');
    if(!$.zui.imgReady) throw new Error('imgReady requires for lightbox');

    var Lightbox = function(element, options) {
        this.$ = $(element);
        this.options = this.getOptions(options);

        this.init();
    };

    Lightbox.DEFAULTS = {
        modalTeamplate: '<div class="uk-icon-spinner uk-icon-spin loader"></div><div class="modal-dialog"><button class="close" data-dismiss="modal" aria-hidden="true"><i class="uk-icon-uniE614"></i></button><button class="controller prev"><i class="icon uk-icon-chevron-left"></i></button><button class="controller next"><i class="icon uk-icon-chevron-right"></i></button><img class="lightbox-img" src="{image}" alt="" data-dismiss="modal" /><div class="caption"><div class="content">{caption}<div></div></div>'
    }; // default options

    Lightbox.prototype.getOptions = function(options) {
		
        var IMAGE = 'image';
        options = $.extend({}, Lightbox.DEFAULTS, this.$.data(), options);
        if(!options[IMAGE]) {
            options[IMAGE] = this.$.attr('src') || this.$.attr('href') || this.$.find('img').attr('src');
            this.$.data(IMAGE, options[IMAGE]);
        }
        return options;
    };

    Lightbox.prototype.init = function() {
	
        this.bindEvents();
    };

    Lightbox.prototype.initGroups = function() {
        var groups = this.$.data('groups');
        if(!groups) {
            groups = $('[data-toggle="lightbox"][data-group="' + this.options.group + '"], [data-lightbox-group="' + this.options.group + '"]');
            this.$.data('groups', groups);
            groups.each(function(index) {
                $(this).attr('data-group-index', index);
            });
        }
        this.groups = groups;
        this.groupIndex = parseInt(this.$.data('group-index'));
    };

    Lightbox.prototype.bindEvents = function() {
        var $e = this.$,
            that = this;
        var options = this.options;
        if(!options.image) return false;
        $e.modalTrigger({
            type: 'custom',
            name: 'lightboxModal',
            position: 'center',
            custom: function(e) {
                that.initGroups();
                var modal = e.modal,
                    groups = that.groups,
                    groupIndex = that.groupIndex;

                modal.addClass('modal-lightbox')
                    .html(options.modalTeamplate.format(options))
                    .toggleClass('lightbox-with-caption', typeof options.caption == 'string')
                    .removeClass('lightbox-full')
                    .data('group-index', groupIndex);
                var dialog = modal.find('.modal-dialog'),
                    winWidth = $(window).width();
                $.zui.imgReady(options.image, function() {
                    dialog.css({
                        width: Math.min(winWidth, this.width)
                    });
                    if(winWidth < (this.width + 30)) modal.addClass('lightbox-full');
					
                    e.ready();
                });

                modal.find('.prev').toggleClass('show', groups.filter('[data-group-index="' + (groupIndex - 1) + '"]').length > 0);
                modal.find('.next').toggleClass('show', groups.filter('[data-group-index="' + (groupIndex + 1) + '"]').length > 0);

                modal.find('.controller').click(function() {
                    var $this = $(this);
                    var id = modal.data('group-index') + ($this.hasClass('prev') ? -1 : 1);
                    var $e = groups.filter('[data-group-index="' + id + '"]');
                    if($e.length) {
                        var image = $e.data('image'),
                            caption = $e.data('caption');
                        modal.addClass('modal-loading')
                            .data('group-index', id)
                            .toggleClass('lightbox-with-caption', typeof caption == 'string')
                            .removeClass('lightbox-full');
                        modal.find('.lightbox-img').attr('src', image);
                        modal.find('.caption > .content').text(caption);
                        winWidth = $(window).width();
                        $.zui.imgReady(image, function() {
                            dialog.css({
                                width: Math.min(winWidth, this.width)
                            });
                            if(winWidth < (this.width + 30)) modal.addClass('lightbox-full');
							
                            e.ready();
                        });
                    }
                    modal.find('.prev').toggleClass('show', groups.filter('[data-group-index="' + (id - 1) + '"]').length > 0);
                    modal.find('.next').toggleClass('show', groups.filter('[data-group-index="' + (id + 1) + '"]').length > 0);
                    return false;
                });
            }
        });
    };

    $.fn.lightbox = function(option) {
		
        var defaultGroup = 'group' + (new Date()).getTime();
        return this.each(function() {
            var $this = $(this);

            var options = typeof option == 'object' && option;
            if(typeof options == 'object' && options.group) {
                $this.attr('data-lightbox-group', options.group);
            } else if($this.data('group')) {
                $this.attr('data-lightbox-group', $this.data('group'));
            } else {
                $this.attr('data-lightbox-group', defaultGroup);
            }
            $this.data('group', $this.data('lightbox-group'));

            var data = $this.data('zui.lightbox');
            if(!data) $this.data('zui.lightbox', (data = new Lightbox(this, options)));

            if(typeof option == 'string') data[option]();
        });
    };

    $.fn.lightbox.Constructor = Lightbox;
	
    $(function() {
        $('[data-toggle="lightbox"]').lightbox();
    });
}(jQuery, window, Math));


/* ========================================================================
 * ZUI: messager.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function($, window) {
    'use strict';

    var id = 0;
    var template = '<div class="messager messager-{type} {placement}" id="messager{id}" style="display:none"><div class="messager-content"></div><div class="messager-actions"><button type="button" class="close action">&times;</button></div></div>';
    var defaultOptions = {
        type: 'default',
        placement: 'top',
        time: 4000,
        parent: 'body',
        // clear: false,
        icon: null,
        close: true,
        fade: true,
        scale: true
    };
    var lastMessager;

    var Messager = function(message, options) {
        var that = this;
        that.id = id++;
        options = that.options = $.extend({}, defaultOptions, options);
        that.message = (options.icon ? '<i class="uk-icon-' + options.icon + ' icon"></i> ' : '') + message;

        that.$ = $(template.format(options)).toggleClass('fade', options.fade).toggleClass('scale', options.scale).attr('id', 'messager-' + that.id);
        if(!options.close) {
            that.$.find('.close').remove();
        } else {
            that.$.on('click', '.close', function() {
                that.hide();
            });
        }

        that.$.find('.messager-content').html(that.message);


        that.$.data('zui.messager', that);
    };

    Messager.prototype.show = function(message) {
        var that = this,
            options = this.options;

        if(lastMessager) {
            if(lastMessager.id == that.id) {
                that.$.removeClass('in');
            } else if(lastMessager.isShow) {
                lastMessager.hide();
            }
        }

        if(that.hiding) {
            clearTimeout(that.hiding);
            that.hiding = null;
        }

        if(message) {
            that.message = (options.icon ? '<i class="uk-icon-' + options.icon + ' icon"></i> ' : '') + message;
            that.$.find('.messager-content').html(that.message);
        }

        that.$.appendTo(options.parent).show();

        if(options.placement === 'top' || options.placement === 'bottom' || options.placement === 'center') {
            that.$.css('left', ($(window).width() - that.$.width() - 50) / 2);
        }


        if(options.placement === 'left' || options.placement === 'right' || options.placement === 'center') {
            that.$.css('top', ($(window).height() - that.$.height() - 50) / 2);
        }

        that.$.addClass('in');

        if(options.time) {
            that.hiding = setTimeout(function() {
                that.hide();
            }, options.time);
        }

        that.isShow = true;
        lastMessager = that;
    };

    Messager.prototype.hide = function() {
        var that = this;
        if(that.$.hasClass('in')) {
            that.$.removeClass('in');
            setTimeout(function() {
                that.$.remove();
            }, 200);
        }

        that.isShow = false;
    };

    var showMessage = function(message, options) {
        if(typeof options === 'string') {
            options = {
                type: options
            };
        }
        var msg = new Messager(message, options);
        msg.show();
        return msg;
    };

    var hideMessage = function() {
        $('.messager').each(function() {
            var msg = $(this).data('zui.messager');
            if(msg && msg.hide) msg.hide();
        });
    };

    var getOptions = function(options) {
        return(typeof options === 'string') ? {
            placement: options
        } : options;
    };

    $.zui({
        Messager: Messager,
        showMessager: showMessage,
        messager: {
            show: showMessage,
            hide: hideMessage,
            primary: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'primary'
                }, getOptions(options)));
            },
            success: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'success',
                    icon: 'ok-sign'
                }, getOptions(options)));
            },
            info: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'info',
                    icon: 'info-sign'
                }, getOptions(options)));
            },
            warning: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'warning',
                    icon: 'warning-sign'
                }, getOptions(options)));
            },
            danger: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'danger',
                    icon: 'exclamation-sign'
                }, getOptions(options)));
            },
            important: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'important'
                }, getOptions(options)));
            },
            special: function(message, options) {
                return showMessage(message, $.extend({
                    type: 'special'
                }, getOptions(options)));
            }
        }
    });
}(jQuery, window));

