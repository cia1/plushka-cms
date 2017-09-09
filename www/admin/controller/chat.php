<?php class sController extends controller {

	protected function right() {
		return array(
			'Setting'=>'chat.setting',
			'Message'=>'chat.moderate',
			'Delete'=>'chat.moderate'
		);
	}

	//Настройки чата
	public function actionSetting() {
		$cfg=core::config('chat');
		$form=core::form();
		$form->text('messageCount','Количество сообщений в истории',$cfg['messageCount']);
		$alias='';
		foreach($cfg['loginAlias'] as $real=>$virtual) $alias.=$real.'='.$virtual."\n";
		$blacklist=implode("\n",core::config('chat-blacklist'));
		$form->checkbox('linkFilter','Запрещать внешние ссылки',$cfg['linkFilter']);
		$form->textarea('blacklist','Чёрный список (стоп-фразы)',$blacklist);
		$form->textarea('loginAlias','Псевдонимы логинов зарегистрированных пользователей',$alias);
		$form->text('pageTitle','Заголовок страницы',$cfg['pageTitle_'._LANG]);
		$form->text('metaTitle','META Заголовок',$cfg['metaTitle_'._LANG]);
		$form->text('metaDescription','META Описание',$cfg['metaDescription_'._LANG]);
		$form->text('metaKeyword','META Ключевые слова',$cfg['metaKeyword_'._LANG]);
		$form->submit();
		$this->cite='<b>Псевдонимы</b> позволяют заменить в чате имя зарегистрированного пользователя. Укажите строки вида:<br />реальный_логин=псевдоним<br />например:<br />root=Админ';
		return $form;
	}

	public function actionSettingSubmit($data) {
		core::import('admin/core/config');
		$cfg=new config('chat');
		$cfg->messageCount=(int)$data['messageCount'];
		$cfg->linkFilter=isset($data['linkFilter']);
		$alias=array();
		$tmp=explode("\n",$data['loginAlias']);
		foreach($tmp as $item) {
			if(!$item) continue;
			$item=explode('=',$item);
			if(count($item)!=2) continue;
			$alias[trim($item[0])]=trim($item[1]);
		}
		$cfg->loginAlias=$alias;
		$cfg->{'pageTitle_'._LANG}=trim($data['pageTitle']);
		$cfg->{'metaTitle_'._LANG}=trim($data['metaTitle']);
		$cfg->{'metaDescription_'._LANG}=trim($data['metaDescription']);
		$cfg->{'metaKeyword_'._LANG}=trim($data['metaKeyword']);
		$cfg->save('chat');
		$cfg=new config();
		if($data['blacklist']) $blacklist=explode("\n",$data['blacklist']); else $blacklist=array();
		for($i=0,$cnt=count($blacklist);$i<$cnt;$i++) $blacklist[$i]=trim($blacklist[$i]);
		$cfg->setData($blacklist);
		$cfg->save('chat-blacklist');
		core::redirect('?controller=chat&action=setting','Настройки обновлены');
	}

	//Модерирование сообщений, выводит список сообщений с кнопками управления
	public function actionMessage() {
		core::import('model/chat');
		$content=chat::content();
		$table=core::table();
		$table->rowTh('Время|Кто, кому|Сообщение|');
		foreach($content as $item) {
			$table->text(date('d.m.Y H:i:s',$item['time']));
			if($item['fromId']) $who='<a href="'.core::link('?controller=user&action=userItem&id='.$item['fromId']).'">'; else $who='';
			$who.=$item['fromLogin'];
			if($item['fromId']) $who.='</a>';
			else $who=$item['fromLogin'];
			if($item['toLogin']) {
				$who.=' => ';
				if($item['toId']) $who.'<a href="'.core::link('?controller=user&action=userItem&id='.$item['toId']).'</a>';
				$who.=$item['toLogin'];
				if($item['toId']) $who.='</a>';
			}
			$table->text($who);
			$table->text($item['message']);
			$table->delete('?controller=chat&action=delete&time='.$item['time'],'Подтвердите удаление сообщения');
		}
		return $table;
	}

	//Удаление сообщение (сразу действие, без формы)
	public function actionDelete() {
		core::import('admin/model/chat');
		if(!chat::delete($_GET['time'])) return '_empty';
		core::redirect('?controller=chat&action=message');
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