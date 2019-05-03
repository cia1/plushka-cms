<form action="<?=plushka::linkAdmin('user/user')?>" method="get" class="filter">
<input type="hidden" name="controller" value="user" />
<input type="hidden" name="action" value="user" />
<?php if(isset($_GET['_front'])) { ?><input type="hidden" name="_front" value="" /><?php } ?>
Группа: <input type="text" name="group" value="<?=$this->group?>" />&nbsp;&nbsp;&nbsp;&nbsp;
Логин: <input type="text" name="login" value="<?=$this->login?>" />&nbsp;&nbsp;&nbsp;&nbsp;
E-mail: <input type="text" name="email" value="<?=$this->email?>" />&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" value="Фильтр" />
</form>
<?php
$t=plushka::table();
$t->rowTh('Логин|Группа|Статус|Сообщение|');
foreach($this->data as $item) {
	$t->text($item['login']);
	$t->text($item['groupId'],false,'style="text-align:center;"');
	$t->text($item['status'],false,'style="text-align:center;"');
	$t->text('<a href="'.plushka::link('admin/user/message?id='.$item['id']).'"><img src="'.plushka::url().'admin/public/icon/message16.png" alt="сообщение" title="Написать сообщение" /></a> | <a href="mailto:'.$item['email'].'">'.$item['email'].'</a>');
	$t->delete('id='.$item['id'],'user');
}
$t->render();
?>
<cite>Список зарегистрированных пользователей (группы 1-199) и администраторов (группы 200-254). Если статус "не активен", то пользователь не сможет авторизоваться (войти на сайт).</cite>