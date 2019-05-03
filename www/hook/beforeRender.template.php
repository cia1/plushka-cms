<?php
if(!$data[0]) return true;
$t=plushka::config();
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
if($tmplName) plushka::template($tmplName);
return true;
?>