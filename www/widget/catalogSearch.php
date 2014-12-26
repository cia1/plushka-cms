<?php
/* Реализует поиск по универсальному каталогу
array $options: int id - идентификатор каталога; array fld - поля поиска */
class widgetCatalogSearch extends widget {

	public function action() {
		$this->layout=core::config('catalogLayout/'.$this->options['id']); //конфигурация каталога
		//Перечислить все поля, по которым должен быть выполнен поиск и подготовленные данные загрузить в $this->data
		$this->data=array();
		foreach($this->options['fld'] as $id=>$item) {
			if($id=='title') $item=array('id'=>$id,'title'=>'Заголовок','type'=>'string'); else {
				if(!is_array($item)) $item=array();
				$item['id']=$id;
				$item['title']=$this->layout['data'][$id][0];
				$item['type']=$this->layout['data'][$id][1];
				if($item['type']=='list') {
					$item['list']=explode("\n",$this->layout['data'][$id][2]);
				}
			}
			if(isset($_GET[$id])) {
				if(isset($item['range'])) $item['value']=explode('-',$_GET[$id]); else $item['value']=$_GET[$id];
			} else $item['value']=null;
			$this->data[$id]=$item;
		}
		$this->catalogId=$this->options['id'];
		unset($this->options);
		return 'CatalogSearch';
	}

	/* Выводит HTML-представление поля */
	public function renderField($data) {
		$f='_render'.ucfirst($data['type']);
		self::$f($data,$value);
	}

	private static function _renderString($data) {
		echo '<input type="text" name="'.$data['id'].'" value="'.addslashes($data['value']).'" />';
	}

	/* HTML-представление для числа (строгое значение или диапазон значений) */
	private static function _renderInteger($data) {
		if($data['range']) self::_renderIntegerRange($data); else self::_renderIntegerSingle($data);
	}

	/* HTML-представление для числа (строго заданное значение) */
	private static function _renderIntegerSingle($data) {
		echo '<select name="'.$data['id'].'"><option value="">&nbsp;</option>';
		$value=$data['value'];
		for($i=$data['min'];$i<=$data['max'];$i+=$data['step']) {
			echo '<option value="'.$i.'"'.($i==$value ? ' selected="selected"' : '').'>'.$i.'</option>';
		}
		if($i!=$data['max']) echo '<option value="'.$data['max'].'">'.$data['max'].'</option>';
		echo '</select>';
	}

	/* HTML-представление для числа (диапазон) */
	private static function _renderIntegerRange($data) {
		static $index;
		$index++;
		?>
		от:&nbsp;&nbsp;
		<select id="cir<?=$index?>-1" class="range1" onchange="document.getElementById('cir<?=$index?>').value=this.value+'-'+document.getElementById('cir<?=$index?>-2').value;"><option value="">&nbsp;</option>';
		<?php for($i=$data['min'];$i<=$data['max'];$i+=$data['step']) {
			echo '<option value="'.$i.'"'.($i==$data['value'][0] ? ' selected="selected"' : '').'>'.$i.'</option>';
		}
		if($i!=$data['max']) echo '<option value="'.$data['max'].'"'.($data['max']==$data['value'][0] ? ' selected="selected"' : '').'>'.$data['max'].'</option>';
		?>
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;до:&nbsp;&nbsp;<select id="cir<?=$index?>-2" class="range2" onchange="document.getElementById('cir<?=$index?>').value=document.getElementById('cir<?=$index?>-1').value+'-'+this.value;"><option value="">&nbsp;</option>
		<?php for($i=$data['min'];$i<=$data['max'];$i+=$data['step']) {
			echo '<option value="'.$i.'"'.($i==$data['value'][1] ? ' selected="selected"' : '').'>'.$i.'</option>';
		}
		if($i!=$data['max']) echo '<option value="'.$data['max'].'"'.($data['max']==$data['value'][1] ? ' selected="selected"' : '').'>'.$data['max'].'</option>';
		?>
		</select>
		<input type="hidden" name="<?=$data['id']?>" id="cir<?=$index?>" />
		<?php
	}

	/* Выпадающий список (одно значение из множества) */
	private static function _renderList($data) {
		echo '<select name="'.$data['id'].'"><option value="">&nbsp;</option>';
		foreach($data['list'] as $item) {
			if(!$item) $item='&nbsp;';
			echo '<option value="'.$item.'"'.($data['value']==$item ? ' selected="selected"' : '').'>'.$item.'</option>';
		}
		echo '</select>';
	}

}
?>