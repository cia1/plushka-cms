<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<?php if($this->metaDescription) echo '<meta name="description" content="'.$this->metaDescription.'" />'; ?><?php if($this->metaKeyword) echo '<meta name="keyword" content="'.$this->metaKeyword.'" />'; ?>
	<title><?=$this->metaTitle?></title>
	<link href="<?=plushka::url()?>public/template/style.css" rel="stylesheet" type="text/css" media="screen" />
	<link rel="shortcut icon" href="<?=plushka::url()?>favicon.ico" type="image/x-icon" />
	<?=$this->_head?>
</head>
<body itemscope itemtype="http://schema.org/WebPage">
	<?=plushka::widget('admin')?>
	<header>
		<a href="<?=plushka::url()?>"><img src="<?=plushka::url()?>public/template/logo1.png" alt="cms0" style="float:left;width:140px;" /></a>
		<?=plushka::widget('language')?>
		<?=plushka::widget('search')?>
	</header>
	<div id="header"><?=plushka::widget('menu',1)?></div>
	<div id="page">
		<main>
			<?php $this->breadcrumb(); ?>
			<div class="block">
				<?=plushka::section('top')?>
				<?php if($this->pageTitle) echo '<h1 class="pageTitle">'.$this->pageTitle.'</h1>'; ?>
				