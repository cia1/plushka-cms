<?php
namespace plushka\admin\core;

class Documentation extends \plushka\admin\core\ModelEx {

	function __construct($db=null) {
		parent::__construct('documentation');
		$this->multiLanguage();
	}

	public function rule() {
		$rule=array(
			'id'=>array('primary'),
			'parentId'=>array('integer'),
			'alias'=>array('latin','псевдоним',true),
			'text2'=>array('html')
		);
		return $this->commonRuleAppend($rule,'title,alias,metaTitle,metaDescription,metaKeyword');
	}

	protected function afterInsert($id=null) {
		$path=$this->db->fetchArrayOnce('SELECT d3.alias,d2.alias,d1.alias FROM documentation d1 LEFT JOIN documentation d2 ON d2.id=d1.parentId LEFT JOIN documentation d3 ON d3.id=d2.parentId WHERE d1.id='.$this->parentId);
		foreach($path as $i=>$item) if($item===null) unset($path[$i]);
		if($path) $path=implode('/',$path).'/'; else $path='';
		$path='documentation/'.$path.$this->alias;
		plushka::hook('modify','documentation/view/'.$this->alias); //Обновить дату изменения статьи
	}
	protected function afterUpdate($id=null) {
		return $this->afterInsert($id);
	}

}
