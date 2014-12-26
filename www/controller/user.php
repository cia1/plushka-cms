<?php
/* Регистрация, авторизация, восстановление пароля, личный кабинет */
class sController extends controller {

	/* Личный кабинет */
	public function actionIndex() {
		$u=core::user();
		if(!$u->id) core::redirect('user/login'); //если пользователь не авторизован
		//Форма смены пароля.
		$f=core::form();
		$f->label('Логин:',$u->login);
		$f->label('E-mail:',$u->email);
		$f->html('<h3>Смена пароля</h3>');
		$f->password('password1','Старый пароль');
		$f->password('password2','Новый пароль');
		$f->password('password3','Новый пароль (ещё раз)');
		$f->submit('Продолжить');

		$this->pageTitle=$this->metaTitle='Личный кабинет';
		return $f;
	}

	public function actionIndexSubmit($data) {
		if($data['password2']!=$data['password3']) {
			controller::$error='Введённые пароли не совпадают.';
			return false;
		}
		$db=core::db();
		$u=core::user();
		if(!$db->fetchValue('SELECT 1 FROM user WHERE id='.$u->id.' AND password='.$db->escape($data['password1']))) {
			controller::$error='Старый пароль введён неверно.';
			return false;
		}
		$db->query('UPDATE user SET password='.$db->escape($data['password2']).' WHERE id='.$u->id);
		core::redirect('user','Пароль изменён.');
	}

	/* Выводит форму авторизации */
	public function actionLogin() {
		$f=core::form();
		$f->text('login','Логин');
		$f->password('password','Пароль');
		$f->submit('Войти');
		$f->html('<a href="'.core::link('user/restore1').'">Забыли пароль?</a>');

		$this->metaTitle='Авторизация';
		$this->pageTitle='Войти';
		$this->form=$f;
		return 'Login';
	}

	public function actionLoginSubmit($data) {
		$u=core::user();
		if(!$u->login($data['login'],$data['password'])) return;
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
		$model=core::model('user');
		if($data['password1']!=$data['password2']) {
			controller::$error='Введённые пароли не совпадают';
			return false;
		}
		$data['code']=md5(mktime().'regIster');
		$data['password']=$data['password1'];
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'login'=>array('callback','логин',true,array($this,'validateLogin')),
			'password'=>array('string','пароль',true,'min'=>3,'max'=>32,'trim'=>false),
			'email'=>array('callback','e-mail',true,array($this,'validateEmail')),
			'code'=>array('string')
		))) return false;
		//Отправить пользователю письмо с кодом подтверждения
		core::import('core/email');
		$e=new email();
		$cfg=core::config();
		$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
		$e->subject('Регистрация на сайте '.$_SERVER['HTTP_HOST']);
		$e->messageTemplate('register',array( //шаблон в /data/email/register.html
			'login'=>$data['login'],
			'confirmLink'=>'http://'.$_SERVER['HTTP_HOST'].core::link('user/confirm').'?code='.$data['code'],
			'password'=>$data['password1']
		));
		$e->send($data['email']);
		core::redirect('user/login','На указанный при регистрации адрес электронной почты отправлено письмо. Следуйте указанным в письме инструкциям.');
	}

	/* Подтверждение адреса электронной почты */
	public function actionConfirm() {
		$code=$_GET['code'];
		$db=core::db();
		$user=$db->fetchArrayOnce('SELECT id,login,email,password FROM user WHERE status=0 AND code='.$db->escape($code));
		if($user) {
			$db->query('UPDATE user SET status=1 WHERE id='.$user[0]);
			$cfg=core::config();
			core::import('core/email');
			//Отправить письмо администрации с информацией о новом зарегистрированном пользователе
			$e=new email();
			$e->from($user[2],$_SERVER['HTTP_HOST']);
			$e->subject('Регистрация нового пользователя');
			$e->replyTo($user[2],$user[1]);
			$e->messageTemplate('registerAdmin',array( //шаблон в /data/email/registerAdmin.html
				'login'=>$user[1],
				'password'=>$user[3],
				'email'=>$user[2]
			));
			$e->send($cfg['adminEmailEmail']);
			core::hook('userCreate',$user[0],$user[1],$user[2]);
		} else controller::$error='Данная ссылка является не действительной.';
		$this->pageTitle=$this->metaTitle='Регистрация';
		return 'Confirm';
	}

	/* "Выход" */
	public function actionLogout() {
		$u=core::user();
		$u->logout();
		core::redirect('');
	}

	/* Восстановление пароля по адресу электронной почты */
	public function actionRestore1() {
		$f=core::form();
		$f->text('email','E-mail, указанный при регистрации');
		$f->submit('Продолжить');
		$this->metaTitle=$this->pageTitle='Восстановление пароля';
		return $f;
	}

	public function breadcrumbRestore1() {
		return array('<a href="'.core::link('user/login').'">Войти</a>');
	}

	public function actionRestore1Submit($data) {
		$db=core::db();
		$user=$db->fetchArrayOnceAssoc('SELECT id,login,password,status,code FROM user WHERE email='.$db->escape($data['email']));
		if(!$user) {
			controller::$error='Пользователь с таким адресом электронной почты не зарегистрирован';
			return false;
		}
		//Отправить письмо пользователю
		core::import('core/email');
		$cfg=core::config();
		$email=new email();
		$email->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
		$email->replyTo($cfg['adminEmailEmail'],$cfg['adminEmailName']);
		$email->subject('Восстановление пароля');
		$data=array(
			'email'=>$data['email'],
			'login'=>$user['login'],
			'password'=>$user['password']
		);
		if($user['status']=='0') {
			$data['status']='<p><u>Внимание!</u> Адрес электронной почты не подтверждён. Чтобы завершить процедуру регистрации перейдите по ссылке: <a href="http://'.$_SERVER['HTTP_HOST'].core::link('user/confirm?code='.$user['code']).'">http://'.$_SERVER['HTTP_HOST'].core::link('user/confirm?code='.$user['code']).'</a></p>';
		} elseif($user['status']=='2') {
			$data['status']='<p><u>Внимание!</u> Ваш аккаунт заблокирован администратором. Вы можете обратиться к администрации для получения дополнительной информации.</p>';
		} else $data['status']='';
		$email->messageTemplate('restorePassword',$data); //шаблон в файле /data/email/restorePassword.html
		$email->send($data['email']);
		core::redirect('user/login','Пароль был выслан на указанный e-mail. Пожалуйста, проверьте вашу почту.');
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
			'id'=>$item[0],'date'=>$item[1],'direct'=>($item[2]==$uid ? 2 : 1),'subject'=>($item[2]==$uid ? 'Вы пишете <b>'.$item[4].'</b>' : 'вам пишиет <b>'.$item[3].'</b>'),'message'=>$item[5],'isNew'=>$item[6]);
			if($item[6]) $this->newCount++;
		}
		if($this->newCount) $db->query('UPDATE userMessage SET isNew=0 WHERE user2Id='.$uid);

		$this->pageTitle=$this->metaTitle='Личные сообщения';
		return 'Message';
	}

	/* Отправка нового сообщения по внутренней почте */
	public function actionMessageSubmit($data) {
		$db=core::db();
		$data2=$db->fetchArrayOnceAssoc('SELECT user1Id,user1Login FROM userMessage WHERE id='.(int)$data['replyTo']);
		if(!$data2) core::error404();
		core::import('model/user');
		if(!modelUser::message($data2['user1Id'],$data2['user1Login'],nl2br($data['message']),true)) return false;
		core::redirect('user/message','Сообщение отправлено');
	}



/* --- PRIVATE --------------------------------------------------------------------------------- */
	/* Проверяет уникальность логина */
	public function validateLogin($field,$value) {
		$db=core::db();
		$value=$db->getEscape($value);
		if(strlen($value)<3) {
			controller::$error='Логин не может состоять менее чем из 3х символов';
			return false;
		}
		if(strlen($value)>20) {
			controller::$error='Логин не может быть длинее 20 символов';
			return false;
		}
		if($db->fetchValue("SELECT 1 FROM user WHERE login='".$value."'")) {
			controller::$error='Пользователь с таким логином уже зарегистрирован';
			return false;
		}
		return $value;
	}

	/* Проверяет уникальность адреса электронной почты */
	public function validateEmail($field,$value) {
		$i=preg_match('/^[-a-z0-9!#$%&\'*+\/=?^_`{|}~]+(?:\.[-a-z0-9!#$%&\'*+\/=?^_`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/',$value);
		if(!$i) {
			controller::$error='E-mail указан неверно';
			return;
		}
		$db=core::db();
		if($db->fetchValue("SELECT 1 FROM user WHERE email='".$value."'")) {
			controller::$error='Пользователь с таким адресом электронной почты уже зарегистрирован';
			return false;
		}
		return $value;
	}

}
?>