<?php
core::import('model/log');
class paymentPerfectmoney extends payment {
	public $title='Perfect Money';

	public function formData($paymentId,$amount=null) {
		$cfg=$this->config();
		$form=array('action'=>'https://perfectmoney.is/api/step1.asp','method'=>'POST','field'=>array(
			array('type'=>'hidden','name'=>'PAYMENT_ID','value'=>$paymentId),
			array('type'=>'hidden','name'=>'PAYEE_ACCOUNT','value'=>$cfg['wallet']),
			array('type'=>'hidden','name'=>'PAYEE_NAME','value'=>$cfg['title']),
			array('type'=>'hidden','name'=>'PAYMENT_UNITS','value'=>'USD'),
			array('type'=>'hidden','name'=>'STATUS_URL','value'=>'http://'.$_SERVER['HTTP_HOST'].'/payment/perfectmoney/status'),
			array('type'=>'hidden','name'=>'PAYMENT_URL','value'=>'http://'.$_SERVER['HTTP_HOST'].'/payment/perfectmoney/success'),
			array('type'=>'hidden','name'=>'PAYMENT_URL_METHOD','value'=>'POST'),
			array('type'=>'hidden','name'=>'NOPAYMENT_URL','value'=>'http://'.$_SERVER['HTTP_HOST'].'/payment/perfectmoney/fail'),
			array('type'=>'hidden','name'=>'NOPAYMENT_URL_METHOD','value'=>'POST'),
			array('type'=>'hidden','name'=>'SUGGESTED_MEMO','value'=>'')
		));
		if($amount) $form['field'][]=array('type'=>'hidden','name'=>'PAYMENT_AMOUNT','value'=>$amount);
		else $form['field'][]=array('type'=>'text','name'=>'PAYMENT_AMOUNT','title'=>LNGAmount.', USD','value'=>'0');
		$form['field'][]=array('type'=>'submit','name'=>'PAYMENT_METHOD','value'=>LNGPayNow);
		return $form;
	}

	public function getPaymentId($action) {
		return $_POST['PAYMENT_ID'];
	}

	public function validate($action,$info) {
		$cfg=$this->config();
		$hash=strtoupper(md5($info['id'].':'.$cfg['wallet'].':'.$_POST['PAYMENT_AMOUNT'].':'.$_POST['PAYMENT_UNITS'].':'.$_POST['PAYMENT_BATCH_NUM'].':'.$_POST['PAYER_ACCOUNT'].':'.strtoupper(md5($cfg['secret'])).':'.$_POST['TIMESTAMPGMT']));
		if($hash==$_POST['V2_HASH']) return true; else return false;
	}

	public function getAmount($action,$paymentInfo) {
		return $_POST['PAYMENT_AMOUNT'];
	}

}