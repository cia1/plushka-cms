<?php
/* Регистрация, авторизация, восстановление пароля, личный кабинет */
class sController extends controller {

	public function __construct($action) {
		parent::__construct($action);
		if($action=='restore' && isset($_GET['code'])) {
			$this->url[1]='RestoreSendPassword';
		}
	}

	/* Личный кабинет */
	public function actionIndex() {
		$u=core::user();
		if(!$u->id) core::redirect('user/login'); //если пользователь не авторизован
		//Форма смены пароля.
		$f=core::form();
		$f->label('Логин:',$u->login);
		$f->label('E-mail:',$u->email);
		$f->html('<h3>Смена пароля</h3>');
		$f->password('passwordOld','Старый пароль');
		$f->password('password1','Новый пароль');
		$f->password('password2','Новый пароль (ещё раз)');
		$f->submit('Продолжить');

		$this->pageTitle=$this->metaTitle='Личный кабинет';
		return $f;
	}

	public function actionIndexSubmit($data) {
		if($data['password1']!=$data['password2']) {
			controller::$error='Введённые пароли не совпадают.';
			return false;
		}
		//Проверка старого пароля
		$user=core::user();
		core::import('model/user');
		$userModel=new modelUser();
		if(!$userModel->login($user->login,$data['passwordOld'])) {
			controller::$error='Старый пароль введён неверно.';
			return false;
		}
		//Сохранить новый пароль в базе данных
		$userModel->password=$data['password1'];
		$userModel->save('id,password');
		core::redirect('user','Пароль изменён.');
	}

	/* Выводит форму авторизации */
	public function actionLogin() {
		$f=core::form();
		$f->text('login','Логин');
		$f->password('password','Пароль');
		$f->submit('Войти');
		$f->html('<a href="'.core::link('user/restore').'">Забыли пароль?</a>');
		$this->metaTitle='Авторизация';
		$this->pageTitle='Войти';
		$this->form=$f;
		return 'Login';
	}

	public function actionLoginSubmit($data) {
		if(!core::user()->model()->login($data['login'],$data['password'])) return;
		core::redirect('');
	}

	/* Выводит форму регистрации */
	public function actionRegister() {
		if(core::userId()) core::redirect('/');
		$f=core::form();
		$f->text('login','Логин');
		$f->password('password1','Пароль');
		$f->password('password2','Пароль (ещё раз)');
		$f->text('email','E-mail');
		$f->submit('Продолжить');
		$this->metaTitle=$this->pageTitle='Регистрация';
		return $f;
	}

	public function actionRegisterSubmit($data) {
		if($data['password1']!=$data['password2']) {
			controller::$error='Введённые пароли не совпадают';
			return false;
		}
		core::import('model/user');
		$user=new modelUser();
		if(!$user->create($data['login'],$data['password1'],$data['email'])) return false; //регистрация пользователя
		if(!$user->sendMail('activate')) return false; //письмо с ссылкой подтверждения адреса электронной почты
		core::redirect('user/login','На указанный при регистрации адрес электронной почты отправлено письмо. Следуйте указанным в письме инструкциям.');
	}

	/* Подтверждение адреса электронной почты */
	public function actionConfirm() {
		$user=core::user()->model();
		if(!$user->loginByCode($_GET['code'])) return 'Confirm'; //поиск пользователя по коду и авторизация, если найден
		//Обновить статус пользователя
		$user->status=1;
		$this->code=null;
		$user->save(false,'status,code');
		$user->sendMail('userInfoAdmin'); //сообщение администрации
		$this->login=$user->login;
		$this->pageTitle=$this->metaTitle='Регистрация';
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
		$f->text('email','E-mail, указанный при регистрации');
		$f->submit('Продолжить');
		$this->metaTitle=$this->pageTitle='Восстановление пароля';
		return $f;
	}

	public function breadcrumbRestore() {
		return array('<a href="'.core::link('user/login').'">Войти</a>');
	}

	public function actionRestoreSubmit($data) {
		$user=core::user()->model();
		if(!$user->loadByEmail($data['email'])) return 'Confirm'; //загрузка информации по e-mail
		if($user->status==2) {
			controller::$error='Извините, но этот аккаунт заблокирован администрацией';
			return false;
		}
		//Обновление кода подтверждения
		$user->code=md5(time().'resTore');
		$user->save(false,'code');
		if(!$user->sendMail('restoreLink')) return 'Confirm'; //отправить ссылку для восстановления пароля
		core::redirect('user/login','Инструкции по восттановлению пароля высланы на указанный адрес электронной почты');
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
		core::redirect('user/login','Новый пароль был выслан на указанный адрес электронной почты. Вы можете его изменить в личном кабинете.');
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
			'id'=>$item[0],'date'=>$item[1],'direct'=>($item[2]==$uid ? 2 : 1),'subject'=>($item[2]==$uid ? 'Вы пишете <b>'.$item[4].'</b>' : 'Вам пишиет <b>'.$item[3].'</b>'),'message'=>$item[5],'isNew'=>$item[6]);
			if($item[6]) $this->newCount++;
		}
		if($this->newCount) $db->query('UPDATE userMessage SET isNew=0 WHERE user2Id='.$uid);

		$this->pageTitle=$this->metaTitle='Личные сообщения';
		return 'Message';
	}

	/* Отправка нового сообщения по внутренней почте */
	public function actionMessageSubmit($data) {
		//Пользователи могут только отвечать на уже существующие сообщения, но не отправлять новые
		$db=core::db();
		$data2=$db->fetchArrayOnceAssoc('SELECT user1Id,user1Login FROM userMessage WHERE id='.(int)$data['replyTo']);
		if(!$data2) core::error404();
		if(!core::userCore()->model()->message($data2['user1Id'],$data2['user1Login'],nl2br($data['message']),true)) return false;
		core::redirect('user/message','Сообщение отправлено');
	}

}
?>