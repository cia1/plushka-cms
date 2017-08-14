$.fn.chat=function(urlRoot,serverTime,form) {
	var console=$(this.get(0)); //HTML-контейнер для сообщений
	var hideMe=false;

	//Генерирует HTML сообщения и обновляет дату последнего сообщения
	var _html=function(data) {
		line=data.split("\t");
		if(line.length!=5) return '<p class="system">'+data+'</p>';
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
	$(form).ajaxForm(function(data) {
		$('.message',form).val('').focus();
		if(!data) return;
		if(hideMe==false) {
			$('.hideMe',form).remove();
			hideMe=true;			
		}
		console.append(_html(data));
	});
	$('.message',form).focus();

	//Обновляет чат
	setInterval(function() {
		var link=urlRoot+'index2.php?controller=chat&action=content&time='+serverTime;
		$.get(link,function(content) {
			if(!content) return;
			content=content.split("\n");
			var html='';
			for(var i=0;i<content.length;i++) {
				html+=_html(content[i]);
			}
			console.append(html);
		});
	},2500);

	//Форма вставляет смайл в чат
	var smile=$('.smile',form);
	if(smile.length==1) {
		$('img',smile).click(function() {
			$('.message',form).insertAtCaret(' [['+this.alt+']] ');
		});
	}

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