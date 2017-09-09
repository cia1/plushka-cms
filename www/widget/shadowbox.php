<?php
/* Просто подключает Shadowbox, чтобы можно было в любое место вставлять всплывающие фотографии и галереи */
class widgetShadowbox extends widget {

	public function __invoke() { return true; }

	public function render() {
		echo core::js('jquery.min','defer');
		echo core::js('shadowbox/shadowbox','defer');
		echo '<link rel="stylesheet" type="text/css" href="'.core::url().'public/js/shadowbox/shadowbox.css" />';
		echo '<script>Shadowbox.init();</script>';
	}

}