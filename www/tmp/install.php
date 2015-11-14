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

	core::import('admin/core/config');
	$cfg=new config('admin/_hook');
	$cfg->delete('languageCreate');
	$cfg->delete('languageDelete');
	return $cfg->save('admin/_hook');
}
?>