<div id="commentList" itemscope itemtype="http://schema.org/Comment"><?php mComment::renderList($this->link,$this); ?></div>
<form action="<?=core::url()?>index2.php?controller=comment" method="post" id="comment">
	<input type="hidden" name="comment[link]" value="<?=$this->link?>" />
	<dl class="form">
		<?php if(!core::userGroup()) echo '<dt class="text">'.LNGName.': </dt><dd class="text"><input type="text" name="comment[name]" value="" />'; ?>
		<dt class="textarea"><?=LNGComment?>:</dt><dd class="textarea"><textarea name="comment[text]"></textarea></dd>
		<?php if(!core::userId()) { ?>
			<dt class="captcha"><?=LNGCaptcha?>:<img src="<?=core::url()?>captcha.php" alt=""></dt><dd class="captcha"><input type="text" name="comment[captcha]" /></dd>
		<?php } ?>
		<dd class="submit"><input type="submit" value="<?=LNGSend?>" class="button" /></dd>
	</dl>
</form>
<?php
echo core::js('jquery.min');
echo core::js('jquery.form');
?>
<script>
$('form#comment').ajaxForm({success:function(data) {
	if(data!='OK') {
		alert(data);
		return;
	}
	$('form#comment').remove();
	alert('<?=($this->status ? LNGCommentAdded : LNGCommentWillBePublicAfterAprove)?>');
	$("#commentList").load('<?=core::url()?>index2.php?controller=comment&action=list&link=<?=$this->link?>');
} });
</script>