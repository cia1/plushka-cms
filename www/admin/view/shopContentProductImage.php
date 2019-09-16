<?php
use plushka\admin\core\plushka;

if(!$this->image) echo '<p><i>Ни одной фотографии для этого товара не загружено.</i></p>';
foreach($this->image as $item) { ?>
	<div class="image"><img src="<?=plushka::url().'public/shop-product/_'.$item?>" /><br />
	<?php if($this->mainImage!=$item) echo '<a href="'.plushka::link('admin/shopContent/productImageMain?id='.$_GET['id'].'&image='.$item).'">Сделать главным</a><br />'; ?>
	<a href="<?=plushka::link('admin/shopContent/productImageDelete?id='.$_GET['id'].'&image='.$item)?>">Удалить</a>
	</div>
<?php }
?>
<div style="clear:both;"></div>
<h3>Добавить изображение</h3>
<?php $this->form->render(); ?>