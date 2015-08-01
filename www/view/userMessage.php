<?php if(!$this->items) echo '<p align="center"><br /><i>Сообщений нет.</i></p>'; else {

echo '<p align="center"><i>';
if(!$this->newCount) echo 'Новых сообщений нет.'; else echo 'У вас '.$this->newCount.' новых сообщений';
echo '</i></center></p>';
?>
<table>
<?php foreach($this->items as $index=>$item) {
	echo '<div class="messageItem row'.$item['direct'].($item['isNew'] ? ' newMessage' : '').'"><p class="title">'.$item['subject'].'<span>'.date('d.m.Y H:i',$item['date']).'</span></p>
	<div class="content">'.$item['message'].'</div>';
	if($item['direct']=='1') echo '<p class="control"><a href="#" onclick="return showAnswerForm('.$index.');" class="button">Ответить</a></p>';
	echo '</div>';
	if($item['direct']=='1') {
		echo '<div class="answer" id="answer'.$index.'" style="display:none;">';
		$f=core::form();
		$f->hidden('replyTo',$item['id']);
		$f->textarea('message','');
		$f->submit('Отправить');
		$f->render();
		echo '</div>';
	}
} ?>
</table>
<script>
function showAnswerForm(index) {
	var o=document.getElementById('answer'+index);
	if(!o.style.display) o.style.display='none'; else {
		o.style.display='';
		o.getElementsByTagName('textarea')[0].focus();
	}
	return false;
}
</script>

<?php } ?>