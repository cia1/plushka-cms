/*
setInterval(function() {
},2000);
*/
$.fn.chat=function(urlRoot,serverTime,form) {
	var console=$(this.get(0)); //HTML-контейнер для сообщений

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
	return this;
}