<?php
use plushka\core\plushka;
?>
<div class="post postStart">
	<div class="userInfo">
		<img src="<?=$this->topic['avatar']?>"  class="avatar" alt="<?=$this->topic['login']?>" />
		<br /><a href="<?=plushka::link('forum/profile/'.$this->topic['userId'])?>"><?=$this->topic['login']?></a>
	</div>
	<p class="date"><?=date('d.m.Y H:i',$this->topic['date'])?></p>
	<div class="message"><?=$this->topic['message']?></div>
	<div style="clear:both;"></div>
</div>
<?php foreach($this->post as $item) { ?>
	<div class="post post">
		<?php $this->admin($item); ?>
		<div class="userInfo">
			<img src="<?=$item['avatar']?>"  class="avatar" alt="<?=$item['login']?>" />
			<br /><a href="<?=plushka::link('forum/profile/'.$item['userId'])?>"><?=$item['login']?></a>
		</div>
		<p class="date"><?=date('d.m.Y H:i',$item['date'])?></p>
		<div class="message"><?=$item['message']?></div>
		<div style="clear:both;"></div>
	</div>
<?php } ?>
<?php if(!$this->topic['status']) { ?>
	<p style="font-style:italic;"><?=LNGTopicClosed?></p>
<?php } ?>
<?php if(isset($this->formReply)) { ?>
	<p class="title"><?=LNGShortAnswer?></p>
	<p><?=LNGYouCanUseTags?> <label title="[b]<?=LNGboldText?>[/b]">[b]</label>, <label title="[i]<?=LNGcursiveText?>[/i]">[i]</label>, <label title="[u]<?=LNGunderlineText?>[/u]">[u]</label>, <label title="[img]http://<?=LNGimageLink?>[/img]">[img]</label></p>
	<?php $this->formReply->render(); ?>
<?php } ?>
<div style="clear:both;"></div>
<?php plushka::widget('pagination',array('count'=>$this->postTotal,'limit'=>$this->onPage)); ?>