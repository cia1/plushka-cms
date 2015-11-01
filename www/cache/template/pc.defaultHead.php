<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="<?=$this->metaDescription?>" />
	<meta name="keywords" content="<?=$this->metaKeyword?>" />
	<title><?=$this->metaTitle?></title>
	<link href="<?=core::url()?>public/template/style.css" rel="stylesheet" type="text/css" media="screen" />
	<link rel="shortcut icon" href="<?=core::url()?>favicon.ico" type="image/x-icon" />
	<?=$this->_head?>
</head>
<body itemscope itemtype="http://schema.org/WebPage">
	<?=core::widget('admin')?>
	<header>
		<a href="<?=core::url()?>"><img src="<?=core::url()?>public/template/logo1.png" alt="cms0" style="float:left;width:140px;" /></a>
		<?=core::widget('language')?>
		<?=core::widget('search')?>
	</header>

	<div id="header"><?=core::widget('menu','1')?></div>

	<div id="page">
		<main>
			<?php $this->breadcrumb(); ?>
			<div class="block">
				<?=core::section('top')?>
				<?php if($this->pageTitle) echo '<h1 class="pageTitle">'.$this->pageTitle.'</h1>'; ?>
				