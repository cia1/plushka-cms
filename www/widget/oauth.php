<?php
/* Кнопки входа через социальные сети */
class widgetOauth extends widget {

	public function action() {
		if(core::userGroup()) return false;
		$this->data=core::config('oauth');
		unset($this->data['userGroup']);
		return 'Oauth';
	}

	public function adminLink() {
		return array(
			array('oauth.*','?controller=oauth&action=server','setting','Настройка серверов OAuth')
		);
	}

}
?>