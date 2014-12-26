<?php
/* �������: �������� ������� � �����-���� �������
������: �����������
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, array $data[2] - ������ �������, � ������� ��� ����� ������ */
if($data[0]!='comment') return true;

$db=core::db();
$ids='';
foreach($data[2] as $item) {
	//������� ������ ������� ������������, ������� ������������� ��������� ��������
	$i=strlen($item)-1;
	$s=substr($item,0,$i);
	if($item[$i]=='/') $s=' LIKE '.$db->escape($s.'%');
	elseif($item[$i]=='.') $s='='.$db->escape($s);
	else $s=' LIKE '.$db->escape($s.'/%');
	$id=$db->fetchArray('SELECT id FROM commentGroup WHERE link'.$s);
	if(!$id) continue;
	foreach($id as $item) {
		if($ids) $ids.=','.$item[0]; else $ids=$item[0];
	}
}
if(!$ids) return true;
$db->query('DELETE FROM comment WHERE groupId IN('.$ids.')');
$db->query('DELETE FROM commentGroup WHERE id IN('.$ids.')');
return true;
?>