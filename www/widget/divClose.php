<?php class widgetDivClose extends widget {

	public function __invoke() {
		echo '</div>';
		return false;
	}

}