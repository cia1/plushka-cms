<?php class widgetLanguage extends widget {

	public function __invoke() {
		$this->language=core::config('language');
		$this->language=$this->language['lang'];
		$link=self::_getLink();
		foreach($this->language as $id=>$item) {
			$this->language[$id]=array('link'=>core::link($link,$id),'title'=>$item);
		}
		return true;
	}

	public function render($view=null) {
		foreach($this->language as $id=>$item) { ?>
			<a href="<?=$item['link']?>"><img src="<?=core::url()?>public/flag/<?=$id?>.png" alt="<?=$item['title']?>" title="<?=$item['title']?>" /></a>
		<?php }
	}

	public function adminLink() {
		return array(
			array('language.rule','?controller=language&action=setting','setting','Правила переключения зыков')
		);
	}

	//Возвращает ссылку без языка для "переключателя" с учтёом настройки мультиязычных страниц
	private static function _getLink() {
		$link=$_GET['corePath'];
		unset($link[count($link)-1]);
		$link=implode('/',$link);
		$rule=core::config('language');
		$rule=$rule['rule'];

		if(in_array($link,$rule)) return $link;
		$link=$_SERVER['REQUEST_URI'];
		return substr($_SERVER['REQUEST_URI'],strlen(core::url()));
	}

}