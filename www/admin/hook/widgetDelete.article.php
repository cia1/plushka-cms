<?php
/* �������: �������� �������. ������� ����, ���� �� ���� ������ ��� ������.
������: article (������)
���������: string [0] - ��� �������, int [1] - ������������� �������, mixed [2] - ��������� �������. */

if($data[0]!='blog') return true;
//������ �� ������, ���� ������ �� ��� ��������� ���� � ����� �� ������� ����.
$db=plushka::db();
$alias=$db->fetchValue('SELECT alias FROM article_category_'._LANG.' WHERE id='.$data[2]['categoryId']);

//������ �� ������, ���� ���� ������ ������� � ���� ����������.
plushka::import('admin/model/objectLink');
$param=array('categoryId'=>$data[2]['categoryId']);
$cnt=modelObjectLink::fromSectionWidget('blog',$param)+modelObjectLink::fromTemplateWidget('blog',$param);
if($cnt>1) return true;

$db->query('DELETE FROM article_category_'._LANG.' WHERE id='.$data[2]['categoryId']);
$db->query('DELETE FROM article WHERE categoryId='.$data[2]['categoryId']);
return true;