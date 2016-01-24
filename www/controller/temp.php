<?php class sController extends controller {

	public function actionIndex() {
		$f=core::form();
		$f->editor('asdf','asdf');
		$f->submit();
		return $f;
	}

	public function actionIndexSubmit($data) {
var_dump($data['asdf']);
	}
}