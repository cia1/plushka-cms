<?php
if(!$this->image) echo '<p><i>Ни одной фотографии для этого товара не загружено.</i></p>';
foreach($this->image as $item) { ?>
	<div class="image"><img src="<?=core::url().'public/productImage/_'.$item?>" /><br />
	<?php if($this->mainImage!=$item) echo '<a href="'.core::link('?controller=shop&action=productImageMain&id='.$_GET['id'].'&image='.$item).'">Сделать главным</a><br />'; ?>
	<a href="<?=core::link('?controller=shop&action=productImageDelete&id='.$_GET['id'].'&image='.$item)?>">Удалить</a>
	</div>
<?php }
?>
<div style="clear:both;"></div>
<h3>Добавить изображение</h3>
<?php $this->form->render(); ?>