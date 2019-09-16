<?php
use plushka\admin\core\plushka;
?>
<form action="" method="get">
<input type="hidden" name="controller" value="log" />
<input type="hidden" name="action" value="log" />
<input type="hidden" name="id" value="<?=$_GET['id']?>" />
<input type="hidden" name="_front" />
<input type="hidden" name="_lang" value="<?=_LANG?>" />
<input type="text" name="keyword" value="<?=$this->keyword?>" placeholder="�����..." />
<input type="submit" value="�����" />
</form>
<?php $this->table->render(); ?>
<?php plushka::widget('pagination',array('count'=>$this->count,'limit'=>LOG_LIMIT_ON_PAGE)); ?>