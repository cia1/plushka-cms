<?php if($this->fileCreate) { ?>
	<p>Были созданы следующие файлы:</p>
	<ul>
	<?php foreach($this->fileCreate as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->fileChange) { ?>
	<p>Следующие файлы были изменены:</p>
	<ul>
	<?php foreach($this->fileChange as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->fileDelete) { ?>
	<p>Следующие файлы были удалены:</p>
	<ul>
	<?php foreach($this->fileDelete as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->tableCreate) { ?>
	<p>Созданные таблицы БД:</p>
	<ul>
	<?php foreach($this->tableCreate as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->tableDrop) { ?>
	<p>Разрушенные таблицы БД:</p>
	<ul>
	<?php foreach($this->tableDrop as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->tableModify) { ?>
	<p>Изменена структура таблиц:</p>
	<ul>
	<?php foreach($this->tableModify as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->widgetCreate) { ?>
	<p>Новые типы виджетов:</p>
	<ul>
	<?php foreach($this->widgetCreate as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->menuCreate) { ?>
	<p>Новые типы пунктов меню:</p>
	<ul>
	<?php foreach($this->menuCreate as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->menuDelete) { ?>
	<p>Типы меню удалены или изменены:</p>
	<ul>
	<?php foreach($this->menuDelete as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->rightCreate) { ?>
	<p>Созданные группы прав доступа:</p>
	<ul>
	<?php foreach($this->rightCreate as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<?php if($this->rightDelete) { ?>
	<p>Удалённые группы прав доступа:</p>
	<ul>
	<?php foreach($this->rightDelete as $item) { ?>
		<li><?=$item?></li>
	<?php } ?>
	</ul>
<?php } ?>

<p>Шаблон модуля:</p>
<textarea style="width:97%;height:300px;"><?=$this->module?></textarea>
<cite>Для создания корректно установленного модуля необходимо сделать следующее:
	<ul>
		<li>Внести информацию о модуле в файл /admin/config/_module.php;</li>
		<li>Создать конфигурационный файл /admin/module/{ИМЯ_МОДУЛЯ}.php. Содержимое файла представленно на этой странице ("шаблон мудуля").</li>
	</ul>
	Для создания переносимого модуля необходимо сделать следующее:
	<ul>
		<li>Экспортировать модуль при помощи инструментов <b>devTool</b>;</li>
		<li>Создать файлы /tmp/install.mysql.sql и /tmp/install.sqlite.sql, которые должны содержать SQL-запросы создания таблиц и загрузки необходимых данных;</li>
		<li>Создать файл /tmp/install.php (если это необходимо), который может содержать функции beforeInstall(), afterInstall(), beforeUninstall(), afterUninstall().</li>
	</ul>
</cite>