<?php
/* Реализует регистрацию и авторизацию OAuth 2.0
URL: oauth/ID - авторизация через сервер ID; oauth/return/ID - адрес возврата, на который сервер авторизации возвращает пользователя */
class sController extends controller {

	public function __construct() {
		parent::__construct();
		if($this->url[1]=='Return') $this->id=$_GET['corePath'][2];
		else {
			$this->id=$_GET['corePath'][1];
			$this->url[1]='Redirect';
		}
	}

	/* Редирект на сервер авторизации (соц.сеть) для запроса разрешений */
	public function actionRedirect() {
		$data=core::config('oauth');
		if(!isset($data[$this->id])) core::error404();
		$data=$data[$this->id];
		header('Location: '.self::_linkCode($this->id,$data[0]));
		exit;
	}

	/* Анализирует возвращённый сервером ответ */
	public function actionReturn() {
		if(isset($_REQUEST['error'])) {
			if(isset($_REQUEST['error_description'])) controller::$error=urldecode($_REQUEST['error_description']);
			else controller::$error='Авторизоваться не удалось';
			return 'Answer';
		}
		$data=core::config('oauth');
		if(!isset($data[$this->id])) core::error404();
		$userGroup=$data['userGroup'];
		$data=$data[$this->id];
		$answer=self::_load(self::_linkToken($this->id,$data[0],$data[1],$_REQUEST['code'])); //запрос токена
		if(!$answer) return 'Answer';

		if(!isset($answer['id']) || !isset($answer['email'])) { //ВКонтакте сразу возвращает необходимые данные - можно сэкономить на одном запросе
			$answer=self::_load(self::_linkInfo($this->id,$answer['access_token']));
			if(!$answer) return 'Answer';
		}

		if(!isset($answer['email'])) $answer['email']=null;
		$db=core::db();
		$data=$db->fetchArrayOnceAssoc('SELECT u.id,u.groupId,u.login,u.email FROM oauth o LEFT JOIN user u ON u.id=o.userId WHERE o.id='.$db->escape($answer['id']).' AND o.social='.$db->escape($this->id));
		if(!$data) { //пользователь не был зарегистрирован ранее
			if(!$userGroup) {
				controller::$error='Вы не зарегистрированы на этом сайте';
				return 'Answer';
			}
			$user=core::user()->model();
			if(!$user->create($this->id.($db->fetchValue('SELECT MAX(id) FROM user')+1),null,$answer['email'],1,$userGroup)) return false;
//			$data=array(
//				'id'=>null,
//				'groupId'=>$userGroup,
//				'login'=>$this->id.($db->fetchValue('SELECT MAX(id) FROM user')+1),
//				'email'=>$answer['email']
//			);
//			$db->query('INSERT INTO user SET groupId='.$data['groupId'].',login='.$db->escape($data['login']).',password='.$db->escape($data['login'].time()).',email='.$db->escape($data['email']).',status=1');
//			$data['id']=$db->insertId();
//			$db->query('INSERT INTO oauth SET social='.$db->escape($this->id).',id='.$db->escape($answer['id']).',userId='.$data['id']);
//			core::hook('userCreate',$data['id'],$data['login'],$data['email']);

		}
//		$u=core::user();
//		$u->id=$data['id'];
//		$u->group=$data['groupId'];
//		$u->login=$data['login'];
//		$u->email=$data['email'];
		core::redirect('','Вы вошли как '.$user->login);
	}

	/* Возвращает URL страницы соц.сети, открывающей сессию авторизации (первый запрос) */
	private static function _linkCode($id,$appId) {
		$backlink=urlencode('http://'.$_SERVER['HTTP_HOST'].core::link('oauth/return/'.$id));
		switch($id) {
		case 'vk':
			return 'https://oauth.vk.com/authorize?client_id='.$appId.'&scope=email&redirect_uri='.$backlink.'&response_type=code';
		case 'facebook':
			return 'https://www.facebook.com/dialog/oauth?client_id='.$appId.'&scope=email&redirect_uri='.$backlink.'&response_type=code';
		}
	}

	/* Возвращает URL страницы соц.сети, возвращающей токен (второй запрос) */
	private static function _linkToken($id,$appId,$secret,$code) {
		$backlink=urlencode('http://'.$_SERVER['HTTP_HOST'].core::link('oauth/return/'.$id));
		switch($id) {
		case 'vk':
			return 'https://oauth.vk.com/access_token?client_id='.$appId.'&client_secret='.$secret.'&code='.$code.'&redirect_uri='.$backlink;
		case 'facebook':
			return 'https://graph.facebook.com/oauth/access_token?client_id='.$appId.'&client_secret='.$secret.'&code='.$code.'&redirect_uri='.$backlink;
		}
	}

	/* Возвращает URL страницы соц.сети, возвращающей информацию о пользователе (третий запрос) */
	private static function _linkInfo($id,$accessToken) {
		switch($id) {
		case 'facebook':
			return 'https://graph.facebook.com/me?access_token='.$accessToken;
		}
	}

	/* Загружает URL $link и анализирует ответ. Устанавливает сообщения об ошибке, если она произошла. Возвращает ответ в виде массива */
	private static function _load($link) {
		if(function_exists('curl_init')) {
			$ch=curl_init($link);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$data=curl_exec($ch);
		} else $data=file_get_contents($link);
		if(!$data) {
			controller::$error='Авторизация не удалась';
			return false;
		}
		if($data[0]=='{') $data=json_decode($data,true); else parse_str($data,$data);
		if(isset($data['error'])) {
			if(isset($data['error_description'])) controller::$error=$data['error_description'];
			else controller::$error='Авторизоваться не удалось';
			return false;
		}
		if(isset($data['user_id'])) { //разные соц. сети именуют это поле по разному
			$data['id']=$data['user_id'];
			unset($data['user_id']);
		}
		return $data;
	}

}
?>