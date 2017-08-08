<?php class widgetLanguage extends widget {

	public function __invoke() {
		$cfg=core::config();
		$link=substr($_SERVER['REQUEST_URI'],strlen(core::url()));
		if(_LANG!=$cfg['languageDefault']) $link=substr($link,($link[2]=='?' ? 2 : 3));
		$this->language=$cfg['languageList'];
		foreach($this->language as $i=>$item) {
			if($item==$cfg['languageDefault']) $lang=''; else $lang=$item.'/';
			$this->language[$i]=array('alias'=>$item,'link'=>core::url().$lang.$link,'title'=>$item);
		}
		$this->language[0]['title']='english';
		$this->language[1]['title']='русский';
		return true;
	}

	public function render($view=null) {
		foreach($this->language as $item) { ?>
			<a href="<?=$item['link']?>"><img src="<?=core::url()?>public/flag/<?=$item['alias']?>.png" alt="<?=$item['title']?>" title="<?=$item['title']?>" /></a>
		<?php }
	}

}