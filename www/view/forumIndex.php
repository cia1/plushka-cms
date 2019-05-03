<div id="forum">
<?php
$cnt=count($this->data)-1;
foreach($this->data as $i=>$item) {
	if(!$i) $item['first']=true; elseif($i==$cnt) $item['last']=true;
	?>
	<div class="item">
		<?php $this->admin($item); ?>
		<p><a href="<?=plushka::link('forum/'.$item['id'])?>"><?=$item['title']?></a></p>
	</div>
<?php } ?>
</div>