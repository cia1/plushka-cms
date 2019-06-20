<?php
namespace plushka\widget;
use plushka;
use plushka\core\Widget;
use plushka\model\Shop;

class ShopCategoryWidget extends Widget {

	public function __invoke() { return true; }

	public function render($view): void {
		if(isset($_GET['corePath'][1]) && $_GET['corePath'][0]=='shop') {
			if(isset(plushka::$controller->category)) $id=plushka::$controller->category['id'];
			else {
				$db=plushka::db();
				$id=$db->fetchValue('SELECT id FROM shp_category WHERE alias='.$db->escape($_GET['corePath'][1]));
			}
		} else $id=null;
		$items=Shop::cacheCategoryTree();
		if($id) $pid=$items[$id]['parentId']; else $pid=null;
		$this->_renderList($items['ROOT'],$id,$pid);
	}

	private function _renderList($data,$currentId,$parentId) {
		echo '<ul>';
		foreach($data as $item) {
			$id=$item['id'];
			echo '<li';
			if($id===$currentId) echo ' class="active"';
			echo '><a href="'.plushka::link('shop/'.$item['alias']).'">'.$item['title'].'</a>';
			if(($id==$parentId || $id==$currentId) && $item['child']) $this->_renderList($item['child'],$currentId,$id);
			echo '</li>';
		}
		echo '</ul>';
	}
}
