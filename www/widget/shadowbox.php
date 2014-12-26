<?php
/* Просто подключает Shadowbox, чтобы можно было в любое место вставлять всплывающие фотографии и галереи */
class widgetShadowbox extends widget {

	public function action() { return true; }

	public function render() {
		echo core::script('jquery.min');
		echo core::script('shadowbox/shadowbox');
		echo '<link rel="stylesheet" type="text/css" href="'.core::url().'public/js/shadowbox/shadowbox.css" />';
		echo '<script type="text/javascript">Shadowbox.init();</script>';
	}

}
?>