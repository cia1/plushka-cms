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
	$('#_adminDialogBox .head span').html('<img src="'+img.attr('src')+'"> '+img.attr('title'));
	$.adminDialog.load(o.attr('href'));
	$('#_adminDialogBox .head').mousedown(function(e) {
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
	$('.head img',$.adminDialog.self).mouseover(function() {
		$.adminDialog.self.css('opacity',0.4);
	}).mouseout(function() {
		$.adminDialog.self.css('opacity',1);
	});
	return false;
}
$.adminDialog.load=function(url) {
	$.adminDialog.self.byScreenWidth(95).fadeIn().center();
	$('#_adminDialogBox iframe.container')[0].src=url;
}
$.adminDialog.afterLoad=function(h) {
	$('#_adminDialogBoxLoading').hide();
	$('#_adminDialogBox iframe').height(h+26);
	$('#_adminDialogBox').center();
}

/* Рисует кнопки административного интерфейса */
function _adminElement() { jQuery(function() {
	$('._adminItem img').each(function() {
		var o=$(this); //<img>
		var container=o.parent(); //<a>
		var positionContainer=container.position(); //позици контейнера кнопки
//		container=container.parent(); //контейнер, содержащий кнопку
		var s=positionContainer.left+'.'+positionContainer.top;
		//"Сдвигает" кнопку вправо, если в этой точке есть другие кнопки
		if(isNaN(_adminElement.itemByPoint[s])) indexLeft=0; else {
			indexLeft=_adminElement.itemByPoint[s]+20;
		}
		if(indexLeft && !parseInt(o.attr('index'))) { //если это следующая группа в строке, то нужно её немного сдвинуть визуально
			indexLeft+=6;
		}
		_adminElement.itemByPoint[s]=indexLeft;
		o.css({
			'position':'absolute',
			'left':positionContainer.left+indexLeft,
			'top':positionContainer.top
		});
//_debug(o,s,indexTop,19,22);
	});
}); }
_adminElement.itemByPoint=[]; //содержит кол-во кнопок в одной точке (чтобы они не накладывались друг на друга)
setTimeout(_adminElement,300);
delete _adminElement;
/*
function _debug(o,sp,indexTop,from,to) {
	if(!_debug.index) _debug.index=1; else _debug.index++;
	if(!from) from=1;
	if(!to) to=9999;
	if(_debug.index<from || _debug.index>to) return;
	var parent=o.parent();
	var position=parent.position();
	parent=parent.parent();
	var offset=parent.offset();
	var s='#'+_debug.index+' ICON: '+o[0].src.substr(29,o[0].src.length-35)+"\nLINK: "+o.parent()[0].href.substr(27);
	s+="\nParent Class: "+parent[0].className;
	s+="\nIndex TOP: "+indexTop;
	s+="\nParent position: "+String(position.left)+' x '+String(position.top)+"\noffset: "+String(offset.left)+' x '+String(offset.top)+"\nparrent padding: "+String(parseInt(parent.css('padding-left')))+" x "+String(parseInt(parent.css('padding-top')))+"\nsave position: "+sp;
	o.css('border','1px solid green');
	alert(s);
	o.css('border','1px solid red');
}
*/