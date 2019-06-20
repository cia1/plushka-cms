<?php
namespace plushka\controller;
use plushka;
use plushka\model\User;

/* Регистрация, авторизация, восстановление пароля, личный кабинет */
class UserController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		if($_GET['corePath'][1]==='restore' && isset($_GET['code'])===true) $this->url[1]='restoreSendPassword';
		plushka::language('user');
	}

	/* Личный кабинет */
	public function actionIndex() {
		$u=plushka::user();
		if(!$u->id) plushka::redirect('user/login'); //если пользователь не авторизован
		//Форма смены пароля.
		$form=plushka::form();
		$form->label(LNGLogin,$u->login);
		$form->label('E-mail',$u->email);
		$form->html('<h3>'.LNGPasswordChanging.'</h3>');
		$form->password('passwordOld',LNGOldPassword);
		$form->password('password1',LNGNewPassword);
		$form->password('password2',LNGNewPasswordAgain);
		$form->submit();
		$this->formPassword=$form;
		$this->notification=plushka::moduleExists('notification');
		$this->pageTitle=$this->metaTitle=LNGYourProfile;
		return 'Index';
	}

	public function actionIndexSubmit($data) {
		if($data['password1']!=$data['password2']) {
			plushka::error(LNGPasswordsAreNotEqual);
			return false;
		}
		//Проверка старого пароля
		$user=plushka::user();
		$userModel=new User();
		if(!$userModel->login($user->login,$data['passwordOld'])) {
			plushka::error(LNGOldPasswordIsWrong);
			return false;
		}
		//Сохранить новый пароль в базе данных
		$userModel->password=$data['password1'];
		$userModel->save('id');
		plushka::redirect('user',LNGPasswordChanged);
	}

	/* Выводит форму авторизации */
	public function actionLogin() {
		$f=plushka::form();
		$f->text('login',LNGLogin);
		$f->password('password',LNGPassword);
		if(isset($_SESSION['wrongPassword'])) $f->captcha('captcha',LNGCaptcha);
		$f->submit(LNGEnter);
		$f->html('<a href="'.plushka::link('user/restore').'">'.LNGForgotPassword.'</a>');
		$this->metaTitle=LNGAuthorization;
		$this->pageTitle=LNGEnter;
		$this->form=$f;
		return 'Login';
	}

	public function actionLoginSubmit($data) {
		if(isset($_SESSION['wrongPassword']) && (!isset($data['captcha']) || !$data['captcha'] || (int)$data['captcha']!==$_SESSION['captcha'])) {
			plushka::error(LNGCaptcha.' '.LNGwroteWrong);
			return;
		}
		if(!plushka::user()->model()->login($data['login'],$data['password'])) {
			if(isset($_SESSION['wrongPassword'])) $_SESSION['wrongPassword']++; else $_SESSION['wrongPassword']=1;
			return;
		}
		if(isset($_SESSION['wrongPassword'])) unset($_SESSION['wrongPassword']);
		plushka::redirect('user');
	}

	/* Выводит форму регистрации */
	public function actionRegister() {
		if(plushka::userId()) plushka::redirect('/');
		$f=plushka::form();
		$f->text('login',LNGLogin);
		$f->password('password1',LNGPassword);
		$f->password('password2',LNGPasswordAgain);
		$f->text('email','E-mail');
		$f->captcha('captcha',LNGCaptcha);
		$f->submit();
		$this->metaTitle=$this->pageTitle=LNGRegistration;
		return $f;
	}

	public function actionRegisterSubmit($data) {
		if($data['password1']!=$data['password2']) {
			plushka::error(LNGPasswordsAreNotEqual);
			return false;
		}
		if(!$data['captcha'] || (int)$data['captcha']!==$_SESSION['captcha']) {
			plushka::error(LNGCaptcha.' '.LNGwroteWrong);
		}
		if(plushka::config('_core','emailRequired')===false) $user=plushka::user()->model();
		else $user=new User();
		if(!$user->create($data['login'],$data['password1'],$data['email'])) return false; //регистрация пользователя
		if(!$user->sendMail('activate')) return false; //письмо с ссылкой подтверждения адреса электронной почты
		plushka::redirect('user',LNGMessageSentFollowInstructions);
	}

	/* Подтверждение адреса электронной почты */
	public function actionConfirm() {
		$user=plushka::user()->model();
		if(!$user->loginByCode($_GET['code'])) return '_empty'; //поиск пользователя по коду и авторизация, если найден
		//Обновить статус пользователя
		$user->status=1;
		$this->code=null;
		$user->save(false,'id');
		//$user->sendMail('infoAdmin'); //сообщение администрации
		$this->login=$user->login;
		$this->pageTitle=$this->metaTitle=LNGRegistration;
		return 'Confirm';
	}

	/* "Выход" */
	public function actionLogout() {
		plushka::user()->model()->logout();
		plushka::redirect('');
	}

	/* Восстановление пароля по адресу электронной почты */
	public function actionRestore() {
		$f=plushka::form();
		$f->text('email',LNGEmailUsedAtRegistration);
		$f->submit();
		$this->metaTitle=$this->pageTitle=LNGPasswordRestore;
		return $f;
	}

	public function breadcrumbRestore() {
		return array('<a href="'.plushka::link('user/login').'">'.LNGLogin.'</a>','{{pageTitle}}');
	}

	public function actionRestoreSubmit($data) {
		$user=plushka::user()->model();
		if(!$user->loadByEmail($data['email'])) return 'Confirm'; //загрузка информации по e-mail
		if($user->status==2) {
			plushka::error(LNGSorryYourAccountBlocked);
			return false;
		}
		//Обновление кода подтверждения
		$user->code=md5(time().'resTore');
		$user->save(false,'id');
		if(!$user->sendMail('restoreLink')) return 'Confirm'; //отправить ссылку для восстановления пароля
		plushka::redirect('user/login',LNGInstructionsSent);
	}

	//Переход по ссылке восстановления пароля (из e-mail)
	public function actionRestoreSendPassword() {
		$user=plushka::user()->model();
		if(!$user->loginByCode($_GET['code'])) return 'Confirm'; //поиск пользователя по коду активации
		//Сохранить обновлённые данные
		$user->password=substr(md5(uniqid(rand(),true)),0,7);
		$user->status=1;
		$user->code=null;
		$user->save(false,'id');
		if(!$user->sendMail('restorePassword')) return 'Confirm'; //отправить новый пароль по почте
		plushka::redirect('user/login',LNGNewPasswordSent);
	}

	/* Список личных сообщений */
	public function actionMessage() {
		$uid=plushka::userId();
		if(!$uid) plushka::redirect('user/login');
		$db=plushka::db();
		$db->query('SELECT id,date,user1Id,user1Login,user2Login,message,isNew FROM user_message WHERE user1Id='.$uid.' OR user2Id='.$uid.' ORDER BY date DESC LIMIT 0,25');
		$this->items=array();
		$this->newCount=0; //количество новых сообщений
		while($item=$db->fetch()) {
			if($item[2]==$uid) $item[6]=false; elseif($item[6]=='1') $item[6]=true; else $item[6]=false; //новое сообщение или нет
			$this->items[]=array(
			'id'=>$item[0],'date'=>$item[1],'direct'=>($item[2]==$uid ? 2 : 1),'subject'=>($item[2]==$uid ? LNGYouWriteTo.' <b>'.$item[4].'</b>' : LNGWriteToYou.' <b>'.$item[3].'</b>'),'message'=>$item[5],'isNew'=>$item[6]);
			if($item[6]) $this->newCount++;
		}
		if($this->newCount) {
			$db->query('UPDATE user_message SET isNew=0 WHERE user2Id='.$uid);
			$_SESSION['newMessageCount']=0;
			$_SESSION['newMessageTimeout']=time();
		}

		$this->pageTitle=$this->metaTitle=LNGYourMessages;
		return 'Message';
	}

	/* Отправка нового сообщения по внутренней почте */
	public function actionMessageSubmit($data) {
		//Пользователи могут только отвечать на уже существующие сообщения, но не отправлять новые
		$data2=plushka::db()->fetchArrayOnceAssoc('SELECT user1Id,user1Login FROM user_message WHERE id='.(int)$data['replyTo']);
		if(!$data2) plushka::error404();
		if(plushka::userReal()->model()->message(nl2br($data['message']),$data2['user1Id'],$data2['user1Login'])===false) return false;
		plushka::redirect('user/message',LNGMessageSent);
	}

}
