<?php
/* Произвольный HTML-код
string $options - имя файла с текстом */
class widgetHtml extends widget {

	public function __invoke() { return true; }

	public function render() {
		include(core::path().'data/widgetHtml/'.$this->options.'.html');
	}

	public function adminLink() {
		return array(
			array('html.*','?controller=html&action=item&id='.$this->options,'edit','Редактировать текст')
		);
	}

}
?>