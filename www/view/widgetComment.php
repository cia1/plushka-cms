<div id="commentList" itemscope itemtype="http://schema.org/Comment"><?php mComment::renderList($this->link,$this); ?></div>
<form action="<?=core::url()?>index2.php?controller=comment" method="post" id="comment">
	<input type="hidden" name="comment[link]" value="<?=$this->link?>" />
	<dl class="form">
		<?php if(!core::userGroup()) echo '<dt class="text">Имя: </dt><dd class="text"><input type="text" name="comment[name]" value="" />'; ?>
		<dt class="textarea">Комментарий:</dt><dd class="textarea"><textarea name="comment[text]"></textarea></dd>
		<?php if(!core::userId()) { ?>
			<dt class="captcha">Текст на картинке:<img src="<?=core::url()?>captcha.php" alt="каптча" title="Введите текст на картинке"></dt><dd class="captcha"><input type="text" name="comment[captcha]" /></dd>
		<?php } ?>
		<dd class="submit"><input type="submit" value="Отправить" class="button" /></dd>
	</dl>
</form>
<?php
echo core::script('jquery.min');
echo core::script('jquery.form');
?>
<script>
$('form#comment').ajaxForm({success:function(data) {
	if(data!='OK') {
		alert(data);
		return;
	}
	$('form#comment').remove();
	alert('<?=($this->status ? 'Комментарий добавлен' : 'Комментарий будет опубликован после проверки администратором')?>');
	$("#commentList").load('<?=core::url()?>index2.php?controller=comment&action=list&link=<?=$link?>');
} });
</script>