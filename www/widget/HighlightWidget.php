<?php
namespace plushka\widget;
use plushka;

class HighlightWidget extends \plushka\core\Widget {

	public function __invoke() {
		return true;
	}

	public function render() { ?>
		<link rel="stylesheet" href="<?=plushka::url()?>public/css/highlight.css">
		<?=plushka::js('highlight.pack')?>
		<script>hljs.initHighlightingOnLoad();</script>
	<?php }

} ?>