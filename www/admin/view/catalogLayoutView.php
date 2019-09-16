<?php
use plushka\admin\core\plushka;
?>
<form action="<?=plushka::link('admin/catalog/layoutView?lid='.$_GET['lid'])?>" method="post">
<input type="hidden" name="catalog[view]" value="<?=$_GET['view']?>" />
<div id="layoutView">
<div class="checkbox1">Включён</div><div class="title">Блок данных</div><div class="checkbox2">Отображать заголовок</div>
<div style="clear:both;"></div>
<?php
foreach($this->layout as $item) { ?>
	<div class="item <?=($item['enabled'] ? 'enabled' : 'disabled')?>" id="item<?=$item['index']?>">
	<input type="hidden" name="catalog[index][]" value="<?=$item['index']?>" />
	<div class="checkbox1"><input type="checkbox" name="catalog[enabled][<?=$item['index']?>]"<?php if($item['enabled']) echo ' checked="checked"'; ?> onchange="setEnabled('<?=$item['index']?>',this.checked);" /></div>
	<div class="title"><?=$item['title']?></div>
	<div class="checkbox2"><input type="checkbox" name="catalog[showTitle][<?=$item['index']?>]"<?php if($item['showTitle']) echo ' checked="checked"'; ?> /></div>
	<a href="#" onclick="return moveUp('<?=$item['index']?>');"><img src="<?=plushka::url()?>admin/public/icon/up16.png" alt="Вверх" title="Поднять выше" /></a>
	<a href="#" onclick="return moveDown('<?=$item['index']?>');"><img src="<?=plushka::url()?>admin/public/icon/down16.png" alt="Вниз" title="Опустить ниже" /></a>
	</div>
<?php }
?>
</div>
<hr />
<?php if($_GET['view']=='view1') {
	echo '<dl class="form"><dt class="text">Элементов на странице</dt><dd class="text"><input type="text" name="catalog[onPage]" value="'.$this->onPage.'" /></dd>';
	echo '<dt class="select">Сортировка</dt><dd class="select"><select name="catalog[sort]"><option value="">(нет)</option>';
	foreach($this->sortList as $item) {
		echo '<option value="'.$item[0].'"'.($item[0]==$this->sort ? ' selected="selected"' : '').'>'.$item[1].'</option>';
	}
	echo '</select></dl>';
} ?>
<input type="submit" class="button" value="Продолжить" style="float:right;" />
</form>
<script>
function setEnabled(id,value) {
	var item=document.getElementById('item'+id);
	if(value) item.className='item enabled'; else item.className='item disabled';
}
function moveUp(id) {
		var item=$('#item'+id);
		item.insertBefore(item.prev('.item'));
		return false;
}
function moveDown(id) {
		var item=$('#item'+id);
		item.insertAfter(item.next('.item'));
		return false;
}
</script>