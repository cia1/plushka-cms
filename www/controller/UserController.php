<?php
namespace plushka\controller;
use plushka;
use plushka\core\Controller;
use plushka\core\Form;
use plushka\model\User as UserModel;

/**
 * Регистарция, авторизация, восстановление пароля, личный кабинет
 * @property-read Form $formPassword Форма смены пароля для действия "index"
 * @property-read bool $notification Признак установлен ли модуль "notification", используется в действии "index"
 * @property-read Form $form Форма авторизации для действия "login"
 * @property-read string $content Сообщение о подтверждении e-mail для действия "confirm"
 * @property-read array[] $messageList Список новых сообщений для действия "message"
 * @property-read int $newMessageCount Кол-во новых сообщений для действия "message"
 */
class UserController extends Controller {

    private const _SALT='resTore';

	public function __construct() {
		parent::__construct();
		if($_GET['corePath'][1]==='restore' && isset($_GET['code'])===true) $this->url[1]='restoreSendPassword';
		plushka::language('user');
	}

    /**
     * Личный кабинет
     * @return string
     */
	public function actionIndex(): string {
		$user=plushka::user();
		if(!$user->id) plushka::redirect('user/login'); //если пользователь не авторизован
		//Форма смены пароля.
		$form=plushka::form();
		$form->label(LNGLogin,$user->login);
		$form->label('E-mail',$user->email);
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

    /**
     * Смена пароля
     * @param array $data
     */
	public function actionIndexSubmit(array $data): void {
		if($data['password1']!==$data['password2']) {
			plushka::error(LNGPasswordsAreNotEqual);
			return;
		}
		//Проверка старого пароля
		$user=plushka::user();
		$userModel=new UserModel();
		if(!$userModel->login($user->login,$data['passwordOld'])) {
			plushka::error(LNGOldPasswordIsWrong);
			return;
		}
		//Сохранить новый пароль в базе данных
		$userModel->password=$data['password1'];
		$userModel->save('id');
		plushka::redirect('user',LNGPasswordChanged);
	}

    /**
     * Форма авторизации
     * @return string
     */
	public function actionLogin(): string {
		$form=plushka::form();
		$form->text('login',LNGLogin);
		$form->password('password',LNGPassword);
		if(isset($_SESSION['wrongPassword'])===true) $form->captcha('captcha',LNGCaptcha);
		$form->submit(LNGEnter);
		$form->html('<a href="'.plushka::link('user/restore').'">'.LNGForgotPassword.'</a>');
		$this->metaTitle=LNGAuthorization;
		$this->pageTitle=LNGEnter;
		$this->form=$form;
		return 'Login';
	}

	public function actionLoginSubmit($data): void {
		if(isset($_SESSION['wrongPassword'])===true && (isset($data['captcha'])===false || !$data['captcha'] || (int)$data['captcha']!==$_SESSION['captcha'])) {
			plushka::error(LNGCaptcha.' '.LNGwroteWrong);
			return;
		}
		if(!plushka::user()->model()->login($data['login'],$data['password'])) {
			if(isset($_SESSION['wrongPassword'])===true) $_SESSION['wrongPassword']++; else $_SESSION['wrongPassword']=1;
			return;
		}
		if(isset($_SESSION['wrongPassword'])===true) unset($_SESSION['wrongPassword']);
		plushka::redirect('user');
	}

    /**
     * Форма регистрации
     * @return Form
     */
	public function actionRegister(): Form {
		if(plushka::userId()) plushka::redirect('/');
		$form=plushka::form();
		$form->text('login',LNGLogin);
		$form->password('password1',LNGPassword);
		$form->password('password2',LNGPasswordAgain);
		$form->text('email','E-mail');
		$form->captcha('captcha',LNGCaptcha);
		$form->submit();
		$this->metaTitle=$this->pageTitle=LNGRegistration;
		return $form;
	}

	public function actionRegisterSubmit(array $data): void {
		if($data['password1']!==$data['password2']) {
			plushka::error(LNGPasswordsAreNotEqual);
			return;
		}
		if(!$data['captcha'] || (int)$data['captcha']!==$_SESSION['captcha']) {
			plushka::error(LNGCaptcha.' '.LNGwroteWrong);
		}
		if(plushka::config('_core','emailRequired')===false) $user=plushka::user()->model();
		else $user=new UserModel();
		if(!$user->create($data['login'],$data['password1'],$data['email'])) return; //регистрация пользователя
		if(!$user->sendMail('activate')) return; //письмо с ссылкой подтверждения адреса электронной почты
		plushka::redirect('user',LNGMessageSentFollowInstructions);
	}

    /**
     * Подтверждение адреса электронной почты
     * @return string
     */
	public function actionConfirm(): string {
		$user=plushka::user()->model();
		plushka::language('user');
		$this->content=sprintf(LNGEmailConfirmedYouLoggedIn,$user->login);
		if(!$user->loginByCode($_GET['code'])) return '_empty'; //поиск пользователя по коду и авторизация, если найден
		//Обновить статус пользователя
		$user->status=UserModel::STATUS_ACTIVE;
		$user->save(false);
		//$user->sendMail('infoAdmin'); //сообщение администрации
		$this->pageTitle=$this->metaTitle=LNGRegistration;
		return '_empty';
	}

    /**
     * Выход
     */
	public function actionLogout(): void {
		plushka::user()->model()->logout();
		plushka::redirect('');
	}

    /**
     * Восстановление пароля по адресу электронной почты
     * @return Form
     */
	public function actionRestore(): Form {
		$form=plushka::form();
		$form->text('email',LNGEmailUsedAtRegistration);
		$form->submit();
		$this->metaTitle=$this->pageTitle=LNGPasswordRestore;
		return $form;
	}

	public function breadcrumbRestore(): array {
		return ['<a href="'.plushka::link('user/login').'">'.LNGLogin.'</a>','{{pageTitle}}'];
	}

	public function actionRestoreSubmit(array $data): void {
		$user=plushka::user()->model();
		if(!$user->loadByEmail($data['email'])) return; //загрузка информации по e-mail
		if($user->status===UserModel::STATUS_BLOCKED) {
			plushka::error(LNGSorryYourAccountBlocked);
			return;
		}
		//Обновление кода подтверждения
		$user->code=md5(time().self::_SALT);
		$user->save(false,'id');
		if($user->sendMail('restoreLink')===false) return; //отправить ссылку для восстановления пароля
		plushka::redirect('user/login',LNGInstructionsSent);
	}

    /**
     * Переход по ссылке восстановления пароля из электронного письма
     * @return string|null
     */
	public function actionRestoreSendPassword(): ?string {
		$user=plushka::user()->model();
		if(!$user->loginByCode($_GET['code'])) return 'Confirm'; //поиск пользователя по коду активации
		//Сохранить обновлённые данные
		$user->password=substr(md5(uniqid(rand(),true)),0,7);
		if($user->status!==UserModel::STATUS_BLOCKED) $user->status=UserModel::STATUS_ACTIVE;
		$user->code=null;
		$user->save(false,'id');
		if(!$user->sendMail('restorePassword')) return 'Confirm'; //отправить новый пароль по почте
		plushka::redirect('user/login',LNGNewPasswordSent);
		return null;
	}

    /**
     * Список личных сообщений
     * @return string
     */
	public function actionMessage(): string {
	    $userModel=plushka::user()->model();
	    $this->messageList=$userModel->messageList();
	    $this->newMessageCount=$userModel->newMessageCount;
	    $userModel->clearNewMessage();
		$this->pageTitle=$this->metaTitle=LNGYourMessages;
		return 'Message';
	}

    /**
     * Отправка нового сообщения по внутренней почте (отправка ответа)
     * @param $data
     */
	public function actionMessageSubmit($data): void {
		//Пользователи могут только отвечать на уже существующие сообщения, но не отправлять новые
        if(plushka::user()->model()->messageReply(nl2br(strip_tags($data['replyTo'])),$data['message'])===false) return;
		plushka::redirect('user/message',LNGMessageSent);
	}

}
