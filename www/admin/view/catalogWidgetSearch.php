<?php $this->f->render(); ?>
<script>
$('dl.form .id select').change(function() {
	$('#fieldList').load('<?=core::url()?>admin/index2.php?controller=catalog&action=field&id='+this.value+'&fld=<?=$this->fld?>&_front');
}).change();
</script>
<style>
#fieldList .text {width:60px;}
</style>