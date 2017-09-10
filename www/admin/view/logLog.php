<?php $this->table->render(); ?>
<?php core::widget('pagination',array('count'=>$this->count,'limit'=>LOG_LIMIT_ON_PAGE)); ?>