<?php
/* �������: �������� �������
������: ���������� �����
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, $data[2] - ��������� ������� */

if($data[0]!='form') return true;
$db=core::db();
$db->query('DELETE FROM frmField WHERE formId='.$data[2]);
$db->query('DELETE FROM frmForm WHERE id='.$data[2]);
return true;
?>