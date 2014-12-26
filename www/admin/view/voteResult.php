<?php foreach($this->answer as $item) {
	echo '<p>'.$item[0].': '.$item[1].'</p>';
} ?>
<p><br />Всего проголосовало <b><?=$this->total?></b> человек.</p>