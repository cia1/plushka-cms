<?php
namespace plushka\admin\controller;

class ChatController extends \plushka\admin\core\Controller {

	protected function right() {
		return array(
			'setting'=>'chat.setting',
			'message'=>'chat.moderate',
			'delete'=>'chat.moderate'
		);
	}

	//Настройки чата
	public function actionSetting() {
		$cfg=plushka::config('chat');
		$form=plushka::form();
		$form->text('messageCount','Количество сообщений в истории',$cfg['messageCount']);
		$alias='';
		foreach($cfg['loginAlias'] as $real=>$virtual) $alias.=$real.'='.$virtual."\n";
		$blacklist=implode("\n",plushka::config('chat-blacklist'));
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
		plushka::import('admin/core/config');
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
		plushka::redirect('chat/setting','Настройки обновлены');
	}

	//Модерирование сообщений, выводит список сообщений с кнопками управления
	public function actionMessage() {
		$chatId=$_GET['chatId'];
		plushka::import('model/chat');
		$content=chat::content($chatId);
		$table=plushka::table();
		$table->rowTh('Время|Кто, кому|Сообщение|');
		foreach($content as $item) {
			$table->text(date('d.m.Y H:i:s',$item['time']));
			if($item['fromId']) $who='<a href="'.plushka::link('admin/user/userItem?id='.$item['fromId']).'">'; else $who='';
			$who.=$item['fromLogin'];
			if($item['fromId']) $who.='</a>';
			else $who=$item['fromLogin'];
			if($item['toLogin']) {
				$who.=' => ';
				if($item['toId']) $who.'<a href="'.plushka::link('admin/user/userItem?id='.$item['toId']).'</a>';
				$who.=$item['toLogin'];
				if($item['toId']) $who.='</a>';
			}
			$table->text($who);
			$table->text($item['message']);
			$table->delete('chatId='.$chatId.'&time='.$item['time'],'delete','Подтвердите удаление сообщения');
		}
		return $table;
	}

	//Удаление сообщение (сразу действие, без формы)
	public function actionDelete() {
		plushka::import('admin/model/chat');
		if(!chat::delete($_GET['chatId'],$_GET['time'])) return '_empty';
		plushka::redirect('chat/message?chatId='.$_GET['chatId']);
	}

	public function actionMenu() {
		$form=plushka::form();
		$form->submit(LNGSend,'submit');
		return $form;
	}

	public function actionMenuSubmit($data) {
		return 'chat';
	}

	public function actionWidget() {
		$form=plushka::form();
		$form->submit(LNGSend,'submit');
		return $form;
	}

	public function actionWidgetSubmit() {
		return '';
	}

}