<?php
namespace plushka\widget;
use plushka;
use plushka\core\Widget;
use plushka\model\Notification;

class NotificationSettingFormWidget extends Widget {

	public function __invoke() {
		if(isset($_POST['notification'])) self::_submit($_POST['notification']);
		$userId=plushka::userId();
		$group=Notification::groupList($userId); //группы сообщений
		if(!$group) return false;
		$transport=Notification::transportList($userId); //список транспортов
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
				$item='Notification'.ucfirst($item->id()).'Setting';
				if(file_exists(plushka::path().'widget/'.$item.'Widget.php')) {
					plushka::widget($item);
				}
			}
		}
		unset($transport);
		if(!$available) return false;
		$form=plushka::form('notification');
		plushka::language('notification');
		array_unshift($available,array(false,LNGDisabled));
		foreach($group as $g=>$item) {
			$form->radio($g,$item['title'],$available,$item['transport']);
		}
		$form->submit();
		return $form;
	}

	private static function _submit($data) {
		$groupList=array_keys(Notification::groupList());
		$transportList=Notification::transportListFloat();
		foreach($data as $id=>$item) {
			if(in_array($id,$groupList)===false) unset($data[$id]);
			if(in_array($item,$transportList)===false) unset($data[$id]);
		}
		$user=plushka::user()->model();
		$user->attribute('notification',$data);
	}

}
