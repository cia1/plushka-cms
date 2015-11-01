<?php
/* Произвольный HTML-код
string $options - имя файла с текстом */
class widgetHtml extends widget {

	public function __invoke() { return true; }

	public function render() {
		$f=core::path().'data/widgetHtml/'.$this->options.'_'._LANG.'.html';
		if(!file_exists($f)) {
			$cfg=core::config();
			$f=core::path().'data/widgetHtml/'.$this->options.'_'.$cfg['languageDefault'].'.html';
		}
		include($f);
	}

	public function adminLink() {
		return array(
			array('html.*','?controller=html&action=item&id='.$this->options,'edit','Редактировать текст')
		);
	}

}
?>