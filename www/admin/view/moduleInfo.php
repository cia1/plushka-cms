<?php
use plushka\admin\core\plushka;

if(plushka::error()) return;
?>
<?php if($this->moduleExists) { ?>
	<p><b>ВНИМАНИЕ! </b>Модуль &laquo;<?=$this->module['name']?>&raquo; (версия <?=$this->module['version']?>) уже установлен. Если вы продолжите установку, то модуль будет обновлён.</p>
	<p></p><p></p>
<?php } ?>

<p><b>Модуль</b>: <?=$this->module['name'] ?></p>
<?php if($this->module['version']) echo '<p><b>Версия</b>: '.$this->module['version'].'</p>'; ?>
<?php if($this->module['url']) echo '<p><b>URL</b>: '.$this->module['url'].'</p>'; ?>
<?php if($this->module['author']) echo '<p><b>Автор</b>: '.$this->module['author'].'</p>'; ?>
<?php if($this->module['description']) echo '<p><b>Описание</b>:<br />'.$this->module['description'].'</p>'; ?>
<form method="get" action="<?=plushka::linkAdmin('module/installStart')?>">
<input type="hidden" name="controller" value="module" />
<input type="hidden" name="action" value="installStart" />
<input type="hidden" name="_front" value="" />
<input type="submit" value="  Установить модуль  " />
</form>