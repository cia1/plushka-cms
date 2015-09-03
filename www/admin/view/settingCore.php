<?php /*
<div class="tab">
	<form action="<?=$this->form->action?>" method="post">
	<fieldset>
		<legend>Базовые настройки</legend>
		<dl class="form">
			<dt class="checkbox"><label for="debug">Режим отладки</label></dt>
			<dd class="checkbox"><?=$this->form->getCheckbox('debug',$this->cfg['debug'],'id="debug"')?></dd>
			<dt class="text">E-mail администрации</dt>
			<dd class="text"><?=$this->form->getText('adminEmailEmail',$this->cfg['adminEmailEmail'])?></dd>
			<dt class="text">Имя администрации (e-mail)</dt>
			<dd class="text"><?=$this->form->getText('adminEmailName',$this->cfg['adminEmailName'])?></dd>
			<dt class="select">Метод отправки почты</dt>
			<dd class="select method"><?=$this->form->getSelect('method',array(array('smtp','SMTP'),array('email','PHP')),$this->method)?></dd>
			<dt class="checkbox"><label for="smtpSSL">SMTP Защищённое соединеие SSL</label></dt>
			<dd class="checkbox"><?=$this->form->getCheckbox('smtpSSL',$this->smtpSSL,'id="smtpSSL"')?></dd>
			<dt class="text">SMTP e-mail</dt>
			<dd class="text"><?=$this->form->getText('smtpEmail',$this->cfg['smtpEmail'])?></dd>
			<dt class="text">SMTP хост</dt>
			<dd class="text"><?=$this->form->getText('smtpHost',$this->cfg['smtpHost'])?></dd>
			<dt class="text">SMTP логин</dt>
			<dd class="text"><?=$this->form->getText('smtpUser',$this->cfg['smtpUser'])?></dd>
			<dt class="text">SMTP пароль</dt>
			<dd class="text"><?=$this->form->getText('smtpPassword',$this->cfg['smtpPassword'])?></dd>
			<dd class="submit"><?=$this->form->getSubmit()?></dd>
		</dl>
	</fieldset>
	<fieldset>
		<legend>Мультиязычность</legend>
		<dl class="form">
			<dt class="select">Основной язык</dt>
			<dd><?=$this->form->getSelect('languageDefault',$this->languageListSelect,$this->cfg['languageDefault'])?></dd>
			<dt class="textarea">Список используемых языков</dt>
			<dd class="textarea"><?=$this->form->getTextarea('languageList',implode("\n",$this->cfg['languageList']))?></dd>
			<dd class="submit"><?=$this->form->getSubmit()?></dd>
		</dl>
	</fieldset>
	</form>
</div>
<script>
$('.method select').change(function() {
	if(this.value=='smtp') {
		$('.smtpSSL,.smtpEmail,.smtpHost,.smtpUser,.smtpPassword').show();
	} else {
		$('.smtpSSL,.smtpEmail,.smtpHost,.smtpUser,.smtpPassword').hide();
	}
});
setTimeout(function() {
	$('.tab').tab();
	$('.method select').change();
},200);
</script>
*/
?><?php $this->form->render(); ?>
<script>
$('.method select').change(function() {
	if(this.value=='smtp') {
		$('.smtpSSL,.smtpEmail,.smtpHost,.smtpUser,.smtpPassword').show();
	} else {
		$('.smtpSSL,.smtpEmail,.smtpHost,.smtpUser,.smtpPassword').hide();
	}
}).change();
</script>
