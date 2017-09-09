<?php
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
	public function link($title,$link,$colspan=0,$html=null) {
		return $this->text('<a href="'.core::link($link).'">'.$title.'</a>',$colspan,$html);
	}

	/* Добавляет ячейку, содержащую кнопки "выше" и "ниже" */
	public function upDown($link,$index=null,$count=null) {
		$this->_data.='<td width="60px" align="center">';
		if($index>1 || $index===null) {
			$this->_data.='<a href="'.core::link($link.'up').'"><img src="'.core::url().'admin/public/icon/up16.png" alt="Выше" title="Поднять выше" /></a>';
		}
		if($count===null || $index!=$count) {
			$this->_data.='&nbsp;<a href="'.core::link($link.'down').'"><img src="'.core::url().'admin/public/icon/down16.png" alt="Ниже" title="Спустить ниже" /></a>';
		}
		$this->_data.='</td>';
		$this->_index++;
	}

	/* Добавляет ячейку, содержащую кнопку "удалить" */
	public function delete($link,$confirm='Подтвердите удаление') {
		$this->_data.='<td width="40px" align="center">'
		.'<a href="'.core::link($link).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.core::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/* Добавляет ячейку, сдержащую две кнопки "изменить" и "удалить" */
	public function editDelete($link,$confirm='Подтвердите удаление') {
		if(strpos($link,'action=')!==false) $action=true; else $action=false;
		$this->_data.='<td width="40px" align="center">'
		.'<a href="'.core::link($link.($action ? 'Edit' : '&action=edit')).'"><img src="'.core::url().'admin/public/icon/edit16.png" alt="изменить" title="Редактировать..." /></a>'
		.'<a href="'.core::link($link.($action ? 'Delete' : '&action=delete')).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.core::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/* Добавляет ячейку, содержащую две кнопки "изменить" и "удалить", отличается от $this->editDelete() видом формируемой ссылки */
	public function itemDelete($link,$confirm='Подтвердите удаление') {
		if(strpos($link,'action=')!==false) $action=true; else $action=false;
		$this->_data.='<td width="60px" align="center">'
		.'<a href="'.core::link($link.($action ? 'Item' : '&action=item')).'"><img src="'.core::url().'admin/public/icon/edit16.png" alt="Изменить" title="Редактировать" /></a>'
		.'&nbsp;<a href="'.core::link($link.($action ? 'Delete' : '&action=delete')).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.core::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
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