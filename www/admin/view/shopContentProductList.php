<form action="" method="post" name="productList">
<?php $this->table->render(); ?>
<?php core::widget('pagination',array('limit'=>100,'count'=>$this->paginationCount,'link'=>core::link('?controller=shopContent&action=productList&id='.$_GET['id']))); ?>
</form>