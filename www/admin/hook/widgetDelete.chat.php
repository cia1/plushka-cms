<?php
/* �������: �������� �������
������: ���
���������: string [0] - ��� �������, int [1] - ������������� �������, mixed [2] - ��������� �������
int [2]['id'] - ������������� ���� */
if($data[0]!='chat') return true;
unlink(core::path().'data/chat.'.$data[2]['id'].'.txt');
return true;
?>