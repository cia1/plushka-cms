<?php
/* �������: ���������� Last modified
���������: string $data[0] - ����� ��������, ������� ���� �������� */
$db=core::db();
$db->query('REPLACE INTO modified (link,time) VALUES ('.$db->escape($data[0]).','.time().')');
return true;
?>