<?php
use plushka\admin\core\plushka;
?>
<br /><p style="font-size:30px;font-weight:bold;">404</p>
<p>Запрошенная страница <b>http://<?=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']?></b> не существует.</p>
<p><br /><b><a href="<?=plushka::url()?>admin">На главную</a></b></p>