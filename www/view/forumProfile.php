<?php if($this->avatar) { ?>
	<img src="<?=$this->avatar?>?t=<?=time()?>" />
<?php } ?>
<?php $this->form->render(); ?>