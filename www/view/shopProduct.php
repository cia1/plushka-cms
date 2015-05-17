<div class="product">

<div class="description"><?=$this->product['text2']?></div>
<div class="price">Цена: <span><?=$this->product['price']?></span> руб.</div>
<?php if($this->product['variantCount']) {
	echo '<div id="variant">Модификации товара: ';
	foreach($this->product['variant'] as $item) {
		$feature='';
		if($item['feature']) foreach($item['feature'] as $f) {
			if($feature) $feature.="\n";
			$feature.=$f['title'].': '.str_replace('"','&quot;',$f['value']);
		}
		echo '<span><a href="#" onclick="return false;" title="'.$feature.'">'.$item['title'].'</a></span> ';
	}
	echo '</div>';
} ?>
<a href="<?=core::url().'public/shop-product/'.$this->product['mainImage']?>" rel="shadowbox[gallery]"><img src="<?=core::url().'public/shop-product/'.$this->product['mainImage']?>" alt="<?=$this->product['title']?>" class="mainImage" /></a>
<div class="otherImage">
<?php foreach($this->product['image'] as $item) echo '<a href="'.core::url().'public/shop-product/'.$item.'" rel="shadowbox[gallery]"><img src="'.core::url().'public/shop-product/_'.$item.'" alt="'.$this->product['title'].'" /></a>'; ?>
</div>
<div style="clear:both;"></div>

<p>Производитель: <?=$this->product['brand']?></p>
<?php if($this->product['feature']) {
	echo '<h2>Характеристики</h2>';
	foreach($this->product['feature'] as $item) {
		echo '<p>'.$item['title'].': '.$item['value'].'</p>';
	}
} ?>
<form action="<?=core::url()?>index2.php?controller=shop&amp;action=addToCart" onsubmit="return addToCart(this);" method="post" class="addToCart">
<input type="hidden" name="shop[id]" value="<?=$this->product['id']?>" />
<input type="hidden" name="shop[quantity]" value="1" />
<button type="submit" value="В корзину" class="button">В корзину</button>
</form>
</div>
<script type="text/javascript">Shadowbox.init();</script>
<script type="text/javascript">
function addToCart(form) {
	var data={};
	jQuery('button',form).attr('disabled','disabled');
	jQuery('input,select',form).each(function(n,el) {
		data[el.name]=el.value;
	});
	jQuery.post(form.action,data,function(answer) {
		alert(answer);
		window.location=window.location;
	});
	return false;
}
</script>