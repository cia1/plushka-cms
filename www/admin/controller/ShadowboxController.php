<?php
namespace plushka\admin\controller;

/* Для виджета Shadowbox */
class ShadowboxController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'widgetShadowbox'=>'*'
		);
	}

	public function actionWidgetShadowbox() {
		$f=plushka::form();
		$f->submit('Продолжить','submit');
		$this->cite='Позволяет отображать всплывающие изображения. В любом месте страницы можно использовать следующую конструкцию:<br />&lt;a href="big_img.jpg" rel="shadowbox[gallery1]"&gt;&lt;img src="small_img.jpg"&gt;&lt;/a&gt;<br />При переходе по этой ссылке откроется всплывающее окно с изображением big_img.jpg. <b>Gallery1</b> (не обязательно) - имя группы (галлереи) изображений.';
		return $f;
	}

	public function actionWidgetShadowboxSubmit($data) {
		return '';
	}

}