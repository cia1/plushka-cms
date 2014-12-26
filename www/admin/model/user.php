<?php
/* Объект "пользователь". */
core::import('core/model');
class modelUser extends model {
	protected $validate=array(
		'id'=>array('primary'),
		'login'=>array('latin','Логин',true),
		'groupId'=>array('integer','Группа',true,'min'=>1,'max'=>255),
		'status'=>array('boolean','Статус'),
		'email'=>array('email','E-mail',true),
	);
	protected $fields='*';

	
	public function __construct() {
		parent::__construct('user');
	}

	/* Добавить валидацию пароля */
	public function set($data) {
		parent::set($data);
		if($this->password) {
			$this->validate['password']=array('string','Пароль',true);
		}
	}

	public function validate($fields=null) {
		if($this->password) {
			if($this->_data['password']!=$this->_data['password2']) {
				controller::$error='Введённые пароли не совпадают';
				return false;
			}
		}
		if(!parent::validate($fields)) return false;
		if(!$this->id) {
			if($this->db->fetchValue('SELECT 1 FROM user WHERE login='.$this->db->escape($this->login))) {
				controller::$error='Пользователь с таким логином уже зарегистрирован';
				return false;
			}
			if($this->db->fetchValue('SELECT 1 FROM user WHERE email='.$this->db->escape($this->email))) {
				controller::$error='Пользователь с таким адресом электронной почты уже зарегистрирован';
				return false;
			}
		}
		return true;
	}

	/* Возвращает пароль пользователя (не вижу смысла его прятать в md5 */
	public function passwordByLogin($login=null) {
		if(!$login) $login=$this->login;
		return $this->db->fetchValue('SELECT password FROM user WHERE login='.$this->db->escape($login));
	}

	/* Меняет статус на противоположенный (предполагается, что пользователь уже подтвердил e-mail) */
	public function status($id=null) {
		if($id) $id=(int)$id; else $id=$this->id;
		$status=(int)$this->db->fetchValue('SELECT status FROM user WHERE id='.$id);
		if($status=='1') $status='2'; else $status='1';
		$this->db->query('UPDATE user SET status='.$status.' WHERE id='.$id);
		return true;
	}

	/* Удаляет пользователя */
	public function delete($id=null) {
		$db=core::db();
		$db->query('DELETE FROM userMessage WHERE user1Id='.$id.' OR user2Id='.$id);
		$db->query('DELETE FROM user WHERE id='.$id);
		return true;
	}

}
?>