<?php
namespace plushka\model;
use InvalidArgumentException;
use plushka\core\plushka;

/**
 * Хелпер, реализующий регистрацию и авторизацию OAuth
 */
class Oauth {

	/**
	 * Выполняет редирект пользователя на сервер OAuth
	 * Прерывает работу.
	 * @param string $id       ID сервера авторизации (см. /config/oauth.php)
	 * @param string $backlink URL страницы возврата
	 * @throws InvalidArgumentException
	 */
	public static function redirect(string $id,string $backlink): void {
		$data=plushka::config('oauth',$id);
		if($data===null) throw new InvalidArgumentException('Unknown OAuth server '.$id);
		header('Location: '.self::_linkCode($id,$data[0],$backlink));
		exit;
	}

	/**
	 * Возвращет массив с данными авторизации или NULL, если авторизация не удалась
	 * @param string $id       ID сервера авторизации (см. /config/oauth.php)
	 * @param string $backlink URL страницы возврата
	 * @return array|null
	 * @throws InvalidArgumentException
	 */
	public static function getAnswer(string $id,string $backlink): ?array {
		plushka::language('oauth');
		if(isset($_REQUEST['error'])===true) {
			if(isset($_REQUEST['error_description'])===true) plushka::error(urldecode($_REQUEST['error_description']));
			else plushka::error(LNGLogInFailed);
			return null;
		}
		$data=plushka::config('oauth',$id);
		if($data===null) throw new InvalidArgumentException('Unknown OAuth server '.$id);
		$answer=self::_load(self::_linkToken($id,$data[0],$data[1],$_REQUEST['code'],$backlink)); //запрос токена
		if(!$answer) return null;
		if(isset($answer['id'])===false || isset($answer['email'])===false) { //ВКонтакте сразу возвращает необходимые данные - можно сэкономить на одном запросе
			$answer=self::_load(self::_linkInfo($id,$answer['access_token']));
			if(!$answer) return null;
		}
		if(isset($answer['email'])===false) $answer['email']=null;
		return $answer;
	}

	/**
	 * Возвращает информацию о пользователе, если он был зарегистрирован ранее или NULL
	 * @param string $socialId Идентификатор социальной сети ("vk", "facebook")
	 * @param string $answerId Идентификатор пользователя на удалённом сервере
	 * @return string[]|null
	 */
	public static function getUser(string $socialId,string $answerId): ?array {
		$db=plushka::db();
		/** @noinspection SqlResolve */
		return $db->fetchArrayOnceAssoc('SELECT u.id,u.groupId,u.login,u.email FROM oauth o LEFT JOIN user u ON u.id=o.userId WHERE o.id='.$db->escape($answerId).' AND o.social='.$db->escape($socialId));
	}

	/**
	 * Загружает URL $link и анализирует ответ. Устанавливает сообщения об ошибке, если она произошла. Возвращает ответ в виде массива
	 * @param string $link
	 * @return bool|false|mixed|string
	 */
	private static function _load(string $link): ?array {
		if(function_exists('curl_init')) {
			$ch=curl_init($link);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$data=curl_exec($ch);
		} else $data=file_get_contents($link);
		if(!$data) {
			plushka::error(LNGLogInFailed);
			return null;
		}
		if($data[0]=='{') $data=json_decode($data,true); else parse_str($data,$data);
		if(isset($data['error'])) {
			if(isset($data['error_description'])) plushka::error($data['error_description']);
			else plushka::error(LNGLogInFailed);
			return null;
		}
		if(isset($data['user_id'])) { //разные соц. сети именуют это поле по разному
			$data['id']=$data['user_id'];
			unset($data['user_id']);
		}
		return $data;
	}

	/**
	 * Возвращает URL страницы соц.сети, открывающей сессию авторизации (первый запрос)
	 * @param string $id       Идентификатор соц. сети
	 * @param string $appId    Идентификатор приложения в соц. сети
	 * @param string $backlink URL, на который нужно вернуть пользователя
	 * @return string URL
	 */
	private static function _linkCode(string $id,string $appId,string $backlink): string {
		$backlink=urlencode(plushka::link($backlink,true,true));
		switch($id) {
			case 'vk':
				return 'https://oauth.vk.com/authorize?client_id='.$appId.'&scope=email&redirect_uri='.$backlink.'&response_type=code';
			case 'facebook':
				return 'https://www.facebook.com/dialog/oauth?client_id='.$appId.'&scope=email&redirect_uri='.$backlink.'&response_type=code';
			default:
				throw new InvalidArgumentException('Unknown OAuth server: '.$id);
		}
	}

	/**
	 * Возвращает URL страницы соц.сети, возвращающей токен (второй запрос)
	 * @param string $id       Идентификатор соц. сети
	 * @param string $appId    Идентификатор приложения в соц. сети
	 * @param string $secret   Секретный ключ доступа
	 * @param string $code     Код сессии
	 * @param string $backlink URL, на который нужно вернуть пользователя
	 * @return string URL
	 */
	private static function _linkToken(string $id,string $appId,string $secret,string $code,string $backlink): string {
		$backlink=urlencode(plushka::link($backlink,true,true));
		switch($id) {
			case 'vk':
				return 'https://oauth.vk.com/access_token?client_id='.$appId.'&client_secret='.$secret.'&code='.$code.'&redirect_uri='.$backlink;
			case 'facebook':
				return 'https://graph.facebook.com/oauth/access_token?client_id='.$appId.'&client_secret='.$secret.'&code='.$code.'&redirect_uri='.$backlink.'&scope=email';
			default:
				throw new InvalidArgumentException('Unknown OAuth server: '.$id);
		}
	}

	/**
	 * Возвращает URL страницы соц.сети, возвращающей информацию о пользователе (третий запрос)
	 * @param string $id          Идентификатор соц. сети
	 * @param string $accessToken Токен доступа
	 * @return string URL
	 */
	private static function _linkInfo(string $id,string $accessToken): string {
		switch($id) {
			case 'facebook':
				return 'https://graph.facebook.com/me?access_token='.$accessToken.'&scope=email';
			default:
				throw new InvalidArgumentException('Unknown OAuth server: '.$id);
		}
	}

}