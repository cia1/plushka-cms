<?php
session_start();
if(!isset($_FILES['upload']) || !$_FILES['upload']['size'] || !$_SESSION['_ckUploadTo']) _die('Uploading files is forbitten');
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

if(!move_uploaded_file($_FILES['upload']['tmp_name'],core::path().$_SESSION['_ckUploadTo'].$fileName)) _die('Cannot move file');
die(json_encode(array(
	'uploaded'=>1,
	'fileName'=>$fileName,
	'url'=>core::url().$_SESSION['_ckUploadTo'].$fileName
)));

function _die($message) {
	die(json_encode(array('uploaded'=>0,'error'=>array('message'=>$message))));
}