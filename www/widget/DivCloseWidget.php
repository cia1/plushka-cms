<?php
namespace plushka\widget;
use plushka\core\Widget;

class DivCloseWidget extends Widget {

	public function __invoke() {
		echo '</div>';
		return false;
	}

}
