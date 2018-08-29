<?php
/* Регистрация, авторизация, восстановление пароля, личный кабинет */
class sController extends controller {

	public function __construct($action) {
		parent::__construct($action);
		if($action=='restore' && isset($_GET['code'])) {
			$this->url[1]='restoreSendPassword';
		}
		core::language('user');
	}

	/* Личный кабинет */
	public function actionIndex() {
		$u=core::user();
		if(!$u->id) core::redirect('user/login'); //если пользователь не авторизован
		//Форма смены пароля.
		$form=core::form();
		$form->label(LNGLogin,$u->login);
		$form->label('E-mail',$u->email);
		$form->html('<h3>'.LNGPasswordChanging.'</h3>');
		$form->password('passwordOld',LNGOldPassword);
		$form->password('password1',LNGNewPassword);
		$form->password('password2',LNGNewPasswordAgain);
		$form->submit();
		$this->formPassword=$form;
		$this->notification=core::moduleExists('notification');
		$this->pageTitle=$this->metaTitle=LNGYourProfile;
		return 'Index';
	}

	public function actionIndexSubmit($data) {
		if($data['password1']!=$data['password2']) {
			core::error(LNGPasswordsAreNotEqual);
			return false;
		}
		//Проверка старого пароля
		$user=core::user();
		core::import('model/user');
		$userModel=new modelUser();
		if(!$userModel->login($user->login,$data['passwordOld'])) {
			core::error(LNGOldPasswordIsWrong);
			return false;
		}
		//Сохранить новый пароль в базе данных
		$userModel->password=$data['password1'];
		$userModel->save('id,password');
		core::redirect('user',LNGPasswordChanged);
	}

	/* Выводит форму авторизации */
	public function actionLogin() {
		$f=core::form();
		$f->text('login',LNGLogin);
		$f->password('password',LNGPassword);
		if(isset($_SESSION['wrongPassword'])) $f->captcha('captcha',LNGCaptcha);
		$f->submit(LNGEnter);
		$f->html('<a href="'.core::link('user/restore').'">'.LNGForgotPassword.'</a>');
		$this->metaTitle=LNGAuthorization;
		$this->pageTitle=LNGEnter;
		$this->form=$f;
		return 'Login';
	}

	public function actionLoginSubmit($data) {
		if(isset($_SESSION['wrongPassword']) && (!isset($data['captcha']) || !$data['captcha'] || (int)$data['captcha']!==$_SESSION['captcha'])) {
			core::error(LNGCaptcha.' '.LNGwroteWrong);
			return;
		}
		if(!core::user()->model()->login($data['login'],$data['password'])) {
			if(isset($_SESSION['wrongPassword'])) $_SESSION['wrongPassword']++; else $_SESSION['wrongPassword']=1;
			return;
		}
		if(isset($_SESSION['wrongPassword'])) unset($_SESSION['wrongPassword']);
		core::redirect('user');
	}

	/* Выводит форму регистрации */
	public function actionRegister() {
		if(core::userId()) core::redirect('/');
		$f=core::form();
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
			core::error(LNGPasswordsAreNotEqual);
			return false;
		}
		if(!$data['captcha'] || (int)$data['captcha']!==$_SESSION['captcha']) {
			core::error(LNGCaptcha.' '.LNGwroteWrong);
		}
		core::import('model/user');
		if(core::config('_core','emailRequired')===false) $user=core::user()->model();
		else {
			core::import('model/user');
			$user=new modelUser();
		}
		if(!$user->create($data['login'],$data['password1'],$data['email'])) return false; //регистрация пользователя
		if(!$user->sendMail('activate')) return false; //письмо с ссылкой подтверждения адреса электронной почты
		core::redirect('user',LNGMessageSentFollowInstructions);
	}

	/* Подтверждение адреса электронной почты */
	public function actionConfirm() {
		$user=core::user()->model();
		if(!$user->loginByCode($_GET['code'])) return '_empty'; //поиск пользователя по коду и авторизация, если найден
		//Обновить статус пользователя
		$user->status=1;
		$this->code=null;
		$user->save(false,'status,code');
		$user->sendMail('infoAdmin'); //сообщение администрации
		$this->login=$user->login;
		$this->pageTitle=$this->metaTitle=LNGRegistration;
		return 'Confirm';
	}

	/* "Выход" */
	public function actionLogout() {
		core::user()->model()->logout();
		core::redirect('');
	}

	/* Восстановление пароля по адресу электронной почты */
	public function actionRestore() {
		$f=core::form();
		$f->text('email',LNGEmailUsedAtRegistration);
		$f->submit();
		$this->metaTitle=$this->pageTitle=LNGPasswordRestore;
		return $f;
	}

	public function breadcrumbRestore() {
		return array('<a href="'.core::link('user/login').'">'.LNGLogin.'</a>','{{pageTitle}}');
	}

	public function actionRestoreSubmit($data) {
		$user=core::user()->model();
		if(!$user->loadByEmail($data['email'])) return 'Confirm'; //загрузка информации по e-mail
		if($user->status==2) {
			core::error(LNGSorryYourAccountBlocked);
			return false;
		}
		//Обновление кода подтверждения
		$user->code=md5(time().'resTore');
		$user->save(false,'code');
		if(!$user->sendMail('restoreLink')) return 'Confirm'; //отправить ссылку для восстановления пароля
		core::redirect('user/login',LNGInstructionsSent);
	}

	//Переход по ссылке восстановления пароля (из e-mail)
	public function actionRestoreSendPassword() {
		$user=core::user()->model();
		if(!$user->loginByCode($_GET['code'])) return 'Confirm'; //поиск пользователя по коду активации
		//Сохранить обновлённые данные
		$user->password=substr(md5(uniqid(rand(),true)),0,7);
		$user->status=1;
		$user->code=null;
		$user->save(false,'status,password,code');
		if(!$user->sendMail('restorePassword')) return 'Confirm'; //отправить новый пароль по почте
		core::redirect('user/login',LNGNewPasswordSent);
	}

	/* Список личных сообщений */
	public function actionMessage() {
		$uid=core::userId();
		if(!$uid) core::redirect('user/login');
		$db=core::db();
		$db->query('SELECT id,date,user1Id,user1Login,user2Login,message,isNew FROM userMessage WHERE user1Id='.$uid.' OR user2Id='.$uid.' ORDER BY date DESC LIMIT 0,25');
		$this->items=array();
		$this->newCount=0; //количество новых сообщений
		while($item=$db->fetch()) {
			if($item[2]==$uid) $item[6]=false; elseif($item[6]=='1') $item[6]=true; else $item[6]=false; //новое сообщение или нет
			$this->items[]=array(
			'id'=>$item[0],'date'=>$item[1],'direct'=>($item[2]==$uid ? 2 : 1),'subject'=>($item[2]==$uid ? LNGYouWriteTo.' <b>'.$item[4].'</b>' : LNGWriteToYou.' <b>'.$item[3].'</b>'),'message'=>$item[5],'isNew'=>$item[6]);
			if($item[6]) $this->newCount++;
		}
		if($this->newCount) {
			$db->query('UPDATE userMessage SET isNew=0 WHERE user2Id='.$uid);
			$_SESSION['newMessageCount']=0;
			$_SESSION['newMessageTimeout']=time();
		}

		$this->pageTitle=$this->metaTitle=LNGYourMessages;
		return 'Message';
	}

	/* Отправка нового сообщения по внутренней почте */
	public function actionMessageSubmit($data) {
		//Пользователи могут только отвечать на уже существующие сообщения, но не отправлять новые
		$data2=core::db()->fetchArrayOnceAssoc('SELECT user1Id,user1Login FROM userMessage WHERE id='.(int)$data['replyTo']);
		if(!$data2) core::error404();
		if(!core::userCore()->model()->message($data2['user1Id'],$data2['user1Login'],nl2br($data['message']))) return false;
		core::redirect('user/message',LNGMessageSent);
	}

}