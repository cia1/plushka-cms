<?php
core::import('core/model');
class article extends model {

	private $_oldAlias;
	protected $fields='*';

	function __construct($db=null) {
		parent::__construct('article');
		$this->_multiLanguage=true;
	}

	public function loadbyAlias($alias) {
		return $this->load('alias='.$this->db->escape($alias));
	}

	//Удаляет из всех мультиязычных таблиц только в том случае, если статья не находится в категории
	public function delete($id=null,$affected=false) {
		$id=(int)$id;
		$data=$this->db->fetchArrayOnce('SELECT categoryId,alias FROM article_'._LANG.' WHERE id='.$id);
		if($data[0]) $this->_multiLanguage=false;
		if(!$this->delete($id)) return false;
		$this->_multiLanguage=true;
		core::hook('pageDelete','article/view/'.$data[1]);
		return true;
	}

	protected function validateRule() {
		return array(
			'id'=>array('primary'),
			'categoryId'=>array('integer','Категория'),
			'alias'=>array('latin','URL (псевдоним)',true),
			'title'=>array('string','Заголовок',true,'max'=>150),
			'text1'=>array('html','Краткий текст (введение)'),
			'text2'=>array('html','Текст статььи'),
			'date'=>array('date','Дата публикации',false),
			'metaTitle'=>array('string','meta Заголовок'),
			'metaKeyword'=>array('string','meta Ключевые слова'),
			'metaDescription'=>array('string','meta Описание')
		);
	}

	protected function beforeInsertUpdate($id,$field=null) {
		//Проверить уникальность псевдонима
		if($this->_data['id']) $this->_oldAlias=$this->db->fetchValue('SELECT alias FROM article_'._LANG.' WHERE id='.$this->_data['id']);
		else $this->_data['oldAlias']=null;
		if($this->_data['alias']!==$this->_oldAlias && $this->db->fetchValue('SELECT 1 FROM article_'._LANG.' WHERE categoryId='.$this->_data['categoryId'].' AND alias='.$this->db->escape($this->_data['alias']).($this->_data['id'] ? ' AND id!='.$this->_data['id'] : ''))) {
			core::error('Статья с таким псевдонимом уже существует. Совпадение псевдонимов допустимо только для статей, находящихся в разных категориях.');
			return false;
		}
		if($this->_data['categoryId']) {
			$this->_multiLanguage=false; //копии записей создавать только если статья не находится в категории
			$this->_table='article_'._LANG;
		}
		return true;
	}

	protected function afterInsert($id=null) {
		$this->_table='article';
		core::hook('modify','article/view/'.$this->alias); //Обновить дату изменения статьи
		return true;
	}

	//Обновляет меню, а также проверять URI главной страницы. Вообще это нужно вынести в отдельный класс.
	protected function afterUpdate($id=null) {
		$this->_table='article';
		if($this->_oldAlias!=$this->_data['alias']) {
			$cfg1=core::config();
			$s='article/view/'.$this->_oldAlias;
			if($cfg1['mainPath']==$s || isset($cfg1['link'][$s])) {
				core::import('admin/core/config');
				$cfg2=new config('_core');
				if($cfg1['mainPath']==$s) $cfg2->mainPath='article/view/'.$this->_data['alias'];
				if(isset($cfg1['link'][$s])) {
					$alias=$cfg1['link'][$s];
					$link=$cfg2->link;
					unset($link[$s]);
					$link['article/view/'.$this->_data['alias']]=$alias;
					$cfg2->link=$link;
				}
				$cfg2->save('_core');
			}
			$this->db->query('UPDATE menuItem SET link='.$this->db->escape('article/view/'.$this->_data['alias']).' WHERE link='.$this->db->escape($s));
		}
		core::hook('modify','article/view/'.$this->alias); //Обновить дату изменения статьи
		return true;
	}


}
