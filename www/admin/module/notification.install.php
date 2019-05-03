<?php
function installAfter($version) {
	plushka::import('admin/core/config');
	$cfg=new config('notification');
	$group=$cfg->group;
	if($group===null) $group=array();
	if(isset($group['privateMessage'])) return true;
	$group['privateMessage']='Личное сообщение';
	$cfg->group=$group;
	$cfg->save('notification');
	return true;
}

function uninstallAfter() {
	$f=plushka::path().'config/notification.php';
	if(file_exists($f)) unlink($f);
}