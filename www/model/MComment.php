<?php
namespace plushka\model;
use plushka;

class MComment {

	/* ¬ыводит HTML-код со списком комментариев.
	$link - страница, дл€ которой нужен список комментариев; $widget -  */
	public static function renderList($link=null,&$widget=null) {
		if(!$link) $link=implode('/',$_GET['corePath']); //если не задана, то использовать адрес запрошенной старницы
		//$groupId - условный идентификатор страницы, дл€ который вывод€тс€ комментарии
		$db=plushka::db();
		$groupId=$db->fetchValue('SELECT id FROM comment_group WHERE link='.$db->escape($link));
		if(!$groupId) return;
		if(isset($_GET['comment'])) $page=(int)$_GET['comment']; else $page=0; //пагинаци€ комментариев
		$db->query('SELECT date,name,text,id,userId FROM comment WHERE groupId='.$groupId.' AND status>0 ORDER BY date DESC'); //,20,$page);
		while($item=$db->fetch()) {
			echo '<div class="item" itemprop="comment" itemscope itemtype="http://schema.org/UserComments">';
			if($widget) $widget->admin($item);
			if($item[4]) {
				echo '<a href="',plushka::link('user/'.$item[1]),'" class="name" itemprop="creator">'.$item[1].'</a>';
			} else echo '<span class="name" itemprop="creator">'.$item[1].'</span>';
			echo '<span class="date" itemprop="commentTime">'.date('d.m.Y H:i',$item[0]).'</span>
			<p itemprop="commentText">'.$item[2].'</p>
			</div>';
		}
	}

}