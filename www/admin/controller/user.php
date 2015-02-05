<?php
/* Управление пользователями и группами */
class sController extends controller {

	public function right($right) {
		switch($this->url[1]) {
		case 'Group': case 'GroupItem': case 'GroupDelete':
			if(isset($right['user.group'])) return true; else return false;
			break;
		case 'User': case 'UserItem': case 'userDelete': case 'Status': case 'Replace':  case 'Return':
			if(isset($right['user.user'])) return true; else return false;
		}
		return false;
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Список групп пользователей */
	public function actionGroup() {
		$this->button('action=groupItem','new','Создать новую группу пользователей');
		$t=core::table();
		$t->rowTh('Группа|Описание|');
		$db=core::db();
		$db->query('SELECT id,name FROM userGroup ORDER BY id');
		while($item=$db->fetch()) {
			$t->link($item[0],'?controller=user&action=groupItem&id='.$item[0]);
			$t->link($item[1],'?controller=user&action=groupItem&id='.$item[0]);
			$t->delete('?controller=user&action=groupDelete&id='.$item[0]);
		}

		$this->cite='Каждый посетитель относится к одной из групп пользователей (0-255), определяющей его роль на сайте (0 - не авторизованный пользователь).<br /><u>Внимание</u>! Пользователи групп 200-255 считаются администраторами, пользователь группы 255 имеет неограниченные права. Рекомендуется создавать такие группы: <b>255</b> (суперпользователь), <b>250</b> (администратор), <b>200</b> (редактор), <b>1</b> (зарегистрированный пользователь, если требуется).';
		return $t;
	}

	/* Создание или изменение группы пользователей */
	public function actionGroupItem() {
		$db=core::db();
		if(isset($_GET['id'])) { //Редактирование
			$data=array('id'=>$_GET['id'],'name'=>$db->fetchValue('SELECT name FROM userGroup WHERE id='.$_GET['id']));
		} else { //Создание
			$data=array('id'=>null,'name'=>'');
		}
		$f=core::form();
		if($data['id']) {
			$f->label('Группа',$data['id']);
			$f->hidden('id',$data['id']);
		} else $f->text('id','Группа','','onkeyup="if(parseInt(this.value)<200 || parseInt(this.value)>254) $(\'#_admRight\').slideUp(); else $(\'#_admRight\').slideDown();"');
		$f->text('name','Описание',$data['name']);
		if(!$data['id'] || ($data['id']>=200 && $data['id']!=255)) { //Если группа пользователей относится к администраторам или ещё неизвестна, то присоединить чекбоксы с правами пользователей
			$f->html('<div id="_admRight"><h2>Права группы пользователей</h2><fieldset>');
			$db->query('SELECT module,description,groupId FROM userRight ORDER BY module');
			$module1='';
			while($item=$db->fetch()) {
				$s=explode('.',$item[0]);
				$group=explode(',',$item[2]);
				if($s[0]!=$module1 && $module1) $f->html('</fieldset><fieldset>');
				if($data['id'] && in_array($data['id'],$group)) $checked=true; else $checked=false;
				$f->checkbox('right]['.$item[0],$item[1],$checked);
				$module1=$s[0];
			}
			$f->html('</fieldset></div>');
		}
		$f->submit('Сохранить');
		return $f;
	}

	public function actionGroupItemSubmit($data) {
		core::import('admin/model/userGroup');
		$model=new modelUserGroup();
		$model->set($data);
		if(!$model->save()) return false;
		core::redirect('?controller=user&action=group');
	}

	/* Удаление группы */
	public function actionGroupDelete() {
		core::import('admin/model/userGroup');
		$model=new modelUserGroup();
		if(!$model->delete($_GET['id'])) return false;
		core::redirect('?controller=user&action=group','Группа пользователей удалена');
	}

	/* Список пользователей */
	public function actionUser() {
		$this->button('action=userItem','new','Создать нового пользователя');
		//Построить SQL-запрос в зависимости от параметров фильтра
		$db=core::db();
		$s='SELECT id,login,groupId,status,email FROM user WHERE groupId<='.core::userGroup();
		if(isset($_GET['group']) && $_GET['group']) {
			$this->group=(int)$_GET['group'];
			$s.=' AND groupId='.$this->group;
		} else $this->group='';
		if(isset($_GET['login']) && $_GET['login']) {
			$this->login=$_GET['login'];
			$s.=' AND login LIKE '.$db->escape('%'.$this->login.'%');
		} else $this->login='';
		if(isset($_GET['email']) && $_GET['email']) {
			$this->email=$_GET['email'];
			$s.=' AND email LIKE '.$db->escape('%'.$this->email.'%');
		} else $this->email='';
		$s.=' ORDER BY status,id DESC';
		$this->data=$db->fetchArrayAssoc($s);
		$cnt=count($this->data);
		for($i=0;$i<$cnt;$i++) {
			if($this->data[$i]['status']!=0 && $this->data[$i]['status']!=2) $s='<a href="'.core::link('?controller=user&action=replace&id='.$this->data[$i]['id']).'"><img src="'.core::url().'admin/public/icon/login16.png" alt="войти" title="Переключиться на этого пользователя" /></a> '; else $s='';
			$this->data[$i]['login']=$s.'<a href="'.core::link('?controller=user&action=userItem&id='.$this->data[$i]['id']).'">'.$this->data[$i]['login'].'</a>';
			if($this->data[$i]['status']=='0') $this->data[$i]['status']='<a href="'.core::link('?controller=user&action=status&id='.$this->data[$i]['id']).'"><img src="'.core::url().'admin/public/icon/status016.png" alt="E-mail  не подтверждён" title="E-mail  не подтверждён" /></a>';
			elseif($this->data[$i]['status']=='1') $this->data[$i]['status']='<a href="'.core::link('?controller=user&action=status&id='.$this->data[$i]['id']).'"><img src="'.core::url().'admin/public/icon/status116.png" alt="Активен" title="Активен" /></a>';
			elseif($this->data[$i]['status']=='2') $this->data[$i]['status']='<a href="'.core::link('?controller=user&action=status&id='.$this->data[$i]['id']).'"><img src="'.core::url().'admin/public/icon/status016.png" alt="Заблокирован" title="Заблокирован" /></a>';
		}
		return 'User';
	}

	/* Создание или изменение пользователя */
	public function actionUserItem() {
		core::import('model/user');
		$model=new modelUser();
		if(isset($_GET['id'])) $data=$model->loadById($_GET['id'],'*'); //Если редактирование, то загрузить данные пользователя
		else $data=array('id'=>null,'login'=>'','password'=>'','groupId'=>1,'status'=>true,'email'=>'');
		$f=core::form();
		$f->hidden('id',$data['id']);
		$f->text('login','Логин',$data['login']);
		$f->password('password','Пароль');
		$f->password('password2','Пароль ещё раз');
		$f->text('email','E-mail',$data['email']);
		$f->select('groupId','Группа','SELECT id,name FROM userGroup ORDER BY id',$data['groupId']);
		$f->checkbox('status','Активен',$data['status']);
		$f->submit('Сохранить');
		$f->checkbox('sendMessage','Отправить уведомление');

		if($data['id']) $this->cite='Для смены пароля заполните поля &laquo;Пароль&raquo; и &laquo;Пароль ещё раз&raquo;. Если пароль менять не нужно, то оставьте эти поля пустыми.<br />';
		$this->cite.=' Если признак &laquo;Отправить уведомление&raquo; отмечен, то на указанный e-mail будет отправлено сообщение, содержащее регистрационные данные (включая пароль).';
		return $f;
	}

	public function actionUserItemSubmit($data) {
		if($data['id']) $isNew=false; else $isNew=true;
		core::import('model/user');
		$model=new modelUser();
		$model->set($data);
		if(!$model->save()) return false;
		$s='Изменения сохранены';
			//Если отмечен чекбокс "отправить регистрационные данные", то выслать пользователю его регистрационные данные
		if(isset($data['sendMessage'])) {
			$model->sendMail('info');
			$s.='<br />Регистрационные данные отправлены на адрес '.$data['email'];
		}
		core::redirect('?controller=user&action=user',$s);
	}

	/* Смена статуса пользователя (активен/заблокирован) */
	public function actionStatus() {
		core::import('model/user');
		$modelUser=new modelUser();
		$modelUser->loadById($_GET['id'],'id,status');
		$modelUser->status=!$modelUser->status;
		$modelUser->save(false,'id,status');
		core::redirect('?controller=user&action=user');
	}

	/* Удаление пользователя.
	Обработку события удаления пользователя добавлю при первой необходимости */
	public function actionUserDelete() {
		core::import('model/user');
		$model=new modelUser();
		$model->delete($_GET['id']);
		core::redirect('?controller=user&action=user');
	}

	/* Вход в режим подмены пользователя */
	public function actionReplace() {
		$_SESSION['userCore']=new user($_GET['id']);
		core::redirectPublic('/');
	}

	/* Выход из режима подмены пользователя */
	public function actionReturn() {
		unset($_SESSION['userCore']);
		core::redirectPublic('/');
	}

	/* Отправка личного сообщения */
	public function actionMessage() {
		$u=new user($_GET['id']); //ИД пользователя, которому нужно отправить сообщение
		$f=core::form();
		$f->hidden('user2Id',$u->id);
		$f->hidden('user2Login',$u->login);
		$f->hidden('user2Email',$u->email);
		$f->label('Кому',$u->login);
		$f->editor('message','Сообщение');
		$f->checkbox('email','Отправить на e-mail',true);
		$f->submit('Отправить');

		$this->cite='Сообщение будет отправлено по внутренней почте сайта. Если отмечен признак <b>Отправить на e-mail</b>, то сообщение также будет отправленно на электронную почту пользователя.';
		return $f;
	}

	public function actionMessageSubmit($data) {
		core::import('model/user');
		if(!modelUser::message($data['user2Id'],$data['user2Login'],$data['message'],(isset($data['email']) ? true : false))) return false;
		core::redirect('?controller=user','Сообщение отправлено');
	}
/* ----------------------------------------------------------------------------------- */

}
?>