<?php class widgetNotificationSettingForm extends widget {

	public function __invoke() {
		core::import('model/notification');
		$userId=core::userId();
		$group=notification::groupList($userId);
		if(!$group) return false;
		$transport=notification::transportList($userId);
		if(!$transport) return false;

		$available=array();
		$widget=array();
		foreach($transport as $i=>$item) {
			if($item->available($userId)===true) { //транспорт готов к использованию
				$available[]=array(
					$item->id(),
					$item->title()
				);
			} else { //транспорт ещё не настроен для этого пользователя
				$item='notification'.ucfirst($item->id()).'Setting';
				if(file_exists(core::path().'widget/'.$item.'.php')) {
					core::widget($item);
				}
			}
		}
		unset($transport);

		if($available) {
			$this->form=core::form();
			core::language('notification');
			foreach($group as $g=>$item) {
				array_unshift($available,array(false,LNGDisabled));
				$this->form->radio($g,$item['title'],$available,$item['transport']);
			}
			$this->form->submit();
		} else $this->form=false;

		return true;
	}

	public function render($view=null) {
//		if($this->setting) foreach($this->setting as $item) {
//			$item->renderConnect(core::userId());
//		}
		if($this->form!==false) $this->form->render();
	}

}