<div id="commentList" itemscope itemtype="http://schema.org/Comment"><?php \plushka\model\mComment::renderList($this->link,$this); ?></div>
<form action="<?=plushka::url()?>index2.php?controller=comment" method="post" id="comment">
	<input type="hidden" name="comment[link]" value="<?=$this->link?>" />
	<dl class="form">
		<?php if(!plushka::userGroup()) echo '<dt class="text">'.LNGName.': </dt><dd class="text"><input type="text" name="comment[name]" value="" />'; ?>
		<dt class="textarea"><?=LNGComment?>:</dt><dd class="textarea"><textarea name="comment[text]"></textarea></dd>
		<?php if(!plushka::userId()) { ?>
			<dt class="captcha"><?=LNGCaptcha?>:<img src="<?=plushka::url()?>captcha.php" alt=""></dt><dd class="captcha"><input type="text" name="comment[captcha]" /></dd>
		<?php } ?>
		<dd class="submit"><input type="submit" value="<?=LNGSend?>" class="button" /></dd>
	</dl>
</form>
<script>
	if(document._lang==undefined) document._lang=new Array();
	document._lang['commentMessage']='<?=($this->status ? LNGCommentAdded : LNGCommentWillBePublicAfterAprove)?>';
</script>
<?php
echo plushka::js('jquery.min','defer');
echo plushka::js('jquery.form','defer');
echo plushka::js('comment','defer');