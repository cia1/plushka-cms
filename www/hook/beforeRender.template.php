<?php
/* Событие: срабатывает перед началом вывода контента. Подменяет имя шаблона, если для запрошенной страницы указан индивидуальный шаблон
Модуль: template (индивидуальные шаблоны для разных страниц сайта)
Параметры: bool $data[0] - будет или нет обработан шаблон */
if(!$data[0]) return true;
$t=core::config();
$t=$t['template'];
$link='';
$cntGet=count($_GET['corePath']);
$tmplName=null;
$tmplCount=0;
foreach($t as $item=>$template) {
	$item=explode('/',$item);
	$cntItem=count($item);
	if($cntItem>$cntGet) continue;
	$cntTmp=0;
	for($i=0;$i<$cntItem;$i++) {
		if($item[$i]!=$_GET['corePath'][$i]) break;
		$cntTmp++;
	}
	if($cntTmp<$cntItem) continue;
	if($tmplCount<$cntTmp) {
		$tmplCount=$cntTmp;
		$tmplName=$template;
		if($cntItem==$cntGet) break;
	}
}
if($tmplName) core::template($tmplName);
return true;
?>