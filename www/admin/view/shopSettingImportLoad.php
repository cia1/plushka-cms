<?php if(controller::$error) { ?>
	<link href="<?=core::url()?>admin/public/template/front.css" rel="stylesheet" type="text/css" />
	<p>Импорт прерван.</p>
	<?php
	return;
} ?>
<p>Не закрывайте это окно - идёт импорт.</p>
<p>Обработано <b><?=$this->total?></b> записей.</p>
<script type="text/javascript">
setTimeout(function() {
	document.location='<?=core::url().'admin/'.$this->link?>&_front';
},7000);
</script>