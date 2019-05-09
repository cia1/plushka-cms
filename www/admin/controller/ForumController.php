<?php
namespace plushka\admin\controller;

/* Управление форумом */
class ForumController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'setting'=>'forum.category',
			'category'=>'forum.category',
			'categoryUp'=>'forum.category',
			'categoryDown'=>'forum.category',
			'categoryDelete'=>'forum.category',
			'topic'=>'forum.moderate',
			'topicDelete'=>'forum.moderate',
			'topicStatus'=>'forum.moderate',
			'postEdit'=>'forum.moderate',
			'postDelete'=>'forum.moderate',
			'userStatus'=>'forum.moderate',
			'menuProfile'=>'*',
			'menuCategory'=>'*'
		);
	}

	/* Настройки форума */
	public function actionSetting() {
		$cfg=plushka::config('forum');
		$f=plushka::form();
		$f->text('onPageTopic','Тем на странице',$cfg['onPageTopic']);
		$f->text('onPagePost','Сообщений на странице',$cfg['onPagePost']);
		$f->text('avatarWidth','Ширина аватара (px)',$cfg['avatarWidth']);
		$f->text('avatarHeight','Высота аватара (px)',$cfg['avatarHeight']);
		$f->submit();
		return $f;
	}

	public function actionSettingSubmit($data) {
		plushka::import('admin/core/config');
		$cfg=new config();
		$cfg->onPageTopic=(int)$data['onPageTopic'];
		$cfg->onPagePost=(int)$data['onPagePost'];
		$cfg->avatarWidth=(int)$data['avatarWidth'];
		$cfg->avatarHeight=(int)$data['avatarHeight'];
		$cfg->save('forum');
		plushka::success('Настройки сохранены');
		plushka::redirect('forum/setting');
	}

	/* Создание/редактирование категории */
	public function actionCategory() {
		if(isset($_GET['id'])) {
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,title,newTopic,newPost,metaTitle,metaKeyword,metaDescription FROM forum_category WHERE id='.(int)$_GET['id']);
		} else $data=array('id'=>null,'title'=>'','newTopic'=>true,'newPost'=>true,'metaTitle'=>'','metaKeyword'=>'','metaDescription'=>'');
		$f=plushka::form();
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
		plushka::import('core/model');
		$db=plushka::db();
		$data['sort']=$db->fetchValue('SELECT MAX(sort) FROM forum_category')+1;
		$model=new model('forum_category'); //таблица forum_category
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
		plushka::success('Изменения сохранены');
		plushka::redirect('forum/category?id='.$model->id);
	}

	/* Сортировка категорий: выше */
	public function actionCategoryUp() {
		$db=plushka::db();
		$sort=(int)$db->fetchValue('SELECT sort FROM forum_category WHERE id='.(int)$_GET['id']);
		$db->query('UPDATE forum_category SET sort='.$sort.' WHERE sort='.($sort-1));
		$db->query('UPDATE forum_category SET sort='.($sort-1).' WHERE id='.(int)$_GET['id']);
		plushka::success('Выполнено');
		plushka::redirect('forum/category?id='.$_GET['id']);
	}

	/* Сортировка категорий: ниже */
	public function actionCategoryDown() {
		$db=plushka::db();
		$sort=(int)$db->fetchValue('SELECT sort FROM forum_category WHERE id='.(int)$_GET['id']);
		$db->query('UPDATE forum_category SET sort='.$sort.' WHERE sort='.($sort+1));
		$db->query('UPDATE forum_category SET sort='.($sort+1).' WHERE id='.(int)$_GET['id']);
		plushka::success('Выполнено');
		plushka::redirect('forum/category?id='.$_GET['id']);
	}

	/* Удаление категории */
	public function actionCategoryDelete() {
		$db=plushka::db();
		$id=(int)$_GET['id'];
		$db->query('DELETE FROM forum_topic WHERE categoryId='.$id);
		$db->query('DELETE FROM forum_category WHERE id='.$id);
		plushka::success('Категория удалена');
		plushka::redirect('forum/category');
	}

	/* Редактирование темы */
	public function actionTopic() {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT id,categoryId,title,message FROM forum_topic WHERE id='.(int)$_GET['id']);
		$f=plushka::form();
		$f->hidden('id',$data[0]);
		$f->select('categoryId','Категория','SELECT id,title FROM forum_category ORDER BY sort',$data[1]);
		$f->text('title','Заголовок',$data[2]);
		$f->textarea('message','Сообщение',$data[3]);
		$f->submit();
		return $f;
	}

	public function actionTopicSubmit($data) {
		plushka::import('core/model');
		$model=new model('forum_topic');
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'categoryId'=>array('integer'),
			'title'=>array('string','заголовок',true),
			'message'=>array('string','сообщение',true)
		))) return false;
		plushka::success('Выполнено');
		plushka::redirect('forum/topic?id='.$model->id);
	}

	/* Удаление темы */
	public function actionTopicDelete() {
		$db=plushka::db();
		$id=(int)$_GET['id'];
		//Если администратор удаляет тему, то на это есть причина и нужно также откатить счётчик сообщений
		$topicUserId=$db->fetchValue('SELECT userId FROM forum_topic WHERE id='.$id);
		if(!$topicUserId) plushka::error404();
		$data=$db->fetchArray('SELECT userId,COUNT(userId) FROM forum_post WHERE topicId='.$id.' GROUP BY userId');
		foreach($data as $item) {
			if($item[0]==$topicUserId) $item[1]++;
			$db->query('UPDATE forum_user SET postCount=postCount-'.$item[1].' WHERE id='.$item[0]);
		}

		$db->query('DELETE FROM forum_post WHERE topicId='.$id);
		$db->query('DELETE FROM forum_topic WHERE id='.$id);
		plushka::success('Тема удалена');
		plushka::redirect('forum/topic?id='.$id);
	}

	/* Закрыть/открыть тему */
	public function actionTopicStatus() {
		$db=plushka::db();
		$status=$db->fetchValue('SELECT status FROM forum_topic WHERE id='.(int)$_GET['id']);
		if($status===false) plushka::error404();
		$db->query('UPDATE forum_topic SET status='.($status ? '0' : '1').' WHERE id='.(int)$_GET['id']);
		plushka::success(($status ? 'Тема закрыта' : 'Тема вновь открыта'));
		plushka::redirect('forum/topic?id='.$id);
	}

	/* Редактирование сообщения */
	public function actionPostEdit() {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT id,message FROM forum_post WHERE id='.(int)$_GET['id']);
		if(!$data) plushka::error404();
		$f=plushka::form();
		$f->hidden('id',$data[0]);
		$f->textarea('message','Сообщение',$data[1]);
		$f->submit();
		return $f;
	}

	public function actionPostEditSubmit($data) {
		plushka::import('core/model');
		$model=new model('forum_post');
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'message'=>array('html','Сообщение',true)
		))) return false;
		plushka::success('Изменения сохранены');
		plushka::redirect('forum/post?id='.$model->id);
	}

	/* Удаление сообщения */
	public function actionPostDelete() {
		plushka::import('core/model');
		$db=plushka::db();
		$id=(int)$_GET['id'];
		$data=$db->fetchArrayOnce('SELECT p1.topicId,MAX(p2.date) FROM forum_post p1 LEFT JOIN forum_post p2 ON p2.topicId=p1.topicId AND p2.id!='.$id.' WHERE p1.id='.$id);
		if(!$data) plushka::error404();
		$data[1]=(int)$data[1];
		$db->query('DELETE FROM forum_post WHERE id='.$id);
		$db->query('UPDATE forum_topic SET lastDate='.$data[1].',postCount=postCount-1 WHERE id='.$data[0]);
		plushka::success('Сообщение удалено');
		plushka::redirect('forum/topic');
	}

	/* Блокировка и разблокировка пользователя */
	public function actionUserStatus() {
		$db=plushka::db();
		$id=(int)$_GET['id'];
		$status=$db->fetchValue('SELECT status FROM forum_user WHERE id='.$id);
		if($status===false) plushka::error404();
		if(!$status) $status=1; else $status=0;
		$db->query('UPDATE forum_user SET status='.$status.' WHERE id='.$id);
		plushka::success(($status ? 'Пользователь разблокирован' :'Пользователь заблокирован'));
		plushka::redirect('forum/userStatus');
	}
/* ----------------------------------------------------------------------------------- */



/* ---------- MENU ------------------------------------------------------------------- */
	/* Профиль пользователя. Ссылка forum/profile */
	public function actionMenuProfile() {
		$f=plushka::form();
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionMenuProfileSubmit($data) {
		return 'forum/profile';
	}

	/* Разделы форума */
	public function actionMenuCategory() {
		$f=plushka::form();
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionMenuCategorySubmit($data) {
		return 'forum';
	}
/* ----------------------------------------------------------------------------------- */

}
?>