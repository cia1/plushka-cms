<?php
namespace plushka\admin\core;

trait ModelRuleTrait {

	/**
	 * Добавляет популярные правила валидации
	 * @param array[]           $rule      Массив всех правил валидации
	 * @param array|string|null $attribute Список параметров
	 * @return array[] Полученный массив правил валидации, к которым добавлены новые
	 */
	protected function commonRuleAppend(array $rule,$attribute=null): array {
		if($attribute===null) $attribute=['metaTitle','metaDescription','metaKeyword'];
		elseif(is_string($attribute)===true) $attribute=explode(',',$attribute);
		$ruleTemplate=[
			'title'=>['string','заголовок',true],
			'alias'=>['latin','псевдоним (url)',true],
			'metaTitle'=>['string'],
			'metaDescription'=>['string'],
			'metaKeyword'=>['string']
		];
		foreach($attribute as $item) {
			if(isset($ruleTemplate[$item])===false) continue;
			$rule[$item]=$ruleTemplate[$item];
		}
		return $rule;
	}

}