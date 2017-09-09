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
	$db=core::db();
	if($db->fetchValue('SELECT count(id) FROM menuItem WHERE link='.$db->escape($link))!='1') return true;
	$alias=substr($link,13);
	$db->query('DELETE FROM article WHERE alias='.$db->escape($alias));
	return true;
}

/* ������� ��������� ������, ���� �� �� ���� ������ ���� ������ $link */
function _blogDelete($link) {
	$db=core::db();
	if($db->fetchValue('SELECT count(id) FROM menuItem WHERE link='.$db->escape($link))!='1') return true;
	$alias=substr($link,13);
	$id=$db->fetchValue('SELECT id FROM articleCategory_'._LANG.' WHERE alias='.$db->escape($alias));

	core::import('admin/model/objectLink');
	$param=array('categoryId'=>$id);
	$cnt=modelObjectLink::fromSectionWidget('blog',$param)+modelObjectLink::fromTemplateWidget('blog',$param);
	if($cnt) return true;

	$cfg=core::config();
	if(isset($cfg['languageList'])) $languageList=$cfg['languageList']; else $languageList=array($cfg['languageDefault']);
	foreach($languageList as $item) {
		$db->query('DELETE FROM articleCategory_'.$item.' WHERE id='.$id);
		$db->query('DELETE FROM article_'.$item.' WHERE categoryId='.$id);
	}
	return true;
}
?>