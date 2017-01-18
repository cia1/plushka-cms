<?php class widgetHighlight extends widget {

	public function __invoke() {
		return true;
	}

	public function render() { ?>
		<link rel="stylesheet" href="<?=core::url()?>public/css/highlight.css">
		<?=core::js('highlight.pack')?>
		<script>hljs.initHighlightingOnLoad();</script>
	<?php }

} ?>