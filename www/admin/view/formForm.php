<?php $this->f->render(); ?>
<script>
$('.emailSource input').click(function() {
	if(this.value=='other') $('.email').show(); else $('.email').hide();
	if(this.value=='no') $('.subject').hide(); else $('.subject').show();
});
$('#script').keyup(function() {
	if(!this.value) $('#scriptComment').html('');
	else $('#scriptComment').html('Должен существовать по крайней мере один из файлов, возвращающих true/false<br /><b><?=str_replace('\\','/',core::path())?>admin/data/'+this.value+'Before.php</b> - вызывается до стандартной валидации<br />'+
	'<b><?=str_replace('\\','/',core::path())?>admin/data/'+this.value+'Aftef.php</b> - вызывается после стандартной валидации, до отправки письма.<br />');
});
</script>
<?php if(!$this->showEmail) echo '<style>.email {display:none;}</style>'; ?>
<?php if(!$this->showSubject) echo '<style>.subject {display:none;}</style>'; ?>