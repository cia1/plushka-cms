<div class="faq">
	<a href="#" onclick="jQuery('#faqContainer').fadeIn();return false;" class="question">Задать вопрос</a>
<?php
if(!$this->items) echo '<p><i>Ни одного вопроса ещё не задано.</i></p>';
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
		url:'<?=core::url()?>index2.php?controller=faq&action=index',
		success:function(html) {
			if(html!='OK') {
				$('#newQuestion > div > div').html(html+'<div style="clear:both;"></div>');
				connectFAQForm();
			} else $('#newQuestion > div > div').html('Спасибо за вопрос. Мы ответим на него в ближайшее время. Вы получите ответ на указанный адрес электронной почты.');
		}
	});

	$('#faqName').focus(function() {
		if(this.value=='Ваше имя...') this.value='';
	}).blur(function() {
		if(this.value=='') this.value='Ваше имя...';
	});
	$('#faqEmail').focus(function() {
		if(this.value=='Ваш e-mail...') this.value='';
	}).blur(function() {
		if(this.value=='') this.value='Ваш e-mail...';
	});
	$('#faqQuestion').focus(function() {
		if(this.value=='Текст вопроса...') this.value='';
	}).blur(function() {
		if(this.value=='') this.value='Текст вопроса...';
	});
}
connectFAQForm();
</script>