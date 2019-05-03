<?php
namespace plushka\controller;
use plushka;
use plushka\model\Form;

/* Универсальная контактная форма
	ЧПУ: /form/ИД (actionIndex) - вывод формы; /form/ИД/success (actionSuccess) - сообщение после отправки формы
*/
class FormController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		$this->id=(int)$this->url[1]; //идентификатор формы
		if(!$this->id) plushka::error404();
		if(isset($this->url[2])) $this->url[1]=$this->url[2]; else $this->url[1]='index';
	}

	/* Вывод формы */
	public function actionIndex() {
		$f=new Form($this->id);
		$this->pageTitle=$this->metaTitle=$f->title;
		return $f; //$this->id - идентификатор формы (таблица form)
	}

	protected function breadcrumbIndex() {
		return array('{{pageTitle}}');
	}

	public function adminIndexLink() {
		return array(
			array('form.*','?controller=form&action=form&id='.$this->id,'setting','Настройка формы'),
			array('form.*','?controller=form&action=field&id='.$this->id,'field','Поля формы')
		);
	}

	public function actionIndexSubmit($data) {
		$form=new Form();
		if(!$form->execute($this->id,$data)) return false; //false в случае ошибки или если задан скрипт обработки данных форм
		plushka::redirect('form/'.$this->id.'/success');
	}

	/* Выводит сообщение об успехе */
	public function actionSuccess() {
		$db=plushka::db();
		$form=$db->fetchArrayOnce('SELECT title_'._LANG.',successMessage_'._LANG.',redirect FROM frm_form WHERE id='.$this->id);
		if(!$form) plushka::error404();
		if($form[2]) plushka::redirect($form[2],$form[1]);
		if(!$form[1]) $this->content='<p>'.LNGWeGotYourMessage.'</p>'; else $this->content=$form[1];
		$this->redirect=$form[2];

		$this->pageTitle=$form[0];
		return '_empty';
	}

}