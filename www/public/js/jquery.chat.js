$.fn.chat=function(urlContent,serverTime,form,callback) {
	var console=$(this.get(0)); //HTML-контейнер для сообщений
	console.scrollTop(99999);
	var hideMe=false;

	//Генерирует HTML сообщения и обновляет дату последнего сообщения
	var _html=function(data) {
		line=data.split("\t");
		if(line.length!=5) {
			alert(data.replace(/<\/?[^>]+>/gi,''));
			hideMe=true;
			return '';
		}
		if(serverTime>=line[0]) return '';
		if(line[0]>serverTime) serverTime=line[0];
		html='<p><span class="time">';
		var date=new Date(line[0]*1000);
		var s=date.getDate().toString();
		if(s.length==1) s='0'+s;
		html+=s+'.';
		var s=date.getMonth().toString();
		if(s.length==1) s='0'+s;
		html+=s+'.'+date.getFullYear()+' ';
		var s=date.getHours().toString();
		if(s.length==1) s='0'+s;
		html+=s+':';
		var s=date.getMinutes();
		if(s.length==1) s='0'+s;
		html+=s+':';
		var s=date.getSeconds();
		if(s.length==1) s='0'+s;
		var from=line[1].split('|');
		html+=s+'</span> <span class="from">'+from[0]+'</span>: ';
		if(line[2]!='|') {
			var to=line[2].split('|');
			html+='<span class="to">'+to[0]+'</span>, ';
		}
		html+='<span class="message">'+line[3]+'</span>';
		html+='</p>';
		return html;
	}

	//Форма постит сообщения
	form.action+='?ajax';
	$(form).ajaxForm(function(data) {
		$('.message',form).val('').focus();
		if(!data) return false;
		console.append(_html(data));
		if(hideMe==false) {
			$('.hideMe',form).remove();
			hideMe=true;			
		}
	});

	//Обновляет чат
	setInterval(function() {
		var link=urlContent+'&time='+serverTime;
		$.get(link,function(content) {
			if(!content) return;
			content=content.split("\n");
			var html='';
			for(var i=0;i<content.length;i++) {
				html+=_html(content[i]);
			}
			console.append(html);
			console.scrollTop(99999);
		});
	},2500);

	//Форма вставляет смайл в чат
	var smile=$('div.smile',form);
	if(smile.length==1) {
		$('img',smile).click(function() {
			$('.message',form).insertAtCaret(' [['+this.alt+']] ');
		});
	}

	if(callback!=undefined) callback(console,form);
	return this;
};


jQuery.fn.insertAtCaret=function(value) {
	return this.each(function(i) {
		if(document.selection) {
			this.focus();
			var sel=document.selection.createRange();
			sel.text=myValue;
			this.focus();
		} else if(this.selectionStart || this.selectionStart=='0') {
			var startPos=this.selectionStart;
			var endPos=this.selectionEnd;
			this.value=this.value.substring(0, startPos)+value+this.value.substring(endPos,this.value.length);
			this.focus();
			this.selectionStart=startPos+myValue.length;
			this.selectionEnd=startPos+myValue.length;
		} else {
			this.value+=myValue;
			this.focus();
		}
	});
};

function setHeight(console,form) {
	var img=$('img',form);
	var counter=0;
	img.each(function() {
		if(this.complete) counter++;
	});
	if(counter<img.length) {
		img.on('load',function() {
			counter++;
		});
	}
	var interval=setInterval(function() {
		if(counter==img.length) {
			clearInterval(interval);
			var height=$(window).height()-$(form).outerHeight(true)-3;
			if(height<150) height=185;
			console.height(height);
			$(document).ready(function() {
				$(window).scrollTop(console.offset().top-3);
			});
		}
	},50);
}

$('#chatConsole').chat(
	document.chatUrlContent,
	document.chatTime,
	document.forms.chatMessage,
	setHeight
);