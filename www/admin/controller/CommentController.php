<?php
namespace plushka\admin\controller;

/* Управление комментариями */
class CommentController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'moderate'=>'comment.moderate',
			'setting'=>'comment.setting',
			'edit'=>'comment.moderate',
			'delete'=>'comment.moderate',
			'widgetComment'=>'comment.setting'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общая кнопка "Комментарии: модерировать". Список всех комментариев, ожидающих модерации */
	public function actionModerate() {
		$this->button('comment/setting','setting','Настройки');
		$db=plushka::db();
		$db->query('SELECT id,date,name,ip,text FROM comment WHERE status!=1 ORDER BY date DESC');
		$t=plushka::table();
		$t->rowTh('Дата|Имя|IP|Комментарий|');
		while($item=$db->fetch()) {
			$t->text(date('d.m.Y',$item[1]));
			$t->text($item[2]);
			$t->text($item[3]);
			$t->text($item[4]);
			$t->editDelete('noConfirm&id='.$item[0]);
		}
		return $t;
	}

	/* Общие настройки модуля комментариев */
	public function actionSetting() {
		$form=plushka::form();
		$cfg=plushka::config('comment');
		$form->select('status','Состояние новых сообщений',array(array(0,'публиковать после проверки модератором'),array(1,'публиковать сразу')),$cfg['status']);
		$form->checkbox('Вход через OAuth',$cfg['oauth']);
		$form->submit();
		return $form;
	}

	public function actionSettingSubmit($data) {
		$cfg=new \plushka\admin\core\Config();
		$cfg->status=(int)$data['status'];
		$cfg->oauth=isset($data['oauth']);
		$cfg->save('comment');
		plushka::success('Изменения сохранены');
		plushka::redirect('comment/moderate');
	}

	/* Редактирование комментария (в том числе модерация) */
	public function actionEdit() {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT name,text,status,date FROM comment WHERE id='.(int)$_GET['id']);
		$f=plushka::form();
		$f->hidden('id',$_GET['id']); //идентификатор комментария
		$f->checkbox('status','Опубликован',$data[2]);
		$f->label('Дата',date('d.m.Y H:i',$data[3]));
		$f->text('name','Имя',$data[0]);
		$f->textarea('text','Комментарий',str_replace('<br />',"\n",$data[1]));
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionEditSubmit($data) {
		$m=plushka::model('comment');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'name'=>array('html'),
			'status'=>array('boolean'),
			'text'=>array('html')
		))) return false;
		plushka::success('Изменения сохранены');
		plushka::redirect('comment/moderate');
	}

	/* Удаление комментария */
	public function actionDelete() {
		if(isset($_GET['noConfirm'])) { //Удалить без подтверждения (это действие может вызываться из разных мест)
			$db=plushka::db();
			$db->query('DELETE FROM comment WHERE id='.$_GET['id']);
			plushka::redirect('comment/moderate');
		}
		$f=plushka::form();
		$f->html('Подтвердите удаление комментария');
		$f->hidden('id',$_GET['id']);
		$f->submit();
		return $f;
	}

	public function actionDeleteSubmit($data) {
		$db=plushka::db();
		$id=(int)$data['id'];
		$comment=$db->fetchArrayOnce('SELECT c.status,g.link,c.groupId FROM comment c LEFT JOIN comment_group g ON g.id=c.groupId WHERE c.id='.$id);
		if(!$comment) plushka::error404();
		if($comment[0]>0) {
			if(plushka::hook('commentDelete',$comment[1],$comment[2],$id)===false) return false;
		}
		$db->query('DELETE FROM comment WHERE id='.$id);
		plushka::success('Комментарий удалён');
		plushka::redirect('comment');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Комментарии и форма добавления комментария. Никакой дополнительной настройки не требуется. */
	public function actionWidgetComment() {
		$f=plushka::form();
		$f->submit('Продолжить','submit'); //чтобы был хотя бы один параметр в $_POST, а то submit-действие не сработает
		$this->cite='<u>Внимание</u>! Если вы убираете виджет с какой-либо страницы, то накопленные на этой странице комментарии будут удалены.';
		return $f;
	}

	public function actionWidgetCommentSubmit($data) {
		return '';
	}
/* ----------------------------------------------------------------------------------- */

}
?>