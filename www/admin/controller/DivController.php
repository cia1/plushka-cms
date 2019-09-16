<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;

class DivController extends Controller {

	public function actionWidget() {
		$form=plushka::form();
		$form->submit('Продолжить','submit');
		return $form;
	}

	public function actionWidgetSubmit($data) {
		return '';
	}

}