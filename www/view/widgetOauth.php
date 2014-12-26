<?php if(!$this->data) {
	echo '<p style="font-style:italic;">Ни одного сервера OAuth не настроено.</p>';
	return;
} ?>
<p class="title">Войти через:</p>
<?php foreach($this->data as $id=>$item) { ?>
	<a href="<?=core::link('oauth/'.$id)?>"><img src="/public/<?=$id?>.png" alt="<?=$id?>" /></a>
<?php } ?>
