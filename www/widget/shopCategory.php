<?php
/* ������ ��������� ��������-�������� */
class widgetShopCategory extends widget {

	public function __invoke() { return true; }

	public function render() {
		//���������� �� ������� ���������
		if(isset($_GET['corePath'][2]) && $_GET['corePath'][0]=='shop' && ($_GET['corePath'][1]=='category' || $_GET['corePath'][1]=='product')) $id=(int)$_GET['corePath'][2]; else $id=null;
		core::import('model/shop');
		$items=shop::cacheCategoryTree(); //������ ���������
		if($id) $pid=$items[$id]['parentId']; else $pid=null; //����������� ������ ��� ������ ������
		$this->_renderList($items['ROOT'],$id,$pid);
	}

	/* ���������� ������� ��������� ��������-�������� */
	private function _renderList($data,$currentId,$parentId) {
		echo '<ul>';
		foreach($data as $item) {
			echo '<li';
			if($item['id']===$currentId) echo ' class="active"';
			echo '><a href="'.core::link('shop/category/'.$item['id']).'">'.$item['title'].'</a>';
			if(($item['id']==$parentId || $item['id']==$currentId) && $item['child']) $this->_renderList($item['child'],$currentId,$item['id']);
			echo '</li>';
		}
		echo '</ul>';
	}
}
?>