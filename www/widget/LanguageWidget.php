<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

class LanguageWidget extends Widget {

	public function __invoke() {
		$this->language=plushka::config('language');
		$this->language=$this->language['lang'];
		$link=self::_getLink();
		foreach($this->language as $id=>$item) {
			$this->language[$id]=array('link'=>plushka::link($link,$id),'title'=>$item);
		}
		return true;
	}

	public function render($view): void {
		foreach($this->language as $id=>$item) { ?>
			<a href="<?=$item['link']?>"><img src="<?=plushka::url()?>public/flag/<?=$id?>.png" alt="<?=$item['title']?>" title="<?=$item['title']?>" /></a>
		<?php }
	}

	public function adminLink(): array {
		return array(
			array('language.rule','?controller=language&action=setting','setting','Правила переключения зыков')
		);
	}

	//Возвращает ссылку без языка для "переключателя" с учётом настройки мультиязычных страниц
	private static function _getLink() {
		$link=$_GET['corePath'];
		unset($link[count($link)-1]);
		$link=implode('/',$link);
		$rule=plushka::config('language');
		$rule=$rule['rule'];

		if(in_array($link,$rule)) return $link;
		$link=$_SERVER['REQUEST_URI'];
		$len=strlen(plushka::url(true));
		if($link[$len-1]=='?') $len--;
		return substr($_SERVER['REQUEST_URI'],$len);
	}

}
