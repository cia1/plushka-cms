<?php
/* Интернет-магазин: поиск по характеристикам товара, в зависимости от текущей категории
array $options: array feature содержит характеристики, по которым нужно настроить фильтр (поиск) */
class widgetShopFeatureSearch extends widget {

	public function __invoke() {
		if(count($_GET['corePath'])==4) return false;
		if($_GET['corePath'][0]=='shop' && $_GET['corePath'][1]=='category') $this->categoryId=(int)$_GET['corePath'][2];
		else $this->categoryId=null;
		$db=core::db();
		//Если в браузере открыта страница магазина - вывести только те характеристики, которые относятся к текущей категории,
		//если же категория неизвестна, то вывести все характеристики, указанные в настройках виджета
		if($this->categoryId) {
			$feature=$db->fetchValue('SELECT feature FROM shpCategory WHERE id='.$this->categoryId);
			if(!$feature) return false;
			$this->data=$db->fetchArrayAssoc('SELECT id,title,unit,data FROM shpFeature WHERE id IN('.implode(',',array_keys($this->options['feature'])).') AND id IN('.$feature.') ORDER BY id DESC');
		} else $this->data=$db->fetchArrayAssoc('SELECT id,title,unit,data FROM shpFeature WHERE id IN('.implode(',',array_keys($this->options['feature'])).') ORDER BY id DESC');
		if(!$this->data) return false;
		$feature=$this->options['feature'];

		//Если есть параметр с типом "list", то загрузить из кеша варианты значений для всех характеристик с типом "list"
		$cache=array();
		foreach($feature as $id=>$item) {
			if($item['type']=='list') $cache[]=$id;
		}
		if($cache) {
			$categoryId=$this->categoryId;
			//Анонимная callback-функция возвращает массив уникальных значений характеристик товаров
			$cache=core::cache('featureSearch-'.$this->categoryId,function() use($categoryId,$cache) {
				if($categoryId) $q='SELECT DISTINCT pf.value FROM shpProductFeature pf INNER JOIN shpProduct p ON p.id=pf.productId AND p.categoryId='.$categoryId.' WHERE pf.featureId=';
				else $q='SELECT DISTINCT value FROM shpProductFeature WHERE featureId=';
				$db=core::db();
				$featureValue=array();
				foreach($cache as $featureId) {
					$db->query($q.$featureId);
					$data='';
					while($item=$db->fetch()) {
						if($data) $data.='|';
						$data.=$item[0];
					}
					$featureValue[$featureId]=$data;
				}
				return $featureValue;
			},7200);
		} else $cache=null;
		//Окончательный цикл, объединяющий исходные данные в один массив
		for($i=0,$cnt=count($this->data);$i<$cnt;$i++) {
			$id=$this->data[$i]['id'];
			$this->data[$i]=array_merge($this->data[$i],$feature[$id]);
			if($feature[$id]['type']=='list') {
				$this->data[$i]['type']='select';
				$this->data[$i]['data']=$cache[$id];
			}
		}
		unset($cache);
		return true;
	}

	public function render() {
		echo '<form action="'.core::link('shop/category'.($this->categoryId ? '/'.$this->categoryId : '')).'" method="get" onsubmit="return submitMe();">';
		echo '<link href="'.core::url().'public/css/shop.css" rel="stylesheet" type="text/css" />';
		$db=core::db();
		foreach($this->data as $item) {
			$s='_render'.ucfirst($item['type']);
			self::$s($item);
		}
		if($this->options['price']) {
			if(isset($_GET['price1']) && $_GET['price1']) $price1=(float)$_GET['price1']; else $price1='';
			if(isset($_GET['price2']) && $_GET['price2']) $price2=(float)$_GET['price2']; else $price2='';
			?>
			<div class="price">
				<p><?=LNGPrice?></p>
				<?=LNGfrom?> &nbsp;<input type="text" name="price1" value="<?=$price1?>" />&nbsp;&nbsp;&nbsp;&nbsp; <?=LNGto?> &nbsp;<input type="text" name="price2" value="<?=$price2?>" />
			</div>
			<?php
		}
		echo '<input type="submit" class="button" value="'.LNGCommit.'" /></form>';
		?>
		<script>
		function submitMe() {
var o=$('.widgetshopFeatureSearch form select').each(function() {
	if(!this.value) $(this).remove();
});
return true;
//			return false;
		}
		</script>
		<?php
	}

	// Вывод HTML-кода целого числа (диапазон)
	private static function _renderRange($data) {
		if(isset($_GET['feature']) && isset($_GET['feature'][$data['id']])) $value=$_GET['feature'][$data['id']]; else $value=array($data['min'],$data['max']);
		?>
		<div class="range"><p><?=$data['title']?>:</p>
			<input type="text" name="feature[<?=$data['id']?>][]" value="<?=$value[0]?>" /> <?=$data['unit']?>
			<input type="text" name="feature[<?=$data['id']?>][]" value="<?=$value[1]?>" /> <?=$data['unit']?>
		</div>
	<?php }

	/* Вывод HTML-кода выпадающего списка */
	private static function _renderSelect($data) {
		if(isset($_GET['feature']) && isset($_GET['feature'][$data['id']])) $value=$_GET['feature'][$data['id']]; else $value=null;
		$valueList=explode('|',$data['data']);
		?>
		<div class="select">
			<p><?=$data['title']?>:</p>
			<select name="feature[<?=$data['id']?>]">
			<option value=""></option>
			<?php foreach($valueList as $item) { ?>
				<option value="<?=$item?>"<?php if($item==$value) echo ' selected="selected"'; ?>><?=$item?></option>
			<?php } ?>
			</select></div>
	<?php }

	/* Вывод HTML-кода чекбокса */
	private static function _renderCheckboxList($data) {
		if(isset($_GET['feature']) && isset($_GET['feature'][$data['id']])) $checked=$_GET['feature'][$data['id']]; else $checked=array();
		$valueList=explode('|',$data['data']);
		?>
		<div class="checkboxList"><p><?=$data['title']?>:</p>
		<?php foreach($valueList as $item) { ?>
			<label><input type="checkbox" name="feature[<?=$data1['id']?>][]" value="<?=$item?>"<?php if(in_array($item,$checked)) echo ' checked="checked"'; ?> /><?=$item?></option></label>
		<?php } ?>
			<div style="clear:both;"></div>
		</div>
	<?php }

}
?>