$(document).ready(function() {
	$('#catalogType').change(function() {
		if(this.value=='list') {
			$('dl.form .value').show();
			$('dl.form .width,dl.form .height,dl.form .widthSize,dl.form .heightSize').hide();
		} else if(this.value=='image' || this.value=='gallery') {
			$('dl.form .value').hide();
			$('dl.form .width,dl.form .height').show();
			$('#width,#height').change();
		} else {
			$('dl.form .value,dl.form .width,dl.form .height,dl.form .widthSize,dl.form .heightSize').hide();
		}
	}).change();
	$('#width').change(function() {
		if(this.value) $('dl.form .widthSize').show(); else $('dl.form .widthSize').hide();
	});
	$('#height').change(function() {
		if(this.value) $('dl.form .heightSize').show(); else $('dl.form .heightSize').hide();
	});

	$('dl.form dd.gallery a').click(function() {
		if(!confirm('Подтвердите удаление изображения')) return false;
		var link=this.href;
		$(this).remove();
		$.get(link);
		return false;
	});
});