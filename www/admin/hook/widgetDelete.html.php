<?php
/* �������: �������� �������
������: ������������ �����
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, mixed $data[2] - ��������� ������� */

if($data[0]!='html') return true;
$f=core::path().'data/widgetHtml/'.$data[2].'.html';
if(!file_exists($f)) return true;
unlink($f);
return true;
?>