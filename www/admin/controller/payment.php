<?php class sController extends controller {

	protected function right() {
		return array(
			'Method'=>'payment.method',
			'Log'=>'payment.log'
		);
	}

	//Включение/выключение режима песочницы
	public function actionSundbox() {
		core::import('admin/core/config');
		$cfg=new config('payment');
		$cfg->sandbox=!$cfg->sandbox;
		$cfg->save('payment');
		core::redirect('?controller=payment&action=method');
	}

	//Форма настройки платёжных систем
	public function actionMethod() {
		$this->button('?controller=payment&action=log','list','Лог');
		core::import('admin/model/payment');
		$this->payment=payment::methodList();
		$this->sandbox=core::config('payment');
		$this->sandbox=$this->sandbox['sandbox'];
		return 'Method';
	}

	public function actionMethodSubmit($data) {
		$method=core::translit($_GET['method']);
		$f=core::path().'admin/model/payment-'.$method.'.php';
		if(!file_exists($f)) {
			controller::$error='Платёжный модуль '.$method.' не установлен';
			return false;
		}
		core::import('admin/model/payment');
		include_once($f);
		$f='payment'.ucfirst($method);
		if(!class_exists($f) || !method_exists($f,'settingSubmit')) {
			controller::$error='Платёжный модуль '.$method.' не может быть использован';
			return false;
		}
		if(!$f::settingSubmit($data)) return false;
		if(!isset($data['active'])) $data['rate']=0;
		if(!payment::saveRate($method,$data['rate'])) return;
		core::redirect('?controller=payment&action=method','Сохранено');
	}

	//Лог последних платежей
	public function actionLog() {
		$db=core::db();
		$db->query('SELECT p.id,p.userId,p.date,p.amount,p.method,p.status,u.login,p.data FROM payment p LEFT JOIN user u ON u.id=p.userId ORDER BY p.date DESC',200);
		$table=core::table();
		$table->rowTh('ID|Дата|Сумма|Способ оплаты||Пользователь|');
		while($item=$db->fetch()) {
			$table->text($item[0]);
			$table->text(date('d.m.Y H:i:s',$item[2]));
			$table->text($item[3]);
			$table->text($item[4]);
			$table->text($item[5]);
			$table->link($item[6],'user/userItem&id='.$item[1]);
			$table->text($item[7]);
		}
		return $table;
	}

}