<?php
namespace plushka\model;
use plushka\core\plushka;

class PaymentRobokassa extends Payment {
	public $title='RoboKassa';

	public function formData($paymentId,$amount=null) {
		$cfg=$this->config();
		//MerchantLogin:(OutSum):InvId:Пароль#1
		$crc=$cfg['id'].':'.($amount ?  $amount : '').':'.$paymentId.':'.$cfg['password1'];
		$crc=md5($crc);
		$form=array('action'=>'https://auth.robokassa.ru/Merchant/Index.aspx','method'=>'GET','field'=>array(
			array('type'=>'hidden','name'=>'MerchantLogin','value'=>$cfg['id']),
			array('type'=>($amount ? 'hidden' : 'text'),'name'=>($amount ? 'OutSum' : 'FreeOutSum'),'title'=>LNGAmount,'value'=>$amount),
			array('type'=>'hidden','name'=>'InvDesc','value'=>$paymentId),
			array('type'=>'hidden','name'=>'SignatureValue','value'=>$crc),
			array('type'=>'hidden','name'=>'InvId','value'=>$paymentId),
			array('type'=>'hidden','name'=>'Culture','value'=>_LANG),
			array('type'=>'hidden','name'=>'Encoding','value'=>'utf-8')
		));
		$u=plushka::user();
		if($u->email) $form['field'][]=array('type'=>'hidden','name'=>'Email','value'=>$u->email);
		if($this->sandbox) $form['field'][]=array('type'=>'hidden','name'=>'IsTest','value'=>'1');
		$form['field'][]=array('type'=>'submit','value'=>LNGPayNow);
		return $form;
	}

	public function getPaymentId($action) {
		if($action=='status') return $_POST['InvId'];
		else return $_GET['InvId'];
	}

	public function validate($action,$info) {
		if($action=='status' || $action=='success') {
			$cfg=$this->config();
			if(isset($_POST['OutSum'])) {
				$hash=$_POST['OutSum'].':'.$_POST['InvId'].':'.$cfg['password2'];
				$hash=strtoupper(md5($hash));
				if($hash==$_POST['SignatureValue']) return true;
			} elseif(isset($_GET['OutSum'])) {
				$hash=$_GET['OutSum'].':'.$_GET['InvId'].':'.$cfg['password1'];
				$hash=md5($hash);
				if($hash==$_GET['SignatureValue']) return true;
			}
		} else {
			if(!$info || ($info['method']!='robokassa' && $info['method']!=null) || ($info['status']!='request' && $info['status']!='fail')) return false;
			return true;
		}
		return false;
	}

	public function getAmount($action,$paymentInfo) {
		if($action=='status') return $_POST['OutSum'];
		else return $_GET['OutSum'];
	}

	protected function config() {
		$cfg=parent::config();
		$u=plushka::userReal();
		if($this->sandbox) {
			$cfg['password1']=$cfg['password1Debug'];
			$cfg['password2']=$cfg['password2Debug'];
		}
		unset($cfg['password1Debug']);
		unset($cfg['password2Debug']);
		return $cfg;
	}

}