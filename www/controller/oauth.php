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
		$data=oauth::getAnswer('oauth/return/'.$this->id);
		if(!$data) return 'Answer';

		$db=core::db();
		$user=oauth::getUser($this->id,$data['id']);
		if(!$user) { //пользователь не был зарегистрирован ранее
			if(!$userGroup) {
				core::error(LNGYouDontRegisteredThisSite);
				return 'Answer';
			}
			$user=core::user()->model();
			if(!$user->create($this->id.($db->fetchValue('SELECT MAX(id) FROM user')+1),null,$data['email'],1,$userGroup)) return false;
		}
		core::redirect('',LNGYouLoginAs.' '.$user->login);
	}

}