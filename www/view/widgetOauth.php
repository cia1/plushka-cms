<?php if(!$this->data) {
	echo '<p style="font-style:italic;">'.LNGNoOneServers.'</p>';
	return;
} ?>
<?php foreach($this->data as $id=>$item) { ?>
	<a href="<?=plushka::link('oauth/'.$id)?>"><img src="/public/<?=$id?>.png" alt="<?=$id?>" /></a>
<?php } ?>