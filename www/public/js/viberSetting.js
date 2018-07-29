function viberSettingInit() {
	$.get('/index2.php?controller=viber&action=init',function(answer) {
		answer=answer.split("\n");
		if(answer[0]!='OK') {
			alert(answer[1]);
			return;
		}
		document.getElementById('viberSettingButton').style.display='none';
		document.getElementById('viberSettingConnect').style.display='';
		document.getElementById('viberCode').innerHTML=answer[1];
		var timer=setInterval(function() {
				$.get('/index2.php?controller=viber&action=userId',function(response) {
					response=response.split("\n");
					if(response[0]!='OK') {
						alert(response[1]);
						clearInterval(timer);
						return;
					}
					if(!response[1]) return;
						clearInterval(timer);
						alert('Настройка завершена, теперь вы можете включить получение уведомлений через Viber.');
						document.location.reload();
				});
		},3000);
	});
}