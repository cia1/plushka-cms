<?php class paymentLiqpay implements paymentSetting {

	public static function settingForm($cfg) {
		$form=payment::form($cfg);
		$form->text('publicKey','Публичный ключ',$cfg['publicKey']);
		$form->text('privateKey','Приватный ключ',$cfg['privateKey']);
//		$form->html('<cite>На сайте digiseller.ru необходимо создать товар: уникальный товар с нефиксированной ценой / уникальный код.<br />
//			Единица товара - "1", цена товара - "1"; проверка уникального кода: "автоматическая" (http://'.$_SERVER['HTTP_HOST'].'/payment/digiseller/status'.'); количество товара - "не ограничено".<br />
//			<b>ID товара</b> - идентификатор созданного товара (можно увидеть в URL-адресе или на вклдаке "HTML-код").</cite>');
		$form->submit('Сохранить');
		return array(
			'title'=>'Liq Pay',
			'content'=>$form
		);
	}

	public static function settingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('payment');
		$payment=$cfg->liqpay;
		$payment['publicKey']=$data['publicKey'];
		$payment['privateKey']=$data['privateKey'];
		$cfg->liqpay=$payment;
		return $cfg->save('payment');
	}

}