<?php
//Статьи в виде блога
foreach($this->itemsPreview as $i=>$item) { ?>
	<div class="item preview">
		<?php $this->admin($item); ?>
		<p class="title"><?=$item['title']?></p>
		<?=$item['text1']?>
		<a href="<?=core::link('article/blog/'.$this->categoryAlias.'/'.$item['alias'])?>" class="readmore">Читать далее...</a>
	</div>
<?php }

//Статьи в виде ссылок
foreach($this->itemsLink as $item) { ?>
	<div class="item link">
		<a href="<?=core::link('article/'.$this->options['linkType'].'/'.$this->categoryAlias.'/'.$item['alias'])?>"><?=$item['title']?></a>
	</div>
<?php }
?>