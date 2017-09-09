<?php
class sController extends controller {

	function __construct() {
		parent::__construct();
		if(count($this->url)<3) core::error404();
		core::import('model/payment');
		$this->payment=payment::instance(lcfirst($this->url[1]));
		if(!$this->payment) core::error404();
		$this->url[1]=ucfirst($this->url[2]);
		if($this->url[1]!='Status' && $this->url[1]!='Success' && $this->url[1]!='Fail') $this->url[1]='ModelMethod';
	}

	//Обновление статуса платежа. Вызывается сервером платёжной системы.
	public function actionStatus() {
		$paymentInfo=$this->payment->getPaymentId('status');
		if($paymentInfo) $paymentInfo=$this->payment->getInfo($paymentInfo);
		if(!$paymentInfo) {
			core::language('payment');
			die(LNGPaymentDataIsWrong);
		}
		if($paymentInfo['status']!='request') {
			core::language('payment');
			die(LNGPaymentDataIsWrong);
		}
		if(!$this->payment->validate('status',$paymentInfo)) {
			core::language('payment');
			die(LNGPaymentDataIsWrong);
		}
		$paymentInfo['method']=$_GET['corePath'][1];
		$rate=core::config('payment');
		$rate=$rate[$paymentInfo['method']]['rate'];
		$paymentInfo['rate']=$rate;
		$paymentInfo['amount']=(float)$this->payment->getAmount('status',$paymentInfo);
		$paymentInfo['status']='success';
		payment::updatePaymentInfo($paymentInfo);
		$result=core::hook('paymentStatus',$paymentInfo);
		if(!$result[0]) die(core::error(false));
		if(method_exists($this->payment,'actionStatus')) return $this->payment->actionStatus();
	}

	//Оповещение пользователя об успешном платеже
	public function actionSuccess() {
		core::language('payment');
		$paymentInfo=$this->payment->getPaymentId('success');
		if($paymentInfo) $paymentInfo=$this->payment->getInfo($paymentInfo);
		if(!$paymentInfo) {
			core::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		if(!$this->payment->validate('success',$paymentInfo)) {
			core::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		$paymentInfo['method']=$_GET['corePath'][1];
		$rate=core::config('payment');
		$rate=$rate[$paymentInfo['method']]['rate'];
		$paymentInfo['rate']=$rate;
		$paymentInfo['amount']=(float)$this->payment->getAmount('success',$paymentInfo);
		unset($_SESSION['paymentId']);
		$data=core::hook('paymentSuccess',$paymentInfo);
		for($i=count($data)-1;$i>=0;$i--) {
			if($data[$i]) {
				$view=$data[$i];
				break;
			}
		}
		unset($data);
		if(!$view) $view='_empty';
		$this->paymentInfo=$paymentInfo;
		$this->pageTitle=$this->metaTitle=LNGPaymentSuccess;
		$this->content=sprintf(LNGPaymentSuccessMessage,$paymentInfo['amount']);
		return $view;
	}

	public function actionFail() {
		core::language('payment');
		$paymentInfo=$this->payment->getPaymentId('fail');
		if($paymentInfo) $paymentInfo=payment::getInfo($paymentInfo);
		if(!$paymentInfo) {
			core::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		if(!$this->payment->validate('fail',$paymentInfo)) {
			core::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		$paymentInfo['method']=$_GET['corePath'][1];
		$rate=core::config('payment');
		$rate=$rate[$paymentInfo['method']]['rate'];
		$paymentInfo['rate']=$rate;
		$paymentInfo['amount']=(float)$this->payment->getAmount('fail',$paymentInfo);
		$paymentInfo['status']='cancel';
		unset($_SESSION['paymentId']);
		payment::updatePaymentInfo($paymentInfo);
		core::hook('paymentFail',$paymentInfo);
		$this->paymentInfo=$paymentInfo;
		$this->pageTitle=$this->metaTitle=LNGPaymentFail;
		return '_empty';
	}

	//Произвольный запрос от платёжного сервера или от самого сайта
	public function actionModelMethod() {
		core::language('payment');
		$action='action'.ucfirst($this->url[2]);
		if(!method_exists($this->payment,$action)) core::error404();
		$this->pageTitle=$this->metaTitle=$this->payment->title;
		return $this->payment->$action();
	}

	public function actionModelMethodSubmit($data) {
		core::language('payment');
		$action='action'.ucfirst($this->url[2]).'Submit';
		if(!method_exists($this->payment,$action)) return false;
		return $this->payment->$action($data);
	}
}