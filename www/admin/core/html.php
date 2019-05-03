<?php
namespace plushka\admin\core;

/* Конструктор HTML-таблиц (<table>) */
class table {

	private $_count=0; //Количество столбцов таблицы
	private $_index=0;
	private $_trHtml=''; //HTML-код для тега <tr>
	private $_data='<table cellpadding="0" cellspacing="0" class="admin"';

	/* Если задан $html, то присоединить его к тегу <table> */
	public function __construct($html=null) {
		if($html) $this->_data.=$html;
		$this->_data.='><tr>';
	}

	/* Добавляет заголовок к таблице (<tr><th>) */
	public function th($title,$html='') {
		if(substr($title,0,9)=='checkbox[') {
			$title=substr($title,9,strlen($title)-10);
			$title='<input type="checkbox" onclick="$(\'.check'.$title.'\').attr(\'checked\',this.checked);" />';
		}
		$this->_data.='<th'.($html ? ' '.$html : '').'>'.$title.'</th>';
		$this->_count++;
	}

	/* Устанавливает сразу все заголовки. $a - либо массив либо строка, разделённая "|" */
	public function rowTh($a) {
		if(is_array($a)) {
			for($i=0,$cnt=count($a);$i<$cnt;$i++) {
				if(is_array($a[$i])) $this->th($a[$i][0],$a[$i][1]); else $this->th($a[$i]);
			}
		} else {
			$a=explode('|',$a);
			for($i=0,$cnt=count($a);$i<$cnt;$i++) $this->th($a[$i]);
		}
	}

	/* Устанавливает HTML-код, который должен быть "приписан" к тегу <tr> */
	public function trHtml($html) {
		$this->_trHtml=$html;
	}

	/* Добавляет текст $text во всю ширину таблицы */
	public function row($text) {
		$this->_data.='<tr><td colspan="'.$this->_count.'">'.$text.'</td></tr>';
	}

	//добавляет ячейку, содержащую флажок
	public function checkbox($controller=null,$name,$value,$colspan=0,$html=null) {
		$this->_tr();
		$this->_data.='<td style="width:30px;text-align:center;" '.$html;
		if($colspan) {
			$this->_data.='colspan="'.$colspan.'"';
			$this->_index+=$colspan;
		} else $this->_index++;
		$this->_data.='><input type="checkbox" name="'.($controller ? $controller.'['.$name.']' : $name).'[]" value="'.$value.'" class="check'.$name.'" /></td>';
	}
	/* Добавляет ячейку с произвольным текстом */
	public function text($text,$colspan=0,$html=null) {
		$this->_tr();
		$this->_data.='<td '.$html;
		if($colspan) {
			$this->_data.=' colspan="'.$colspan.'"';
			$this->_index+=$colspan;
		} else $this->_index++;
		$this->_data.='>'.$text.'</td>';
	}

	/* Добавляет ячейку, содержащую ссылку на какую-либо страницу админки */
	public function link($link,$title,$colspan=0,$html=null) {
		return $this->text('<a href="'.plushka::linkAdmin($link).'">'.$title.'</a>',$colspan,$html);
	}

	/* Добавляет ячейку, содержащую кнопки "выше" и "ниже" */
	public function upDown($params,$index=null,$count=null,$actionAlias=null) {
		$this->_data.='<td width="60px" align="center">';
		$params='&'.$params;
		if($index>1 || $index===null) {
			$link=plushka::$controller->url[0].'/'.($actionAlias===null ? 'up' : $actionAlias.'Up');
			$this->_data.='<a href="'.plushka::linkAdmin($link.$params).'"><img src="'.plushka::url().'admin/public/icon/up16.png" alt="Выше" title="Поднять выше" /></a>';
		}
		if($count===null || $index<$count) {
			$link=plushka::$controller->url[0].'/'.($actionAlias===null ? 'down' : $actionAlias.'Down');
			$this->_data.='&nbsp;<a href="'.plushka::linkAdmin($link.$params).'"><img src="'.plushka::url().'admin/public/icon/down16.png" alt="Ниже" title="Спустить ниже" /></a>';
		}
		$this->_data.='</td>';
		$this->_index++;
	}

	/* Добавляет ячейку, содержащую кнопку "удалить" */
	public function delete($params,$actionAlias='delete',$confirm='Подтвердите удаление') {
		$params='&'.$params;
		$link=plushka::$controller->url[0].'/'.($actionAlias===null ? 'delete' : $actionAlias.'Delete');
		$this->_data.='<td width="40px" align="center">'
		.'<a href="'.plushka::linkAdmin($link.$params).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.plushka::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/* Добавляет ячейку, сдержащую две кнопки "изменить" и "удалить" */
	public function editDelete($params,$actionAlias='edit',$confirm='Подтвердите удаление') {
		$params='&'.$params;
		$linkEdit=$linkDelete=plushka::$controller->url[0].'/';
		if($actionAlias===null) {
			$linkEdit.='item';
			$linkDelete.='delete';
		} else {
			$linkEdit.=$actionAlias.'Item';
			$linkDelete.=$actionAlias.'Delete';
		}
		$this->_data.='<td width="40px" align="center">'
		.'<a href="'.plushka::linkAdmin($linkEdit.$params).'"><img src="'.plushka::url().'admin/public/icon/edit16.png" alt="изменить" title="Редактировать..." /></a>'
		.'<a href="'.plushka::linkAdmin($linkDelete.$params).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.plushka::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/* Добавляет ячейку, содержащую две кнопки "изменить" и "удалить", отличается от $this->editDelete() видом формируемой ссылки */
	public function itemDelete($params,$actionAlias=null,$confirm='Подтвердите удаление') {
		$params='&'.$params;
		$linkItem=$linkDelete=plushka::$controller->url[0].'/';
		if($actionAlias===null) {
			$linkItem.='item';
			$linkDelete.='delete';
		} else {
			$linkItem.=$actionAlias.'Item';
			$linkDelete.=$actionAlias.'Delete';
		}
		$this->_data.='<td width="60px" align="center">'
		.'<a href="'.plushka::linkAdmin($linkItem.$params).'"><img src="'.plushka::url().'admin/public/icon/edit16.png" alt="Изменить" title="Редактировать" /></a>'
		.'&nbsp;<a href="'.plushka::linkAdmin($linkDelete.$params).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.plushka::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/* Выводит HTML-представление таблицы */
	public function render() {
		echo $this->_data;
		echo '</tr></table>';
	}

	/* Отслеживает начало новой строки */
	private function _tr() {
		if($this->_index==$this->_count) $this->_index=0;
		if(!$this->_index) {
			$this->_data.='</tr><tr';
			if($this->_trHtml) {
				$this->_data.=' '.$this->_trHtml;
				$this->_trHtml='';
			}
			$this->_data.='>';
		}
	}

}