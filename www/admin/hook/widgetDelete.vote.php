<?php
/* �������: �������� �������
������: vote (�����)
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, mixed $data[2] - ������������� ������ */
if($data[0]!='vote') return true;

$db=core::db();
$db->query('DELETE FROM vote WHERE id='.$data[2]);
return true;
?>