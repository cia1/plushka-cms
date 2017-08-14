<div id="chatConsole"><?php foreach($this->content as $item) { ?>
	<p>
		<span class="time"><?=date('d.m.Y H:i:s',$item['time'])?></span>
		<span class="from"><?=$item['fromLogin']?></span>:
		<span class="message">
			<?php if($item['toLogin']) { ?>
				<span class="to"><?=$item['toLogin']?></span>,
			<?php } ?>
			<?=$item['message']?>
		</span>
	</p>
<?php } ?></div>

<form action="<?=core::link('chat')?>" class="chatMessage" method="post" name="chatMessage">
<?php if($this->fromLogin) { ?>
<?php } else { ?>
	<p><?=LNGEnterYourName?>: <input type="text" name="chat[login]" placeholder="<?=LNGLogin?>" /></p>
	<p><?=LNGCaptcha?>:<br />
	<img src="<?=core::url()?>captcha.php" alt="captcha" /> <input type="text" name="chat[captcha]" />
	<p>Или <a href="<?=core::link('user/login')?>"><?=LNGlogIn?></a></p>
<?php } ?>
<input type="text" name="chat[message]" class="message" />
<?php if($this->smile) { ?>
	<p class="smile"><?php foreach($this->smile as $id=>$item) { ?>
		<img src="<?=$item?>" alt="<?=$id?>" />
	<?php } ?>
	</p>
<?php } ?>
<input type="submit" value="<?=LNGSay?>" class="button" />
</form>

<script>
$('#chatConsole').chat(
	'<?=core::url()?>',
	<?=microtime(true)?>,
	document.forms.chatMessage
);
</script>