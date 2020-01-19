<?php
use plushka\admin\controller\ModuleController;

/**
 * @var ModuleController $this
 */
?>
<form action="" method="get">
    <input type="hidden" name="controller" value="module">
    <input type="hidden" name="action" value="uninstall">
    <input type="hidden" name="_lang" value="<?=_LANG?>">
	<?php if(isset($_GET['_front'])===true) { ?>
      <input type="hidden" name="_front" value="">
	<?php } ?>
	<?php $this->table->render(); ?>
</form>