<?php
$f=core::form();
$f->listBox('menuId','Выберите меню','SELECT id,title FROM menu',$this->menuId,'(создать новое меню)','onclick="selectMenu(this.value);"');
$f->submit('Продолжить');
$f->render();
?>
<cite>Выберите из списка меню, которое хотите отобразить на сайте.</cite>
<script type="text/javascript">
function selectMenu(value) {
	if(value=='') document.location="<?=$this->newItemLink?>";
}

</script>