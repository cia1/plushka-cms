<?php
//Дополнить информацию о файле, если он был загружен через скрипт upload.php
$alias=$data[0];
if(isset($_SESSION['_uploadList'])) $uploadList=$_SESSION['_uploadList']; else $uploadList=array();
foreach($_POST[$alias] as $fld=>$item) {
	if(is_array($item) && isset($item[0])) {
		foreach($item as $i=>$_item) {
			if(substr($_item,0,7)=='upload:') {
				$_item=substr($_item,7);
				if(!isset($uploadList[$_item])) unset($_POST[$alias][$fld][$i]);
				else {
					$_POST[$alias][$fld][$i]=$uploadList[$_item];
					$_POST[$alias][$fld][$i]['name']=$_item;
				}
			}
		}
	} elseif(is_string($item) && substr($item,0,7)=='upload:') {
		$item=substr($item,7);
		if(!isset($uploadList[$item])) $_POST[$alias][$fld]=array('name'=>null,'tmpName'=>null,'type'=>null,'size'>0);
		else {
			$_POST[$alias][$fld]=$uploadList[$item];
			$_POST[$alias][$fld]['name']=$item;
		}
	}
}
unset($_SESSION['_uploadFolder']);
unset($_SESSION['_uploadTimeLimit']);
unset($_SESSION['_uploadList']);
return true;