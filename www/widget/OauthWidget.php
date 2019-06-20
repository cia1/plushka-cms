<?php
namespace plushka\widget;
use plushka;
use plushka\core\Widget;

/* Кнопки входа через социальные сети */
class OauthWidget extends Widget {

	public function __invoke() {
		if(plushka::userGroup()) return false;
		$this->data=plushka::config('oauth');
		unset($this->data['userGroup']);
		return 'Oauth';
	}

	public function adminLink(): array {
		return array(
			array('oauth.*','?controller=oauth&action=server','setting','Настройка серверов OAuth')
		);
	}

}
