<?php
core::import('core/model');
class modelEx extends model {

	protected function commonRuleAppend($rule,$attribute=null) {
		if($attribute===null) $attribute=array('metaTitle','metaDescription','metaKeyword');
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