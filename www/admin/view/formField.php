<?php
use plushka\admin\controller\FormController;

/**
 * @var FormController $this
 */
$this->form->render(); ?>
<script>
    $('.htmlType select').change(function () {
        if (this.value === 'select' || this.value === 'radio') $('.value').show(); else $('.value').hide();
        if (this.value === 'file') {
            $('.fileType').show();
            $('.defaultValue').hide();
            $('.required').show();
        } else if (this.value === 'captcha') {
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
<style>
    <?php
		if($this->value===false) echo '.value {display:none;} ';
		if($this->fileType===false) echo '.fileType {display:none;} '; else echo '.defaultValue {display:none;} ';
		if($this->required===false) echo '.required {display:none;}';
		if($this->defaultValue===false) echo '.defaultValue {display:none;}';
		?>
</style>