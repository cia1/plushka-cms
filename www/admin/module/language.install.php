<?php
//Удаляет все обработчики событий languageCreate и languageDelete,
// а также удаляет само событие
function uninstallAfter() {
	$path=core::path().'admin/hook/';
	$d=opendir($path);
	while($f=readdir($d)) {
		if($f=='.' || $f=='..') continue;
		if(substr($f,0,15)=='languageCreate.' || substr($f,0,15)=='languageDelete.') unlink($path.$f);
	}
	closedir($d);
	return true;
}
?>