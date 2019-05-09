<?php
namespace plushka\admin\core;

class FormEx extends \plushka\core\Form {

	public function __construct($namespace=null) {
		if($namespace) $this->_namespace=$namespace; else $this->_namespace=$_GET['controller'];
	}

	//Добавляет к форме часто используемые поля (title, alias, metaTitle, metaDescription, metaKeyword)
	public function commonAppend($data,$attribute=null) {
		if($attribute===null) $attribute=array('metaTitle','metaDescription','metaKeyword');
		elseif(is_string($attribute)===true) $attribute=explode(',',str_replace(' ','',$attribute));
		$title=array(
			'title'=>'Заголовок',
			'alias'=>'Псевдоним (URL)',
			'metaTitle'=>'meta Заголовок',
			'metaDescription'=>'meta Описание',
			'metaKeyword'=>'meta Ключевые слова'
		);
		foreach($attribute as $item) {
			$this->text(
				$item,
				(isset($title[$item]) ? $title[$item] : $item),
				(is_array($data)===true ? (isset($data[$item]) ? $data[$item] : null) : $data->$item)
			);
		}
	}

}