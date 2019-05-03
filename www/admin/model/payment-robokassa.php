<?php
namespace plushka\admin\core;

class paymentRobokassa implements paymentSetting {

	public static function settingForm($cfg) {
		$form=payment::form($cfg);
		$form->text('id','Логин',$cfg['id']);
		$form->text('password1','Пароль 1',$cfg['password1']);
		$form->text('password2','Пароль 2',$cfg['password2']);
		$form->text('password1Debug','Пароль 1 (песочница)',$cfg['password1Debug']);
		$form->text('password2Debug','Пароль 2 (песочница)',$cfg['password2Debug']);
		$link='http://'.$_SERVER['HTTP_HOST'].'/payment/robokassa/';
		$form->html('<cite>Для приёма платежей сперва зарегистрируйтесь тут: <a href="https://partner.robokassa.ru/Reg/Register?culture=ru">partner.robokassa.ru</a>. Укажите следующие настройки:<br />
		Алгоритм расчета хеша: "MD5"; Result Url: "'.$link.'status"; Метод отсылки данных по Result Url: "POST"; Success Url: "'.$link.'success"; Метод отсылки данных по Success Url: "GET"; Fail Url: "'.$link.'fail"; Метод отсылки данных по Fail Url: "GET"<br />
		<u>Параметры проведения тестовых платежей:</u><br />
		Алгоритм расчета хеша: "MD5".</cite>');
		$form->submit('Сохранить');
		return array(
			'title'=>'Робокасса',
			'content'=>$form
		);
	}

	public static function settingSubmit($data) {
		plushka::import('admin/core/config');
		$cfg=new config('payment');
		$payment=$cfg->robokassa;
		$payment['id']=$data['id'];
		$payment['password1']=$data['password1'];
		$payment['password2']=$data['password2'];
		$payment['password1Debug']=$data['password1Debug'];
		$payment['password2Debug']=$data['password2Debug'];
		$cfg->robokassa=$payment;
		return $cfg->save('payment');
	}

}