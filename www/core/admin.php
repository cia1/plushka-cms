<?php
//Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\core;

/**
 * Отвечает за генерацию кнопок административного интерфейса
 */
class Admin {

	/** @var int Порядковый номер кнопки в группе */
	private $_index=0;

	/**
	 * Добавляет кнопку в группу
	 * @param string $link Ссылка на страницу админки
	 * @param string $image Иконка кнопки (/admin/pulbic/icon/{$image}.png)
	 * @param string $title Текст всплывающей подсказки
	 * @param string|null $alt Значение атрибута ALT для HTML-тега <img>
	 * @param string|null $javaScript JavaScript-код, который должен будет вызван при нажатии кнопки
	 */
	public function add($link,$image='setting',$title='Администрировать элемент',$alt=null,$javaScript=null) {
		$data=array(1=>$link,2=>$image,3=>$title,4=>$alt,5=>$javaScript);
		$this->render($data);
	}

	/**
	 * Генерирует и выводит HTML-код кнопки
	 * @param array $item Массив, содержащий информацию о кнопке
	 */
	public function render($item=null) {
		static $_head;
		static $_count;
		if($item===null) return;
		$_count++;
		$link=$item[1];
		if($link[0]=='?') $link=plushka::url().'admin/index.php'.$link; else $link=plushka::url().'admin/'.$link;
		$link.='&_front&_lang='._LANG;
		echo '<a href="',$link,'" onclick="';
		if(isset($item[5]) && $item[5]) echo $item[5],';';
		echo 'return $.adminDialog(this);" class="_adminItem"><img src="',plushka::url(),'admin/public/icon/',$item[2],'16.png" alt="';
		if(isset($item[4]) && $item[4]!==null) echo str_replace('"','',$item[4]); else echo str_replace('"','',$item[3]);
		echo '" title="',str_replace('"','',$item[3]),'" index="',($this->_index++),'" style="width:16px;height:16px;" id="_adminButton',$_count,'" /></a>';
	}
}