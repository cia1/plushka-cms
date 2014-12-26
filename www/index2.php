<?php
if(isset($_GET['controller'])) $s=$_GET['controller'];
if(isset($_GET['action'])) $s.='/'.$_GET['action'];
$_GET['corePath']=$s;
require_once('./core/core.php');

$name=core::path().'controller/'.$_GET['corePath'][0].'.php';
if(!file_exists($name)) {
	include(core::path().'controller/error.php');
	controller::$self=new sController();
	core::error404();
}
include_once($name);

runApplication(false);
?>