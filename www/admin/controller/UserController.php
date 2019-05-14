<?php
namespace plushka\admin\controller;
use plushka;
use plushka\admin\model\User;

/* Управление пользователями и группами */
class UserController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'group'=>'user.group',
			'groupItem'=>'user.group',
			'groupDelete'=>'user.group',
			'user'=>'user.user',
			'userItem'=>'user.user',
			'status'=>'user.user',
			'userDelete'=>'user.user',
			'replace'=>'user.replace',
			'return'=>'user.replace',
			'message'=>'*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */

	/* Список групп пользователей */
	public function actionGroup() {
		$this->button('user/groupItem','new','Создать новую группу пользователей');
		$t=plushka::table();
		$t->rowTh('Группа|Описание|');
		$db=plushka::db();
		$db->query('SELECT id,name FROM user_group ORDER BY id');
		while($item=$db->fetch()) {
			$t->link('user/groupItem?id='.$item[0],$item[0]);
			$t->link('user/groupItem?id='.$item[0],$item[1]);
			$t->delete('id='.$item[0],'group');
		}

		$this->cite='Каждый посетитель относится к одной из групп пользователей (0-255), определяющей его роль на сайте (0 - не авторизованный пользователь).<br /><u>Внимание</u>! Пользователи групп 200-255 считаются администраторами, пользователь группы 255 имеет неограниченные права. Рекомендуется создавать такие группы: <b>255</b> (суперпользователь), <b>250</b> (администратор), <b>200</b> (редактор), <b>1</b> (зарегистрированный пользователь, если требуется).';
		return $t;
	}

	protected function helpGroup() {
		return 'core/user-group';
	}

	/* Создание или изменение группы пользователей */
	public function actionGroupItem() {
		$db=plushka::db();
		if(isset($_GET['id'])) { //Редактирование
			$data=array('id'=>$_GET['id'],'name'=>$db->fetchValue('SELECT name FROM user_group WHERE id='.$_GET['id']));
		} else { //Создание
			$data=array('id'=>null,'name'=>'');
		}
		$f=plushka::form();
		if($data['id']) {
			$f->label('Группа',$data['id']);
			$f->hidden('id',$data['id']);
		} else $f->number('id','Группа','','onkeyup="if(parseInt(this.value)<200 || parseInt(this.value)>254) $(\'#_admRight\').slideUp(); else $(\'#_admRight\').slideDown();" min="1" max="255"');
		$f->text('name','Описание',$data['name']);
		if(!$data['id'] || ($data['id']>=200 && $data['id']!=255)) { //Если группа пользователей относится к администраторам или ещё неизвестна, то присоединить чекбоксы с правами пользователей
			$f->html('<div id="_admRight"><h2>Права группы пользователей</h2><fieldset>');
			$db->query('SELECT module,description,groupId FROM user_right ORDER BY module');
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
		$userGroup=new \plushka\admin\model\UserGroup();
		$userGroup->set($data);
		if(!$userGroup->save()) return false;
		plushka::redirect('user/group');
	}

	/* Удаление группы */
	public function actionGroupDelete() {
		plushka::import('admin/model/userGroup');
		$model=new userGroup();
		if(!$model->delete($_GET['id'])) return false;
		plushka::redirect('user/group','Группа пользователей удалена');
	}

	/* Список пользователей */
	public function actionUser() {
		$this->button('user/userItem','new','Создать нового пользователя');
		//Построить SQL-запрос в зависимости от параметров фильтра
		$db=plushka::db();
		$s='SELECT id,login,groupId,status,email FROM user WHERE groupId<='.plushka::userGroup();
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
			if($this->data[$i]['status']!=0 && $this->data[$i]['status']!=2) $s='<a href="'.plushka::link('admin/user/replace?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/login16.png" alt="войти" title="Переключиться на этого пользователя" /></a> '; else $s='';
			$this->data[$i]['login']=$s.'<a href="'.plushka::link('admin/user/userItem?id='.$this->data[$i]['id']).'">'.$this->data[$i]['login'].'</a>';
			if($this->data[$i]['status']=='0') $this->data[$i]['status']='<a href="'.plushka::link('admin/user/status?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/status016.png" alt="E-mail  не подтверждён" title="E-mail  не подтверждён" /></a>';
			elseif($this->data[$i]['status']=='1') $this->data[$i]['status']='<a href="'.plushka::link('admin/user/status?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/status116.png" alt="Активен" title="Активен" /></a>';
			elseif($this->data[$i]['status']=='2') $this->data[$i]['status']='<a href="'.plushka::link('admin/user/status?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/status016.png" alt="Заблокирован" title="Заблокирован" /></a>';
		}
		return 'User';
	}

	protected function helpUser() {
		return 'core/user';
	}

	/* Создание или изменение пользователя */
	public function actionUserItem() {
		$user=new User();
		if(isset($_GET['id'])) $user->loadById($_GET['id'],'*'); //Если редактирование, то загрузить данные пользователя
		$f=plushka::form();
		$f->hidden('id',$user->id);
		$f->text('login','Логин',$user->login);
		$f->password('password','Пароль');
		$f->password('password2','Пароль ещё раз');
		$f->text('email','E-mail',$user->email);
		$f->select('groupId','Группа','SELECT id,name FROM user_group ORDER BY id',$user->groupId);
		$f->checkbox('status','Активен',$user->status);
		$f->submit('Сохранить');
		$f->checkbox('sendMessage','Отправить уведомление');

		if($user->id) $this->cite='Для смены пароля заполните поля &laquo;Пароль&raquo; и &laquo;Пароль ещё раз&raquo;. Если пароль менять не нужно, то оставьте эти поля пустыми.<br />';
		$this->cite.=' Если признак &laquo;Отправить уведомление&raquo; отмечен, то на указанный e-mail будет отправлено сообщение, содержащее регистрационные данные (включая пароль).';
		return $f;
	}

	public function actionUserItemSubmit($data) {
		if($data['password'] && $data['password']!=$data['password2']) {
			plushka::error('Введённые пароли не совпадают');
			return false;
		}
		$user=new User();
		$user->set($data);
		if(!$user->save()) return false;
		$s='Изменения сохранены';
		//Если отмечен чекбокс "отправить регистрационные данные", то выслать пользователю его регистрационные данные
		if(isset($data['sendMessage'])) {
			$user->sendMail('info');
			$s.='<br />Регистрационные данные отправлены на адрес '.$data['email'];
		}
		plushka::redirect('user/user',$s);
	}

	/* Смена статуса пользователя (активен/заблокирован) */
	public function actionStatus() {
		$user=new User();
		$user->status($_GET['id']);
		plushka::redirect('user/user');
	}

	/* Удаление пользователя.
	Обработку события удаления пользователя добавлю при первой необходимости */
	public function actionUserDelete() {
		$user=new User();
		$user->delete($_GET['id']);
		plushka::redirect('user/user');
	}

	/* Вход в режим подмены пользователя */
	public function actionReplace() {
		$_SESSION['userReal']=$_SESSION['user'];
		$_SESSION['user']=new \plushka\core\User($_GET['id']);
		plushka::redirectPublic('/');
	}

	/* Выход из режима подмены пользователя */
	public function actionReturn() {
		if(isset($_SESSION['userReal'])===true) {
			$_SESSION['user']=$_SESSION['userReal'];
			unset($_SESSION['userReal']);
		}
		plushka::redirectPublic('/');
	}

	/* Отправка личного сообщения */
	public function actionMessage() {
		$u=new \plushka\core\User($_GET['id']); //ИД пользователя, которому нужно отправить сообщение
		$f=plushka::form();
		$f->hidden('user2Id',$u->id);
		$f->hidden('user2Login',$u->login);
		$f->hidden('user2Email',$u->email);
		$f->label('Кому',$u->login);
		$f->editor('message','Сообщение');
		$f->submit('Отправить');

		$this->cite='Сообщение будет отправлено по внутренней почте сайта. Если отмечен признак <b>Отправить на e-mail</b>, то сообщение также будет отправленно на электронную почту пользователя.';
		return $f;
	}

	public function actionMessageSubmit($data) {
		$user=plushka::user();
		if(!$user->model()->message($data['user2Id'],$data['user2Login'],$data['message'])) return false;
		plushka::redirect('user','Сообщение отправлено');
	}
/* ----------------------------------------------------------------------------------- */

}