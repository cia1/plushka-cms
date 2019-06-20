<?php
namespace plushka\widget;
use plushka;
use plushka\core\Widget;

/* Универсальный виджет комментариев
array $options: bool child - вывдить или нет комментарии на дочерних страницах */
class CommentWidget extends Widget {

	public function __invoke() {
		$this->link=implode('/',$_GET['corePath']);
		$cfg=plushka::config('comment');
		$this->status=$cfg['status'];
		plushka::language('comment');
		return 'Comment';
	}

	public function adminLink2($data) {
		return array(
			array('comment.moderate','?controller=comment&action=edit&id='.$data[3],'edit','Править комментарий'),
			array('comment.moderate','?controller=comment&action=delete&id='.$data[3],'delete','Удалить комментарий')
		);
	}

}
