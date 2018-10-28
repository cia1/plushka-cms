<?php
/* Библиотека функций личного кабинета пользователей */
define('_SALT','2f48uj0'); //Соль для шифрования пароля (одна на всех)
core::import('core/model');
core::language('user');
class modelUser extends model {

	private $_self; //указатель на класс, находящийся в сессии (содержит информацию о пользователе)
	private $_attribute; //контейнет для массива, содержащего дополнительные данные пользователя

	//Если задан $id, то загружается информация из базы данных. $user - ссылка на класс, находящийся в сессии
	public function __construct($id=null,&$user=null) {
		parent::__construct('user');
		$this->_self=$user;
		if($id) {
			if(!$this->loginById($id)) core::error(LNGUserNotExists);
		} elseif($user!==null) foreach($this->_self as $key=>$value) $this->_data[$key]=$value;
	}

	//Загружает данные пользователя по адресу электронной почты (без авторизации)
	public function loadByEmail($email) {
		if(!$this->load('email='.$this->db->escape($email),'id,groupId,status,login,email')) {
			core::error(LNGUserWithEmailNotFound);
			return false;
		}
		return true;
	}

	//Загружает данные, а также авторизует пользователя по указанному идентификатору
	public function loginById($id) {
		if(!$this->loadById($id)) {
			core::error(LNGUserNotExists);
			return false;
		}
		$this->groupId=(int)$this->groupId;
		if($this->_self) { //Если класс создан через core:user() или core::userCore()
			$this->_self->id=$this->id;
			$this->_self->group=$this->groupId;
			$this->_self->login=$this->login;
			$this->_self->email=$this->email;
			$this->_userRight();
		}
		return true;
	}

	public function load($where,$fieldList=null) {
		$this->_attribute=null;
		return parent::load($where,$fieldList);
	}

	//Сохраняет (если задан $value) и/или возвращает дополнительный атрибут с именем $attribute
	public function attribute($attribute,$value=null) {
		if(!$this->id) return null;
		$id=(int)$this->_data['id'];
		if($this->_attribute===null) {
			$this->_attribute=$this->db->fetchValue('SELECT data FROM user WHERE id='.$id);
			if($this->_attribute) $this->_attribute=json_decode($this->_attribute,true); else $this->_attribute=array();
		}
		if($value!==null) {
			$this->_attribute[$attribute]=$value;
			$this->db->query('UPDATE user SET data='.$this->db->escape(json_encode($this->_attribute)).' WHERE id='.$id);
		}
		if(isset($this->_attribute[$attribute])) return $this->_attribute[$attribute];
		return null;
	}

	protected function fieldList($isSave) {
		if($isSave===true) {
			//Если пароль строго false, то не требовать ввода пароля (регистрация oauth). Не достаточно очевидный вариант реализации, но это работает.
			if($this->password===false) return 'id,groupId,login,status,email,code';
			return '*';
		}
		return 'id,groupId,login,email';
	}

	//Возвращает массив правил валидиции
	protected function rule() {
		$data=array(
			'id'=>array('primary'),
			'login'=>array('callback',LNGlogin,true,array($this,'validateLogin')),
			'password'=>array('callback',LNGpassword,true,array($this,'validatePassword')),
			'email'=>array('callback','e-mail',core::config('_core','emailRequired'),array($this,'validateEmail')),
			'code'=>array('string')
		);
		if(isset($this->_data['status'])) $data['status']=array('boolean');
		if(isset($this->_data['groupId'])) $data['groupId']=array('integer');
		return $data;
	}

	//Загружает данные, а также авторизует пользователя по коду активации
	public function loginByCode($code) {
		if(!$this->load('status!=2 AND code='.$this->db->escape($code))) {
			core::error(LNGActivationCodeIsWrong);
			return false;
		}
		$this->groupId=(int)$this->groupId;
		if($this->_self) { //Если класс создан через core:user() или core::userCore()
			$this->_self->id=$this->id;
			$this->_self->group=$this->groupId;
			$this->_self->login=$this->login;
			$this->_self->email=$this->email;
			$this->_userRight();
		}
		return true;
	}

	/* Авторизация по логину и паролю */
	public function login($login,$password) {
		//TODO: Когда PHP 5.6 станет использоваться повсеместно, нужно переписать на hash_equals
		if(!$this->load('login='.$this->db->escape($login).' AND password='.$this->db->escape(self::_hash($password)).' AND status!=2')) {
			core::error(LNGLoginOrPasswordIsWrong);
			return false;
		}
		$this->groupId=(int)$this->groupId;
		if($this->_self) { //Если класс создан не напрямую, а через core::user()->model(), то передать в класс user данные пользователя
			$this->_self->id=$this->id;
			$this->_self->group=$this->groupId;
			$this->_self->login=$this->login;
			$this->_self->email=$this->email;
			$this->_userRight();
		}
		return true;
	}

	//Отправляет личное сообщение пользователю
	//int $user2Id и string $user2Login - ИД и логин получателя; string $message - текст сообщения
	public function message($user2Id=null,$user2Login=null,$message) {
		$message=trim($message);
		if(!$message) {
			core::error(LNGNothingToSend);
			return false;
		}
		$db=core::db();
		//Даже если были бы заданы и ИД и логин, то всёравно нужно удостовериться что такой пользователь существует
		if($user2Id) $user2=$db->fetchArrayOnceAssoc('SELECT id,login FROM user WHERE id='.$user2Id);
		elseif($user2Login) $user2=$db->fetchArrayOnceAssoc('SELECT id,login FROM user WHERE login='.$db->escape($user2Login));
		else $user2=null;
		if(!$user2) {
			core::error(LNGIncorrectRecepientData);
			return false;
		}
		if(!$this->_self->id) core::redirect('user/login');
		$db->insert('user_message',array(
			'user1Id'=>$this->_self->id,
			'user1Login'=>$this->_self->login,
			'user2Id'=>$user2['id'],
			'user2Login'=>$user2['login'],
			'message'=>$message,
			'date'=>time()
		));
		//Уведомления
		if(core::moduleExists('notification')) {
			core::import('model/notification');
			notification::sendIfCan($user2['id'],'privateMessage','<p>'.sprintf(LNGYouGotNewMessageOnSite,$_SERVER['HTTP_HOST'].core::url()).'</p><hr />'.$message);
		}
		return true;
	}

	/* Возвращает массив, содержащий все права пользователя (только для администраторов) */
	public function rightData($module) {
		$db=core::db();
		return $db->fetchArrayOnceAssoc('SELECT description,picture FROM user_right WHERE module='.$db->escape($module));
	}

	//"Выход" пользователя
	public function logout() {
		$this->_id=null;
		$this->groupId=0;
		$this->login=null;
		$this->email=null;
		if($this->_self) {
			$this->_self->id=null;
			$this->_self->group=0;
			$this->_self->login=null;
			$this->_self->email=null;
		}
		unset($_SESSION['newMessageCount']);
		unset($_SESSION['newMessageTimeout']);
		unset($_SESSION['ckUploadTo']);
		return true;
	}

	//Создаёт пользователя
	public function create($login,$password,$email,$status=0,$groupId=1) {
		$this->id=null; //чтобы гарантированно был выполнен запрос INSERT, а не UPDATE
		$this->groupId=$groupId;
		$this->status=$status;
		$this->code=md5(time().'regIster'); //код подтверждения e-mail
		$this->login=$login;
		$this->password=$password;
		$this->email=$email;
		if (!$this->save()) return false;
		if($this->_self) { //Если этот класс создан через core::user()->model()
			$this->_self->id=$this->id;
			$this->_self->group=$this->groupId;
			$this->_self->login=$this->login;
			$this->_self->email=$this->email;
		}
		return true;
	}

	/* Отправляет пользователю письмо.
	$type: "activate" - активация аккаунта, "infoAdmin" - сообщение администрации о регистрации пользователя,
	"restoreLink" - ссылка на страницу восстановления пароля, "restorePassword" - ответное письмо (восстановление пароля),
	"info" (шаблон в /admin/data - регистрационная информация пользователя */
	public function sendMail($type) {
		core::import('core/email');
		$e=new email();
		$cfg=core::config();
		$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
		$e->replyTo($cfg['adminEmailEmail'],$cfg['adminEmailName']);
		$template=array('login'=>$this->login);
		//Разные параметры письма в зависимости от типа сообщения
		$e->subject(sprintf(LNGRegistrationOnSite,$_SERVER['HTTP_HOST']));
		$email=$this->email;
		$templateName='user'.ucfirst($type);
		switch($type) {
		case 'activate': //ссылка подтверждения e-mail
			$template['confirmLink']='http://'.$_SERVER['HTTP_HOST'].core::link('user/confirm').'?code='.$this->code;
			break;
		case 'infoAdmin': //письмо администратору
			$e->replyTo($this->_data['email'],$this->_data['login']);
			$template['email']=$this->_data['email'];
			$email=$cfg['adminEmailEmail'];
			$templateName='admin/'.$templateName;
			break;
		case 'restoreLink': //ссылка на восстановление пароля
			$e->subject(sprintf(LNGPasswordRestoreOnSite,$_SERVER['HTTP_HOST']));
			$template['confirmLink']='http://'.$_SERVER['HTTP_HOST'].core::link('user/restore').'?code='.$this->code;
			break;
		case 'restorePassword': //содержит новый пароль
			$e->subject(sprintf(LNGPasswordRestoreOnSite,$_SERVER['HTTP_HOST']));
			$template['password']=$this->_data['password'];
			break;
		case 'info': //информация пользователю
			core::import('language/user.'._LANG);
			$e->subject(sprintf(LNGYouAreRegisteredOnSite,$_SERVER['HTTP_HOST']));
			if($this->_data['password']) $template['password']=$this->_data['password']; else $template['password']='('.LNGknownOnlyYou.')';
			$template['status']=($this->_data['status'] ? LNGaccountActive : LNGaccountBlocked);
			$template['email']=$this->_data['email'];
			break;
		}
		$e->messageTemplate($templateName,$template);
		if(!$e->send($email)) return false;
		return true;
	}

	//Выполняет сохранение информации в базе данных
	public function save($validate=true,$id=null) {
		if($id || $this->_data['id']) $isNew=false; else $isNew=true;
		//Сохранить в БД зашифрованный пароль, однако, в классе хранить НЕ зашифрованный
		if(!isset($this->_data['password'])) return parent::save($validate,$id);
		$password=$this->_data['password'];
		$this->_data['password']=self::_hash($password);
		$result=parent::save($validate,$id);
		$this->_data['password']=$password;
		//Обработать событие изменения или создания пользователя
		if($result) {
			if($isNew) core::hook('userCreate',$this->_data['id'],$this->_data['login'],$this->_data['email']);
			else core::hook('userModify',$this->_data['id'],$this->_data['login'],$this->_data['email']);
		}
		return $result;
	}

	/* --- PRIVATE --------------------------------------------------------------------------------- */
	/* Проверяет уникальность логина */
	public function validateLogin($value,$field) {
		$value=trim(str_replace(array("'",'"','/','\\'),'',strip_tags($value)));
		if(strlen($value)<3) {
			core::error(LNGLoginCannotBeShorter3Symbols);
			return false;
		}
		if(mb_strlen($value)>35) {
			core::error(LNGLoginCannotBeLonger35Symbols);
			return false;
		}
		$q='SELECT 1 FROM user WHERE login='.$this->db->escape($value);
		if($this->_data['id']) $q.=' AND id!='.(int)$this->_data['id'];
		if($this->db->fetchValue($q)) {
			core::error(LNGThisLoginAlreadyUses);
			return false;
		}
		return $value;
	}

	/* Проверяет уникальность адреса электронной почты */
	public function validateEmail($value,$field) {
		if(!filter_var($value,FILTER_VALIDATE_EMAIL)) {
			core::error(LNGEMailIsWrong);
			return false;
		}
		$q='SELECT 1 FROM user WHERE email='.$this->db->escape($value);
		if($this->_data['id']) $q.=' AND id!='.$this->data['id'];
		if($this->db->fetchValue($q)) {
			core::error(LNGThisEmailAlreadyUses);
			return false;
		}
		return $value;
	}

	// Проверяет и шифрует пароль перед сохранением
	public function validatePassword($value,$field) {
		$l=strlen($value);
		if($l<3) {
			core::error(LNGPasswordTooShort);
			return false;
		}
		return $value;
	}

	// Удаляет пользователя с указанным идентификатором
	public function delete($id=null,$affected=false) {
		if(!parent::delete($id,$affected)) return false;
		core::hook('userDelete',$id);
		return true;
	}

	/* --- PRIVATE --------------------------------------------------------------------- */
	//Возвращает зашифрованный пароль
	private static function _hash($password) {
		return crypt($password,_SALT);
	}

	//Загружает набор прав доступа для текущего пользователя в $this->right
	private function _userRight() {
		if($this->groupId<200) return;
		$right=array();
		$this->db->query('SELECT module,groupId,picture FROM user_right');
		while($item=$this->db->fetch()) {
			$group=explode(',',$item[1]);
			if(in_array($this->groupId, $group) || $this->groupId==255) {
				if($item[2]) $right[$item[0]]=true; else $right[$item[0]]=false;
			}
		}
		$this->_self->right=$right;
	}

}