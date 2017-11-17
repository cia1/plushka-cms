<?php
//Расширенная форма: содержит drag&drop поле для загрузки файлов
core::import('core/form');
class formDragDrop extends form {

	public function __construct($namespace=null) {
		parent::__construct($namespace);
	}

	/* Поле для загрузки файла (<input type="file")
	$name - имя поля формы, $label - отображаемый заголовок, $fileCount - разрешить выбирать несколько файлов, $html - произвольный текст, который будет присоединён к тегу <input> */
	public function fileDragDrop($name,$label,$fileCount=1,$jsCallBack=null) {
		controller::$self->js('jquery.min');
		controller::$self->js('formDragDrop');
		core::language('formDragDrop');
		controller::$self->js('LNGFileAlreadyUploaded');
		controller::$self->js('LNGFileMaximumAlreadyUploaded');
		$this->_data.='<dt class="fileDragDrop">'.$label.':</dt><dd class="fileDragDrop">'.$this->getFileDragDrop($name,$fileCount,$jsCallBack).'</dd>';
	}

	/* Возвращает HTML-код поля для загрузки файла */
	public function getFileDragDrop($name,$fileCount=1,$jsCallBack=null) {
		static $_index;
		if(!$_index) {
			$html='<link href="'.core::url().'public/css/formDragDrop.css" rel="stylesheet" />';
			core::language('formDragDrop');
			echo core::js('jquery.min');
			echo core::js('formDragDrop');
			echo core::js('LNGFileAlreadyUploaded');
			echo core::js('LNGFileMaximumAlreadyUploaded');
			$path=core::path().'tmp/upload/';
			$d=opendir($path);
			$time=time()-3600;
			while($f=readdir($d)) {
				if($f=='.' || $f=='..') continue;
				$f=$path.$f;
				$t=filemtime($f);
				if($time>$t) unlink($f);
			}
			closedir($d);
			$_SESSION['_uploadTimeLimit']=time()+240;
			$_SESSION['_uploadFolder']='tmp/upload/';
			$_SESSION['_uploadList']=array();
		} else $html='';
		$path=core::path().'tmp/upload/';
		if(!is_dir($path)) mkdir($path);
		$_index++;
		$html.='<div class="fileDropBox" id="fileDropBox_'.$_index.'">'.LNGDropOrClick.'.<br /><input type="file"';
		if($fileCount>1) $html.=' multiple="multiple"';
		$html.=' /></div>';
		$html.='<script>fileDragDrop("'.$this->_namespace.'","'.$name.'",document.getElementById("fileDropBox_'.$_index.'"),'.$fileCount.($jsCallBack ? ',"'.$jsCallBack.'"' : '').');</script>';
		return $html;
	}

}