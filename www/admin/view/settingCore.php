<?php $this->form->render(); ?>
<script>
$('.method select').change(function() {
	if(this.value=='smtp') {
		$('.smtpEmail,.smtpHost,.smtpPort,.smtpUser,.smtpPassword').show();
	} else {
		$('.smtpEmail,.smtpHost,.smtpPort,.smtpUser,.smtpPassword').hide();
	}
}).change();
</script>
