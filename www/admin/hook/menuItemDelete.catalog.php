<?php
/* �������: �������� ������ ����
������: catalog (������������� �������)
���������: string $data[0] - ��������� ������, int $data[1] - ������������� ���������� ������ ���� */
$link=$data[0];

if(substr($link,0,8)!='catalog/') return true;
$id=substr($link,strrpos($link,'/')+1);
$db=plushka::db();
$db->query('DROP TABLE catalog_'.$id);
unlink(plushka::path().'config/catalogLayout/'.$id.'.php');
return true;
?>