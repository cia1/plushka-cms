<?php
function installAfter($version) {
	plushka::import('admin/core/config');
	$cfg=new config('_core');
	$cfg->template=array();
	$cfg->save('_core');
	return true;
}

function uninstallBefore() {
	plushka::import('admin/core/config');
	$cfg=new config('_core');
	unset($cfg->template);
	$cfg->save('_core');
	return true;
}