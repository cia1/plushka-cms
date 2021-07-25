<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;

/**
 * Конструктор HTML-таблиц (<table>)
 */
class Table {

	private $_count=0; //Количество столбцов таблицы
	private $_index=0;
	private $_trHtml=''; //HTML-код для тега <tr>
	private $_data='<table cellpadding="0" cellspacing="0" class="admin"';

	/**
	 * @param string|null $html Произвольный HTML-код, присоединяемый к тегу <table>
	 */
	public function __construct(string $html=null) {
		if($html!==null) $this->_data.=$html;
		$this->_data.='><tr>';
	}

	/**
	 * Добавляет заголовок к таблице (<tr><th>)
	 * @param string $title Текст заголовка
	 * @param string|null $html Произвольный HTML-код, добавляемый к тегу
	 */
	public function th(string $title,string $html=null): void {
		if(substr($title,0,9)==='checkbox[') {
			$title=substr($title,9,strlen($title)-10);
			$title='<input type="checkbox" onclick="$(\'.check'.$title.'\').attr(\'checked\',this.checked);" />';
		}
		$this->_data.='<th'.($html ? ' '.$html : '').'>'.$title.'</th>';
		$this->_count++;
	}

	/**
	 * Устанавливает сразу все заголовки
	 * @param array|string $titleList Заголовки, если строка, то разделённая символом "|"
	 */
	public function rowTh($titleList): void {
		if(is_array($titleList)===true) {
			for($i=0,$cnt=count($titleList);$i<$cnt;$i++) {
				if(is_array($titleList[$i])===true) $this->th($titleList[$i][0],$titleList[$i][1]);
				else $this->th($titleList[$i]);
			}
		} else {
			$titleList=explode('|',$titleList);
			for($i=0,$cnt=count($titleList);$i<$cnt;$i++) $this->th($titleList[$i]);
		}
	}

	/**
	 * Устанавливает HTML-код, который должен быть "приписан" к тегу <tr>
	 * @param string $html HTML-код
	 */
	public function trHtml(string $html): void {
		$this->_trHtml=$html;
	}

	/**
	 * Добавляет текст во всю ширину таблицы
	 * @param string $text
	 */
	public function row(string $text): void {
		$this->_data.='<tr><td colspan="'.$this->_count.'">'.$text.'</td></tr>';
	}

	/** 
	 * Добавляет ячейку, содержащую флажок
	 * @param string $name Имя поля
	 * @param string $value Значение поля
	 * @param string|null $namespace Имя контроллера
	 * @param int $colspan Количество объединяемых ячеек
	 * @param string|null $html HTML-код, присоединяемый к тегу <input>
	 */
	public function checkbox(string $name,string $value,$namespace=null,int $colspan=0,string $html=null): void {
		$this->_tr();
		$this->_data.='<td style="width:30px;text-align:center;" '.$html;
		if($colspan>0) {
			$this->_data.='colspan="'.$colspan.'"';
			$this->_index+=$colspan;
		} else $this->_index++;
		$this->_data.='><input type="checkbox" name="'.($namespace ? $namespace.'['.$name.']' : $name).'[]" value="'.$value.'" class="check'.$name.'" /></td>';
	}

	/**
	 * Добавляет ячейку с произвольным текстом
	 * @param string $text
	 * @param int $colspan Количество объединяемых ячеек
	 * @param string|null $html HTML-код, присоединяемый к тегу <input>
	 */
	public function text($text,int $colspan=0,string $html=null): void {
		$this->_tr();
		$this->_data.='<td '.$html;
		if($colspan>0) {
			$this->_data.=' colspan="'.$colspan.'"';
			$this->_index+=$colspan;
		} else $this->_index++;
		$this->_data.='>'.$text.'</td>';
	}

	/**
	 * Добавляет ячейку, содержащую ссылку на какую-либо страницу админки
	 * @param string $link Ссылка
	 * @param string $title Якорь ссылки
	 * @param int $colspan Количество объединяемых ячеек
	 * @param string|null $html HTML-код, присоединяемый к тегу <input>
	 */
	public function link(string $link,string $title,int $colspan=0,string $html=null): void {
		$this->text('<a href="'.plushka::linkAdmin($link).'">'.$title.'</a>',$colspan,$html);
	}

	/**
	 * Добавляет ячейку, содержащую кнопки "выше" и "ниже".
	 * @param string $params URL-параметры
	 * @param int|null $index Порядковый номер элемента
	 * @param int|null $count Общее количество элементов
	 * @param |null $actionAlias Имя действия (к нему будет добавлено "Up"/"Down")
	 */
	public function upDown(string $params,int $index=null,int $count=null,$actionAlias=null): void {
		$this->_data.='<td style="width:60px;text-align:center;">';
		$params='&'.$params;
		if($index===null || $index>1) {
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

	/**
	 * Добавляет ячейку, содержащую кнопку "удалить"
	 * @param string $params URL-параметры
	 * @param string|null $actionAlias Имя действия
	 * @param string $confirm Текст сообщения подтверждения
	 */
	public function delete(string $params,string $actionAlias=null,string $confirm='Подтвердите удаление'): void {
		$params='&'.$params;
		$link=plushka::$controller->url[0].'/'.($actionAlias===null ? 'delete' : $actionAlias.'Delete');
        /** @noinspection HtmlUnknownTarget */
        $this->_data.='<td style="width:40px;text-align:center;">'
		.'<a href="'.plushka::linkAdmin($link.$params).'" onclick="return confirm(\''.$confirm.'\');">
		 <img src="'.plushka::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/**
	 * Добавляет ячейку, сдержащую две кнопки "изменить" и "удалить"
	 * @param string $params URL-параметры
	 * @param string|null $actionAlias Имя действия (к нему будет добавлено "Edit"/"Delete")
	 * @param string $confirm Текст сообщения подтверждения
	 */
	public function editDelete(string $params,string $actionAlias=null,string $confirm='Подтвердите удаление'): void {
		$params='&'.$params;
		$linkEdit=$linkDelete=plushka::$controller->url[0].'/';
		if($actionAlias===null) {
			$linkEdit.='edit';
			$linkDelete.='delete';
		} else {
			$linkEdit.=$actionAlias.'Edit';
			$linkDelete.=$actionAlias.'Delete';
		}
        /** @noinspection HtmlUnknownTarget */
		$this->_data.='<td style="width:40px;text-align:center;">'
		.'<a href="'.plushka::linkAdmin($linkEdit.$params).'"><img src="'.plushka::url().'admin/public/icon/edit16.png" alt="изменить" title="Редактировать..." /></a>'
		.'<a href="'.plushka::linkAdmin($linkDelete.$params).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.plushka::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/**
	 * Добавляет ячейку, содержащую две кнопки "изменить" и "удалить", отличается от $this->editDelete() видом формируемой ссылки
	 * @param string $params URL-параметры
	 * @param string|null $actionAlias Имя действия (к нему будет добавлено "Item"/"Delete")
	 * @param string $confirm Текст сообщения подтверждения
	 */
	public function itemDelete(string $params,string $actionAlias=null,string $confirm='Подтвердите удаление'): void {
		$params='&'.$params;
		$linkItem=$linkDelete=plushka::$controller->url[0].'/';
		if($actionAlias===null) {
			$linkItem.='item';
			$linkDelete.='delete';
		} else {
			$linkItem.=$actionAlias.'Item';
			$linkDelete.=$actionAlias.'Delete';
		}
        /** @noinspection HtmlUnknownTarget */
		$this->_data.='<td style="width:60px;text-align:center;">'
		.'<a href="'.plushka::linkAdmin($linkItem.$params).'"><img src="'.plushka::url().'admin/public/icon/edit16.png" alt="Изменить" title="Редактировать" /></a>'
		.'&nbsp;<a href="'.plushka::linkAdmin($linkDelete.$params).'" onclick="return confirm(\''.$confirm.'\');"><img src="'.plushka::url().'admin/public/icon/delete16.png" alt="Удалить" title="Удалить" /></a>'
		.'</td>';
		$this->_index++;
	}

	/**
	 * Выводит HTML-представление таблицы
	 */
	public function render(): void {
		echo $this->_data;
		echo '</tr></table>';
	}

	/**
	 * Отслеживает начало новой строки
	 */
	private function _tr(): void {
		if($this->_index===$this->_count) $this->_index=0;
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