<table>
<?php foreach($this->answer as $item) {
	echo '<tr><td>'.$item[0].':</td><td>'.$item[1].'</td></tr>';
} ?>
</table>
<p>Всего проголосовало <b><?=$this->total?></b> человек.</p>