<?php
/* Результаты поиска по сайту и форма поиска
	Для вывода контента генерируется событие с именем "search"
 */
class sController extends controller {

	public function actionIndex() {
		if(isset($_GET['keyword'])) $this->keyword=$_GET['keyword'];
		else $this->keyword=null;
		$this->form=core::form();
		$this->form->method='get';
		$this->form->text('keyword','Поиск:',$this->keyword);
		$this->form->submit('Найти');

		$this->metaTitle=$this->pageTitle='Поиск по сайту';
		return 'Index';
	}

	public function actionIndexSubmit($data) {}

}
?>