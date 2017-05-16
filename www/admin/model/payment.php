<?php
class payment {

	public static function methodList() {
		$d=opendir(core::path().'admin/model');
		$data=array();
		$cfg=core::config('payment');
		while($f=readdir($d)) {
			if($f=='.' || $f=='..') continue;
			if(substr($f,0,8)!='payment-') continue;
			include_once(core::path().'admin/model/'.$f);
			$alias=substr($f,8,strlen($f)-12);
			$f='payment'.ucfirst($alias);
			if(class_exists($f) && method_exists($f,'settingForm')) {
				$item=$f::settingForm((isset($cfg[$alias]) ? $cfg[$alias] : null));
				if(!is_array($item)) continue;
				if(is_object($item['content'])) {
					if(!$item['content']->action) $item['content']->action='?controller=payment&action=method&method='.$alias;
				}
				$item['alias']=$alias;
				$item['rate']=(isset($cfg[$alias]) ? $cfg[$alias]['rate'] : 0);
				$data[]=$item;
			}
		}
		closedir($d);
		return $data;
	}

	public static function form($rate=null) {
		if(is_array($rate)) $rate=$rate['rate'];
		elseif(!$rate) $rate=1;
		$form=core::form('payment');
		$form->checkbox('active','Метод платежа включён',($rate ? true : false));
		$form->text('rate','Курс метода платежа',$rate);
		return $form;
	}

	public static function saveRate($method,$rate) {
		core::import('admin/core/config');
		$cfg=new config('payment');
		$data=$cfg->$method;
		if(!is_array($data)) return false;
		$data['rate']=floatval(str_replace(',','.',$rate));
		$cfg->$method=$data;
		return $cfg->save('payment');
	}

}


interface paymentSetting {
	public static function settingForm($config);
	public static function settingSubmit($data);
}