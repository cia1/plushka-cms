<?php
namespace plushka\model;
use plushka\core\plushka;
use plushka\core\Email;
use plushka\core\Model;
use plushka\core\User as UserCore;

plushka::language('user');

/**
 * Библиотека функций личного кабинета пользователей
 * @property int|null $id Идентификатор, первичный ключ
 * @property int $groupId Группа пользователя
 * @property string|null $login Логин
 * @property string|null $email Адрес электронной почты
 * @property string $code Код авторизации
 * @property int $status Статус
 * @property string $password Пароль или хеш пароля
 *
 * @property-read int|null $newMessageCount Количество новых сообщений, достпно после вызова self::messageList()
 */
class User extends Model {

    public const STATUS_NOT_CONFIRMED=0; //Новый пользователь, e-mail не подтверждён
    public const STATUS_ACTIVE=1; //E-mail подтверждён
    public const STATUS_BLOCKED=2; //Заблокирован

    public const EMAIL_ACTIVATE='activate'; //подтверждение электронной почты
    public const EMAIL_INFO_ADMIN='infoAdmin'; //сообщение администрации о регистрации пользователя
    public const EMAIL_RESTORE_LINK='restoreLink'; //ссылка на страницу восстановления пароля
    public const EMAIL_RESTORE_PASSWORD='restorePassword'; //ответное письмо (восстановление пароля)
    public const EMAIL_INFO='info'; //(шаблон в /admin/data) - регистрационная информация пользователя
    public const MESSAGE_DIRECTION_FROM=false; //Сообщение отправлено мной
    public const MESSAGE_DIRECTION_TO=true; //Сообщение отправлено мне

    private const _SALT='2f48uj0'; //Соль для шифрования пароля (одна на всех)
	private $_self; //указатель на класс, находящийся в сессии (содержит информацию о пользователе)
	private $_attribute; //контейнет для массива, содержащего дополнительные данные пользователя

    /**
     * Если задан $id или $user, то модель будет инициирована соответствующими данными
     * @param int|null $id ID пользователя
     * @param UserCore|null $user
     */
	public function __construct(int $id=null,UserCore $user=null) {
		parent::__construct('user');
		$this->_self=$user;
		if($id!==null) {
			if($this->loginById($id)===false) plushka::error(LNGUserNotExists);
		} elseif($user!==null) foreach($this->_self as $key=>$value) $this->_data[$key]=$value;
	}

	/**
     * Загружает данные пользователя по адресу электронной почты (без авторизации)
     * @param string $email
     * @return bool Успешно ли загружены данные
     */
	public function loadByEmail(string $email): bool {
		if(!$this->load('email='.$this->db->escape($email),'id,groupId,status,login,email')) {
			plushka::error(LNGUserWithEmailNotFound);
			return false;
		}
		return true;
	}

	/**
     * Загружает данные, а также авторизует пользователя по указанному идентификатору
     * @param int $id Идентификатор пользователя
     * @return bool Найден ли пользователь
     */
	public function loginById(int $id): bool {
		if(!$this->loadById($id)) {
			plushka::error(LNGUserNotExists);
			return false;
		}
		$this->groupId=(int)$this->groupId;
		if($this->_self) { //Если класс создан через plushka:user() или plushka::userReal()
			$this->_self->id=$this->id;
			$this->_self->group=$this->groupId;
			$this->_self->login=$this->login;
			$this->_self->email=$this->email;
			$this->_userRight();
		}
		return true;
	}

    /**
     * Загружает данные пользователя по SQL-запросу (WHERE)
     * @param string $where WHERE-часть SQL-запроса
     * @param array|string|null $fieldList Список полей, которые нужно загрузить
     * @return bool
     */
	public function load(string $where,$fieldList=null): bool {
		$this->_attribute=null;
		return parent::load($where,$fieldList);
	}

	/**
     * Сохраняет и/или возвращает значение дополнительного атрибута, ассоциированного с пользователем
     * @param string $attribute Имя атрибута
     * @param mixed $value Значение атрибута, если задано, то будет сохранено в БД
     * @return null|mixed
     */
	public function attribute(string $attribute,$value=null) {
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

    /**
     * Загрузка данных по коду авторизации
     * @param string $code Код авторизации
     * @return bool Найден ли пользователь
     */
    public function loginByCode(string $code): bool {
        if(!$this->load('status!=2 AND code='.$this->db->escape($code))) {
            plushka::error(LNGActivationCodeIsWrong);
            return false;
        }
        $this->groupId=(int)$this->groupId;
        if($this->_self) { //Если класс создан через plushka:user() или plushka::userReal()
            $this->_self->id=$this->id;
            $this->_self->group=$this->groupId;
            $this->_self->login=$this->login;
            $this->_self->email=$this->email;
            $this->_userRight();
        }
        return true;
    }

    /**
     * Авторизация по логину и паролю
     * @param string $login Логин
     * @param string $password Пароль
     * @return bool Найден ли пользователь
     */
    public function login(string $login,string $password): bool {
        if(!$this->load('login='.$this->db->escape($login).' AND password='.$this->db->escape(self::_hash($password)).' AND status!=2')) {
            plushka::error(LNGLoginOrPasswordIsWrong);
            return false;
        }
        $this->groupId=(int)$this->groupId;
        if($this->_self) { //Если класс создан не напрямую, а через plushka::user()->model(), то передать в класс user данные пользователя
            $this->_self->id=$this->id;
            $this->_self->group=$this->groupId;
            $this->_self->login=$this->login;
            $this->_self->email=$this->email;
            $this->_userRight();
        }
        return true;
    }

    /**
     * Возвращает список последних сообщений пользователя
     * Количество новых сообщений будет доступно в $this->newMessageCount
     * @param int $limit
     * @return array
     */
    public function messageList(int $limit=25) {
        $userId=$this->id;
        $data=[];
        $this->db->query('SELECT id,date,user1Id,user1Login,user2Login,message,isNew FROM user_message WHERE user1Id='.$userId.' OR user2Id='.$userId.' ORDER BY date DESC LIMIT '.$limit);
        $newCount=0;
        while($item=$this->db->fetch()) {
            $isNew=($item[2]!=$userId && $item[6] ? true : false);
            $data[]=[
                'id'=>(int)$item[0],
                'date'=>$item[1],
                'direction'=>($item[2]==$userId ? self::MESSAGE_DIRECTION_FROM : self::MESSAGE_DIRECTION_TO),
                'message'=>$item[5],
                'isNew'=>$isNew,
                'login'=>($item[2]==$userId ? $item[4] : $item[2])
            ];
            if($isNew) $newCount++;
        }
        $this->newMessageCount=$newCount;
        return $data;
    }

    /**
     * Сбрасывает счётчик новых сообщений, помечая все сообщения прочитанными
     */
    public function clearNewMessage(): void {
        $this->db->query('UPDATE user_message SET isNew=0 WHERE user2Id='.$this->id);
        $_SESSION['newMessageCount']=0;
        $_SESSION['newMessageTimeout']=time();
    }

    /**
     * Отправляет ответ на личное сообщение
     * @param int $messageId Идентификатор сообщения
     * @param string $text Текст сообщения
     * @return bool
     */
    public function messageReply(int $messageId,string $text): bool {
        $data=$this->db->fetchArrayOnceAssoc('SELECT user1Id,user1Login FROM user_message WHERE id='.$messageId.' AND user2Id='.$this->id);
        if($data===null) return false;
        return $this->message($text,$data['user1Id'],$data['user1Login']);
    }

    /**
     * Отправляет личное сообщение пользователю
     * Допустимо указывать один из параметров: либо $user2Id либо $user2Login.
     * @param string $message Текст сообщения
     * @param int|null $user2Id ID пользователя-получателя
     * @param string|null $user2Login Логин пользователя получателя
     * @return bool Удалось ли отправить сообщение (могут быть запрещены пользователем)
     */
    public function message(string $message,int $user2Id=null,string $user2Login=null): bool {
        $message=trim($message);
        if(!$message) {
            plushka::error(LNGNothingToSend);
            return false;
        }
        $db=plushka::db();
        //Даже если были бы заданы и ИД и логин, то всёравно нужно удостовериться что такой пользователь существует
        if($user2Id) $user2=$db->fetchArrayOnceAssoc('SELECT id,login FROM user WHERE id='.$user2Id);
        elseif($user2Login) $user2=$db->fetchArrayOnceAssoc('SELECT id,login FROM user WHERE login='.$db->escape($user2Login));
        else $user2=null;
        if(!$user2) {
            plushka::error(LNGIncorrectRecepientData);
            return false;
        }
        if(!$this->_self->id) plushka::redirect('user/login');
        $db->insert('user_message',array(
            'user1Id'=>$this->_self->id,
            'user1Login'=>$this->_self->login,
            'user2Id'=>$user2['id'],
            'user2Login'=>$user2['login'],
            'message'=>$message,
            'date'=>time()
        ));
        //Уведомления
        if(plushka::moduleExists('notification')) {
            Notification::sendIfCan($user2['id'],'privateMessage','<p>'.sprintf(LNGYouGotNewMessageOnSite,$_SERVER['HTTP_HOST'].plushka::url()).'</p><hr />'.$message);
        }
        return true;
    }

    /**
     * "Выход" пользователя
     */
    public function logout(): void {
        $this->id=null;
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
    }

    /**
     * Созадёт пользователя
     * @param string $login Логин
     * @param string $password Пароль
     * @param string $email Адрес электронной почты
     * @param int $status Статус
     * @param int $groupId Группа пользователя
     * @return bool Удалось ли создать пользователя
     */
    public function create(string $login,string $password,string $email,int $status=0,int $groupId=1): bool {
        $this->id=null; //чтобы гарантированно был выполнен запрос INSERT, а не UPDATE
        $this->groupId=$groupId;
        $this->status=$status;
        $this->code=md5(time().'regIster'); //код подтверждения e-mail
        $this->login=$login;
        $this->password=$password;
        $this->email=$email;
        if (!$this->save()) return false;
        if($this->_self) { //Если этот класс создан через plushka::user()->model()
            $this->_self->id=$this->id;
            $this->_self->group=$this->groupId;
            $this->_self->login=$this->login;
            $this->_self->email=$this->email;
        }
        return true;
    }

    /**
     * Отправляет пользователю письмо на электронную почту
     * @param string $type Тип письма
     * @return bool Удалось ли отправить сообщение
    */
    public function sendMail(string $type): bool {
        $email=new Email();
        $cfg=plushka::config();
        $email->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
        $email->replyTo($cfg['adminEmailEmail'],$cfg['adminEmailName']);
        $template=array('login'=>$this->login);
        //Разные параметры письма в зависимости от типа сообщения
        $email->subject(sprintf(LNGRegistrationOnSite,$_SERVER['HTTP_HOST']));
        $recipient=$this->email;
        $templateName='user'.ucfirst($type);
        switch($type) {
            case 'activate': //ссылка подтверждения e-mail
                $template['confirmLink']='http://'.$_SERVER['HTTP_HOST'].plushka::link('user/confirm').'?code='.$this->code;
                break;
            case 'infoAdmin': //письмо администратору
                $email->replyTo($this->_data['email'],$this->_data['login']);
                $template['email']=$this->_data['email'];
                $recipient=$cfg['adminEmailEmail'];
                $templateName='admin/'.$templateName;
                break;
            case 'restoreLink': //ссылка на восстановление пароля
                $email->subject(sprintf(LNGPasswordRestoreOnSite,$_SERVER['HTTP_HOST']));
                $template['confirmLink']='http://'.$_SERVER['HTTP_HOST'].plushka::link('user/restore').'?code='.$this->code;
                break;
            case 'restorePassword': //содержит новый пароль
                $email->subject(sprintf(LNGPasswordRestoreOnSite,$_SERVER['HTTP_HOST']));
                $template['password']=$this->_data['password'];
                break;
            case 'info': //информация пользователю
                $email->subject(sprintf(LNGYouAreRegisteredOnSite,$_SERVER['HTTP_HOST']));
                if($this->_data['password']) $template['password']=$this->_data['password']; else $template['password']='('.LNGknownOnlyYou.')';
                $template['status']=($this->_data['status'] ? LNGaccountActive : LNGaccountBlocked);
                $template['email']=$this->_data['email'];
                break;
        }
        $email->messageTemplate($templateName,$template);
        return $email->send($recipient);
    }

    /**
     * @inheritDoc
     */
    public function save($validate=null,string $primaryAttribute=null): bool {
        if($this->_data['id']) $isNew=false; else $isNew=true;
        //Сохранить в БД зашифрованный пароль, однако, в классе хранить НЕ зашифрованный
        if(!isset($this->_data['password'])) return parent::save($validate,$primaryAttribute);

        if(isset($this->_data['password'])===true) {
            $password=$this->_data['password'];
            $this->_data['password']=self::_hash($password);
        }
        $result=parent::save($validate,$primaryAttribute);
        if(isset($this->_data['password'])===true) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->_data['password']=$password;
        }
        //Обработать событие изменения или создания пользователя
        if($result) {
            if($isNew) plushka::hook('userCreate',$this->_data['id'],$this->_data['login'],$this->_data['email']);
            else plushka::hook('userModify',$this->_data['id'],$this->_data['login'],$this->_data['email']);
        }
        return $result;
    }

    /* Проверяет уникальность логина */
    public function validateLogin(string $value, /** @noinspection PhpUnusedParameterInspection */string $attribute) {
        $value=trim(str_replace(array("'",'"','/','\\'),'',strip_tags($value)));
        if(strlen($value)<3) {
            plushka::error(LNGLoginCannotBeShorter3Symbols);
            return false;
        }
        if(mb_strlen($value)>35) {
            plushka::error(LNGLoginCannotBeLonger35Symbols);
            return false;
        }
        $q='SELECT 1 FROM user WHERE login='.$this->db->escape($value);
        if($this->_data['id']) $q.=' AND id!='.(int)$this->_data['id'];
        if($this->db->fetchValue($q)) {
            plushka::error(LNGThisLoginAlreadyUses);
            return false;
        }
        return $value;
    }

    /* Проверяет уникальность адреса электронной почты */
    public function validateEmailAddress(string $value, /** @noinspection PhpUnusedParameterInspection */string $field) {
        if(!filter_var($value,FILTER_VALIDATE_EMAIL)) {
            plushka::error(LNGEMailIsWrong);
            return false;
        }
        $q='SELECT 1 FROM user WHERE email='.$this->db->escape($value);
        if($this->_data['id']) $q.=' AND id!='.$this->_data['id'];
        if($this->db->fetchValue($q)) {
            plushka::error(LNGThisEmailAlreadyUses);
            return false;
        }
        return $value;
    }

    // Проверяет и шифрует пароль перед сохранением
    public function validatePassword(string $value, /** @noinspection PhpUnusedParameterInspection */string $field) {
        $l=strlen($value);
        if($l<3) {
            plushka::error(LNGPasswordTooShort);
            return false;
        }
        return $value;
    }

    /**
     * Удаляет пользователя с указанным идентификатором
     * @inheritDoc
     */
    public function delete(int $id=null,bool $affected=false): bool {
        $result=parent::delete($id,$affected);
        if($result) plushka::hook('userDelete',$id);
        return $result;
    }

    protected function fieldListLoad(): string {
      return 'id,groupId,login,email';
    }

    protected function fieldListSave(): string {
	    //Если пароль строго false, то не требовать ввода пароля (регистрация oauth). Не достаточно очевидный вариант реализации, но это работает.
        if($this->password===false) return 'id,groupId,login,status,email,code';
        return '*';
	}




	protected function rule(): array {
		$data=array(
			'id'=>array('primary'),
			'login'=>array('callback',LNGlogin,true,array($this,'validateLogin')),
			'password'=>array('callback',LNGpassword,true,array($this,'validatePassword')),
			'email'=>array('callback','e-mail',plushka::config('_core','emailRequired'),array($this,'validateEmailAddress')),
			'code'=>array('string')
		);
		if(isset($this->_data['status'])) $data['status']=array('boolean');
		if(isset($this->_data['groupId'])) $data['groupId']=array('integer');
		return $data;
	}




	//Возвращает зашифрованный пароль
	private static function _hash(string $password): string {
		return crypt($password,self::_SALT);
	}

	//Загружает набор прав доступа для текущего пользователя в $this->right
	private function _userRight(): void {
		if($this->groupId<200) return;
		$right=[];
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
