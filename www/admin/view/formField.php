<?php $this->f->render(); ?>
<script type="text/javascript">
	$('.htmlType select').change(function() {
		if(this.value=='select' || this.value=='radio') $('.value').show(); else $('.value').hide();
		if(this.value=='file') {
			$('.fileType').show();
			$('.defaultValue').hide();
			$('.required').show();
		} else if(this.value=='captcha') {
			$('.fileType').hide();
			$('.defaultValue').hide();
			$('.required').hide();
		} else {
			$('.fileType').hide();
			$('.defaultValue').show();
			$('.required').show();
		}
	});
</script>
<style type="text/css">
<?php
if(!$this->value) echo '.value {display:none;} ';
if(!$this->fileType) echo '.fileType {display:none;} ';
else echo '.defaultValue {display:none;} ';
if(!$this->required) echo '.required {display:none;}';
if(!$this->defaultValue) echo '.defaultValue {display:none;}';
?>
</style>