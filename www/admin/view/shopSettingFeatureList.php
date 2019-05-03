Группа характеристик: <select id="featureGroup" style="width:400px" onchange="document.location='<?=plushka::link('admin/shopSetting/featureList')?>&gid='+this.value;">
<option value="">(выбрать)</option>
<?php foreach($this->featureGroup as $item) {
	echo '<option value="'.$item[0].'"';
	if($this->gid==$item[0]) echo ' selected="selected"';
	echo '>'.$item[1].'</option>';
} ?>
</select>
&nbsp;&nbsp;<a href="#" onclick="featureGroupDelete();return false;"><img src="<?=plushka::url()?>admin/public/icon/delete16.png" alt="удалить" title="удалить всю группу"  style="position:relative;top:4px;" /></a>
<div id="feature"></div>

<?php
if(!$this->gid) echo '<p>Выберите группу характеристик.</p>';
elseif($this->null) echo '<p>Характеристик нет.</p>';
else $this->t->render();
?>
<cite>Это список всех характеристик товаров. Сначала добавьте характеристики на этой странице, затем перейдите в нужную категорию товаров и нажмите кнопку &laquo;Характеристики товаров этой категории&raquo; для управления характеристиками товаров в категории.<br /><b>Внимание!</b> Удалении характеристики или группы характеристик затрагивает ВСЕ товары, в которых используются данные характеристики. Будьте внимательны!</cite>
<script>
document.mainUrl='<?=plushka::url()?>admin/';
document.featureLink='<?=plushka::link('admin/shopSetting/featureList')?>';
</script>