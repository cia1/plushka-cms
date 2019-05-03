<?php
/* �������: �������� ������ ����.
������: ������
���������: string $data[0] - ��������� ������, int $data[1] - ������������� ������ ���� */
$link=$data[0];

if(substr($link,0,13)=='article/view/') return _articleDelete($link);
if(substr($link,0,13)=='article/blog/' || substr($link,0,13)=='article/list/') return _blogDelete($link);
return true;

/* ������� ������ ��� ����, ���� �� ��� ������ ��� ������ */
function _articleDelete($link) {
	$db=plushka::db();
	if($db->fetchValue('SELECT count(id) FROM menu_item WHERE link='.$db->escape($link))!='1') return true;
	$alias=substr($link,13);
	$db->query('DELETE FROM article WHERE alias='.$db->escape($alias));
	return true;
}

/* ������� ��������� ������, ���� �� �� ���� ������ ���� ������ $link */
function _blogDelete($link) {
	$db=plushka::db();
	if($db->fetchValue('SELECT count(id) FROM menu_item WHERE link='.$db->escape($link))!='1') return true;
	$alias=substr($link,13);
	$id=$db->fetchValue('SELECT id FROM article_category_'._LANG.' WHERE alias='.$db->escape($alias));

	plushka::import('admin/model/objectLink');
	$param=array('categoryId'=>$id);
	$cnt=modelObjectLink::fromSectionWidget('blog',$param)+modelObjectLink::fromTemplateWidget('blog',$param);
	if($cnt) return true;
	$cfg=plushka::config();
	if(isset($cfg['languageList'])) $languageList=$cfg['languageList']; else $languageList=array($cfg['languageDefault']);
	foreach($languageList as $item) {
		$db->query('DELETE FROM article_category_'.$item.' WHERE id='.$id);
		$db->query('DELETE FROM article_'.$item.' WHERE categoryId='.$id);
	}
	return true;
}