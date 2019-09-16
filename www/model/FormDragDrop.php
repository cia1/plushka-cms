<?php
namespace plushka\model;
use plushka\core\plushka;
use plushka\core\Form;

/**
 * Расширенная форма: содержит drag&drop поле для загрузки файлов
 */
class FormDragDrop extends Form {

    /**
     * Поле выбора файла/файлов Drag&Drop
     * @param string $name Имя поля
     * @param string $label Заголовок
     * @param int $fileCount Количество файлов, которые можно загрузить через форму
     * @param string|null $jsCallBack JS-скрипт, который будет вызван при выборе файла
     */
	public function fileDragDrop(string $name,string $label,int $fileCount=1,string $jsCallBack=null): void {
		plushka::$controller->js('jquery.min','defer');
		plushka::$controller->js('formDragDrop','defer');
		plushka::language('formDragDrop');
		plushka::$controller->js('LNGFileAlreadyUploaded');
		plushka::$controller->js('LNGFileMaximumAlreadyUploaded');
		$this->_data.='<dt class="fileDragDrop">'.$label.':</dt><dd class="fileDragDrop">'.$this->getFileDragDrop($name,$fileCount,$jsCallBack).'</dd>';
	}
    /**
     * Возвращает HTML-код поля загрузки файла(ов)
     * @param string $name Имя поля
     * @param int $fileCount Количество файлов, которые можно загрузить через форму
     * @param string|null $jsCallBack JS-скрипт, который будет вызван при выборе файла
     * @return string
     */
	public function getFileDragDrop(string $name,int $fileCount=1,string $jsCallBack=null): string {
		static $_index=0;
		if($_index===0) {
			$html='<link href="'.plushka::url().'public/css/formDragDrop.css" rel="stylesheet" />';
			plushka::language('formDragDrop');
			echo plushka::js('jquery.min','defer');
			echo plushka::js('formDragDrop','defer');
			echo plushka::js('LNGFileAlreadyUploaded');
			echo plushka::js('LNGFileMaximumAlreadyUploaded');
			$path=plushka::path().'tmp/upload/';
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
		$path=plushka::path().'tmp/upload/';
		if(!is_dir($path)) mkdir($path);
		$_index++;
		$html.='<div class="fileDropBox" id="fileDropBox_'.$_index.'">'.LNGDropOrClick.'.<br /><input type="file"';
		if($fileCount>1) $html.=' multiple="multiple"';
		$html.=' /></div>';
        /** @noinspection BadExpressionStatementJS */
        $html.='<script defer="defer">fileDragDrop(
            "'.$this->_namespace.'",
            "'.$name.'",
            document.getElementById("fileDropBox_'.$_index.'"),'.$fileCount;
        if($jsCallBack) $html.=',"'.$jsCallBack.'"';
        $html.=')</script>';
		return $html;
	}

}
