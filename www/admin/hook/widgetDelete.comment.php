<?php
/* �������: �������� �������
������: �����������
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, mixed $data[2] - ��������� ������� */

if($data[0]!='comment') return true;
//��������� ������ ���� �������, � ������� �� ������� ��� ���� ������
$db=core::db();
$db->query('SELECT url FROM section WHERE widgetId='.$data[1]);
$links='';
while($item=$db->fetch()) {
	if($links) $links.=',';
	$links.=$db->escape(substr($item[0],0,strlen($item[0])-1));
}
//������� �� ����� ������������, ��������������� ���������, �� ������� ��� ������
$db->query('SELECT id FROM commentGroup WHERE link IN('.$links.')');
unset($links);
$ids='';
while($item=$db->fetch()) {
	if($ids) $ids.=','.$item[0]; else $ids=$item[0];
}
if(!$ids) return true;
$db->query('DELETE FROM comment WHERE groupId IN('.$ids.')');
$db->query('DELETE FROM commentGroup WHERE id IN('.$ids.')');
return true;
?>