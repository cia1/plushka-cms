<?php
/* ��������� Last modified
���������: string $data[0] - ������������� URL �������� (��� �������� �����);
bool $data[1] - ���� true, �� �������� ��� ���� ������ */
$db=core::db();
$cfg=core::config();
if(isset($data[1]) && $data[1]) {
	$link=array();
	foreach($cfg['languageList'] as $item) {
		if($item==_LANG) $link[]=$data[0]; else $link[]=$item.'/'.$data[0];
	}
} else {
	if($cfg['languageDefault']==_LANG) $link=array($data[0]); else $link=array(_LANG.'/'.$data[0]);
}
foreach($link as $item) {
	$db->query('REPLACE INTO modified (link,time) VALUES ('.$db->escape($item).','.time().')');
}
return true;