<?php
/* Дерево категорий интернет-магазина */
class widgetShopCategory extends widget {

	public function action() { return true; }

	public function render() {
		//Определить ИД текущей категории
		if(isset($_GET['corePath'][2]) && $_GET['corePath'][0]=='shop' && ($_GET['corePath'][1]=='category' || $_GET['corePath'][1]=='product')) $id=(int)$_GET['corePath'][2]; else $id=null;
		core::import('model/shop');
		$items=shop::cacheCategoryTree(); //дерево категорий
		if($id) $pid=$items[$id]['parent']['id']; else $pid=null;
		$this->_renderList($items[0]['child'],$id,$pid);
	}

	/* Рекурсивно выводит категории интернет-магазина */
	private function _renderList($data,$currentId,$parentId) {
		echo '<ul>';
		foreach($data as $id=>$item) {
			echo '<li';
			if($id===$currentId) echo ' class="active"';
			echo '><a href="'.core::link('shop/category/'.$id).'">'.$item['title'].'</a>';
			if(($id==$parentId || $id==$currentId) && $item['child']) $this->_renderList($item['child'],$currentId,$id);
			echo '</li>';
		}
		echo '</ul>';
	}
}
?>