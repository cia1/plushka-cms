<?php
namespace plushka\admin\core;

plushka::import('admin/core/modelEx');
class articleCategory extends modelEx {

	//Возвращает список ещё не опубликованных статей в категории $categoryId
	public static function featureList($categoryId) {
		$categoryId=(int)$categoryId;
		$db=plushka::db();
		$db->query('SELECT id,date,title FROM article_'._LANG.' WHERE categoryId='.$categoryId.' AND date>'.time());
		$data=array();
		while($item=$db->fetchAssoc()) {
			$item['date']=date('d.m.Y',$item['date']);
			$data[]=$item;
		}
		return $data;
	}

	public function __construct() {
		parent::__construct('article_category');
		$this->multiLanguage();
	}

	public function init() {
		$this->_data=array(
			'onPage'=>20
		);
	}

	public function loadbyAlias($alias) {
		return $this->load('alias='.$this->db->escape($alias));
	}

	protected function rule() {
		return $this->commonRuleAppend(
			array(
				'id'=>array('primary'),
				'parentId'=>array('integer','родительская категория'),
				'onPage'=>array('integer','Количество статей в списке',true,'min'=>1,'max'=>255),
				'text1'=>array('html','Краткий текст (введение)'),
				'text2'=>array('html','Текст статььи'),
			),
			'title,alias,metaTitle,metaDescription,metaKeyword'
		);
	}

	protected function beforeInsertUpdate($id,$field=null) {
		//Проверить уникальность псевдонима
		$sql='SELECT 1 FROM article_category_'._LANG.' WHERE alias='.$this->db->escape($data['alias']);
		if($this->id) $sql.=' AND id<>'.$this->id;
		if($this->db->fetchValue($sql)) {
			plushka::error('Такой псевдоним уже используется для другой категории');
			return false;
		}
		return true;
	}

	protected function afterInsert($id=null) {
		plushka::hook('modify','article/blog/'.$this->alias);
		plushka::hook('modify','article/list/'.$this->alias);
		return true;
	}

	protected function afterUpdate($id=null) {
		return $this->afterInsert($id);
	}

	public function delete($id=null,$affected=false) {
		if($id!==null) $this->load((int)$id,'id,alias');
		if(!parent::delete($id)) return false;
		plushka::hook('pageDelete','article/blog/'.$this->alias);
		plushka::hook('pageDelete','article/list/'.$this->alias);
		return true;
	}

}