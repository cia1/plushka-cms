<?php
core::import('core/form');
class formEx extends form {

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