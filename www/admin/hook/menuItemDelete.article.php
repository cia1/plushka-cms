<?php
/**
 * Событие: удаление пункта меню
 * @var array $data :
 *  string [0] ссылка на удаляемый пункт меню
 */
use plushka\admin\core\plushka;
use plushka\admin\model\ObjectLink;

$link=$data[0];
if(substr($link,0,13)==='article/view/') return _articleDelete($link);
if(substr($link,0,13)==='article/blog/' || substr($link,0,13)=='article/list/') return _blogDelete($link);
return true;

function _articleDelete($link) {
	$db=plushka::db();
	if($db->fetchValue('SELECT count(id) FROM menu_item WHERE link='.$db->escape($link))!='1') return true;
	$alias=substr($link,13);
	$db->query('DELETE FROM article WHERE alias='.$db->escape($alias));
	return true;
}

function _blogDelete($link) {
	$db=plushka::db();
	if($db->fetchValue('SELECT count(id) FROM menu_item WHERE link='.$db->escape($link))!='1') return true;
	$alias=substr($link,13);
	$id=$db->fetchValue('SELECT id FROM article_category_'._LANG.' WHERE alias='.$db->escape($alias));

	$param=['categoryId'=>$id];
	$cnt=ObjectLink::fromSectionWidget('blog',$param)+ObjectLink::fromTemplateWidget('blog',$param);
	if($cnt) return true;
	$cfg=plushka::config();
	if(isset($cfg['languageList'])) $languageList=$cfg['languageList']; else $languageList=[$cfg['languageDefault']];
	foreach($languageList as $item) {
		$db->query('DELETE FROM article_category_'.$item.' WHERE id='.$id);
		$db->query('DELETE FROM article_'.$item.' WHERE categoryId='.$id);
	}
	return true;
}