<?php class sController extends controller {

	public function actionIndex() {
		$form=core::form();
		$form->checkbox('success','Success',true);
		$form->checkbox('error','Error',true);
		$form->checkbox('redirect','Redirect',true);
		$form->submit();
		return $form;
	}

	public function actionIndexSubmit($data) {
		if(isset($data['success'])) {
			core::success('This is the success message.');
		}
		if(isset($data['error'])) {
			core::error('This is the error message.');
		}
		if(isset($data['error'])) {
			core::redirect('test');
		}
	}
}