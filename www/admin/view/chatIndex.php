<table style="width:100%;">
<tr><th style="width:125px;">Дата</th><th>Имя</th><th></th><th>Сообщение</th><th>IP</th></tr>
<?php
foreach($this->items as $i=>$item) {
	echo '<tr><td>'.$item['date'].'</td>
	<td>'.$item['name'].'</td>
	<td><a href="'.core::link('?controller=chat&action=ban&t='.$item['time'].'&id='.$this->id).'" onclick="return chatBan(\''.$item['time'].'\');">удалить</a></td>
	<td>'.$item['message'].'</td>
	<td>'.$item['ip'].'</td>
	</tr>';
}
?>
</table>
<script>
function chatBan(time) {
	if(confirm("Забанить этот IP на неделю?")) {
		jQuery.adminDialog.load('<?=core::url()?>admin/index2.php?controller=chat&action=ban&t='+time+'&ban&_front');
		return false;
	}
	return true;
}
</script>