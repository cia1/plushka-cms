<?php
/* Индивидуальные шаблоны для разных страниц сайта */
class sController extends controller {

	public function right() {
		return array(
			'index'=>'template.*'
		);
	}

	/* Соответствие шаблонов страницам сайта */
	public function actionIndex() {
		//Настройки хранятся в основном конфигурационном файле (_core.php)
		$t=core::config();
		$t=$t['template'];
		$s='';
		foreach($t as $link=>$template) {
			$s.=$link.'='.$template."\n";
		}
		$f=core::form();
		$f->textarea('link','Шаблоны',$s);
		$f->submit();
		$this->cite='В списке выше вы можете указать какой использовать шаблон для каждой отдельной страницы. Шаблон &laquo;default&raquo; используется по умолчанию, его указывать не нужно.<br />Строки должны быть в следующем виде: <b>ОТНОСИТЕЛЬНАЯ_ССЫЛКА=НАЗВАНИЕ_ШАБЛОНА</b>.<br />Доступные шаблоны: ';
		//Список шаблонов составить просто исходя из имён файлов в директории /template
		$d=opendir(core::path().'template');
		$i=0;
		while($t=readdir($d)) {
			if($t=='.' || $t=='..') continue;
			$y=strrpos($t,'.');
			if(substr($t,0,3)!='pc.' || substr($t,$y)!='.html') continue;
			if($i) $this->cite.=', ';
			$this->cite.=substr($t,3,$y-3);
			$i++;
		}
		closedir($d);
		return $f;
	}

	public function actionIndexSubmit($data) {
		$data=explode("\n",$data['link']);
		$template=array();
		foreach($data as $item) {
			$item=explode('=',$item);
			if(count($item)!=2) continue;
			$item[0]=trim($item[0]);
			$item[1]=trim($item[1]);
			if(!$item[0] || !$item[1]) continue;
			$template[$item[0]]=$item[1];
		}
		core::import('admin/core/config');
		$cfg=new config('_core');
		$cfg->template=$template;
		$cfg->save('_core');
		core::redirect('?controller=template','Информация обновлена');
	}

}
?>