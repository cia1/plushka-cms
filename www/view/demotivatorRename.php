<?php
use plushka\core\plushka;
?>
<p><?=LNGDemotivatorCreatedSo?></p>
<div style="width:40%;float:left;">
	<img src="<?=plushka::url()?>public/demotivator/<?=$this->img?>" style="max-width:300px;" /></p>
</div>
<div class="demFormRight">
	<?php $this->f->render('demotivator/rename/'.$this->url[2]); ?>
</div>