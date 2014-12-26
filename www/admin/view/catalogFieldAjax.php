<?php
/* Список полей каталога (ajax) */
foreach($this->data as $item) { ?>
	<dt class="checkbox fld<?=$item['type']?>"><label for="fld<?=$item['index']?>"><?=$item['title']?> (<?=$item['description']?>)</label></dt>
	<dd class="checkbox fld<?=$item['type']?>"><input type="checkbox" name="catalog[fld][<?=$item['index']?>][set]" id="fld<?=$item['index']?>"<?=($item['checked'] ? ' checked="checked"' : '')?> />
	<?php switch($item['type']) {
	case 'integer': case 'float': ?>
		&nbsp;&nbsp;&nbsp;Мин.: <input type="text" name="catalog[fld][<?=$item['index']?>][min]" class="text" value="<?=$item['min']?>" /> Макс.: <input type="text" name="catalog[fld][<?=$item['index']?>][max]" class="text" value="<?=$item['max']?>" /> Шаг: <input type="text" name="catalog[fld][<?=$item['index']?>][step]" class="text" value="<?=$item['step']?>" />&nbsp;&nbsp;&nbsp;&nbsp;
		<label><input type="checkbox" name="catalog[fld][<?=$item['index']?>][range]"<?=($item['range'] ? ' checked="checked"' : '')?> />диапазон</label>
		<?php
		break;
	case 'date': ?>
		&nbsp;&nbsp;&nbsp;Мин.: <input type="text" name="catalog[fld][<?=$item['index']?>][min]" class="text" value="<?=$item['min']?>" /> Макс.: <input type="text" name="catalog[fld][<?=$item['index']?>][max]" class="text" value="<?=$item['max']?>" />
	<?php } ?>
	</dd>
<?php } ?>