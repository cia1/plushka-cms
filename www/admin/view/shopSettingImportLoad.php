<?php if(plushka::error()) { ?>
	<link href="<?=plushka::url()?>admin/public/template/front.css" rel="stylesheet" type="text/css" />
	<p>Импорт прерван.</p>
	<?php
	return;
} ?>
<p>Не закрывайте это окно - идёт импорт.</p>
<p>Обработано <b><?=$this->total?></b> записей.</p>
<script>
setTimeout(function() {
	document.location='<?=plushka::url().'admin/'.$this->link?>&_front';
},7000);
</script>