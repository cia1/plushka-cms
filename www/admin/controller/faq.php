<?php
/* Управление часто задаваемыми вопросами. На сайте может быть только один раздел ЧаВо. */
class sController extends controller {

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
		$cfg=core::config('faq'); //конфигурация модуля
		$htmlAdmin=file_get_contents(core::path().'admin/data/email/faq.html'); //шаблон письма администрации
		$htmlAnswer=file_get_contents(core::path().'/data/email/'._LANG.'.faqAnswer.html'); //шаблон письма ответа на вопрос пользователя
		$f=core::form();
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
		core::import('admin/core/config');
		$cfg=new config('faq');
		$cfg->keyword=$data['keyword'];
		$cfg->description=$data['description'];
		if(!$cfg->save('faq')) return false;
		$f=fopen(core::path().'admin/data/email/faqAnswer.html','w');
		fwrite($f,$data['htmlAnswer']);
		fclose($f);
		core::success('Настройки сохранены');
		core::redirect('faq/setting');
	}

	/* Список вопросов и ответов. Почти дублирует тот список, что в общедоступной части. */
	public function actionList() {
		$db=core::db();
		$t=core::table();
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
		$db=core::db();
		$data=$db->fetchArrayOnceAssoc('SELECT name,email,question,answer,date FROM faq WHERE id='.(int)$_GET['id']);
		if(!$data) core::error404();
		$f=core::form();
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
		$m=core::model('faq');
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
			core::import('core/email');
			$e=new email();
			$cfg=core::config('admin');
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
		core::success($message);
		core::redirect('faq/list');
	}

	/* Удаление вопроса */
	public function actionDelete() {
		$db=core::db();
		$db->query('DELETE FROM faq WHERE id='.$_GET['id']);
		core::redirect('faq/list');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- MENU ------------------------------------------------------------------- */
	/* Ссылка на список вопросов-ответов */
	public function actionMenuList() {
		$f=core::form();
		$f->submit('Продолжить','submit'); //чтобы в $_POST были какие-либо данные
		return $f;
	}

	public function actionMenuListSubmit($data) {
		return 'faq';
	}
/* ----------------------------------------------------------------------------------- */

}
?>