<?php
namespace plushka\admin\controller;

class sController extends controller {

	public function actionWidget() {
		$form=plushka::form();
		$form->submit('Продолжить','submit');
		return $form;
	}

	public function actionWidgetSubmit($data) {
		return '';
	}

}