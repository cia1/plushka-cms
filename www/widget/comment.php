<?php
/* Универсальный виджет комментариев
array $options: bool child - вывдить или нет комментарии на дочерних страницах */
class widgetComment extends widget {

	public function __invoke() {
//		if($this->options) {
//			if($this->options['child'] && $this->link==implode('/',$_GET['corePath'])) return false;
//		}
		core::import('model/comment');
		$this->link=implode('/',$_GET['corePath']);
		$cfg=core::config('comment');
		$this->status=$cfg['status'];
		return 'Comment';
	}

	public function adminLink2($data) {
		return array(
			array('comment.moderate','?controller=comment&action=edit&id='.$data[3],'edit','Править комментарий'),
			array('comment.moderate','?controller=comment&action=delete&id='.$data[3],'delete','Удалить комментарий')
		);
	}

}
?>