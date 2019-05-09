<?php
namespace plushka\admin\controller;

class DivController extends \plushka\admin\core\Controller {

	public function actionWidget() {
		$form=plushka::form();
		$form->submit('Продолжить','submit');
		return $form;
	}

	public function actionWidgetSubmit($data) {
		return '';
	}

}