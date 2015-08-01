<div id="chatPage">
<div class="online"><p><b>Кто в чате:</b></p><div id="online">Загрузка...</div></div>
<div id="console"></div>
<?php
$f=core::form('chat');
$f->hidden('time',0,'id="time"');
if($this->login) $f->label('Имя:',$this->login); else $f->text('login','Имя:',$this->login,'id="login"');
$f->text('message','Сообщение:','','id="message"');
$f->submit('Сказать');
$f->render('index2.php?controller=chat&amp;action=post&amp;id='.$this->id);
?>
<script>
jQuery(function() {
	var isName=false;
	var chatBtn=$('form.chat input.button');
	$('form.chat').submit(function() {
		var status=true;
		$('input[type=text]',this).each(function() {
			if(this.name=='chat[login]') isName=true;
			if(!$(this).val().trim()) { status=false; }
		});
		if(status) return true;
		return false;
	});
	$('form.chat').ajaxForm({success:function(data) {
		if(isName) {
			if(data.substr(data.indexOf('|||',13)+3,3)!='<i>') $('dd.login').append($('input#login').remove().val());
		}
		loadChatData.timestamp=parseInt(new Date().valueOf()/1000);
		appendChatData(data);
		$('#message').val('').focus();
		chatBtn.timeout=30;
		chatBtn.attr('disabled','disabled').attr('value','( '+chatBtn.timeout+' )');
		var chatPause=setInterval(function() {
			if(!--chatBtn.timeout) {
				clearInterval(chatPause);
				chatBtn.removeAttr('disabled').val('Сказать');
			} else chatBtn.attr('value','( '+chatBtn.timeout+' )');
		},1000);
	}});

	function loadChatData() {
		$.get('<?=core::link('index2.php?controller=chat&action=load&id='.$this->id)?>&t='+loadChatData.timestamp,appendChatData);
		loadChatData.timestamp=parseInt(new Date().valueOf()/1000);
	}
	loadChatData.timestamp=0;

	function appendChatData(data) {
		data=data.split("\n");
		if(data[data.length-1]) $('#online').html('<ul><li>'+data[data.length-1].split('|||').sort().join('</li><li>')+'</li></ul>');
		else $('#online').html('Активности в чате нет.');
		$('#online li').click(function() {
			var o=$('#message');
			o.val(this.innerHTML+': '+o.val());
		});
		var s='';
		for(i=data.length-2;i>=0;i--) {
			data[i]=data[i].split('|||');
			var date=new Date(data[i][0]*1000);
			s+='<p><span class="time">'+date.getDate()+'.'+date.getMonth()+' '+date.getHours()+':'+date.getMinutes()+'</span><span class="name">'+data[i][1]+'</span>: '+data[i][2]+'</p>';
		}
		if(s) $('#time').val(data[0][0]);
		$('#console').append(s);
		$("#console").animate({scrollTop:$("#console").prop("scrollHeight")}); 
	}

	loadChatData();
	setInterval(loadChatData,10000);
});
</script>
</div>
<div style="clear:both;"></div>
<?php if(!core::userGroup()) echo '<p><br />Чтобы общение было более приятным пройдите простую <a href="'.core::link('user/register').'" rel="nofollow">процедуру регистрации</a>.</p>'; ?>