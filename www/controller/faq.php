<?php
/* Модуль "Часто задаваемые вопросы" */
class sController extends controller {

	public function __construct() {
		parent::__construct();
		//Установить метатеги
		$cfg=core::config('faq');
		if($cfg['keyword']) $this->metaKeyword=$cfg['keyword'];
		if($cfg['description']) $this->metaDescription=$cfg['description'];
		core::language('faq');
	}

	/* Список Вопросов и ответов */
	public function actionIndex() {
		$cfg=core::config('faq');
		$db=core::db();
		$this->items=$db->fetchArrayAssoc('SELECT name,question,answer,date FROM faq WHERE answer IS NOT NULL ORDER BY date DESC');
		$this->_newQuestionForm(); //Присоединяет к контроллеру ($this->newQuestion) форму добавления вопроса

		$this->script('jquery.min');
		$this->script('jquery.form');
		$this->style('faq');
		$this->pageTitle=$this->metaTitle=LNGFAQ;
		return 'Index';
	}

	public function actionIndexSubmit($data) {
		if($_SERVER['SCRIPT_NAME']==core::url().'index.php') $inFrame==false; else $inFrame=true;
		$m=core::model('faq');
		$data['date']=time();
		$m->set($data);
		if(!$m->save(array(
			'name'=>array('string',LNGName,true),
			'email'=>array('email','E-mail',true),
			'question'=>array('string',LNGYourQuestion,true),
			'date'=>array('integer'),
			'captcha'=>array('captcha',LNGCaptcha)
		))) {
			if($inFrame) {
				core::error(controller::$error);
				$this->_newQuestionForm(true);
				exit;
			}
			return false;
		}
		//Новый вопрос добавлен. Выслать уведомление администрации.
		core::import('core/email');
		$e=new email();
		$e->from($m->email,$m->name);
		$e->subject(LNGFAQ);
		$e->messageTemplate('admin/faq',$data);
		$cfg=core::config();
		$e->send($cfg['adminEmailEmail']);
		if(!$inFrame) core::redirect('faq',LNGThankyouForQuestion);
		core::success(LNGThankyouForQuestion);
		echo '<dl class="form"><dd class="button"><input type="button" class="button" value="Закрыть" onclick="jQuery(\'#faqContainer\').fadeOut();" /></dd></dl>';
		exit;
	}

	public function adminIndexLink() {
		return array(
			array('faq.*','?controller=faq&action=setting','setting','Настройки модуля'),
			array('faq.*','?controller=faq&action=list','list','Управление вопросами и ответами')
		);
	}

	/* Присоединяет к контроллеру ($this->newQuestion) форму добавления нового вопроса.
	Параметры: bool $render - нужно или нет выводить HTML-представление формы (если форма загружается во фрейме) */
	private function _newQuestionForm($render=false) {
		$f=core::form();
		$f->text('name',LNGName,LNGYourName.'...','id="faqName"');
		$f->text('email','E-mail','Ваш e-mail...','id="faqEmail"');
		$f->textarea('question',LNGYourQuestion,LNGYourQuestion.'...','id="faqQuestion"');
		$f->captcha('captcha',LNGCaptcha.'&nbsp;&nbsp;&nbsp;&nbsp;');
		$f->html('<dd class="submit"><input type="button" onclick="jQuery(\'#faqContainer\').fadeOut();" class="button" style="float:left;color:#666;" value="'.LNGCancel.'" /></dd>');
		$f->submit();
		$this->newQuestion=$f;
		if($render) $this->newQuestion->render();
	}
} ?>