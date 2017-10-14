<?php
//Модель произвольный HTML-код
class html {

	private $_fileName;
	public $html;

	//Генерирует имя файла для указанной секции
	public static function fileNameBySection($section) {
		$fileName=$section;
		$d=opendir(core::path().'data/widgetHtml');
		$index=1;
		$len=strlen($section)+1;
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(substr($f,0,$len)==$section.'.') {
				$i=(int)substr($f,$len);
				if($i>=$index) $index=$i+1;
			}
		}
		$fileName=$section.'.'.$index;
		return $fileName;
	}

	//Инициализирует пустой текст
	public function init() {
		$this->html='';
		return true;
	}

	//Загружает текст из файла
	public function load($fileName) {
		$fileName=str_replace(array('/','..'),'',$fileName);
		$this->_fileName=$fileName;
		$f=core::path().'data/widgetHtml/'.$fileName.'_'._LANG.'.html';
		if(!file_exists($f)) {
			$cfg=core::config();
			$f=core::path().'data/widgetHtml/'.$fileName.'_'.$cfg['languageDefault'].'.html';
		}
		if(file_exists($f)) $this->html=file_get_contents($f); else $this->html='';
		return true;
	}

	//Возвращает экземпляр класса form для редактирования текста
	public function form() {
		$form=core::form();
		$form->hidden('fileName',$this->_fileName);
		$form->editor('html','Текст',$this->html);
		$form->submit();
		return $form;
	}

	//Сохраняет текс в файл
	public function save($fileName=null) {
		$f=fopen(core::path().'data/widgetHtml/'.$fileName.'_'._LANG.'.html','w');
		fwrite($f,$this->html);
		fclose($f);
		return true;
	}

}