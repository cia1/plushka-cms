<?php
namespace plushka\admin\controller;

/* Управление часто задаваемыми вопросами. На сайте может быть только один раздел ЧаВо. */
class FaqController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'setting'=>'faq.setting',
			'list'=>'faq.content',
			'edit'=>'faq.content',
			'delete'=>'faq.content',
			'menuList'=>'faq.setting'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общие настройки модуля */
	public function actionSetting() {
		$cfg=plushka::config('faq'); //конфигурация модуля
		$htmlAdmin=file_get_contents(plushka::path().'admin/data/email/faq.html'); //шаблон письма администрации
		$htmlAnswer=file_get_contents(plushka::path().'/data/email/'._LANG.'.faqAnswer.html'); //шаблон письма ответа на вопрос пользователя
		$f=plushka::form();
		$f->html('<div class="tab"><fieldset><legend>Meta-теги</legend>');
		$f->text('keyword','meta Ключевые слова',$cfg['keyword']);
		$f->text('description','meta Описание',$cfg['description']);
		$f->html('</fieldset><fieldset><legend>Шаблоны писем</legend>');
		$f->editor('htmlAnswer','Ответ пользователю',$htmlAnswer);
		$f->html('<cite>Вы можете использовать следующие теги:<br /><b>{{siteName}}</b> - название сайта (домен), <b>{{siteLink}}</b> - ссылка на главную страницу сайта, <b>{{date}}</b> - дата вопроса, <b>{{name}}</b> - имя пользователя, <b>{{email}}</b> - адрес электронной почты пользователя, <b>{{question}}</b> - текст вопроса, <b>{{answer}}</b> - текст ответа.</cite>');
		$f->html('</fieldset></div>');
		$f->submit('Сохранить');
		$f->html('<script>setTimeout(function() { $(".tab").tab(); },100);</script>');
		return $f;
	}

	public function actionSettingSubmit($data) {
		$cfg=new \plushka\admin\core\Config('faq');
		$cfg->keyword=$data['keyword'];
		$cfg->description=$data['description'];
		if(!$cfg->save('faq')) return false;
		$f=fopen(plushka::path().'admin/data/email/faqAnswer.html','w');
		fwrite($f,$data['htmlAnswer']);
		fclose($f);
		plushka::success('Настройки сохранены');
		plushka::redirect('faq/setting');
	}

	/* Список вопросов и ответов. Почти дублирует тот список, что в общедоступной части. */
	public function actionList() {
		$db=plushka::db();
		$t=plushka::table();
		$t->rowTh('Имя|Вопрос|Ответ|');
		$db->query('SELECT id,name,email,question,answer FROM faq ORDER BY date DESC');
		while($item=$db->fetch()) {
			$t->text($item[1].' (<a href="mailto:'.$item[2].'">'.$item[2].'</a>)');
			if(strlen($item[3])>50) $item[3]=mb_substr($item[3],0,47,'UTF-8').'...';
			if(strlen($item[4])>50) $item[4]=mb_substr($item[4],0,47,'UTF-8').'...'; elseif(!$item[4]) $item[4]='( нет ответа )';
			$t->text($item[3]);
			$t->link('faq/edit?id='.$item[0],$item[4]);
			$t->editDelete('id='.$item[0]);
		}
		return $t;
	}

	/* Редактирование вопроса, также можно написать ответ */
	public function actionEdit() {
		$db=plushka::db();
		$data=$db->fetchArrayOnceAssoc('SELECT name,email,question,answer,date FROM faq WHERE id='.(int)$_GET['id']);
		if(!$data) plushka::error404();
		$f=plushka::form();
		$f->hidden('id',$_GET['id']);
		$f->date('date','Дата',date('d.m.Y',$data['date']));
		$f->text('name','Имя',$data['name']);
		$f->text('email','E-mail',$data['email']);
		$f->text('question','Вопрос',$data['question']);
		$f->textarea('answer','Ответ',$data['answer']);
		$f->checkbox('send','Отправить ответ на e-mail',false);
		$f->submit('Сохранить');
		return $f;
	}

	public function actionEditSubmit($data) {
		$m=plushka::model('faq');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'date'=>array('date'),
			'name'=>array('string'),
			'email'=>array('email','E-mail'),
			'question'=>array('string','Вопрос',true),
			'answer'=>array('string'),
		))) return false;
		//Если администратор отметил, что нужно отправить ответ пользователю
		if(isset($data['send'])) {
			$e=new \plushka\core\Email();
			$cfg=plushka::config('admin');
			$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
			$e->subject('Ответ на вопрос на сайте '.$_SERVER['HTTP_HOST']);
			$e->messageTemplate(
				'faqAnswer',
				array('date'=>date('d.m.Y',$m->date),'name'=>$m->name,'email'=>$m->email,'question'=>$m->question,'answer'=>nl2br($m->answer)),
				true
			);
			$e->send($m->email);
			$message='Изменения сохранены. Ответ отправлен на адрес '.$m->email;
		} else $message='Изменения сохранены';
		plushka::success($message);
		plushka::redirect('faq/list');
	}

	/* Удаление вопроса */
	public function actionDelete() {
		$db=plushka::db();
		$db->query('DELETE FROM faq WHERE id='.$_GET['id']);
		plushka::redirect('faq/list');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Ссылка на список вопросов-ответов */
	public function actionMenuList() {
		$f=plushka::form();
		$f->submit('Продолжить','submit'); //чтобы в $_POST были какие-либо данные
		return $f;
	}

	public function actionMenuListSubmit($data) {
		return 'faq';
	}
/* ----------------------------------------------------------------------------------- */

}
?>