<?php
// Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
namespace plushka\admin\core;
use plushka\core\Model;

/**
 * Расширенная модель.
 * Упрощает валидацию часто используемых полей (title, alias, metaTitle, metaDescription, metaKeyword)
 */
class ModelEx extends Model {

	/**
	 * Добавляет популярные правила валидации
	 * @param array[] $rule Массив всех правил валидации
	 * @param array|string|null $attribute Список параметров
	 * @return array[] Полученный массив правил валидации, к которым добавлены новые
	 */
	protected function commonRuleAppend($rule,$attribute=null): array {
		if($attribute===null) $attribute=['metaTitle','metaDescription','metaKeyword'];
		elseif(is_string($attribute)===true) $attribute=explode(',',$attribute);
		$ruleTemplate=array(
			'title'=>array('string','заголовок',true),
			'alias'=>array('latin','псевдоним (url)',true),
			'metaTitle'=>array('string'),
			'metaDescription'=>array('string'),
			'metaKeyword'=>array('string')
		);
		foreach($attribute as $item) {
			if(isset($ruleTemplate[$item])===false) continue;
			$rule[$item]=$ruleTemplate[$item];
		}
		return $rule;
	}

}
