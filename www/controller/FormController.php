<?php
namespace plushka\controller;
use plushka\core\HTTPException;
use plushka\core\plushka;
use plushka\core\Controller;
use plushka\model\Form;

/**
 * Универсальная контактная форма
 * ЧПУ:
 * `/form/ИД` (actionIndex) - вывод формы;
 * `/form/ИД/success` (actionSuccess) - сообщение после отправки формы
 *
 * @property-read string $content  Текст сообщения для действия "success"
 * @property-read string $redirect URL-адрес для редиректа для действия "success"
 */
class FormController extends Controller {

	/** @var int Идентификатор формы */
	public $id;

	/**
	 * @throws HTTPException
	 */
	public function __construct() {
		parent::__construct();
		$this->id=(int)$this->url[1]; //идентификатор формы
		if(!$this->id) throw new HTTPException(404);
		if(isset($this->url[2])) $this->url[1]=$this->url[2]; else $this->url[1]='index';
	}

	/**
	 * Страница контактной формы
	 * @return Form
	 * @throws HTTPException
	 */
	public function actionIndex() {
		$form=new Form();
		if($form->load($this->id)===false) throw new HTTPException(404);
		$this->pageTitle=$this->metaTitle=$form->title;
		return $form; //$this->id - идентификатор формы (таблица form)
	}

	protected function breadcrumbIndex() {
		return ['{{pageTitle}}'];
	}

	public function adminIndexLink() {
		return [
			['form.*','?controller=form&action=form&id='.$this->id,'setting','Настройка формы'],
			['form.*','?controller=form&action=field&id='.$this->id,'field','Поля формы']
		];
	}

	public function actionIndexSubmit($data) {
		$form=new Form();
		if(!$form->execute($this->id,$data)) return false; //false в случае ошибки или если задан скрипт обработки данных форм
		plushka::redirect('form/'.$this->id.'/success');
		return null;
	}

	/**
	 * Сообщение об успешно отправленной форме
	 * @return string
	 * @throws HTTPException
	 */
	public function actionSuccess() {
		$db=plushka::db();
		$form=$db->fetchArrayOnce('SELECT title_'._LANG.',successMessage_'._LANG.',redirect FROM frm_form WHERE id='.$this->id);
		if(!$form) throw new HTTPException(404);
		if($form[2]) plushka::redirect($form[2],$form[1]);
		if(!$form[1]) $this->content='<p>'.LNGWeGotYourMessage.'</p>'; else $this->content=$form[1];
		$this->redirect=$form[2];

		$this->pageTitle=$form[0];
		return '_empty';
	}

}