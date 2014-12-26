<?php if(!controller::$error) { ?>
<p>Адрес электронной почты подтверждён. Теперь вы можете <a href="<?=core::link('user/login')?>">войти</a>.
<?php } ?>