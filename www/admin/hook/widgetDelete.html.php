<?php
/* �������: �������� �������
������: ������������ �����
���������: string $data[0] - ��� �������, int $data[1] - ������������� �������, mixed $data[2] - ��������� ������� */

if($data[0]!='html') return true;
$cfg=plushka::config();
foreach($cfg['languageList'] as $item) {
	$f=plushka::path().'data/widgetHtml/'.$data[2].'_'.$item.'.html';
	if(file_exists($f)) unlink($f);
}
return true;
?>