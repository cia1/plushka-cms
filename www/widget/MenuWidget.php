<?php
namespace plushka\widget;
use plushka;

/* Выводит меню
int $options - идентификатор меню */
class MenuWidget extends \plushka\core\Widget {
	private $_requestMatches;

	public function __invoke() {
		//Алгоритм: выбрать все пункты меню из базы данных, затем для каждого посчитать количество совпавших частей ЧПУ ссылки (те, что разделены символом "/") с запрошенным адресом.
		//"Текущим" пунктом (выделенным) считается тот, у которого всех больше совпадений (это не идельное решение, но на практике неплохо работает).
		//В $this->data буедт древовидный массив всех пунктов меню, а в $this->active - все "текущие" пункты меню (несколько - чтобы выделить родительские)
		$db=plushka::db();
		$db->query('SELECT link,title_'._LANG.',id,parentId FROM menu_item WHERE menuId="'.$this->options.'" ORDER BY sort');
		$items=array();
		if($_GET['corePath'][1]) $this->_requestMatches=count($_GET['corePath']);
		else $this->_requestMatches=1; //количество элементов ЧПУ ссылки в запрошенной странице
		$activeMatches=null; //для подсчёта совпадений
		$this->active=array(); //содержит список активных пунктов в меню  (для выделения всех родительских пунктов)
		$activeCount=0;
		$parent=array();
		while($item=$db->fetch()) {
			if($this->_requestMatches!=$activeMatches) { //Если активный пункт меню ещё не определён
				$matches=$this->_matches($item[0]);
				if($matches>$activeMatches) {
					$activeMatches=$matches;
					$activeCount=1;
					$this->active[0]=$item[2];
				} elseif($matches==$activeMatches) $activeCount++;
			}
			if(!isset($items[$item[3]])) $items[$item[3]]=array();
			$items[$item[3]][]=$item;
			$parent[$item[2]]=$item[3];
		}
		$this->data=$items;
		if($activeCount!=1) $this->active=array();
		else {
			if(isset($this->actiive[0])) {
				$id=$this->active[0];
				while($p=$parent[$id]) $this->active[]=$id=$p;
			}
		}
		if(!$this->data[0]) $this->data=array();
		return true;
	}

	public function render($d=null,$child=false) {
		if(!$child) echo '<nav>';
		if($d===true) $d=$this->data[0];
		echo '<ul itemscope itemtype="http://www.schema.org/SiteNavigationElement">';
		for($i=0,$cnt=count($d);$i<$cnt;$i++) {
			$item=$d[$i];
			echo '<li class="item'.$i;
			if(in_array($item[2],$this->active)) echo ' active';
			echo '" itemprop="name"><a href="'.plushka::link($item[0]).'" itemprop="url"><span>'.$item[1].'</span></a>';
			if(isset($this->data[$item[2]])) $this->render($this->data[$item[2]],true);
			echo '</li>';
		}
		echo '</ul>';
		if(!$child) echo '</nav>';
	}

	public function adminLink() {
		return array(
			array('menu.*','?controller=menu&action=items&menuId='.$this->options,'list','Управление меню'),
			array('menu.*','?controller=menu&action=item&menuId='.$this->options,'new','Добавить пункт меню')
		);
	}

	/* Количество совпадений частей ссылки */
	private function _matches($link) {
		$link=explode('/',$link);
		$match=0;
		$cnt=count($link);
		if($cnt>$this->_requestMatches) $cnt=$this->_requestMatches;
//		if($cnt>$this->_requestMatches) return 0;
		for($i=0;$i<$cnt;$i++) if($link[$i]==$_GET['corePath'][$i]) $match++; else break;
		return $match;
	}

}