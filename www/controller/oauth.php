<?php
/* Реализует регистрацию и авторизацию OAuth 2.0
URL: oauth/ID - авторизация через сервер ID; oauth/return/ID - адрес возврата, на который сервер авторизации возвращает пользователя */
class sController extends controller {

	public function __construct() {
		parent::__construct();
		if($this->url[1]==='return') $this->id=$_GET['corePath'][2];
		else {
			$this->id=$_GET['corePath'][1];
			$this->url[1]='redirect';
		}
		core::language('oauth');
	}

	//Редирект на сервер авторизации (соц.сеть) для запроса разрешений
	public function actionRedirect() {
		core::import('model/oauth');
		if(!oauth::redirect($this->id,'oauth/return/'.$this->id)) core::error404();
	}

	//Анализирует возвращённый сервером ответ
	public function actionReturn() {
		core::import('model/oauth');
		$data=oauth::getAnswer($this->id,'oauth/return/'.$this->id);
		if(!$data) return 'Answer';
		$user=oauth::getUser($this->id,$data['id']);
		if(!$user) { //пользователь не был зарегистрирован ранее
			$userGroup=core::config('oauth');
			$userGroup=$userGroup['userGroup'];
			if(!$userGroup) {
				core::error(LNGYouDontRegisteredThisSite);
				return 'Answer';
			}
			$user=core::user()->model();
			if(!$user->create(($data['email'] ? $data['email'] : $data['name']),false,$data['email'],1,$userGroup)) return '_empty';
		}
		core::redirect('user',LNGYouLoginAs.' '.$user->login);
	}

}