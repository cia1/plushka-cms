<?php
namespace plushka\model;
use plushka;

class Oauth {

	//Выполняет редирект пользователя на сервер OAuth
	public static function redirect($id,$backlink) {
		$data=plushka::config('oauth');
		if(!isset($data[$id])) return false;
		$data=$data[$id];
		header('Location: '.self::_linkCode($id,$data[0],$backlink));
		exit;
	}

	//Возвращет массив с данными авторизации или false, если авторизация не удалась
	// Возвращаемые данные: id, email, и д.р.
	public static function getAnswer($id,$backlink) {
		plushka::language('oauth');
		if(isset($_REQUEST['error'])) {
			if(isset($_REQUEST['error_description'])) plushka::error(urldecode($_REQUEST['error_description']));
			else plushka::error(LNGLogInFailed);
			return false;
		}
		$data=plushka::config('oauth');
		if(!isset($data[$id])) return false;
		$data=$data[$id];
		$answer=self::_load(self::_linkToken($id,$data[0],$data[1],$_REQUEST['code'],$backlink)); //запрос токена
		if(!$answer) return false;
		if(!isset($answer['id']) || !isset($answer['email'])) { //ВКонтакте сразу возвращает необходимые данные - можно сэкономить на одном запросе
			$answer=self::_load(self::_linkInfo($id,$answer['access_token']));
			if(!$answer) return false;
		}

		if(!isset($answer['email'])) $answer['email']=null;
		return $answer;
	}

	//Возвращает информацию о пользователе, если он был зарегистрирован ранее или false
	public static function getUser($socialId,$answerId) {
		$db=plushka::db();
		return $db->fetchArrayOnceAssoc('SELECT u.id,u.groupId,u.login,u.email FROM oauth o LEFT JOIN user u ON u.id=o.userId WHERE o.id='.$db->escape($answerId).' AND o.social='.$db->escape($socialId));
	}

	//Загружает URL $link и анализирует ответ. Устанавливает сообщения об ошибке, если она произошла. Возвращает ответ в виде массива
	private static function _load($link) {
		if(function_exists('curl_init')) {
			$ch=curl_init($link);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$data=curl_exec($ch);
		} else $data=file_get_contents($link);
		if(!$data) {
			plushka::error(LNGLogInFailed);
			return false;
		}
		if($data[0]=='{') $data=json_decode($data,true); else parse_str($data,$data);
		if(isset($data['error'])) {
			if(isset($data['error_description'])) plushka::error($data['error_description']);
			else plushka::error(LNGLogInFailed);
			return false;
		}
		if(isset($data['user_id'])) { //разные соц. сети именуют это поле по разному
			$data['id']=$data['user_id'];
			unset($data['user_id']);
		}
		return $data;
	}

	//Возвращает URL страницы соц.сети, открывающей сессию авторизации (первый запрос)
	private static function _linkCode($id,$appId,$backlink) {
		$backlink=urlencode(plushka::link($backlink,true,true));
		switch($id) {
		case 'vk':
			return 'https://oauth.vk.com/authorize?client_id='.$appId.'&scope=email&redirect_uri='.$backlink.'&response_type=code';
		case 'facebook':
			return 'https://www.facebook.com/dialog/oauth?client_id='.$appId.'&scope=email&redirect_uri='.$backlink.'&response_type=code';
		}
	}

	//Возвращает URL страницы соц.сети, возвращающей токен (второй запрос)
	private static function _linkToken($id,$appId,$secret,$code,$backlink) {
		$backlink=urlencode(plushka::link($backlink,true,true));
		switch($id) {
		case 'vk':
			return 'https://oauth.vk.com/access_token?client_id='.$appId.'&client_secret='.$secret.'&code='.$code.'&redirect_uri='.$backlink;
		case 'facebook':
			return 'https://graph.facebook.com/oauth/access_token?client_id='.$appId.'&client_secret='.$secret.'&code='.$code.'&redirect_uri='.$backlink.'&scope=email';
		}
	}

	//Возвращает URL страницы соц.сети, возвращающей информацию о пользователе (третий запрос)
	private static function _linkInfo($id,$accessToken) {
		switch($id) {
		case 'facebook':
			return 'https://graph.facebook.com/me?access_token='.$accessToken.'&scope=email';
		}
	}

}