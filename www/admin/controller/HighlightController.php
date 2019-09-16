<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;

class HighlightController extends Controller {

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