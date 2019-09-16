<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;
use plushka\core\Form;

/**
 * Расширенная форма.
 * Упрощает добавление часто используемых полей (title, alias, metaTitle, metaDescription, metaKeyword)
 */
class FormEx extends Form {

    /**
     * @param string|null $namespace Пространство имён полей формы (имя контроллера)
     */
	public function __construct(string $namespace=null) {
	    parent::__construct($namespace);
		if($namespace===null) $this->_namespace=$_GET['controller'];
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
