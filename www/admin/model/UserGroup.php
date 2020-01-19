<?php
namespace plushka\admin\model;
use plushka\admin\core\plushka;
use plushka\core\Model;

/**
 * AR-модель "группа пользователей"
 */
class UserGroup extends Model {

	public function __construct() {
		parent::__construct('user_group');
	}

	protected function rule(): array {
		$exists=$this->db->fetchValue('SELECT 1 FROM user_group WHERE id='.(int)$this->id); //если уже есть такая группа, то выполнить UPDATE вместо INSERT
		return [
			'id'=>[($exists ? 'primary' : 'integer'),'Группа',true,'min'=>1,'max'=>255],
			'name'=>['string','Описание',true]
		];
	}

	/** @inheritDoc */
	public function afterInsert($id=null): void { $this->afterUpdate($id); }

	/** @inheritDoc */
	public function afterUpdate($id=null): void {
		//Если пользователь является администратором, то обновить его права (они находятся в _POST)
		if($this->id<200 || $this->id==255) return;
		$right=$_POST['user']['right'];
		$items=$this->db->fetchArray('SELECT module,groupId FROM user_right');
		foreach($items as $item) {
			if($item[1]) $group=explode(',',$item[1]); else $group=[];
			if(isset($right[$item[0]])===true) {
				if(in_array($this->id,$group)===true) continue;
				$group[]=$this->id;
			} else {
				if(in_array($this->id,$group)===false) continue;
				foreach($group as $key=>$value) {
					if($value==$this->id) {
						unset($group[$key]);
						break;
					}
				}
			}
			$this->db->query('UPDATE user_right SET groupId='.$this->db->escape(implode(',',$group)).' WHERE module='.$this->db->escape($item[0]));
		}
	}

	public function delete($id=null,bool $validateAffected=false): bool {
		if($id===null) return false;
		$db=plushka::db();
		$items=$this->db->fetchArray('SELECT module,groupId FROM user_right');
		$db->transaction();
		foreach($items as $item) {
			$group=explode(',',$item[1]);
			foreach($group as $index=>$value) {
				if($value==$id) {
					unset($group[$index]);
					$this->db->query('UPDATE user_right SET groupId='.$this->db->escape(implode(',',$group)).' WHERE module='.$this->db->escape($item[0]));
					break;
				}
			}
		}
		if(parent::delete($id,$validateAffected)===false) {
			$db->rollback();
			return false;
		}
		$db->commit();
		return true;
	}

}