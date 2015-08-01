$(document).ready(function() {
	$('#catalogType').change(function() {
		if(this.value=='list') {
			$('dl.form .value').show();
			$('dl.form .width,dl.form .height,dl.form .widthSize,dl.form .heightSize,dl.form .thumbnail,').hide();
		} else if(this.value=='image') {
			$('dl.form .value,dl.form .thumbnail,dl.form .thumbWidth,dl.form .thumbHeight,dl.form .thumbWidthSize,dl.form .thumbHeightSize').hide();
			$('dl.form .width,dl.form .height').show();
			$('#width,#height').change();
		} else if(this.value=='gallery') {
			$('dl.form .value').hide();
			$('dl.form .width,dl.form .height,dl.form .thumbnail').show();
			if(document.getElementById('width').value) $('dl.form .widthSize').show(); else $('dl.form .widthSize').hide();
			if(document.getElementById('height').value) $('dl.form .heightSize').show(); else $('dl.form .heightSize').hide();
			if(document.getElementById('thumbnail').checked) {
				$('dl.form .thumbWidth,dl.form .thumbHeight').show();
				if(document.getElementById('thumbWidth').value) $('dl.form .thumbWidthSize').show(); else $('dl.form .thumbWidthSize').hide();
				if(document.getElementById('thumbHeight').value) $('dl.form .thumbHeightSize').show(); else $('dl.form .thumbHeightSize').hide();
			} else {
				$('dl.form .thumbWidth,dl.form .thumbHeight,dl.form .thumbWidthSize,dl.form .thumbHeightSize').hide();
			}
		} else {
			$('dl.form .value,dl.form .width,dl.form .height,dl.form .widthSize,dl.form .heightSize,dl.form .thumbnail,dl.form .thumbWidth,dl.form .thumbHeight,dl.form .thumbWidthSize,dl.form .thumbHeightSize').hide();
		}
	}).change();
	$('#width').change(function() {
		if(this.value) $('dl.form .widthSize').show(); else $('dl.form .widthSize').hide();
	});
	$('#height').change(function() {
		if(this.value) $('dl.form .heightSize').show(); else $('dl.form .heightSize').hide();
	});
	$('#thumbnail').change(function() {
		if(this.checked) {
			$('dl.form .thumbWidth,dl.form .thumbHeight,dl.form .thumbWidthSize,dl.form .thumbHeightSize').show();
		} else {
			$('dl.form .thumbWidth,dl.form .thumbHeight,dl.form .thumbWidthSize,dl.form .thumbHeightSize').hide();
		}
	});
	$('#thumbWidth').change(function() {
		if(this.value) $('dl.form .thumbWidthSize').show(); else $('dl.form .thumbWidthSize').hide();
	});
	$('#thumbHeight').change(function() {
		if(this.value) $('dl.form .thumbHeightSize').show(); else $('dl.form .thumbHeightSize').hide();
	});

	$('dl.form dd.gallery a').click(function() {
		if(!confirm('Подтвердите удаление изображения')) return false;
		var link=this.href;

		var index=parseInt(this.href.substr(this.href.indexOf('index=')+6,2));
		var tagA=$(this).parent();
		$(this).remove();
		tagA=$('a',tagA);
		for(var i=index;i<tagA.length;i++) {
			tagA[i].href=tagA[i].href.substr(0,tagA[i].href.indexOf('index=')+6)+i;
		}
		$.get(link);
		return false;
	});
});