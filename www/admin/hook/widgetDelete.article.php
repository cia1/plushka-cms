<?php
/* �������: �������� �������. ������� ����, ���� �� ���� ������ ��� ������.
������: article (������)
���������: string [0] - ��� �������, int [1] - ������������� �������, mixed [2] - ��������� �������. */

if($data[0]!='blog') return true;
//������ �� ������, ���� ������ �� ��� ��������� ���� � ����� �� ������� ����.
$db=core::db();
$alias=$db->fetchValue('SELECT alias FROM articleCategory WHERE id='.$data[2]['categoryId']);
//if($db->fetchValue('SELECT count(id) FROM menuItem WHERE link LIKE '.$db->escape('article/blog/'.$alias).' OR link LIKE '.$db->escape('article/list/'.$alias))!='1') return true;

//������ �� ������, ���� ���� ������ ������� � ���� ����������.
core::import('admin/model/objectLink');
$param=array('categoryId'=>$data[2]['categoryId']);
$cnt=modelObjectLink::fromSectionWidget('blog',$param)+modelObjectLink::fromTemplateWidget('blog',$param);
if($cnt>1) return true;

//������� ��������� � ��� ��������� ������.
$db->query('DELETE FROM articleCategory WHERE id='.$data[2]['categoryId']);
$db->query('DELETE FROM article WHERE categoryId='.$data[2]['categoryId']);
return true;
?>