<div class="list">
<?=$this->category['text1']?>
<?php foreach($this->items as $item) {
	echo '<p><a href="'.core::link('article/list/'.$this->category['alias'].'/'.$item['alias']).'">'.$item['title'].'</a></p>';
} ?>
</div>