<?php
namespace plushka\admin\model;
use plushka\admin\core\plushka;
use plushka\model\User as UserModel;

plushka::language('user');

/**
 * AR-модель "пользователь"
 */
class User extends UserModel {

	protected function rule(): array {
		$data=parent::rule();
		unset($data['code']);
		if(!$this->_data['password']) unset($data['password']);
		$data['status']=['boolean'];
		return $data;
	}

	/** @inheritDoc */
	public function save($validate=true,string $primaryAttribute=null): bool {
		if(!$this->_data['password']) unset($this->_data['password']);
		return parent::save($validate,$primaryAttribute);
	}

	/**
	 * Меняет статус на противоположенный (предполагается, что пользователь уже подтвердил e-mail)
	 * @param int $id     ID пользователя
	 * @param int $status Статус
	 */
	public function status(int $id=null,int $status=null): void {
		if($id!==null) $id=$this->id;
		if($status===null) {
			$status=(int)$this->db->fetchValue('SELECT status FROM user WHERE id='.$id);
			if($status===1) $status=2; else $status=1;
		}
		$this->db->query('UPDATE user SET status='.$status.' WHERE id='.$id);
	}

	/* Удаляет пользователя */
	public function delete($id=null,bool $validateAffected=false):bool {
		$db=plushka::db();
		$db->query('DELETE FROM user_message WHERE user1Id='.$id.' OR user2Id='.$id);
		$db->query('DELETE FROM user WHERE id='.$id);
		return true;
	}

}
