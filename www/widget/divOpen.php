<?php class widgetDivOpen extends widget {

	public function __invoke() {
		echo '<div class="'.$this->cssClass.'">';
		return false;
	}

}