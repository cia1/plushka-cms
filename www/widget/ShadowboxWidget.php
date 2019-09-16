<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

class ShadowboxWidget extends Widget {

	public function __invoke() { return true; }

	public function render($view): void {
		echo plushka::js('jquery.min');
		echo plushka::js('shadowbox/shadowbox');
		echo '<link rel="stylesheet" type="text/css" href="'.plushka::url().'public/js/shadowbox/shadowbox.css" />';
		echo '<script>Shadowbox.init();</script>';
	}

}
