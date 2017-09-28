<?php
class paymentWmcard extends payment {
	public $title='Карты предоплаты WebMoney';

	public function formData($paymentId,$amount=null) {
		$cfg=$this->config();
		$form=core::form('payment');
		$form->text('number','Номер карты');
		$form->text('code','Код активации');
		$cfg=$this->config();
		$wallet=array();
		if($cfg['wmr']) $wallet[]='WMR';
		if($cfg['wmu']) $wallet[]='WMU';
		if($cfg['wmz']) $wallet[]='WMZ';
		if($cfg['wme']) $wallet[]='WME';
		$wallet=implode(', ',$wallet);
		unset($cfg);
		core::language('wmcard');
		$form->label('','* '.sprintf(LNGYouCanUseCards,$wallet));
		$form->submit(LNGPayNow);
		$form->action='payment/wmcard/post';
		return $form;
	}

	public function actionPost() {
		return $this->formData();
	}

	public function actionPostSubmit($data) {
		$data['number']=intVal($data['number']);
		$data['code']=intVal($data['code']);
		core::language('wmcard');
		if(!$data['number'] || !$data['code']) {
			core::error(LNGCardDataIsWrong);
			return false;
		}
		$user=core::user();
		$db=core::db();
		$db->query('INSERT INTO payment (userId,method,date,data) VALUES('.$user->id.','.$db->escape('wmcard').','.time().','.$db->escape(json_encode($data)).')');
		$id=$db->insertId();
		core::import('core/email');
		$email=new email();
		$cfg=core::config();
		$email->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
		$email->replyTo($user->email,$user->login);
		$email->subject('Платёж картой WebMoney');
		$email->message('<p>Пользователь: <b>'.$user->login.'</b><br />
		Номер карты: <b>'.$data['number'].'</b><br />
		Код: <b>'.$data['code'].'</b></p>
		<p>Для подтверждения карты перейдите по ссылке: <a href="http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?controller=wmcard&id='.$id.'">http://'.$_SERVER['HTTP_HOST'].'/admin/index2.php?controller=wmcard&id='.$id.'</a></p>');
		$email->send($cfg['adminEmailEmail']);
		core::redirect('payment/wmcard/success');
	}

	public function getPaymentId($action) {
		return true;
	}
	public function validate($action,$info) {
		return true;
	}
	public function getAmount($action,$paymentInfo) {
		return true;
	}

	public static function getInfo($id) {
		return true;
	}


}