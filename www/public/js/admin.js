$.fn.center=function() {
	return this.each(function() {
		var o=$(this);
		var offset=o.offset();
		var position=o.position();
		var top=($(window).height()-o.outerHeight())/2;
		var top=($(window).height()-o.outerHeight())/2-(offset.top-position.top)+$(document).scrollTop();
		if(top<0) top=5;
		var left=($(window).width()-o.outerWidth())/2-(offset.left-position.left);
		o.css({'left':left,'top':top});
	});
}

$.fn.byScreenWidth=function(width) {
	var winWidth=$(window).width();
	return this.each(function() {
		$(this).width(winWidth/100*width);
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
	return false;
}
$.adminDialog.load=function(url) {
	$.adminDialog.self.byScreenWidth(95).fadeIn().center();
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
	if(toggleFullScreen.width==100) toggleFullScreen.width=95; else toggleFullScreen.width=100;
	$.adminDialog.self.byScreenWidth(toggleFullScreen.width);
}
toggleFullScreen.width=95;