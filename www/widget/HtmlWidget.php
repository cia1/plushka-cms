<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/**
 * Произвольный HTML-код
 * @property-read string $options Имя файла с текстом
 */
class HtmlWidget extends Widget {

	public function __invoke(): bool { return true; }

	public function render($view): void {
		$f=plushka::path().'data/widgetHtml/'.$this->options.'_'._LANG.'.html';
		if(!file_exists($f)) {
			$cfg=plushka::config();
			$f=plushka::path().'data/widgetHtml/'.$this->options.'_'.$cfg['languageDefault'].'.html';
		}
		if(file_exists($f)===true) /** @noinspection PhpIncludeInspection */ include($f);
	}

	public function adminLink(): array {
		return [
			['html.*','?controller=html&action=item&id='.$this->options,'edit','Редактировать текст']
		];
	}

}