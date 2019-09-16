<?php
use plushka\admin\core\plushka;
?>
<p>Песочница: <b><?=($this->sandbox ? 'включена' : 'выключена')?></b> (<a href="<?=plushka::link('admin/payment/sundbox')?>"><?=($this->sandbox ? 'выключить' : 'включить')?></a>)</p><p style="font-style:italic;font-size:0.9em;">* В режиме песочницы вместо реальных платежей будет проведена имитация. Поддержка этого режима зависит от конкретного метода платежа. Также этот режим работает ТОЛЬКО для администраторов.</p>
<?php foreach($this->payment as $item) { ?>
	<h4><?=$item['title']?></h4>
	<?php if(is_object($item['content'])) $item['content']->render(); else echo $item['content']; ?>
	<p style="clear:both;"><br /></p>
<?php } ?>
<cite>Сумма платежа будет умножена на <b>курс метода платежа</b>, таким образом можно задавать комиссию для разных видов платежа.</cite>