<?php
/* Событие: удаление виджета. Удалить блог, если на него больше нет ссылок.
Модуль: article (статьи)
Параметры: string [0] - имя виджета, int [1] - идентификатор виджета, mixed [2] - параметры виджета. */

if($data[0]!='blog') return true;
//Ничего не делать, если ссылка на эту категорию есть в одном из пунктов меню.
$db=plushka::db();
$alias=$db->fetchValue('SELECT alias FROM article_category_'._LANG.' WHERE id='.$data[2]['categoryId']);

//Ничего не делать, если есть другие виджеты с этой категорией.
plushka::import('admin/model/objectLink');
$param=array('categoryId'=>$data[2]['categoryId']);
$cnt=modelObjectLink::fromSectionWidget('blog',$param)+modelObjectLink::fromTemplateWidget('blog',$param);
if($cnt>1) return true;

$db->query('DELETE FROM article_category_'._LANG.' WHERE id='.$data[2]['categoryId']);
$db->query('DELETE FROM article WHERE categoryId='.$data[2]['categoryId']);
return true;