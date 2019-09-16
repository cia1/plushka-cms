<?php
use plushka\admin\core\plushka;
?>
<form action="" method="post" name="productList">
<?php $this->table->render(); ?>
<?php plushka::widget('pagination',array('limit'=>100,'count'=>$this->paginationCount,'link'=>plushka::link('admin/shopContent/productList?id='.$_GET['id']))); ?>
</form>