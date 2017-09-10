<?php if($this->noTableMySQL) { ?>
	<p>Следующие таблицы отсутствуют в MySQL: <?=implode(',<br />',$this->noTableMySQL)?>.</p>
<?php } ?>
<?php if($this->noTableSQLite) { ?>
	<p>Следующие таблицы отсутствуют в SQLite: <?=implode(',<br />',$this->noTableSQLite)?>.</p>
<?php } ?>
<?php if($this->noFieldMySQL) { ?>
	<p>Следующие поля отсутствуют в MySQL:
	<?php foreach($this->noFieldMySQL as $item) { ?>
		<br /><?=implode('.',$item)?>
	<?php } ?>
	</p>
<?php } ?>
<?php if($this->noFieldSQLite) { ?>
	<p>Следующие поля отсутствуют в SQLite:
	<?php foreach($this->noFieldSQLite as $item) { ?>
		<br /><?=implode('.',$item)?>
	<?php } ?>
	</p>
<?php } ?>
<?php if(!$this->noTableMySQL && !$this->noTableSQLite && !$this->noFieldMySQL && !$this->noFieldSQLite) { ?>
	<p>Структура баз данных идентична. Типы полей, а также индексы не проверялись.</p>
<?php } ?>
