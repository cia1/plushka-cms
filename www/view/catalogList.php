<?php
use plushka\core\plushka;
use plushka\model\catalog;
?>
<div id="catalog" itemscope itemtype="https://schema.org/DataCatalog">
<?php if($this->text1) echo '<div class="text1" itemprop="about">',$this->text1,'</div>'; ?>
<?php if(!$this->data) {
	echo '<p><i>'.LNGThereIsNothingToYourRequest.'</i></p>';
} ?>
<?php foreach($this->data as $item) {
	?>
	<div class="item" itemprop="dataset" itemscope itemtype="https://schema.org/Dataset">
		<p class="title" itemprop="name"><?=($item['link'] ? '<a href="'.$item['link'].'" itemprop="url">'.$item['title'].'</a>' : $item['title'])?></p>
		<?php foreach($item['field'] as $fld) {
			echo '<p>';
			if($fld['layout']['title']) echo '<label>',$fld['layout']['title'],'</label>: ';
			catalog::render($fld); //генерирует HTML-код поля
			echo '</p>';
		}
		if($item['link']) echo '<!--noindex--><a class="readmore" href="',$item['link'],'">'.LNGDetails.'</a><!--/noindex-->';
		?>
	</div>
<?php } ?>
</div>
<?php plushka::widget('pagination',array('limit'=>$this->onPage,'count'=>$this->foundRows)); ?>