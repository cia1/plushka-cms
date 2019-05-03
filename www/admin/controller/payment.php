<?php
namespace plushka\admin\controller;

class sController extends controller {

	protected function right() {
		return array(
			'method'=>'payment.method',
			'log'=>'payment.log'
		);
	}

	//Включение/выключение режима песочницы
	public function actionSundbox() {
		plushka::import('admin/core/config');
		$cfg=new config('payment');
		$cfg->sandbox=!$cfg->sandbox;
		$cfg->save('payment');
		plushka::redirect('payment/method');
	}

	//Форма настройки платёжных систем
	public function actionMethod() {
		$this->button('payment/log','list','Лог');
		plushka::import('admin/model/payment');
		$this->payment=payment::methodList();
		$this->sandbox=plushka::config('payment');
		$this->sandbox=$this->sandbox['sandbox'];
		return 'Method';
	}

	public function actionMethodSubmit($data) {
		$method=plushka::translit($_GET['method']);
		$f=plushka::path().'admin/model/payment-'.$method.'.php';
		if(!file_exists($f)) {
			controller::$error='Платёжный модуль '.$method.' не установлен';
			return false;
		}
		plushka::import('admin/model/payment');
		include_once($f);
		$f='payment'.ucfirst($method);
		if(!class_exists($f) || !method_exists($f,'settingSubmit')) {
			controller::$error='Платёжный модуль '.$method.' не может быть использован';
			return false;
		}
		if(!$f::settingSubmit($data)) return false;
		if(!isset($data['active'])) $data['rate']=0;
		if(!payment::saveRate($method,$data['rate'])) return;
		plushka::redirect('payment/method','Сохранено');
	}

	//Лог последних платежей
	public function actionLog() {
		$db=plushka::db();
		$db->query('SELECT p.id,p.userId,p.date,p.amount,p.method,p.status,u.login,p.data FROM payment p LEFT JOIN user u ON u.id=p.userId ORDER BY p.date DESC',200);
		$table=plushka::table();
		$table->rowTh('ID|Дата|Сумма|Способ оплаты||Пользователь|');
		while($item=$db->fetch()) {
			$table->text($item[0]);
			$table->text(date('d.m.Y H:i:s',$item[2]));
			$table->text($item[3]);
			$table->text($item[4]);
			$table->text($item[5]);
			$table->link('user/userItem?id='.$item[1],$item[6]);
			$table->text($item[7]);
		}
		return $table;
	}

}