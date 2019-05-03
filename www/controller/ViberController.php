<?php
namespace plushka\controller;
use plushka;
use plushka\model\Notification;
use plushka\model\User;

class ViberController extends \plushka\core\Controller {

	//AJAX. Генерация кода авторизации
	public function actionInit() {
		$user=plushka::user()->model();
		if(!$user->id) exit;
		$code=rand(1000,9999);
		$user->attribute('viber',$code);
		echo "OK\n".$code;
	}

	//AJAX. Возвращает код. Этот метод нужен для отлавливания момента подключения вайбера (ввода кода)
	public function actionUserId() {
		$user=plushka::user()->model();
		if(!$user->id) exit;
		$viber=$user->attribute('viber');
		echo "OK\n";
		if(strlen($viber)===24) {
			$user->viber=$viber;
			echo $viber;
		}
	}

	//Обработчик-получатель событий, вызывается сервером viber.
	public function actionHook() {
		$request=json_decode(file_get_contents('php://input'),true);
		$method='_hook'.ucfirst($request['event']);
		if(!method_exists($this,$method)) $method='_hookUnknown';
		$this->$method($request);
	}

	public function _hookMessage($data) {
		$sender=$data['sender']['id'];
		if(strlen($sender)!=24) self::_exit();
		$code=(int)trim($data['message']['text']);
		if($code<1000 || $code>9999) self::_exit();

		$user=new User();
		$db=plushka::db();
		$user->load('data LIKE '.$db->escape('%"viber":'.$code.'%'));
		if(!$user->id) self::_exit();
		$user->attribute('viber',$sender);
		$db->query('UPDATE user SET viber='.$db->escape($sender).' WHERE viber='.$code);
		self::_exit();
	}

	private function _hookUnknown($data) {
		self::_exit();
	}

	private static function _exit() {
		echo json_encode(array(
			'event'=>'webhook',
			'timestamp'=>time(),
			'message_token'=>time()
		));
		exit;
	}

}