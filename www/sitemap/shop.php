<?php $host='http://'.$_SERVER['HTTP_HOST']; ?>
<url><loc><?=$host?><?=core::link('shop/category')?></loc></url>
<?php
/* Выводит XML-карту сайта для модуля shop (интернет-магазин) */
$db=core::db();
//Категории
$db->query('SELECT id FROM shpCategory ORDER BY parentId,sort');
while($item=$db->fetch()) { ?>
	<url><loc><?=$host?><?=core::link('shop/category/'.$item[0])?></loc></url>
<?php }

//Товары
$db->query('SELECT p.categoryId,p.alias,m.time FROM shpProduct p LEFT JOIN modified m ON m.link=CONCAT("shop/category/",p.categoryId,"/",p.alias) ORDER BY p.categoryId');
$cid=0; $link='';
while($item=$db->fetch()) {
	if($cid!=$item[0]) {
		$link=core::link('shop/category/'.$item[0]).'/';
		$cid=$item[0];
	}
	?>
	<url>
		<loc><?=$host?><?=$link?><?=$item[1]?></loc>
		<?php if($item[2]) { ?>
		<lastmod><?=date('d.m.Y',$item[2])?></lastmod>
		<?php } ?>
	</url>
<?php }
?>