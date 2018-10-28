<?php
/* Реализует модуль регистрации и авторизации OAuth */
class sController extends controller {

	public function right() {
		return array(
			'server'=>'oauth.*',
			'item'=>'oauth.*',
			'widget'=>'*'
		);
	}

	/* Подключение серверов OAuth (социальных сетей) */
	public function actionServer() {
		$db=core::db();
		$cfg=core::config('oauth');
		$t=core::table();
		$t->rowTh('Сервер|Статус');
		$cfgSocial=core::config('admin/oauth');
		foreach($cfgSocial as $id=>$item) {
			$t->link('oauth/item?id='.$id,$item[0]);
			$t->text((isset($cfg[$id]) ? 'подключено' : 'не подключено'));
		} return $t;
	}

	/* Настройка выбранного сервера (соц. сети) */
	public function actionItem() {
		$cfg=core::config('oauth');
		if(isset($cfg[$_GET['id']])) $cfg=$cfg[$_GET['id']]; else $cfg=array('','');
		$cfgSocial=core::config('admin/oauth');
		$cfgSocial=$cfgSocial[$_GET['id']];
		$f=core::form();
		$f->label('Сервер:',$cfgSocial[0]);
		$f->hidden('id',$_GET['id']);
		$f->text('appId','ID приложения',$cfg[0]);
		$f->text('secret','Секретный ключ',$cfg[1]);
		$f->submit();
		$this->cite=$cfgSocial[1];
		return $f;
	}

	public function actionItemSubmit($data) {
		core::import('/admin/core/config');
		$cfg=new config('oauth');
		if($data['appId']) $cfg->$data['id']=array($data['appId'],$data['secret']);
		else {
			$s=$data['id'];
			unset($cfg->$s);
		}
		$cfg->save('oauth');
		core::redirect('oauth/server');
	}

	/* Форма регистрации и авторизации
	Параметры: bool $data['register'] - регистрировать новых пользователей */
	public function actionWidget($data=null) {
		$cfg=core::config('oauth');
		$f=core::form();
		$db=core::db();
		$f->select('userGroup','Группа новых пользователей','SELECT id,name FROM user_group WHERE id<200 ORDER BY id',$cfg['userGroup'],'не регистрировать новых пользователей');
		$f->submit('Продолжить','submit');
		return $f;
	}

	public function actionWidgetSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('oauth');
		$cfg->userGroup=($data['userGroup'] ? (int)$data['userGroup'] : false);
		$cfg->save('oauth');
		return true;
	}

}
?>