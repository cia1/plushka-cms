<?php
/* Внимание! Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
Класс предназначен для генерации кнопок административного интерфейса */
class admin {
	private $_index=0;

	/* $link - ссылка на страницу админки, $image - изображение (/admin/public/icon/XXX16.png) */
	public function add($link,$image='setting',$title='Администрировать элемент',$alt=null,$javaScript=null) {
		$data=array(1=>$link,2=>$image,3=>$title,4=>$alt,5=>$javaScript);
		$this->render($data);
	}

	public function render($item=null) {
		static $_head;
		static $_count;
		if($item) {
			$_count++;
			$link=$item[1];
			if($link[0]=='?') $link=core::url().'admin/index.php'.$link; else $link=core::url().'admin/'.$link;
			$link.='&_front&_lang='._LANG;
			echo '<a href="',$link,'" onclick="';
			if($item[5]) echo $item[5],';';
			echo 'return $.adminDialog(this);" class="_adminItem"><img src="',core::url(),'admin/public/icon/',$item[2],'16.png" alt="';
			if(isset($item[4]) && $item[4]!==null) echo str_replace('"','',$item[4]); else echo str_replace('"','',$item[3]);
			echo '" title="',str_replace('"','',$item[3]),'" index="',($this->_index++),'" style="width:16px;height:16px;" id="_adminButton',$_count,'" /></a>';
		}
	}
} ?>