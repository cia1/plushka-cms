<?php class sController extends controller {

	public function actionWidget() {
		$form=core::form();
		$form->submit('Продолжить','submit');
		return $form;
	}

	public function actionWidgetSubmit($data) {
		return '';
	}

}