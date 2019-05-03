<?php
namespace plushka\widget;

class DivOpenWidget extends \plushka\core\Widget {

	public function __invoke() {
		echo '<div class="'.$this->cssClass.'">';
		return false;
	}

}