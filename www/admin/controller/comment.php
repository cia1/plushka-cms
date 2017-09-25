<?php
/* Управление комментариями */
class sController extends controller {

	public function right() {
		return array(
			'Moderate'=>'comment.moderate',
			'Setting'=>'comment.setting',
			'Edit'=>'comment.moderate',
			'Delete'=>'comment.moderate',
			'WidgetComment'=>'comment.setting'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Общая кнопка "Комментарии: модерировать". Список всех комментариев, ожидающих модерации */
	public function actionModerate() {
		$this->button('?controller=comment&action=setting','setting','Настройки');
		$db=core::db();
		$db->query('SELECT id,date,name,ip,text FROM comment WHERE status!=1 ORDER BY date DESC');
		$t=core::table();
		$t->rowTh('Дата|Имя|IP|Комментарий|');
		while($item=$db->fetch()) {
			$t->text(date('d.m.Y',$item[1]));
			$t->text($item[2]);
			$t->text($item[3]);
			$t->text($item[4]);
			$t->editDelete('?controller=comment&noConfirm&id='.$item[0]);
		}
		return $t;
	}

	/* Общие настройки модуля комментариев */
	public function actionSetting() {
		$form=core::form();
		$cfg=core::config('comment');
		$form->select('status','Состояние новых сообщений',array(array(0,'публиковать после проверки модератором'),array(1,'публиковать сразу')),$cfg['status']);
		$form->checkbox('Вход через OAuth',$cfg['oauth']);
		$form->submit();
		return $form;
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->status=(int)$data['status'];
		$cfg->oauth=isset($data['oauth']);
		$cfg->save('comment');
		core::success('Изменения сохранены');
		core::redirect('?controller=comment&action=moderate');
	}

	/* Редактирование комментария (в том числе модерация) */
	public function actionEdit() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT name,text,status,date FROM comment WHERE id='.(int)$_GET['id']);
		$f=core::form();
		$f->hidden('id',$_GET['id']); //идентификатор комментария
		$f->checkbox('status','Опубликован',$data[2]);
		$f->label('Дата',date('d.m.Y H:i',$data[3]));
		$f->text('name','Имя',$data[0]);
		$f->textarea('text','Комментарий',str_replace('<br />',"\n",$data[1]));
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionEditSubmit($data) {
		$m=core::model('comment');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'name'=>array('html'),
			'status'=>array('boolean'),
			'text'=>array('html')
		))) return false;
		core::success('Изменения сохранены');
		core::redirect('?controller=comment&action=moderate');
	}

	/* Удаление комментария */
	public function actionDelete() {
		if(isset($_GET['noConfirm'])) { //Удалить без подтверждения (это действие может вызываться из разных мест)
			$db=core::db();
			$db->query('DELETE FROM comment WHERE id='.$_GET['id']);
			core::redirect('?controller=comment&action=moderate');
		}
		$f=core::form();
		$f->html('Подтвердите удаление комментария');
		$f->hidden('id',$_GET['id']);
		$f->submit();
		return $f;
	}

	public function actionDeleteSubmit($data) {
		$db=core::db();
		$id=(int)$data['id'];
		$comment=$db->fetchArrayOnce('SELECT c.status,g.link,c.groupId FROM comment c LEFT JOIN commentGroup g ON g.id=c.groupId WHERE c.id='.$id);
		if(!$comment) core::error404();
		if($comment[0]>0) {
			if(!core::hook('commentDelete',$comment[1],$comment[2],$id)) return false;
		}
		$db->query('DELETE FROM comment WHERE id='.$id);
		core::success('Комментарий удалён');
		core::redirect('?controller=comment');
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Комментарии и форма добавления комментария. Никакой дополнительной настройки не требуется. */
	public function actionWidgetComment() {
		$f=core::form();
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