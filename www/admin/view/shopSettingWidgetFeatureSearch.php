<?php
use plushka\admin\core\plushka;
?>
<form action="<?=plushka::link('admin/shopSetting/widgetFeatureSearch')?>" method="post">
<dl class="form">
	<dt class="checkbox">Поиск по цене</dt>
	<dd class="checkbox">
		<label><input type="checkbox" name="shopSetting[price]"<?php if($this->price) echo ' checked="checked"'; ?> /></label>
	</dd>
	<dt class="checkbox">Поиск по производителю</dt>
	<dd class="checkbox">
		<label><input type="checkbox" name="shopSetting[brand]"<?php if($this->brand) echo ' checked="checked"'; ?> /></label>
	</dd>
</dl>
<h3>Характеристики поиска:</h3>
<?php foreach($this->feature as $i=>$data) {
	?>
	<p class="group minus" onclick="shopToggleShow(this,'group<?=$i?>');"><?=$data['title']?></p>
	<div class="group" id="group<?=$i?>">
	<?php foreach($data['data'] as $item) { ?>
		<label><input type="checkbox" name="shopSetting[checked][]" value="<?=$item['id']?>"<?=($item['checked'] ? ' checked="checked"' : '')?> /> <?=$item['title']?></label>
		<div class="featureData">
			<?php $this->renderFeatureSelect($item['type'],$item['id'],$item['displayType']); ?>
			<?php $this->renderFeatureData($item); ?>
		</div>
	<?php } ?>
	</div>
<?php } ?>
<input type="submit" class="button" style="float:right;" />
</form>
<script>
function shopToggleShow(link,id) {
	$(document.getElementById(id)).toggle('fast',function(a,b) {
		if(this.style.display=='none') link.className='group minus'; else link.className='group plus';
	});
}
function shopSelectType(o) {
	if(o.value=='range') {
		$('.range',$(o).parent()).show();
	} else {
		$('.range',$(o).parent()).hide();
	}
}
</script>
<cite>Укажите все характеристики, которые могут быть использованы при поиске. Если виджет публикуется на странице категории или товара интернет-магазина, то будут отображаться только те характеристики, которые соответствуют текущей категории. В остальных случаях будут отображаться все указанные характеристики.</cite>
