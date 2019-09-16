<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

class HighlightWidget extends Widget {

	public function __invoke() {
		return true;
	}

	public function render(): void { ?>
		<link rel="stylesheet" href="<?=plushka::url()?>public/css/highlight.css">
		<?=plushka::js('highlight.pack')?>
		<script>hljs.initHighlightingOnLoad();</script>
	<?php }

}
