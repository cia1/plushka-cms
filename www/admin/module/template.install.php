<?php
function installAfter() {
	core::import('admin/core/config');
	$cfg=new config('_core');
	$cfg->template=array();
	$cfg->save('_core');
	return true;
}

function uninstallBefore() {
	core::import('admin/core/config');
	$cfg=new config('_core');
	$cfg->delete('template');
	$cfg->save('_core');
	return true;
}
?>