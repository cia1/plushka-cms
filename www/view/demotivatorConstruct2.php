<form action="<?=plushka::link('demotivator/construct')?>" method="post" onkeydown="if(event.keyCode==13) return false;" onsubmit="return prepareSize();">
	<input type="hidden" name="demotivator[step2]" value="1" />
	<input type="hidden" name="demotivator[size]" id="demSize" value="" />
	<div id="background" style="padding:<?=$this->cfg['paddingTop'].'px '.$this->cfg['paddingX'].'px '.$this->cfg['paddingBottom'].'px '.$this->cfg['paddingX']?>px;background:#<?=$this->cfg['backgroundColor']?>">
		<img src="<?=plushka::url()?>public/demotivator/tmp/<?=$this->image?>" style="margin-bottom:<?=$this->cfg['paddingY']?>px;" /><br />
		<div id="demText" style="width:<?=$this->imageWidth?>px;">
			<input type="text" name="demotivator[text][]" onkeyup="return inputKeyUp(this);" ondblclick="return changeFontSize(this);" value="Ваш текст..." id="demLine1" rel="1" style="<?=$this->defaultInputStyle?>" />
		</div>
	</div>
	<div style="clear:both;"></div>
	<input type="submit" value="<?=LNGContinue?>" class="button" />
</form>
<p><i><?=LNGToChangeFontSize?></i></p>
<script>
function inputKeyUp(self) {
	if(event.keyCode==13) {
		event.cancelBubble=true;
		event.returnValue=false;
		appendLine(self);
		return false;
	} else if(event.keyCode==8) {
		var index=$(self).attr('rel');
		if(self.value || index==1) return;
		$(self).remove();
	}
}
function appendLine(self) {
	document.demTextLastIndex++;
	var s=$('<input type="text" name="demotivator[text][]" onkeyup="return inputKeyUp(this);" ondblclick="return changeFontSize(this);" id="demLine'+document.demTextLastIndex+'" rel="'+document.demTextLastIndex+'" value="<?=LNGYourText?>" style="<?=$this->defaultInputStyle?>" />');
	$(self).after(s);
	s.select().focus();
}
function changeFontSize(self) {
	var o=$(self);
	var fs1=parseInt(o.css('fontSize'));
	var fs2=parseInt(prompt('<?=LNGFontSize?>',fs1));
	if(!fs2 || fs1==fs2 || fs2<8 || fs2>80) return;
	o.css('fontSize',fs2);
}
function prepareSize() {
	var s='';
	$('#demText input').each(function() {
		var fs=parseInt($(this).css('fontSize'));
		if(s) s+='|';
		s+=fs;
	});
	$('#demSize').val(s);
}
document.demTextLastIndex=1;
</script>