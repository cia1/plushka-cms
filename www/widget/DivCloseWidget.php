<?php
namespace plushka\widget;

class DivCloseWidget extends \plushka\core\Widget {

	public function __invoke() {
		echo '</div>';
		return false;
	}

}