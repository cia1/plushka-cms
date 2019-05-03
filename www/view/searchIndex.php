<form action="<?=plushka::link('search')?>">
<dl class="form">
	<dt class="text keyword"><?=LNGSearch?>:</dt>
	<dd class="text keyword"><input type="text" name="keyword" value="<?=$this->keyword?>" /></dd>
	<input type="submit" value="<?=LNGFind?>" class="button" />
</dl>
</form>
<?php
if($this->keyword) {
	echo '<div style="clear:both;"></div><h2>'.LNGSerachResults.'</h2>';
	plushka::hook('search',$this->keyword);
}
?>