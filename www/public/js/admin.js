$.fn.center=function() {
	return this.each(function() {
		var o=$(this);
		var offset=o.offset();
		var position=o.position();
		if(toggleFullScreen.full) top=0;
		else {
			var top=($(window).height()-o.outerHeight())/2;
			var top=($(window).height()-o.outerHeight())/2-(offset.top-position.top)+$(document).scrollTop();
			if(top<0) top=5;
		}
		var left=($(window).width()-o.outerWidth())/2-(offset.left-position.left);
		o.css({'left':left,'top':top});
	});
}

$.fn.byWindowSize=function(full) {
	var winWidth=$(window).width();
	return this.each(function() {
		var tmp=$(this);
		tmp[0].className=(full ? 'full' : '');
		tmp.width(winWidth/100*(full ? 99.7 : 95));
	});
}

$.adminDialog=function(o) {
	var o=$(o);
	var img=o.children('img');
	$.adminDialog.self=$('#_adminDialogBox');
	$('#_adminDialogBoxLoading').show();
	$('#_adminDialogBox ._adminHead span').html('<img src="'+img.attr('src')+'"> '+img.attr('title'));
	$.adminDialog.load(o.attr('href'));
	$('#_adminDialogBox ._adminHead').mousedown(function(e) {
		$.adminDialog.x=e.pageX;
		$.adminDialog.y=e.pageY;
		if($.adminDialog.isMove) return;
		var tmp=$.adminDialog.self.position();
		$.adminDialog.top=tmp.top;
		$.adminDialog.left=tmp.left;
		$.adminDialog.isMove=true;
		$(document).mouseup(function() {
			$(document).unbind('mouseup').unbind('mousemove');
			$.adminDialog.self.css('opacity',1);
			$.adminDialog.isMove=false;
		}).mousemove(function(e) {
			if(!$.adminDialog.isMove) return;
			$.adminDialog.self.css({'top':$.adminDialog.top+e.pageY-$.adminDialog.y});
			$.adminDialog.self.css({'left':$.adminDialog.left+e.pageX-$.adminDialog.x});
		});
		$.adminDialog.self.css('opacity',0.7);
	});
	$('._adminHead img',$.adminDialog.self).mouseover(function() {
		$.adminDialog.self.css('opacity',0.4);
	}).mouseout(function() {
		$.adminDialog.self.css('opacity',1);
	});
	var i1=o[0].href.indexOf('controller=')+11;
	var i2=o[0].href.indexOf('&',i1);
	var path=o[0].href.substring(i1,i2)+'/';
	var i1=o[0].href.indexOf('action=')+7;
	var i2=o[0].href.indexOf('&',i1);
	if(!i2) i2=900;
	path='&path='+path+o[0].href.substring(i1,i2);
	i1=$('._adminDialogBoxHelp',$.adminDialog.self).get(0);
	i2=i1.href.indexOf('path=');
	if(i2!=-1) i1=i1.href.substring(0,i1);
	i1.href=i1.href+path;
	return false;
}
$.adminDialog.load=function(url) {
	$.adminDialog.self.byWindowSize(false).fadeIn().center();
	$('#_adminDialogBox > iframe.container')[0].src=url;
}
$.adminDialog.afterLoad=function(h) {
	$('#_adminDialogBoxLoading').hide();
	$('#_adminDialogBox > iframe.container').height(h+26);
	$('#_adminDialogBox').center();
}

/* Рисует кнопки административного интерфейса */
function _adminElement() { jQuery(function() {
	$('._adminItem img').each(function() {
		var img=$(this); //<img>
		var a=img.parent(); //<a>
		var aPosition=a.position(); //позици <a>
		var aOffset=a.offset();
		var savedPosition=aOffset.left+'.'+aOffset.top;
//		var savedPosition=aPosition.left+'.'+aPosition.top;
		//"Сдвигает" кнопку вправо, если в этой точке есть другие кнопки
		if(isNaN(_adminElement.itemByPoint[savedPosition])) indexLeft=0; else {
			indexLeft=_adminElement.itemByPoint[savedPosition]+20;
		}
		if(indexLeft && !parseInt(img.attr('index'))) { //если это следующая группа в строке, то нужно её немного сдвинуть визуально
			indexLeft+=6;
		}
//_debug(img,savedPosition,indexLeft,15,15);
		_adminElement.itemByPoint[savedPosition]=indexLeft;
		img.css({
			'position':'absolute',
			'left':aPosition.left+indexLeft,
			'top':aPosition.top
		});
//_debug(o,s,indexTop,19,22);
	});
}); }
_adminElement.itemByPoint=[]; //содержит кол-во кнопок в одной точке (чтобы они не накладывались друг на друга)
setTimeout(_adminElement,300);
//delete _adminElement;
/*
function _debug(img,savedPosition,indexLeft,from,to) {
	if(!_debug.index) _debug.index=1; else _debug.index++;
	if(!from) from=1;
	if(!to) to=9999;
	if(_debug.index<from || _debug.index>to) return;
	var a=img.parent();
	var aPosition=a.position();
	var aOffset=a.offset();
	var container=a.parent();
	var offset=container.offset();
	var s='#'+_debug.index+' ICON: '+img.attr('title')+' ('+img[0].src.substr(40,img[0].src.length-27)+")\nLINK: "+img.parent()[0].href.substr(27);
	s+="\nParent Class: "+container[0].className;
//	s+="\nIndex TOP: "+indexTop;
	s+="\n<A> position: "+String(aPosition.left)+' x '+String(aPosition.top)+"\nContainer offset: "+String(offset.left)+' x '+String(offset.top)+"\nContainer padding: "+String(parseInt(container.css('padding-left')))+" x "+String(parseInt(container.css('padding-top')))+"\nSaved position: "+savedPosition+"\nIndex Left: "+indexLeft;
	img.css('border','1px solid green');
	alert(s);
	img.css('border','1px solid red');
}
*/
function toggleFullScreen() {
	toggleFullScreen.full=!toggleFullScreen.full;
	if(toggleFullScreen.full) {
		$.adminDialog.self.byWindowSize(true);
		$('iframe',$.adminDialog.self).get(0).contentWindow.document.body.className='fullScreen';
	} else {
		$.adminDialog.self.byWindowSize(false);
		$('iframe',$.adminDialog.self).get(0).contentWindow.document.body.className='';
	}
}
toggleFullScreen.full=false;