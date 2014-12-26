<?php
/* ���������� ������ comment (�����������) */
class mComment {

	/* ������� HTML-��� �� ������� ������������.
	$link - ��������, ��� ������� ����� ������ ������������; $widget -  */
	public static function renderList($link=null,&$widget=null) {
		if(!$link) $link=implode('/',$_GET['corePath']); //���� �� ������, �� ������������ ����� ����������� ��������
		//$groupId - �������� ������������� ��������, ��� ������� ��������� �����������
		$db=core::db();
		$groupId=$db->fetchValue('SELECT id FROM commentGroup WHERE link='.$db->escape($link));
		if(!$groupId) return;
		if(isset($_GET['comment'])) $page=(int)$_GET['comment']; else $page=0; //��������� ������������
		$db->query('SELECT date,name,text,id FROM comment WHERE groupId='.$groupId.' AND status=1 ORDER BY date DESC'); //,20,$page);
		while($item=$db->fetch()) {
			echo '<div class="item" itemprop="comment" itemscope itemtype="http://schema.org/UserComments">';
			if($widget) $widget->admin($item);
			echo '<span class="name" itemprop="creator">'.$item[1].'</span><span class="date" itemprop="commentTime">'.date('d.m.Y H:i',$item[0]).'</span>
			<p itemprop="commentText">'.$item[2].'</p>
			</div>';
		}
	}

}
?>