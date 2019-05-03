<div id="_cart" style="position:relative;"><?php plushka::import('view/cart'); ?></div>

<?php
if(count($_SESSION['cart'])) { ?>
	<h2><?=LNGMakeRequest?></h2>
	<?php $this->form->render('checkout/checkout'); ?>
<?php } ?>
<script>
function shpCartDelete(id) {
	if(!confirm('<?=LNGConfirmProductDeliting?>')) return false;
	var cart=$('#_cart');
	cart.html('<img src="<?=plushka::url()?>public/icon/loadingBig.gif" id="_load" style="display:block;margin:'+(cart.height()/2-10)+'px auto;" />');
	$.get('<?=plushka::url()?>index2.php?controller=checkout&action=cart&delete='+id,function(html) {
		cart.html(html);
	});
	return false;
}

function cartFormSubmit() {
	var data={};
	$('table.cart input,table.cart select',this).each(function(n,el) {
		data[el.name]=el.value;
	});
	var cart=$('#_cart');
	cart.html('<img src="<?=plushka::url()?>public/icon/loadingBig.gif" id="_load" style="display:block;margin:'+(cart.height()/2-10)+'px auto;" />');
	$.post('<?=plushka::url()?>index2.php?controller=checkout&action=cart',data,function(html) {
		cart.html(html);
		$('form',cart).submit(cartFormSubmit);
	});
	return false;
}
$('#_cart form').submit(cartFormSubmit);
</script>