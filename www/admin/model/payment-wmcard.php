<?php class paymentWmcard implements paymentSetting {

	public static function settingForm($cfg) {
		controller::$self->button('?controller=wmcard','webmoney','Не обработанные платежи');
		$form=payment::form($cfg);
		$form->checkbox('wmr','Принимать WMR',$cfg['wmr']);
		$form->checkbox('wmu','Принимать WMU',$cfg['wmu']);
		$form->checkbox('wmz','Принимать WMZ',$cfg['wmz']);
		$form->checkbox('wme','Принимать WME',$cfg['wme']);
		$form->submit('Сохранить');
		return array(
			'title'=>'Предоплаченные карты WebMoney',
			'content'=>$form
		);
	}

	public static function settingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('payment');
		$payment=$cfg->wmcard;
		if(isset($data['wmr'])) $payment['wmr']=true; else $payment['wmr']=false;
		if(isset($data['wmu'])) $payment['wmu']=true; else $payment['wmu']=false;
		if(isset($data['wmz'])) $payment['wmz']=true; else $payment['wmz']=false;
		if(isset($data['wme'])) $payment['wme']=true; else $payment['wme']=false;
		$cfg->wmcard=$payment;
		return $cfg->save('payment');
	}

}