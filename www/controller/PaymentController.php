<?php
namespace plushka\controller;
use plushka;
use plushka\model\Payment;

class PaymentController extends \plushka\core\Controller {

	function __construct() {
		parent::__construct();
		if(count($this->url)<3) plushka::error404();
		$this->payment=Payment::instance(lcfirst($this->url[1]));
		if(!$this->payment) plushka::error404();
		$this->url[1]=$this->url[2];
		if($this->url[1]!=='status' && $this->url[1]!=='success' && $this->url[1]!=='fail') $this->url[1]='modelMethod';
	}

	//Обновление статуса платежа. Вызывается сервером платёжной системы.
	public function actionStatus() {
		$paymentInfo=$this->payment->getPaymentId('status');
		if($paymentInfo) $paymentInfo=$this->payment->getInfo($paymentInfo);
		if(!$paymentInfo) {
			plushka::language('payment');
			die(LNGPaymentDataIsWrong);
		}
		if($paymentInfo['status']!='request') {
			plushka::language('payment');
			die(LNGPaymentDataIsWrong);
		}
		if(!$this->payment->validate('status',$paymentInfo)) {
			plushka::language('payment');
			die(LNGPaymentDataIsWrong);
		}
		$paymentInfo['method']=$_GET['corePath'][1];
		$rate=plushka::config('payment');
		$rate=$rate[$paymentInfo['method']]['rate'];
		$paymentInfo['rate']=$rate;
		$paymentInfo['amount']=(float)$this->payment->getAmount('status',$paymentInfo);
		$paymentInfo['status']='success';
		Payment::updatePaymentInfo($paymentInfo);
		$result=plushka::hook('paymentStatus',$paymentInfo);
		if(!$result[0]) die(plushka::error(false));
		if(method_exists($this->payment,'actionStatus')) return $this->payment->actionStatus();
	}

	//Оповещение пользователя об успешном платеже
	public function actionSuccess() {
		plushka::language('payment');
		$paymentInfo=$this->payment->getPaymentId('success');
		if($paymentInfo) $paymentInfo=$this->payment->getInfo($paymentInfo);
		if(!$paymentInfo) {
			plushka::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		if(!$this->payment->validate('success',$paymentInfo)) {
			plushka::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		$paymentInfo['method']=$_GET['corePath'][1];
		$rate=plushka::config('payment');
		$rate=$rate[$paymentInfo['method']]['rate'];
		$paymentInfo['rate']=$rate;
		$paymentInfo['amount']=(float)$this->payment->getAmount('success',$paymentInfo);
		unset($_SESSION['paymentId']);
		$data=plushka::hook('paymentSuccess',$paymentInfo);
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
		plushka::language('payment');
		$paymentInfo=$this->payment->getPaymentId('fail');
		if($paymentInfo) $paymentInfo=payment::getInfo($paymentInfo);
		if(!$paymentInfo) {
			plushka::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		if(!$this->payment->validate('fail',$paymentInfo)) {
			plushka::error(LNGPaymentDataIsWrong);
			return '_empty';
		}
		$paymentInfo['method']=$_GET['corePath'][1];
		$rate=plushka::config('payment');
		$rate=$rate[$paymentInfo['method']]['rate'];
		$paymentInfo['rate']=$rate;
		$paymentInfo['amount']=(float)$this->payment->getAmount('fail',$paymentInfo);
		$paymentInfo['status']='cancel';
		unset($_SESSION['paymentId']);
		Payment::updatePaymentInfo($paymentInfo);
		plushka::hook('paymentFail',$paymentInfo);
		$this->paymentInfo=$paymentInfo;
		$this->pageTitle=$this->metaTitle=LNGPaymentFail;
		return '_empty';
	}

	//Произвольный запрос от платёжного сервера или от самого сайта
	public function actionModelMethod() {
		plushka::language('payment');
		$action='action'.ucfirst($this->url[2]);
		if(!method_exists($this->payment,$action)) plushka::error404();
		$this->pageTitle=$this->metaTitle=$this->payment->title;
		return $this->payment->$action();
	}

	public function actionModelMethodSubmit($data) {
		plushka::language('payment');
		$action='action'.ucfirst($this->url[2]).'Submit';
		if(!method_exists($this->payment,$action)) return false;
		return $this->payment->$action($data);
	}
}