<?php
/* Объект "пользователь". */
core::import('model/user');
core::import('language/user.'._LANG);

class userAdmin extends modelUser {

	protected function fieldList($action) {
		return '*';
	}

	//Возвращает массив с правилами валидации
	protected function rule() {
		$data=parent::rule();
		unset($data['code']);
		if(!$this->_data['password']) unset($data['password']);
		$data['status']=array('boolean');
		return $data;
	}

	public function save($validate=true,$fields=null,$id=null) {
		if(!$this->_data['password']) unset($this->_data['password']);
		return parent::save($validate,$fields,$id);
	}

	/* Меняет статус на противоположенный (предполагается, что пользователь уже подтвердил e-mail) */
	public function status($id=null,$status=null) {
		if($id) $id=(int)$id; else $id=$this->id;
		if($status===null) {
			$status=(int)$this->db->fetchValue('SELECT status FROM user WHERE id='.$id);
			if($status=='1') $status='2'; else $status='1';
		}
		$this->db->query('UPDATE user SET status='.$status.' WHERE id='.$id);
		return true;
	}

	/* Удаляет пользователя */
	public function delete($id=null,$affected=false) {
		$db=core::db();
		$db->query('DELETE FROM userMessage WHERE user1Id='.$id.' OR user2Id='.$id);
		$db->query('DELETE FROM user WHERE id='.$id);
		return true;
	}

}