<?php class widgetNotificationSettingForm extends widget {

	public function __invoke() {
		core::import('model/notification');
		if(isset($_POST['notification'])) self::_submit($_POST['notification']);
		$userId=core::userId();
		$group=notification::groupList($userId); //группы сообщений
		if(!$group) return false;
		$transport=notification::transportList($userId); //список транспортов
		if(!$transport) return false;
		$available=array();
		$widget=array();
		foreach($transport as $i=>$item) {
			if($item->available($userId)===true) { //транспорт готов к использованию
				$available[]=array(
					$item->id(),
					$item->title()
				);
			} else { //транспорт ещё не настроен для этого пользователя (требуется указать дополнительные данные)
				$item='notification'.ucfirst($item->id()).'Setting';
				if(file_exists(core::path().'widget/'.$item.'.php')) {
					core::widget($item);
				}
			}
		}
		unset($transport);
		if(!$available) return false;
		$form=core::form('notification');
		core::language('notification');
		foreach($group as $g=>$item) {
			array_unshift($available,array(false,LNGDisabled));
			$form->radio($g,$item['title'],$available,$item['transport']);
		}
		$form->submit();
		return $form;
	}

	private static function _submit($data) {
		$groupList=array_keys(notification::groupList());
		$transportList=notification::transportListFloat();
		foreach($data as $id=>$item) {
			if(in_array($id,$groupList)===false) unset($data[$id]);
			if(in_array($item,$transportList)===false) unset($data[$id]);
		}
		$user=core::user()->model();
		$user->attribute('notification',$data);
	}

}