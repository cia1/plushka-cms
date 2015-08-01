<form action="<?=core::link('demotivator/construct')?>" method="post" onkeydown="if(event.keyCode==13) return false;" onsubmit="return prepareSize();">
	<input type="hidden" name="demotivator[step2]" value="1" />
	<input type="hidden" name="demotivator[size]" id="demSize" value="" />
	<div id="background" style="padding:<?=$this->cfg['paddingTop'].'px '.$this->cfg['paddingX'].'px '.$this->cfg['paddingBottom'].'px '.$this->cfg['paddingX']?>px;background:#<?=$this->cfg['backgroundColor']?>">
		<img src="<?=core::url()?>public/demotivator/tmp/<?=$this->image?>" style="margin-bottom:<?=$this->cfg['paddingY']?>px;" /><br />
		<div id="demText" style="width:<?=$this->imageWidth?>px;">
			<input type="text" name="demotivator[text][]" onkeyup="return inputKeyUp(this);" ondblclick="return changeFontSize(this);" value="Ваш текст..." id="demLine1" rel="1" style="<?=$this->defaultInputStyle?>" />
		</div>
	</div>
	<div style="clear:both;"></div>
	<input type="submit" value="Продолжить" class="button" />
</form>
<p><i>Для изменения размера шрифта дважы щёлкните мышкой на строке текста. Для добавления новой строки нажмите клавишу Enter.</i></p>
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
	var s=$('<input type="text" name="demotivator[text][]" onkeyup="return inputKeyUp(this);" ondblclick="return changeFontSize(this);" id="demLine'+document.demTextLastIndex+'" rel="'+document.demTextLastIndex+'" value="Ваш текст..." style="<?=$this->defaultInputStyle?>" />');
	$(self).after(s);
	s.select().focus();
}
function changeFontSize(self) {
	var o=$(self);
	var fs1=parseInt(o.css('fontSize'));
	var fs2=parseInt(prompt('Размер шрифта',fs1));
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