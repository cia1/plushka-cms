<?php
namespace plushka\widget;
use plushka;

class ShadowboxWidget extends \plushka\core\Widget {

	public function __invoke() { return true; }

	public function render($view) {
		echo plushka::js('jquery.min');
		echo plushka::js('shadowbox/shadowbox');
		echo '<link rel="stylesheet" type="text/css" href="'.plushka::url().'public/js/shadowbox/shadowbox.css" />';
		echo '<script>Shadowbox.init();</script>';
	}

}