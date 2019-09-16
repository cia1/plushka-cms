<?php
use plushka\core\plushka;
?>
<?=$this->category['text1']?>
<div class="blog category<?=$this->category['id']?>">
<?php foreach($this->articles as $item) {
	$link=plushka::link('article/blog/'.$this->category['alias'].'/'.$item['alias']);
	echo '<div class="item"><p class="title"><a href="'.$link.'">'.$item['title'].'</a></p>';
	$this->admin($item); //вывод административных кнопок для каждой статьи блога
	if($item['date']) echo '<span class="date">'.date('d.m.Y',$item['date']).'</span>';
	echo '<div>'.$item['text1'].'</div><!--noindex--><a href="'.$link.'" class="readmore">'.LNGReadMore.'</a><!--/noindex-->';
	echo '</div>';
} ?>
</div>
<?php plushka::widget('pagination',array('link'=>plushka::link('article/blog/'.$this->category['alias']),'count'=>$this->foundRows,'limit'=>$this->category['onPage'])); ?>
