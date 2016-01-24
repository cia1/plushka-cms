<?php class sController extends controller {

	public function actionSetRedirect() {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->redirect=($_GET['redirect']=='true' ? true : false);
		$cfg->save('admin/debug');
	}

}