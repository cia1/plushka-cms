<?php
namespace plushka\admin\model;

/* Объект "группа пользователей */
class UserGroup extends \plushka\core\Model {

	public function __construct() {
		parent::__construct('user_group');
	}

	//Возвращает правила валидации
	protected function rule() {
		$exists=$this->db->fetchValue('SELECT 1 FROM user_group WHERE id='.(int)$this->id); //если уже есть такая группа, то выполнить UPDATE вместо INSERT
		return array(
			'id'=>array(($exists ? 'primary' : 'integer'),'Группа',true,'min'=>1,'max'=>255),
			'name'=>array('string','Описание',true)
		);
	}

	public function afterInsert($id=null) { return $this->afterUpdate($id); }

	public function afterUpdate($id=null) {
		//Если пользователь является администратором, то обновить его права (они находятся в _POST)
		if($this->id<200 || $this->id==255) return true;
		$right=$_POST['user']['right'];
		$db=$this->db;
		$items=$this->db->fetchArray('SELECT module,groupId FROM user_right');
		foreach($items as $item) {
			if($item[1]) $group=explode(',',$item[1]); else $group=array();
			if(isset($right[$item[0]])) {
				if(in_array($this->id,$group)) continue;
				$group[]=$this->id;
			} else {
				if(!in_array($this->id,$group)) continue;
				foreach($group as $key=>$value) {
					if($value==$this->id) {
						unset($group[$key]);
						break;
					}
				}
			}
			$this->db->query('UPDATE user_right SET groupId='.$this->db->escape(implode(',',$group)).' WHERE module='.$this->db->escape($item[0]));
		}
		return true;
	}

	/* Удаляет группу пользователей */
	public function delete($id=null,$affected=false) {
		$id=(int)$id;
		if(!$id) plushka::redirect('user/group');
		$items=$this->db->fetchArray('SELECT module,groupId FROM user_right');
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
		$this->db->query('DELETE FROM user_group WHERE id='.$id);
		return true;
	}
}