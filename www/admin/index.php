<?php
use plushka\admin\core\core;
use plushka\admin\core\controller;

require_once(__DIR__.'/core/core.php');

$name=plushka::path().'admin/controller/'.$_GET['corePath'][0].'.php';
if(!file_exists($name)) {
	include(plushka::path().'admin/controller/error.php');
	plushka::$controller=new \plushka\admin\controller\sController();
	plushka::error404();
}
include_once($name);
runApplication();