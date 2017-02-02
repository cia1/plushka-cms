<?php
/* Управление форумом */
class sController extends controller {

	public function right() {
		return array(
			'Setting'=>'forum.category',
			'Category'=>'forum.category',
			'CategoryUp'=>'forum.category',
			'CategoryDown'=>'forum.category',
			'CategoryDelete'=>'forum.category',
			'Topic'=>'forum.moderate',
			'TopicDelete'=>'forum.moderate',
			'TopicStatus'=>'forum.moderate',
			'PostEdit'=>'forum.moderate',
			'PostDelete'=>'forum.moderate',
			'UserStatus'=>'forum.moderate',
			'MenuProfile'=>'*',
			'MenuCategory'=>'*'
		);
	}

	/* Настройки форума */
	public function actionSetting() {
		$cfg=core::config('forum');
		$f=core::form();
		$f->text('onPageTopic','Тем на странице',$cfg['onPageTopic']);
		$f->text('onPagePost','Сообщений на странице',$cfg['onPagePost']);
		$f->text('avatarWidth','Ширина аватара (px)',$cfg['avatarWidth']);
		$f->text('avatarHeight','Высота аватара (px)',$cfg['avatarHeight']);
		$f->submit();
		return $f;
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->onPageTopic=(int)$data['onPageTopic'];
		$cfg->onPagePost=(int)$data['onPagePost'];
		$cfg->avatarWidth=(int)$data['avatarWidth'];
		$cfg->avatarHeight=(int)$data['avatarHeight'];
		$cfg->save('forum');
		core::success('Настройки сохранены');
		core::redirect('?controller=forum&action=setting');
	}

	/* Создание/редактирование категории */
	public function actionCategory() {
		if(isset($_GET['id'])) {
			$db=core::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title,newTopic,newPost,metaTitle,metaKeyword,metaDescription FROM forumCategory WHERE id='.(int)$_GET['id']);
		} else $data=array('id'=>null,'title'=>'','newTopic'=>true,'newPost'=>true,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->text('title','Заголовок',$data['title']);
		$f->checkbox('newTopic','Пользователи могут создавать темы',$data['newTopic']);
		$f->checkbox('newPost','Пользователи могут отвечать в темах',$data['newPost']);
		$f->text('metaTitle','meta Заголовк',$data['metaTitle']);
		$f->text('metaKeyword','meta Ключевые слова',$data['metaKeyword']);
		$f->text('metaDescription','meta Опиание',$data['metaDescription']);
		$f->submit();
		return $f;
	}

	public function actionCategorySubmit($data) {
		core::import('core/model');
		$db=core::db();
		$data['sort']=$db->fetchValue('SELECT MAX(sort) FROM forumCategory')+1;
		$model=new model('forumCategory'); //таблица forumCategory
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'title'=>array('string','заголовок',true),
			'sort'=>array('integer'),
			'newTopic'=>array('boolean'),
			'newPost'=>array('boolean'),
			'metaTitle'=>array('string'),
			'metaKeyword'=>array('string'),
			'metaDescription'=>array('string')
		))) return false;
		core::success('Изменения сохранены');
		core::redirect('?controller=forum&action=category&id='.$model->id);
	}

	/* Сортировка категорий: выше */
	public function actionCategoryUp() {
		$db=core::db();
		$sort=(int)$db->fetchValue('SELECT sort FROM forumCategory WHERE id='.(int)$_GET['id']);
		$db->query('UPDATE forumCategory SET sort='.$sort.' WHERE sort='.($sort-1));
		$db->query('UPDATE forumCategory SET sort='.($sort-1).' WHERE id='.(int)$_GET['id']);
		core::success('Выполнено');
		core::redirect('?controller=forum&action=category&id='.$_GET['id']);
	}

	/* Сортировка категорий: ниже */
	public function actionCategoryDown() {
		$db=core::db();
		$sort=(int)$db->fetchValue('SELECT sort FROM forumCategory WHERE id='.(int)$_GET['id']);
		$db->query('UPDATE forumCategory SET sort='.$sort.' WHERE sort='.($sort+1));
		$db->query('UPDATE forumCategory SET sort='.($sort+1).' WHERE id='.(int)$_GET['id']);
		core::success('Выполнено');
		core::redirect('?controller=forum&action=category&id='.$_GET['id']);
	}

	/* Удаление категории */
	public function actionCategoryDelete() {
		$db=core::db();
		$id=(int)$_GET['id'];
		$db->query('DELETE FROM forumTopic WHERE categoryId='.$id);
		$db->query('DELETE FROM forumCategory WHERE id='.$id);
		core::success('Категория удалена');
		core::redirect('?controller=forum&action=category');
	}

	/* Редактирование темы */
	public function actionTopic() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT id,categoryId,title,message FROM forumTopic WHERE id='.(int)$_GET['id']);
		$f=core::form();
		$f->hidden('id',$data[0]);
		$f->select('categoryId','Категория','SELECT id,title FROM forumCategory ORDER BY sort',$data[1]);
		$f->text('title','Заголовок',$data[2]);
		$f->textarea('message','Сообщение',$data[3]);
		$f->submit();
		return $f;
	}

	public function actionTopicSubmit($data) {
		core::import('core/model');
		$model=new model('forumTopic');
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'categoryId'=>array('integer'),
			'title'=>array('string','заголовок',true),
			'message'=>array('string','сообщение',true)
		))) return false;
		core::success('Выполнено');
		core::redirect('?controller=forum&action=topic&id='.$model->id);
	}

	/* Удаление темы */
	public function actionTopicDelete() {
		$db=core::db();
		$id=(int)$_GET['id'];
		//Если администратор удаляет тему, то на это есть причина и нужно также откатить счётчик сообщений
		$topicUserId=$db->fetchValue('SELECT userId FROM forumTopic WHERE id='.$id);
		if(!$topicUserId) core::error404();
		$data=$db->fetchArray('SELECT userId,COUNT(userId) FROM forumPost WHERE topicId='.$id.' GROUP BY userId');
		foreach($data as $item) {
			if($item[0]==$topicUserId) $item[1]++;
			$db->query('UPDATE forumUser SET postCount=postCount-'.$item[1].' WHERE id='.$item[0]);
		}

		$db->query('DELETE FROM forumPost WHERE topicId='.$id);
		$db->query('DELETE FROM forumTopic WHERE id='.$id);
		core::success('Тема удалена');
		core::redirect('?controller=forum&action=topic&id='.$id);
	}

	/* Закрыть/открыть тему */
	public function actionTopicStatus() {
		$db=core::db();
		$status=$db->fetchValue('SELECT status FROM forumTopic WHERE id='.(int)$_GET['id']);
		if($status===false) core::error404();
		$db->query('UPDATE forumTopic SET status='.($status ? '0' : '1').' WHERE id='.(int)$_GET['id']);
		core::success(($status ? 'Тема закрыта' : 'Тема вновь открыта'));
		core::redirect('?controller=forum&action=topic&id='.$id);
	}

	/* Редактирование сообщения */
	public function actionPostEdit() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT id,message FROM forumPost WHERE id='.(int)$_GET['id']);
		if(!$data) core::error404();
		$f=core::form();
		$f->hidden('id',$data[0]);
		$f->textarea('message','Сообщение',$data[1]);
		$f->submit();
		return $f;
	}

	public function actionPostEditSubmit($data) {
		core::import('core/model');
		$model=new model('forumPost');
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'message'=>array('html','Сообщение',true)
		))) return false;
		core::success('Изменения сохранены');
		core::redirect('?controlller=forum&action=post&id='.$model->id);
	}

	/* Удаление сообщения */
	public function actionPostDelete() {
		core::import('core/model');
		$db=core::db();
		$id=(int)$_GET['id'];
		$data=$db->fetchArrayOnce('SELECT p1.topicId,MAX(p2.date) FROM forumPost p1 LEFT JOIN forumPost p2 ON p2.topicId=p1.topicId AND p2.id!='.$id.' WHERE p1.id='.$id);
		if(!$data) core::error404();
		$data[1]=(int)$data[1];
		$db->query('DELETE FROM forumPost WHERE id='.$id);
		$db->query('UPDATE forumTopic SET lastDate='.$data[1].',postCount=postCount-1 WHERE id='.$data[0]);
		core::success('Сообщение удалено');
		core::redirect('?controller=forum&action=topic');
	}

	/* Блокировка и разблокировка пользователя */
	public function actionUserStatus() {
		$db=core::db();
		$id=(int)$_GET['id'];
		$status=$db->fetchValue('SELECT status FROM forumUser WHERE id='.$id);
		if($status===false) core::error404();
		if(!$status) $status=1; else $status=0;
		$db->query('UPDATE forumUser SET status='.$status.' WHERE id='.$id);
		core::success(($status ? 'Пользователь разблокирован' :'Пользователь заблокирован'));
		core::redirect('?controller=forum&action=userStatus');
	}
/* ----------------------------------------------------------------------------------- */



/* ---------- MENU ------------------------------------------------------------------- */
	/* Профиль пользователя. Ссылка forum/profile */
	public function actionMenuProfile() {
		$f=core::form();
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionMenuProfileSubmit($data) {
		return 'forum/profile';
	}

	/* Разделы форума */
	public function actionMenuCategory() {
		$f=core::form();
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionMenuCategorySubmit($data) {
		return 'forum';
	}
/* ----------------------------------------------------------------------------------- */

}
?>