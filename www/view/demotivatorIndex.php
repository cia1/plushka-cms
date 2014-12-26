<p>Прими участие: создай свой демотиватор <a href="<?=core::link('demotivator/construct')?>">тут</a>!</p>
<div id="demList">
<?php foreach($this->items as $item) { ?>
	<div class="item">
		<?php $this->admin($item); ?>
		<a href="<?=$item['image']?>" data-rel="shadowbox"><img src="<?=$item['image']?>" alt="<?=$item['title']?>" title="<?=$item['title']?>" /></a>
		<p style="text-align:right;padding-right:10px;"><small><i><?=$item['date']?></i></small></p>
		<b><?=$item['title']?></b><br />
		<?php if($item['author']) echo '<p>Автор: '.$item['author'].'</p>'; ?>
		<div style="clear:both;"></div>
	</div>
<?php } ?>
</div>
<script type="text/javascript">
Shadowbox.init();
</script>