<?php class paymentDigiseller extends payment {

	public $title='Digiseller';

	public function formData($paymentId,$amount=null) {
		$cfg=$this->config();
		$form=array('action'=>'https://www.oplata.info/asp2/pay_wm.asp','method'=>'GET','field'=>array(
			array('type'=>'hidden','name'=>'id_d','value'=>$cfg['productId']),
			array('type'=>'hidden','name'=>'lang','value'=>_LANG),
			array('type'=>'submit','value'=>LNGPayNow)
		));
		return $form;
	}

	public function getPaymentId($action) {
		$db=core::db();
		$id=$db->fetchValue('SELECT id FROM payment WHERE method='.$db->escape('digiseller').' AND data='.$db->escape($_GET['uniquecode']));
		if($id) return $id;
		if($action!='status') return false;
		$db->query('INSERT INTO payment(userId,method,date,data) VALUES('.core::userId().','.$db->escape('digiseller').','.time().','.$db->escape($_GET['uniquecode']).')');
		return $db->insertId();
	}

	public function validate($action,$info) {
		core::import('model/request');
		$request=new request();
		$cfg=$this->config();
		$sign=md5($cfg['id'].':'.$info['data'].':'.$cfg['password']);
		if($request->post('https://www.oplata.info/xml/check_unique_code.asp','<?xml version="1.0" encoding="utf-8"?>
		<digiseller.request>
		<id_seller>'.$cfg['id'].'</id_seller>
		<unique_code>'.$info['data'].'</unique_code>
		<sign>'.$sign.'</sign>
		</digiseller.request>')!=200) return false;
		$xml=new XMLReader();
		$xml->XML($request->content());
		$data=array('retval'=>'','id_seller'=>'','inv'=>'','amount'=>'','type_curr'=>'','email'=>'');
		while($xml->read()) {
			$name=$xml->name;
			if(!isset($data[$name]) || $xml->nodeType!=XMLReader::ELEMENT) continue;
			$xml->read();
			$data[$name]=$xml->value;
		}
		if($data['revtavl']!=0) return false;
		$_GET['digisellerAmount']=$data['amount'];
		return true;
	}

	public function getAmount($action,$paymentInfo) {
		$amount=$_GET['digisellerAmount'];
		unset($_GET['digisellerAmount']);
		return $amount;
	}

	public function actionStatus() {
		core::redirect('payment/digiseller/success?uniquecode='.$_GET['uniquecode']);
	}

}