<div id="slider<?=$this->index?>" class="noOneFreeHorizontal">
	<a href="#" class="prev">PREV</a>
	<div class="sliderContainer">
	<?php
	$url=core::url().'public/slider/'.$this->options['id'].'.';
	foreach($this->cfg['data'] as $item) { ?>
		<div class="item">
		<img src="<?=$url.$item['image']?>" class="bg" alt="" />
		<div class="inner"><?=$item['html']?></div>
		</div>
	<?php }
	?>
	</div>
	<a href="#" class="next">NEXT</a>
</div>
<div style="clear:both;"></div>
<script type="text/javascript">
	$('#slider<?=$this->index?>').sliderHorizontal({
		prevButton:'.prev',
		nextButton:'.next'
	});
</script>