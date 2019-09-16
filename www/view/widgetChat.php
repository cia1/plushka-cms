<?php
use plushka\core\plushka;
?>
<link href="<?=plushka::url()?>public/css/chat.css" rel="stylesheet" type="text/css" />
<?php foreach($this->content as $item) { ?>
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
<?php } ?>
<p><a href="<?=plushka::link('chat')?>"><?=LNGToChat?>...</a></p>