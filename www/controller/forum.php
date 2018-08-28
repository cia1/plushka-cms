<?php
/* Форум */
class sController extends controller {

	public function __construct() {
		parent::__construct();
		//Преобразования ЧПУ
		$cnt=count($this->url);
		if($this->url[1]!=='index' && $this->url[1]!=='profile') {
			$this->categoryId=(int)$this->url[1];
			if($cnt===2) $this->url[1]='category';
			elseif($cnt===3 && $this->url[2]==='post') $this->url[1]='newTopic';
			else {
				$this->topicId=(int)$this->url[2];
				$this->url[1]='topic';
			}
			if($cnt===4 && $this->url[3]==='post') $this->url[1]='newPost';
		} elseif($this->url[1]==='profile' && $cnt===3) {
			$this->url[1]='user';
		}
		$this->style('forum');
		core::language('forum');
	}

	/* Настройки профиля пользователя */
	public function actionProfile() {
		if(!core::userGroup()) core::redirect('user/login');
		$db=core::db();
		$this->avatar=$db->fetchValue('SELECT avatar FROM forumUser WHERE id='.core::userId());
		if($this->avatar) $this->avatar=core::url().'public/avatar/'.$this->avatar;
		$this->form=core::form();
		$this->form->file('avatar',LNGUploadAvatar);
		$this->form->submit(LNGSave,'submit');
		$this->pageTitle=$this->metaTitle=LNGProfile;
		return 'Profile';
	}

	public function breadcrumbProfile() {
		return array('<a href="'.core::link('forum').'">'.LNGForum.'</a>','{{pageTitle}}');
	}

	public function actionProfileSubmit($data) {
		$userId=core::userId();
		if(!$userId) core::redirect('user/login');
		//Если был выбра файл, то установить аватар
		if($data['avatar']) {
			core::import('core/picture');
			$p=new picture($data['avatar']);
			if(core::error()) return false;
			$cfg=core::config('forum');
			$p->resize($cfg['avatarWidth'],$cfg['avatarHeight']);
			$fname=$p->save('public/avatar/'.$userId);
			//Удалить старый аватар
			$db=core::db();
			$oldAvatar=$db->fetchValue('SELECT avatar FROM forumUser WHERE id='.$userId);
			if($oldAvatar && $oldAvatar!=$fname) unlink(core::path().'public/avatar/'.$oldAvatar);

			core::import('core/model');
			$model=new model('forumUser');
			$model->set(array('id'=>$userId,'avatar'=>$fname));
			if(!$model->save(array(
				'id'=>array('primary'),
				'avatar'=>array('string')
			))) return false;
		} else unset($data['avatar']);
		core::redirect('forum/profile',LNGChangesSaved);
	}

	/* Главная страница */
	public function actionIndex() {
		$db=core::db();
		$this->data=$db->fetchArrayAssoc('SELECT id,title FROM forumCategory ORDER BY sort');
		$this->pageTitle=$this->metaTitle=LNGForum;
		return 'Index';
	}

	protected function breadcrumbIndex() {
		return array('{{pageTitle}}');
	}

	public function adminIndexLink() {
		return array(
			array('forum.setting','?controller=forum&action=setting','setting','Настройки форума'),
			array('forum.category','?controller=forum&action=category','new','Создать раздел')
		);
	}

	public function adminIndexLink2($item) {
		$data=array(
			array('forum.category','?controller=forum&action=categoryDelete&id='.$item['id'],'delete','Удалить категорию','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;')
		);
		if(!isset($item['first'])) $data[]=array('forum.category','?controller=forum&action=categoryUp&id='.$item['id'],'up','В списке выше');
		if(!isset($item['last'])) $data[]=array('forum.category','?controller=forum&action=categoryDown&id='.$item['id'],'down','В списке ниже');
		return $data;
	}

	/* Форум - категория */
	public function actionCategory() {
		$db=core::db();
		$category=$db->fetchArrayOnce('SELECT id,title,newTopic,metaTitle,metaKeyword,metaDescription FROM forumCategory WHERE id='.$this->categoryId);
		if(!$category) core::error404();
		$this->newPost=(bool)$category[2];
		$this->pageTitle=$category[1];
		if($category[3]) $this->metaTitle=$category[3]; else $this->metaTitle=$category[1];
		$this->metaKeyword=$category[4];
		$this->metaDescription=$category[5];
		//Выбрать информацию о темах
		$cfg=core::config('forum');
		$this->onPage=$cfg['onPageTopic'];
		$this->topic=$db->fetchArrayAssoc('SELECT t.id,t.title,t.date,t.lastDate,t.postCount,u.login,u.avatar FROM forumTopic t LEFT JOIN forumUser u ON u.id=t.userId WHERE categoryId='.$this->categoryId.' ORDER BY t.lastDate DESC',$this->onPage);
		for($i=0,$cnt=count($this->topic);$i<$cnt;$i++) {
			if($this->topic[$i]['avatar']) $this->topic[$i]['avatar']=core::url().'public/avatar/'.$this->topic[$i]['avatar'];
			else $this->topic[$i]['avatar']=core::url().'public/avatar/no.png';
		}
		$this->topicTotal=$db->foundRows();
		return 'Category';
		}

	/* Хлебные крошки */
	public function breadcrumbCategory() {
		return array('<a href="'.core::link('forum').'">'.LNGForum.'</a>','{{pageTitle}}');
	}

	/* Интерфейс администратора */
	public function adminCategoryLink() {
		return array(
			array('forum.category','?controller=forum&action=category&id='.$this->categoryId,'edit','Редактировать категорию')
		);
	}

	/* Создание новой темы */
	public function actionNewTopic() {
		if(!core::userGroup()) core::redirect('user/login');
		$db=core::db();
		$this->categoryTitle=$db->fetchValue('SELECT title FROM forumCategory WHERE id='.$this->categoryId);
		if(!$this->categoryTitle) core::error404();
		$f=core::form();
		$f->action='forum/'.$this->categoryId.'/post';
		$f->text('title','Тема');
		$f->textarea('message',LNGMessageText);
		$f->submit();
		$this->pageTitle=$this->metaTitle=LNGNewTopic;
		return $f;
	}

	public function actionNewTopicSubmit($data) {
		if(!core::userGroup()) core::redirect('user/login');
		$db=core::db();
		if(!$db->fetchValue('SELECT newTopic FROM forumCategory WHERE id='.$this->categoryId)) {
			core::error(LNGNewTopicsForbiddenThisCategory);
			return false;
		}
		core::import('core/model');
		$model=new model('forumTopic');
		$data['categoryId']=$this->categoryId;
		$data['userId']=core::userId();
		$data['date']=time();
		$model->set($data);
		if(!$model->save(array(
			'id'=>array('primary'),
			'categoryId'=>array('integer'),
			'userId'=>array('integer'),
			'title'=>array('string',LNGtopic,true,'min'=>7,'max'=>200),
			'date'=>array('integer'),
			'message'=>array('string',LNGmessageText,true,'min'=>10,'mix'=>2000)
		))) return false;
		$db->query('UPDATE forumUser SET postCount=postCount+1 WHERE id='.core::userId()); //счётчик сообщений пользователя
		core::redirect('forum/'.$this->categoryId.'/'.$model->id,LNGTopicCreated);
	}

	public function breadcrumbNewTopic() {
		return array('<a href="'.core::link('forum').'">'.LNGForum.'</a>','<a href="'.core::link('forum/'.$this->categoryId).'">'.$this->categoryTitle.'</a>','{{pageTitle}}');
	}

	/* Форум - Категория - Тема */
	public function actionTopic() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT title,newPost FROM forumCategory WHERE id='.$this->categoryId);
		if(!$data) core::error404();
		$this->categoryTitle=$data[0];
		$this->newPost=$data[1];
		unset($data);
		$this->topic=$db->fetchArrayOnceAssoc('SELECT t.title,t.date,t.message,t.status,t.userId,u.login,u.avatar FROM forumTopic t LEFT JOIN forumUser u ON u.id=t.userId WHERE t.id='.$this->topicId);
		if(!$this->topic) core::error404();

		$this->topic['message']=str_replace(
				array('[b]','[B]','[/b]','[/B]','[i]','[I]','[/i]','[/I]','[u]','[U]','[/u]','[/U]','[img]','[IMG]','[/img]','[/IMG]'),
				array('<b>','<b>','</b>','</b>','<i>','<i>','</i>','</i>','<u>','<u>','</u>','</u>','<img src="','<img src="','" alt="">','" alt="">'),
				nl2br($this->topic['message'])
		);

		if($this->topic['avatar']) $this->topic['avatar']=core::url().'public/avatar/'.$this->topic['avatar'];
		else $this->topic['avatar']=core::url().'public/avatar/no.png';
		$this->pageTitle=$this->metaTitle=$this->topic['title'];
		//Выбор сообщений
		$cfg=core::config('forum');
		$this->onPage=$cfg['onPagePost']-1;
		$this->post=$db->fetchArrayAssoc('SELECT p.id,p.date,p.message,p.userId,u.login,u.avatar FROM forumPost p LEFT JOIN forumUser u ON u.id=p.userId WHERE p.topicId='.$this->topicId.' ORDER BY p.date',$this->onPage);
		for($i=0,$cnt=count($this->post);$i<$cnt;$i++) {
			if($this->post[$i]['avatar']) $this->post[$i]['avatar']=core::url().'public/avatar/'.$this->post[$i]['avatar'];
			else $this->post[$i]['avatar']=core::url().'public/avatar/no.png';
			$this->post[$i]['message']=str_replace(
				array('[b]','[B]','[/b]','[/B]','[i]','[I]','[/i]','[/I]','[u]','[U]','[/u]','[/U]','[img]','[IMG]','[/img]','[/IMG]'),
				array('<b>','<b>','</b>','</b>','<i>','<i>','</i>','</i>','<u>','<u>','</u>','</u>','<img src="','<img src="','" alt="">','" alt="">'),
				nl2br($this->post[$i]['message'])
			);
		}
		$this->postTotal=$db->foundRows(); //количество сообщений (включая первое) - нужно для пагинации

		//Форма для написания ответа.
		if($this->topic['status'] && core::userGroup() && $this->newPost) { //если тема открыта и пользователь авторизован
			$this->formReply=core::form();
			$this->formReply->action='forum/'.$this->categoryId.'/'.$this->topicId.'/post';
			$this->formReply->textarea('message',LNGMessageText);
			$this->formReply->submit();
		}
		return 'Topic';
	}

	/* Хлебные крошки */
	public function breadcrumbTopic() {
		return array('<a href="'.core::link('forum').'">'.LNGForum.'</a>','<a href="'.core::link('forum/'.$this->categoryId).'">'.$this->categoryTitle.'</a>','{{pageTitle}}');
	}

	public function adminTopicLink() {
		return array(
			array('forum.moderate','?controller=forum&action=topic&id='.$this->topicId,'edit','Редиктировать тему'),
			array('forum.moderate','?controller=forum&action=topicDelete&id='.$this->topicId,'delete','Удалить тему','Удалить','if(!confirm(\'Подтвердите удаление.\')) return false;'),
			array('forum.moderate','?controller=forum&action=topicStatus&id='.$this->topicId,($this->topic['status'] ? 'status0' : 'status1'	),($this->topic['status'] ? 'Закрыть тему' : 'Вновь открыть тему'))
		);
	}

	public function adminTopicLink2($data) {
		return array(
			array('forum.moderate','?controller=forum&action=postEdit&id='.$data['id'],'edit','Редактировать сообщение'),
			array('forum.moderate','?controller=forum&action=postDelete&id='.$data['id'],'delete','Удалить сообщение',null,'if(!confirm(\'Подтвердите удаление.\')) return false;')
		);
	}

	/* Новое сообщение */
	public function actionNewPost() {
		if(!core::userGroup()) core::redirect('user/login');
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT t.title,c.title FROM forumTopic t INNER JOIN forumCategory c ON c.id=t.categoryId WHERE t.id='.$this->topicId);
		if(!$data) core::error404();
		$this->topicTitle=$data[0];
		$this->categoryTitle=$data[1];
		$f=core::form();
		$f->action='forum/'.$this->categoryId.'/'.$this->topicId.'/post';
		$f->textarea('message',LNGMessageText);
		$f->submit();
		$this->pageTitle=$this->metaTitle=LNGNewMessage;
		return $f;
	}

	public function breadcrumbNewPost() {
		return array('<a href="'.core::link('forum').'">'.LNGForum.'</a>','<a href="'.core::link('forum/'.$this->categoryId).'">'.$this->categoryTitle.'</a>','<a href="'.core::link('forum/'.$this->categoryId.'/'.$this->topicId).'">'.$this->topicTitle.'</a>','{{pageTitle}}');
	}

	public function actionNewPostSubmit($data) {
		$db=core::db();
		if(!$db->fetchValue('SELECT newPost FROM forumCategory WHERE id='.$this->categoryId)) {
			core::error(LNGThisTopcWrittingForbidden);
			return false;
		}
		if(!core::userGroup()) core::redirect('user/login'); //писать сообщения могут только зарегистрированные пользователи
		core::import('core/model');
		$model=new model('forumPost');
		$model->set($data);
		$model->topicId=$this->topicId;
		$model->userId=core::userId();
		$model->date=time(); //дата поста
		if(!$model->save(array(
			'id'=>array('primary'),
			'topicId'=>array('integer'),
			'userId'=>array('integer'),
			'date'=>array('integer'),
			'message'=>array('string',LNGmessageText,true,'min'=>2)
		))) return false;
		$db->query('UPDATE forumTopic SET lastDate='.time().',postCount=postCount+1 WHERE id='.$this->topicId);
		$db->query('UPDATE forumUser SET postCount=postCount+1 WHERE id='.core::userId());
		core::redirect('forum/'.$this->categoryId.'/'.$this->topicId);
	}

	/* Информация о пользователе (профиль) */
	public function actionUser() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT login,avatar,date,postCount,status FROM forumUser WHERE id='.(int)$this->url[2].(core::userGroup()>=200 ? '' : ' AND status=1'));
		if(!$data) core::error404();
		$this->login=$data[0];
		if(!$data[1]) $this->avatar='no.png'; else $this->avatar=$data[1];
		$this->avatar=core::url().'public/avatar/'.$this->avatar;
		$this->date=$data[2];
		$this->postCount=$data[3];
		$this->status=(bool)$data[4];
		$this->pageTitle=LNGUser.' &laquo;'.$data[0].'&raquo;';
		$this->metaTitle=LNGUser.' '.$data[0];
		return 'User';
	}

	public function breadcrumbUser() {
		return array('<a href="'.core::link('forum').'">'.LNGForum.'</a>','{{pageTitle}}');
	}

	public function adminUserLink() {
		return array(
			array('forum.moderate','?controller=forum&action=userStatus&id='.$this->url[2],($this->status ? 'status0' : 'status1'),($this->status ? 'Заблокировать' : 'Разблокировать'))
		);
	}

}
?>