<?php
namespace plushka\admin\controller;

class sController extends controller {

	public function actionWidget() {
		$form=plushka::form();
		$form->submit('Продолжить','submit');
		$this->cite='Ваш код должен быть заключён в теги <b>&lt;pre&gt;&lt;code&gt;...&lt;/code&gt;&lt;/pre&gt;</b>.';
		return $form;
	}

	public function actionWidgetSubmit($data) {
		return null;
	}

} ?>