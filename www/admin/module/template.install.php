<?php
use plushka\admin\core\Config;

function installAfter($version) {
	$cfg=new Config('_core');
	$cfg->template=array();
	$cfg->save('_core');
	return true;
}

function uninstallBefore() {
	$cfg=new Config('_core');
	unset($cfg->template);
	$cfg->save('_core');
	return true;
}