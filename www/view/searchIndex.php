<form action="<?=core::link('search')?>">
<dl class="form">
	<dt class="text keyword">Поиск:</dt>
	<dd class="text keyword"><input type="text" name="keyword" value="<?=$this->keyword?>" /></dd>
	<input type="submit" value="Найти" class="button" />
</dl>
</form>
<?php
if($this->keyword) {
	echo '<div style="clear:both;"></div><h2>Результаты поиска</h2>';
	core::hook('search',$this->keyword);
}
?>