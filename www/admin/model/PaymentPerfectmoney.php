<?php
namespace plushka\admin\core;

class PaymentPerfectmoney implements PaymentSetting {

	public static function settingForm($cfg) {
		$form=payment::form($cfg);
		$form->text('id','Аккаунт ID',$cfg['id']);
		$form->text('wallet','Кошелёк',$cfg['wallet']);
		$form->text('title','Имя, которое видит пользователь',$cfg['title']);
		$form->text('secret','Альтернативная кодовая фраза',$cfg['secret']);
		$form->text('password','Пароль',$cfg['password']);
		$form->html('<cite>Для приёма платежей сперва зарегистрируйтесь тут: <a href="https://perfectmoney.is/?ref=seoutils">perfect money</a>, затем укажите ваши данные на этой форме<br />
			<b>Альтернативная кодовая фраза</b> задаётся в настройках аккаунта Prefect Money; <b>пароль</b> - пароль для доступа к аккаунту.<br /><br /></cite>');
		$form->submit('Сохранить');
		return array(
			'title'=>'Perfect Money',
			'content'=>$form
		);
	}

	public static function settingSubmit($data) {
		plushka::import('admin/core/config');
		$cfg=new config('payment');
		$payment=$cfg->perfectmoney;
		$payment['id']=$data['id'];
		$payment['wallet']=$data['wallet'];
		$payment['title']=$data['title'];
		$payment['secret']=$data['secret'];
		$payment['password']=$data['password'];
		$cfg->perfectmoney=$payment;
		return $cfg->save('payment');
	}

}
