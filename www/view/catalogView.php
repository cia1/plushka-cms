<div id="catalogView">

<?php foreach($this->data as $i=>$item) {
	if(!$item['value']) continue;
	echo '<div class="item item'.$i.'">';
	if($item['title']) echo '<label>'.$item['title'].'</label>: ';
	\plushka\model\catalog::render($item);
	echo '</div>';
}
?>

</div>