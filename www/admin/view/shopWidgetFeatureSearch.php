<form action="<?=core::link('?controller=shop&action=widgetFeatureSearch')?>" method="post">
<dl class="form">
<dt class="checkbox">Поиск по цене</dt><dd class="checkbox"><label><input type="checkbox" name="shop[price]"<?php if($this->data['price']) echo ' checked="checked"'; ?> /></label></dd>
</dl>
<h3>Характеристики поиска:</h3>
<?php foreach($this->feature as $i=>$data) {
	echo '<h4 onclick="$(\'#group'.$i.'\').toggle();">'.$data['title'].'</h4><div class="group" id="group'.$i.'">';
	foreach($data['data'] as $item) {
		$d=(isset($this->data['feature'][$item['id']]) ? $this->data['feature'][$item['id']] : null);
		echo '<label><input type="checkbox" name="shop[item]['.$item['id'].']"'.($d ? 'checked="checked"' : '').' /> '.$item['title'].'</label>
		<div class="type">';
		switch($item['type']) {
		case 'text':
			echo '<p>диапазон целых чисел (ползунок)</p>';
			echo '<input type="hidden" name="shop[type]['.$item['id'].']" value="slider" />';
			echo 'минимум: <input type="text" name="shop[min]['.$item['id'].']" value="'.($d ? $d['min'] : '').'" /> максимум: <input type="text" name="shop[max]['.$item['id'].']" value="'.($d ? $d['max'] : '').'" />';
			break;
		case 'select':
			$_type=($d ? $d['type'] : null);
			echo '<p>выпадающий список</p>';
			echo '<select name="shop[type]['.$item['id'].']">
			<option value="select"'.($_type=='select' ? ' selected="selected"' : '').'>выпадающий список</option>
			<option value="checkboxList"'.($_type=='checkboxList' ? ' selected="selected"' : '').'>список чекбоксов</option>
			</select>';
			break;
		case 'checkbox':
			echo '<p>чекбокс</p>';
			echo '<input type="hidden" name="shop[type]['.$item['id'].']" value="checkbox" />';
			break;
		}
		echo '</div>';
	}
	echo '</div>';
} ?>
<input type="submit" class="button" style="float:right;" />
</form>
<script type="text/javascript">
$('.group').hide();
</script>
<cite>Укажите все характеристики, которые могут быть использованы при поиске. Если виджет публикуется на странице категории или товара интернет-магазина, то будут отображаться только те характеристики, которые соответствуют текущей категории. В остальных случаях будут отображаться все указанные характеристики.</cite>
