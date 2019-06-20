<?php
namespace plushka\widget;
use plushka\core\Widget;

class DivOpenWidget extends Widget {

	public function __invoke() {
		echo '<div class="'.$this->cssClass.'">';
		return false;
	}

}
