<?php
/* Корзина интернет-магазина для старницы оформления заказа, не MVC-представление. */
if(!isset($_SESSION['cart'])) $_SESSION['cart']=array();
if(!count($_SESSION['cart'])) {
	echo '<p><i>'.LNGCartEmpty.'</i></p>';
	return;
}
?>
<form action="<?=core::link('checkout/cart')?>" method="post"><table class="cart">
<tr><th><?=LNGProduct?></th><th><?=LNGQuantity?></th><th><?=LNGPrice?></th><th><?=LNGCost?></th><th></th></tr>
<?php
$link=core::link('checkout?delete=');
foreach($_SESSION['cart'] as $id=>$item) {
	echo '<tr><td><a href="'.core::link('shop/category/'.$item['categoryId'].'/'.$item['alias']).'">'.$item['title'].'</a></td>
	<td><input type="text" name="checkout[quantity]['.$id.']" value="'.$item['quantity'].'" /></td>
	<td>'.$item['price'].'</td>
	<td>'.($item['quantity']*$item['price']).'</td>
	<td><a href="'.$link.$id.'" onclick="return shpCartDelete('.$id.');"><img src="'.core::url().'admin/public/icon/delete16.png" alt="'.LNGdelete.'" /></a></td></tr>';
} ?>
</table>
<input type="submit" name="checkout[submit]" value="<?=LNGChangeQuantity?>" class="button" />
</form>