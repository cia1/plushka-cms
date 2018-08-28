<?php class sController extends controller {

	//AJAX. Генерация кода авторизации
	public function actionInit() {
		core::import('model/notification');
		$user=core::user()->model();
		if(!$user->id) exit;
		$code=rand(1000,9999);
		$user->attribute('viber',$code);
		echo "OK\n".$code;
	}

	//AJAX. Возвращает код. Этот метод нужен для отлавливания момента подключения вайбера (ввода кода)
	public function actionUserId() {
		$user=core::user()->model();
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

		core::import('model/user');
		$user=new modelUser();
		$db=core::db();
		$user->load('data LIKE '.$db->escape('%"viber":'.$code.'%'));
		if(!$user->id) self::_exit();
		$user->attribute('viber',$sender);
		$db->query('UPDATE user SET viber='.$db->escape($sender).' WHERE viber='.$code);
		self::_exit();
	}

	private function _hookUnknown($data) {
/*
		$f=fopen($_SERVER['DOCUMENT_ROOT'].'/tmp/VIBER-'.microtime().'.txt','w');
		ob_start();
		var_dump($data);
		fwrite($f,ob_get_contents());
		fclose($f);
		ob_end_clean();
*/
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