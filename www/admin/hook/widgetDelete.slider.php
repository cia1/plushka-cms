<?php
/* �������: �������� �������
������: slider (�������)
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, mixed $data[2] - ��������� �������
int $data[2]['id'] - ������������� �������� */
if($data[0]!='slider') return true;

$cfg=core::config('slider-'.$data[2]['id']);
$path=core::path().'public/slider/';
foreach($cfg['data'] as $item) unlink($path.$data[2]['id'].'.'.$item['image']);
unlink(core::path().'config/slider-'.$data[2]['id'].'.php');
return true;
?>