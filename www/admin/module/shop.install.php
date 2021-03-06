<?php
function installAfter($version) {
	if($version) return true;
	core::import('admin/model/form');
	core::import('admin/core/config');
	$f=new mForm();
	$id=$f->create('Оформление заказа','Заказ с сайта','shop');
	if(!$id) return false;
	$cfg=new config('shop');
	$cfg->formId=$id;
	$cfg->save('shop');

	$f->text('Ваше имя',true);
	$f->text('Телефон');
	$f->email('E-mail');
	$f->textarea('Комментарий к заказу');
	return true;
}

function uninstallBefore() {
	$cfg=core::config('shop');
	core::import('admin/model/form');
	return mForm::drop($cfg['formId']);
}
?>