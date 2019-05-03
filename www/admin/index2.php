<?php
require_once('./core/core.php');

$name=plushka::path().'admin/controller/'.$_GET['corePath'][0].'.php';
if(!file_exists($name)) {
	include(plushka::path().'admin/controller/error.php');
	plushka::$controller=new sController();
	plushka::error404();
}
include_once($name);

runApplication(false);