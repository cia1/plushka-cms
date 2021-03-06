<?php
/* Обрабатывает запросы на добавление комментариев. Все визуальные элементы формируются в виджете comment */
class sController extends controller {

	public function __construct() {
		parent::__construct();
		core::language('comment');
	}

	/* Добавляет комментарий в базу данных. Вызывается AJAX-запросом. */
	public function actionIndexSubmit($data) {
		$user=core::user();
		if($user->group) { //Если пользователь авторизован, то в качестве имени использовать его логин
			if($user->group<200) $name=$user->login; else $name=LNGAdministrator;
		} else $name=$data['name'];
		$link=$data['link']; //Олицетворяет страницу, для которой добавляется комментарий
		$db=core::db();
		$groupId=$db->fetchValue('SELECT id FROM commentGroup WHERE link='.$db->escape($data['link']));
		if(!$groupId) {
			$db->insert('commentGroup',array('link'=>$data['link']));
			$groupId=$db->insertId();
		}
		$text=nl2br($data['text']);
		$text=$data['text'];
		if($user->group<200) {
			$name=strip_tags($name);
			if(strpos($name,'www.')!==false || strpos($name,'http://')!==false) die(LNGCommentUserNameIsWrong);
			$text=strip_tags($text);
			if(strpos($text,'www.')!==false || strpos($text,'http://')!==false) die(LNGAnyLinksForbiddenInComments);
		}
		if(!$name) die(LNGUserNameNecessary);
		if(!$text) die(LNGCommentTextCannotBeEmpty);
		if(!core::userId()) { //Каптча только для неавторизованных пользователей
			if((int)$data['captcha']!==$_SESSION['captcha']) die(LNGCaptchaIsWrong);
		}
		$cfg=core::config('comment');
		$db->insert('comment',array(
			'groupId'=>$groupId,
			'userId'=>$user->id,
			'date'=>time(),
			'name'=>$name,
			'text'=>$text,
			'status'=>$cfg['status'],
			'ip'=>$this->_ip()
		));
		if($cfg['status']>0) core::hook('commentPost',$data['link'],$groupId);
		echo 'OK';
		exit;
	}

	/* Выводит список комментариев (HTML). Вызывается AJAX-запросом. */
	public function actionList() {
		core::import('model/comment');
		mComment::renderList($_GET['link']);
		exit;
	}

	/* Возвращает IP-адрес посетителя */
	private static function _ip() {
 		if(!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
 		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}

}