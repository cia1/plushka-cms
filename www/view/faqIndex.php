<?php
use plushka\core\plushka;
?>
<div class="faq">
	<a href="#" onclick="jQuery('#faqContainer').fadeIn();return false;" class="question"><?=LNGAskQuestion?></a>
<?php
if(!$this->items) echo '<p><i>'.LNGNoOneQuestion.'</i></p>';
else foreach($this->items as $item) { ?>
	<div class="item">
	<p class="question"><?=$item['question']?></p>
	<p class="status"><?=$item['name']?></p>
	<p class="answer"><?=nl2br($item['answer'])?></p>
	</div>
<?php }
?>
	<div id="faqContainer">
		<div id="background"></div>
		<div id="newQuestion"><div><div>
			<?php $this->newQuestion->render(); ?>
			<div style="clear:both;"></div>
		</div></div></div>
	</div>
</div>
<script>
function connectFAQForm() {
	jQuery('#newQuestion form').ajaxForm({
		url:'<?=plushka::url()?>index2.php?controller=faq&action=index',
		success:function(html) {
			if(html!='OK') {
				$('#newQuestion > div > div').html(html+'<div style="clear:both;"></div>');
				connectFAQForm();
			} else $('#newQuestion > div > div').html('<?=LNGThankyouForQuestion?>');
		}
	});

	$('#faqName').focus(function() {
		if(this.value=='<?=LNGYourName?>') this.value='';
	}).blur(function() {
		if(this.value=='') this.value='<?=LNGYourName?>';
	});
	$('#faqEmail').focus(function() {
		if(this.value=='<?=LNGYourEmail?>') this.value='';
	}).blur(function() {
		if(this.value=='') this.value='<?=LNGYourEmail?>';
	});
	$('#faqQuestion').focus(function() {
		if(this.value=='<?=LNGQuestionText?>') this.value='';
	}).blur(function() {
		if(this.value=='') this.value='<?=LNGQuestionText?>';
	});
}
connectFAQForm();
</script>