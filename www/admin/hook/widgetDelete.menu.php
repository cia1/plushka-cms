<?php
/* �������: �������� �������
������: ����
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, mixed $data[2] - ��������� ������� */
if($data[0]!='menu') return true;

core::import('admin/model/objectLink');
//���� ���� ����� �������, �� ������ �� ������
$cnt=modelObjectLink::fromSectionWidget('menu',$data[2])+modelObjectLink::fromTemplateWidget('menu',$data[2]);
if($cnt>1) return true;
//����� ������� ������ ���� � ���� ����
$db=core::db();
if($db->fetchValue('SELECT 1 FROM menuItem WHERE menuId='.$data[2].' LIMIT 1')) {
	controller::$error='���������� ������� ������� ��� ������ ����.';
	return false;
}
$db->query('DELETE FROM menu WHERE id='.$data[2]);
return true;
?>