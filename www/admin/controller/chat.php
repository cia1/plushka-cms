<?php class sController extends controller {

	protected function right() {
		return array(
			'Setting'=>'chat.setting'
		);
	}

	public function actionSetting() {
		$cfg=core::config('chat');
		$form=core::form();
		$form->text('messageCount','Количество сообщений в истории',$cfg['messageCount']);
		$alias='';
		foreach($cfg['loginAlias'] as $real=>$virtual) $alias.=$real.'='.$virtual."\n";
		$form->textarea('loginAlias','Псевдонимы логинов зарегистрированных пользователей',$alias);
		$form->submit();
		$this->cite='<b>Псевдонимы</b> позволяют заменить в чате имя зарегистрированного пользователя. Укажите строки вида:<br />реальный_логин=псевдоним<br />например:<br />root=Админ';
		return $form;
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->messageCount=(int)$data['messageCount'];
		$alias=array();
		$tmp=explode("\n",$data['loginAlias']);
		foreach($tmp as $item) {
			if(!$item) continue;
			$item=explode('=',$item);
			if(count($item)!=2) continue;
			$alias[trim($item[0])]=trim($item[1]);
		}
		$cfg->loginAlias=$alias;
		if(!$cfg->save('chat')) return;
		core::redirect('?controller=chat&action=setting','Настройки обновлены');
	}

	public function actionMenu() {
		$form=core::form();
		$form->submit(LNGSend,'submit');
		return $form;
	}

	public function actionMenuSubmit($data) {
		return 'chat';
	}

	public function actionWidget() {
		$form=core::form();
		$form->submit(LNGSend,'submit');
		return $form;
	}

	public function actionWidgetSubmit() {
		return '';
	}

}