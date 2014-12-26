<?php
/* Интернет-магазин: поиск по характеристикам товара, в зависимости от текущей категории
array $options: array feature содержит характеристики, по которым нужно настроить фильтр (поиск) */
class widgetShopFeatureSearch extends widget {

	public function action() {
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
		return true;
	}

	public function render() {
		echo '<form action="'.core::link('shop/category'.($this->categoryId ? '/'.$this->categoryId : '')).'" method="get">';
		if($this->categoryId) echo '<input type="hidden" name="category" value="'.$this->categoryId.'" />';
		echo '<link href="'.core::url().'public/css/shop.css" rel="stylesheet" type="text/css" />';
		echo core::script('jquery.min');
		echo core::script('shop');
		$db=core::db();
		$title=array();
		foreach($this->data as $item1) {
			$id=$item1['id'];
			$item2=$this->options['feature'][$id];
			$s='_render'.ucfirst($item2['type']);
			self::$s($item2,$item1);
		}
		if($this->options['price']) {
			if(isset($_GET['price1']) && $_GET['price1']) $price1=(float)$_GET['price1']; else $price1='';
			if(isset($_GET['price2']) && $_GET['price2']) $price2=(float)$_GET['price2']; else $price2='';
			echo '<div class="price"><p>Цена</p>
			от &nbsp;<input type="text" name="price1" value="'.$price1.'" />&nbsp;&nbsp;&nbsp;&nbsp; до &nbsp;<input type="text" name="price2" value="'.$price2.'" /></div>';
		}
		echo '<input type="submit" class="button" value="Применить" /></form>';
	}

	/* Вывод HTML-кода слайдера (число) */
	private static function _renderSlider($data2,$data1) {
		if(isset($_GET['feature']) && isset($_GET['feature'][$data1['id']])) $value=$_GET['feature'][$data1['id']]; else $value=$data2['min'];
		echo '<div class="slider"><p>'.$data1['title'].': <span>'.$value.' '.$data1['unit'].'</span></p>
		<input type="hidden" name="feature['.$data1['id'].']" value="'.$value.'" />
		<div class="box">
		<div class="min">'.$data2['min'].'</div>
		<div class="max">'.$data2['max'].'</div>
		<div class="begun" min="'.$data2['min'].'" max="'.$data2['max'].'" unit="'.$data1['unit'].'"></div>
		</div></div>';
	}

	/* Вывод HTML-кода выпадающего списка */
	private static function _renderSelect($data2,$data1) {
		if(isset($_GET['feature']) && isset($_GET['feature'][$data1['id']])) $value=$_GET['feature'][$data1['id']]; else $value=null;
		$data=explode('|',$data1['data']);
		echo '<div class="select"><p>'.$data1['title'].'</p>
		<select name="feature['.$data1['id'].']">';
		foreach($data as $item) echo '<option value="'.$item.'"'.($item==$value ? ' selected="selected"' : '').'>'.$item.'</option>';
		echo '</select></div>';
	}

	/* Вывод HTML-кода чекбокса */
	private static function _renderCheckboxList($data2,$data1) {
		if(isset($_GET['feature']) && isset($_GET['feature'][$data1['id']])) $g=$_GET['feature'][$data1['id']]; else $g=array();
		$data=explode('|',$data1['data']);
		echo '<div class="checkboxList"><p>'.$data1['title'].'</p>';
		foreach($data as $item) echo '<label><input type="checkbox" name="feature['.$data1['id'].'][]" value="'.$item.'"'.(in_array($item,$g) ? ' checked="checked"' : '').' />'.$item.'</option></label>';
		echo '</div>';
	}

}
?>