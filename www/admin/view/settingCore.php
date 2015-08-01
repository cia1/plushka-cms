<?php $this->form->render() ?>
<script>
$('.method select').change(function() {
	if(this.value=='smtp') {
		$('.smtpSSL,.smtpEmail,.smtpHost,.smtpUser,.smtpPassword').show();
	} else {
		$('.smtpSSL,.smtpEmail,.smtpHost,.smtpUser,.smtpPassword').hide();
	}
}).change();
</script>