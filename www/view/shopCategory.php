<div class="category">
<?php
if($this->category['text1']) echo '<div class="text1">',$this->category['text1'],'</div>';
foreach($this->categoryList as $item) { ?>
	<div class="item">
	<a href="<?=$item['link']?>">
	<?php if($item['image']) echo '<img src="'.$item['image'].'" alt="'.$item['title'].'" />'; ?>
	<div class="title"><?=$item['title']?></div>
	</a>
	</div>
<?php } ?>
</div>
<?php if($this->productList) {
	if($this->sort) echo '<p class="sort"><?=LNGOrder?>: '.$this->sort.'</p>';
	echo '<div class="product">';
	foreach($this->productList as $item) { ?>
		<div class="item" itemscope itemtype="http://schema.org/Offer">
		<div class="title" itemprop="name"><?='<a href="'.$item['link'].'" itemprop="url">'.$item['title'].'</a>'?></div>
		<a href="<?=$item['link']?>"><img src="<?=core::url()?>public/shop-product/_<?=$item['mainImage']?>" alt="<?=$item['title']?>" itemprop="image" /></a>
		<?=LNGPrice?>: <span itemprop="price"><?=$item['price']?></span>
	</div>
	<?php }
	echo '</div>';
}
?>