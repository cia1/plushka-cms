<?php
echo plushka::js('jquery.min','defer');
echo plushka::js('jquery.form','defer');
?>
<link type="text/css" rel="stylesheet" href="<?=plushka::url()?>public/css/chat.css" />
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
<form action="<?=plushka::link($this->options['urlSubmit'])?>" class="chatMessage" method="post" name="chatMessage">
<?php if(!$this->fromLogin) { ?>
	<p class="hideMe"><input type="text" name="chat[login]" placeholder="<?=LNGEnterYourName?>...	" style="width:98px;" />
	&nbsp;&nbsp;&nbsp;
	<img src="<?=plushka::url()?>captcha.php" alt="captcha" /> <input type="text" name="chat[captcha]" placeholder="<?=LNGCaptcha?>" style="width:112px;" />
	&nbsp;&nbsp;
	или <a href="<?=plushka::link('user/login')?>"><?=LNGlogIn?></a></p>
<?php } ?>
<div class="messageLine">
	<input type="text" name="chat[message]" class="message" />
	<input type="button" value="" class="button smile" onclick="document.getElementById('smile').style.display='block';" />
	<input type="submit" value="<?=LNGSay?>" class="button" />
</div>
<?php if($this->smile) { ?>
	<div id="smile" class="smile" style="display:none;" onclick="document.getElementById('smile').style.display='none';"><?php foreach($this->smile as $id=>$item) { ?>
		<img src="<?=$item?>" alt="<?=$id?>" />
	<?php } ?>
	</div>
<?php } ?>
</form>
<script>document.chatTime=<?=microtime(true)?>;document.chatUrlContent='<?=$this->options['urlContent']?>';</script>
<?=plushka::js('jquery.chat','defer')?>