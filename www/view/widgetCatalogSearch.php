<form action="<?=core::link('catalog/'.$this->catalogId)?>" method="get">
<dl class="form search">
	<?php foreach($this->data as $id=>$item) { ?>
		<dt class="text <?=$item['type']?> fld<?=$id?>"><?=$item['title']?></dt>
		<dd class="text <?=$item['type']?> fld<?=$id?>">
			<?php $this->renderField($item); ?>
		</dd>
	<?php } ?>
	<dd class="submit"><input type="submit" value="<?=LNGFind?>" class="button" /></dd>
</dl>
</form>