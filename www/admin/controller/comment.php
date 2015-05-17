<?php
/* Управление комментариями */
class sController extends controller {

	public function right($right,$action) {
		if($action=='WidgetComment') return true;
		if(isset($right['comment.moderate'])) return true;
		return false;
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
		$f=core::form();
		$cfg=core::config('comment');
		$f->select('status','Состояние новых сообщений',array(array(0,'публиковать после проверки модератором'),array(1,'публиковать сразу')),$cfg['status']);
		$f->submit();
		return $f;
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->status=(int)$data['status'];
		$cfg->save('comment');
		core::redirect('?controller=comment&action=moderate','Изменения сохранены');
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
		core::redirect('?controller=comment&action=moderate','Изменения сохранены');
	}

	/* Удаление комментария */
	public function actionDelete() {
		$db=core::db();
		$db->query('DELETE FROM comment WHERE id='.(int)$data['id']);
		core::redirect('?controller=comment','Комментарий удалён');
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