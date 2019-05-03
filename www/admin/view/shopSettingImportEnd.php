<?php if(plushka::error()) { ?>
	<link href="<?=plushka::url()?>admin/public/template/front.css" rel="stylesheet" type="text/css" />
	<p>Импорт прерван.</p>
	<?php
	return;
} ?>
<p align="center"><b>ИМПОРТ ЗАВЕРШЁН!</b></p>
<p>Обработано товаров: <b><?=$this->rowCount?></b>.</p>
<p>Удалено товаров: <b><?=$this->deleteCount?></b>.</p>
<?php if($this->log) echo '<h4>Лог ошибок</h4>'.$this->log; ?>