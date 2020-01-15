<?php
use plushka\admin\core\Config;
use plushka\admin\core\plushka;
use plushka\admin\model\Form;

function installAfter($version): bool {
	if($version) return true;
	$f=new Form();
	$id=$f->create('Оформление заказа','Заказ с сайта','shop');
	if($id===null) return false;
	$cfg=new Config('shop');
	/** @noinspection PhpUndefinedFieldInspection */
	$cfg->formId=$id;
	$cfg->save('shop');

	$f->addFieldText('Ваше имя',true);
	$f->addFieldText('Телефон');
	$f->addFieldEmail('E-mail');
	$f->addFieldTextarea('Комментарий к заказу');
	return true;
}

function uninstallBefore() {
	$cfg=plushka::config('shop');
	return Form::drop($cfg['formId']);
}