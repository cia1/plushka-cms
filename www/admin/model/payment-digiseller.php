<?php class paymentDigiseller implements paymentSetting {

	public static function settingForm($cfg) {
		$form=payment::form($cfg);
		$form->text('id','ID аккаунта',$cfg['id']);
		$form->text('productId','ID товара',$cfg['productId']);
		$form->text('password','Пароль',$cfg['password']);
		$form->html('<cite>На сайте digiseller.ru необходимо создать товар: уникальный товар с нефиксированной ценой / уникальный код.<br />
			Единица товара - "1", цена товара - "1"; проверка уникального кода: "автоматическая" (http://'.$_SERVER['HTTP_HOST'].'/payment/digiseller/status'.'); количество товара - "не ограничено".<br />
			<b>ID товара</b> - идентификатор созданного товара (можно увидеть в URL-адресе или на вклдаке "HTML-код").</cite>');
		$form->submit('Сохранить');
		return array(
			'title'=>'Digiseller',
			'content'=>$form
		);
	}

	public static function settingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('payment');
		$payment=$cfg->digiseller;
		$payment['id']=$data['id'];
		$payment['productId']=$data['productId'];
		$payment['password']=$data['password'];
		$cfg->digiseller=$payment;
		return $cfg->save('payment');
	}

}