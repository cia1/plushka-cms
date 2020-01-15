<?php
namespace plushka\admin\model;

//Модель произвольный HTML-код
class Html {

	private $_fileName;
	public $html;
	public $section;

	//Генерирует имя файла для указанной секции
	public static function fileNameBySection($section) {
		$fileName=$section;
		$d=opendir(plushka::path().'data/widgetHtml');
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
	public function init($section=null) {
		$this->html='';
		$this->section=$section;
		return true;
	}

	//Загружает текст из файла
	public function load($fileName) {
		$fileName=str_replace(array('/','..'),'',$fileName);
		$this->_fileName=$fileName;
		$f=plushka::path().'data/widgetHtml/'.$fileName.'_'._LANG.'.html';
		if(!file_exists($f)) {
			$cfg=plushka::config();
			$f=plushka::path().'data/widgetHtml/'.$fileName.'_'.$cfg['languageDefault'].'.html';
		}
		if(file_exists($f)) $this->html=file_get_contents($f); else $this->html='';
	}

	//Возвращает экземпляр класса form для редактирования текста
	public function form() {
		$form=plushka::form();
		$form->hidden('section',$this->section);
		$form->hidden('fileName',$this->_fileName);
		$form->editor('html','Текст',$this->html);
		$form->submit();
		return $form;
	}

	//Сохраняет текс в файл
	public function save($fileName=null) {
		$f=fopen(plushka::path().'data/widgetHtml/'.$fileName.'_'._LANG.'.html','w');
		fwrite($f,$this->html);
		fclose($f);
		return true;
	}

}
