<?php if(core::userGroup() && $this->newPost) { ?>
	<p class="forumControl"><a href="<?=core::link('forum/'.$this->categoryId.'/'.'post')?>"><?=LNGCreateTopic?></a></p>
<?php } elseif($this->newPost) { ?>
	<p class="forumControl"><a href="<?=core::link('user/login')?>"><?=LNGEnterToCreateNewTopic?></a></p>
<?php } ?>
<?php foreach($this->topic as $item) { ?>
	<div class="topic">
		<a href="<?=core::link('forum/'.$this->categoryId.'/'.$item['id'])?>" class="title"><?=$item['title']?></a>
		<div class="info">
			<span class="date"><?=date('d.m.Y H:i',$item['date'])?></span>
			<span class="login"><?=$item['login']?></span>
			<span class="postCount"><?=LNGCountAnswersInTopic?> <?=$item['postCount']?></span>
		</div>
		<div style="clear:both;"></div>
	</div>
<?php } ?>
<?php core::widget('pagination',array('count'=>$this->topicTotal,'limit'=>$this->onPage)); ?>