<?php
/* �������: �������� ������ ����
������: core
���������: string $data[0] - ��������� ������, int $data[1] - ������������� ���������� ������ ���� */
$link=$data[0];

$db=core::db();
$db->query('DELETE FROM section WHERE url IN('.$db->escape($link.'.').','.$db->escape($link.'*').','.$db->escape($link.'/').')');
return true;
?>