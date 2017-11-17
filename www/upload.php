<?php
session_start();
if(!isset($_FILES['upload']) || !$_FILES['upload']['size'] || !$_SESSION['_uploadFolder']) _die('Uploading files is forbitten');
if(isset($_SESSION['_uploadTimeLimit'])) {
	if(time()>$_SESSION['_uploadTimeLimit']) _die('Time out. Please, try again.');
}

include(dirname(__FILE__).'/core/core.php');
core::language('global');
$fileName=core::translit($_FILES['upload']['name']);
$ext=strtolower(substr($fileName,strrpos($fileName,'.')+1));
switch($ext) {
case 'jpeg': case 'jpg': case 'png': case 'gif': case 'xls': case 'docx': case 'doc': case 'zip': case 'gzip': case 'rar': case '7zip': case 'pdf':
	break;
default:
	_die('You cannot upload this file');
}

$f=core::path().$_SESSION['_uploadFolder'].$fileName;
if(file_exists($f)) {
	$fileName=str_replace(array('.',' '),'',microtime()).'_'.$fileName;
	$f=core::path().$_SESSION['_uploadFolder'].$fileName;
}
if(!move_uploaded_file($_FILES['upload']['tmp_name'],core::path().$_SESSION['_uploadFolder'].$fileName)) _die('Cannot move file');
if(isset($_SESSION['_uploadList'])) {
	$_SESSION['_uploadList'][$fileName]=array('tmpName'=>$_FILES['upload']['tmp_name'],'type'=>$_FILES['upload']['type'],'size'=>$_FILES['upload']['size']);
}
die(json_encode(array(
	'uploaded'=>1,
	'fileName'=>$fileName,
	'url'=>core::url().$_SESSION['_uploadFolder'].$fileName
)));

function _die($message) {
	die(json_encode(array('uploaded'=>0,'error'=>array('message'=>$message))));
}