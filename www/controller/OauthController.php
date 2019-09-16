<?php
namespace plushka\controller;
use plushka\core\HTTPException;
use plushka\core\plushka;
use plushka\model\Oauth;
/* Реализует регистрацию и авторизацию OAuth 2.0
URL: oauth/ID - авторизация через сервер ID; oauth/return/ID - адрес возврата, на который сервер авторизации возвращает пользователя */
class OauthController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		if($this->url[1]==='return') $this->id=$_GET['corePath'][2];
		else {
			$this->id=$_GET['corePath'][1];
			$this->url[1]='redirect';
		}
		plushka::language('oauth');
	}

	//Редирект на сервер авторизации (соц.сеть) для запроса разрешений
	public function actionRedirect() {
		if(!Oauth::redirect($this->id,'oauth/return/'.$this->id)) throw new HTTPException(404);
	}

	//Анализирует возвращённый сервером ответ
	public function actionReturn() {
		$data=Oauth::getAnswer($this->id,'oauth/return/'.$this->id);
		if(!$data) return 'Answer';
		$user=Oauth::getUser($this->id,$data['id']);
		if(!$user) { //пользователь не был зарегистрирован ранее
			$userGroup=plushka::config('oauth');
			$userGroup=$userGroup['userGroup'];
			if(!$userGroup) {
				plushka::error(LNGYouDontRegisteredThisSite);
				return 'Answer';
			}
			$user=plushka::user()->model();
			if(!$user->create(($data['email'] ? $data['email'] : $data['name']),false,$data['email'],1,$userGroup)) return '_empty';
		}
		plushka::redirect('user',LNGYouLoginAs.' '.$user->login);
	}

}