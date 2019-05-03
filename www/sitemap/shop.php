<?php $host='http://'.$_SERVER['HTTP_HOST']; ?>
<url><loc><?=$host?><?=plushka::link('shop/category')?></loc></url>
<?php
$db=plushka::db();
$db->query('SELECT id FROM shp_category ORDER BY parentId,sort');
while($item=$db->fetch()) { ?>
	<url><loc><?=$host?><?=plushka::link('shop/category/'.$item[0])?></loc></url>
<?php }

//Товары
$db->query('SELECT p.categoryId,p.alias,m.time FROM shp_product p LEFT JOIN modified m ON m.link=CONCAT("shop/category/",p.categoryId,"/",p.alias) ORDER BY p.categoryId');
$cid=0; $link='';
while($item=$db->fetch()) {
	if($cid!=$item[0]) {
		$link=plushka::link('shop/category/'.$item[0]).'/';
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