<?php
namespace plushka\admin\model;
use plushka\admin\core\plushka;
use plushka\admin\core\FormEx;

/**
 * Модель-помощник по управлению блоками HTML-кода
 */
class Html {

	/** @var string HTML-код */
	public $html;
	/** @var string|null Имя секции */
	public $section;

	/** @var string */
	private $_fileName;

	/**
	 * Генерирует имя файла для нового виджета в секции
	 * @param string $section Имя секции
	 * @return string
	 */
	public static function fileNameBySection(string $section): string {
		$d=opendir(plushka::path().'data/widgetHtml');
		$index=1;
		$len=strlen($section)+1;
		while($f=readdir($d)) {
			if($f==='.' || $f==='..') continue;
			if(substr($f,0,$len)===$section.'.') {
				$i=(int)substr($f,$len);
				if($i>=$index) $index=$i+1;
			}
		}
		$fileName=$section.'.'.$index;
		return $fileName;
	}

	/**
	 * Инициирует новый виджет
	 * @param string|null $section
	 * @return bool
	 */
	public function init(string $section=null): void {
		$this->html='';
		$this->section=$section;
	}

	/**
	 * Загружает текст из файла
	 * @param string $fileName Имя файла
	 */
	public function load(string $fileName): void {
		$fileName=str_replace(['/','..'],'',$fileName);
		$this->_fileName=$fileName;
		$f=plushka::path().'data/widgetHtml/'.$fileName.'_'._LANG.'.html';
		if(file_exists($f)===false) { //попробовать загрузить текст для языка по умолчанию, если мультиязычного нет
			$cfg=plushka::config();
			$f=plushka::path().'data/widgetHtml/'.$fileName.'_'.$cfg['languageDefault'].'.html';
		}
		if(file_exists($f)===true) $this->html=file_get_contents($f); else $this->html='';
	}

	/**
	 * Возвращает форму для редактирования виджета
	 * @return FormEx
	 */
	public function form(): FormEx {
		$form=plushka::form();
		$form->hidden('section',$this->section);
		$form->hidden('fileName',$this->_fileName);
		$form->editor('html','Текст',$this->html);
		$form->submit();
		return $form;
	}

	/**
	 * Сохраняет текст в файл
	 * @param string|null $fileName имя файла, если не задано, то сохраняет в загруженный ранее файла
	 * @return bool Удалось ли сохранить
	 */
	public function save($fileName=null): bool {
		if($fileName!==null) $this->_fileName=$fileName;
		if($this->_fileName===null && $this->section!==null) $this->_fileName=self::fileNameBySection($this->section);
		$f=fopen(plushka::path().'data/widgetHtml/'.$this->_fileName.'_'._LANG.'.html','w');
		if($f===false) return false;
		fwrite($f,$this->html);
		fclose($f);
		return true;
	}

}