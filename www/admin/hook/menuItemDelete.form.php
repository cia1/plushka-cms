<?php
/* �������: �������� ������ ����
������: ���������� �����
���������: string $data[0] - ��������� ������, int $data[1] - ������������� ������ ���� */
$link=$data[0];

if(substr($link,0,5)!='form/') return true;
$id=substr($link,5);
$db=core::db();
$db->query('DELETE FROM frm_field WHERE formId='.$id);
$db->query('DELETE FROM frm_form WHERE id='.$id);
return true;