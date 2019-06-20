<?php
namespace plushka\widget;
use plushka;
use plushka\core\Widget;

/* Произвольный HTML-код
string $options - имя файла с текстом */
class HtmlWidget extends Widget {

	public function __invoke() { return true; }

	public function render($view): void {
		$f=plushka::path().'data/widgetHtml/'.$this->options.'_'._LANG.'.html';
		if(!file_exists($f)) {
			$cfg=plushka::config();
			$f=plushka::path().'data/widgetHtml/'.$this->options.'_'.$cfg['languageDefault'].'.html';
		}
		$f=plushka::path().'data/widgetHtml/'.$this->options.'_'._LANG.'.html';
		if(file_exists($f)) include($f);
	}

	public function adminLink(): array {
		return array(
			array('html.*','?controller=html&action=item&id='.$this->options,'edit','Редактировать текст')
		);
	}

}
