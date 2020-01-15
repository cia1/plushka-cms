<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\FormEx;
use plushka\admin\core\plushka;
use plushka\admin\core\Table;
use plushka\admin\model\User;
use plushka\admin\model\UserGroup;
use plushka\core\User as UserCore;

/**
 * Управление пользователями и группами
 *
 * `/admin/user/group` - список групп
 * `/admin/user/groupItem?id={groupId}` - создание или изменение группы пользователей
 * `/admin/user/groupDelete?id={groupId}` - удаление группы
 * `/admin/user/user[?group={groupId}]` - список пользователей
 * `/admin/user/userItem?id={userId}` - создание/редактирование пользователя
 * `/admin/user/status?id={userId}` - смена статуса пользователя (активен/заблокирован)
 * `/admin/user/userDelete?id={userId}` - удаление пользователя
 * `/admin/user/replace?id={userId}` - вход в режим подмены пользователя
 * `/admin/user/return` - выход из режима подмены пользователя
 * `/admin/user/message?id={userId}` - отправка личного сообщения
 *
 * @property-read int    $group (actionUser)
 * @property-read string $login (actionUser)
 * @property-read string $email (actionUser)
 * @property-read array  $data  (actionUser)
 */
class UserController extends Controller {

	public function right(): array {
		return [
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
		];
	}

	/**
	 * Список групп пользователей
	 * @return Table
	 */
	public function actionGroup(): Table {
		$this->button('user/groupItem','new','Создать новую группу пользователей');
		$table=plushka::table();
		$table->rowTh('Группа|Описание|');
		$db=plushka::db();
		$db->query('SELECT id,name FROM user_group ORDER BY id');
		while($item=$db->fetch()) {
			$table->link('user/groupItem?id='.$item[0],$item[0]);
			$table->link('user/groupItem?id='.$item[0],$item[1]);
			$table->delete('id='.$item[0],'group');
		}
		$this->cite='Каждый посетитель относится к одной из групп пользователей (0-255), определяющей его роль на сайте (0 - не авторизованный пользователь).<br /><u>Внимание</u>! Пользователи групп 200-255 считаются администраторами, пользователь группы 255 имеет неограниченные права. Рекомендуется создавать такие группы: <b>255</b> (суперпользователь), <b>250</b> (администратор), <b>200</b> (редактор), <b>1</b> (зарегистрированный пользователь, если требуется).';
		return $table;
	}

	protected function helpGroup(): string {
		return 'core/user-group';
	}

	/**
	 * Создание или изменение группы пользователей
	 * @return FormEx
	 */
	public function actionGroupItem(): FormEx {
		$db=plushka::db();
		if(isset($_GET['id'])===true) { //Редактирование
			$data=['id'=>(int)$_GET['id'],'name'=>$db->fetchValue('SELECT name FROM user_group WHERE id='.(int)$_GET['id'])];
		} else { //Создание
			$data=['id'=>null,'name'=>''];
		}
		$form=plushka::form();
		if($data['id']!==null) {
			$form->label('Группа',$data['id']);
			$form->hidden('id',$data['id']);
		} else $form->number('id','Группа','','onkeyup="if(parseInt(this.value)<200 || parseInt(this.value)>254) $(\'#_admRight\').slideUp(); else $(\'#_admRight\').slideDown();" min="1" max="255"');
		$form->text('name','Описание',$data['name']);
		if($data['id']===null || ($data['id']>=200 && $data['id']!=255)) { //Если группа пользователей относится к
			// администраторам или ещё неизвестна, то присоединить чекбоксы с правами пользователей
			$form->html('<div id="_admRight"><h2>Права группы пользователей</h2><fieldset>');
			$db->query('SELECT module,description,groupId FROM user_right ORDER BY module');
			$module1='';
			while($item=$db->fetch()) {
				$s=explode('.',$item[0]);
				$group=explode(',',$item[2]);
				if($s[0]!=$module1 && $module1) $form->html('</fieldset><fieldset>');
				if($data['id'] && in_array($data['id'],$group)) $checked=true; else $checked=false;
				$form->checkbox('right]['.$item[0],$item[1],$checked);
				$module1=$s[0];
			}
			$form->html('</fieldset></div>');
		}
		$form->submit('Сохранить');
		return $form;
	}

	public function actionGroupItemSubmit(array $data): void {
		$userGroup=new UserGroup();
		$userGroup->set($data);
		if($userGroup->save()===false) return;
		plushka::redirect('user/group');
	}

	/**
	 * Удаление группы
	 */
	public function actionGroupDelete(): void {
		$model=new UserGroup();
		if($model->delete($_GET['id'])===false) return;
		plushka::redirect('user/group','Группа пользователей удалена');
	}

	/**
	 * Список пользователей
	 * @return string
	 */
	public function actionUser(): string {
		$this->button('user/userItem','new','Создать нового пользователя');
		//Построить SQL-запрос в зависимости от параметров фильтра
		$db=plushka::db();
		$s='SELECT id,login,groupId,status,email FROM user WHERE groupId<='.plushka::userGroup();
		if(isset($_GET['group'])===true && $_GET['group']) {
			$this->group=(int)$_GET['group'];
			$s.=' AND groupId='.$this->group;
		} else $this->group='';
		if(isset($_GET['login'])===true && $_GET['login']) {
			$this->login=$_GET['login'];
			$s.=' AND login LIKE '.$db->escape('%'.$this->login.'%');
		} else $this->login='';
		if(isset($_GET['email'])===true && $_GET['email']) {
			$this->email=$_GET['email'];
			$s.=' AND email LIKE '.$db->escape('%'.$this->email.'%');
		} else $this->email='';
		$s.=' ORDER BY status,id DESC';
		$this->data=$db->fetchArrayAssoc($s);
		$cnt=count($this->data);
		for($i=0;$i<$cnt;$i++) {
			if($this->data[$i]['status']!==0 && $this->data[$i]['status']!==2) $s='<a href="'.plushka::link('admin/user/replace?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/login16.png" alt="войти" title="Переключиться на этого пользователя" /></a> '; else $s='';
			$this->data[$i]['login']=$s.'<a href="'.plushka::link('admin/user/userItem?id='.$this->data[$i]['id']).'">'.$this->data[$i]['login'].'</a>';
			if($this->data[$i]['status']==='0') $this->data[$i]['status']='<a href="'.plushka::link('admin/user/status?id='
					.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/status016.png" alt="E-mail  не подтверждён" title="E-mail  не подтверждён" /></a>';
			elseif($this->data[$i]['status']==='1') $this->data[$i]['status']='<a href="'.plushka::link('admin/user/status?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/status116.png" alt="Активен" title="Активен" /></a>';
			elseif($this->data[$i]['status']==='2') $this->data[$i]['status']='<a href="'.plushka::link('admin/user/status?id='.$this->data[$i]['id']).'"><img src="'.plushka::url().'admin/public/icon/status016.png" alt="Заблокирован" title="Заблокирован" /></a>';
		}
		return 'User';
	}

	protected function helpUser(): string {
		return 'core/user';
	}

	/**
	 * Создание или изменение пользователя
	 * @return FormEx
	 */
	public function actionUserItem(): FormEx {
		$user=new User();
		if(isset($_GET['id'])===true) $user->loadById($_GET['id'],'*'); //Если редактирование, то загрузить данные пользователя
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

	public function actionUserItemSubmit(array $data): void {
		if($data['password'] && $data['password']!=$data['password2']) {
			plushka::error('Введённые пароли не совпадают');
			return;
		}
		$user=new User();
		$user->set($data);
		if($user->save()===false) return;
		$s='Изменения сохранены';
		//Если отмечен чекбокс "отправить регистрационные данные", то выслать пользователю его регистрационные данные
		if(isset($data['sendMessage'])===true) {
			$user->sendMail('info');
			$s.='<br />Регистрационные данные отправлены на адрес '.$data['email'];
		}
		plushka::redirect('user/user',$s);
	}

	/**
	 * Смена статуса пользователя (активен/заблокирован)
	 */
	public function actionStatus(): void {
		$user=new User();
		$user->status($_GET['id']);
		plushka::redirect('user/user');
	}

	/**
	 * Удаление пользователя.
	 */
	public function actionUserDelete(): void {
		$user=new User();
		$user->delete($_GET['id']);
		plushka::redirect('user/user');
	}

	/**
	 * Вход в режим подмены пользователя
	 */
	public function actionReplace(): void {
		$_SESSION['userReal']=$_SESSION['user'];
		$_SESSION['user']=new UserCore($_GET['id']);
		plushka::redirectPublic('/');
	}

	/**
	 * Выход из режима подмены пользователя
	 */
	public function actionReturn(): void {
		if(isset($_SESSION['userReal'])===true) {
			$_SESSION['user']=$_SESSION['userReal'];
			unset($_SESSION['userReal']);
		}
		plushka::redirectPublic('/');
	}

	/**
	 * Отправка личного сообщения
	 * @return FormEx
	 */
	public function actionMessage(): FormEx {
		$u=new UserCore($_GET['id']); //ИД пользователя, которому нужно отправить сообщение
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

	public function actionMessageSubmit(array $data): void {
		$user=plushka::user();
		if($user->model()->message($data['message'],$data['user2Id'],$data['user2Login'])===false) return;
		plushka::redirect('user','Сообщение отправлено');
	}

}