<?=core::js('jquery.min')?>
<?=core::js('viberSetting')?>
<div id="viberSettingButton">
	<p>Чтобы получать уведомления через Viber, необходимо подключить ваш телефон.</p>
	<input type="button" onclick="viberSettingInit();" value="Подключить уведомления Viber" class="button">
</div>

<div id="viberSettingConnect" style="display:none;">
	<p>Выполните следующие шаги...</p>
	<p><span>1.</span> Присоединитесь к группе Viber <b><?=$this->groupId?></b>, для этого<br>
	- если на этом компьютере (телефоне) установлен Viber, то перейдите по ссылки: <a href="viber://pa?chatURI=<?=$this->groupId?>">viber://pa?chatURI=<?=$this->groupId?></a>;<br>
	- или запустите Viber, в меню выберите пункт "QR-код" и наведите камеру телефона на изображённый штрих-код:<br>
	<p style="text-align:center;"><img src="<?=core::url()?>public/viber-qr.png"></p>
	<p><span>2.</span> Вступите в группу.
	<p><span>3.</span> Отправьте сообщение в общий чат группы с текстом "<span id="viberCode">(loading...)</span>.</p>
	<p><i>Ожидается получение кода подтверждения...</i></p>
</div>
