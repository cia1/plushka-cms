<?php
use plushka\admin\model\ObjectLink;

if($data[0]!='blog') return true;
$db=plushka::db();
$alias=$db->fetchValue('SELECT alias FROM article_category_'._LANG.' WHERE id='.$data[2]['categoryId']);

$param=array('categoryId'=>$data[2]['categoryId']);
$cnt=ObjectLink::fromSectionWidget('blog',$param)+ObjectLink::fromTemplateWidget('blog',$param);
if($cnt>1) return true;

$db->query('DELETE FROM article_category_'._LANG.' WHERE id='.$data[2]['categoryId']);
$db->query('DELETE FROM article WHERE categoryId='.$data[2]['categoryId']);
return true;