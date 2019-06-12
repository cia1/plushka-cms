<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;

/**
 * Расширенная форма. Упрощает добавление часто используемыч полей (title, alias, metaTitle, metaDescription, metaKeyword)
 */
class FormEx extends \plushka\core\Form {

	public function __construct($namespace=null) {
		if($namespace) $this->_namespace=$namespace; else $this->_namespace=$_GET['controller'];
	}

	/**
	 * Добавляет к форме часто используемые поля
	 * @param array|object $data массив или объект, содержащий данные для формы
	 * @param array|string|null $attribute Список атрибутов
	 */
	public function commonAppend($data,array $attribute=null): void {
		if($attribute===null) $attribute=['metaTitle','metaDescription','metaKeyword'];
		elseif(is_string($attribute)===true) $attribute=explode(',',str_replace(' ','',$attribute));
		$title=[
			'title'=>'Заголовок',
			'alias'=>'Псевдоним (URL)',
			'metaTitle'=>'meta Заголовок',
			'metaDescription'=>'meta Описание',
			'metaKeyword'=>'meta Ключевые слова'
		];
		foreach($attribute as $item) {
			$this->text(
				$item,
				$title[$item] ?? $item,
				(is_array($data)===true ? ($data[$item] ?? null) : $data->$item)
			);
		}
	}

}