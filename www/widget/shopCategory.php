<?php
//Отображает список категорий. Модуль: shop
class widgetShopCategory extends widget {

	public function __invoke() { return true; }

	public function render() {
		//Попытка определить ИД категории разными способами (для экономии SQL-запроса)
		if(isset($_GET['corePath'][1]) && $_GET['corePath'][0]=='shop') {
			if(isset(controller::$self->category)) $id=controller::$self->category['id'];
			else {
				$db=core::db();
				$id=$db->fetchValue('SELECT id FROM shpCategory WHERE alias='.$db->escape($_GET['corePath'][1]));
			}
		} else $id=null;
		core::import('model/shop');
		$items=shop::cacheCategoryTree(); //древовидный массив категорий
		if($id) $pid=$items[$id]['parentId']; else $pid=null;
		$this->_renderList($items['ROOT'],$id,$pid);
	}

	private function _renderList($data,$currentId,$parentId) {
		echo '<ul>';
		foreach($data as $item) {
			$id=$item['id'];
			echo '<li';
			if($id===$currentId) echo ' class="active"';
			echo '><a href="'.core::link('shop/'.$item['alias']).'">'.$item['title'].'</a>';
			if(($id==$parentId || $id==$currentId) && $item['child']) $this->_renderList($item['child'],$currentId,$id);
			echo '</li>';
		}
		echo '</ul>';
	}
}
?>