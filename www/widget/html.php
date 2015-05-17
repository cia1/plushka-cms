<?php
/* Произвольный HTML-код
string $options - имя файла с текстом */
class widgetHtml extends widget {

	public function action() { return true; }

	public function render($view=null) {
		include(core::path().'data/widgetHtml/'.$this->options.'.html');
	}

	public function adminLink() {
		return array(
			array('html.*','?controller=html&action=item&id='.$this->options,'edit','Редактировать текст')
		);
	}

}
?>