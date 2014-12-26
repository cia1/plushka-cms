<div class="tab">
	<fieldset>
		<legend>База данных</legend>
		<p style="font-style:italic;font-size:0.9em;">
			СУБД: <b><?=$this->config['dbDriver']?></b>
			<?php if($this->config['dbDriver']=='mysql') echo ', имя базы данных: <b>',$this->config['mysqlDatabase'],'</b>'; ?>
		</p>
		<h3 style="clear:both;">Сравнение структуры СУБД</h3>
		<?php $this->formDbCompare->render('devTool/compare'); ?>
		<div style="clear:both;"></div>
		<hr />
		<h3>Копирование данных</h3>
		<?php $this->formDbMove->render('devTool/copy'); ?>
		<div style="clear:both;"></div>
		<hr />
		<h3>Очистка контента</h3>
		<a href="<?=core::link('devTool/setting')?>">Настройки</a>
		<?php $this->formClear->render('devTool/clear','onsubmit="return confirm(\'Будет удалён весь контент как из базы данных, так и из файловой системы. Подтвердите операцию.\');"'); ?>
		<cite>Будет удалён весь контент сайта, включая главную страницу, поэтому после удаления вы увидите 404-ю ошибку.</cite>
		<div style="clear:both;"></div>
		<?php if($this->config['dbDriver']=='mysql') { ?>
			<hr />
			<h3>Бэкап</h3>
			<?php if($this->backupDate) { ?>
				<p>Бэкап создан <?=date('d.m.Y H:i',$this->backupDate); ?>. <a href="<?=core::url()?>tmp/dump.sql">Скачайте</a> и затем <a href="<?=core::link('?controller=devTool&action=backupDelete')?>">удалите</a> файл.</p>
			<?php } ?>
			<a href="<?=core::link('?controller=devTool&action=backupCreate')?>">Создать бэкап MySQL</a>
			<cite>Будет попытка выполнить команду mysqldump.</cite>
		<?php } ?>
	</fieldset>
	<fieldset>
		<legend>Модуль</legend>
		<?php $this->formExport->render('devTool/export'); ?>
		<cite>Модуль будет экспортирован в директорий /tmp.</cite>
	</fieldset>
	<fieldset>
		<legend>Снимок движка</legend>
		<?php if($this->imageDate) { ?>
			<p>Снимок создан <?=date('d.m.Y H:i:s',$this->imageDate)?>.</p><br />
			<div style="width:50%;float:right;"><?php $this->formImageCompare->render('devTool/imageCompare'); ?></div>
		<?php } else { ?>
			<p>Ранее снимок не был создан.</p><br />
		<?php } ?>
		<div style="width:50%;float:left;"><?php $this->formImageMake->render('devTool/imageMake'); ?></div>
		<cite>Снимок помогает отследить изменение файлов и структуры базы данных.</cite>
		<div style="clear:both;"></div><br /><br />
	</fieldset>
<?php /*
	<fieldset>
		<legend>Генератор кода</legend>
	</fieldset>
*/ ?>
</div>
<script type="text/javascript">
	setTimeout(function() { $('.tab').tab(); },200);
</script>